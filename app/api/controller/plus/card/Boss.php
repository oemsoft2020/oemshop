<?php

namespace app\api\controller\plus\card;

use app\api\controller\Controller;
use app\api\model\plus\card\Card;
use app\api\model\plus\card\RadarClient;
use app\api\model\plus\supply\Supply;
use app\common\model\plus\card\CardAuth;
use app\common\model\plus\card\CardCount;
use app\common\service\qrcode\QrcodeService;
use think\db\Where;
use think\facade\Db;

class Boss extends Controller
{

    /* 总览 */
    public function index()
    {
        //新增客户数
        $data =  $this->postData();
        $user_info = $this->getUser();
        
        $radarClientModel = new RadarClient();

        $cardModel = new Card();

        $cardCountModel = new CardCount();
        
        $cardInfo = $cardModel->getSelfCard($user_info['user_id']);

        if($cardInfo['type'] != 'boss'){

            return $this->renderError('权限不足,仅BOSS可查看',[],3);
        }

        $beginTime = 0; 
        if($data['time'] < 0){

            $beginTime = mktime(0, 0, 0, date("m"), date("d") +$data['time'], date("Y"));

        }

        //查出当前supply_id下的名片
        $where = [
            'supply_id'=>$cardInfo['supply_id'],
            'status'=>1
        ];
        $currentSupplyCard = $cardModel->where($where)->column('card_id');

         //总用户数
        $params = [
            'supply_id'=>$cardInfo['supply_id'],
        ];
         //浏览用户
        $view_params = [
            ['sign','=','view'],
            ['type','=', 1],
        ];
        //跟进客户
        $mark_params = [
            'supply_id'=>$cardInfo['supply_id'],
            'status'=>2,
        ];

         //转发次数
        $share_params = [
            ['sign','=','praise'],
            ['type','=', 4],
            ['card_id','in',$currentSupplyCard]
        ];

        //保存次数
        $save_params = [
            ['sign','=','copy'],
            ['type','=', 1],
            ['card_id','in',$currentSupplyCard]
        ];

        //被点赞次数
        $zan_params = [
            ['sign','=','praise'],
            ['type','=', 3],
            ['card_id','in',$currentSupplyCard]
        ];

        if($beginTime > 0){   
            $params['create_time'] = $beginTime;
            $mark_params['create_time'] = $beginTime;

            $view_params[] = ['create_time','>',$beginTime];
            $share_params[] = ['create_time','>',$beginTime];
            $save_params[] = ['create_time','>',$beginTime];
            $zan_params[] = ['create_time','>',$beginTime];

           
        }

        $new_client = $radarClientModel->getClientCount($params);

        $mark_client = $radarClientModel->getClientCount($mark_params);

        $view_client = $cardCountModel->where($view_params)->group('user_id')->count('user_id');

        $share_count = $cardCountModel->where($share_params)->count();
        
        $save_count = $cardCountModel->where($save_params)->count();
        
        $thumbs_count = $cardCountModel->where($zan_params)->count();
        //成交客户
        $series = $radarClientModel->getSeriesData('',$params);

        $pie = [
            'series'=>$series,
        ];
      
        $nine = [
            'new_client'=>$new_client,
            'view_client'=>$view_client,
            'mark_client'=>$mark_client,
            'share_count'=>$share_count,
            'save_count'=>$save_count,
            'thumbs_count'=>$thumbs_count,
        ];
        return $this->renderSuccess('读取成功',compact('nine','pie'));

    }

    /* 获取团队 */
    public function team()
    {
        $data = $this->postData();

        $cardModel = new card();

        $user_info = $this->getUser();
        $cardInfo = $cardModel->getSelfCard($user_info['user_id']);

        if($cardInfo['type'] != 'boss'){

            return $this->renderError('权限不足,仅BOSS可查看',[],3);
        }
        $params = [
            'supply_id'=>$cardInfo['supply_id']
        ];
        $teamList = $cardModel->getList($params);

        return $this->renderSuccess('成功',compact('teamList'));
    }

    /* 获取团队下的客户 */
    public function custom()
    {
        $data = $this->postData();

        $params =[];
        $user_id = $data['user_id'];
        $radarClientModel = new RadarClient();
        $customList =  $radarClientModel->getListByParams($params,$user_id);
        foreach($customList as &$custom){
            $custom['date'] = date('Y-m-d',$custom['deal_time']);
        }
        unset($custom);
        return $this->renderSuccess('成功',compact('customList'));
    }

    /* 管理 */
    public function manager()
    {
        $data = $this->postData();

        $canInviter = false;

        $cardModel = new card();

        $user_info = $this->getUser();
        $cardInfo = $cardModel->getSelfCard($user_info['user_id']);

        $supply_id = $cardInfo['supply_id'];

        if($cardInfo['type'] != 'boss'){

            return $this->renderError('权限不足,仅BOSS可查看',[],3);
        }
        $params = [
            'supply_id'=>$cardInfo['supply_id'],
            'status' =>-1,
        ];
        $managerList = $cardModel->getList($params);
        //当前商家名片数量限制
        $cardAuthModel = new CardAuth();
        $cardnumber = $cardAuthModel->where('supply_id',$cardInfo['supply_id'])->where('is_delete',0)->find();
        //当前商家已创建名片数量
        $supplycarnumber =  $cardModel->where('supply_id',$cardInfo['supply_id'])->where('status',1)->count();
        if(!empty($cardnumber)&&$cardnumber['number']>$supplycarnumber){
            $canInviter = true;
        }


        return $this->renderSuccess('成功',compact('managerList','canInviter','supply_id'));
    }

    /* 审核 */
    public function audit()
    {
        $data  = $this->postData();

        $cardModel = new card();
        $where = [
            'card_id'=>$data['card_id']
        ];
        $card_info =  $cardModel->where($where)->find();
        if(empty($card_info)){
            return $this->renderError('名片不存在',[],3);
        }
        $card_info->save(['status'=>1]);
        return $this->renderSuccess('成功');
    }
    /* 删除 */
    public function delete()
    {
        $data  = $this->postData();

        $cardModel = new card();
        $where = [
            'card_id'=>$data['card_id']
        ];
        $card_info =  $cardModel->where($where)->find();
        if(empty($card_info)){
            return $this->renderError('名片不存在',[],3);
        }
        $card_info->save(['is_delete'=>1]);
        return $this->renderSuccess('成功');
    }

    /* 获取邀请二维码 */
    public function inviterCode()
    {
        $params = $this->postData();
        if (empty($params['supply_id'])) {
            return  $this->renderError('公司不存在');
        }
        $source = $params['source'];
        $supplyModel = new Supply();
        $supplyinfo =  $supplyModel->detail($params['supply_id']);
        $page = 'card/pages/card/boss/inviter/inviter';
        $scene = [
            'supply_id'=>$params['supply_id'],
            'from'=>$params['from']
        ];
        $Qrcode = new QrcodeService($supplyinfo,$source,$page,$scene);
        return $this->renderSuccess('', [
            // 二维码图片地址
            'qrcode' => $Qrcode->getImage(),
        ]);
    }
   

}

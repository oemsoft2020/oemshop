<?php

namespace app\api\controller\plus\card;

use app\api\controller\Controller;
use app\api\model\settings\Setting as SettingModel;
use app\api\model\plus\card\Card as CardModel;
use app\api\model\plus\card\CardMessage;
use app\api\model\plus\supply\Supply;
use app\common\model\user\UserThumbsUp as UserThumbsUpModel;
use app\common\model\plus\card\CardCount as CardCountModel;
use app\common\service\qrcode\CardService;
use app\common\model\product\Label as LabelModel;
use app\api\model\plus\card\RadarClient;
use app\api\model\plus\card\CardCategory as CardCategoryModel;
use app\api\model\plus\card\CardGrade;
use app\api\model\plus\card\CardGradeOrder;
use app\api\model\settings\Message as MessageModel;
use app\common\model\plus\card\CardAuth;

class Card extends Controller
{


    /* 名片详情页 */
    public function detail()
    {
        $postdata = $this->postData();
        $user_info = $this->getUser(false);
        $model = new CardModel();
        $card_id = isset($postdata['card_id'])?$postdata['card_id']:0;
        $user_id = isset($user_info['user_id'])?$user_info['user_id']:0;
        $clientUnread = 0;
        $temlIds_arr = MessageModel::getMessageByNameArr('wx', ['card_chat_message']);
        $showMySelf = false;
        $myCardId = 0;
        $outofdate = 0;
        //强制获取默认版
        if(isset($postdata['getdefault'])&&$postdata['getdefault']==1){

            $card_info = $model->getDefaultCard();
            if (empty($card_info)) {
                return  $this->renderError('名片不存在');
            }
            $card_id = $card_info['card_id'];

        }else{
            if (empty($card_id)) {

                //查询自己的名片
                $card_info  =  $model->getSelfCard($user_id);
    
                if (empty($card_info)) {
                    //查询默认名片
                    $card_info = $model->getDefaultCard();
                }

                if (empty($card_info)) {
                    return  $this->renderError('名片不存在');
                }
                $card_id = $card_info['card_id'];
            }
        }
       
        $info = $model->detail($card_id, $user_id);
       
        //浏览名片记录
        if (isset($info['user_id'])&&!empty($user_id)&&$user_id != $info['user_id']) {
            $data = [
                'user_id' => $user_id,
                'to_user_id' => $info['user_id'],
                'card_id' => $card_id,
                'type' => 1,
                'sign' => 'view',
                'app_id' => $this->app_id
            ];

            $card_count  = new  CardCountModel();
            $res = $card_count->save($data);
            $radarClientModel = new RadarClient();
            $cardMessageModel = new CardMessage();
            $radarClientModel->addClient($info['user_id'],$user_id);
            $params = [
                'target_id'=>$user_id,
                'status'=>1,
                'user_id'=>$info['user_id'],
            ];
            $clientUnread = $cardMessageModel->getMessageCount($params);
            $selfCard =  $model->getSelfCard($user_id);
            if(!empty($selfCard)&&$selfCard['card_id']!=$info['card_id']){
                $showMySelf = true;
                $myCardId =  $selfCard['card_id'];
            }   
        }


        if(empty($postdata['getdefault'])){

            if(empty($info['isdefault'])&&$info['end_time']<time()){
                //浏览其他名片
                $outofdate = 2;
                if($info['user_id'] == $user_id){
                    //浏览自己名片
                    $outofdate = 1;
                }
            }
        }
        $jumpUrl = 'pages/supply/detail/detail?supply_id=4';
        return $this->renderSuccess('success', compact('info','clientUnread','temlIds_arr','showMySelf','myCardId','outofdate','jumpUrl'));
    }

    /* 点击标签 */

    public function labelClick()
    {
        $params = $this->postData();
        if (empty($params['card_id'])) {
            return  $this->renderError('名片不存在');
        }
        $card_model = new CardModel();
        $card_info =  $card_model->detail($params['card_id']);
        $user_info = $this->getUser();
        $data  = [
            'user_id' => $user_info['user_id'],
            'obj_user_id' => $card_info['user_id'],
            'card_id' => $params['card_id'],
            'type' => 'kmd_label',
            'data_id' => $params['kmd_label_id'],
            'app_id' => $this->app_id
        ];
        $model  = new UserThumbsUpModel();
        $thumbsup_info = $model->where($data)->find();
        if ($thumbsup_info) {
            return  $this->renderError('您已经点赞过了');
        }
        $res = $model->save($data);
        if ($res) {
            $radarClientModel = new RadarClient();
            $radarClientModel->addClient($card_info['user_id'],$user_info['user_id']);
            $info =  $card_model->detail($params['card_id'], $user_info['user_id']);
            return  $this->renderSuccess('点赞成功', compact('info'));
        } else {
            return  $this->renderError('点赞失败');
        }
    }

    /* 靠谱点击事件 */
    public function zanClick()
    {
        $params = $this->postData();
        if (empty($params['card_id'])) {
            return  $this->renderError('名片不存在');
        }
        $card_model = new CardModel();
        $card_info =  $card_model->detail($params['card_id']);
        $user_info = $this->getUser();
        $data = [
            'user_id' => $user_info['user_id'],
            'to_user_id' => $card_info['user_id'],
            'card_id' => $params['card_id'],
            'type' => 3,
            'sign' => 'praise',
            'app_id' => $this->app_id
        ];

        $card_count  = new  CardCountModel();
        $zan_info =  $card_count->where($data)->find();
        if ($zan_info) {
            $zan_info->delete();
        } else {
            $res = $card_count->save($data);
            $radarClientModel = new RadarClient();
            $radarClientModel->addClient($card_info['user_id'],$user_info['user_id']);
        }
        $info =  $card_model->detail($params['card_id'], $user_info['user_id']);
        return  $this->renderSuccess('操作成功', compact('info'));
    }

    /* 
    复制事件
    */
    public function copyClick()
    {
        $params = $this->postData();
        if (empty($params['card_id'])) {
            return  $this->renderError('名片不存在');
        }

        $typeList = [
            'communicationbook' => 1,
            'mobile' => 2,
            'phone' => 3,
            'wechat' => 4,
            'email' => 5,
            'company' => 6,
            'position' => 7
        ];
        $card_model = new CardModel();
        $card_info =  $card_model->detail($params['card_id']);
        $user_info = $this->getUser();
        $data = [
            'user_id' => $user_info['user_id'],
            'to_user_id' => $card_info['user_id'],
            'card_id' => $params['card_id'],
            'type' => isset($typeList[$params['type']]) ? $typeList[$params['type']] : 0,
            'sign' => 'copy',
            'app_id' => $this->app_id
        ];
        $card_count  = new  CardCountModel();
        $res = $card_count->save($data);
        $radarClientModel = new RadarClient();
        $radarClientModel->addClient($card_info['user_id'],$user_info['user_id']);
        return  $this->renderSuccess('ok');
    }

    /* 
    * 生成二维码
    */

    public function qrCode()
    {
        $params = $this->postData();
        if (empty($params['card_id'])) {
            return  $this->renderError('名片不存在');
        }
        $source = $params['source'];
        $card_model = new CardModel();
        $card_info =  $card_model->detail($params['card_id']);

        $Qrcode = new CardService($card_info,$card_info['user_id'], $source);
        return $this->renderSuccess('', [
            // 二维码图片地址
            'qrcode' => $Qrcode->getImage(),
        ]);
    }

    /* 
    *　用户添加标签
    */

    public function addUserTag()
    {
        $params = $this->postData();
        if (empty($params['card_id'])) {
            return  $this->renderError('名片不存在');
        }
        $user_info = $this->getUser(false);
        $label_model = new LabelModel();

        $card_model = new CardModel();
        $card_info =  $card_model->detail($params['card_id']);
        $data  = [
            'name' => $params['kmd_label'],
            'user_id' => $user_info['user_id'],
            'type' => 'card',
            'app_id' => $this->app_id
        ];
        $res = $label_model->save($data);
        $label = [];
        if ($res) {
            $kmd_label_list = explode(',', $card_info['kmd_label_ids']);
            $kmd_label_list[] = $label_model->kmd_label_id;
            $data = ['kmd_label_ids' => implode(',', $kmd_label_list)];
            $card_info->save($data);
            $label = $label_model->find($label_model->kmd_label_id);
        }
        return  $this->renderSuccess('ok', compact('label'));
    }

    /* 
    保存名片信息
    */

    public function editCard()
    {
        $params  = $this->postData();
        $cardModel = new CardModel();
        $noCreated = 0;
        if (isset($params['card_id'])) {
            $card_info =  $cardModel->detail($params['card_id']);
            $card_info->update($params);
            $msg = "保存成功";
            $card_id = $params['card_id'];
        } else {

            if(isset($params['from'])&&$params['from']=='inviter'){
                 //当前商家名片数量限制
                $cardAuthModel = new CardAuth();
                $cardnumber = $cardAuthModel->where('supply_id',$params['supply_id'])->where('is_delete',0)->find();
                //当前商家已创建名片数量
                $supplycarnumber =  $cardModel->where('supply_id',$params['supply_id'])->where('status',1)->count();
                if(!empty($cardnumber)&&$cardnumber['number']<=$supplycarnumber){
                    $noCreated = 1;
                    return  $this->renderSuccess('创建失败,已超数量限制',compact('noCreated'));
                }
            }
           
            $user_info = $this->getUser();
            $params['user_id'] = $user_info['user_id'];
            $params['app_id'] = $this->app_id;
            $params['status']  = -1;
            $info =  $cardModel->save($params);
            $msg = '等待审核';
            $card_id = $cardModel->card_id;
        }
        $data = [
            'card_id' => $card_id
        ];
        return  $this->renderSuccess($msg, $data);
    }

    /* 
    *供应商信息
    */
    public function supplyInfo()
    {
        $params  = $this->postData();

        $supply_model = new Supply();
        $info = [];
        if (isset($params['supply_id'])) {
            $info = $supply_model->find($params['supply_id']);
        }
        if(isset($params['from'])&&$params['from']=='inviter'){
            
        }
        return  $this->renderSuccess('success', compact('info'));
    }


    /* 
    * 我的名片
    */

    public function mine()
    {
        $model = new CardModel();
        $user_info = $this->getUser();
        $card_info  =  $model->getCardInfo($user_info['user_id']);
        $noCard = 0;
        if(empty($card_info)){
            $noCard = 1;
            return  $this->renderSuccess('您还没有名片',compact('noCard'));
        }
        if($card_info['status']!=1){
            $noCard = 1;
            return  $this->renderSuccess('您的名片尚未通过审核',compact('noCard'));
        }
        $info = $model->detail($card_info['card_id']);
        $info['outofdate'] = 0;
        if(isset($info['end_time'])&&$info['end_time']<time()){
            $info['outofdate'] = 1;
        }elseif(isset($info['end_time'])&&$info['end_time']>time()&&($info['end_time']<time()+3*24*3600)){
            $info['outofdate'] = 2;
        }
        return  $this->renderSuccess('success', compact('info'));
    }

    /* 
    * 雷达
    */
    public function radar()
    {   
    
        $card_count  = new  CardCountModel();
        $user_info = $this->getUser(); 
        $data = $this->postData();
        $radarData =  $card_count->radar($user_info,$data);

        return  $this->renderSuccess('success', compact('radarData'));
    }

    /* 时间线 */
    public function timeline()
    {
        $card_count  = new  CardCountModel();
        $user_info = $this->getUser(); 
        $data = $this->postData();
        $timeData =  $card_count->timeStatis($user_info['user_id'],$data);
        return  $this->renderSuccess('success', compact('timeData'));
    }

    /* 
    * 获取行业分类
    */
    public function getCategoryList()
    {
        $cardCategoryModel = new  CardCategoryModel();
        $category =  $cardCategoryModel->getList();
        $categoryList = $categorySubList = [];
        foreach ($category as $key => $value) {
            if(!empty($value['child'])){
                $categorySubList[] = $value['child'];
            }else{
                $categorySubList[] = [];
            }
            
        }
        $categoryList = [
            $category,
            $categorySubList
        ];

        return  $this->renderSuccess('success', compact('categoryList'));
    }

    /* 
    * 获取升级礼包列表
    */
    public function getGrade()
    {
        $data =  $this->postData();
        $cardGradeModel = new  CardGrade();
        $list = $cardGradeModel->getList($data);

        return  $this->renderSuccess('success', compact('list'));
    
    }

    /* 购买升级礼包 */
    public function buyGrade($card_grade_id)
    {
        // 用户信息
        $user = $this->getUser();
        $params = $this->request->param();
        //当前名片
        $cardModel = new CardModel();
        $cardinfo =  $cardModel->getSelfCard($user['user_id']);
        // 升级订单
        $model = new CardGradeOrder;
        // 创建订单
        if (!$model->createOrder($cardinfo, $card_grade_id, $params)) {
            return $this->renderError($model->getError() ?: '订单创建失败');
        }
        // 构建支付请求
        $payment = CardGradeOrder::onOrderPayment($user, $model, $params['pay_type'], $params['pay_source']);
        // 返回结算信息
        return $this->renderSuccess(['success' => '支付成功', 'error' => '订单未支付'], [
            'order_id' => $model['order_id'],   // 订单id
            'pay_type' => $params['pay_type'],  // 支付方式,仅支持微信
            'payment' => $payment,               // 微信支付参数
        ]);
    }

    /* 
    * 获取续费订单列表
    */

    public function getGradeOrderList()
    {
        $user = $this->getUser();
        $params = $this->postData();

        $model = new CardGradeOrder();
        $where = [
            'user_id'=>$user['user_id']
        ];
        $list =  $model->getList($where);

        return $this->renderSuccess('success',compact('list'));

    }

}

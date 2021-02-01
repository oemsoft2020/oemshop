<?php

namespace app\common\model\plus\live;

use app\common\library\helper;
use app\common\model\BaseModel;
use app\common\model\plus\live\AnchorNotice as AnchorNoticeMod;
use app\common\model\user\User as UserMod;
use app\shop\model\plus\anchor\Anchor as AnchorModel;

/**
 * 直播打卡积分模型
 */
class AnchorCoin extends BaseModel
{
    protected $name = 'anchor_coin';
    protected $pk = 'coin_id';

    /**
     * 添加主播预告积分任务
     * @Author   linpf
     * @DataTime 2020-11-09T16:23:00+0800
     * @param    array                    $data [description]
     */
    public function addLiveCoinData($data = array())
    {
        if(empty($data)){
            return ['status'=>false,'msg'=>'请提交数据'];
        }
       
        $notice_mod = new AnchorNoticeMod();

        // 获取用户id
        if(!isset($data['user_id']) || empty($data['user_id'])){
            return ['status'=>false,'msg'=>'请先登陆'];
        }

        // 获取预告id
        if(!isset($data['notice_id']) || empty($data['notice_id'])){
            return ['status'=>false,'msg'=>'请选择直播间'];
        }

        $notice = $notice_mod->where('notice_id',$data['notice_id'])->count();

        if(empty($notice)){
            return ['status'=>false,'msg'=>'直播间不存在'];
        }

        $addData = [
            'user_id' => $data['user_id'],
            'notice_id' => $data['notice_id'],
            'path' => $data['path'],
            'created_at' => time(),
            'app_id' => self::$app_id,
        ];

        $res = $this->insert($addData);
        
        return $res ? ['status'=>true,'msg'=>'内容上传成功，照片审核中'] : ['status' => false,'msg'=>'提交失败，请检查提交数据'];
    }

    /**
     * 前端获取任务积分列表
     * @Author   linpf
     * @DataTime 2020-11-10T10:28:13+0800
     * @param    [type]                   $params [description]
     * @return   [type]                           [description]
     */
    public function getCoinList($params = array())
    {
        $model = $this;
        $notice_mod = new AnchorNoticeMod();
        $user_mod = new UserMod();
        $anchor_mod = new AnchorModel();
        
        if (!empty($params['search'])) {
            $user_ids = $user_mod->where('nickName', 'like', '%' . $params['search'] . '%')->column('user_id');
            if(!empty($user_ids)){
                $model = $model->whereIn('user_id',$user_ids);
            }else{
                return [];
            }
        }

        if(!empty($params['aduit_value'])){
            $model = $model->where('status',$params['aduit_value']);
        }
        
        $data = $model->paginate($params, false, [
            'query' => request()->request()

        ])->each(function($item,$key)use($notice_mod,$user_mod,$anchor_mod){

            $item->nickName = '';
            $item->avatarUrl = '';

            if($item['user_id']){
                $user_info = $user_mod->where('user_id',$item['user_id'])->find();

                if(!empty($user_info)){
                    $item->nickName = $user_info['nickName'];
                    $item->avatarUrl = $user_info['avatarUrl'];
                }
            }


            if($item['status'] == 1){
                $item->status_text = '待审核';
            
            }elseif($item['status'] == 2){
                $item->status_text = '审核通过';
            
            }else{
                $item->status_text = '审核不通过';
            }

            $notice_info = $notice_mod->where('notice_id',$item['notice_id'])->find();

            if(!empty($notice_info)){
                $item->notice_img = $notice_info['img_url'];
                if(!empty($notice_info['anchor_id'])){
                    $item->anchor_name = $anchor_mod->where('anchor_id',$notice_info['anchor_id'])->value('name');
                }
            }else{
                $item->notice_img = '';
                $item->anchor_name = '';
            }

        });
            
        return $data;
    }
}

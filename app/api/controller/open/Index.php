<?php

namespace app\api\controller\open;

use app\KmdController;
use app\common\model\plus\anchor\Anchor as AnchorModel;
use think\facade\Db;
use think\facade\Env;
class Index extends KmdController
{
   
    public function add()
    {

        $data = $this->postData();
        if(!isset($data['app_id'])||!isset($data['sign'])){
            return $this->renderError('sign 不可为空');  
        }

        if(!isset($data['object'])){
            return $this->renderError('object 不可为空');
        }
        $prefix =  Env::get('database.prefix', 'kmdshop_');
        $tablename = $prefix.$data['object'];

        $where = [
            'name'=>$data['app_id']
        ];
        $api_info =  Db::table('kmdshop_api')->where($where)->find();
        if(empty($api_info)){
            return $this->renderError('应用api不可用');
        }
       
        $newsign = md5(md5($data['app_id'].$api_info['secret']).$data['request_id']);
        if($newsign!=$data['sign']){
            return $this->renderError('sign验证错误');
        }

        if($data['request_id']+300<time()){
            return $this->renderError('请求超时'); 
        }
        

        $where = [
            'app_id'=>$api_info['app_id'],
            'anchor_user_id'=>$data['anchor_user_id'],
            'anchor_data_id'=>$data['anchor_data_id']
        ];
        $info =  Db::table($tablename)->where($where)->find();

        if(empty($info)){
            $data['app_id'] = $api_info['app_id'];
            $data['create_time'] = time();
            $data['update_time'] = time();
            $res = Db::table($tablename)->strict(false)->insert($data);
            $msg = "数据写入成功";
        }else{
            $saveData = [
                'name' => $data['name'],
                'image_url' => $data['image_url'],
                'birthday' => $data['birthday'],
                'mobile' => $data['mobile'],
                'sex' => ($data['sex'] == '女' ? 0:1), 
                'wechat' => $data['wechat'],
                'anchor_data_id' => $data['anchor_data_id'],
                'anchor_user_id' => $data['anchor_user_id'],
                'isteam' => $data['isteam'],
                'islink' => $data['islink'],
                'broadcast' => $data['broadcast'],
                'broadcast_num' => (int)$data['broadcast_num'],
                'cut_broadcast' => $data['cut_broadcast'],
                'cut_broadcast_num' => (int)$data['cut_broadcast_num'],
                'no_broadcasted' => $data['no_broadcasted'],
                'no_broadcast_num' => $data['no_broadcast_num'],
                'is_change_account' => $data['is_change_account'],
                'change_reason' => $data['change_reason'],
                'broadcast_type' => $data['broadcast_type'],
                'bring_goods' => $data['bring_goods'],
                'bring_type' => $data['bring_type'],
                'is_open_window' => $data['is_open_window'],
                'type' => $data['type'],
                'hope_bring_type' => $data['hope_bring_type'],
                'hope_unit_price' => $data['hope_unit_price'],
                'hope_brand' => $data['hope_brand'],
                'exclusive_number' => $data['exclusive_number'],
                'no_broadcast_reason' => $data['no_broadcast_reason']
            ];

            // $data['anchor_id'] = $info['anchor_id'];
            // unset($data['app_id']);
            $res = Db::table($tablename)->where('anchor_id',$info['anchor_id'])->update($saveData);
            $msg = "数据更新成功";
            
        }
        if($msg){
            return $this->renderSuccess($msg);
        }else{
            return $this->renderError("数据写入失败,请联系技术");
        }   
       
     
    }
}
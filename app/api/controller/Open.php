<?php

namespace app\api\controller;

use app\KmdController;
use app\common\model\plus\api\Api as ApiModel;
use think\facade\Db;
class Open extends KmdController
{
   
    public function add()
    {
        $api_model = new  ApiModel;
        $data = $this->postData();
        if(!isset($data['app_id'])||!isset($data['sign'])){
            return $this->renderError('sign 不可为空');  
        }
        
        $where = [
            'name'=>$data['app_id']
        ];
        $api_info =  $api_model->where($where)->find();
        if(empty($api_info)){
            return $this->renderError('应用api不可用');
        }
        $newsign = md5(md5($data['app_id'].$api_info['secret']).$data['request_id']);
        if($newsign!=$data['sign']){
            return $this->renderError('sign验证错误');
        }
        if(!isset($data['object'])){
            return $this->renderError('object 不可为空');
        }
        $tablename = 'kmdshop_'.$data['object'];
        $where = [
            'app_id'=>$api_info['app_id'],
            'anchor_user_id'=>$data['anchor_user_id'],
            'anchor_data_id'=>$data['anchor_data_id']
        ];
        $info =  Db::table($tablename)->where($where)->find();
        if(empty($info)){
            $res = Db::table($tablename)->strict(false)->save($data);
            if($res){
                $this->renderSuccess('数据成功写入');
            }
            
        }else{
           $this->renderError('数据已存在');
        }
        
    }
}
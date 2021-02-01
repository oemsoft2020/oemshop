<?php

namespace app\shop\controller\plus\api;

use app\shop\controller\Controller;
use app\common\model\plus\api\Api as ApiModel;
use think\facade\Db;

/**
 * API控制器
 */
class App extends Controller
{

    /**
     * API查看
     */
    public function index()
    {
        $model = new ApiModel;
        if($this->request->isGet()){

            $info  = $model::find();

            $values = [
                'api_id'=>'',
                'api_secret'=>''
            ];
            if($info){
                $values = [
                    'api_id'=>$info['name'],
                    'api_secret'=>$info['secret']
                ];
            }
            
            return $this->renderSuccess('',compact('values'));
        }
        
        $info  = $model::find();
        $name  = date('YmdHis') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $secret = md5(date('YmdHis') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT));
        if(empty($info)){
            
            $data  = [
                'name'=>$name,
                'secret'=>$secret,
                'app_id'=>$this->store['app']['app_id']
            ];
            $res = $model::create($data);
        }else{
            $data  = [
                'secret'=>$secret,
            ];
            $res = $info->save($data); 
        }
       
        return $this->renderSuccess('生成密钥成功');
    }

}
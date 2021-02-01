<?php
namespace app\api\controller\plus\feng;

use app\api\controller\Controller;
use app\api\model\plus\feng\Feng as FengModel;
use app\api\model\user\UserAddress;
use app\api\model\plus\storage\Storage as StorageModel;
use app\common\model\settings\Region;
use app\api\model\settings\Express as ExpressModel;


Class Feng extends Controller{

    public function detail(){
//        halt($this->getData());
        $user = $this->getUser();
        $params = $this->getData();
        $model = new UserAddress;

        $address= $model->detail($user['user_id'],$user['address_id']);

         $model = new StorageModel();
         $data =   $model->getStorageDetail($user['user_id'],$params['storage_id']);
         $fengMdoel = new FengModel();
         $create_time = $fengMdoel->where('storage_id','=',$data['storage_id'])->field('start_time')->find();
         $create_time =  isset($create_time['start_time']) ? $create_time['start_time'] : 0;
         $data['feng_time'] = date('Y-m-d',$create_time);

        return $this->renderSuccess('', compact('data','address'));

    }

    public function apply(){

        $params = $this->getData();
        $user = $this->getUser();
        $model = new UserAddress;
        $address= $model->detail($user['user_id'],$params['address_id']);
        $data = [
          'storage_id'=>  $params['storage_id'],
          'name'=>  $address['name'],
          'mobile'=>  $address['phone'],
          'province_id'=>  $address['province_id'],
          'city_id'=>  $address['city_id'],
          'regoin_id'=>  $address['regoin_id'],
          'detail'=>  $address['detail'],
          'type'=>  1,
          'start_time'=> time(),
          'app_id'=> $params['app_id'],

        ];

        $model = new FengModel;
            $res =  $model->save($data);

            $model = new StorageModel();
            $data1 =   $model->where('storage_id',"=",$params['storage_id'])
                            ->update(['feng_status'=>1]);

        return $this->renderSuccess('', compact('data','address'));

    }


    //
    public function hui(){
        $model = new ExpressModel();

        $expressList = $model->getAll();

        $user = $this->getUser();

        $params = $this->getData();

        $model = new UserAddress;

        $address= $model->detail($user['user_id'],$user['address_id']);

        $model = new StorageModel();

        $data =   $model->getStorageDetail($user['user_id'],$params['storage_id']);

        $fengMdoel = new FengModel();

        $create_time = $fengMdoel->where('storage_id','=',$data['storage_id'])->field('start_time')->find();



        return $this->renderSuccess('', compact('data','address','expressList'));
    }

    //shop回寄封坛条
    public function fengArticle(){

        $postData = $this->getData();

        $model = new StorageModel();

        $data = $model->where('storage_id',$postData['storage_id'])->update(['feng_status'=>3]);

        $FengModel = new FengModel;

        $res = $FengModel->where('storage_id',$postData['storage_id'])->update(['type'=>3,'express_id_1'=>$postData['express_id'],'express_no_1'=>$postData['express_no']]);

        return $this->renderSuccess('', compact('data','address','expressList'));

    }
}
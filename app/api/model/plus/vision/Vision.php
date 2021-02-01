<?php


namespace app\api\model\plus\vision;


use app\common\model\plus\vision\Vision as VisionModel;
use app\api\model\user\User as UserModel;
use app\common\exception\BaseException;


class Vision extends VisionModel
{

    /**
     * 根据登录小程序端的手机号获取学生视力数据信息
     * @param $mobile
     * @param   $app_id
     * @return array|null|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getvisionlist($mobile,$app_id){
       $model = new VisionModel();
       if(isset($mobile)&&isset($app_id)) {
           $model = $model->where(["mobile" => $mobile, "app_id" => $app_id, "is_delete" => 0])
               ->order(['vision_id' => 'desc'])
               ->paginate(5, false, [
                   'query' => request()->request()
               ]);
       }

        return $model;
    }



    /**
     * 视力数据详情
     */
    public static function detail($vision_id)
    {
        if (!$model = parent::detail($vision_id)) {
            throw new BaseException(['msg' => '视力数据不存在']);
        }
        if($model){
            if($model['test_time']>0){
                $model['test_time']= date("Y-m-d",$model['test_time']);
            }
            if(isset($model['left_eyesight'])&&isset($model['right_eyesight'])&&isset($model['double_eyesight'])){
                $arr=[$model['left_eyesight'],$model['right_eyesight'],$model['double_eyesight']];
                $model['min']=min($arr);
            }
        }

        return $model;
    }
    /**
     *
     * 根据手机号获取学生视力数据信息
     * @param $data
     * @return array|null|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getvisiondata($data){
        $model = new VisionModel();

        $model= $model->where(["id_card"=> $data['id_card'],"app_id" => $data['app_id'],"is_delete" => 0])->where('student_name', 'like','%' . trim($data['student_name']) . '%')->find();

        if($model){
            if($model['test_time']>0){
               $model['test_time']= date("Y-m-d",$model['test_time']);
            }
            if(isset($model['left_eyesight'])&&isset($model['right_eyesight'])&&isset($model['double_eyesight'])){
                $arr=[$model['left_eyesight'],$model['right_eyesight'],$model['double_eyesight']];
                $model['min']=min($arr);
            }

        }
        return $model;
    }
}
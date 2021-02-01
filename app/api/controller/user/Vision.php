<?php

namespace app\api\controller\user;

use app\api\controller\Controller;
use app\api\model\plus\vision\Vision as VisionModel;
use app\api\model\user\User as UserModel;


/**
 *
 * 视力数据列表控制器
 * @package app\api\controller\user\vision
 */
class Vision extends Controller
{


    private $model;
    protected $user;

    /**
     * 构造方法
     */
    public function initialize()
    {
        parent::initialize();
        $this->model = new VisionModel;
    }

    /**
     *
     * 小程序端视力数据列表
     */
    public function visionlist(){
        $is_force=true;
        if (!$token = $this->request->param('token')) {
            if ($is_force) {
                throw new BaseException(['msg' => '缺少必要的参数：token', 'code' => -1]);
            }
            return false;
        }
        // 当前用户信息
        $userModel= new UserModel();
        $user = $userModel->getUser($token);
        if($user){
            $mobile =$user['mobile'];
            $app_id =$user['app_id'];
        }
        $visionList = $this->model->getvisionlist($mobile, $app_id);
        if ($visionList){
            return $this->renderSuccess('查询成功', compact('visionList'));
        }
        return $this->renderError('该手机号码对应的学生视力数据不存在');


    }
    /**
     *视力数据详情
     */
    public function visiondetail($vision_id)
    {
        $detail = VisionModel::detail($vision_id);
        return $this->renderSuccess('', compact('detail'));
    }


    /**
     *
     * 家长通过小程序端查询孩子视力数据
     */
    public function visiondata(){
        $data = $this->request->get();
        if ($data["student_name"] == ''){
            return $this->renderError('姓名不能为空');
        }
        if ($data["id_card"] == ''){
            return $this->renderError('身份证号码不能为空');
        }
        $visioninfo = $this->model->getvisiondata($data);
        if ($visioninfo){
            return $this->renderSuccess('查询成功', compact('visioninfo'));
        }
        return $this->renderError('该学生不存在');
    }





}
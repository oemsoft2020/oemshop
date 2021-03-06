<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/8/4
 * Time: 0:45
 */

namespace app\api\controller\plus\signadv;



use app\api\controller\Controller;
use app\api\model\plus\signadv\Signadv as SignAdvModel;
use think\App;

class Signadv extends Controller
{

    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->user = $this->getUser();   // 用户信息
        $this->model = new SignAdvModel();
    }
    private $user;
    private $model;

    /**
     *  签到详情
     */
    public function index(){
        $list = $this->model->getListByUserId($this->user["user_id"]);
        return $this->renderSuccess('', compact('list'));
    }

    /**
     *  获取今天是否打卡
     */
    public function isTrueSign(){
        $is_sign = $this->model->trueSign($this->user["user_id"]);
        if (!empty($is_sign)){
            return $this->renderSuccess( '',compact(''));
        }
    }

    /**
     * 添加用户签到
     */
    public function add(){
        $is_sign = $this->model->trueSign($this->user["user_id"]);
        $data = $this->request->post();
        if (!empty($is_sign)){
            return $this->renderError( '您今天已经签过到了');
        }
        $is_validation = $this->model->dataValidation($data["left_eye_vision"],$data["right_eye_vision"],$data["eyes_vision"]);
        if (!$is_validation){
            return $this->renderError( '请填写正确的视力数据');
        }
        $msg = $this->model->add($this->user,$data);
        if ($msg != '') {
            return $this->renderSuccess('签到成功');
        }
        return $this->renderError($this->model->getError() ?: '签到失败，请重试');
    }
    /**
     *  获取用户签到记录
     */
    public function signAdvLog(){
        $log = $this->model->getSignAdvLog($this->user);
        return $this->renderSuccess('', compact('log'));
    }

    /**
     *  获取用户有效订单
     */
    public function getOrderList(){
        $is_order_list = $this->model->effectiveOrder($this->user["user_id"]);
        if (!$is_order_list){
            return $this->renderError('非法操作！');
        }
        $is_order_list = $this->model->orderManage($is_order_list);
        return $this->renderSuccess('', compact('is_order_list'));
    }

    /**
     *  是否存在用户档案
     */
    public function getArchives(){
        $type = $this->model->dataArchives($this->user["user_id"]);
        if ($type["type"] == 'success'){
            $data = $type["data"];
         //   dd($data);
            return $this->renderSuccess($type["msg"],compact('data'));
        }else{
            return $this->renderSuccess($type["msg"],'-1');
           // return $this->renderError($type["msg"]);
        }
      //  dd($type);
    }

    /**
     *  更改订单状态
     */
    public function changeOrderType(){
        $data = $this->request->get();
        if (empty($data) || $data["days"] <= 0){
            return $this->renderError('未知错误！');
        }
        if ($this->model->orderChange($data,$this->user["user_id"])){
            return $this->renderSuccess('关联成功！');
        }else{
            return $this->renderError('关联失败！请稍后再试');
        }
    }

    /**
     * 打卡帮助按钮
     * @return \think\response\Json
     */
    public function isRules(){
        $isTrue = $this->model->isRules($this->user["user_id"]);
        if ($isTrue){
            return $this->renderSuccess('',true);
        }
        return $this->renderSuccess('',false);
    }

    /**
     * 获取配置项指定内容
     * @return \think\response\Json
     */
    public function getSetting(){
        $isTrue = $this->model->getSetting($this->request->get());
        return $this->renderSuccess('',compact('isTrue'));
    }
}
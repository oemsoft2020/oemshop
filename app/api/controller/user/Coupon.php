<?php

namespace app\api\controller\user;

use app\api\controller\Controller;
use app\api\model\plus\coupon\UserCoupon as UserCouponModel;
use app\api\model\user\User as UserModel;

/**
 * 用户优惠券
 */
class Coupon extends Controller
{
    // 模型
    private $model;
    private $model_user;

    // 当前用户
    private $user;

    /**
     * 构造方法
     */
    public function initialize()
    {
        $this->model = new UserCouponModel;
        $this->model_user = new UserModel;
        $this->user = $this->getUser();
    }

    /**
     * 优惠券列表
     */
    public function lists($data_type = 'all')
    {
        $is_use = false;
        $is_expire = false;
        switch ($data_type) {
            case 'not_use':
                $is_use = false;
                break;
            case 'is_use':
                $is_use = true;
                break;
            case 'is_expire':
                $is_expire = true;
                break;
        }
        $list = $this->model->getList($this->user['user_id'], $is_use, $is_expire);
        return $this->renderSuccess('', compact('list'));
    }

    /**
     * 领取优惠券
     */
    public function receive($coupon_id)
    {
        if ($this->model->receive($this->user, $coupon_id)) {
            return $this->renderSuccess([], '领取成功');
        }
        return $this->renderError($this->model->getError() ?: '添加失败');
    }

    /**
     * 批量领取优惠券
     */
    public function receiveList($coupon_ids)
    {
        if ($this->model->receiveList($this->user, $coupon_ids)) {
            return $this->renderSuccess('领取成功', '');
        }
        return $this->renderError('领取失败');
    }

    /**************************    新增      ***********************************************************/

    /**
     * peng
     * 查询用户是否可以转让
     */
    public function queryReceive(){
        $data = $this->request->get();
        if ($data["mobile"] == ''){
            return $this->renderError('号码不能为空');
        }
        $user = $this->model_user->setPhone($data["mobile"],$data["app_id"]);
        if ($user){
            return $this->renderSuccess('查询成功', compact('user'));
        }
        return $this->renderError('该用户不存在');
    }

    /**
     * peng
     *  获取可以转让的优惠券信息
     */
    public function userTransfer(){
        $data = $this->request->get();
        if ($data["user_id"] == ''){
            return $this->renderError('未知错误');
        }
        $coupon = $this->model->getCouponType($data["user_id"]);
        return $this->renderSuccess('', compact('coupon'));
    }

    /**
     * peng
     *  转让优惠券
     */
    public function turnSpecified(){
        $data = $this->request->post();
        $couponSum = explode(',',$data["couponSum"]);  // 优惠券数量
        $couponID = explode(',',$data["couponID"]);   // 优惠券id
        if ($data["count"] <= 0){
            return $this->renderError('非法转让数量');
        }else{
            if (!$this->couponNull($couponID,$couponSum)){
                return $this->renderError('转让数量不能为0');
            }
        }
        $transfer_id = $data["uid"];  // 转让人id
        $receive_id = $data["id"];  // 接收人id
        $pop = $this->model->validationCoupon($transfer_id,$receive_id,$couponID,$couponSum);
        if(!$pop){
            return $this->renderError('转让失败');
        }
        return $this->renderSuccess('转让成功', '');
    }

    /**
     * peng
     *  判断优惠券空提交数
     */
    public function couponNull($couponID,$couponSum){
        if (count($couponID) == 1 && $couponSum[0] == 0){
            return false;
        }
        $sun = 0;  // 计数
        for ($i = 0;$i < count($couponID);$i++){
            if ($couponSum[$i] == 0){
                ++$sun;
            }
        }
        if ($sun == count($couponID)){
            return false;
        }else{
            return true;
        }
    }

    /**
     * peng
     *  转让记录列表
     */
    public function couponLogList($data_type='all'){
        $list = $this->model->getCouponList($this->user['user_id'],$data_type);
        return $this->renderSuccess('', compact('list'));
    }
}
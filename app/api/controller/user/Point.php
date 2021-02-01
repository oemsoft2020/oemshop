<?php

namespace app\api\controller\user;

use app\api\controller\Controller;
use app\api\model\settings\Setting as SettingModel;
use app\api\model\user\PointsOrder as PointsOrderModel;
use app\common\enum\order\OrderPayTypeEnum;

/**
 * 充值套餐
 */
class Point extends Controller
{
    /**
     * 余额首页
     */
    public function index(){
        $user = $this->getUser();
        // 设置
        $settings = SettingModel::getItem('points');
        return $this->renderSuccess('', compact('settings'));
    }

    /**
     * 充值套餐
     */
    public function submit($money)
    {
        $params = $this->request->param();
        // 用户信息
        $user = $this->getUser();
        $settings = SettingModel::getItem('points');
        $real_points = intval($settings['balance_ratio'] * $money);
        if ($real_points<1) {
           return $this->renderError('金额错误');
        }
        // 生成等级订单
        $model = new PointsOrderModel();
        $order_id = $model->createOrder($user, $real_points, $money);
        if(!$order_id){
            return $this->renderError($model->getError() ?: '充值失败');
        }
        // 在线支付
        $payment = PointsOrderModel::onOrderPayment($user, $model, OrderPayTypeEnum::WECHAT, $params['pay_source']);
        // 返回结算信息
        return $this->renderSuccess(['success' => '支付成功', 'error' => '订单未支付'], [
            'order_id' => $order_id,   // 订单id
            'pay_type' => OrderPayTypeEnum::WECHAT,  // 支付方式
            'payment' => $payment,               // 微信支付参数
        ]);
    }
}
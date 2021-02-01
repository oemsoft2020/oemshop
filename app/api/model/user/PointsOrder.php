<?php

namespace app\api\model\user;

use app\api\service\order\PaymentService;
use app\common\enum\order\OrderPayTypeEnum;
use app\common\enum\order\OrderTypeEnum;
use app\common\exception\BaseException;
use app\common\model\user\PointsOrder as PointsOrderModel;

/**
 * 充值模型
 */
class PointsOrder extends PointsOrderModel
{
    /**
     * 创建订单
     */
    public function createOrder($user, $real_points, $user_money)
    {
        // 获取订单数据
        $data = [
            'order_no' => $this->orderNo(),
            'user_id' => $user['user_id'],
            'app_id' => self::$app_id,
            'pay_price' => $user_money,
            'real_points' => $real_points,
        ];
        $this->save($data);
        return $this['order_id'];
    }

    /**
     * 待支付订单详情
     */
    public static function getPayDetail($orderNo)
    {
        $model = new static();
        return $model->where(['order_no' => $orderNo, 'pay_status' => 10])->with(['user'])->find();
    }

    /**
     * 订单详情
     */
    public static function getUserOrderDetail($order_id, $user_id)
    {
        $model = new static();
        $order = $model->where(['order_id' => $order_id, 'user_id' => $user_id])->find();
        if (empty($order)) {
            throw new BaseException(['msg' => '订单不存在']);
        }
        return $order;
    }

    /**
     * 构建支付请求的参数
     */
    public static function onOrderPayment($user, $order, $payType, $pay_source)
    {
        //如果来源是h5,首次不处理，payH5再处理
        if($pay_source == 'h5'){
            return [];
        }
        if ($payType == OrderPayTypeEnum::WECHAT) {
            return self::onPaymentByWechat($user, $order, $pay_source);
        }
        return [];
    }

    /**
     * 构建微信支付请求
     */
    protected static function onPaymentByWechat($user, $order, $pay_source)
    {
        return PaymentService::wechat(
            $user,
            $order['order_id'],
            $order['order_no'],
            $order['pay_price'],
            OrderTypeEnum::POINTS,
            $pay_source
        );
    }
}

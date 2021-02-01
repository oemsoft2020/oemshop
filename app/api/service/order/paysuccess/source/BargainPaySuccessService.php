<?php

namespace app\api\service\order\paysuccess\source;

use app\common\enum\settings\DeliveryTypeEnum;
use app\common\library\helper;
use app\api\model\plus\agent\Apply as AgentApplyModel;

/**
 * 砍价订单支付成功后的回调
 */
class BargainPaySuccessService
{
    /**
     * 回调方法
     */
    public function onPaySuccess($order)
    {
        if($order['delivery_type']['value'] == DeliveryTypeEnum::NO_EXPRESS){
            $order->save([
                'delivery_status' => 20,
                'delivery_time' => time(),
                'receipt_status' => 20,
                'receipt_time' => time(),
                'order_status' => 30
            ]);
        }
        return true;
    }
}
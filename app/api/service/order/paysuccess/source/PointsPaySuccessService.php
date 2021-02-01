<?php

namespace app\api\service\order\paysuccess\source;

use app\common\library\helper;
use app\api\model\plus\agent\Apply as AgentApplyModel;

/**
 * 积分订单支付成功后的回调
 */
class PointsPaySuccessService
{
    /**
     * 回调方法
     */
    public function onPaySuccess($order)
    {
        return true;
    }
}
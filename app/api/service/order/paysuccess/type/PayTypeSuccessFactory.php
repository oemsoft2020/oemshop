<?php

namespace app\api\service\order\paysuccess\type;

use app\common\enum\order\OrderTypeEnum;

/**
 * 支付成功辅助工厂类
 */
class PayTypeSuccessFactory
{
    public static function getFactory($out_trade_no, $order_type)
    {
        switch ($order_type) {
            case OrderTypeEnum::MASTER:
                return new MasterPaySuccessService($out_trade_no);
                break;
            case OrderTypeEnum::GIFT;
                return new GiftPaySuccessService($out_trade_no);
                break;
            case OrderTypeEnum::GRADE;
                return new GradePaySuccessService($out_trade_no);
                break;
            case OrderTypeEnum::SUPPLY;
                return new SupplyPaySuccessService($out_trade_no);
                break;
            case OrderTypeEnum::CARD;
                return new CardGradePaySuccessService($out_trade_no);
                break;
            case OrderTypeEnum::POINTS;
                return new PointsPaySuccessService($out_trade_no);
                break;
        }
    }
}
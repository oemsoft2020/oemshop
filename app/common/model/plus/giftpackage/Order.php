<?php

namespace app\common\model\plus\giftpackage;

use app\common\model\BaseModel;
use app\common\service\order\OrderService;

/**
 * Class Order
 * 礼包购模型
 * @package app\common\model\plus\giftpackage
 */
class Order extends BaseModel
{
    protected $name = 'gift_order';
    protected $pk = 'order_id';

    /**
     * @return $this
     * 关联用户表
     */
    public function user()
    {
        return $this->belongsTo("app\\common\\model\\user\\User", 'user_id', 'user_id')->field('user_id,nickName');
    }

    /**
     * 生成订单号
     */
    public function orderNo()
    {
        return OrderService::createOrderNo();
    }
}
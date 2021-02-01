<?php

namespace app\common\enum\order;

use MyCLabs\Enum\Enum;

/**
 * 订单类型枚举类,用于后期扩展，比如虚拟物品
 */
class OrderTypeEnum extends Enum
{
    // 商城订单
    const MASTER = 10;

    // 礼包购订单
    const GIFT = 20;
    // 会员升级订单
    const GRADE = 30; 
    // 供应商申请订单
    const SUPPLY = 40;

    // 名片续费订单
    const CARD = 50; 
    // 余额充值订单
    const POINTS = 60;

    /**
     * 获取订单类型值
     */
    public static function data()
    {
        return [
            self::MASTER => [
                'name' => '商城订单',
                'value' => self::MASTER,
            ],
            self::GIFT => [
                'name' => '礼包购订单',
                'value' => self::GIFT,
            ],
            self::GRADE => [
                'name' => '会员升级订单',
                'value' => self::GRADE,
            ],
            self::SUPPLY => [
                'name' => '供应商申请订单',
                'value' => self::SUPPLY,
            ],
            self::CARD => [
                'name' => '名片续费订单',
                'value' => self::CARD,
            ],
            self::POINTS => [
                'name' => '积分充值订单',
                'value' => self::POINTS,
            ],
        ];
    }

    /**
     * 获取订单类型名称
     */
    public static function getTypeName()
    {
        static $names = [];

        if (empty($names)) {
            foreach (self::data() as $item)
                $names[$item['value']] = $item['name'];
        }

        return $names;
    }

}
<?php

namespace app\common\enum\user\balanceLog;

use MyCLabs\Enum\Enum;

/**
 * 余额变动场景枚举类
 */
class BalanceLogSceneEnum extends Enum
{
    // 用户充值
    const RECHARGE = 10;

    // 用户消费
    const CONSUME = 20;

    // 管理员操作
    const ADMIN = 30;

    // 订单退款
    const REFUND = 40;

    // 云仓回购
    const STORAGE = 50;

    // 云仓退货
    const STORAGEREFUND = 60;

    // 完成任务
    const TASK = 70;
    /**
     * 获取订单类型值
     */
    public static function data()
    {
        return [
            self::RECHARGE => [
                'name' => '用户充值',
                'value' => self::RECHARGE,
                'describe' => '用户充值：%s',
            ],
            self::CONSUME => [
                'name' => '用户消费',
                'value' => self::CONSUME,
                'describe' => '用户消费：%s',
            ],
            self::ADMIN => [
                'name' => '管理员操作',
                'value' => self::ADMIN,
                'describe' => '后台管理员 [%s] 操作',
            ],
            self::REFUND => [
                'name' => '订单退款',
                'value' => self::REFUND,
                'describe' => '订单退款：%s',
            ],
            self::STORAGE => [
                'name' => '云仓回购',
                'value' => self::STORAGE,
                'describe' => '云仓回购：%s',
            ],
            self::STORAGEREFUND => [
                'name' => '云仓退货',
                'value' => self::STORAGEREFUND,
                'describe' => '云仓退货：%s',
            ],
            self::TASK => [
                'name' => '完成任务',
                'value' => self::TASK,
                'describe' => '完成任务：%s',
            ],
        ];
    }

}
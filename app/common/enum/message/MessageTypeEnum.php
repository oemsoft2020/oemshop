<?php

namespace app\common\enum\message;

use MyCLabs\Enum\Enum;

/**
 * 消息类型枚举类
 */
class MessageTypeEnum extends Enum
{
    // 订单
    const ORDER = 10;

    // 分销
    const AGENT = 20;

     // 分销
     const CARD = 30;


    /**
     * 获取枚举数据
     */
    public static function data()
    {
        return [
            self::ORDER => [
                'value' => self::ORDER,
                'name' => '订单',
            ],
            self::AGENT => [
                'value' => self::AGENT,
                'name' => '分销',
            ],
            self::CARD => [
                'value' => self::CARD,
                'name' => '名片',
            ],
        ];
    }

}
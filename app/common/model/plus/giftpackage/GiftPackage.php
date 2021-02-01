<?php

namespace app\common\model\plus\giftpackage;

use app\common\model\BaseModel;

/**
 * Class GiftPackage
 * 礼包购模型
 * @package app\common\model\plus\giftpackage
 */
class GiftPackage extends BaseModel
{
    protected $name = 'gift_package';
    protected $pk = 'gift_package_id';

    /**
     * 物流公司详情
     */
    public static function detail($gift_package_id)
    {
        return self::find($gift_package_id);
    }
    /**
     * 开始时间
     */
    public function getStartTimeAttr($value)
    {
        return ['text' => date('Y-m-d H:i:s', $value), 'value' => $value];
    }

    /**
     * 有效期-结束时间
     */
    public function getEndTimeAttr($value)
    {
        return ['text' => date('Y-m-d H:i:s', $value), 'value' => $value];
    }

    /**
     * 状态
     */
    public function getStatusAttr($value)
    {
        $text = [0 => '未生效', 1 => '生效，已开始', 2 => '已结束'];
        return ['text' => $text[$value], 'value' => $value];
    }
}
<?php

namespace app\api\model\product;

use app\common\model\product\Label as LabelModel;
/**
 * 商品模型
 */
class Label extends LabelModel
{
    /**
     * 隐藏字段
     */
    protected $hidden = [
        'is_delete',
        'app_id',
        'create_time',
        'type',
        'introduce',
        'setting',
        'update_time'
    ];
}
<?php

namespace app\api\model\plus\carousel;

use app\common\model\plus\carousel\CarouselCategory as CarouselCategoryModel;

/**
 * 轮播图分类模型
 */
class CarouselCategory extends CarouselCategoryModel
{
    /**
     * 隐藏字段
     */
    protected $hidden = [
        'app_id',
        'update_time'
    ];
}

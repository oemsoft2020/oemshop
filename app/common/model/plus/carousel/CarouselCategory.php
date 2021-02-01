<?php

namespace app\common\model\plus\carousel;

use think\facade\Cache;
use app\common\model\BaseModel;

/**
 * 轮播图分类模型
 */
class CarouselCategory extends BaseModel
{
    protected $name = 'carousel_category';
    protected $pk = 'carousel_category_id';

    /**
     * 所有分类
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getALL()
    {
        $model = new static;
        return $model->order(['sort' => 'asc', 'create_time' => 'asc'])->select();
    }

    public static function getLastCategory()
    {
        $model = new static;
        return $model->order('sort' , 'asc')->find();
    }

}
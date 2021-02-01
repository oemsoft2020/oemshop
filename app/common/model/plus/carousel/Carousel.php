<?php

namespace app\common\model\plus\carousel;

use app\common\model\BaseModel;

/**
 * 轮播图模型
 */
class Carousel extends BaseModel
{
    protected $name = 'carousel';
    protected $pk = 'carousel_id';

    /**
     * 关联封面图
     * @return \think\model\relation\HasOne
     */
    public function image()
    {
        return $this->hasOne('app\\common\\model\\file\\UploadFile', 'file_id', 'image_id');
    }

    /**
     * 关联轮播图分类表
     * @return \think\model\relation\BelongsTo
     */
    public function category()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->BelongsTo("app\\{$module}\\model\\plus\\carousel\\CarouselCategory", 'carousel_category_id', 'carousel_category_id');
    }

    /**
     * 轮播图详情
     * @param $carousel_id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function detail($carousel_id)
    {
        return self::with(['image', 'category'])->find($carousel_id);
    }
}

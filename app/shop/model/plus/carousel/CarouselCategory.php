<?php

namespace app\shop\model\plus\carousel;

use think\facade\Cache;
use app\common\model\plus\carousel\CarouselCategory as CarouselCategoryModel;
use app\shop\model\plus\carousel\Carousel as CarouselModel;

/**
 * 轮播图分类模型
 */
class CarouselCategory extends CarouselCategoryModel
{
    /**
     * 分类详情
     * @param $carousel_category_id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function detail($carousel_category_id)
    {
        return static::find($carousel_category_id);
    }

    /**
     * 添加新记录
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        $data['app_id'] = self::$app_id;
        return $this->save($data);
    }

    /**
     * 编辑记录
     * @param $data
     * @return bool
     */
    public function edit($data)
    {
        $data['create_time'] = strtotime($data['create_time']);
        $data['update_time'] = time();
        return $this->save($data);
    }

    /**
     * 删除轮播图分类
     * @return bool
     * @throws \Exception
     */
    public function remove()
    {
        // 判断是否存在轮播图
        $carouselCount = CarouselModel::getCarouselTotal(['carousel_category_id' => $this['carousel_category_id']]);
        if ($carouselCount > 0) {
            $this->error = '该分类下存在' . $carouselCount . '个轮播图，不允许删除';
            return false;
        }
        return $this->delete();
    }

    /**
     * 删除缓存
     * @return bool
     */
    private function deleteCache()
    {
        return Cache::delete('carousel_category_' . self::$app_id);
    }

}
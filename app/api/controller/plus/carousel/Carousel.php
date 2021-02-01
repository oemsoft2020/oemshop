<?php

namespace app\api\controller\plus\carousel;

use app\api\controller\Controller;
use app\api\model\plus\carousel\Carousel as CarouselModel;
use app\api\model\plus\carousel\CarouselCategory as CarouselCategoryModel;

/**
 * 轮播图控制器
 */
class Carousel extends Controller
{
    /**
     *获取分类
     */
    public function category()
    {
        // 轮播图分类
        $category = CarouselCategoryModel::getAll();
        return $this->renderSuccess('', compact('category'));
    }

    /**
     * 轮播图列表
     */
    public function index($carousel_category_id = 0)
    {
        $model = new CarouselModel;
        $list = $model->getList($carousel_category_id, $this->postData());
        return $this->renderSuccess('', compact('list'));
    }
}
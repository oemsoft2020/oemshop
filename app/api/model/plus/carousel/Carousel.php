<?php

namespace app\api\model\plus\carousel;

use app\common\model\plus\carousel\Carousel as CarouselModel;

/**
 * 文章模型
 */
class Carousel extends CarouselModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'is_delete',
        'app_id',
        'update_time'
    ];

    /**
     * 获取轮播图列表
     * @param int $carousel_category_id
     * @param $params
     * @return \think\Paginator
     * @throws \think\db\exception\DbException
     */
    public function getList($carousel_category_id = 0, $params)
    {
        $model = $this;
        if ($carousel_category_id > 0) {
            $model = $model->where('carousel_category_id', '=', $carousel_category_id);
        } else {
            $carousel_category_id = CarouselCategory::getLastCategory()['carousel_category_id'];
            $model = $model->where('carousel_category_id', '=', $carousel_category_id);
        }
        return $model->with(['image', 'category'])
            ->where('is_delete', '=', 0)
            ->order(['sort' => 'asc', 'create_time' => 'desc'])
            ->paginate($params, false, [
                'query' => \request()->request()
            ]);
    }

}
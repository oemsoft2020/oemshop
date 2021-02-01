<?php

namespace app\shop\model\plus\carousel;

use app\common\model\plus\carousel\Carousel as CarouselModel;

/**
 * 轮播图模型
 */
class Carousel extends CarouselModel
{
    /**
     * 获取轮播图列表
     * @param $params
     * @return \think\Paginator
     * @throws \think\db\exception\DbException
     */
    public function getList($params)
    {
        return $this->with(['image', 'category'])
            ->where('is_delete', '=', 0)
            ->order(['sort' => 'asc', 'create_time' => 'desc'])
            ->paginate($params, false, [
                'query' => request()->request()
            ]);

    }

    /**
     * 新增记录
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        $data['app_id'] = self::$app_id;
        return $this->save($data);
    }

    /**
     * 更新记录
     * @param $data
     * @return bool
     */
    public function edit($data)
    {
        return $this->save($data);
    }

    /**
     * 软删除
     * @return bool
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]);
    }

    /**
     * 获取文章总数量
     * @param $where
     * @return int
     */
    public static function getCarouselTotal($where)
    {
        $model = new static;
        return $model->where($where)->where('is_delete', '=', 0)->count();
    }
}
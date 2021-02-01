<?php

namespace app\api\model\plus\banner;

use app\common\exception\BaseException;
use app\common\model\plus\banner\Banner as BannerModel;

/**
 * 文章模型
 */
class Banner extends BannerModel
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
     * 获取文章列表
     */
    public function getList($supply_id = 0)
    {
        $model = $this;
        $model = $model->where('supply_id', '=', $supply_id)->where('status', '=', 1)
            ->where('is_delete', '=', 0)
            ->order(['sort' => 'asc', 'create_time' => 'desc'])
            ->paginate();
        return $model;
    }

}
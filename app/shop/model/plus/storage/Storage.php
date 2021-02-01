<?php

namespace app\shop\model\plus\storage;

use app\common\model\plus\storage\Storage as StorageModel;

/**
 * 云仓模型
 */
class Storage extends StorageModel
{
    /**
     * 获取云仓列表
     * @param $param
     * @param $nick_name
     * @return \think\Paginator
     * @throws \think\db\exception\DbException
     */
    public function getList($param, $nick_name)
    {
        $model = $this;
        if (!empty($nick_name)) {
            $model = $model->where('product_name', 'like', '%' . $nick_name . '%');
        }
        // 获取列表数据
        return $model->with(['image', 'user'])
            ->order(['create_time' => 'asc'])
            ->paginate($param, false, [
                'query' => \request()->request()
            ]);
    }
}
<?php

namespace app\shop\model\plus\giftpackage;

use app\common\model\plus\giftpackage\Order as OrderModel;

/**
 * Class Ordre
 * 礼包购订单
 * @package app\shop\model\plus\giftpackage
 */
class Order extends OrderModel
{
    /**
     * 订单列表
     * @param $data
     */
    public function getList($data)
    {
        $model = $this;
        if ($data['user_name'] != '') {
            $model = $model->where('user_name', 'like', '%' . trim($data['user_name']) . '%');
        }
        return $model->with(['user'])
            ->where('gift_package_id', '=', $data['id'])
            ->order('create_time', 'desc')
            ->paginate($data, false, [
                'query' => request()->request()
            ]);
    }
}
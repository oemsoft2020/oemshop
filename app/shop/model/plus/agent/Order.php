<?php

namespace app\shop\model\plus\agent;

use app\common\model\plus\agent\Order as OrderModel;
use app\common\model\product\Product;
use app\common\model\user\User;
use app\common\service\order\OrderService;

/**
 * 分销商订单模型
 */
class Order extends OrderModel
{
    /**
     * 获取分销商订单列表
     */
    public function getList($user_id = null, $is_settled = -1)
    {
        $model = $this;
        // 检索查询条件
        if ($user_id > 1) {
            $model = $model->where('first_user_id|second_user_id|third_user_id', '=', $user_id);
        }
        if ($is_settled > -1) {
            $model = $model->where('is_settled', '=', $is_settled);
        }
        // 获取分销商订单列表
        $data = $model->with([
            'agent_first',
            'agent_second',
            'agent_third'
        ])
            ->where('type','=','agent')
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
        if ($data->isEmpty()) {
            return $data;
        }
        // 获取订单的主信息
        $with = ['product' => ['image', 'refund'], 'address', 'user'];
        $list = OrderService::getOrderList($data, 'order_master', $with);
        return  $this->formatData($list);
    }

}
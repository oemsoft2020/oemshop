<?php

namespace app\shop\model\plus\deposit;

use app\common\model\plus\deposit\Deposit as DepositModel;
use app\shop\controller\Controller;
/**
 * 保证金
 */
class Deposit extends DepositModel
{
    /**
     * @param $id
     * 保证金列表
     * @return \think\Collection
     */
    public function getList($param)
    {
        $model = $this;
        if (isset($param['user_id']) && !empty($param['user_id'])) {
            $model = $model->where('user_id', '=' ,$param['user_id']);
        }
        $list = $model->with(['user'])
            ->order('create_time', 'desc')
            ->paginate($param, false, [
                'query' => request()->request()
            ]);

        return $list;
    }

    /**
     *获取为开始的数据列表
     */
    public function getDatas()
    {
        return $this->where('end_time', '<', time())->select();
    }
}
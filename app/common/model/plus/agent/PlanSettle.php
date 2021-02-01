<?php

namespace app\common\model\plus\agent;

use app\common\model\BaseModel;

/**
 * 分销商计划结算资金明细模型
 */
class PlanSettle extends BaseModel
{
    protected $name = 'agent_plan_settle';
    protected $pk = 'agent_plan_settle_id';

    /**
     * 分销商资金明细
     * @param $data
     */
    public static function add($data)
    {
        $model = new static;
        $model->save(array_merge([
            'app_id' => $model::$app_id
        ], $data));
    }

    public function updateStatus($order_id)
    {
        if(empty($order_id)){
            return false;
        }
        $model = new static; 
        $where = [
            'order_id'=>$order_id
        ];
        $res = $model->where($where)->update(['is_delete'=>1]);
        return $res;
    }

    public function countMoney($user_id)
    {
        $model = new static;
        $where = [
            'user_id'=>$user_id,
            'is_delete'=>0
        ];
        return $model->where($where)->sum('money');
    }
}
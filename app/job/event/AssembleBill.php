<?php

namespace app\job\event;

use think\facade\Cache;
use app\job\model\plus\assemble\Bill as BillModel;
use app\common\library\helper;

/**
 * 拼团任务行为管理
 */
class AssembleBill
{
    private $model;

    /**
     * 执行函数
     */
    public function handle($model)
    {
        if (!$model instanceof BillModel) {
            return new BillModel and false;
        }
        $this->model = $model;
        if (!$model::$app_id) {
            return false;
        }
        $cacheKey = "task_space_assemble_bill_task_{$model::$app_id}";
        if (!Cache::has($cacheKey)) {
            // 将已过期的砍价任务标记为已结束
            $this->closeAssemble();
            Cache::set($cacheKey, time(), 10);
        }
        return true;
    }

    /**
     * 到期订单未拼团成功自动关闭，并退款
     */
    private function closeAssemble()
    {
        // 获取到期未拼团成功的订单
        $billList = $this->model->getCloseIds();
        $billIds = helper::getArrayColumn($billList, 'assemble_bill_id');
        if (!empty($billIds)) {
            //关闭订单
            $this->model->close($billIds);
        }
        // 记录日志
        $this->dologs('closeAssemble', [
            'billIds' => json_encode($billIds),
            'error' => $this->model->getError()
        ]);
        return true;
    }

    /**
     * 记录日志
     * @param $method
     * @param array $params
     * @return bool|int
     */
    private function dologs($method, $params = [])
    {
        $value = 'behavior assemble_bill Task --' . $method;
        foreach ($params as $key => $val)
            $value .= ' --' . $key . ' ' . $val;
        return log_write($value);
    }

}
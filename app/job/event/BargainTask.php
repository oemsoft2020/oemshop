<?php

namespace app\job\event;

use think\facade\Cache;
use app\job\model\plus\bargain\Task as TaskModel;
use app\common\library\helper;

/**
 * 砍价任务行为管理
 */
class BargainTask
{
    private $model;

    /**
     * 执行函数
     */
    public function handle($model)
    {
        if (!$model instanceof TaskModel) {
            return new TaskModel and false;
        }
        $this->model = $model;
        if (!$model::$app_id) {
            return false;
        }
        $cacheKey = "task_space_bargain_task_{$model::$app_id}";
        if (!Cache::has($cacheKey)) {
            // 将已过期的砍价任务标记为已结束
            $this->onSetIsEnd();
            Cache::set($cacheKey, time(), 10);
        }
        return true;
    }

    /**
     * 将已过期的砍价任务标记为已结束
     */
    private function onSetIsEnd()
    {
        // 获取已过期但未结束的砍价任务
        $list = $this->model->getEndList();
        $taskIds = helper::getArrayColumn($list, 'bargain_task_id');
        // 将砍价任务标记为已结束(批量)
        !empty($taskIds) && $this->model->setIsEnd($taskIds);
        // 记录日志
        $this->dologs('close', [
            'orderIds' => json_encode($taskIds),
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
        $value = 'behavior bargain Task --' . $method;
        foreach ($params as $key => $val)
            $value .= ' --' . $key . ' ' . $val;
        return log_write($value);
    }

}
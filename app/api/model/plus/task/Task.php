<?php

namespace app\api\model\plus\task;

use app\api\model\user\BalanceLog;
use app\api\model\user\PointsLog;
use app\api\model\user\User;
use app\common\model\plus\task\Task as TaskModel;

/**
 * 任务模型
 */
class Task extends TaskModel
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
     * 获取任务列表
     * @param $user_id
     * @param $params
     * @return \think\Paginator
     * @throws \think\db\exception\DbException
     */
    public function getList($user_id, $params)
    {
        $model = $this;
        $list = $model->alias('task')
            ->where('is_delete', '=', 0)
            ->order(['create_time' => 'desc'])
            ->paginate($params, false, [
                'query' => \request()->request()
            ]);
        foreach ($list as $val) {
            $val['is_complete'] = 0;
            $taskLogModel = new TaskLog();
            $log = $taskLogModel->where(['task_id' => $val['task_id'], 'user_id' => $user_id])->find();
            if ($log) {
                $val['is_complete'] = 1;
            }
        }
        return $list;
    }

    /**
     * 完成任务后处理
     * @param $task_id
     * @param $user_id
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveTaskLog($task_id, $user_id)
    {
        $taskInfo = $this->where('task_id', $task_id)->where('is_delete', 0)->find();
        if ($taskInfo) {
            $logModel = new TaskLog();
            $log = $logModel->getDetail($task_id, $user_id);
            if (!$log) {
                $type = $taskInfo['type'];
                $bonus = $taskInfo['bonus'];
                $describe = '完成任务：' . $taskInfo['title'];
                $userModel = new User();
                if ($type == '10') {//增加积分
                    $userModel->where('user_id', '=', $user_id)->inc('points', $bonus)->update();
                    $pointsLogModel = new PointsLog();
                    $logData = [
                        'user_id' => $user_id,
                        'value' => $bonus,
                        'describe' => $describe,
                        'app_id' => $taskInfo['app_id']
                    ];
                    $pointsLogModel->save($logData);
                } else {//增加余额
                    $userModel->where('user_id', '=', $user_id)->inc('balance', $bonus)->update();
                    $balanceLogModel = new BalanceLog();
                    $logData = [
                        'user_id' => $user_id,
                        'money' => $bonus,
                    ];
                    $balanceLogModel::add(70, $logData, $describe);
                }
                $logModel->saveTaskLog($task_id, $user_id, $bonus, $taskInfo['type'], $taskInfo['app_id']);
            }
        }
        return true;
    }
}
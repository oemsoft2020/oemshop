<?php

namespace app\api\model\plus\task;

use app\common\model\plus\task\TaskLog as TaskLogModel;

/**
 * 轮播图分类模型
 */
class TaskLog extends TaskLogModel
{
    /**
     * 隐藏字段
     */
    protected $hidden = [
        'app_id',
        'update_time'
    ];

    /**
     * 查询任务记录是否存在
     * @param $task_id
     * @param $user_id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getDetail($task_id, $user_id)
    {
        return $this->where(['task_id' => $task_id, 'user_id' => $user_id])->find();
    }

    /**
     * 添加任务记录
     * @param $task_id
     * @param $user_id
     * @param $bonus
     * @param $type
     * @param $app_id
     * @return bool
     */
    public function saveTaskLog($task_id, $user_id, $bonus, $type, $app_id)
    {
        $data = [
            'task_id'   => $task_id,
            'user_id'   => $user_id,
            'bonus'     => $bonus,
            'type'      => $type,
            'app_id'    => $app_id
        ];
        return $this->save($data);
    }
}

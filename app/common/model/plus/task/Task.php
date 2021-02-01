<?php

namespace app\common\model\plus\task;

use app\common\model\BaseModel;

/**
 * 任务模型
 */
class Task extends BaseModel
{
    protected $name = 'task';
    protected $pk = 'task_id';

    /**
     * 任务详情
     * @param $task_id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function detail($task_id)
    {
        return self::find($task_id);
    }
}

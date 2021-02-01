<?php

namespace app\shop\model\plus\task;

use app\common\model\plus\task\Task as TaskModel;

/**
 * 任务模型
 */
class Task extends TaskModel
{
    /**
     * 获取任务列表
     */
    public function getList($params)
    {
        return $this
            ->where('is_delete', '=', 0)
            ->order('create_time','desc')
            ->paginate($params, false, [
                'query' => request()->request()
            ]);
    }

    /**
     * 新增记录
     */
    public function add($data)
    {
        $data['app_id'] = self::$app_id;
        return $this->save($data);
    }

    /**
     * 更新记录
     */
    public function edit($data)
    {
        return $this->save($data);
    }

    /**
     * 软删除
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]);
    }
}
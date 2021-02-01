<?php

namespace app\api\controller\plus\task;

use app\api\controller\Controller;
use app\api\model\plus\task\Task as TaskModel;

/**
 * 任务控制器
 */
class Task extends Controller
{
    /**
     * 任务列表
     */
    public function index()
    {
        $user = $this->getUser();
        $model = new TaskModel;
        $list = $model->getList($user['user_id'], $this->postData());
        return $this->renderSuccess('', compact('list'));
    }

}
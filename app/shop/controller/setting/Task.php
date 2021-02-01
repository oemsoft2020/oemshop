<?php

namespace app\shop\controller\setting;

use app\shop\controller\Controller;
use app\shop\model\settings\Setting as SettingModel;

/**
 * 任务控制器
 */
class Task extends Controller
{
    /**
     * 任务设置
     */
    public function index()
    {

        if ($this->request->isGet()) {
            return $this->fetchData();
        }
        $model = new SettingModel;
        $data = $this->request->param();

        $arr = [
            'is_open_task' => $data['is_open_task'],
        ];
        if ($model->edit('task', $arr)) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 获取任务配置
     */
    public function fetchData()
    {
        $vars['values'] = SettingModel::getItem('task');
        return $this->renderSuccess('', compact('vars'));
    }

}

<?php

namespace app\shop\controller\setting;

use app\shop\controller\Controller;
use app\shop\model\settings\Setting as SettingModel;

/**
 * 好友圈控制器
 */
class Friend extends Controller
{
    /**
     * 好友圈设置
     */
    public function index()
    {
        if ($this->request->isGet()) {
            return $this->fetchData();
        }
        $model = new SettingModel;
        $data = $this->request->param();
        if ($model->edit('friend', $data)) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 获取好友圈配置
     */
    public function fetchData()
    {
        $vars['values'] = SettingModel::getItem('friend');
        return $this->renderSuccess('', compact('vars'));
    }

}

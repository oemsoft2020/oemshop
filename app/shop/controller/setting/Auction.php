<?php

namespace app\shop\controller\setting;

use app\shop\controller\Controller;
use app\shop\model\settings\Setting as SettingModel;

/**
 * 拍卖控制器
 */
class Auction extends Controller
{
    /**
     * 拍卖设置
     */
    public function index()
    {
        if ($this->request->isGet()) {
            return $this->fetchData();
        }
        $model = new SettingModel;
        $data = $this->request->param();
        if ($model->edit('auction', $data)) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 获取仓库配置
     */
    public function fetchData()
    {
        $vars['values'] = SettingModel::getItem('auction');
        return $this->renderSuccess('', compact('vars'));
    }

}

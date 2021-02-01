<?php

namespace app\agent\controller;

use app\agent\model\app\App as AppModel;

/**
 * 后台首页控制器
 */
class Index extends Controller
{
    /**
     * 代理商后台首页
     * 需要返回当前代理商的可使用子账号数量
     */
    public function index()
    {
        $model = new AppModel;
        $total = $model->getSystemNumTotal();
        $last = $model->getLastSystemNum();
        $res = [
            'total' => $total,
            'last' => $last
        ];
        $data=get_version('all');
        return $this->renderSuccess('', compact('res','data'));
    }
}
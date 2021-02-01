<?php

namespace app\shop\controller\setting;

use app\shop\controller\Controller;
use app\shop\model\settings\Setting as SettingModel;

/**
 * 仓库控制器
 */
class Depot extends Controller
{
    /**
     * 仓库设置
     */
    public function index()
    {

        if ($this->request->isGet()) {
            return $this->fetchData();
        }
        $model = new SettingModel;
        $data = $this->request->param();

        $arr = [
            'image' => $data['image'],
            'is_open_storage' => $data['is_open_storage'],
            'title' => isset($data['title']) ? $data['title'] : null,
            'detail' => isset($data['detail']) ? $data['detail'] : null,
            'giving_detail' => isset($data['giving_detail']) ? $data['giving_detail'] : null,
            'sales_return_detail' => isset($data['sales_return_detail']) ? $data['sales_return_detail'] : null,
            'auction_detail' => isset($data['auction_detail']) ? $data['auction_detail'] : null,
            'repo_detail' => isset($data['repo_detail']) ? $data['repo_detail'] : null,
            'FengTan_detail' => isset($data['FengTan_detail']) ? $data['FengTan_detail'] : null,
            'pick_detail' => isset($data['pick_detail']) ? $data['pick_detail'] : null
        ];
        if ($model->edit('depot', $arr)) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 获取仓库配置
     */
    public function fetchData()
    {
        $vars['values'] = SettingModel::getItem('depot');
        return $this->renderSuccess('', compact('vars'));
    }

}

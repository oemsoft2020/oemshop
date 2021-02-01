<?php

namespace app\shop\controller\product;

use app\shop\controller\Controller;
use app\shop\model\settings\Setting as SettingModel;

/**
 * 交易设置控制器
 */
class Setting extends Controller
{
    /**
     * 交易设置
     */
    public function index()
    {
        if($this->request->isGet()){
            return $this->fetchData();
        }
        $model = new SettingModel;
        $data = $this->request->param();

        $arr = [
            'product_no_text'=>$data['product_no_text'],
            'product_diy_no_text'=>$data['product_diy_no_text'],
            'product_no_show'=>$data['product_no_show'],
            'product_diy_no_show'=>$data['product_diy_no_show'],
            'product_price_show'=>$data['product_price_show'],
            'supply_show'=>$data['supply_show'],
            'label_show'=>$data['label_show'],
            'label_in_title'=>$data['label_in_title'],
            'single_spec'=>$data['single_spec'],
        ];
        if ($model->edit('product', $arr)) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 获取交易设置
     */
    public function fetchData()
    {
        $vars['values'] = SettingModel::getItem('product');
        return $this->renderSuccess('', compact('vars'));
    }

}

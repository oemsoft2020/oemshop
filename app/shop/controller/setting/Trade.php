<?php

namespace app\shop\controller\setting;

use app\shop\controller\Controller;
use app\shop\model\settings\Setting as SettingModel;
use app\shop\model\user\Grade as GradeModel;

/**
 * 交易设置控制器
 */
class Trade extends Controller
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
            'order' => [
                'close_days' => $data['close_days'],
                'receive_days' => $data['receive_days'],
                'refund_days' => $data['refund_days']
            ],
            'freight_rule' => $data['freight_rule'],
            'price_mode' => $data['price_mode'],
            'buy_auth' => $data['buy_auth'],
            'balance_text'=>isset($data['balance_text'])?$data['balance_text']:'余额',
            'open_view_product'=>isset($data['open_view_product'])?$data['open_view_product']:0,
        ];
        if ($model->edit('trade', $arr)) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 获取交易设置
     */
    public function fetchData()
    {
        $vars['values'] = SettingModel::getItem('trade');
        $model = new GradeModel;
        $grade_list = $model->getLists();
        return $this->renderSuccess('', compact('vars','grade_list'));
    }

}

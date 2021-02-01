<?php

namespace app\shop\controller\setting;

use app\common\service\BaseService;
use app\shop\controller\Controller;
use app\shop\model\settings\Setting as SettingModel;
use app\common\enum\settings\DeliveryTypeEnum;

/**
 * 商城设置控制器
 */
class Store extends Controller
{
    /**
     * 商城设置
     */
    public function index()
    {
        if ($this->request->isGet()) {
            return $this->fetchData();
        }
        $model = new SettingModel;
        $data = $this->request->param();
        $arr = [
            'name' => $data['name'],
            'describe' => $data['describe'],
            'delivery_type' => $data['checkedCities'],
            'kuaidi100' => ['customer' => $data['customer'], 'key' => $data['key']],
            'is_open_cart' => $data['is_open_cart'],
            'msg' => $data['msg']
        ];
        if ($model->edit('store', $arr)) {
            BaseService::record(SettingModel::$app_id);
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 获取商城配置
     */
    public function fetchData()
    {
        $vars['values'] = SettingModel::getItem('store');
        $all_type = DeliveryTypeEnum::data();
        return $this->renderSuccess('', compact('vars', 'all_type'));
    }

}

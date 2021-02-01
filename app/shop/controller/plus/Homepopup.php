<?php

namespace app\shop\controller\plus;

use app\shop\controller\Controller;
use app\shop\model\settings\Setting as SettingModel;
use app\shop\model\product\Category as CategoryModel;
use app\shop\model\product\Product as ProductModel;

/**
 * Class Homepush 首页推送控制器
 * @package app\shop\controller\plus\homepush
 */
class Homepopup extends Controller
{

    /**
     *首页推送配置
     */
    public function index()
    {


        if($this->request->isGet()){
            return $this->fetchData();
        }


        $model = new SettingModel;
        $data = $this->request->param();

        $arr = [
            'is_open'=>$data['is_open'],
            'name'=>$data['name'],
            'type'=>$data['type']
        ];
        if ($model->edit('homepopup', $arr)) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');



    }


    /**
     * 获取仓库配置
     */
    public function fetchData()
    {
        $vars['values'] = SettingModel::getItem('homepopup');
        return $this->renderSuccess('', compact('vars'));
    }

}
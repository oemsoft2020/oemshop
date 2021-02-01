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
class Homepush extends Controller
{

    /**
     *首页推送配置
     */
    public function index()
    {
        $key = 'homepush';
        if($this->request->isGet()){
            $vars['values'] = SettingModel::getItem($key);
            return $this->renderSuccess('', compact('vars'));
        }

        $model = new SettingModel;
        if ($model->edit($key, $this->postData())) {
            return $this->renderSuccess('操作成功');
        }
    }

}
<?php

namespace app\api\controller\plus\banner;

use app\api\controller\Controller;
use app\api\model\plus\banner\Banner as BannerModel;

/**
 * 秒杀产品控制器
 */
class Banner extends Controller
{
    
    /**
     * 列表信息
     */
    public function index($supply_id = 0)
    {
        $model = new BannerModel;
        $list = $model->getList($supply_id);
        if (empty($list->toArray()['total'])) {
        	$list = $model->getList(0);
        }
        return $this->renderSuccess('', compact('list'));
    }

}
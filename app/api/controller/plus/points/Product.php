<?php

namespace app\api\controller\plus\points;

use app\api\controller\Controller;
use app\api\model\plus\points\Product as ProductModel;
use app\common\service\product\BaseProductService;

/**
 * 积分商城控制器
 */
class Product extends Controller
{
    /**
     *积分商品列表
     */
    public function index()
    {
        $model = new ProductModel();
        $list = $model->getList($this->request->param());

        return $this->renderSuccess('', compact('list'));
    }

    /**
     *积分商品列表
     */
    public function detail($point_product_id)
    {
        $detail = (new ProductModel())->getPointDetail($point_product_id);
        //规格
        $specData = BaseProductService::getSpecData($detail['product']);
        return $this->renderSuccess('',compact('detail', 'specData'));
    }
}
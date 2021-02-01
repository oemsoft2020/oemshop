<?php

namespace app\shop\controller\product;

use app\shop\controller\Controller;
use app\shop\model\product\Brand as BrandModel;

/**
 * 商品品牌
 */
class Brand extends Controller
{
    /**
     * 商品品牌列表
     */
    public function index()
    {
        $model = new BrandModel;
        $list = $model->getList($this->postData());
        return $this->renderSuccess('', compact('list'));
    }

    /**
     * 删除商品品牌
     */
    public function delete($brand_id)
    {
        $model = BrandModel::find($brand_id);
        if ($model->remove($brand_id)) {
            return $this->renderSuccess('删除成功');
        }
        return $this->renderError($model->getError() ?:'删除失败');
    }

    /**
     * 添加商品品牌
     */
    public function add()
    {
        $model = new BrandModel;
        // 新增记录
        if ($model->add($this->request->post())) {
            return $this->renderSuccess('添加成功');
        }
        return $this->renderError($model->getError() ?:'添加失败');
    }

    /**
     * 编辑商品品牌
     */
    public function edit($brand_id)
    {
        // 模板详情
        $model = BrandModel::detail($brand_id);
        // 更新记录
        if ($model->edit($this->request->post())) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 得到修改图片
     * @return array
     */
    public function image($brand_id)
    {
        $model = new BrandModel;
        $detail = $model->detailWithImage(['brand_id' => $brand_id]);
        return $this->renderSuccess('', compact('detail'));
    }

}

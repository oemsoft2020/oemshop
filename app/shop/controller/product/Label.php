<?php

namespace app\shop\controller\product;

use app\shop\controller\Controller;
use app\shop\model\product\Label as LabelModel;

/**
 * 商品标签
 */
class Label extends Controller
{
    /**
     * 商品标签列表
     */
    public function index()
    {
        $model = new LabelModel;
        $list = $model->getList($this->postData());
        return $this->renderSuccess('', compact('list'));
    }

    /**
     * 删除商品标签
     */
    public function delete($kmd_label_id)
    {
        $model = LabelModel::find($kmd_label_id);
        if ($model->remove($kmd_label_id)) {
            return $this->renderSuccess('删除成功');
        }
        return $this->renderError($model->getError() ?:'删除失败');
    }

    /**
     * 添加商品标签
     */
    public function add()
    {
        $model = new LabelModel;
        // 新增记录
        if ($model->add($this->request->post())) {
            return $this->renderSuccess('添加成功');
        }
        return $this->renderError($model->getError() ?:'添加失败');
    }

    /**
     * 编辑商品标签
     */
    public function edit($kmd_label_id)
    {
        // 模板详情
        $model = LabelModel::detail($kmd_label_id);
        // 更新记录
        if ($model->edit($this->request->post())) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }



}

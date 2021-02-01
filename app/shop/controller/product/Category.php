<?php

namespace app\shop\controller\product;

use app\shop\controller\Controller;
use app\shop\model\product\Category as CategoryModel;
use app\common\model\file\UploadFile as UploadFileModel;

/**
 * 商品分类
 */
class Category extends Controller
{
    /**
     * 商品分类列表
     */
    public function index()
    {
        $model = new CategoryModel;
        $list = $model->getCacheTree();
        return $this->renderSuccess('', compact('list'));
    }

    /**
     * 删除商品分类
     */
    public function delete($category_id)
    {
        $model = CategoryModel::find($category_id);
        if ($model->remove($category_id)) {
            return $this->renderSuccess('删除成功');
        }
        return $this->renderError($model->getError() ?:'删除失败');
    }

    /**
     * 添加商品分类
     */
    public function add()
    {
        $model = new CategoryModel;
        // 新增记录
        if ($model->add($this->request->post())) {
            return $this->renderSuccess('添加成功');
        }
        return $this->renderError($model->getError() ?:'添加失败');
    }

    /**
     * 编辑商品分类
     */
    public function edit($category_id)
    {
        // 模板详情
        $model = CategoryModel::detail($category_id);
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
    public function image($category_id = '')
    {
        $model = new CategoryModel;
        $detail = $model->detailWithImage(['category_id' => $category_id]);
        //判断有没有小图
        if($detail['image_id_1'] != 0){
        $UploadFileModel = new UploadFileModel;
        $data = $UploadFileModel->where('file_id','=',$detail['image_id_1'])->find();
        $detail['image1'] = $data;
        }
        return $this->renderSuccess('', compact('detail'));
    }


}

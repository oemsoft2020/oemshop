<?php

namespace app\shop\controller\user;

use app\shop\controller\Controller;
use app\shop\model\product\Product as ProductModel;
use app\shop\model\user\Grade as GradeModel;
use app\shop\model\user\GradeOrder as GradeOrderModel;

/**
 * 会员等级
 */
class Grade extends Controller
{
    /**
     * 会员等级列表
     */
    public function index()
    {
        $model = new GradeModel;
        $list = $model->getList($this->postData());


        $productModel = new ProductModel();
          
        foreach ($list as $key => &$item) {

            $item['upgrade_goods_id'] = $item['upgrade_goods_id']?explode(',',$item['upgrade_goods_id']):[];
            $item['product'] = [];
            if(!empty($item['upgrade_goods_id'])){
                $item['product'] = $productModel->getListByIds($item['upgrade_goods_id']);
            }
        }
        unset($item);
        return $this->renderSuccess('', compact('list'));
    }
    /**
     * 升级订单列表
     */
    public function order()
    {
        $model = new GradeOrderModel;
        $list = $model->getList($this->postData());
        return $this->renderSuccess('', compact('list'));
    }
    /**
     * 升级订单详情
     */
    public function detail($order_id)
    {
        $model = new GradeOrderModel;
        $detail = $model->getDetail($order_id);
        return $this->renderSuccess('', compact('detail'));
    }
    /**
     * 审核
     */
    public function editApplyStatus($order_id)
    {
        $model = GradeOrderModel::detail($order_id);
        if ($model->submit($this->postData())) {
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError('修改失败'.$model->getError());

    }
    /**
     * 添加等级
     */
    public function add()
    {
        $model = new GradeModel;
        // 新增记录
        if ($model->add($this->postData())) {
            return $this->renderSuccess('添加成功');
        }
        return $this->renderError('添加失败');
    }

    /**
     * 编辑会员等级
     */
    public function edit($grade_id)
    {
        $model = GradeModel::detail($grade_id);
        // 修改记录
        if ($model->edit($this->postData())) {
            return $this->renderSuccess();
        }
        return $this->renderError();
    }

    /**
     * 删除会员等级
     */
    public function delete($grade_id)
    {
        // 会员等级详情
        $model = GradeModel::detail($grade_id);
        if (!$model->setDelete()) {
            return $this->renderError('已存在用户，删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

}
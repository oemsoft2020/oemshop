<?php

namespace app\shop\controller\product;

use app\common\enum\settings\DeliveryTypeEnum;
use app\shop\model\product\Product as ProductModel;
use app\shop\model\product\Label as LabelModel;
use app\shop\model\product\Category as CategoryModel;
use app\shop\model\settings\Setting as SettingModel;
use app\shop\service\ProductService;
use app\shop\controller\Controller;
use app\shop\service\order\ExportService;

/**
 * 商品管理控制器
 */
class Product extends Controller
{
    public $commission_list = [
        ['id' => 'different_level', 'name' => '级差返利'],
        ['id' => 'commission_rules', 'name' => '推荐返利']
    ];

    /**
     * 商品列表(全部)
     */
    public function index()
    {
        // 获取全部商品列表
        $model = new ProductModel;
        //调用自动上下架接口
        $model->shelves();
        $list = $model->getList(array_merge(['status' => -1], $this->postData()));
        // 商品分类
        $catgory = CategoryModel::getCacheTree();
        $product_vars = SettingModel::getItem('product');
        return $this->renderSuccess('', compact('list', 'catgory','product_vars'));
    }

    /**
     * 商品列表(在售)
     */
    public function lists()
    {
        // 获取全部商品列表
        $model = new ProductModel;
        $list = $model->getLists($this->postData());
        // 商品分类
        $catgory = CategoryModel::getCacheTree();
        return $this->renderSuccess('', compact('list', 'catgory'));
    }

    /**
     * 添加商品
     */
    public function add($scene = 'add')
    {
        // get请求
        if ($this->request->isGet()) {
            return $this->getBaseData();
        }
        //post请求
        $data = $this->postData();

        if (isset($data['link_start_at']) && !empty($data['link_start_at'])) {
            $data['link_start_at'] = strtotime($data['link_start_at']);
        }

        if (isset($data['link_end_at']) && !empty($data['link_end_at'])) {
            $data['link_end_at'] = strtotime($data['link_end_at']);
        }

        if (isset($postData['delivery_type']) && !empty($postData['delivery_type'])) {
            $postData['delivery_type'] = implode(',',$postData['delivery_type']);
        }

        if ($scene == 'copy') {
            unset($data['create_time']);
            unset($data['sku']['product_sku_id']);
            unset($data['sku']['product_id']);
            unset($data['product_sku']['product_sku_id']);
            unset($data['product_sku']['product_id']);
            //初始化销量等数据
            $data['sales_initial'] = 0;
        }

        $model = new ProductModel;
        if (isset($data['product_id'])) {
            $data['product_id'] = 0;
        }

        if ($model->add($data)) {
            return $this->renderSuccess('添加成功');
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 获取基础数据
     */
    public function getBaseData()
    {
        $product_vars = SettingModel::getItem('product');
        $storage_vars = SettingModel::getItem('depot');
        $all_type = array_merge(DeliveryTypeEnum::data());

        $commission_list = $this->commission_list;
        return $this->renderSuccess('', array_merge(ProductService::getEditData(null, 'add'), compact('product_vars', 'storage_vars', 'commission_list','all_type')));
    }

    /**
     * 获取编辑数据
     */
    public function getEditData($product_id, $scene = 'edit')
    {
        $model = ProductModel::detail($product_id);
        return $this->renderSuccess('', array_merge(ProductService::getEditData($model, $scene), compact('model')));
    }

    /**
     * 商品编辑
     */
    public function edit($product_id, $scene = 'edit')
    {
        if ($this->request->isGet()) {
            $model = ProductModel::detail($product_id);
            // 格式化时间
            if (isset($model['link_start_at']) && !empty($model['link_start_at'])) {
                $model['link_start_at'] = date('Y-m-d H:i:s', $model['link_start_at']);
            } else {
                $model['link_start_at'] = '';
            }

            if (isset($model['link_end_at']) && !empty($model['link_end_at'])) {
                $model['link_end_at'] = date('Y-m-d H:i:s', $model['link_end_at']);
            } else {
                $model['link_end_at'] = '';
            }

            $model['time'] = [$model['link_start_at'], $model['link_end_at']];

            $relation_product = ProductModel::with(['image.file'])->where('product_id', 'in', $model->relation_product_ids)->select();
            $relation_label = LabelModel::where('kmd_label_id', 'in', $model->kmd_label_ids)->select();
            $labels_name = '';
            foreach ($relation_label as $v) {
                $labels_name .= $v['name'];
            }
            if ($labels_name) {
                $name = explode($labels_name, $model->product_name);
                $model->product_name = end($name);
            }
            $model->setting = json_decode($model->setting);
            $model->commission_type = $model->commission_type ? json_decode($model->commission_type) : [];
            $deliveryType = explode(',', $model->delivery_type);
            $delivery = [];
            foreach ($deliveryType as $v) {
                $delivery[] = intval($v);
            }
            $model->delivery_type = $delivery;
            $product_vars = SettingModel::getItem('product');
            $storage_vars = SettingModel::getItem('depot');
            $all_type = array_merge(DeliveryTypeEnum::data());

            $commission_list = $this->commission_list;
            //独立详细分销佣金
            $model->independent_commission = $model->independent_commission ? json_decode($model->independent_commission) : [];
            return $this->renderSuccess('', array_merge(ProductService::getEditData($model, $scene), compact('model', 'relation_product', 'relation_label', 'product_vars', 'storage_vars', 'commission_list','all_type')));
        }
        if ($scene == 'copy') {
            return $this->add($scene);
        }
    
        $postData = $this->postData();
        if($scene =='sort'){
            if(empty($postData['product_sort'])){
                return $this->renderError('更新失败');
            }
            return $this->updateSort($product_id,$postData['product_sort']);
        }
        if (isset($postData['link_start_at']) && !empty($postData['link_start_at'])) {
            $postData['link_start_at'] = strtotime($postData['link_start_at']);
        }

        if (isset($postData['link_end_at']) && !empty($postData['link_end_at'])) {
            $postData['link_end_at'] = strtotime($postData['link_end_at']);
        }
        if (isset($postData['delivery_type']) && !empty($postData['delivery_type'])) {
            if ($postData['delivery_type'][0] == 0) unset($postData['delivery_type'][0]);
            $postData['delivery_type'] = implode(',',$postData['delivery_type']);
        }
        // 商品详情
        $model = ProductModel::detail($product_id);
        // 更新记录
        if ($model->edit($postData)) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 修改商品状态
     */
    public function state($product_id, $state)
    {
        // 商品详情
        $model = ProductModel::detail($product_id);
        if (!$model->setStatus($state)) {
            return $this->renderError('操作失败');
        }
        return $this->renderSuccess('操作成功');
    }

    /**
     * 删除商品
     */
    public function delete($product_id)
    {
        // 商品详情
        $model = ProductModel::detail($product_id);
        if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    // 批量删除商品
    public function batchDel($product_ids)
    {
        if ($product_ids) {
            $model = new ProductModel;
            $product_ids = explode(',', $product_ids);
            $data = array();
            $data['is_delete'] = 1;
            $res = $model->where('product_id', 'in', $product_ids)
                ->save($data);

            return !$res ? $this->renderError($model->getError() ?: '删除失败') : $this->renderSuccess('删除成功');
        }

    }

    /**
     * 批量上架商品
     */
    public function batchShelvesProduct($product_ids)
    {
        //var_dump($product_ids);die;
        $model = new ProductModel;
        $result = $model->batchShelvesProduct($product_ids);
        if (!$result) {
            return $this->renderError('操作失败，请检查是否有未上架商品');
        }
        return $this->renderSuccess('操作成功');

    }

    /**
     * 批量下架商品
     */
    public function batchUnShelvesProduct($product_ids)
    {
        //var_dump($product_ids);die;
        $model = new ProductModel;
        $result = $model->batchUnShelvesProduct($product_ids);
        if (!$result) {
            return $this->renderError('操作失败，请检查是否有未下架商品');
        }
        return $this->renderSuccess('操作成功');

    }

    // 批量修改库存
    public function batchStock($product_ids, $num = 0)
    {
        $model = new ProductModel;
        $res = $model->batchStock($product_ids, $num);
        if (!$res) {
            return $this->renderError('操作失败，请检查商品库存数量是否填写有误');
        }
        return $this->renderSuccess('操作成功');
    }

    // 批量修改直推收益
    public function batchIncome($product_ids, $money = 0.00)
    {
        $model = new ProductModel;
        $res = $model->batchIncome($product_ids, $money);
        if (!$res) {
            return $this->renderError('操作失败，请检查直推收益是否填写有误');
        }
        return $this->renderSuccess('操作成功');
    }

    // 导出商品
    public function exportGoods($goods_ids = '')
    {
        $model = new ProductModel;
        $data = $model->getExportGoods($this->postData(), $goods_ids);

        // 导出excel文件
        return !empty($data) ? (new Exportservice)->productExportData($data) : $this->renderError('暂无商品导出');
    }

    /**
     * 回收站列表(全部)
     */
    public function recycleIndex()
    {
        // 获取全部商品列表
        $model = new ProductModel;
        //调用自动上下架接口
        // $model->shelves();
        $list = $model->getRecycleGoods($this->postData());
        // 商品分类
        $catgory = CategoryModel::getCacheTree();
        $product_vars = SettingModel::getItem('product');
        return $this->renderSuccess('', compact('list', 'catgory','product_vars'));
    }

    /**
     * 恢复商品
     */
    public function recycle($product_id)
    {
        $model = new ProductModel;

        $res = $model->where('product_id',$product_id)->update(['is_delete'=>0]);

        // $model = ProductModel::detail($product_id);
        // if (!$model->setStatus($state)) {
        //     return $this->renderError('操作失败');
        // }
        return $res ? $this->renderSuccess('操作成功') : $this->renderError('操作失败');
    }

    /* 
    * 更新排序
    */

    public function updateSort($product_id,$product_sort)
    {
        $model = new ProductModel;

        $res = $model->where('product_id',$product_id)->update(['product_sort'=>$product_sort]);
        return $res ? $this->renderSuccess('操作成功') : $this->renderError('操作失败');
    }
}

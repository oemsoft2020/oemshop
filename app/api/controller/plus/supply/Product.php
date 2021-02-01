<?php

namespace app\api\controller\plus\supply;

use app\api\controller\Controller;
use app\api\model\plus\supply\Supply as SupplyModel;
use app\api\model\order\Order as OrderModel;
use app\api\model\product\Product as ProductModel;
use app\shop\model\product\Product as ShopProductModel;
use app\shop\model\settings\Setting as SettingModel;
use app\shop\service\ProductService;
use app\common\enum\settings\DeliveryTypeEnum;

/**
 * 供应商控制器
 */
class Product extends Controller
{
    // user
    private $user;

    /**
     * 构造方法
     */
    public function initialize()
    {
        parent::initialize();
        $this->user = $this->getUser();   // 用户信息
        $this->supply = SupplyModel::detail(['user_id'=>$this->user['user_id']]);

    }
    /**
     * 商品列表
     */
    public function lists()
    {
        if (empty($this->supply['supply_id'])) {
            return $this->renderError('无权限操作');
        }
        
        // 整理请求的参数
        $supply = $this->supply;
        $param = array_merge($this->postData(), [
            'supply_id' => $supply['supply_id'],
            'owner'=>'supply'

        ]);

        // 获取列表数据
        $model = new ProductModel;
        $list = $model->getList($param, $this->getUser(false));
        return $this->renderSuccess('', compact('list'));
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
        $data = json_decode($data['product_data'],true);
        if (isset($data['link_start_at']) && !empty($data['link_start_at'])) {
            $data['link_start_at'] = strtotime($data['link_start_at']);
        }

        if (isset($data['link_end_at']) && !empty($data['link_end_at'])) {
            $data['link_end_at'] = strtotime($data['link_end_at']);
        }

        if (isset($postData['delivery_type']) && !empty($postData['delivery_type'])) {
            $postData['delivery_type'] = implode(',',$postData['delivery_type']);
        }
        $data['supply_id'] = $this->supply['supply_id'];
        $model = new ShopProductModel;
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

        return $this->renderSuccess('', array_merge(ProductService::getEditData(null, 'add'), compact('product_vars', 'storage_vars','all_type')));
    }
    /**
     * 获取编辑数据
     */
    public function getEditData($product_id, $scene = 'edit')
    {
        $model = ShopProductModel::detail($product_id);
        return $this->renderSuccess('', array_merge(ProductService::getEditData($model, $scene), compact('model')));
    }

    /**
     * 商品编辑
     */
    public function edit($product_id, $scene = 'edit')
    {
        if ($this->request->isGet()) {
            $model = ShopProductModel::detail($product_id);
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

            return $this->renderSuccess('', array_merge(ProductService::getEditData($model, $scene), compact('model', 'product_vars', 'storage_vars','all_type')));
        }
        if ($scene == 'copy') {
            return $this->add($scene);
        }

        $postData = $this->postData();
        $postData = json_decode($postData['product_data'],true);
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
        $model = ShopProductModel::detail($product_id);
        // 更新记录
        if ($model->edit($postData,true)) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
     /**
     * 删除商品
     */
    public function delete($product_id)
    {
        // 商品详情
        $model = ShopProductModel::detail($product_id);
        if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }
     /**
     * 上架商品
     */
    public function shelvesProduct()
    {
        $model = new ProductModel;
        $data=$this->postData();
        $res = $model->where('product_id','=',$data['product_id'])
            ->save(['product_status'=>$data['product_status']]);
        if ($res) {
            return $this->renderSuccess('操作成功');
        }else{
             return $this->renderError('操作失败');
        }
        
    }
    /**
     * 刷新商品
     */
    public function updateProduct()
    {
        $setting = SettingModel::getItem('supply');
        $model = new ProductModel;
        $data=$this->postData();
        if($this->user['points']<$setting['promotion_points']){
             return $this->renderError('积分不足');
        }
        if ($setting['promotion_points']>0) {
             $describe = "用户刷新商品消费：商品ID:{$data['product_id']}";
            $this->user->setIncPoints(-$setting['promotion_points'], $describe);
        }
       
        $res = $model->where('product_id','=',$data['product_id'])
            ->save(['promotion_time'=>time()]);
        if ($res) {
            return $this->renderSuccess('操作成功');
        }else{
             return $this->renderError('操作失败');
        }
        
    }
}
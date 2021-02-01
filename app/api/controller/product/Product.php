<?php

namespace app\api\controller\product;

use app\api\model\plus\codebatch\CodeBatch;
use app\api\model\product\Product as ProductModel;
use app\api\model\product\Label as LabelModel;
use app\api\model\order\Cart as CartModel;
use app\api\controller\Controller;
use app\common\model\settings\Setting;
use app\common\service\qrcode\ProductService;
use app\common\model\plus\supply\Supply as SupplyModel;
use app\api\model\settings\Setting as SettingModel;
use app\common\model\product\Product as ProductModel1;
use app\api\model\order\OrderProduct as OrderProductMod;
use app\common\model\order\OrderProduct as OrderModel;
use app\shop\model\user\User as UserModel;


/**
 * 商品控制器
 */
class Product extends Controller
{

    /**
     * 商品列表
     */
    public function lists()
    {
        // 整理请求的参数
        $param = array_merge($this->postData(), [
            'product_status' => 10
        ]);

        // 获取列表数据
        $model = new ProductModel;
        $list = $model->getList($param, $this->getUser(false));
        return $this->renderSuccess('', compact('list'));
    }

    /**
     * 推荐产品
     */
    public function recommendProduct()
    {
        $model = new ProductModel;
        $list = $model->getRecommendProduct($this->postData(), $this->getUser(false));
        return $this->renderSuccess('', compact('list'));
    }

    /**
     * 获取商品详情
     */
    public function detail($product_id, $url, $time = null)
    {
        // 用户信息
        $user = $this->getUser(false);
        /*
         * 访问自动上下架接口。判断商品的上下架状态
         * 2010.10.09
         * by keven
         * */
        $model1 = new ProductModel1();
        $model1->shelves();

        // 商品详情
        $model = new ProductModel;
        $product = $model->getDetails($product_id, $this->getUser(false));


        //浏览商品记录
        $model->productBrowseRecords($user, $product_id);


        if ($product === false) {
            return $this->renderError($model->getError() ?: '商品信息不存在');
        }

        $relation_list = ProductModel::with(['image.file'])->where('product_id', 'in', $product->relation_product_ids)->select();

        /*
         * 添加购买人数据
         * 2010.10.09
         * by keven
         * */
        $list  = $this->PurchaseRecords($product_id);

        $relation_product = $model->setProductListDataFromApi($relation_list, true, ['userInfo' => $this->getUser(false)]);
        $relation_label = LabelModel::where('kmd_label_id', 'in', $product->kmd_label_ids)->select();
        $supply = SupplyModel::detail($product->supply_id);
        $supply_vars = SettingModel::getItem('supply');
        $product_vars = SettingModel::getItem('product');
        // 多规格商品sku信息
        $specData = $product['spec_type'] == 20 ? $model->getManySpecData($product['spec_rel'], $product['sku']) : null;
        // 商品批次信息
        $codeBatchModel = new CodeBatch();
        if ($product['type'] == 'birthday' && $product['code_product_open'] == 1 && isset($time)) {
            $productBatchData = $codeBatchModel->getProductBatch($product['product_id'], $product['type'], $time);
            if (!$productBatchData) {
                return $this->renderError('当前日期的编码不存在');
            }
        } elseif ($product['type'] == 'birthday' && $product['code_product_open'] == 1 && !isset($time)) {
            $productBatchData = $codeBatchModel->getProductBatch($product['product_id'], $product['type']);
        } elseif ($product['type'] != 'birthday' && $product['code_product_open'] == 1) {
            $productBatchData = $codeBatchModel->getProductBatch($product['product_id'], $product['type']);
        } else {
            $productBatchData = [];
        }
        $values = Setting::getItem('logistics');
        $store = Setting::getItem('store');
        $trade = Setting::getItem('trade');
        return $this->renderSuccess('', [
            'store' => $store,
            'product_vars' => $product_vars,
            // 开启物流中心
            'is_show_stock' => (isset($values['is_open_logistics']) && !empty($values['is_open_logistics'])) ? 0 : 1,
            // 商品详情
            'detail' => $product,
            // 关联商品
            'relation_product' => $relation_product,
            // 关联标签
            'relation_label' => $relation_label,
            'supply' => $supply,
            'supply_open' => isset($supply_vars['is_open']) ? $supply_vars['is_open'] : 0,
            // 购物车商品总数量
            'cart_total_num' => $user ? (new CartModel($user))->getProductNum() : 0,
            // 多规格商品sku信息
            'specData' => $specData,
            // 商品批次信息
            'batchData' => $productBatchData,
            // 购买记录
            'PurchaseRecords' => $list['data'],//购买记录
            'buyNum' => $list['num'],//购买总数
            // 微信公众号分享参数
            'share' => $this->getShareParams($url, $product['product_name'], $product['product_name'], '/pages/product/detail/detail', $product['image'][0]['file_path']),
        ]);
    }

    /**
     * 生成商品海报
     */
    public function poster($product_id, $source)
    {
        // 商品详情
        $detail = ProductModel::detail($product_id);
        $Qrcode = new ProductService($detail, $this->getUser(false), $source);
        return $this->renderSuccess('', [
            'qrcode' => $Qrcode->getImage(),
        ]);
    }

    /**
     * 获取我的品单商品
     * @Author   linpf
     * @DataTime 2020-11-04T16:48:12+0800
     * @return   [type]                   [description]
     */
    public function getMyProductList()
    {
        $user = $this->getUser();

        if($user){
            $user_id = $user->user_id;
            $order_goods_mod = new OrderProductMod();

            $data = $order_goods_mod->getAnchorGoods($user_id,$this->postData());

            return $this->renderSuccess('', compact('data'));
        }else{
            return $this->renderError('请先登录');
        }

    }

    /**
     * 获取直播海报
     * @Author   linpf
     * @DataTime 2020-11-05T14:20:32+0800
     * @return   [type]                   [description]
     */
    public function getLivePoster($ids = '')
    {
        if(empty($ids)){
            return $this->renderError('请选择商品');
        }

        $user = $this->getUser();
        $mod = new ProductModel();

        if($user){
            $res = $mod->makeLivePoster($ids,$user->user_id);
            return $res['status'] ? $this->renderSuccess($res['msg'], $res['data']) : $this->renderError($res['msg']);
        }else{
            return $this->renderError('请先登录');
        }

    }
    /**
     * 供应商管理 产品上下架
     */
    public function standup_and_down($product_id,$product_status)
    {
        $model = new ProductModel;
        $result = $model->standup_and_downProduct($product_id,$product_status);
        if (!$result) {
            return $this->renderError('操作失败');
        }
        return $this->renderSuccess('操作成功',compact('result'));
    }





    /**
     * 购买记录
     */
    public function PurchaseRecords($product_id){
        $model = new OrderModel();
//        halt($product_id);
        $list = $model->alias('o')
            ->join('order','order.order_id=o.order_id')
            ->join('user','user.user_id=order.user_id')
            ->where('o.product_id','=',$product_id)
            ->field('order.user_id,user.avatarUrl,o.total_num,user.nickname,o.order_product_id,o.create_time')
            ->order('o.create_time', 'desc')
            ->select();
//
//        $list1 = $model->alias('o')
//            ->join('order','order.order_id=o.order_id')
//            ->join('user','user.user_id=order.user_id')
//            ->where('o.product_id','=',$product_id)
//            ->group('user.user_id')
//            ->field('order.user_id,user.avatarUrl,o.total_num,user.nickname,o.order_product_id,o.create_time')
//            ->order('o.create_time', 'desc')
//            ->select();

        $num = count($list);
        $data =[
            'num' =>$num,
            'data' =>$list,
        ];
        return $data;
    }

}
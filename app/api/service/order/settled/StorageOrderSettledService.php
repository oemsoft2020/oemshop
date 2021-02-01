<?php

namespace app\api\service\order\settled;


use app\api\model\order\Order as OrderModel;
use app\api\model\order\OrderProduct;
use app\api\model\order\OrderAddress as OrderAddress;
use app\api\model\product\Product;
use app\api\model\product\ProductSku;
use app\common\enum\order\OrderPayTypeEnum;


/**
 * 普通订单结算服务类
 */
class StorageOrderSettledService extends OrderSettledService
{
    /**
     * 构造函数
     */
    public function __construct($user, $productList, $params)
    {

        $this->model = new OrderModel;
        $this->app_id = OrderModel::$app_id;
        $this->user = $user;
        $this->productList = $productList;
        $this->params = $params;
    }


    /**
     * 订单确认-结算台
     */
    public function settlement()
    {
        // 整理订单数据
        $this->orderData = $this->getOrderData();
        // 验证商品状态, 是否允许购买
        $this->validateProductList();
        // 设置订单商品会员折扣价
        $this->setOrderGrade();

        // 处理配送方式
        $this->setOrderExpress();


        // 计算订单最终金额
        $this->setOrderPayPrice();

        // 返回订单数据
        return array_merge([
            'product_list' => array_values($this->productList),   // 商品信息
            'order_total_num' => $this->params['number']      // 商品总数量
        ], $this->orderData);
    }


    /**
     * 验证订单商品的状态
     */
    public function validateProductList()
    {

        return true;
    }

    /**
     * 整理订单数据(结算台初始化)
     */
    private function getOrderData()
    {


        return [
            // 配送类型
            'delivery' => 10,
            // 默认地址
            'address' => $this->user['address_default'],
            // 是否存在收货地址
            'exist_address' => $this->user['address_id'] > 0,
            // 配送费用
            'express_price' => 0.00,
            // 支付方式
            'pay_type' => isset($this->params['pay_type']) ? $this->params['pay_type'] : OrderPayTypeEnum::WECHAT,

        ];
    }

    /**
     * 订单配送-快递配送
     */
    private function setOrderExpress()
    {

        // 订单总运费金额
        $this->orderData['express_price'] = 0;
        return true;
    }

    /**
     * 设置订单的实际支付金额(含配送费)
     */
    private function setOrderPayPrice()
    {
        // 订单金额(含优惠折扣)
        $this->orderData['order_price'] = $this->params['hoard_fee'];
        // 订单实付款金额(订单金额 + 运费)
        $this->orderData['order_pay_price'] = $this->params['hoard_fee'];
    }

    /**
     * 设置订单商品会员折扣价
     */
    private function setOrderGrade()
    {
        return true;
    }

    /**
     * 创建新订单
     */
    public function createOrder($order)
    {
        // 表单验证
        if (!$this->validateOrderForm($order, $this->params)) {
            return false;
        }
        // 创建新的订单
        $status = $this->model->transaction(function () use ($order) {
            // 创建订单事件
            return $this->createOrderEvent($order);
        });
        // 余额支付标记订单已支付
        if ($status && $order['pay_type'] == OrderPayTypeEnum::BALANCE) {
            $this->model->onPaymentByBalance($this->model['order_no']);
        }

        return $this->model['order_id'];
    }

    /**
     * 表单验证 (订单提交)
     */
    private function validateOrderForm(&$order)
    {

        if (empty($order['address'])) {
            $this->error = '请先选择收货地址';
            return false;
        }

        // 余额支付时，判断用户余额是否足够
        if ($order['pay_type'] == OrderPayTypeEnum::BALANCE) {
            if ($this->user['balance'] < $order['order_pay_price']) {
                $this->error = '用户余额不足，无法使用余额支付';
                return false;
            }
        }

        return true;
    }

    /**
     * 创建订单事件
     */
    private function createOrderEvent($order)
    {
        // 新增订单记录
        $status = $this->add($order);

        // 记录收货地址
        $this->saveOrderAddress($order['address'], $status);

        // 保存订单商品信息
        $this->saveOrderProduct($order, $status);


        return $status;
    }

    /**
     * 新增订单记录
     */
    private function add($order)
    {
        // 订单数据
        $data = [
            'user_id' => $this->user['user_id'],
            'order_no' => $this->model->orderNo(),
            'total_price' => 0,
            'order_price' => $order['order_price'],
            'pay_price' => $order['order_pay_price'],
            'delivery_type' => 10,
            'pay_type' => $order['pay_type'],
            'app_id' => $this->app_id,
            'storage_id' => $this->params['storage_id'],
        ];
        $supply_ids = array_column($order['product_list'], 'supply_id');
        $data['supply_ids'] = implode(',', array_unique($supply_ids));

        // 保存订单记录
        $this->model->save($data);
        $new_order_id = $this->model->order_id;
        return $new_order_id;
    }

    /**
     * 记录收货地址
     */
    private function saveOrderAddress($address, $order_id)
    {
        $model = new OrderAddress();
        if ($address['region_id'] == 0 && !empty($address['district'])) {
            $address['detail'] = $address['district'] . ' ' . $address['detail'];
        }
        return $model->save([
            'order_id' => $order_id,
            'user_id' => $this->user['user_id'],
            'app_id' => $this->app_id,
            'name' => $address['name'],
            'phone' => $address['phone'],
            'province_id' => $address['province_id'],
            'city_id' => $address['city_id'],
            'region_id' => $address['region_id'],
            'detail' => $address['detail'],
        ]);
    }

    /**
     * 保存订单商品信息
     */
    private function saveOrderProduct($order, $status)
    {
        // 订单商品列表
        $productList = [];

        foreach ($order['product_list'] as $product) {
            $productModel = new Product();
            $productDetail = $productModel->where('product_id', $product['product_id'])->find();
            $productskuModel = new ProductSKu();
            $productSku = $productskuModel->where('product_id', $product['product_id'])->find();
            $item = [
                'order_id' => $status,
                'user_id' => $this->user['user_id'],
                'app_id' => $this->app_id,
                'supply_id' => $product['supply_id'],
                'product_id' => $product['product_id'],
                'product_name' => $product['product_name'],
                'image_id' => $product['image_id'],
                'deduct_stock_type' => $productDetail['deduct_stock_type'],
                'spec_type' => $productDetail['spec_type'],
                'spec_sku_id' => $productSku['spec_sku_id'],
                'product_sku_id' => $product['product_sku_id'],
                'product_attr' => $product['product_attr'],
                'content' => $productDetail['content'],
                'product_no' => $productDetail['product_no'],
                'product_price' => 0,
                'line_price' => 0,
                'product_supply_price' => 0,
                'product_weight' => $productSku['product_weight'],
                'is_user_grade' => 0,
                'grade_ratio' => 0,
                'grade_product_price' => 0,
                'grade_total_money' => 0,
                'coupon_money' => 0,
                'points_money' => 0,
                'points_num' => 0,
                'points_bonus' => 0,
                'total_num' => $this->params['number'],
                'total_price' => 0,
                'total_pay_price' => $this->params['hoard_fee'],
                'is_ind_agent' => 0,
                'agent_money_type' => 10,
                'first_money' => 0,
                'second_money' => 0,
                'third_money' => 0,
            ];
            // 记录订单商品来源id
            $item['product_source_id'] = 0;
            // 记录订单商品sku来源id
            $item['sku_source_id'] =  0;
            // 记录拼团类的商品来源id
            $item['bill_source_id'] = 0;
            $productList[] = $item;
        }

        $model = new OrderProduct();
        return $model->saveAll($productList);
    }









}
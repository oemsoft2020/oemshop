<?php

namespace app\api\service\order\settled;

use app\api\model\order\Order as OrderModel;
use app\api\model\order\OrderProduct;
use app\api\model\order\OrderAddress as OrderAddress;
use app\api\model\plus\codebatch\CodeBatch;
use app\api\model\plus\coupon\UserCoupon as UserCouponModel;
use app\api\model\product\Product;
use app\common\enum\order\OrderPayTypeEnum;
use app\common\enum\order\OrderTypeEnum;
use app\common\model\settings\Setting as SettingModel;
use app\common\model\user\Grade as GradeModel;
use app\api\service\points\PointsDeductService;
use app\api\service\coupon\ProductDeductService;
use app\common\model\store\Store as StoreModel;
use app\api\service\user\UserService;
use app\common\enum\settings\DeliveryTypeEnum;
use app\common\library\helper;
use app\common\service\delivery\ExpressService;
use app\common\service\BaseService;
use app\common\service\product\factory\ProductFactory;
use app\api\model\plus\logistics\Stock as StockModel;

/**
 * 订单结算服务基类
 */
abstract class OrderSettledService extends BaseService
{
    /* $model OrderModel 订单模型 */
    public $model;

    // 当前应用id
    protected $app_id;

    protected $user;

    // 订单结算商品列表
    protected $productList = [];

    protected $params;
    /**
     * 订单结算的规则
     * 主商品默认规则
     */
    protected $settledRule = [
        'is_coupon' => true,        // 优惠券抵扣
        'is_use_points' => true,        // 是否使用积分抵扣
        'force_points' => false,     // 强制使用积分，积分兑换
        'is_user_grade' => true,     // 会员等级折扣
        'is_agent' => true,     // 商品是否开启分销,最终还是支付成功后判断分销活动是否开启
    ];

    /**
     * 订单结算数据
     */
    protected $orderData = [];
    /**
     * 订单来源
     */
    protected $orderSource;

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
        // 订单商品总数量
        $orderTotalNum = helper::getArrayColumnSum($this->productList, 'total_num');
        // 设置订单商品会员折扣价
        $this->setOrderGrade();
        // 设置订单商品总金额(不含优惠折扣)
        $this->setOrderTotalPrice();
        // 当前用户可用的优惠券列表
        $couponList = $this->getUserCouponList($this->orderData['order_total_price']);
        // 计算优惠券抵扣
        $this->setOrderCouponMoney($couponList, $this->params['coupon_id']);
        // 计算可用积分抵扣
        $this->setOrderPoints();
        // 计算订单商品的实际付款金额
        $this->setOrderProductPayPrice();
        // 设置默认配送方式
        if ($this->orderData['delivery'] != 30) {
            !$this->params['delivery'] && $this->params['delivery'] = current(SettingModel::getItem('store')['delivery_type']);
            // 处理配送方式
            if ($this->params['delivery'] == DeliveryTypeEnum::EXPRESS) {
                $this->setOrderExpress();
            } elseif ($this->params['delivery'] == DeliveryTypeEnum::EXTRACT) {
                $this->params['store_id'] > 0 && $this->orderData['extract_store'] = StoreModel::detail($this->params['store_id']);
            }
        }
        // 计算订单最终金额
        $this->setOrderPayPrice();
        // 计算订单积分赠送数量
        $this->setOrderPointsBonus();
        $vars = SettingModel::getItem('depot');
        // 返回订单数据
        return array_merge([
            'product_list' => array_values($this->productList),   // 商品信息
            'order_total_num' => $orderTotalNum,        // 商品总数量
            'coupon_list' => $couponList,
            'vars' => $vars
        ], $this->orderData, $this->settledRule);
    }

    /**
     * 验证订单商品的状态
     * @return bool
     */
    abstract function validateProductList();

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
     * 设置订单的商品总金额(不含优惠折扣)
     */
    private function setOrderTotalPrice()
    {
        // 订单商品的总金额(不含优惠券折扣)
        $this->orderData['order_total_price'] = helper::number2(helper::getArrayColumnSum($this->productList, 'total_price'));
    }

    /**
     * 当前用户可用的优惠券列表
     */
    private function getUserCouponList($orderTotalPrice)
    {
        // 是否开启优惠券折扣
        if (!$this->settledRule['is_coupon']) {
            return [];
        }
        return UserCouponModel::getUserCouponList($this->user['user_id'], $orderTotalPrice);
    }

    /**
     * 设置订单优惠券抵扣信息
     */
    private function setOrderCouponMoney($couponList, $couponId)
    {
        // 设置默认数据：订单信息
        helper::setDataAttribute($this->orderData, [
            'coupon_id' => 0,       // 用户优惠券id
            'coupon_money' => 0,    // 优惠券抵扣金额
        ], false);
        // 设置默认数据：订单商品列表
        helper::setDataAttribute($this->productList, [
            'coupon_money' => 0,    // 优惠券抵扣金额
        ], true);
        // 是否开启优惠券折扣
        if (!$this->settledRule['is_coupon']) {
            return false;
        }
        // 如果没有可用的优惠券，直接返回
        if ($couponId <= 0 || empty($couponList)) {
            return true;
        }
        // 获取优惠券信息
        $couponInfo = helper::getArrayItemByColumn($couponList, 'user_coupon_id', $couponId);
        if ($couponInfo == false) {
            $this->error = '未找到优惠券信息';
            return false;
        }
        // 计算订单商品优惠券抵扣金额
        $productListTemp = helper::getArrayColumns($this->productList, ['total_price']);
        $CouponMoney = new ProductDeductService;
        $completed = $CouponMoney->setProductCouponMoney($productListTemp, $couponInfo['reduced_price']);
        // 分配订单商品优惠券抵扣金额
        foreach ($this->productList as $key => &$product) {
            $product['coupon_money'] = $completed[$key]['coupon_money'] / 100;
        }

        // 记录订单优惠券信息
        $this->orderData['coupon_id'] = $couponId;
        $this->orderData['coupon_money'] = helper::number2($CouponMoney->getActualReducedMoney() / 100);
        return true;
    }

    /**
     * 计算订单商品的实际付款金额
     */
    private function setOrderProductPayPrice()
    {
        // 商品总价 - 优惠抵扣
        foreach ($this->productList as &$product) {

            if (!isset($product['nobuy']) && empty($product['nobuy'])) {
                // 减去优惠券抵扣金额
                $value = helper::bcsub($product['total_price'], $product['coupon_money']);
                // 减去积分抵扣金额
                if ($this->orderData['is_allow_points'] && $this->orderData['is_use_points']) {
                    $value = helper::bcsub($value, $product['points_money']);
                }
                $product['total_pay_price'] = helper::number2($value);
            }
        }
        unset($product);
        return true;
    }

    /**
     * 整理订单数据(结算台初始化)
     */
    private function getOrderData()
    {
        // 系统支持的配送方式 (后台设置)
        $deliveryType = SettingModel::getItem('store')['delivery_type'];

        //云仓系统设置及商品单独设置
        $storageVars = SettingModel::getItem('depot');
        if (isset($storageVars['is_open_storage']) && !empty($storageVars['is_open_storage'])) {
            foreach ($this->productList as &$product) {
                if (isset($product['delivery_type']) && !empty($product['delivery_type']) && $product['open_storage'] == 1) {
                    $deliveryType = explode(',', $product['delivery_type']);
                    $delivery = [];
                    foreach ($deliveryType as $v) {
                        $delivery[] = intval($v);
                    }
                    $deliveryType = $delivery;
                }
            }
        }

        // 积分设置
        $pointsSetting = SettingModel::getItem('points');
        if ($this->productList[0]['is_virtual'] == 1) {
            $delivery = 30;
        } else {
            $delivery = $this->params['delivery'] > 0 ? $this->params['delivery'] : $deliveryType[0];
        }

        return [
            // 配送类型
            'delivery' => $delivery,
            // 默认地址
            'address' => $this->user['address_default'],
            // 是否存在收货地址
            'exist_address' => $this->user['address_id'] > 0,
            // 配送费用
            'express_price' => 0.00,
            // 当前用户收货城市是否存在配送规则中
            'intra_region' => true,
            // 自提门店信息
            'extract_store' => [],
            // 是否允许使用积分抵扣
            'is_allow_points' => true,
            // 是否使用积分抵扣
            'is_use_points' => $this->params['is_use_points'],
            // 支付方式
            'pay_type' => isset($this->params['pay_type']) ? $this->params['pay_type'] : OrderPayTypeEnum::WECHAT,
            // 系统设置
            'setting' => [
                'delivery' => $deliveryType,     // 支持的配送方式
                'points_name' => $pointsSetting['points_name'],      // 积分名称
                'points_describe' => $pointsSetting['describe'],     // 积分说明
            ],
            // 记忆的自提联系方式
            'last_extract' => UserService::getLastExtract($this->user['user_id']),
            'deliverySetting' => $deliveryType,
        ];
    }

    /**
     * 订单配送-快递配送
     */
    private function setOrderExpress()
    {
        // 设置默认数据：配送费用
        helper::setDataAttribute($this->productList, [
            'express_price' => 0,
        ], true);
        // 当前用户收货城市id
        $cityId = $this->user['address_default'] ? $this->user['address_default']['city_id'] : null;

        // 初始化配送服务类
        $ExpressService = new ExpressService(
            $this->app_id,
            $cityId,
            $this->productList,
            OrderTypeEnum::MASTER
        );

        // 获取不支持当前城市配送的商品
        $notInRuleProduct = $ExpressService->getNotInRuleProduct();

        // 验证商品是否在配送范围
        $this->orderData['intra_region'] = ($notInRuleProduct === false);

        if (!$this->orderData['intra_region']) {
            $notInRuleProductName = $notInRuleProduct['product_name'];
            $this->error = "很抱歉，您的收货地址不在商品 [{$notInRuleProductName}] 的配送范围内";
            return false;
        } else {
            // 计算配送金额
            $ExpressService->setExpressPrice();
        }

        // 订单总运费金额
        $this->orderData['express_price'] = helper::number2($ExpressService->getTotalFreight());
        return true;
    }

    /**
     * 设置订单的实际支付金额(含配送费)
     */
    private function setOrderPayPrice()
    {
        // 订单金额(含优惠折扣)
        $this->orderData['order_price'] = helper::number2(helper::getArrayColumnSum($this->productList, 'total_pay_price'));
        // 订单实付款金额(订单金额 + 运费)
        $this->orderData['order_pay_price'] = helper::number2(helper::bcadd($this->orderData['order_price'], $this->orderData['express_price']));
    }

    /**
     * 表单验证 (订单提交)
     */
    private function validateOrderForm(&$order)
    {
        if ($order['delivery'] == DeliveryTypeEnum::EXPRESS) {
            if (empty($order['address'])) {
                $this->error = '请先选择收货地址';
                return false;
            }
        }
        if ($order['delivery'] == DeliveryTypeEnum::EXTRACT) {
            if (empty($order['extract_store'])) {
                $this->error = '请先选择自提门店';
                return false;
            }
            if (empty($this->params['linkman']) || empty($this->params['phone'])) {
                $this->error = '请填写联系人和电话';
                return false;
            }
        }
        // 余额支付时，判断用户余额是否足够
        if ($order['pay_type'] == OrderPayTypeEnum::BALANCE) {
            if ($this->user['balance'] < $order['order_pay_price']) {
                $this->error = '用户余额不足，无法使用余额支付';
                return false;
            }
        }
        //如果是积分兑换，判断用户积分是否足够
        if ($this->settledRule['force_points']) {
            if ($this->user['points'] < $order['points_num']) {
                $this->error = '用户积分不足，无法使用积分兑换';
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
        $status = $this->add($order, $this->params['remark']);

        if ($order['delivery'] == DeliveryTypeEnum::EXPRESS) {
            // 记录收货地址
            $this->saveOrderAddress($order['address'], $status);
        } elseif ($order['delivery'] == DeliveryTypeEnum::EXTRACT) {
            // 记录自提信息
            $this->saveOrderExtract($this->params['linkman'], $this->params['phone']);
        }
        isset($this->params['code_ids']) ? $code_ids = $this->params['code_ids'] : $code_ids = null;

        isset($this->params['time']) ? $time = $this->params['time'] : $time = null;
        // 保存订单商品信息
        $this->saveOrderProduct($order, $status, $code_ids, $time);
        // 更新商品库存 (针对下单减库存的商品)
        $values = SettingModel::getItem('logistics');
        if (!empty($values['is_open_logistics'])) {
            //物流中心减库存
            $stock_model = new StockModel();
            $stock_model->updateProductStock($order['product_list'], isset($order['logistics_id']) ? $order['logistics_id'] : 0);
        } else {
            ProductFactory::getFactory($this->orderSource['source'])->updateProductStock($order['product_list']);
        }

        // 设置优惠券使用状态
        UserCouponModel::setIsUse($this->params['coupon_id']);

        // 积分兑换扣除用户积分
        if ($order['force_points']) {
            $describe = "用户积分兑换消费：{$this->model['order_no']}";
            $this->user->setIncPoints(-$order['points_num'], $describe);
        } else {
            // 积分抵扣情况下扣除用户积分
            if ($order['is_allow_points'] && $order['is_use_points'] && $order['points_num'] > 0) {
                $describe = "用户消费：{$this->model['order_no']}";
                $this->user->setIncPoints(-$order['points_num'], $describe);
            }
        }
        return $status;
    }

    /**
     * 新增订单记录
     */
    private function add($order, $remark = '')
    {
        // 订单数据
        $data = [
            'user_id' => $this->user['user_id'],
            'order_no' => $this->model->orderNo(),
            'total_price' => $order['order_total_price'],
            'order_price' => $order['order_price'],
            'coupon_id' => $order['coupon_id'],
            'coupon_money' => $order['coupon_money'],
            'points_money' => $order['points_money'],
            'points_num' => $order['points_num'],
            'pay_price' => $order['order_pay_price'],
            'delivery_type' => $order['delivery'],
            'pay_type' => $order['pay_type'],
            'buyer_remark' => trim($remark),
            'order_source' => $this->orderSource['source'],
            'points_bonus' => isset($order['points_bonus']) ? $order['points_bonus'] : 0,
            'is_agent' => $this->settledRule['is_agent'] ? 1 : 0,
            'app_id' => $this->app_id,
        ];
        $supply_ids = array_column($order['product_list'], 'supply_id');
        $data['supply_ids'] = implode(',', array_unique($supply_ids));
        $data['logistics_id'] = isset($order['logistics_id']) ? $order['logistics_id'] : 0;
        if ($order['delivery'] == DeliveryTypeEnum::EXPRESS) {
            $data['express_price'] = $order['express_price'];
        } elseif ($order['delivery'] == DeliveryTypeEnum::EXTRACT) {
            $data['extract_store_id'] = $order['extract_store']['store_id'];
        }
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
     * 保存上门自提联系人
     */
    private function saveOrderExtract($linkman, $phone)
    {
        // 记忆上门自提联系人(缓存)，用于下次自动填写
        UserService::setLastExtract($this->model['user_id'], trim($linkman), trim($phone));
        // 保存上门自提联系人(数据库)
        return $this->model->extract()->save([
            'linkman' => trim($linkman),
            'phone' => trim($phone),
            'user_id' => $this->model['user_id'],
            'app_id' => $this->app_id,
        ]);
    }

    /**
     * 保存订单商品信息
     */
    private function saveOrderProduct($order, $status, $codeIds = null, $time = null)
    {
        // 订单商品列表
        $productList = [];
        $vars = SettingModel::getItem('depot');
        foreach ($order['product_list'] as $product) {
            $date = null;
            $gift_name = null;
            $gift_phone = null;
            $gift_status = 0;
            if (isset($this->params['date'])) $date = $this->params['date'];
            if (isset($this->params['gift_name'])) $gift_name = $this->params['gift_name'];
            if (isset($this->params['gift_phone']) && !empty($this->params['gift_phone'])) {
                $gift_phone = $this->params['gift_phone'];
                $gift_status = 1;
            }
            $productModel = new Product();
            $productDetail = $productModel->where('product_id', $product['product_id'])->find();
            if (isset($vars['is_open_storage']) && !empty($vars['is_open_storage']) && $productDetail['code_product_open'] == 1) {
                $codeBatchModel = new CodeBatch();
                $productNo = $codeBatchModel->getProductNoByCodeBatch($productDetail, $product['total_num'], $codeIds, $time);
            } else {
                $productNo = $product['product_sku']['product_no'];
            }
            $item = [
                'order_id' => $status,
                'user_id' => $this->user['user_id'],
                'app_id' => $this->app_id,
                'supply_id' => $product['supply_id'],
                'product_id' => $product['product_id'],
                'product_name' => $product['product_name'],
                'image_id' => $product['image'][0]['image_id'],
                'deduct_stock_type' => $product['deduct_stock_type'],
                'spec_type' => $product['spec_type'],
                'spec_sku_id' => $product['product_sku']['spec_sku_id'],
                'product_sku_id' => $product['product_sku']['product_sku_id'],
                'product_attr' => $product['product_sku']['product_attr'],
                'content' => $product['content'],
                'product_no' => $productNo,
                'product_price' => $product['product_sku']['product_price'],
                'line_price' => $product['product_sku']['line_price'],
                'product_supply_price' => $product['product_sku']['product_supply_price'],
                'product_weight' => $product['product_sku']['product_weight'],
                'is_user_grade' => (int)$product['is_user_grade'],
                'grade_ratio' => $product['grade_ratio'],
                'grade_product_price' => isset($product['grade_product_price']) ? $product['grade_product_price'] : 0,
                'grade_total_money' => $product['grade_total_money'],
                'coupon_money' => $product['coupon_money'],
                'points_money' => isset($product['points_money']) ? $product['points_money'] : 0,
                'points_num' => isset($product['points_num']) ? $product['points_num'] : 0,
                'points_bonus' => $product['points_bonus'],
                'total_num' => $product['total_num'],
                'total_price' => $product['total_price'],
                'total_pay_price' => $product['total_pay_price'],
                'is_ind_agent' => $product['is_ind_agent'],
                'agent_money_type' => $product['agent_money_type'],
                'first_money' => $product['first_money'],
                'second_money' => $product['second_money'],
                'date' => $date,
                'gift_name' => $gift_name,
                'gift_phone' => $gift_phone,
                'gift_status' => $gift_status,
                'independent_commission'=>$product['independent_commission']
            ];
            // 记录订单商品来源id
            $item['product_source_id'] = isset($product['product_source_id']) ? $product['product_source_id'] : 0;
            // 记录订单商品sku来源id
            $item['sku_source_id'] = isset($product['sku_source_id']) ? $product['sku_source_id'] : 0;
            // 记录拼团类的商品来源id
            $item['bill_source_id'] = isset($product['bill_source_id']) ? $product['bill_source_id'] : 0;
            $productList[] = $item;
        }

        $model = new OrderProduct();
        return $model->saveAll($productList);
    }

    /**
     * 计算订单可用积分抵扣
     */
    private function setOrderPoints()
    {
        $this->orderData['points_money'] = 0;
        // 积分抵扣总数量
        $this->orderData['points_num'] = 0;
        // 允许积分抵扣
        $this->orderData['is_allow_points'] = false;
        // 积分商城兑换
        if (isset($this->settledRule['force_points']) && $this->settledRule['force_points']) {
            // 积分抵扣金额，商品价格-兑换金额
            $this->orderData['points_money'] = $this->productList[0]['points_money'];
            // 积分抵扣总数量
            $this->orderData['points_num'] = $this->productList[0]['points_num'];
            // 允许积分抵扣
            $this->orderData['is_allow_points'] = true;
            if ($this->user['points'] < $this->productList[0]['points_num']) {
                $this->error = '积分不足，去多赚点积分吧！';
                return false;
            }
            return true;
        }

        // 积分设置
        $setting = SettingModel::getItem('points');
        // 条件：后台开启下单使用积分抵扣
        if (!$setting['is_shopping_discount'] || !$this->settledRule['is_use_points']) {
            return false;
        }
        // 条件：订单金额满足[?]元
        if (helper::bccomp($setting['discount']['full_order_price'], $this->orderData['order_total_price']) === 1) {
            return false;
        }
        // 计算订单商品最多可抵扣的积分数量
        $this->setOrderProductMaxPointsNum();
        // 订单最多可抵扣的积分总数量
        $maxPointsNumCount = helper::getArrayColumnSum($this->productList, 'max_points_num');
        // 实际可抵扣的积分数量
        $actualPointsNum = min($maxPointsNumCount, $this->user['points']);
        if ($actualPointsNum < 1) {
            $this->orderData['points_money'] = 0;
            // 积分抵扣总数量
            $this->orderData['points_num'] = 0;
            // 允许积分抵扣
            $this->orderData['is_allow_points'] = true;
            return false;
        }
        // 计算订单商品实际抵扣的积分数量和金额
        $ProductDeduct = new PointsDeductService($this->productList);
        $ProductDeduct->setProductPoints($maxPointsNumCount, $actualPointsNum);
        // 积分抵扣总金额
        $orderPointsMoney = helper::getArrayColumnSum($this->productList, 'points_money');
        $this->orderData['points_money'] = helper::number2($orderPointsMoney);
        // 积分抵扣总数量
        $this->orderData['points_num'] = $actualPointsNum;
        // 允许积分抵扣
        $this->orderData['is_allow_points'] = true;
        return true;
    }

    /**
     * 计算订单商品最多可抵扣的积分数量
     */
    private
    function setOrderProductMaxPointsNum()
    {
        // 积分设置
        $setting = SettingModel::getItem('points');
        foreach ($this->productList as &$product) {
            // 积分兑换
            if ($this->settledRule['force_points']) {
                $product['max_points_num'] = $product['points_num'];
            } else {
                // 商品不允许积分抵扣
                if (!$product['is_points_discount']) continue;
                // 积分抵扣
                if (isset($setting['discount']['max_money']) && $setting['discount']['max_money'] != '0') {
                    //固定抵扣金额
                    $deductionRatio = $setting['discount']['max_money'];
                    // 最多可抵扣的金额
                    $maxPointsMoney = $deductionRatio;
                } else {
                    // 积分抵扣比例
                    $deductionRatio = helper::bcdiv($setting['discount']['max_money_ratio'], 100);
                    // 最多可抵扣的金额
                    $maxPointsMoney = helper::bcmul($product['total_price'], $deductionRatio);
                }
                // 最多可抵扣的积分数量
                $product['max_points_num'] = helper::bcdiv($maxPointsMoney, $setting['discount']['discount_ratio'], 0);
            }
        }
        return true;
    }


    /**
     * 计算订单积分赠送数量
     */
    private
    function setOrderPointsBonus()
    {
        // 初始化商品积分赠送数量
        foreach ($this->productList as &$product) {
            $product['points_bonus'] = 0;
        }
        // 积分设置
        $setting = SettingModel::getItem('points');
        // 条件：后台开启开启购物送积分
        if (!$setting['is_shopping_gift']) {
            return false;
        }
        // 设置商品积分赠送数量
        foreach ($this->productList as &$product) {
            // 积分赠送比例
            $ratio = $setting['gift_ratio'] / 100;
            // 计算抵扣积分数量
            $product['points_bonus'] = !$product['is_points_gift'] ? 0 : helper::bcmul($product['total_pay_price'], $ratio, 0);
        }
        //  订单积分赠送数量
        $this->orderData['points_bonus'] = helper::getArrayColumnSum($this->productList, 'points_bonus');
        return true;
    }

    /**
     * 设置订单商品会员折扣价
     */
    private function setOrderGrade()
    {
        // 设置默认数据
        helper::setDataAttribute($this->productList, [
            // 标记参与会员折扣
            'is_user_grade' => false,
            // 会员等级抵扣的金额
            'grade_ratio' => 0,
            // 会员折扣的商品单价
            'grade_goods_price' => 0.00,
            // 会员折扣的总额差
            'grade_total_money' => 0.00,
        ], true);

        $data = [];
        // 是否开启会员等级折扣
        if (!$this->settledRule['is_user_grade']) {
            return false;
        }
        // 计算抵扣金额
        foreach ($this->productList as &$product) {
            // 判断商品是否参与会员折扣
            if (!$product['is_enable_grade']) {
                continue;
            }
            $trade = SettingModel::getItem('trade');
            $price_mode = isset($trade['price_mode']) ? $trade['price_mode'] : 0;
            // 商品单独设置了会员折扣
            if ($product['is_alone_grade'] && isset($product['alone_grade_equity'][$this->user['grade_id']])) {
                if ($product['is_alone_grade'] == 2) {
                    // 固定金额
                    $product_price = $product['alone_grade_equity'][$this->user['grade_id']];
                } else {
                    // 折扣比例
                    $discountRatio = helper::bcdiv($product['alone_grade_equity'][$this->user['grade_id']], 100);
                }

            } else {
                // 折扣比例
                $discountRatio = helper::bcdiv($this->user['grade']['equity'], 100);
            }

            if($discountRatio < 1||$product['is_alone_grade'] == 2 && isset($product_price)){

           
                if ($product['is_alone_grade'] == 2 && isset($product_price)) {

                    // 会员折扣后的商品总金额
                    $gradeTotalPrice = $product_price * $product['total_num'];
                    $data = [
                        'is_user_grade' => true,
                        'grade_ratio' => $product_price / $product['product_price'],
                        'grade_product_price' => $product_price,
                        'grade_total_money' => abs(helper::number2(helper::bcsub($product['total_price'], $gradeTotalPrice))),
                        'total_price' => $gradeTotalPrice,
                    ];
                }else if($discountRatio < 1){
                    // 会员折扣后的商品总金额
                    $gradeTotalPrice = max(0.01, helper::bcmul($product['total_price'], $discountRatio));
                    $data = [
                        'is_user_grade' => true,
                        'grade_ratio' => $discountRatio,
                        'grade_product_price' => helper::number2(helper::bcmul($product['product_price'], $discountRatio), true),
                        'grade_total_money' => helper::number2(helper::bcsub($product['total_price'], $gradeTotalPrice)),
                        'total_price' => $gradeTotalPrice,
                    ];
                }

                 // 渠道会员走渠道价，非渠道走会员价
                 if (!empty($price_mode)) {
                    $gradeList = GradeModel::getAgentList();
                    // 渠道价格
                    if (!empty($gradeList)) {
                        $data = $this->getGredePrice($gradeList, $this->user['grade'], $product);
                    }
                    
                }

                if(!empty($data)){
                    helper::setDataAttribute($product, $data, false);
                }
            }
        }
        return true;
    }

    /**
     * 获取会员自动折扣价
     */
    private function getGredePrice($gradeList, $grade, $product)
    {
        $count = count($gradeList) - 1;
        $product_sku = $product['product_sku'];
        if ($count == 0) {
            $step_price = 0;
        } else {
            $step_price = helper::bcdiv(helper::number2($product_sku['agent_max_price'] - $product_sku['agent_min_price'], true, 0), $count);
        }

        $product_price = $product_sku['product_price'];
        $agent_max_price = $product_sku['agent_max_price'] > 0 ? $product_sku['agent_max_price'] : $product_sku['product_price'];
        foreach ($gradeList as $k => $v) {
            if ($v['level'] == $grade['level']) {
                $product_price = helper::number2($agent_max_price - $k * $step_price, true);
                if ($step_price > 0 && $k == $count) {
                    $product_price = $product_sku['agent_min_price'] > 0 ? $product_sku['agent_min_price'] : $product_price;
                }
                break;
            }
        }
        $gradeTotalPrice = max(0.01, helper::bcmul($product_price, $product['total_num']));

        $data = [
            'is_user_grade' => true,
            'grade_ratio' => helper::number2(helper::bcdiv($product_price, $product_sku['product_price']), true),
            'grade_product_price' => helper::number2($product_price, true),
            'grade_total_money' => helper::number2(helper::bcsub($product['total_price'], $gradeTotalPrice)),
            'total_price' => $gradeTotalPrice,
        ];
        return $data;

    }
}
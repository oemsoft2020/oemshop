<?php

namespace app\api\model\order;

use app\api\model\plus\codebatch\CodeBatch;
use app\api\model\product\Product as ProductModel;
use app\api\service\order\paysuccess\type\MasterPaySuccessService;
use app\api\service\order\PaymentService;
use app\api\model\settings\Setting as SettingModel;
use app\common\enum\order\OrderPayTypeEnum;
use app\common\enum\order\OrderSourceEnum;
use app\common\enum\order\OrderTypeEnum;
use app\common\enum\order\OrderPayStatusEnum;
use app\common\enum\order\OrderStatusEnum;
use app\common\exception\BaseException;
use app\common\model\plus\codebatch\Code;
use app\common\model\settings\Setting;
use app\common\service\order\OrderCompleteService;
use app\common\enum\settings\DeliveryTypeEnum;
use app\common\library\helper;
use app\common\model\order\Order as OrderModel;
use app\api\service\order\checkpay\CheckPayFactory;
use app\common\service\product\factory\ProductFactory;
use app\common\model\plus\coupon\UserCoupon as UserCouponModel;

/**
 * 普通订单模型
 */
class Order extends OrderModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'app_id',
        'update_time'
    ];

    /**
     * 订单支付事件
     */
    public function onPay($payType = OrderPayTypeEnum::WECHAT)
    {
        // 判断订单状态
        $checkPay = CheckPayFactory::getFactory($this['order_source']);

        if (!$checkPay->checkOrderStatus($this)) {
            $this->error = $checkPay->getError();
            return false;
        }

        // 余额支付
        if ($payType == OrderPayTypeEnum::BALANCE) {
            return $this->onPaymentByBalance($this['order_no']);
        }
        return true;
    }

    /**
     * 用户中心订单列表
     */
    public function getList($user_id, $type = 'all', $params)
    {
        // 筛选条件
        $filter = [];
        // 订单数据类型
        switch ($type) {
            case 'all':
                break;
            case 'payment';
                $filter['pay_status'] = OrderPayStatusEnum::PENDING;
                $filter['order_status'] = 10;
                break;
            case 'delivery';
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['delivery_status'] = 10;
                $filter['order_status'] = 10;
                break;
            case 'received';
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['delivery_status'] = 20;
                $filter['receipt_status'] = 10;
                $filter['order_status'] = 10;
                break;
            case 'comment';
                $filter['is_comment'] = 0;
                $filter['order_status'] = 30;
                break;
        }
        return $this->with(['product.image','orderRefund'])
            ->where('user_id', '=', $user_id)
            ->where($filter)
            ->where('is_delete', '=', 0)
            ->order(['create_time' => 'desc'])
            ->paginate($params, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 确认收货
     */
    public function receipt()
    {
        // 验证订单是否合法
        // 条件1: 订单必须已发货
        // 条件2: 订单必须未收货
        if ($this['delivery_status']['value'] != 20 || $this['receipt_status']['value'] != 10) {
            $this->error = '该订单不合法';
            return false;
        }
        return $this->transaction(function () {
            // 更新订单状态
            $status = $this->save([
                'receipt_status' => 20,
                'receipt_time' => time(),
                'order_status' => 30
            ]);
            // 执行订单完成后的操作
            $OrderCompleteService = new OrderCompleteService(OrderTypeEnum::MASTER);
            $OrderCompleteService->complete([$this], static::$app_id);
            return $status;
        });
    }

    /**
     * 立即购买：获取订单商品列表
     */
    public static function getOrderProductListByNow($params, $time = null)
    {
        // 商品详情
        $product = ProductModel::detail($params['product_id']);
        // 商品sku信息
        $product['product_sku'] = ProductModel::getProductSku($product, $params['product_sku_id']);
        // 商品列表
        $productList = [$product->hidden(['category', 'content', 'image', 'sku'])];
        $vars = Setting::getItem('depot');
        foreach ($productList as &$item) {
            // 商品单价
            $item['product_price'] = $item['product_sku']['product_price'];
            // 商品购买数量
            $item['total_num'] = $params['product_num'];
            $item['spec_sku_id'] = $item['product_sku']['spec_sku_id'];
            // 商品购买总金额
            $item['total_price'] = helper::bcmul($item['product_price'], $params['product_num']);
            $item['code_list'] = [];
            if (isset($vars['is_open_storage']) && !empty($vars['is_open_storage']) && $item['code_product_open'] == 1) {
                $codeBatchModel = new CodeBatch();
                if (isset($params['code_ids']) && !empty($params['code_ids']) && $params['code_ids'] != 0) {
                    $item['code_list'] = $codeBatchModel->getCodeListByCodes($params['code_ids']);
                } else {
                    $item['code_list'] = $codeBatchModel->getCodeList($item['product_id'], $item['type'], $item['total_num'], $time);
                }
            }
        }
        return $productList;
    }

    /**
     * 获取订单总数
     */
    public function getCount($user, $type = 'all')
    {
        if ($user === false) {
            return false;
        }
        // 筛选条件
        $filter = [];
        // 订单数据类型
        switch ($type) {
            case 'all':
                break;
            case 'payment';
                $filter['pay_status'] = OrderPayStatusEnum::PENDING;
                break;
            case 'received';
                $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                $filter['delivery_status'] = 20;
                $filter['receipt_status'] = 10;
                break;
            case 'comment';
                $filter['order_status'] = 30;
                $filter['is_comment'] = 0;
                break;
            case 'supply';
                $filter = 'FIND_IN_SET(' . $user['supply_id'] . ',supply_ids)';
                break;
        }
        //以下统计的是该供应商的订单数
        if($type=='supply'){
            return $this->where('order_status', '<>', 20)
                ->where($filter)
                ->where('is_delete', '=', 0)
                ->count();
        }else{
            // 以下统计的是登录账号user_id 的订单总数
            return $this->where('user_id', '=', $user['user_id'])
                ->where('order_status', '<>', 20)
                ->where($filter)
                ->where('is_delete', '=', 0)
                ->count();
        }
    }

    /**
     * 取消订单
     */
    public function cancel($user)
    {
        if ($this['delivery_status']['value'] == 20) {
            $this->error = '已发货订单不可取消';
            return false;
        }
        //进行中的拼团订单不能取消
        if ($this['order_source'] == OrderSourceEnum::ASSEMBLE) {
            if ($this['assemble_status'] == 10) {
                $this->error = '订单正在拼团，到期后如果订单未拼团成功将自动退款';
                return false;
            }
        }
        // 订单取消事件
        return $this->transaction(function () use ($user) {
            // 订单是否已支付
            $isPay = $this['pay_status']['value'] == OrderPayStatusEnum::SUCCESS;
            // 未付款的订单
            if ($isPay == false) {
                //主商品退回库存
                ProductFactory::getFactory($this['order_source'])->backProductStock($this['product'], $isPay);
                // 回退用户优惠券
                $this['coupon_id'] > 0 && UserCouponModel::setIsUse($this['coupon_id'], false);
                // 回退用户积分
                $describe = "订单取消：{$this['order_no']}";
                $this['points_num'] > 0 && $user->setIncPoints($this['points_num'], $describe);
                $vars = SettingModel::getItem('depot');
                if (isset($vars['is_open_storage']) && !empty($vars['is_open_storage'] && $this['delivery_type']['value'] == 40)) {
                    $codeModel = new Code();
                    $codeModel->backCodeNumber($this['product']);
                }

            }
            // 更新订单状态
            return $this->save(['order_status' => $isPay ? OrderStatusEnum::APPLY_CANCEL : OrderStatusEnum::CANCELLED]);
        });
    }

    /**
     * 订单详情
     */
    public static function getUserOrderDetail($order_id, $user_id)
    {
        $model = new static();
        $order = $model->where(['order_id' => $order_id, 'user_id' => $user_id])->with(['product' => ['image', 'refund'], 'address', 'express', 'extractStore'])->find();
        if (empty($order)) {
            throw new BaseException(['msg' => '订单不存在']);
        }
        return $order;
    }

    /**
     * 余额支付标记订单已支付
     */
    public function onPaymentByBalance($orderNo)
    {
        // 获取订单详情
        $PaySuccess = new MasterPaySuccessService($orderNo);
        // 发起余额支付
        $status = $PaySuccess->onPaySuccess(OrderPayTypeEnum::BALANCE);
        if (!$status) {
            $this->error = $PaySuccess->getError();
        }
        return $status;
    }

    /**
     * 构建微信支付请求
     */
    protected static function onPaymentByWechat($user, $order, $pay_source)
    {
        return PaymentService::wechat(
            $user,
            $order['order_id'],
            $order['order_no'],
            $order['pay_price'],
            OrderTypeEnum::MASTER,
            $pay_source
        );
    }

    /**
     * 待支付订单详情
     */
    public static function getPayDetail($orderNo)
    {
        $model = new static();
        return $model->where(['order_no' => $orderNo, 'pay_status' => 10, 'is_delete' => 0])->with(['product', 'user'])->find();
    }

    /**
     * 构建支付请求的参数
     */
    public static function onOrderPayment($user, $order, $payType, $pay_source)
    {
        //如果来源是h5,首次不处理，payH5再处理
        if ($pay_source == 'h5') {
            return [];
        }
        if ($payType == OrderPayTypeEnum::WECHAT) {
            return self::onPaymentByWechat($user, $order, $pay_source);
        }
        return [];
    }

    /**
     * 判断当前订单是否允许核销
     */
    public function checkExtractOrder(&$order)
    {
        if (
            $order['pay_status']['value'] == OrderPayStatusEnum::SUCCESS
            && $order['delivery_type']['value'] == DeliveryTypeEnum::EXTRACT
            && $order['delivery_status']['value'] == 10
        ) {
            return true;
        }
        $this->setError('该订单不能被核销');
        return false;
    }

    /**
     * 当前订单是否允许申请售后
     */
    public function isAllowRefund()
    {
        // 必须是已发货的订单
        if ($this['delivery_status']['value'] != 20) {
            return false;
        }
        // 允许申请售后期限(天)
        $refundDays = SettingModel::getItem('trade')['order']['refund_days'];
        // 不允许售后
        if ($refundDays == 0) {
            return false;
        }
        // 当前时间超出允许申请售后期限
        if (
            $this['receipt_status'] == 20
            && time() > ($this['receipt_time'] + ((int)$refundDays * 86400))
        ) {
            return false;
        }
        return true;
    }

    /**
     * 获取活动订单
     * 已付款，未取消
     */
    public static function getPlusOrderNum($user_id, $product_id)
    {
        $model = new static();
        return $model->alias('order')->where('order.user_id', '=', $user_id)
            ->join('order_product', 'order_product.order_id = order.order_id', 'left')
            ->where('order_product.product_source_id', '=', $product_id)
            ->where('order.pay_status', '=', 20)
            ->where('order.order_status', '<>', 20)
            ->count();
    }

    /**
     * 设置错误信息
     */
    protected function setError($error)
    {
        empty($this->error) && $this->error = $error;
    }

    /**
     * 是否存在错误
     */
    public function hasError()
    {
        return !empty($this->error);
    }

    /**
     * 获取指定商品的购买数量
     */
    public function getBuyedProductNum($user_id, $product_id)
    {
        $model = new static();
        return $model->alias('order')->where('order.user_id', '=', $user_id)
            ->join('order_product', 'order_product.order_id = order.order_id', 'left')
            ->where('order_product.product_id', '=', $product_id)
            ->where('order.pay_status', '=', 20)
            ->where('order.order_status', '<>', 20)
            ->sum('order_product.total_num');
    }

    /**
     * 获取指定商品当天的购买数量
     */
    public function getTodayBuyProductNum($user_id, $product_id)
    {
        $model = new static();
        return $model->alias('order')->where('order.user_id', '=', $user_id)
            ->join('order_product', 'order_product.order_id = order.order_id', 'left')
            ->where('order_product.product_id', '=', $product_id)
            ->where('order.pay_status', '=', 20)
            ->where('order.order_status', '<>', 20)
            ->whereDay('order_product.create_time',date('Y-m-d',time()))
            ->sum('order_product.total_num');
    }

    /**
     * 获取总销售额
     */
    public function getOrderTotalPrice($supply)
    {
        $model = $this;
        if (!empty($supply)) {
            $model = $model->where('FIND_IN_SET(' . $supply['supply_id'] . ',supply_ids)');
        }
        return $model->where('pay_status', '=', 20)
            ->where('order_status', '<>', 20)
            ->where('is_delete', '=', 0)
            ->sum('pay_price');
    }


    /**
     * 供应商中心订单列表
     */
    public function getsupplyOrder($supply_id, $type = 'all', $params)
    {

        if(isset($supply_id) && $supply_id>0){
            // 筛选条件
            $filter = [];
            // 订单数据类型
            switch ($type) {
                // 全部
                case 'all':
                    break;
                //待付款
                case 'payment';
                    $filter['pay_status'] = OrderPayStatusEnum::PENDING;
                    $filter['order_status'] = 10;
                    break;
                //待发货
                case 'delivery';
                    $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                    $filter['delivery_status'] = 10;
                    $filter['order_status'] = 10;
                    break;
                //待取消
                case 'cancel';
                    $filter['order_status'] = 21;
                    break;
                //待收货
                case 'received';
                    $filter['pay_status'] = OrderPayStatusEnum::SUCCESS;
                    $filter['delivery_status'] = 20;
                    $filter['receipt_status'] = 10;
                    $filter['order_status'] = 10;
                    break;
                //已完成
                case 'finished';
                    $filter['order_status'] = 30;
                    break;
            }
            return $this->with(['product.image'])
                ->where('FIND_IN_SET(:supply_id,supply_ids)', ['supply_id' => $supply_id])
                ->where($filter)
                ->where('is_delete', '=', 0)
                ->order(['create_time' => 'desc'])
                ->paginate($params, false, [
                    'query' => \request()->request()
                ]);
        }
    }

}
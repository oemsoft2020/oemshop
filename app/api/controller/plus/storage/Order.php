<?php

namespace app\api\controller\plus\storage;

use app\api\model\order\Order as OrderModel;
use app\api\model\plus\storage\Storage as StorageModel;
use app\api\service\order\settled\StorageOrderSettledService;
use app\api\controller\Controller;



/**
 * 普通订单
 */
class Order extends Controller
{
    /**
     * 订单确认-立即购买
     */
    public function buy()
    {

        // 立即购买：获取订单商品列表
        $params = $this->request->param();
        isset($params['time']) ? $time = $params['time'] : $time = null;

        $productList = StorageModel::getOrderStorageByNow($params, $time);

        $user = $this->getUser();

        // 实例化订单service
        $orderService = new StorageOrderSettledService($user, $productList, $params);
        // 获取订单信息
        $orderInfo = $orderService->settlement();
        if ($orderService->hasError()) {
            return $this->renderError($orderService->getError());
        }
        // 创建订单
        $order_id = $orderService->createOrder($orderInfo);
        if (!$order_id) {
            return $this->renderError($orderService->getError() ?: '订单创建失败');
        }
        // 构建支付请求
        $payment = OrderModel::onOrderPayment($user, $orderService->model, $params['pay_type'], $params['pay_source']);

        // 返回结算信息
        return $this->renderSuccess(['success' => '支付成功', 'error' => '订单未支付'], [
            'order_id' => $order_id,   // 订单id
            'pay_type' => $params['pay_type'],  // 支付方式
            'payment' => $payment,               // 微信支付参数
            'storage_id' => $params['storage_id'],               // 酒窖id
            'number'    => $params['number']
        ]);
    }




}
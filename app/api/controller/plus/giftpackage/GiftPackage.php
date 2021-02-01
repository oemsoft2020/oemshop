<?php

namespace app\api\controller\plus\giftpackage;


use app\api\controller\Controller;
use app\api\model\plus\giftpackage\GiftPackage as GiftPackageModel;
use app\api\model\plus\giftpackage\Order as OrderModel;

/**
 * 礼包购控制器
 */
class GiftPackage extends Controller
{
    /**
     * 获取数据
     * @param null $id
     */
    public function getGiftPackage($id)
    {
        $model = new GiftPackageModel();
        $data = $model->getGiftPackage($id);
        return $this->renderSuccess('', compact('data'));
    }

    /**
     * @param $id
     * 礼包购
     * @return \think\response\Json
     */
    public function buy($id)
    {
        // 用户信息
        $user = $this->getUser();
        $params = $this->request->param();
        // 生成礼品订单
        $model = new OrderModel;
        // 创建订单
        if (!$model->createOrder($user, $id, $params)) {
            return $this->renderError($model->getError() ?: '订单创建失败');
        }
        // 构建支付请求
        $payment = OrderModel::onOrderPayment($user, $model, $params['pay_type'], $params['pay_source']);
        // 返回结算信息
        return $this->renderSuccess(['success' => '支付成功', 'error' => '订单未支付'], [
            'order_id' => $model['order_id'],   // 订单id
            'pay_type' => $params['pay_type'],  // 支付方式,仅支持微信
            'payment' => $payment,               // 微信支付参数
        ]);
    }
}
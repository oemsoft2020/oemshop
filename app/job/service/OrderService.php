<?php

namespace app\job\service;

use app\api\model\settings\Setting as SettingModel;
use app\common\model\plus\codebatch\Code;
use app\common\service\product\factory\ProductFactory;
use app\job\model\order\Order as OrderModel;
use app\common\model\plus\coupon\UserCoupon as UserCouponModel;
use app\common\library\helper;
use app\common\enum\order\OrderSourceEnum;
use app\common\model\plus\agent\PlanSettle as AgentPlanSettle;
class OrderService
{
    // 模型
    private $model;

    // 自动关闭的订单id集
    private $closeOrderIds = [];

    /**
     * 构造方法
     * Order constructor.
     */
    public function __construct()
    {
        $this->model = new OrderModel;
    }

    /**
     * 未支付订单自动关闭
     */
    public function close($deadlineTime, $order_source = OrderSourceEnum::MASTER, $where = [])
    {
        // 查询截止时间未支付的订单
        $list = $this->model->getCloseList($deadlineTime, $order_source, ['product', 'user']);
        $this->closeOrderIds = helper::getArrayColumn($list, 'order_id');
        // 取消订单事件
        $planSettleModel = new AgentPlanSettle();
        if (!empty($this->closeOrderIds)) {
            foreach ($list as &$order) {
                // 回退商品库存
                ProductFactory::getFactory($order['order_source'])->backProductStock($order['product'], false);
                // 回退用户优惠券
                $order['coupon_id'] > 0 && UserCouponModel::setIsUse($order['coupon_id'], false);
                // 回退用户积分
                $describe = "订单取消：{$order['order_no']}";
                $order['points_num'] > 0 && $order->user->setIncPoints($order['points_num'], $describe);
                $vars = SettingModel::getItem('depot');
                if (isset($vars['is_open_storage']) && !empty($vars['is_open_storage']) && $order['delivery_type']['value'] == 40) {
                    //取消订单会报错需要修改
                    $codeModel = new Code();
                    $codeModel->backCodeNumber($order['product']);
                }
                //关闭订单则关闭待结算佣金
                $planSettleModel->updateStatus($order['order_id']);
            }
            // 批量更新订单状态为已取消
            return $this->model->onBatchUpdate($this->closeOrderIds, ['order_status' => 20]);
        }
        return true;
    }

    /**
     * 获取自动关闭的订单id集
     */
    public function getCloseOrderIds()
    {
        return $this->closeOrderIds;
    }

}
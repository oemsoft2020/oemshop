<?php

namespace app\shop\model\order;

use app\common\model\order\OrderRefund as OrderRefundModel;
use app\common\service\order\OrderCompleteService;
use app\shop\model\user\User as UserModel;
use app\common\service\order\OrderRefundService;
use app\common\service\message\MessageService;
use app\common\enum\order\OrderTypeEnum;

/**
 * 售后管理模型
 */
class OrderRefund extends OrderRefundModel
{
    /**
     * 获取售后单列表
     */
    public function getList($query = [])
    {

        $model = $this;
        // 查询条件：订单号
        if (isset($query['order_no']) && !empty($query['order_no'])) {
            $model = $model->where('order.order_no', 'like', "%{$query['order_no']}%");
        }
        // 查询条件：起始日期
        if (isset($query['create_time']) && !empty($query['create_time'])) {
            $sta_time = array_shift($query['create_time']);
            $end_time = array_pop($query['create_time']);
            $model = $model->whereBetweenTime('m.create_time', $sta_time, $end_time);
        }
        // 售后类型
        if (isset($query['type']) && $query['type'] > 0) {
            $model = $model->where('m.type', '=', $query['type']);
        }

        // 售后单状态(选项卡)
        if (isset($query['status']) && $query['status'] >= 0) {
            $model = $model->where('m.status', '=', $query['status']);
        }

        // 获取列表数据
        return $model->alias('m')
            ->field('m.*, order.order_no')
            ->with(['orderproduct.image', 'orderMaster', 'user'])
            ->join('order', 'order.order_id = m.order_id')
            ->order(['m.create_time' => 'desc'])
            ->paginate($query, false, [
                'query' => \request()->request()
            ]);
    }

    public function groupCount($query){
        $model = $this;
        // 查询条件：订单号
        if (isset($query['order_no']) && !empty($query['order_no'])) {
            $model = $model->where('order.order_no', 'like', "%{$query['order_no']}%");
        }
        // 查询条件：起始日期
        if (isset($query['create_time']) && !empty($query['create_time'])) {
            $sta_time = array_shift($query['create_time']);
            $end_time = array_pop($query['create_time']);
            $model = $model->whereBetweenTime('m.create_time', $sta_time, $end_time);
        }
        // 售后类型
        if (isset($query['type']) && $query['type'] > 0) {
            $model = $model->where('m.type', '=', $query['type']);
        }

        // 获取列表数据
        return $model->alias('m')
            ->field('m.status,COUNT(*) as total')
            ->join('order', 'order.order_id = m.order_id')
            ->group('m.status')->select()->toArray();
    }

    /**
     * 商家审核
     */
    public function audit($data)
    {
        if ($data['is_agree'] == 20 && empty($data['refuse_desc'])) {
            $this->error = '请输入拒绝原因';
            return false;
        }
        if ($data['is_agree'] == 10 && $this['type']['value'] != 30 &&  empty($data['address_id'])) {
            $this->error = '请选择退货地址';
            return false;
        }
        $this->startTrans();
        try {
            // 拒绝申请, 标记售后单状态为已拒绝
            $data['is_agree'] == 20 && $data['status'] = 10;
            // 同意换货申请, 标记售后单状态为已完成
            $data['is_agree'] == 10 && $this['type']['value'] == 20 && $data['status'] = 20;
            // 订单详情
            $order = Order::detail($this['order_id']);
            if ($data['refund_money'] > $order['pay_price']) {
                $this->error = '退款金额不能大于商品实付款金额';
                return false;
            }

            // 更新退款单状态
            $this->save($data);
            // 同意售后申请, 记录退货地址
            if ($data['is_agree'] == 10&& $this['type']['value'] != 30 ) {
                $model = new OrderRefundAddress();
                $model->add($this['order_refund_id'], $data['address_id']);
            }
            // 发送模板消息
            (new MessageService)->refund(self::detail($this['order_refund_id']), $order['order_no'], 'audit');
             // 如果是仅退款
             if ($data['is_agree'] == 10 && $this['type']['value'] == 30) {
                if ($data['refund_money'] > $order['pay_price']) {
                    $this->error = '退款金额不能大于商品实付款金额';
                    return false;
                }
                $this->refundMoney($order, $data);
            }

            // 事务提交
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 确认收货并退款
     */
    public function receipt($data)
    {
        // 订单详情
        $order = Order::detail($this['order_id']);

        if ($data['refund_money'] > $order['pay_price']) {
            $this->error = '退款金额不能大于商品实付款金额';
            return false;
        }
        $this->transaction(function () use ($order, $data) {
            // 更新售后单状态
            $this->refundMoney($order, $data);
        });

        //单商品订单售后完成更新订单状态为已完成
        if(count($order['product'])==1){
            // 验证订单是否合法
            // 条件1: 订单必须已发货
            // 条件2: 订单必须未收货
            if ($order['delivery_status']['value'] != 20 || $order['receipt_status']['value'] != 10) {
                $this->error = '该订单不合法';
                return false;
            }
                $this->transaction(function () use($order) {
                // 更新订单状态
                     $order->save([
                    'receipt_status' => 20,
                    'receipt_time' => time(),
                    'order_status' => 30
                ]);
                // 执行订单完成后的操作
                $OrderCompleteService = new OrderCompleteService(OrderTypeEnum::MASTER);
                $OrderCompleteService->complete([$this], static::$app_id);

            });
        }
        //
        return true;
    }

    private function refundMoney($order, $data)
    {
        // 更新售后单状态
        $this->save([
            'refund_money' => $data['refund_money'],
            'is_receipt' => 1,
            'status' => 20
        ]);
        // 消减用户的实际消费金额
        // 条件：判断订单是否已结算
        if ($order['is_settled'] == true) {
            (new UserModel)->setDecUserExpend($order['user_id'], $data['refund_money']);
        }
        // 执行原路退款
        (new OrderRefundService)->execute($order, $data['refund_money']);
        // 执行原路退业绩
        (new UserModel)->setDecAchievement($order['user_id'], $order);
        // 发送模板消息
        (new MessageService)->refund(self::detail($this['order_refund_id']), $order['order_no'], 'receipt');
    }



    /**
     * 统计售后订单
     */
    public function getRefundOrderTotal()
    {
        $filter['is_agree'] = 0;
        return $this->where($filter)->count();
    }


    /**
     * 获取退款订单总数 (可指定某天)
     * 已同意的退款
     */
    public function getOrderRefundData($startDate = null, $endDate = null, $type)
    {
        $model = $this;
        $model = $model->where('create_time', '>=', strtotime($startDate));
        if(is_null($endDate)){
            $model = $model->where('create_time', '<', strtotime($startDate) + 86400);
        }else{
            $model = $model->where('create_time', '<', strtotime($endDate) + 86400);
        }

        $model = $model->where('is_agree', '=', 10);

        if($type == 'order_refund_money'){
            // 退款金额
            return $model->sum('refund_money');
        }else if($type == 'order_refund_total'){
            // 退款数量
            return $model->count();
        }
        return 0;
    }
}
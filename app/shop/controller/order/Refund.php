<?php

namespace app\shop\controller\order;

use app\shop\controller\Controller;
use app\shop\model\order\Order as OrderModel;
use app\shop\model\order\OrderRefund as OrderRefundModel;
use app\shop\model\settings\ReturnAddress as ReturnAddressModel;

/**
 * 售后管理
 */
class Refund extends Controller
{
    /**
     * 售后列表
     */
    public function index()
    {
        $model = new OrderRefundModel;
        $params = $this->postData();
        //列表数据
        $list = $model->getList($params);
        //添加多代理商售后状态
        $list = $this->applicationStatus($list);
        //重要数字
        $num_arr = $model->groupCount($params);
        $arr = [];
        foreach ($num_arr as $key => $val) {
            $k = $val['status']['value'];
            $arr[$k] = $val;
        }

        return $this->renderSuccess('', compact('list', 'arr'));
    }

    /**
     * 售后单详情
     */
    public function detail($order_refund_id)
    {
        // 售后单详情
        $detail = OrderRefundModel::detail($order_refund_id);
        //添加多个代理商售后状态
        $detail = $this->applicationStatus1($detail);
        if (isset($detail['send_time']) && $detail['send_time'] > 0) {
            $detail['send_time'] = date('Y-m-d H:i:s', $detail['send_time']);
        }
        // 订单详情
        $order = OrderModel::detail($detail['order_id']);
        // 退货地址
        $address = (new ReturnAddressModel)->getAll();
        return $this->renderSuccess('', compact('detail', 'order', 'address'));
    }

    /**
     * 商家审核
     */
    public function audit($order_refund_id)
    {
        $model = OrderRefundModel::detail($order_refund_id);
//        halt($this->postData());
        if ($model->audit($this->postData())) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 确认收货并退款
     */
    public function receipt($order_refund_id)
    {
        if (!$this->request->isPost()) {
            return false;
        }
        $model = OrderRefundModel::detail($order_refund_id);
        if ($model->receipt($this->postData())) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 代理商的处理下线的售后订单的状态
     */

    public function applicationStatus($list){

        foreach ($list as $k=>$v){
            if($v['agent_status'] == 1){
                $list[$k]['agent_status_value'] = '用户申请中';
            }
            if($v['agent_status'] == 2){
                $list[$k]['agent_status_value'] = '通过申请';
            }
            if($v['agent_status'] == 3){
                $list[$k]['agent_status_value'] = '收货中';
            }
            if($v['agent_status'] >= 4){
                $list[$k]['agent_status_value'] = '已收货';
            }
            if($v['agent_status'] == -1){
                $list[$k]['agent_status_value'] = '拒绝申请';
            }
            if($v['agent_status'] == 0){
                $list[$k]['agent_status_value'] = '';
            }
        }
        return $list;
    }

    /**
     * 详情页代理商的处理下线的售后订单的状态
     */

    public function applicationStatus1($list){

            if($list['agent_status'] == 1){
                $list['agent_status_value'] = '用户申请中';
            }
            if($list['agent_status'] == 2){
                $list['agent_status_value'] = '通过申请';
            }
            if($list['agent_status'] == 3){
                $list['agent_status_value'] = '收货中';
            }
            if($list['agent_status'] >= 4){
                $list['agent_status_value'] = '已收货';
            }
            if($list['agent_status'] == -1){
                $list['agent_status_value'] = '拒绝申请';
            }
            if($list['agent_status'] == 0){
                $list['agent_status_value'] = '';
            }

        return $list;
    }

}
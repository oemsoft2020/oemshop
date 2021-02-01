<?php

namespace app\common\service\order;

use app\common\model\user\User as UserModel;
use app\common\model\user\BalanceLog as BalanceLogModel;
use app\common\enum\order\OrderPayTypeEnum;
use app\common\enum\user\balanceLog\BalanceLogSceneEnum;
use app\common\library\easywechat\WxPay;
use app\common\library\easywechat\AppWx;
use app\common\library\easywechat\AppMp;
use app\api\model\order\OrderRefund as OrderRefundModel;

/**
 * 订单退款服务类
 */
class OrderRefundService
{
    /**
     * 执行订单退款
     */
    public function execute(&$order, $money = null)
    {
        // 退款金额，如不指定则默认为订单实付款金额
        is_null($money) && $money = $order['pay_price'];
        /**
         * by keven
         * 查售后订单表里面有没有存代理商的id， agent_id=0， 没开启 agent_id=1，开启，
         * 有就是开启了代理商售后，钱要直接余额退给代理商
         */
        $model = new OrderRefundModel();
        $data = $model->where('order_id','=',$order['order_id'])->find();
        if($data['agent_id']==0){//agent_id =0,没开启代理商售后，1是开启
            // 1.微信支付退款

            if ($order['pay_type']['value'] == OrderPayTypeEnum::WECHAT) {
                return $this->wxpay($order, $money);
            }
            // 2.余额支付退款
            if ($order['pay_type']['value'] == OrderPayTypeEnum::BALANCE) {
                return $this->balance($order, $money);
            }
        }else{
                $order1=[
                    'user_id'=>$data['agent_id'],
                    'order_no'=>$order['order_no'],
                ];
                //余额退款给代理商
                return $this->balance($order1, $money);
        }


        return false;
    }

    /**
     * 余额支付退款
     */
    private function balance(&$order, $money)
    {
        // 回退用户余额
        $user = UserModel::detail($order['user_id']);
        $user->where('user_id', '=', $order['user_id'])->inc('balance', $money)->update();
        // 记录余额明细
        BalanceLogModel::add(BalanceLogSceneEnum::REFUND, [
            'user_id' => $user['user_id'],
            'money' => $money,
        ], ['order_no' => $order['order_no']]);
        return true;
    }

    /**
     * 微信支付退款
     */
    private function wxpay(&$order, $money)
    {
        if($order['pay_source'] == 'mp' || $order['pay_source'] == 'payH5'){
            $app = AppMp::getWxPayApp($order['app_id']);
        }else if($order['pay_source'] == 'wx'){
            $app = AppWx::getWxPayApp($order['app_id']);
        }
        $WxPay = new WxPay($app);
        return $WxPay->refund($order['transaction_id'], $order['pay_price'], $money);
    }

}
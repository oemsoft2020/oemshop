<?php

namespace app\api\model\plus\card;

use app\api\service\order\PaymentService;
use app\api\service\order\paysuccess\type\CardGradePaySuccessService;
use app\common\enum\order\OrderPayTypeEnum;
use app\common\enum\order\OrderTypeEnum;
use app\common\model\plus\card\CardGradeOrder as CardGradeOrderModel;
use app\api\model\plus\card\CardGrade as CardGradeModel;



/**
 * 名片升级订单模型
 */
class CardGradeOrder extends CardGradeOrderModel
{
    public function getList($where)
    {
        return $this->with(['cardGrade'])->where($where)
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }

    /**
     * 创建购买名片礼包订单
     * 返回订单id
     */
    public function createOrder($card, $card_grade_id, $params)
    {
        $detail = CardGradeModel::detail($card_grade_id);
        //写入order表
        $this->startTrans();
        $status = $this->save([
            'card_grade_id' => $card_grade_id,
            'order_no' => $this->orderNo(),
            'total_price' => $detail['money'],
            'order_price' => $detail['money'],
            'pay_price' => $detail['money'],
            'user_id' => $card['user_id'],
            'card_id'=>$card['card_id'],
            'supply_id'=>$card['supply_id'],
            'app_id' => self::$app_id
        ]);
        // 余额支付标记订单已支付
        if ($status && $params['pay_type'] == OrderPayTypeEnum::BALANCE) {
           $status = $this->onPaymentByBalance($this['order_no']);
          
        }
        if (!$status) {
            $this->rollback();
        }else{
            $this->commit();
        }
        return $status;
    }

    /**
     * 余额支付标记订单已支付
     */
    public function onPaymentByBalance($orderNo)
    {
        // 获取订单详情
        $PaySuccess = new CardGradePaySuccessService($orderNo);
        // 发起余额支付
        $status = $PaySuccess->onPaySuccess(OrderPayTypeEnum::BALANCE);
        if (!$status) {
            $this->error = $PaySuccess->getError();
        }
        return $status;
    }

    /**
     * 待支付订单详情
     */
    public static function getPayDetail($orderNo)
    {
        $model = new static();
        return $model->where(['order_no' => $orderNo, 'pay_status' => 10, 'is_delete' => 0])->with(['user'])->find();
    }

    /**
     * 构建支付请求的参数
     */
    public static function onOrderPayment($user, $order, $payType, $pay_source)
    {
        if ($payType == OrderPayTypeEnum::WECHAT) {
            return self::onPaymentByWechat($user, $order, $pay_source);
        }
        return [];
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
            OrderTypeEnum::CARD,
            $pay_source
        );
    }
}
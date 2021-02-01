<?php

namespace app\api\model\plus\deposit;

use app\api\service\order\PaymentService;
use app\api\service\order\paysuccess\type\SupplyPaySuccessService;
use app\common\enum\order\OrderPayTypeEnum;
use app\common\enum\order\OrderTypeEnum;
use app\common\model\plus\deposit\Record as OrderModel;
use app\common\model\plus\supply\Grade as GradeModel;
use app\common\exception\BaseException;
use app\common\library\helper;

/**
 * 模型
 */
class Record extends OrderModel
{
    public function getList($where)
    {
        return $this->where($where)
            ->order(['level' => 'asc'])
            ->select();
    }

    /**
     * 创建订单
     * 返回订单id
     */
    public function createOrder($user, $kmd_grade_id, $params,$apply_info=[])
    {
        $detail = GradeModel::detail($kmd_grade_id);

        $setting = $detail['setting'];
        //写入order表
        $this->startTrans();
        $status = $this->save([
            'order_no' => $this->orderNo(),
            'total_price' => $setting['charge_fee']+$setting['deposit'],
            'assure_money' => $setting['deposit'],
            'enter_money' =>  $setting['charge_fee'],
            'pay_price' => $setting['charge_fee']+$setting['deposit'],
            'user_id' => $user['user_id'],
            'apply_info' => json_encode($apply_info),
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
        $PaySuccess = new SupplyPaySuccessService($orderNo);
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
            OrderTypeEnum::SUPPLY,
            $pay_source
        );
    }
}
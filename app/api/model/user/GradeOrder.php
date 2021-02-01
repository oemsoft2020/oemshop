<?php

namespace app\api\model\user;

use app\api\service\order\PaymentService;
use app\api\service\order\paysuccess\type\GradePaySuccessService;
use app\common\enum\order\OrderPayTypeEnum;
use app\common\enum\order\OrderTypeEnum;
use app\common\model\user\GradeOrder as OrderModel;
use app\common\model\user\Grade as GradeModel;
use app\common\exception\BaseException;
use app\common\library\helper;

/**
 * 礼包购模型
 */
class GradeOrder extends OrderModel
{
    public function getList($where)
    {
        return $this->where($where)
            ->order(['level' => 'asc'])
            ->select();
    }

    /**
     * 创建礼包购订单
     * 返回订单id
     */
    public function createOrder($user, $grade_id, $params)
    {
        $detail = GradeModel::detail($grade_id);
        $userGrade = $user['grade'];
        if ($userGrade['level']>=$detail['level']) {
            $this->error = '你已经是'.$userGrade['name'].'用户了，不能升级该等级';
            return false;
        }
        //写入order表
        $this->startTrans();
        $status = $this->save([
            'grade_id' => $grade_id,
            'old_grade_id' => $user['grade_id'],
            'order_no' => $this->orderNo(),
            'total_price' => $detail['level_money'],
            'order_price' => $detail['level_money'],
            'pay_price' => $detail['level_money'],
            'user_id' => $user['user_id'],
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
        $PaySuccess = new GradePaySuccessService($orderNo);
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
            OrderTypeEnum::GRADE,
            $pay_source
        );
    }
}
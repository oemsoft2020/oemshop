<?php

namespace app\api\service\order\paysuccess\type;

use app\api\model\plus\storage\Storage;
use app\api\model\user\User as UserModel;
use app\api\model\order\Order as OrderModel;
use app\common\enum\order\OrderTypeEnum;
use app\common\model\user\BalanceLog as BalanceLogModel;
use app\common\enum\order\OrderPayTypeEnum;
use app\common\enum\user\balanceLog\BalanceLogSceneEnum;
use app\api\model\plus\agent\Order as AgentOrderModel;
use app\api\model\plus\logistics\Stock as StockModel;
use app\common\model\settings\Setting as SettingModel;
use app\api\model\plus\supply\Supply as SupplyModel;
use app\common\model\user\Grade;
use app\common\model\user\User;
use app\common\service\BaseService;
use app\common\service\order\OrderCompleteService;
use app\common\service\product\factory\ProductFactory;
use think\facade\Db;


/**
 * 订单支付成功服务类
 */
class MasterPaySuccessService extends BaseService
{
    // 订单模型
    public $model;

    // 当前用户信息
    private $user;

    /**
     * 构造函数
     */
    public function __construct($orderNo)
    {
        // 实例化订单模型
        $this->model = OrderModel::getPayDetail($orderNo);
        // 获取用户信息
        $this->user = UserModel::detail($this->model['user_id']);
    }

    /**
     * 订单支付成功业务处理
     */
    public function onPaySuccess($payType, $payData = [])
    {
        if (empty($this->model)) {
            $this->error = '未找到该订单信息';
            return false;
        }
        // 更新付款状态
        $status = $this->updatePayStatus($payType, $payData);
        // 订单支付成功行为
        if ($status == true) {
            //付款触发升级升级
            $user = new User();
            $user->upgradeUserGradeByPayMoney($this->user['user_id']);
            $user->upgradeUserGradeAfterPay($this->user['user_id']);
            // 获取订单详情
            $detail = OrderModel::getUserOrderDetail($this->model['order_id'], $this->user['user_id']);
            // 记录供应商订单
            SupplyModel::createOrder($detail);
            // 记录分销商订单
            if ($detail['is_agent'] == 1) {
                AgentOrderModel::createOrder($detail);
            }
            $vars = SettingModel::getItem('depot', $detail['app_id']);
            if (isset($vars['is_open_storage']) && !empty($vars['is_open_storage']) && $detail['delivery_type']['value'] == 40) {
                // 增加仓库记录
                Storage::createStorage($detail);
                //修改订单状态为已完成
                $this->updateOrderStatus();
                // 执行订单完成后的操作 分佣
                $OrderCompleteService = new OrderCompleteService(OrderTypeEnum::MASTER);
                $OrderCompleteService->complete([$detail], $detail['app_id']);
            }

            event('PaySuccess', $detail);
        }
        return $status;
    }

    /**
     * 更新付款状态
     */
    private function updatePayStatus($payType, $payData = [])
    {
        // 验证余额支付时用户余额是否满足
        if ($payType == OrderPayTypeEnum::BALANCE) {
            if ($this->user['balance'] < $this->model['pay_price']) {
                $this->error = '用户余额不足，无法使用余额支付';
                return false;
            }
        }
        // 事务处理
        $this->model->transaction(function () use ($payType, $payData) {
            // 更新订单状态
            $this->updateOrderInfo($payType, $payData);
            // 累积用户总消费金额
            $this->user->setIncPayMoney($this->model['pay_price']);
            // 累积用户及其上级的业绩
            $this->user->setIncAchievement($this->model);
            // 记录订单支付信息
            $this->updatePayInfo($payType);
        });
        return true;
    }

    /**
     * 更新订单记录
     */
    private function updateOrderInfo($payType, $payData)
    {
        // 更新商品库存、销量
        $values = SettingModel::getItem('logistics', $this->model['app_id']);
        if (isset($values['is_open_logistics']) && !empty($values['is_open_logistics'])) {
            $stock_model = new StockModel();
            $stock_model->updateStockSales($this->model['product'], $this->model['logistics_id']);
        } else {
            ProductFactory::getFactory($this->model['order_source'])->updateStockSales($this->model['product']);
        }

        // 整理订单信息
        $pay_source = '';
        if (isset($payData['attach'])) {
            $attach = json_decode($payData['attach'], true);
            $pay_source = isset($attach['pay_source']) ? $attach['pay_source'] : '';
        }

        $order = [
            'pay_type' => $payType,
            'pay_status' => 20,
            'pay_time' => time(),
            'pay_source' => $pay_source
        ];
        if ($payType == OrderPayTypeEnum::WECHAT) {
            $order['transaction_id'] = $payData['transaction_id'];
        }
        // 更新订单状态
        return $this->model->save($order);
    }

    /**
     * 商品进入云仓订单状态自动改为已完成
     * @return bool
     */
    public function updateOrderStatus()
    {
        return $this->model->save(['order_status'=>30]);
    }

    /**
     * 记录订单支付信息
     */
    private function updatePayInfo($payType)
    {
        // 余额支付
        if ($payType == OrderPayTypeEnum::BALANCE) {
            // 更新用户余额
            (new UserModel())->where('user_id', '=', $this->user['user_id'])
                ->dec('balance', $this->model['pay_price'])
                ->update();
            BalanceLogModel::add(BalanceLogSceneEnum::CONSUME, [
                'user_id' => $this->user['user_id'],
                'money' => -$this->model['pay_price'],
            ], ['order_no' => $this->model['order_no']]);
        }
    }

}
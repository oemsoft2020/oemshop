<?php

namespace app\api\controller\user;

use app\api\controller\Controller;
use app\api\model\order\Order as OrderModel;
use app\api\model\plus\storage\Storage;
use app\api\model\settings\Setting as SettingModel;
use app\common\enum\order\OrderTypeEnum;
use app\common\enum\order\OrderPayTypeEnum;
use app\common\model\plus\codebatch\Code;
use app\common\model\plus\logistics\Logistics;
use app\common\service\qrcode\ExtractService;
use app\common\service\message\MessageService;
/**
 * 我的订单
 */
class Order extends Controller
{
    // user
    private $user;

    /**
     * 构造方法
     */
    public function initialize()
    {
        parent::initialize();
        $this->user = $this->getUser();   // 用户信息

    }

    /**
     * 我的订单列表
     */
    public function lists($dataType)
    {
        $data = $this->postData();
        $model = new OrderModel;
        $list = $model->getList($this->user['user_id'], $dataType, $data);
        return $this->renderSuccess('', compact('list'));
    }

    /**
     * 订单详情信息
     */
    public function detail($order_id)
    {
        // 订单详情
        $model = OrderModel::getUserOrderDetail($order_id, $this->user['user_id']);
        // 该订单是否允许申请售后
        $model['isAllowRefund'] = $model->isAllowRefund();
        $mini_name = '跳转小程序';
        $mini_appid = '';
        $mini_page = '';
        $extraData = [];
        if($model['product']){

            foreach ($model['product'] as $key => $value) {
                    $value['link'] = isset($value['product']['link']) ? $value['product']['link'] : '';
                    $value['code'] = '';
                    $vars = SettingModel::getItem('depot');
                    if (isset($vars['is_open_storage']) && !empty($vars['is_open_storage'])){
                        if ($value['product']['code_product_open'] == 1) {
                            $storageModel = new Storage();
                            $value['code'] = $storageModel->where('order_id',$value['order_id'])->find();
                        }
                    }
                    // 判断是否显示链接
                    if(isset($value['product']['link_status']) && $value['product']['link_status'] == 1){
                        // 判断当前时间是否到达显示开始时间
                        if(isset($value['product']['link_start_at']) && !empty($value['product']['link_start_at'])){
                            // 判断是否设置了过期时间
                            if(isset($value['product']['link_end_at']) && !empty($value['product']['link_end_at'])){
                                // 大于开始时间小于结束时间
                                if(time() > $value['product']['link_start_at'] && time() < $value['product']['link_end_at']){
                                    $value['link_show'] = 1;
                                }else{
                                    $value['link_show'] = 0;//未到达开始时间或者已经过期
                                }

                            }else{
                                // 未设置过期时间，永久展示
                                $value['link_show'] = 1;
                            }   
                            
                        }else{
                            // 未设置开始展示时间，判断是否设置了过期时间
                            if(isset($value['product']['link_end_at']) && !empty($value['product']['link_end_at'])){
                                if(time() < $value['product']['link_end_at']){
                                    $value['link_show'] = 1;
                                }else{
                                    $value['link_show'] = 0;//已经过期
                                }
                            }else{
                                $value['link_show'] = 1;//未设置开始和结束时间，永久展示
                            }
                           
                        }   
                        
                    }else{
                        $value['link_show'] = 0;
                    }
            }

            foreach($model['product'] as $product){
                if($product['product']['mini_name']){
                    $mini_name = $product['product']['mini_name'];
                }
                if($product['product']['mini_appid']){
                    $mini_appid = $product['product']['mini_appid'];
                }
                if($product['product']['mini_page']){

                    $mini_page = $product['product']['mini_page'];

                    if(strpos($product['product']['mini_page'],'$user_id')!==false){
                        $mini_page = str_replace('$user_id',$this->user['user_id'],$product['product']['mini_page']);
                        $extraData  = ['user_id'=>$this->user['user_id']];
                    }
                   
                }
                break;
            }
        }

        $model['logistics_info'] = [];
        if(!empty($model['logistics_id'])){
            $logisticsModel =  new Logistics();
            $model['logistics_info'] =   $logisticsModel->find($model['logistics_id']);
        }

        //时间段
        $times_start = ["08:00", "08:30","09:00","09:30", "10:00","10:30", "11:00","11:30", "12:00","12:30", "13:00","13:30", "14:00","14:30",
            "15:00","15:30","16:00","16:30", "17:00", "17:30","18:00", "18:30","19:00","19:30","20:00","20:30", "21:00","21:30","22:00","22:30",
            "23:00", "23:30"];
        $times_end = ["08:30","09:00","09:30", "10:00","10:30", "11:00","11:30", "12:00","12:30", "13:00","13:30", "14:00","14:30",
        "15:00","15:30","16:00","16:30", "17:00", "17:30","18:00", "18:30","19:00","19:30","20:00","20:30", "21:00","21:30","22:00","22:30",
        "23:00", "23:30",'00:00'];

        $range = array();
        foreach ($times_start as $index => $item) {
            $tmp = $times_start[$index] . "-" . $times_end[$index];
            array_push($range, $tmp);
        }
        $timeRange = $range;

        $dataArray = [];

        for ($c = 0; count($dataArray) < 7  ; $c++) {
            $count = count($dataArray);
            if ($count == 7) {
                break;
            }
            $time = $c == 0 ? time() : strtotime("+" . $c . " day");

            array_push($dataArray, date("Y-m-d", $time));

        }

        return $this->renderSuccess('', [
            'order' => $model,  // 订单详情
            'setting' => [
                // 积分名称
                'points_name' => SettingModel::getPointsName(),
            ],
            'mini_name'=>$mini_name,
            'mini_appid'=>$mini_appid,
            'mini_page'=>$mini_page,
            'extraData'=>$extraData,
            'timeRange'=>$timeRange,
            'dataArray'=>$dataArray
        ]);
    }

    /**
     * 获取物流信息
     */
    public function express($order_id)
    {
        // 订单信息
        $order = OrderModel::getUserOrderDetail($order_id, $this->user['user_id']);
        if (!$order['express_no']) {
            return $this->renderError('没有物流信息');
        }
        // 获取物流信息
        $model = $order['express'];
        $express = $model->dynamic($model['express_name'], $model['express_code'], $order['express_no']);
        if ($express === false) {
            return $this->renderError($model->getError());
        }
        return $this->renderSuccess('', compact('express'));
    }

    /**
     * 取消订单
     */
    public function cancel($order_id)
    {
        $model = OrderModel::getUserOrderDetail($order_id, $this->user['user_id']);
        if ($model->cancel($this->user)) {
            return $this->renderSuccess('订单取消成功');
        }
        return $this->renderError($model->getError()?:'订单取消失败');
    }

    /**
     * 确认收货
     */
    public function receipt($order_id)
    {
        $model = OrderModel::getUserOrderDetail($order_id, $this->user['user_id']);
        if ($model->receipt()) {
            return $this->renderSuccess('收货成功');
        }
        return $this->renderError($model->getError()?:'收货失败');
    }

    /**
     * 立即支付
     */
    public function pay($order_id, $payType = OrderPayTypeEnum::WECHAT, $pay_source = 'wx')
    {
        // 获取订单详情
        $model = OrderModel::getUserOrderDetail($order_id, $this->user['user_id']);
        // 订单支付事件
        if (!$model->onPay($payType)) {
            return $this->renderError($model->getError() ?: '订单支付失败');
        }
        // 构建微信支付请求
        $payment = $model->onOrderPayment($this->user, $model, $payType, $pay_source);
        // 支付状态提醒
        return $this->renderSuccess('', [
            'order_id' => $model['order_id'],   // 订单id
            'pay_type' => $payType,             // 支付方式
            'payment' => $payment               // 微信支付参数
        ], ['success' => '支付成功', 'error' => '订单未支付']);
    }

    /**
     * 获取订单核销二维码
     */
    public function qrcode($order_id, $source)
    {
        // 订单详情
        $order = OrderModel::getUserOrderDetail($order_id, $this->user['user_id']);
        // 判断是否为待核销订单
        if (!$order->checkExtractOrder($order)) {
            return $this->renderError($order->getError());
        }
        $Qrcode = new ExtractService(
            $this->app_id,
            $this->user,
            $order_id,
            $source,
            OrderTypeEnum::MASTER
        );
        return $this->renderSuccess('',[
            'qrcode' => $Qrcode->getImage(),
        ]);
    }

    /* 
    *　订单预约
    */

    public function reserve()
    {
        $data = $this->postData();
        if(!isset($data['reserve_date'])){
            return $this->renderError('请填写预约日期');
        }
        if(!isset($data['reserve_time'])){
            return $this->renderError('请填写预约时间');
        }
        $order_id = isset($data['order_id'])?$data['order_id']:0;
        if(empty($order_id)){
            return $this->renderError('预约失败');
        } 
        $data = [
            'reserve_date'=>$data['reserve_date'],
            'reserve_time'=>$data['reserve_time']
        ];
        $model = new OrderModel;
        $where = [
            'order_id'=>$order_id
        ];
        $res  = $model->where($where)->update($data);
        
        $order =  OrderModel::getUserOrderDetail($order_id, $this->user['user_id']);
        (new MessageService)->newReserve($order);
        return $this->renderSuccess('预约成功');

    }

}
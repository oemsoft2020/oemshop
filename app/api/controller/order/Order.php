<?php

namespace app\api\controller\order;

use app\api\model\order\Cart as CartModel;
use app\api\model\order\Order as OrderModel;
use app\api\model\plus\codebatch\CodeBatch;
use app\api\service\order\settled\MasterOrderSettledService;
use app\api\controller\Controller;
use app\api\model\settings\Message as MessageModel;
use app\common\model\settings\Setting;
use app\common\model\plus\logistics\Logistics as LogisticsModel;
use app\common\model\plus\logistics\Stock as StockModel;
use app\api\model\settings\Setting as SettingModel;

/**
 * 普通订单
 */
class Order extends Controller
{
    /**
     * 订单确认-立即购买
     */
    public function buy()
    {

        // 立即购买：获取订单商品列表
        $params = $this->request->param();
        isset($params['time']) ? $time = $params['time'] : $time = null;

        $productList = OrderModel::getOrderProductListByNow($params, $time);

        $user = $this->getUser();

        $this->isOpenBindReferee($user);
        
        $productList = $this->validateProductList($productList, $user);

        // 实例化订单service
        $orderService = new MasterOrderSettledService($user, $productList, $params);
        // 获取订单信息
        $orderInfo = $orderService->settlement();

        if ($this->request->isGet()) {
            // 如果来源是小程序, 则获取小程序订阅消息id.获取支付成功,发货通知.
            $template_arr = MessageModel::getMessageByNameArr($params['pay_source'], ['order_pay_user', 'order_delivery_user']);
            return $this->renderSuccess('', compact('orderInfo', 'template_arr'));
        }
        if ($orderService->hasError()) {
            return $this->renderError($orderService->getError());
        }
        $orderInfo = $this->formatOrderInfo($orderInfo, $orderService, $user);
        // 创建订单
        $order_id = $orderService->createOrder($orderInfo);
        if (!$order_id) {
            return $this->renderError($orderService->getError() ?: '订单创建失败');
        }
        // 构建支付请求
        $payment = OrderModel::onOrderPayment($user, $orderService->model, $params['pay_type'], $params['pay_source']);

        // 返回结算信息
        return $this->renderSuccess(['success' => '支付成功', 'error' => '订单未支付'], [
            'order_id' => $order_id,   // 订单id
            'pay_type' => $params['pay_type'],  // 支付方式
            'payment' => $payment,               // 微信支付参数
        ]);
    }

    /**
     * 订单确认-立即购买
     */
    public function cart()
    {
        // 立即购买：获取订单商品列表
        $params = $this->request->param();
        $user = $this->getUser();

        // 商品结算信息
        $CartModel = new CartModel($user);
        // 购物车商品列表
        $productList = $CartModel->getList($params['cart_ids']);
        $productList = $this->validateProductList($productList, $user);
        // 实例化订单service
        $orderService = new MasterOrderSettledService($user, $productList, $params);
        // 获取订单信息
        $orderInfo = $orderService->settlement();
        if ($this->request->isGet()) {
            // 如果来源是小程序, 则获取小程序订阅消息id.获取支付成功,发货通知.
            $template_arr = MessageModel::getMessageByNameArr($params['pay_source'], ['order_pay_user', 'order_delivery_user']);
            return $this->renderSuccess('', compact('orderInfo', 'template_arr'));
        }

        if ($orderService->hasError()) {
            return $this->renderError($orderService->getError());
        }
        //格式化订单数据
        $orderInfo = $this->formatOrderInfo($orderInfo, $orderService, $user);

        // 创建订单
        $order_id = $orderService->createOrder($orderInfo);
        if (!$order_id) {
            return $this->renderError($orderService->getError() ?: '订单创建失败');
        }
        // 移出购物车中已下单的商品
        $CartModel->clearAll($params['cart_ids']);
        // 构建支付请求
        $payment = OrderModel::onOrderPayment($user, $orderService->model, $params['pay_type'], $params['pay_source']);
        // 返回结算信息
        return $this->renderSuccess('', [
            'order_id' => $orderService->model['order_id'],   // 订单id
            'pay_type' => $params['pay_type'],  // 支付方式
            'payment' => $payment               // 微信支付参数
        ], ['success' => '支付成功', 'error' => '订单未支付']);
    }

    /* 
    * 物流中心开启
    */
    public function validateProductList($productList, $user)
    {
        $values = Setting::getItem('logistics');

        if (empty($values['is_open_logistics'])) {
            return $productList;
        }

        if (empty($user['address_default'])) {
            return $productList;
        }
        $logistics_model = new LogisticsModel();
        $stock_model = new StockModel();

        $logistics_info = $logistics_model->where('FIND_IN_SET(:region_id,region)', ['region_id' => $user['address_default']['city_id']])->order(['create_time' => 'desc'])->find();

        foreach ($productList as &$product) {

            $product['logistics_id'] = 0;
            $product['product_stock'] = 0;
            $product['product_sku']['stock_num'] = 0;

            if (!empty($logistics_info)) {
                $product['logistics_id'] = $logistics_info['logistics_id'];
                $where = [
                    'logistics_id' => $logistics_info['logistics_id'],
                    'product_id' => $product['product_id'],
                    'product_sku_id' => empty($product['spec_sku_id']) ? 0 : $product['spec_sku_id']
                ];
                $stock = $stock_model->where($where)->find();

                if ($stock) {
                    $product['product_stock'] = $stock['product_stock'];
                    $product['product_sku']['stock_num'] = $stock['product_stock'];
                } else {
                    $product['total_price'] = 0;
                    $product['total_num'] = 0;
                }

            }
        }
        unset($product);
        return $productList;

    }

    /* 
    * 物流中心-订单信息处理
    */
    public function formatOrderInfo($orderInfo, $orderService, $user)
    {
        $values = Setting::getItem('logistics');
        if (empty($values['is_open_logistics'])) {

            return $orderInfo;
        }
        $logistics_model = new LogisticsModel();

        $logistics_info['logistics_id'] = 0;
        $logistics_info = $logistics_model->where('FIND_IN_SET(:region_id,region)', ['region_id' => $user['address_default']['city_id']])->order(['create_time' => 'desc'])->find();
        if (!empty($logistics_info)) {
            $orderInfo['logistics_id'] = $logistics_info['logistics_id'];
        }
        foreach ($orderInfo['product_list'] as $key => $product) {

            if (isset($product['nobuy']) && !empty($product['nobuy'])) {
                unset($orderInfo['product_list'][$key]);
            }
        }
        return $orderInfo;
    }

    /*
    * 首页订单弹幕接口
     * 查询上一天的所有订单（已支付）
     * 返回 购买人头像，昵称，购买时间
    */
    public function buyOrderList(){
        $getData = $this->getData();
        $date = strtotime(date('y-m-d',time()));//今天0点
        $date1 = $date-(24*3600*10);//昨天0点
        $model = new OrderModel();
        $where = 'o.create_time >=' . $date1 .' and  o.create_time <=' .$date . ' and o.app_id =' .$getData['app_id'];
        $list = $model->alias('o')
            ->join('user','o.user_id = user.user_id')
            ->where('o.pay_status',20)
            ->where($where)
            ->field('user.user_id,user.nickName,user.avatarUrl,o.*')
            ->select();
        $time= time();
//
        if($list){
            foreach ($list as $k =>$v){
                $orderData = '最新订单来自'.$v['nickName'].',';
                $a = '';
               $res = intval(($time- strtotime($v['create_time']))%86400/3600);
               if($res>1){
                   $a = $res.'小时';
               }
                $res = intval(($time- strtotime($v['create_time']))%86400%3600/60);
                if($res>0){
                    $a .= $res.'分';
                }
                $res = intval(($time- strtotime($v['create_time']))%86400%3600%60/60);
//                if($res>0){
                    $a .= $res.'秒前';
//                }

                $list[$k]['time'] =$orderData.$a;
//                unset($a);
            }
        }
        // 返回结算信息
        return $this->renderSuccess('success' , [
            'list' => $list,   // 订单id

        ]);

    }
}
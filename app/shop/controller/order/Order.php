<?php

namespace app\shop\controller\order;

use app\shop\controller\Controller;
use app\shop\model\order\Order as OrderModel;
use app\shop\model\store\Store as StoreModel;
use app\common\enum\settings\DeliveryTypeEnum;
use app\shop\model\plus\logistics\Logistics as LogisticsModel;
use app\shop\model\shop\ShopUserApp as ShopUserAppModel;
use app\shop\model\settings\Setting as SettingModel;
use app\shop\model\auth\User as UserModel;
use app\shop\model\settings\Express as ExpressModel;
use app\shop\model\store\Clerk as ShopClerkModel;
use app\common\model\plus\supply\Supply as SupplyModel;
use app\api\model\plus\agent\Order as AgentOrderModel;
use app\common\model\plus\agent\Order as AgentOrder;
use app\common\model\order\ImportLog as logModel;
use think\facade\Db;

/**
 * 订单控制器
 */
class Order extends Controller
{
    /**
     * 订单列表
     */
    public function initialize()
    {
        parent::initialize();
        $this->user = UserModel::detail($this->store['user']['shop_user_id']);
    }
    public function index($dataType = 'all')
    {
        // 物流中心列表
        $values = SettingModel::getItem('logistics');
        $condition =[];
        $logistics_list = [];
        if(isset($values['is_open_logistics'])&&$values['is_open_logistics']){
            if(!$this->user['is_super']){
                $shopUserAppModel = new ShopUserAppModel();
                $where = [
                    'type'=>'logistics',
                    'app_id'=>$this->store['app']['app_id'],
                    'shop_user_id'=>$this->user['shop_user_id'],
                ];
                $logistics_id_array =  $shopUserAppModel->where($where)->column('data_id');
            
                if($logistics_id_array){
                    $logisticsModel =  new LogisticsModel();
                    $condition = [
                        'logistics_id'=>implode(',',$logistics_id_array)
                    ];
                    
                }
                
            }
            $logisticsModel = new LogisticsModel();
            $logistics_list = $logisticsModel->getAllList($condition);
            
        }
            
        $is_open_logistics = isset($values['is_open_logistics'])?$values['is_open_logistics']:false;
        
        // 订单列表
        $model = new OrderModel();
        $list = $model->getList($dataType,array_merge($condition,$this->postData()));
        $order_count = [
            'order_count' => [
                'payment' => $model->getCount('payment',$condition),
                'delivery' => $model->getCount('delivery',$condition),
                'received' => $model->getCount('received',$condition),
            ],];
        // 自提门店列表
        $shop_list = StoreModel::getAllList();
        $ex_style = DeliveryTypeEnum::data();
        $session = session('kmdshop_store');
        $supply_id = $session['supply_id'];
        $supply = SupplyModel::with('kmd_grade')->find(session('kmdshop_store')['supply_id']);
        return $this->renderSuccess('', compact('list', 'ex_style', 'shop_list', 'order_count','supply_id','is_open_logistics','logistics_list','supply'));
    }

    /**
     * 订单详情
     */
    public function detail($order_id)
    {
        // 订单详情
        $detail = OrderModel::detail($order_id);
        if (isset($detail['pay_time']) && $detail['pay_time'] != '') {
            $detail['pay_time'] = date('Y-m-d H:i:s', $detail['pay_time']);
        }
        if (isset($detail['delivery_time']) && $detail['delivery_time'] != '') {
            $detail['delivery_time'] = date('Y-m-d H:i:s', $detail['delivery_time']);
        }
        // 物流公司列表
        $model = new ExpressModel();
        $expressList = $model->getAll();
        // 门店店员列表
        $shopClerkList = (new ShopClerkModel)->getAll(true);
        $session = session('kmdshop_store');
        $supply = SupplyModel::with('kmd_grade')->find(session('kmdshop_store')['supply_id']);
        return $this->renderSuccess('', compact('detail', 'expressList', 'shopClerkList','supply'));
    }

    /**
     * 确认发货
     */
    public function delivery($order_id)
    {
        $model = OrderModel::detail($order_id);
        if ($model->delivery($this->postData('param'))) {
            return $this->renderSuccess('发货成功');
        }
        return $this->renderError('发货失败');
    }

    /**
     * 修改订单价格
     */
    public function updatePrice($order_id)
    {
        $model = OrderModel::detail($order_id);
        if ($model->updatePrice($this->postData('order'))) {
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError($model->getError() ?: '修改失败');
    }

    /* 
    * 重算佣金
    */
    public function recalculate($order_id)
    {
        $agent_order =  new AgentOrder();

        if(empty($order_id)){
            return false;
        }

        $where = [
            'order_id'=>$order_id,
            'type'=>'agent',
            'is_settled'=>0
        ];
        $agent_order->where($where)->delete();
        
        // 获取订单详情
        $detail = OrderModel::detail($order_id);
        
        // 记录分销商订单
        if ($detail['is_agent'] == 1) {
            AgentOrderModel::createOrder($detail);
        }
    }

    /**
     * 订单导入记录列表
     * @Author   linpf
     * @DataTime 2020-11-25T09:56:06+0800
     * @return   [type]                   [description]
     */
    public function accountlog()
    {
        $log_mod = new logModel();
        $list = $log_mod->getList($this->postData());
        return $this->renderSuccess('', compact('list'));
    }
}
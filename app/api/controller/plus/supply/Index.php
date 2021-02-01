<?php

namespace app\api\controller\plus\supply;

use app\api\controller\Controller;
use app\api\model\plus\supply\Supply as SupplyModel;
use app\api\model\order\Order as OrderModel;
use app\api\model\product\Product as ProductModel;
use app\api\model\page\Page as PageModel;
use app\api\model\page\Page as AppPage;
use app\shop\model\settings\Express as ExpressModel;
use app\shop\model\store\Clerk as ShopClerkModel;

/**
 * 供应商控制器
 */
class Index extends Controller
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
     * 供应商信息
     */
    public function detail()
    {
        $model = new SupplyModel();
        // 订单总数
        $order_model = new OrderModel;
        // 商品总数
        $product_model = new ProductModel;
        $supply = $model->detail(['user_id'=>$this->user['user_id']]);
        if (empty($supply)) {
             return $this->renderError('无权限，先申请');
        }
         $page_model  = new PageModel();
        $page_info = $page_model->where('page_type','50')->where('is_delete',0)->find();

        $items = [];
        if(empty($page_info)){
            $menu = SupplyModel::getMenus();
            $diymenus = 0; 
        }else{

            $items = AppPage::getPageData($this->getUser(false), $page_info['page_id']);
            $jsonData = $page_info['page_data'];
            jsonRecursive($jsonData);
            $menu =[];
            if(count($jsonData['items'])==1){
                $menu = $jsonData['items'];
                foreach ($menu[0]['data'] as $key => &$value) {
                    if(isset($value['appid'])&&strpos($value['linkUrl'],'$user_id')!==false){
                        $value['linkUrl'] = str_replace('$user_id',$user['user_id'],$value['linkUrl']);
                    }
                }
                unset($value);
            }

            
            $diymenus = 1;
        }
        return $this->renderSuccess('', [
            'supply'=>$supply,
            'orderCount'=> $order_model->getCount($supply, 'supply'),
            'productCount'=> $product_model->getCount($supply, 'supply'),
            'totalSale'=> $order_model->getOrderTotalPrice($supply),
            'menus' => $menu,   // 个人中心菜单列表
            'diymenus'=>$diymenus,
            'items'=>$items
        ]);
    }

    /**
     * 供应商订单
     */
    public function supplyOrder($dataType,$supply_id){
        if (isset($supply_id) && $supply_id == 0) {
            return $this->renderError('缺少参数supply_id');
        }
        $data = $this->postData();
        $model = new OrderModel;
        $list = $model->getsupplyOrder($supply_id, $dataType, $data);
        return $this->renderSuccess('', compact('list'));

    }

    /**
     * 订单详情
     */
    public function supplyOrder_detail($order_id,$supply_id)
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
        $supply = \app\common\model\plus\supply\Supply::with('kmd_grade')->find($supply_id);
        return $this->renderSuccess('', compact('detail', 'expressList', 'shopClerkList','supply'));
    }

    /**
     * 订单门店自提核销
    */
    public function supply_extract($order_id,$extract_clerk_id){

        $model = OrderModel::detail($order_id);
        if ($model->verificationOrder($extract_clerk_id)) {
            return $this->renderSuccess('恭喜你，核销成功');
        }
        return $this->renderError($model->getError() ?: '核销失败');
    }

    /**
     * 订单发货
     */
    public function supply_delivery($order_id,$express_id,$express_no){

        $model = \app\shop\model\order\Order::detail($order_id);
        $data=array();
        $data['express_id'] =$express_id;
        $data['express_no'] =$express_no;
        if ($model->delivery($data)) {
            return $this->renderSuccess('恭喜你，发货成功');
        }
        return $this->renderError('发货失败');
    }
    /**
     *用户取消订单审核
     */
    public function supply_Cancel($order_id,$is_cancel){
        $model = \app\shop\model\order\Order::detail($order_id);
        $data=array();
        $data['order_id'] =$order_id;
        $data['is_cancel'] =$is_cancel;
        if ($model->confirmCancel($data)){
            return $this->renderSuccess('恭喜你，审核成功');
        }
        return $this->renderError('审核失败');
    }
}
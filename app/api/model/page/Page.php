<?php

namespace app\api\model\page;

use app\api\model\plus\giftpackage\Order;
use app\api\model\plus\live\WxLive;
use app\api\model\product\Product as ProductModel;
use app\api\model\plus\article\Article;
use app\api\model\store\Store as StoreModel;
use app\api\model\user\UserMp;
use app\common\model\page\Page as PageModel;
use app\api\model\plus\coupon\Coupon;
use app\api\model\plus\seckill\Product as SeckillProductModel;
use app\api\model\plus\seckill\Active as SeckillActiveModel;
use app\api\model\plus\assemble\Product as AssembleProductModel;
use app\api\model\plus\assemble\Active as AssembleActiveModel;
use app\api\model\plus\bargain\Product as BargainProductModel;
use app\api\model\plus\bargain\Active as BargainActiveModel;
use app\shop\model\order\OrderProduct;
use app\shop\model\user\User;
use app\api\model\plus\card\Card as CardModel;
use app\common\model\settings\Setting as SettingModel;
use app\common\model\product\ProductSku as skuModel;
use app\common\model\file\UploadFile as UploadFileModel;
use app\common\model\plus\agent\Setting as agentSetting;
use app\shop\model\product\Category as CategoryModel;

/**
 * 首页模型
 */
class Page extends PageModel
{
    /**
     * 隐藏字段
     */
    protected $hidden = [
        'app_id',
        'create_time',
        'update_time'
    ];

    /**
     * DIY页面详情
     */
    public static function getPageData($user, $page_id = null)
    {
        // 页面详情
        $detail = $page_id > 0 ? parent::detail($page_id) : parent::getHomePage();

        // 页面diy元素
        $items = $detail['page_data']['items'];
        // 页面顶部导航
        isset($detail['page_data']['page']) && $items['page'] = $detail['page_data']['page'];
        // 获取动态数据
        $model = new self;

        foreach ($items as $key => $item) {
            unset($items[$key]['defaultData']);
            if ($item['type'] === 'window') {
                $items[$key]['data'] = array_values($item['data']);
            } else if ($item['type'] === 'product') {
                $items[$key]['data'] = $model->getProductList($user, $item);
            } else if ($item['type'] === 'coupon') {
                $items[$key]['data'] = $model->getCouponList($user, $item);
            } else if ($item['type'] === 'article') {
                $items[$key]['data'] = $model->getArticleList($item);
            } else if ($item['type'] === 'special') {
                $items[$key]['data'] = $model->getSpecialList($item);
            } else if ($item['type'] === 'store') {
                $items[$key]['data'] = $model->getStoreList($item);
            } else if ($item['type'] === 'seckillProduct') {
                // 如果没有活动，则不显示
                $item_data = $model->getSeckillList($item,$user);
                if(empty($item_data)){
                    unset($items[$key]);
                }else{
                    $items[$key]['data'] = $item_data;
                }
            } else if ($item['type'] === 'assembleProduct') {
                // 如果没有活动，则不显示
                $item_data = $model->getAssembleList($item,$user);
                if(empty($item_data)){
                    unset($items[$key]);
                }else{

                    $items[$key]['data'] = $item_data;
                }
            } else if ($item['type'] === 'bargainProduct') {
                // 如果没有活动，则不显示
                $item_data = $model->getBargainList($item,$user);
                if(empty($item_data)){
                    unset($items[$key]);
                }else{
                    $items[$key]['data'] = $item_data;
                }
            } else if ($item['type'] === 'wxlive') {
                $items[$key]['data'] = $model->getWxLiveList($item);
            } else if ($item['type'] === 'option') {
                $items[$key] = $model->getProductListFromOption($user, $item);
            }
        }

        //新增链接（临时）
       // dd($items);
        foreach ($items as $k => $v){
            if ($v["type"] == 'navBar'){
                foreach ($v["data"] as $key => $value){
                    if ($value["text"] == '打卡'){
                        $items[$k]["data"][$key]["linkUrl"] = "pages/plus/signin_adv/signin_adv";
                    }
                    if ($value["text"] == "营地"){
                        $items[$k]["data"][$key]["linkUrl"] = "/pages/user/article/article?dataLink=category";
                    }
                    if ($value["text"] == "科普"){
                        $items[$k]["data"][$key]["linkUrl"] = "pages/user/article/article";
                    }
                }
                
            }
        }

    //    dd($items);
        return ['page' => $items['page'], 'items' => $items];
    }

    /**
     * 商品组件：获取商品列表
     */
    private function getProductListFromOption($user, $item)
    {
        // 获取商品数据
        $model = new ProductModel;
        
        if(count($item['data'])<4){
            $item['style']['rowsNum']=count($item['data']);
        }
        
    
        foreach ($item['data'] as $key => $val) {

           if($val['source']=='categorys'){
                $productList = $model->getList([
                    'type' => 'sell',
                    'category_id' => $val['category'],
                    'sortType' => $val['productSort'],
                    'list_rows' => $val['showNum']
                ], $user);

                if ($productList->isEmpty()) $item['data'][$key]['goodsdata'] = [];
                $data = [];
                foreach ($productList as $product) {
                    $show_sku = ProductModel::getShowSku($product);
                    $direct_commission =  $model->getDirectCommission($user,$product);
                    $data[] = [
                        'product_id' => $product['product_id'],
                        'product_name' => $product['product_name'],
                        'selling_point' => $product['selling_point'],
                        'image' => $product['image'][0]['file_path'],
                        'product_image' => $product['image'][0]['file_path'],
                        'product_price' => $show_sku['product_price'],
                        'line_price' => $show_sku['line_price'],
                        'product_sales' => $product['product_sales'],
                        'direct_commission'=>!empty($direct_commission)?$direct_commission:$product['direct_commission'],
                        'show_commission'=>!empty($direct_commission)?1:0,
                    ];
                }
                $item['data'][$key]['goodsdata'] = $data;

           }

        }

        return $item;
    }

    /**
     * 商品组件：获取商品列表
     */
    private function getProductList($user, $item)
    {
        // 获取商品数据
        $model = new ProductModel;
        if ($item['params']['source'] === 'choice') {
            // 数据来源：手动
            $productIds = array_column($item['data'], 'product_id');
            $productList = $model->getListByIdsFromApi($productIds, $user);
        } else {
            // 数据来源：自动
            $productList = $model->getList([
                'type' => 'sell',
                'category_id' => $item['params']['auto']['category'],
                'sortType' => $item['params']['auto']['productSort'],
                'list_rows' => $item['params']['auto']['showNum']
            ], $user);
        }
        if ($productList->isEmpty()) return [];
        // 格式化商品列表
        $data = [];
        foreach ($productList as $product) {
            //每个商品的购买人头像
            $list = $this->buyImg($product['product_id']);
            //每个商品分类小图
            $file_path = $this->categoryImage($product['category_id']);

            if(empty($product['sale_time']) ){
                $product['sale_time'] = '';
            }else{
                $time = '';
                $time= date('m',$product['sale_time']).'月';
                $time.= date('d',$product['sale_time']).'日';
                $product['sale_time']=$time;
            }
            if(empty($product['delivery_time']) ){
                $product['delivery_time'] = '';
            }else{
//                $product['delivery_time']= date('m-d',$product['delivery_time']).'号';
                $time1 = '';
                $time1= date('m',$product['delivery_time']).'月';
                $time1.= date('d',$product['delivery_time']).'日';
                $product['delivery_time']=$time1;
            }

            $show_sku = ProductModel::getShowSku($product);
            $direct_commission =  $model->getDirectCommission($user,$product);
            $data[] = [
                'product_id' => $product['product_id'],
                'buy_auth' => $product['buy_auth'],
                'product_name' => $product['product_name'],
                'selling_point' => $product['selling_point'],
                'image' => $product['image'][0]['file_path'],
                'product_image' => $product['image'][0]['file_path'],
                'product_price' => $show_sku['product_price'],
                'line_price' => $show_sku['line_price'],
                'product_sales' => $product['product_sales'],
                'direct_commission'=>!empty($direct_commission)?$direct_commission:$product['direct_commission'],
                //添加总库存跟已售数量
                'product_stock'=>$product['product_stock'],
                'sales_actual'=>$product['sales_actual'],
                'avatarUrl'=>$list,
                'sale_time'=>$product['sale_time'],//预售时间
                'delivery_time'=>$product['delivery_time'],//发货时间
                'categoryImage'=>$file_path,//分类按钮图片
                'category_id'=>$product['category_id'],//分类ID
                'show_commission'=>!empty($direct_commission)?1:0,

            ];
        }
        return $data;
    }

    /**
     * 优惠券组件：获取优惠券列表
     */
    private function getCouponList($user, $item)
    {
        // 获取优惠券数据
        return (new Coupon)->getList($user, $item['params']['limit'], true);
    }

    /**
     * 文章组件：获取文章列表
     */
    private function getArticleList($item)
    {
        // 获取文章数据
        $model = new Article;
        $articleList = $model->getList($item['params']['auto']['category'], $item['params']['auto']['showNum']);
        return $articleList->isEmpty() ? [] : $articleList->toArray()['data'];
    }

    /**
     * 头条快报：获取头条列表
     */
    private function getSpecialList($item)
    {
        // 获取头条数据
        $model = new Article;
        $articleList = $model->getList($item['params']['auto']['category'], $item['params']['auto']['showNum']);
        return empty($articleList['data']) ? [] : $articleList->toArray()['data'];
    }

    /**
     * 线下门店组件：获取门店列表
     */
    private function getStoreList($item)
    {
        // 获取商品数据
        $model = new StoreModel;
        if ($item['params']['source'] === 'choice') {
            // 数据来源：手动
            $storeIds = array_column($item['data'], 'store_id');
            $storeList = $model->getListByIds($storeIds);
        } else {
            // 数据来源：自动
            $storeList = $model->getList(null, false, false, $item['params']['auto']['showNum']);
        }
        if ($storeList->isEmpty()) return [];
        // 格式化商品列表
        $data = [];
        foreach ($storeList as $store) {
            $data[] = [
                'store_id' => $store['store_id'],
                'store_name' => $store['store_name'],
                'logo_image' => $store['logo']['file_path'],
                'phone' => $store['phone'],
                'region' => $store['region'],
                'address' => $store['address'],
            ];
        }
        return $data;
    }

    /**
     * 获取限时秒杀
     */
    private function getSeckillList($item,$user)
    {
        // 获取秒杀数据
        $seckill = SeckillActiveModel::getActive();
        $model = new ProductModel();
        if($seckill){
            $product_model = new SeckillProductModel;
            $seckill['product_list'] = $product_model->getProductList($seckill['seckill_activity_id'], $item['params']['showNum']);
            foreach ($seckill['product_list'] as &$p) {
                $product_info = $model->detail($p['product']['product_id']);
                $direct_commission =   $model->getDirectCommission($user,$product_info,$p['seckill_price']);
                $p['direct_commission'] = empty($direct_commission)?0:round($direct_commission,2);
            }
        }
        return $seckill;
    }

    /**
     * 获取限时拼团
     */
    private function getAssembleList($item,$user)
    {
        // 获取拼团数据
        $assemble = AssembleActiveModel::getActive();
        $model = new ProductModel();
        if($assemble){
            $assemble->visible(['assemble_activity_id','title', 'start_time', 'end_time','assemble_type']);
            $product_model = new AssembleProductModel;
            $assemble['product_list'] = $product_model->getProductList($assemble['assemble_activity_id'], $item['params']['showNum']);
            $model_p = new ProductModel();
            foreach ($assemble['product_list'] as &$p) {
                $p['buy_auth'] = $model_p->checkBuyAuth($p['product'],$user);
                $product_info = $model->detail($p['product']['product_id']);
                $direct_commission =   $model->getDirectCommission($user,$product_info,$p['assemble_price']);
                $p['direct_commission'] = empty($direct_commission)?0:round($direct_commission,2);
                $p['assemble_type']=$assemble['assemble_type'];
            }
            unset($p);

        }
        return $assemble;
    }

    /**
     * 获取限时砍价
     */
    private function getBargainList($item,$user)
    {
        // 获取拼团数据
        $bargain = BargainActiveModel::getActive();
        $model = new ProductModel();
        if($bargain){
            $bargain->visible(['bargain_activity_id','title', 'start_time', 'end_time']);
            $product_model = new BargainProductModel;
            $bargain['product_list'] = $product_model->getProductList($bargain['bargain_activity_id'], $item['params']['showNum']);
            foreach ($bargain['product_list'] as &$product) {
                $product_info = $model->detail($product['product']['product_id']);
                $direct_commission =   $model->getDirectCommission($user,$product_info,$product['bargain_price']);
                $product['direct_commission'] = empty($direct_commission)?0:round($direct_commission,2);
            }
            unset($product);
        }
        return $bargain;
    }

    /**
     * 微信直播
     */
    private function getWxLiveList($item)
    {
        // 获取头条数据
        $model = new WxLive();
        $liveList = $model->getList($item['params']['showNum']);
        return $liveList->isEmpty() ? [] : $liveList->toArray()['data'];
    }

    /**
     * 查询购买人头像
     */
    public function buyImg($product_id)
    {
        $model = new OrderProduct();
        $userModel = new User();
       $list = $model->where('product_id','=',$product_id)
           ->field('user_id,order_id')
           ->distinct(true)
           ->group('user_id')
           ->limit(5)
           ->select();


       if(!empty($list)){
           $img=array();
           foreach ($list as $K=>$v){
               $img[] =$userModel->where('user_id','=',$list[$K]['user_id']) ->field('avatarUrl')->find();
           }
       }else{
           $img=0;
       }

            return $img;


    }

    /* 
    * 获取购买者的内容
    */
    public  function getWebSitePage($user_info,$supply_id)
    {
        
        $page_id = 0;
        if(empty($supply_id)){

            if($user_info){
                //如果自身已有名片则查看自身的供应商
                $card_setting = SettingModel::getItem('card');
                
                if(isset($card_setting['is_open_card'])&&!empty($card_setting['is_open_card'])){
    
                    $card_model = new CardModel();
                    
                    $card_info = $card_model->getSelfCard($user_info['user_id']);
                    
                    $supply_id = $card_info['supply_id'];
                }
            }
        } 
		
        $page_info = $this->where('supply_id',$supply_id)->where('page_type', '50')->where('is_delete',0)->find();

        if(!empty($page_info)){
            $page_id = $page_info['page_id'];
        }
        return  $page_id;
    }

    /*
    * 获取分类按钮图片
    */
    public function categoryImage($category_id){
        $model = new CategoryModel;
        $detail = $model->detailWithImage(['category_id' => $category_id]);
        //判断有没有按钮图片
        $file_path = '';
        if($detail['image_id_1'] != 0){
            $UploadFileModel = new UploadFileModel;
            $data = $UploadFileModel->where('file_id','=',$detail['image_id_1'])->find();
            $file_path = $data;
        }
        return $file_path;
    }
}
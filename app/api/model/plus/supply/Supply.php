<?php

namespace app\api\model\plus\supply;

use app\api\controller\Controller;
use app\common\exception\BaseException;
use app\common\model\plus\supply\Supply as SupplyModel;
use app\api\model\plus\agent\Order as AgentOrderModel;
use app\common\enum\order\OrderTypeEnum;
use app\api\model\plus\card\Card as CardModel;
use app\common\model\user\UserBrowseRecords as UserBrowseRecordsModel;
use app\common\model\settings\Setting as SettingModel;

/**
 * 供应商模型
 */
class Supply extends SupplyModel
{
    /**
     * 隐藏字段
     */
    protected $hidden = [
        'shop_user',
    ];

    /**
     * 创建订单记录
     */
    public static function createOrder($order, $order_type = OrderTypeEnum::MASTER)
    {
        // 供应商模型
        $model = new self;
        $data = [];
        foreach ($order['product'] as $product) {
        	if (empty($product['supply_id'])) {
        		continue;
        	}
        	// 获取供应商
        	$supply = $model->find($product['supply_id']);
        	if (empty($supply['user_id'])) {
        		continue;
        	}
        	$data[]=[
	            'user_id' => $order['user_id'],
	            'order_id' => $order['order_id'],
	            'order_type' => $order_type,
	            'type' => 'supply',
	            'ower_id' => $supply['user_id'],
	            'product_id' => $product['product_id'],
	            'product_sku_id' => $product['product_sku_id'],
	            'order_price' => $product['product_supply_price']*$product['total_num'],
	            'is_settled' => 0,
	            'app_id' => $order['app_id']?$order['app_id']:$model::$app_id
	        ];
        	
        }
        if (!empty($data)) {
        	$agent_order_model = new AgentOrderModel();
        	$agent_order_model->saveAll($data);
        }
        
	}
	
	/* 
	* 获取设置中关联的供应商其他相关信息
	*/
	public function getSupplyBySetting($user_info)
	{	
		$model = $this;
		$supply_id = 0;

		//查看自身上次查看过对应的供应商
		if($user_info){
			//如果自身已有名片则查看自身的供应商
			$card_setting = SettingModel::getItem('card');
			if(isset($card_setting['is_open_card'])&&!empty($card_setting['is_open_card'])){
				$card_model = new CardModel();
				$card_info = $card_model->getSelfCard($user_info['user_id']);

				$supply_id = $card_info['supply_id'];
			}else{
			    $supply_model=new SupplyModel();
			    $where=[
			        'user_id'     =>$user_info['user_id'],
                    'is_delete'   =>0,
                    'app_id'      =>$user_info['app_id']
                ];
			    $supply_data =$supply_model->where($where)->find();
			    if($supply_data){
			        $supply_id=$supply_data['supply_id'];
                }
            }
			
			if(empty($supply_id)){
				//上次查看过的供应商
				$user_browse_records_model = new UserBrowseRecordsModel();
				$last_view_supply = $user_browse_records_model->getLastRecordByType('supply',$user_info['user_id']);
				$supply_id = $last_view_supply['data_id'];
			}    
		}

		//如果开启了默认的站点，本身也没有归属供应商
		if(empty($supply_id)){
			$supply_id = $model->getDefaultSupply();
		}

		return $supply_id;
	}

	public function supplyBrowseRecods($user_info,$supply_id)
	{	
		$supply_setting = SettingModel::getItem('supply');

		if(!isset($supply_setting['is_open'])||empty($supply_setting['is_open'])){
			return false;
		}
		if($user_info&&$supply_id){
            $browse_model  = new UserBrowseRecordsModel();
            $data = [
                'user_id'=>$user_info['user_id'],
                'type'=>'supply',
                'data_id'=>$supply_id
            ];
        	return  $browse_model->saveBrowseRecords($data);
		}
		return false;
	}
	/**
     * 菜单列表
     */
    public static function getMenus()
    {
        $menus = [
            'address' => [
                'name' => '收货地址',
                'path' => '/pages/user/address/address',
                'icon' => 'icon-dizhi1'
            ],
            'coupon' => [
                'name' => '领券中心',
                'path' => '/pages/coupon/coupon',
                'icon' => 'icon-youhuiquan1'
            ],
            'my_coupon' => [
                'name' => '我的优惠券',
                'path' => '/pages/user/my-coupon/my-coupon',
                'icon' => 'icon-youhuiquan-'
            ],
            'agent' => [
                'name' => '分销中心',
                'path' => '/pages/agent/index/index',
                'icon' => 'icon-fenxiao1'
            ],
            'bargain' => [
                'name' => '我的砍价',
                'path' => '/pages/user/my-bargain/my-bargain',
                'icon' => 'icon-kanjia'
            ],
        ];
        return $menus;
    }
}

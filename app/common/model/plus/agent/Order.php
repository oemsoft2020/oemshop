<?php

namespace app\common\model\plus\agent;

use app\common\model\BaseModel;
use app\common\enum\order\OrderTypeEnum;
use app\api\model\plus\supply\Supply as SupplyModel;
use app\common\model\product\Product;
use app\common\model\user\User as UserModel;
use app\common\model\plus\agent\Referee as RefereeModel;
use app\common\model\plus\agent\Rules as RulesModel;
use app\common\model\plus\agent\PlanSettle as AgentPlanSettle;

/**
 * 分销商订单模型
 */
class Order extends BaseModel
{
    protected $name = 'agent_order';
    protected $pk = 'id';

    /**
     * 订单模型初始化
     */
    public static function init()
    {
        parent::init();
        // 监听分销商订单行为管理
        $static = new static;
        event('AgentOrder', $static);
    }

    /**
     * 订单所属用户
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('app\common\model\user\User');
    }

    /**
     * 一级分销商用户
     * @return \think\model\relation\BelongsTo
     */
    public function agentFirst()
    {
        return $this->belongsTo('app\common\model\user\User', 'first_user_id');
    }

    /**
     * 二级分销商用户
     * @return \think\model\relation\BelongsTo
     */
    public function agentSecond()
    {
        return $this->belongsTo('app\common\model\user\User', 'second_user_id');
    }

    /**
     * 三级分销商用户
     * @return \think\model\relation\BelongsTo
     */
    public function agentThird()
    {
        return $this->belongsTo('app\common\model\user\User', 'third_user_id');
    }

    /**
     * 订单类型
     * @param $value
     * @return array
     */
    public function getOrderTypeAttr($value)
    {
        $types = OrderTypeEnum::getTypeName();
        return ['text' => $types[$value], 'value' => $value];
    }

    /**
     * 订单详情
     * @param $orderId
     * @param $orderType
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getDetailByOrderId($orderId, $orderType)
    {
        return static::where('order_id', '=', $orderId)
            ->where('order_type', '=', $orderType)
            ->where('type', '=', 'agent')
            ->find();
    }
    public static function getDetailsByOrderId($orderId, $orderType)
    {
        return static::where('order_id', '=', $orderId)
            ->where('order_type', '=', $orderType)
            ->select();
    }
    /**
     * 发放分销订单佣金
     * @param $order
     * @param int $orderType
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function grantMoney($order, $orderType = OrderTypeEnum::MASTER)
    {   
        // 订单是否已完成
        if ($order['order_status']['value'] != 30) {
            return false;
        }
        // 佣金结算天数
        $settleDays = Setting::getItem('settlement', $order['app_id'])['settle_days'];
        // 判断该订单是否满足结算时间 (订单完成时间 + 佣金结算时间) ≤ 当前时间
        $deadlineTime = $order['receipt_time'] + ((int)$settleDays * 86400);
        if ($settleDays > 0 && $deadlineTime > time()) {
            return false;
        }
        // 分销订单详情
        // $model = self::getDetailByOrderId($order['order_id'], $orderType);
        $models = self::getDetailsByOrderId($order['order_id'], $orderType);
        $planSettleModel = new AgentPlanSettle();
        foreach ($models as $detail) {
            if (!$detail || $detail['is_settled'] == 1) {
                continue;
            }
            if ($detail['type']=='agent') {
                    // 重新计算分销佣金
                $capital = $detail->getCapitalByOrder($order);
                // 发放一级分销商佣金
                $detail['first_user_id'] > 0 && User::grantMoney($detail['first_user_id'], $capital['first_money']);
                // 发放二级分销商佣金
                $detail['second_user_id'] > 0 && User::grantMoney($detail['second_user_id'], $capital['second_money']);
                // 发放三级分销商佣金
                $detail['third_user_id'] > 0 && User::grantMoney($detail['third_user_id'], $capital['third_money']);

                //发放代理级差佣金
                if(isset($capital['level_different_money'])&&!empty($capital['level_different_money'])){
                    foreach ($capital['level_different_money'] as $key => $level_different_money) {
                        foreach ($level_different_money as  $level_different_money_item) {
                            $remark = "代理级差返利,来源订单id:".$order['order_id']."商品id:".$key;
                            $level_different_money_item['user_id']>0 && User::grantMoney($level_different_money_item['user_id'], $level_different_money_item['money'],$remark);
                        }
                        
                    }
                }

                // 发放平推低推佣金
                if(isset($capital['commission_rules_money'])&&!empty($capital['commission_rules_money'])){
                    foreach ($capital['commission_rules_money'] as $key => $commission_rules_money_item) {
                       
                        $remark = "推荐返利,来源订单id:".$order['order_id']."商品id:".$key;
                        $params = [
                            'user_grade_id'=>$commission_rules_money_item['user_grade_id'],
                            'invited_user_id'=>$commission_rules_money_item['invited_user_id'],
                            'invited_grade_id'=>$commission_rules_money_item['invited_grade_id'],
                            'product_id'=>$commission_rules_money_item['product_id'],
                        ];
                        $commission_rules_money_item['user_id']>0 && User::grantMoney($commission_rules_money_item['user_id'], $commission_rules_money_item['money'],$remark,$params); 
                    }
                }

                 // 更新分销订单记录
                $detail->save([
                    'order_price' => $capital['orderPrice'],
                    'first_money' => $detail['first_user_id'] > 0? $capital['first_money']:0,
                    'second_money' => $detail['second_user_id'] > 0? $capital['second_money']:0,
                    'third_money' => $detail['third_user_id'] > 0? $capital['third_money']:0,
                    'is_settled' => 1,
                    'settle_time' => time(),
                    'level_different_money'=>json_encode($capital['level_different_money']),
                    'commission_rules_money'=>json_encode($capital['commission_rules_money'])
                ]);
                //更新待结算金额状态
                if($order['order_id']){
                    
                    $planSettleModel->updateStatus($order['order_id']);    
                }
                
            }else{
                if (!$detail || $detail['is_settled'] == 1) {
                    continue;
                }
                // 发放供应商佣金
                $detail['order_price'] > 0 && User::grantMoney($detail['ower_id'], $detail['order_price'],'商家货款结算');
                $detail->save([
                    'is_settled' => 1,
                    'settle_time' => time()
                ]);
            }
           
        }
        // if (!$model || $model['is_settled'] == 1) {
        //     return false;
        // }
        // // 重新计算分销佣金
        // $capital = $model->getCapitalByOrder($order);
        // // 发放一级分销商佣金
        // $model['first_user_id'] > 0 && User::grantMoney($model['first_user_id'], $capital['first_money']);
        // // 发放二级分销商佣金
        // $model['second_user_id'] > 0 && User::grantMoney($model['second_user_id'], $capital['second_money']);
        // // 发放三级分销商佣金
        // $model['third_user_id'] > 0 && User::grantMoney($model['third_user_id'], $capital['third_money']);
        // // 更新分销订单记录
        // return $model->save([
        //     'order_price' => $capital['orderPrice'],
        //     'first_money' => $model['first_user_id'] > 0? $capital['first_money']:0,
        //     'second_money' => $model['second_user_id'] > 0? $capital['second_money']:0,
        //     'third_money' => $model['third_user_id'] > 0? $capital['third_money']:0,
        //     'is_settled' => 1,
        //     'settle_time' => time()
        // ]);
    }

    /**
     * 计算订单分销佣金
     * @param $order
     * @return array
     */
    protected function getCapitalByOrder($order)
    {
        // 分销佣金设置
        $setting = Setting::getItem('commission', $order['app_id']);
        //分销基础设置
        $basic = Setting::getItem('basic', $order['app_id']);
        // 分销层级
        $level = $basic['level'];
        // 分销订单佣金数据
        $capital = [
            // 订单总金额(不含运费)
            'orderPrice' => bcsub($order['pay_price'], $order['express_price'], 2),
            // 一级分销佣金
            'first_money' => 0.00,
            // 二级分销佣金
            'second_money' => 0.00,
            // 三级分销佣金
            'third_money' => 0.00,
            //代理级差佣金
            'level_different_money'=>[],
            //按设置规则
            'commission_rules_money'=>[],
        ];
        // 计算分销佣金
        foreach ($order['product'] as $product) {
            // 判断商品存在售后退款则不计算佣金
            if ($this->checkProductRefund($product)) {
                continue;
            }
            // 商品实付款金额
            $productPrice = min($capital['orderPrice'], $product['total_pay_price']);
            // 计算商品实际佣金
            $productCapital = $this->calculateProductCapital($setting, $product, $productPrice,$order['user_id'], $basic);
            // 累积分销佣金
            $level >= 1 && $capital['first_money'] += $productCapital['first_money'];
            $level >= 2 && $capital['second_money'] += $productCapital['second_money'];
            $level == 3 && $capital['third_money'] += $productCapital['third_money'];

            //级差
            $different_data = $this->calculateOrderCommission($order['user_id'],$product);
            if($different_data){
                $capital['level_different_money'][$product['product_id']] = $different_data;
            }
           
            //规则
            $rules_data = $this->calculateCommissionByRules($order['user_id'],$product);
            if(!empty($rules_data)){
                $capital['commission_rules_money'][$product['product_id']] = $rules_data ;
            }
           
        }
        return $capital;
    }

    /**
     * 计算商品实际佣金
     * @param $setting
     * @param $product
     * @param $productPrice
     * @return float[]|int[]
     */
    private function calculateProductCapital($setting, $product, $productPrice,$user_id=0,$agent_basic=[])
    {
        // 判断是否开启商品单独分销
        if ($product['is_ind_agent'] == false) {
            // 全局分销比例
            return [
                'first_money' => $productPrice * ($setting['first_money'] * 0.01),
                'second_money' => $productPrice * ($setting['second_money'] * 0.01),
                'third_money' => $productPrice * ($setting['third_money'] * 0.01)
            ];
        }
        // 商品单独分销
        if ($product['agent_money_type'] == 10) {
            // 分销佣金类型：百分比
            return [
                'first_money' => $productPrice * ($product['first_money'] * 0.01),
                'second_money' => $productPrice * ($product['second_money'] * 0.01),
                'third_money' => $productPrice * ($product['third_money'] * 0.01)
            ];
        } else if($product['agent_money_type'] == 20){
            return [
                'first_money' => $product['total_num'] * $product['first_money'],
                'second_money' => $product['total_num'] * $product['second_money'],
                'third_money' => $product['total_num'] * $product['third_money']
            ];
        } else if($product['agent_money_type'] == 30){
            //详细独立佣金
            $independent_commission = json_decode($product['independent_commission'],true);
            $first_money = 0;
            $second_money = 0;
            $third_money  = 0;
            if(!empty($independent_commission)&&!empty($agent_basic)){
                $agentUser = $this->getAgentUserId($user_id,$agent_basic['level'],$agent_basic['self_buy']);
                if(!empty($agentUser['first_user_id'])){
                   $first_user = UserModel::detail($agentUser['first_user_id']);
                   $first_money =!empty($first_user)?$independent_commission[$first_user['grade_id']]['first_money']:0;
                }
                if(!empty($agentUser['second_user_id'])){
                    $second_user = UserModel::detail($agentUser['second_user_id']);
                    $second_money =!empty($second_user)? $independent_commission[$second_user['grade_id']]['second_money']:0;
                }
               
                if(!empty($agentUser['third_user_id'])){
                    $third_user = UserModel::detail($agentUser['third_user_id']);
                    $third_money =  !empty($third_user)?$independent_commission[$third_user['grade_id']]['third_money']:0;
                }
                
            }
            return [
                'first_money' =>   $product['total_num']* $first_money,
                'second_money' => $product['total_num']*$second_money,
                'third_money' => $product['total_num']*$third_money,
            ];
           
        }
    }

    /**
     * 验证商品是否存在售后
     * @param $product
     * @return bool
     */
    private function checkProductRefund($product)
    {
        return !empty($product['refund'])
            && $product['refund']['type']['value'] == 10
            && $product['refund']['is_agree']['value'] != 20;
    }

    /* 
    *计算商品的级差返利
    *商品信息 $product 
    *购买者　$user_id
    */

    private function calculateOrderCommission($user_id,$product)
    {
        $product_model  = new Product();
        $product_info = $product_model->detail($product['product_id']);
        $commission_type =  $product_info['commission_type']?json_decode($product_info['commission_type']):[];
        //商品是否有级差返利
        if(!in_array('different_level', $commission_type)){
            return [];
        }

        $buyer_info  = UserModel::detail($user_id);

        $fanli_level= [
			'9'=>0,
			'8'=>0,
			'7'=>0,
			'6'=>0,
			'5'=>0,
			'4'=>0,
			'3'=>0,
			'2'=>0,
			'1'=>0,
			'0'=>0
		];
		if($buyer_info){
			$fanli_level= [
				'9'=>$buyer_info['agent9'],
				'8'=>$buyer_info['agent8'],
				'7'=>$buyer_info['agent7'],
				'6'=>$buyer_info['agent6'],
				'5'=>$buyer_info['agent5'],
				'4'=>$buyer_info['agent4'],
				'3'=>$buyer_info['agent3'],
				'2'=>$buyer_info['agent2'],
				'1'=>$buyer_info['agent1'],
				'0'=>$buyer_info['agent0']
			];
        }
        // $product['is_alone_grade'] && isset($product['alone_grade_equity'][$user['grade_id']])
        //当前返利链上拥有的上级对应的折扣价
        $level_array=[];
        $user_model = new UserModel();
      
		foreach ($fanli_level as $key => $value) {
			if($key==$buyer_info['grade']['level']){
				$value = $user_id;
			}
			if($value){
                $current_agent = $user_model->detail($value);
                
				$level_array[$key] = isset($product_info['alone_grade_equity'][$current_agent['grade_id']])?$product_info['alone_grade_equity'][$current_agent['grade_id']]:0;
			}
        }

        krsort($level_array);	
		$level_array = array_filter($level_array);
		$level_copy_array= $level_array;
		
		$level_differential_array = [];
		foreach ($level_array as $key => $value) {

			unset($level_copy_array[$key]);
			$item = array_shift($level_copy_array);
			if($item){
				$level_differential_array[$key] =abs($value - $item);
			}else{
				$level_differential_array[$key] = 0;
			}	
        }
        
        $level_differential_data = [];
        foreach ($fanli_level as $key => $value) {
			if(isset($level_differential_array[$key])&&$key>$buyer_info['grade']['level']){
                $differential = $level_differential_array[$key];
                $level_differential_data[] = [
                    'product_id'=>$product['product_id'],
                    'user_id'=>$value,
                    'money'=>$differential*$product['total_num'],
                    'total_num'=>$product['total_num']
                ];
			}
        }
        
        return $level_differential_data;

    }

    /* 
    *按照分销设置计算推荐返利
    *购买者 $user_id 
    */

    public function calculateCommissionByRules($user_id,$product)
    {

        //商品是否有推荐返利
        $product_model  = new Product();
        $product_info = $product_model->detail($product['product_id']);
        $commission_type =  $product_info['commission_type']?json_decode($product_info['commission_type']):[];
        if(!in_array('commission_rules', $commission_type)){
            return [];
        }

        $buyer_info  = UserModel::detail($user_id);
        $referee_id  = RefereeModel::getRefereeUserId($user_id,1);
        if(empty($referee_id)){
            return [];
        }
        $referee_info = UserModel::detail($referee_id);

        $rules_info =  (new RulesModel())->getCommissionRules($referee_info['grade_id'],$buyer_info['grade_id'],$product['product_id']);
        if(empty($rules_info)){
            return [];
        }
        $where = [
            'user_grade_id'=>$referee_info['grade_id'],
            'user_id'=>$referee_id,
            'invited_grade_id'=>$buyer_info['grade_id'],
            'invited_user_id'=>$user_id,
            'product_id'=>$product['product_id']
        ];
        $capital_model= new Capital();
        $count = $capital_model->where($where)->count();
        if($count>=$rules_info['num']){
            return [];
        }

        $rules_commission_data = [
            'user_id'=>$referee_id,
            'user_grade_id'=>$referee_info['grade_id'],
            'invited_user_id'=>$user_id,
            'invited_grade_id'=>$buyer_info['grade_id'],
            'product_id'=>$product['product_id'],
            'money'=>$rules_info['money']*$product['total_num'],
            'total_num'=>$product['total_num']
        ];
        return $rules_commission_data;

    }

    /* 
    * 处理数据
    */
    public function formatData($order)
    {   
        $userModel  = new UserModel();
        $productModel = new Product();
        foreach ($order as $key => &$order_item) {
            
            $order_item['different_level'] = $order_item['commission_rules'] =0;
            if($order_item['level_different_money']){
                $different_level =[];
                $level_different_money = json_decode($order_item['level_different_money'],true);
                foreach ($level_different_money as $key => $level_different_money_item) {
                    foreach ($level_different_money_item as $key => $item) {
                        $user_info = $userModel->detail($item['user_id']);
                        $different_level[$item['user_id']]['user'] = $user_info;
                        if(!isset($different_level[$item['user_id']]['money'])){
                            $different_level[$item['user_id']]['money'] = 0; 
                        }
                        $different_level[$item['user_id']]['money'] += $item['money'];
                       
                        $item['product_info']  = $productModel->detail($item['product_id']);
                        $different_level[$item['user_id']]['product_detail'][] = $item;
                    }
                }
                $order_item['different_level'] = $different_level;
            }
    
    
            if($order_item['commission_rules_money']){
                $commission_rules =[];
                $commission_rules_money = json_decode($order_item['commission_rules_money'],true);
                foreach ($commission_rules_money as $key => $commission_rules_money_item) {
                    $user_info = $userModel->detail($commission_rules_money_item['user_id']);
                    $commission_rules[$commission_rules_money_item['user_id']]['money'] = $commission_rules_money_item['money'];
                    $commission_rules[$commission_rules_money_item['user_id']]['user'] = $user_info;
                    $commission_rules_money_item['product_info']  = $productModel->detail($commission_rules_money_item['product_id']);
                    $commission_rules[$commission_rules_money_item['user_id']]['product_detail'][] = $commission_rules_money_item;
                    
                }
                $order_item['commission_rules'] = $commission_rules;
            }
        }
        unset($order_item);
       

        return $order;
        
    }


    /* 
    * 预计发放的佣金记录
    */
    public function settleMoneyRecord($order,$agentUser,$capital)
    {   
        $model  = new AgentPlanSettle();
        if($agentUser['first_user_id']&&$capital['first_money']){
            //一级分销预计佣金
            $data = [
                'user_id'=>$agentUser['first_user_id'],
                'money'=>$capital['first_money'],
                'order_id'=>$order['order_id'],
                'app_id'=>$order['app_id'],
            ];
            $model::add($data);
        }
        if($agentUser['second_user_id']&&$capital['second_money']){
            //二级分销预计佣金
            $data = [
                'user_id'=>$agentUser['second_user_id'],
                'money'=>$capital['second_money'],
                'order_id'=>$order['order_id'],
                'app_id'=>$order['app_id'],
            ];
            $model::add($data);
        }
        if($agentUser['third_user_id']&&$capital['third_money']){
            //三级分销预计佣金
            $data = [
                'user_id'=>$agentUser['third_user_id'],
                'money'=>$capital['third_money'],
                'order_id'=>$order['order_id'],
                'app_id'=>$order['app_id'],
            ];
            $model::add($data);
        }
        //级差预计佣金
        if(isset($capital['level_different_money'])&&!empty($capital['level_different_money'])){
            foreach ($capital['level_different_money'] as $key => $level_different_money) {
                foreach ($level_different_money as  $level_different_money_item) {
                    $data = [];
                    $desc = "代理级差返利,来源订单id:".$order['order_id']."商品id:".$key;
                    if( $level_different_money_item['user_id']>0){
                        $data = [
                            'user_id'=>$level_different_money_item['user_id'],
                            'describe'=>$desc,
                            'money'=>$level_different_money_item['money'],
                            'order_id'=>$order['order_id'],
                            'product_id'=>$key,
                            'app_id'=>$order['app_id'],
                        ];
                        $model::add($data);
                    }
                }
                
            }
        }

        // 预计推荐佣金
        if(isset($capital['commission_rules_money'])&&!empty($capital['commission_rules_money'])){
            foreach ($capital['commission_rules_money'] as $key => $commission_rules_money_item) {

                $data = [];
                $desc = "代理级差返利,来源订单id:".$order['order_id']."商品id:".$key;
                if( $commission_rules_money_item['user_id']>0){
                    $data = [
                        'user_id'=>$commission_rules_money_item['user_id'],
                        'describe'=>$desc,
                        'money'=>$commission_rules_money_item['money'],
                        'order_id'=>$order['order_id'],
                        'product_id'=>$key,
                        'app_id'=>$order['app_id'],
                    ];
                    $model::add($data);
                }
            }
        }

        return true;
 
    }

    /* 
    * 当前买家的所有上级分销商用户id
    */

    private function getAgentUserId($user_id, $level, $self_buy)
    {
        $agentUser = [
            'first_user_id' => $level >= 1 ? Referee::getRefereeUserId($user_id, 1, true) : 0,
            'second_user_id' => $level >= 2 ? Referee::getRefereeUserId($user_id, 2, true) : 0,
            'third_user_id' => $level == 3 ? Referee::getRefereeUserId($user_id, 3, true) : 0
        ];
        // 分销商自购
        if ($self_buy && User::isAgentUser($user_id)) {
            return [
                'first_user_id' => $user_id,
                'second_user_id' => $agentUser['first_user_id'],
                'third_user_id' => $agentUser['second_user_id'],
            ];
        }
        return $agentUser;
    }

}

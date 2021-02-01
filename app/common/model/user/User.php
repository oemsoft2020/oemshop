<?php


namespace app\common\model\user;

use app\common\model\BaseModel;
use app\common\model\plus\agent\Referee;
use app\common\model\plus\agent\Setting;
use app\common\model\user\PointsLog as PointsLogModel;
use app\common\model\plus\agent\User as AgentUserModel;
use app\common\model\user\BalanceLog as BalanceLogModel;
use app\common\model\product\Product;
use app\shop\model\plus\agent\Setting as AgentSetting;
use app\common\model\user\UserAchievement;
use app\api\model\plus\agent\Referee as RefereeModel;
use app\common\exception\BaseException;
use app\common\model\order\Order;
use app\common\model\order\OrderProduct;
use think\facade\Db;

/**
 * 用户模型
 */
class User extends BaseModel
{
    protected $pk = 'user_id';
    protected $name = 'user';

    /**
     * 关联会员等级表
     */
    public function grade()
    {
        return $this->belongsTo("app\\common\\model\\user\\Grade", 'grade_id', 'grade_id');
    }

    /**
     * 关联收货地址表
     */
    public function address()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->hasMany("app\\{$module}\\model\\user\\UserAddress", 'address_id', 'address_id');
    }

    /**
     * 关联收货地址表 (默认地址)
     */
    public function addressDefault()
    {
        return $this->belongsTo('UserAddress', 'address_id', 'address_id');
    }

    /**
     * 获取用户信息
     */
    public static function detail($where)
    {
        $model = new static;
        $filter = ['is_delete' => 0];
        if (is_array($where)) {
            $filter = array_merge($filter, $where);
        } else {
            $filter['user_id'] = (int)$where;
        }
        return $model->where($filter)->with(['address', 'addressDefault', 'grade'])->find();
    }

    /**
     * 获取用户信息
     */
    public static function detailByUnionid($unionid)
    {
        $model = new static;
        $filter = ['is_delete' => 0];
        $filter = array_merge($filter, ['union_id' => $unionid]);
        return $model->where($filter)->with(['address', 'addressDefault', 'grade'])->find();
    }

    /**
     * 指定会员等级下是否存在用户
     */
    public static function checkExistByGradeId($gradeId)
    {
        $model = new static;
        return !!$model->where('grade_id', '=', (int)$gradeId)
            ->where('is_delete', '=', 0)
            ->value('user_id');
    }

    /**
     * 累积用户总消费金额
     */
    public function setIncPayMoney($money)
    {
        return $this->where('user_id', '=', $this['user_id'])->inc('pay_money', $money)->update();
    }

    /**
     * 累积用户实际消费的金额 (批量)
     */
    public function onBatchIncExpendMoney($data)
    {
        foreach ($data as $userId => $expendMoney) {
            $this->where(['user_id' => $userId])->inc('expend_money', $expendMoney)->update();
        }
        return true;
    }

    /**
     * 累积用户的可用积分数量 (批量)
     */
    public function onBatchIncPoints($data)
    {
        foreach ($data as $userId => $expendPoints) {
            $this->where(['user_id' => $userId])->inc('points', $expendPoints)->update();
        }
        return true;
    }

    /**
     * 累积用户的可用积分
     */
    public function setIncPoints($points, $describe)
    {
        // 新增积分变动明细
        PointsLogModel::add([
            'user_id' => $this['user_id'],
            'value' => $points,
            'describe' => $describe,
        ]);

        // 更新用户可用积分
        $data['points'] = ($this['points'] + $points <= 0) ? 0 : $this['points'] + $points;
        // 用户总积分
        if($points > 0){
            $data['total_points'] = $this['total_points'] + $points;
        }
        return $this->where('user_id', '=', $this['user_id'])->update($data);
    }


    /**
    * 更新代理层级
    */
    public function updateUserAgent($user_id,$referee_id='')
    {
        set_time_limit(0);
        $user_info = $this->detail($user_id);
        if(empty($user_info)){
            return false;
        }
        $referee_model = new Referee();
        //上级
        if(empty($referee_id)){

            if(empty($user_info['referee_id'])){

                $condition = array('user_id'=>$user_id);
                $referee_info = $referee_model->where($condition)->find();
                if(!empty( $referee_info)){
                    $referee_id = $referee_info['agent_id'];
                }
            }else{
                $referee_id = $user_info['referee_id'];
            }

        }

        $parent_user_info = $this->detail($referee_id);

        $data = array();
		if (!empty($parent_user_info)) {
            $data['agent9'] = $parent_user_info['grade']['level']==9 ? $parent_user_info['user_id'] : $parent_user_info['agent9'];
			$data['agent8'] = $parent_user_info['grade']['level']==8 ? $parent_user_info['user_id'] : $parent_user_info['agent8'];
			$data['agent7'] = $parent_user_info['grade']['level']==7 ? $parent_user_info['user_id'] : $parent_user_info['agent7'];
			$data['agent6'] = $parent_user_info['grade']['level']==6 ? $parent_user_info['user_id'] : $parent_user_info['agent6'];
			$data['agent5'] = $parent_user_info['grade']['level']==5 ? $parent_user_info['user_id'] : $parent_user_info['agent5'];
			$data['agent4'] = $parent_user_info['grade']['level']==4 ? $parent_user_info['user_id'] : $parent_user_info['agent4'];
			$data['agent3'] = $parent_user_info['grade']['level']==3 ? $parent_user_info['user_id'] : $parent_user_info['agent3'];
			$data['agent2'] = $parent_user_info['grade']['level']==2 ? $parent_user_info['user_id'] : $parent_user_info['agent2'];
			$data['agent1'] = $parent_user_info['grade']['level']==1 ? $parent_user_info['user_id'] : $parent_user_info['agent1'];
            $data['agent0'] = $parent_user_info['grade']['level']==0 ? $parent_user_info['user_id'] : $parent_user_info['agent0'];

            switch ($user_info['grade']['level']) {
				case '9':
						$data['agent8'] = 0;
						$data['agent7'] = 0;
						$data['agent6'] = 0;
						$data['agent5'] = 0;
						$data['agent4'] = 0;
						$data['agent3'] = 0;
						$data['agent2'] = 0;
                        $data['agent1'] = 0;
                        $data['agent0'] = 0;
					break;
				case '8':
						$data['agent7'] = 0;
						$data['agent6'] = 0;
						$data['agent5'] = 0;
						$data['agent4'] = 0;
						$data['agent3'] = 0;
						$data['agent2'] = 0;
                        $data['agent1'] = 0;
                        $data['agent0'] = 0;
					break;
				case '7':
						$data['agent6'] = 0;
						$data['agent5'] = 0;
						$data['agent4'] = 0;
						$data['agent3'] = 0;
						$data['agent2'] = 0;
                        $data['agent1'] = 0;
                        $data['agent0'] = 0;
					break;
				case '6':
						$data['agent5'] = 0;
						$data['agent4'] = 0;
						$data['agent3'] = 0;
						$data['agent2'] = 0;
                        $data['agent1'] = 0;
                        $data['agent0'] = 0;
					break;
				case '5':
						$data['agent4'] = 0;
						$data['agent3'] = 0;
						$data['agent2'] = 0;
                        $data['agent1'] = 0;
                        $data['agent0'] = 0;
					break;
				case '4':
						$data['agent3'] = 0;
						$data['agent2'] = 0;
                        $data['agent1'] = 0;
                        $data['agent0'] = 0;
					break;
				case '3':
						$data['agent2'] = 0;
                        $data['agent1'] = 0;
                        $data['agent0'] = 0;
					break;
				case '2':
                        $data['agent1'] = 0;
                        $data['agent0'] = 0;
					break;
				case '1':
                        $data['agent0'] = 0;
					break;
				case '0':

					break;
				default:

					break;
			}
		}else{
			$data['agent9'] = 0;
			$data['agent8'] = 0;
			$data['agent7'] = 0;
			$data['agent6'] = 0;
			$data['agent5'] = 0;
			$data['agent4'] = 0;
			$data['agent3'] = 0;
			$data['agent2'] = 0;
            $data['agent1'] = 0;
            $data['agent0'] = 0;
        }
        $user_info->save($data);

        $condition = array('agent_id'=>$user_id);
        $child_arr = $referee_model->where($condition)->select();

		foreach ($child_arr as $v) {
			$this->updateUserAgent($v['user_id']);
        }

        return true;

    }

    /**
     * 查询用户列表
     */
    public function getUserList($params,$where){

        $where1 ='';
        if(!empty($params['search'])){
           $where1 =  $this->where('user_id', 'like', '%' . trim($params['search']) . '%')
                ->whereOr('mobile', 'like', '%' . trim($params['search']) . '%');
        }


        return  $this->where('user_id','not in',$where)
            ->where($where1)
            ->order('user_id','desc')
            ->paginate($params, false, [
                'query' => request()->request()
            ]);

    }

    /**
     * 操作用户余额
     * @Author   linpf
     * @DataTime 2020-10-21T14:58:45+0800
     * @param    string                   $user_id [用户id]
     * @param    integer                  $type    [1增加2扣除]
     * @param    float                    $money   [金额]
     * @param    array                    $ext     [扩展参数]
     * @return   [type]                            [description]
     */
    public function handleBalance($user_id = '',$type = 1,$money = 0.00,$order_sn = '')
    {
        $balanceLog_mod = new BalanceLogModel();
        if($type == 1){//增加余额
            $res = $this->where('user_id',$user_id)->inc('balance',$money)->update();

            if($res){
                // 添加日志
                $data['user_id'] = $user_id;    
                $data['scene'] = 30;    
                $data['money'] = $money;    
                $data['app_id'] = self::$app_id;    
                $data['create_time'] = time();
                $data['type'] = 'anchor_free';
                $data['remark'] = '主播获取的佣金@'.$order_sn;
                
                return $balanceLog_mod->insert($data);

            }else{
                return false;//余额增加失败
            }

        }elseif($type == 2){//扣除余额
            $res = $this->where('user_id',$user_id)->dec('balance',$money)->update();

            if($res){
                // 添加日志
                $data['user_id'] = $user_id;    
                $data['scene'] = 30;    
                $data['money'] = '-'.$money;    
                $data['app_id'] = self::$app_id;    
                $data['create_time'] = time();
                $data['type'] = 'supply_to_platform';
                $data['remark'] = '供应商结算给平台的货款@'.$order_sn;
                
                return $balanceLog_mod->insert($data);

            }else{
                return false;//余额增加失败
            }
        }else{
            return false;//非法操作
        }

    }

    /**
     * 累积用户及其上级业绩
     */
    public function setIncAchievement($orderinfo)
    {   
        $setting = AgentSetting::getItem('basic',$orderinfo['app_id']);

        if(!isset($setting['achievement'])||empty($setting['achievement'])){

            return false;
        }

        $product_model = new Product();
        
        $achievement_model = new UserAchievement();

        $referee_model = new Referee();

        //查找直属上级
        $user_info = $this->detail($this['user_id']);

        $referee_id = isset($user_info['referee_id'])?$user_info['referee_id']:0;
        if(empty($referee_id)){

            $condition = array('user_id'=>$this['user_id']);
            $referee_info = $referee_model->where($condition)->find();
            if(!empty( $referee_info)){
                $referee_id = $referee_info['agent_id'];
            }
        }

        foreach ($orderinfo['product'] as $product) {

            $product_detail = $product_model->where('product_id', $product['product_id'])->find();
            if(isset($product_detail['achievement'])&&!empty($product_detail['achievement'])){
                //自身加业绩
                $this->where('user_id', '=', $this['user_id'])->inc('achievement', $product_detail['achievement'])->update();
                $data = [
                    'user_id'=>$this['user_id'],
                    'order_id'=>$orderinfo['order_id'],
                    'flow_type'=>10,
                    'product_id'=>$product['product_id'],
                    'achievement'=>$product_detail['achievement'],
                    'describe'=>"用户".$user_info['nickName']."下单加业绩"
                ];
                $achievement_model->saveUserAchievementRecords($data);

                //上级加业绩
                if($referee_id){
                    $data['user_id'] = $referee_id;
                    $this->where('user_id', '=', $referee_id)->inc('achievement', $product_detail['achievement'])->update();
                    $achievement_model->saveUserAchievementRecords($data);
                }
            }
        }
       
        return true;

    }

    /**
     * 减少用户及其上级业绩
     */
    public function setDecAchievement($userId, $orderinfo,$describe='')
    {
        $setting = AgentSetting::getItem('basic',$orderinfo['app_id']);

        if(!isset($setting['achievement'])||empty($setting['achievement'])){
            
            return false;
        }

        $product_model = new Product();
        
        $achievement_model = new UserAchievement();

        $referee_model = new Referee();

        $user_info = $this->detail($userId);
        //找上级
        $referee_id = isset($user_info['referee_id'])?$user_info['referee_id']:0;
        if(empty($referee_id)){
    
            $condition = array('user_id'=>$userId);
            $referee_info = $referee_model->where($condition)->find();
            if(!empty( $referee_info)){
                $referee_id = $referee_info['agent_id'];
            }
        }

        foreach ($orderinfo['product'] as $product) {

            $product_detail = $product_model->where('product_id', $product['product_id'])->find();
            if($product_detail['achievement']){
                //自身减业绩
                $this->where('user_id', '=', $userId)->dec('achievement', $product_detail['achievement'])->update();
                $data = [
                    'user_id'=>$userId,
                    'order_id'=>$orderinfo['order_id'],
                    'flow_type'=>20,
                    'product_id'=>$product['product_id'],
                    'achievement'=>$product_detail['achievement'],
                    'describe'=>"用户".$user_info['nickName']."退款减业绩"
                ];
                $achievement_model->saveUserAchievementRecords($data);

                //上级减业绩
                if($referee_id){
                    $data['user_id'] = $referee_id;
                    $this->where('user_id', '=', $referee_id)->dec('achievement', $product_detail['achievement'])->update();
                    $achievement_model->saveUserAchievementRecords($data);
                }
            }
        }


        return true;

    }

    /* 
    * 绑定上级
    */
    public function bindReferee($user_id,$referee_id)
    {
        $model = $this;
        $data['referee_id'] = $referee_id;
        $user_info = $this->detail($user_id);

        if(empty($user_id)||empty($referee_id)||(!empty($user_info)&&!empty($user_info['referee_id']))){
            return false;
        }
        $level = isset($user_info['grade']['level'])?$user_info['grade']['level']:0;
        $where = [
            'user_id'=>$referee_id,
            'agent'.$level =>$user_id,
        ];
        $count =  $model->where($where)->count();
        //当前用户为推荐人的上级之一
        if($count>0){
            return false;
        }
        $res = true;
        $this->startTrans();
        try {
        
            if ($user_id && $referee_id > 0) {
                // 记录推荐人关系
                $res =  RefereeModel::createRelation($user_id, $referee_id);
                if($res){
                    $user_info->save(array_merge($data,[
                        'user_id'=>$user_id,
                        'app_id' => self::$app_id
                    ]));
                }
               
                //更新用户邀请数量
                $model->where('user_id', '=', $referee_id)->inc('total_invite')->update();
            }
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw new BaseException(['msg' => $e->getMessage()]);
        }
        return $res;
    }

    /* 
    * 获取上级
    */
    public function getUserReferee($user_id)
    {
        $user_info = $this->detail($user_id);
        $referee_model = new Referee();
        //上级
        $referee_id = 0;
        if(empty($referee_id)){

            if(empty($user_info['referee_id'])){

                $condition = array('user_id'=>$user_id);
                $referee_info = $referee_model->where($condition)->find();
                if(!empty( $referee_info)){
                    $referee_id = $referee_info['agent_id'];
                }
            }else{
                $referee_id = $user_info['referee_id'];
            }

        }
        return $referee_id;
    }

    /* 
    * 用户根据消费金额升级
    */
    public function upgradeUserGradeByPayMoney($user_id)
    {   
        $user_info = self::detail($user_id);
        $grade_model = new Grade();

        if(empty($user_info)){
            return false;
        }

        $where = [
            ['open_pay','=',1],
            ['upgrade_pay', '<=', $user_info['pay_money']],
            ['app_id','=',$user_info['app_id']]
        ];
        
        $grade_info = $grade_model->where($where)->order('level','desc')->find();
        
        if(empty($grade_info)){
            return false;
        }

        if($grade_info['grade_id'] == $user_info['grade_id']){
            return false;
        }
        if($grade_info['level']<=$user_info['grade']['level']){
            return false;
        }
        $data = [
            'grade_id'=>$grade_info['grade_id']
        ];
        $res = $user_info->save($data);
        if($res){
            $this->updateUserAgent($user_id);
            return true;
        }
        return false;
        
        
    }

    /* 
    *用户根据订单购买商品升级
    */
    public function upgradeUserGradeAfterPay($user_id)
    {   
        $user_info = self::detail($user_id);
        $grade_model = new Grade();

        if(empty($user_info)){
            return false;
        }

        $orderProductModel = new OrderProduct();

        $productList  = $orderProductModel->alias('op')
        ->join('order o', 'o.order_id = op.order_id', 'LEFT')
        ->where('o.user_id',$user_id)
        ->where('o.pay_status',20)
        ->where('o.order_status','in',['10','30'])
        ->column('op.product_id');

        $where = [
            ['open_goods','=',1],
            ['app_id','=',$user_info['app_id']]
        ];
        $gradeModel = new Grade();

        $gradeList = $gradeModel->where($where)->order('level','desc')->select();

        if(empty($gradeList)){
            return false;
        }
        foreach ($gradeList as $grade) {
            if(empty($grade['upgrade_goods_id'])){
                continue;
            }
            $has_buy_goods = array_intersect($productList,explode(',',$grade['upgrade_goods_id']));

            if($has_buy_goods){
                $grade_info = $grade;
                break;
            }
        }

        
        if(empty($grade_info)){
            return false;
        }

        if($grade_info['grade_id'] == $user_info['grade_id']){
            return false;
        }
        if($grade_info['level']<=$user_info['grade']['level']){
            return false;
        }
        $data = [
            'grade_id'=>$grade_info['grade_id']
        ];
        $res = $user_info->save($data);
        if($res){
            $this->updateUserAgent($user_id);
            return true;
        }
        return false;
        
        
    }
}
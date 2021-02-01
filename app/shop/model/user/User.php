<?php

namespace app\shop\model\user;

use app\shop\model\user\GradeLog as GradeLogModel;
use app\shop\model\user\BalanceLog as BalanceLogModel;
use app\common\model\user\User as UserModel;
use app\common\enum\user\grade\ChangeTypeEnum;
use app\common\enum\user\balanceLog\BalanceLogSceneEnum as SceneEnum;
use app\common\model\plus\agent\Setting as AgentSetting;
use app\shop\model\user\PointsLog as PointsLogModel;
use app\shop\model\plus\agent\User as AgentUserModel;
use app\shop\model\user\UserRegion as UserRegionModel;
use app\common\model\settings\Region as RegionModel;
use app\shop\model\plus\agent\Referee as AgentReferee;

/**
 * 用户模型
 */
class User extends UserModel
{
    /**
     * 获取当前用户总数
     */
    public function getUserTotal($day = null)
    {
        $model = $this;
        if (!is_null($day)) {
            $startTime = strtotime($day);
            $model = $model->where('create_time', '>=', $startTime)
                ->where('create_time', '<', $startTime + 86400);
        }
        return $model->where('is_delete', '=', '0')->count();
    }

    /**
     * 获取用户id
     * @return \think\Collection
     */
    public function getUsers($where = null)
    {
        // 获取用户列表
        return $this->where('is_delete', '=', '0')
            ->where($where)
            ->order(['user_id' => 'asc'])
            ->field(['user_id'])
            ->select();
    }

    /**
     * 获取用户列表
     */
    public static function getList($search, $grade_id, $reg_date, $gender, $limit)
    {
        $model = new static();
        //检索：用户ID手机号
        if (!empty($search)) {
            $model = $model->whereRaw("user_id=:user_id or mobile=:mobile or nickName like '%{$search}%'", ['user_id' => $search,'mobile'=>$search]);
        }
        // 检索：会员等级
        if ($grade_id > 0) {
            $model = $model->where('grade_id', '=', (int)$grade_id);
        }
        //检索：注册时间
        if (!empty($reg_date[0])) {
            $model = $model->whereTime('create_time', 'between', $reg_date);
        }
        // 检索：性别
        if (!empty($gender) && $gender > -1) {
            $model = $model->where('gender', '=', (int)$gender);
        }
        // 获取用户列表
        $list = $model->with(['grade'])->where('is_delete', '=', '0')
            ->order(['create_time' => 'desc'])
            ->hidden(['open_id', 'union_id'])
            ->paginate($limit, false, [
                'query' => \request()->request()
            ]);
        /* 新增地区 */
        $user_region_model = new UserRegionModel();
        foreach ($list as $k => $v){
            $region_list = $user_region_model->where(['user_id' => $v["user_id"],"app_id" => self::$app_id])->find();
            if ($region_list){
                $list[$k]["province_name"] = RegionModel::getNameById($region_list["province_id"]);
                $list[$k]["city_name"] = RegionModel::getNameById($region_list["city_id"]);
                $list[$k]["region_name"] = RegionModel::getNameById($region_list["region_id"]);
            }else{
                $list[$k]["province_name"] = "未知";
                $list[$k]["city_name"] = "未知";
                $list[$k]["region_name"] = "未知";
            }
        }
        return $list;
    }


    /**
     * 获取赠送优惠券模块用户列表
     */
    public static function getLists($params)
    {
        $model = new static();
        $grade_id = $params['grade_id'];
        $gender   = $params['sex'];
        $nickName = $params['nickName'];
        $mobile   = $params['mobile'];
        $limit    = $params['list_rows'];

       // var_dump($gender >-1);die;
        // 检索：会员等级
        if ($grade_id > 0) {
            $model = $model->where('grade_id', '=', (int)$grade_id);
        }
        // 检索：性别
        if ($gender!=''&&$gender >-1) {
            $model = $model->where('gender', '=', (int)$gender);
        }
        //检索：用户昵称
        if (!empty($nickName)) {
            $model = $model->where('nickName', 'like','%' . trim($nickName) . '%');
        }

        //检索：用户手机号
        if (!empty($mobile)) {
            $model = $model->where('mobile', 'like','%' . trim($mobile) . '%');
        }
        // 获取用户列表
        $list = $model->with(['grade'])->where('is_delete', '=', '0')
            ->order(['create_time' => 'desc'])
            ->hidden(['open_id', 'union_id'])
            ->paginate($limit, false, [
                'query' => \request()->request()
            ]);
        /* 新增地区 */
        $user_region_model = new UserRegionModel();
        foreach ($list as $k => $v){
            $region_list = $user_region_model->where(['user_id' => $v["user_id"],"app_id" => self::$app_id])->find();
            if ($region_list){
                $list[$k]["province_name"] = RegionModel::getNameById($region_list["province_id"]);
                $list[$k]["city_name"] = RegionModel::getNameById($region_list["city_id"]);
                $list[$k]["region_name"] = RegionModel::getNameById($region_list["region_id"]);
            }else{
                $list[$k]["province_name"] = "未知";
                $list[$k]["city_name"] = "未知";
                $list[$k]["region_name"] = "未知";
            }
        }
        return $list;
    }

    /**
     * 软删除
     */
    public function setDelete()
    {
        // 判断是否为分销商
        // if (AgentUserModel::isAgentUser($this['user_id'])) {
        //     $this->error = '当前用户为分销商，不可删除';
        //     return false;
        // }
        return $this->transaction(function () {
            // 删除用户推荐关系
            (new AgentUserModel)->onDeleteReferee($this['user_id']);
            // 删除上级业绩
            // 标记为已删除
            //$this->save(['is_delete' => 1]);
            return $this->delete();    
             
        });
    }

    /**
     * 编辑用户信息
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function editAdd($data){
        $data['create_time'] = strtotime($data['create_time']);
        $data['update_time'] = time();
        $user_id = $data["user_id"];
        $region["province_id"] = isset($data["province_id"])?$data["province_id"]:0;
        $region["city_id"] = isset($data["city_id"])?$data["city_id"]:0;
        $region["region_id"] = isset($data["region_id"])?$data["region_id"]:0;
        $region["user_id"] = $user_id;
        $region["app_id"] = self::$app_id;
        unset($data["user_id"],$data["grade"],$data["province_id"],$data["city_id"],$data["region_id"],$data["province_name"],$data["city_name"],$data["region_name"]);
        $region_list = (new UserRegionModel())->where(["app_id" => self::$app_id,"user_id" => $user_id])->find();
        try{
            $this->where('user_id',$user_id)->save($data);
            if($region["province_id"]>0||$region["city_id"]>0||$region["region_id"]>0){
                if ($region_list){
                    $region["update_time"] = time();
                    (new UserRegionModel())->where('user_region_id',$region_list["user_region_id"])->save($region);
                }else{
                    $region["create_time"] = time();
                    (new UserRegionModel())->insert($region);
                }
            }
            return true;
        }catch (\Exception $e){
            return false;
        }
    }

    /**
     * 新增记录
     */
    public function add($data)
    {   $data["app_id"] = self::$app_id;
        return $this->save($data);
    }

    /**
     * 修改记录
     */
    public function edit($data)
    {
        $data['create_time'] = strtotime($data['create_time']);
        $data['update_time'] = time();
        return $this->save($data);
    }

    /**
     * 修改用户等级
     */
    public function updateGrade($data)
    {
        if (!isset($data['remark'])) {
            $data['remark'] = '';
        }
        // 变更前的等级id
        $oldGradeId = $this['grade_id'];
        return $this->transaction(function () use ($oldGradeId, $data) {
            // 更新用户的等级
            $status = $this->save(['grade_id' => $data['grade_id']]);
            // 新增用户等级修改记录
            if ($status) {
                (new GradeLogModel)->save([
                    'user_id' => $this['user_id'],
                    'old_grade_id' => $oldGradeId,
                    'new_grade_id' => $data['grade_id'],
                    'change_type' => ChangeTypeEnum::ADMIN_USER,
                    'remark' => $data['remark'],
                    'app_id' => $this['app_id']
                ]);
            }
            return $status !== false;
        });
    }

    /**
     * 消减用户的实际消费金额
     */
    public function setDecUserExpend($userId, $expendMoney)
    {
        return $this->where(['user_id' => $userId])->dec('expend_money', $expendMoney)->update();
    }

    /**
     * 用户充值
     */
    public function recharge($storeUserName, $source, $data)
    {
        if ($source == 0) {
            return $this->rechargeToBalance($storeUserName, $data['balance']);
        } elseif ($source == 1) {
            return $this->rechargeToPoints($storeUserName, $data['points']);
        }
        return false;
    }

    /**
     * 用户充值：余额
     */
    private function rechargeToBalance($storeUserName, $data)
    {
        if (!isset($data['money']) || $data['money'] === '' || $data['money'] < 0) {
            $this->error = '请输入正确的金额';
            return false;
        }
        // 判断充值方式，计算最终金额
        if ($data['mode'] === 'inc') {
            $diffMoney = $this['balance'] + $data['money'];
        } elseif ($data['mode'] === 'dec') {
            $diffMoney = $this['balance'] - $data['money'] <= 0 ? 0 : $this['balance'] - $data['money'];
        } else {
            $diffMoney = $data['money'];
        }
        // 更新记录
        $this->transaction(function () use ($storeUserName, $data, $diffMoney) {
            // 更新账户余额
            $this->where('user_id', '=', $this['user_id'])->update(['balance' => $diffMoney]);
            // 新增余额变动记录
            BalanceLogModel::add(SceneEnum::ADMIN, [
                'user_id' => $this['user_id'],
                'money' => $diffMoney,
                'remark' => $data['remark'],
            ], [$storeUserName]);
        });
        return true;
    }

    /**
     * 用户充值：积分
     */
    private function rechargeToPoints($storeUserName, $data)
    {
        if (!isset($data['value']) || $data['value'] === '' || $data['value'] < 0) {
            $this->error = '请输入正确的积分数量';
            return false;
        }
        // 判断充值方式，计算最终积分
        if ($data['mode'] === 'inc') {
            $diffMoney = $this['points'] + $data['value'];
        } elseif ($data['mode'] === 'dec') {
            $diffMoney = $this['points'] - $data['value'] <= 0 ? 0 : $this['points'] - $data['value'];
        } else {
            $diffMoney = $data['value'];
        }
        // 更新记录
        $this->transaction(function () use ($storeUserName, $data, $diffMoney) {
            // 更新账户积分
            $this->where('user_id', '=', $this['user_id'])->update(['points' => $diffMoney]);
            // 新增积分变动记录
            PointsLogModel::add([
                'user_id' => $this['user_id'],
                'value' => $diffMoney,
                'describe' => "后台管理员 [{$storeUserName}] 操作",
                'remark' => $data['remark'],
            ]);
        });
        return true;
    }


    /**
     * 获取用户统计数量
     */
    public function getUserData($startDate = null, $endDate = null, $type)
    {
        $model = $this;
        if(!is_null($startDate)){
            $model = $model->where('create_time', '>=', strtotime($startDate));
        }
        if(is_null($endDate)){
            $model = $model->where('create_time', '<', strtotime($startDate) + 86400);
        }else{
            $model = $model->where('create_time', '<', strtotime($endDate) + 86400);
        }
        if($type == 'user_total' || $type == 'user_add'){
            return $model->count();
        } else if($type == 'user_pay'){
            return $model->where('pay_money', '>', '0')->count();
        } else if($type == 'user_no_pay'){
            return $model->where('pay_money', '=', '0')->count();
        }
        return 0;
    }


    //用户批量操作  修改等级
    public function BatchChangegrade($params,$user_ids)
    {
        if($params['grade_id']!=''&& $user_ids !=''){
            $user_ids = explode(',', $user_ids);
            $data=array();
            $data['grade_id']=$params['grade_id'];
            $result =  $this->where('user_id','in',$user_ids)
                ->save($data);
            if($user_ids){
                foreach ($user_ids as  $user_id) {
                    $this->updateUserAgent($user_id);
                }
            }

            return $result;
            
        }else{
            return false;
        }

        
    }
    //用户批量操作  批量禁用
    public function UserForbidden($user_ids)
    {

        if($user_ids !=''){
            $user_ids = explode(',', $user_ids);
            $data=array();
            $data['is_forbidden']=1;
            return $this->where('user_id','in',$user_ids)
                ->where('is_forbidden','=',0)
                ->save($data);
        }else{
            return false;
        }
    }

    //用户批量操作  批量解封
    public function UserRelieve($user_ids)
    {
        if($user_ids !=''){
            $user_ids = explode(',', $user_ids);
            $data=array();
            $data['is_forbidden']=0;
            return $this->where('user_id','in',$user_ids)
                ->where('is_forbidden','=',1)
                ->save($data);
        }else{
            return false;
        }
    }

    /* 
    * 后台绑定关系
    */
    public function bindRefereeByAdmin($user_id, $referee_id)
    {
        $this->startTrans();
        try {
            //清除当前会员的关系
            $data = [
                'referee_id'=>0,
                'agent0'=>0,
                'agent1'=>0,
                'agent2'=>0,
                'agent3'=>0,
                'agent4'=>0,
                'agent5'=>0,
                'agent6'=>0,
                'agent7'=>0,
                'agent8'=>0,
                'agent9'=>0,
            ];
            $user_info = $this::detail($user_id);
            $user_info->save($data);

            $setting = AgentSetting::getItem('basic');
            $agentUserModel = new AgentUserModel();
            $refereeModel = new AgentReferee;
            
            $agent_user =  $agentUserModel->find($user_id);
            if(!empty($agent_user)){
                $agent_user->delete();
            }
            // 删除用户推荐关系
            $oldReferee1Id = AgentReferee::getRefereeUserId($user_id, 1, true);
            $oldReferee2Id = AgentReferee::getRefereeUserId($user_id, 2, true);
            $newReferee1Id = AgentReferee::getRefereeUserId($referee_id, 1, true);
            $agentUserModel->onDeleteReferee($user_id);
            
            $user_model = new UserModel();
            $res = $user_model->bindReferee($user_id,$referee_id);

            if($res){

                if($setting['level']>=2){
                    // user_id 的一级下级为旧推荐人oldReferee1Id、新推荐人referee_id的二级下级,newReferee1Id的三级下级
                    // user_id 的二级下级为旧推荐人oldReferee1Id、新推荐人referee_id的三级下级
                    if($oldReferee1Id){
                        $team1Ids = $refereeModel->getTeamUserIds($user_id, 1);
                        $team2Ids = $refereeModel->getTeamUserIds($user_id, 2); 
                        if($team1Ids){

                            $refereeModel->where('user_id', 'in', $team1Ids)
                            ->where('agent_id',$oldReferee1Id)
                            ->update(['agent_id' => $referee_id]);

                            if($setting['level']==3){

                                $team2Ids && $refereeModel->where('user_id', 'in', $team2Ids)
                                ->where('agent_id',$oldReferee1Id)
                                ->update(['agent_id' => $referee_id]);
           
                                if($oldReferee2Id&&$newReferee1Id){
                                    $refereeModel->where('user_id', 'in', $team1Ids)
                                    ->where('agent_id',$oldReferee2Id)
                                    ->update(['agent_id' => $newReferee1Id]);
                                }
                            }
                        }
                        
                    }
                }
                $this->commit();
               
                return $res;
            }else{
                $this->rollback();
                return false; 
            }
           
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }      
    }
}

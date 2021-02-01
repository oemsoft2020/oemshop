<?php

namespace app\api\controller\user;

use app\api\controller\Controller;
use app\api\controller\plus\customer\Customer;
use app\api\model\plus\agent\Referee;
use app\api\model\plus\agent\Setting;
use app\api\model\plus\agent\User as AgentUserModel;
use app\api\model\plus\agent\Apply as AgentApplyModel;
use app\api\model\settings\Message as MessageModel;
use app\api\model\plus\customer\Customer as CustomerModel;
use app\common\model\plus\agent\Cash;
use app\common\model\plus\agent\PlanSettle;
use app\shop\model\plus\agent\Setting as AgentSetting;
use app\common\model\user\Grade as GradeModel;

/**
 * 分销中心
 */
class Agent extends Controller
{
    // 用户
    private $user;
    // 分销商
    private $agent;
    // 分销设置
    private $setting;

    /**
     * 构造方法
     */
    public function initialize()
    {
        // 用户信息
        $this->user = $this->getUser();
        // 分销商用户信息
        $this->agent = AgentUserModel::detail($this->user['user_id']);
        // 分销商设置
        $this->setting = Setting::getAll();
    }

    /**
     * 分销商中心
     */
    public function center()
    {
        $basic_setting =  AgentSetting::getItem('basic',$this->app_id);
        $is_open_achievement = isset($basic_setting['achievement'])?$basic_setting['achievement']:0;

        $planSettle = new PlanSettle();
        $to_be_settle_money =  $planSettle->countMoney($this->user['user_id']);

        $grade_mod = new GradeModel();
        // 判断当前用户是否是一级分销商
        // $agent_mod = new Referee();
        // $user_role = $agent_mod->where(['user_id'=>$this->user['user_id'],'level'=>1])->find();
        // 查询当前用户会员等级
        $grade_id = $this->getUser()['grade_id'];
        
        if(!empty($grade_id)){
            $grade_name = $grade_mod->where('grade_id',$grade_id)->value('name');
            if($grade_name == '高级合伙人'){
                $user_role = 1;
            }else{
                $user_role = 0;
            }
        }else{
            $user_role = 0;
        }

        return $this->renderSuccess('', [
            // 当前是否为分销商
            'is_agent' => $this->isAgentUser(),
            // 当前是否在申请中
            'is_applying' => AgentApplyModel::isApplying($this->user['user_id']),
            // 当前用户信息
            'user' => $this->user,
            // 是否是高级合伙人
            'is_best_role' => $user_role,
            // 分销商用户信息
            'agent' => $this->agent,
            // 背景图
            'background' => $this->setting['background']['values']['index'],
            // 页面文字
            'words' => $this->setting['words']['values'],
            // 是否开启客户资源功能  新增
            'is_open' => (new CustomerModel())->is_open($this->user["user_id"]),
            //是否开启业绩明细
            'is_open_achievement'=>$is_open_achievement,
            //预计待结算分销佣金
            'to_be_settle_money'=>$to_be_settle_money?$to_be_settle_money:'0.00'
        ]);
    }

    /**
     * 分销商申请状态
     */
    public function apply($referee_id = null, $platform= '')
    {
        // 推荐人昵称
        $referee_name = '平台';
        // 如果之前有关联分销商，则继续关联之前的分销商
        $has_referee_id = Referee::getRefereeUserId($this->user['user_id'], 1);
        if($has_referee_id > 0){
            $referee_id = $has_referee_id;
        }
        if ($referee_id > 0 && ($referee = AgentUserModel::detail($referee_id))) {
            $referee_name = $referee['user']['nickName'];
        }

        return $this->renderSuccess('', [
            // 当前是否为分销商
            'is_agent' => $this->isAgentUser(),
            // 当前是否在申请中
            'is_applying' => AgentApplyModel::isApplying($this->user['user_id']),
            // 推荐人昵称
            'referee_name' => $referee_name,
            // 背景图
            'background' => $this->setting['background']['values']['apply'],
            // 页面文字
            'words' => $this->setting['words']['values'],
            // 申请协议
            'license' => $this->setting['license']['values']['license'],
            // 如果来源是小程序, 则获取小程序订阅消息id.获取售后通知.
            'template_arr' => MessageModel::getMessageByNameArr($platform, ['agent_apply_user']),
        ]);
    }

    /**
     * 分销商提现信息
     */
    public function cash($platform = '')
    {
        // 如果来源是小程序, 则获取小程序订阅消息id.获取售后通知.
        $template_arr = MessageModel::getMessageByNameArr($platform, ['agent_cash_user']);

        $last_cash_info  =[];
        $cash_model  = new Cash();
        $last_cash_info = $cash_model->where('user_id',$this->user['user_id'])->order('create_time','desc')->find();
        return $this->renderSuccess('', [
            // 分销商用户信息
            'agent' => $this->agent,
            // 结算设置
            'settlement' => $this->setting['settlement']['values'],
            // 背景图
            'background' => $this->setting['background']['values']['cash_apply'],
            // 页面文字
            'words' => $this->setting['words']['values'],
            // 小程序消息
            'template_arr' => $template_arr,
            //上次填写的提现信息
            'last_cash_info'=>$last_cash_info?$last_cash_info:[]
        ]);
    }

    /**
     * 当前用户是否为分销商
     */
    private function isAgentUser()
    {
        return !!$this->agent && !$this->agent['is_delete'];
    }
     public function  supply_cash($platform = ''){
         // 如果来源是小程序, 则获取小程序订阅消息id.获取售后通知.
         $template_arr = MessageModel::getMessageByNameArr($platform, ['agent_cash_user']);

         $last_cash_info  =[];
         $cash_model  = new Cash();
         $last_cash_info = $cash_model->where('user_id',$this->user['user_id'])->order('create_time','desc')->find();

         $data['user'] = $this->getUser();
         return $this->renderSuccess('', [
             // 分销商用户信息
             'agent' => $data,
             // 结算设置
             'settlement' => $this->setting['settlement']['values'],
             // 背景图
             'background' => $this->setting['background']['values']['cash_apply'],
             // 页面文字
             'words' => $this->setting['words']['values'],
             // 小程序消息
             'template_arr' => $template_arr,
             //上次填写的提现信息
             'last_cash_info'=>$last_cash_info?$last_cash_info:[]
         ]);
     }

}
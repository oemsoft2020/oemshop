<?php

namespace app\shop\model\plus\agent;

use app\common\library\easywechat\AppWx;
use app\common\library\easywechat\AppMp;
use app\common\service\message\MessageService;
use app\common\service\order\OrderService;
use app\common\library\easywechat\WxPay;
use app\common\model\plus\agent\Cash as CashModel;
use app\shop\model\user\User as UserModel;
/**
 * 分销商提现明细模型
 */
class Cash extends CashModel
{
    /**
     * 获取器：申请时间
     */
    public function getAuditTimeAttr($value)
    {
        return $value > 0 ? date('Y-m-d H:i:s', $value) : 0;
    }

    /**
     * 获取器：打款方式
     */
    public function getPayTypeAttr($value)
    {
        return ['text' => $this->payType[$value], 'value' => $value];
    }

    /**
     * 获取分销商提现列表
     */
    public function getList($user_id = null, $apply_status = -1, $pay_type = -1, $search = '')
    {
        $model = $this;
        // 构建查询规则
        $model = $model->alias('cash')
            ->with(['user'])
//            ->field('cash.*, agent.real_name, agent.mobile, user.nickName, user.avatarUrl')
            ->field('cash.*, user.nickName, user.avatarUrl,user.name,user.mobile')
            ->join('user', 'user.user_id = cash.user_id')
//            ->join('agent_user agent', 'agent.user_id = cash.user_id')
            ->order(['cash.create_time' => 'desc']);
        // 查询条件
        if ($user_id > 0) {
            $model = $model->where('cash.user_id', '=', $user_id);
        }
//        if (!empty($search)) {
//            $model = $model->where('agent.real_name|user.mobile', 'like', '%' . $search . '%');
//        }
        if (!empty($search)) {
            $model = $model->where('user.nickName|user.mobile', 'like', '%' . $search . '%');
        }
        if ($apply_status > 0) {
            $model = $model->where('cash.apply_status', '=', $apply_status);
        }
        if ($pay_type > 0) {
            $model = $model->where('cash.pay_type', '=', $pay_type);
        }
        // 获取列表数据
        return $model->paginate(15, false, [
            'query' => \request()->request()
        ]);
    }

    /**
     * 分销商提现审核
     */
    public function submit($param)
    {
        $data = ['apply_status' => $param['apply_status']];
        if ($param['apply_status'] == 30) {
            $data['reject_reason'] = $param['reject_reason'];
        }
        // 更新申请记录
        $data['audit_time'] = time();
        self::update($data, ['id' => $param['id']]);
        // 提现驳回：解冻分销商资金
        if ($param['apply_status'] == 30) {
            User::backFreezeMoney($param['user_id'], $param['money']);
        }

        // 发送模板消息
        (new MessageService)->cash($this);
        return true;
    }

    /**
     * 确认已打款
     */
    public function money()
    {
        $this->startTrans();
        try {
            // 更新申请状态
            $data = ['apply_status' => 40, 'audit_time' => time()];
            self::update($data, ['id' => $this['id']]);

            // 更新分销商累积提现佣金
            User::totalMoney($this['user_id'], $this['money']);

            // 记录分销商资金明细
            Capital::add([
                'user_id' => $this['user_id'],
                'flow_type' => 20,
                'money' => -$this['money'],
                'describe' => '申请提现',
            ]);
            // 发送模板消息
           //(new Message)->withdraw($this);
            // 事务提交
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 分销商提现：微信支付企业付款
     */
    public function wechatPay()
    {
        // 微信用户信息
        $user = UserModel::detail($this['user_id']);
        // 生成付款订单号
        $orderNO = OrderService::createOrderNo();
        // 付款描述
        $desc = '分销商提现付款';
        // 微信支付api：企业付款到零钱
        $open_id = '';
        if($user['reg_source'] == 'mp'){
            $app = AppMp::getWxPayApp($user['app_id']);
            $open_id = $user['mpopen_id'];
        }else if($user['reg_source'] == 'wx'){
            $app = AppWx::getWxPayApp($user['app_id']);
            $open_id = $user['open_id'];
        }

        if($open_id == ''){
            $this->error = '未找到用户open_id';
            return false;
        }

        $WxPay = new WxPay($app);
        // 请求付款api
        if ($WxPay->transfers($orderNO, $open_id, $this['money'], $desc)) {
            // 确认已打款
            $this->money();
            return true;
        }
        return false;
    }

    /*
     *统计提现总数量
     */
    public function getAgentOrderTotal()
    {
        return $this->count('id');
    }

    /*
    *统计提现总数量
    */
    public function getAgentApplyTotal()
    {
        return $this->where('apply_status', '=', '10')->count();
    }
}
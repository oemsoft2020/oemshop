<?php

namespace app\api\controller\user;

use app\api\controller\Controller;
use app\common\model\user\Grade as GradeModel;
use app\api\model\settings\Setting as SettingModel;
use app\api\model\user\GradeOrder as OrderModel;
use app\api\model\plus\certification\Apply as ApplyModel;

/**
 * 我的订单
 */
class Grade extends Controller
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
     * 查找等级列表
     */
    public function getGrade()
    {
        $vars = SettingModel::getItem('memberSetting');
        $setting = $vars['auth_setting'];
        $model = new GradeModel;
        $where = [
            'grade_id'=>$setting['grade_id']
        ];
        $list =  $model->where($where)
            ->where('is_delete',0)
            ->where('level_money','>',0)
            ->order(['level' => 'desc'])
            ->select();
        $certification = ApplyModel::detail([
            'user_id' => $this->user['user_id']
        ]);
        $apply = [
            'is_open'=>$setting['is_open'],
            'can_apply'=>true
        ];
        if (!empty($setting['need_certification'])) {
            if (empty($certification)) {
                $apply['can_apply'] = false;
            }elseif ($certification['apply_status']!=20&&$setting['need_certification']==2) {
                $apply['can_apply'] = false;
            }
        }
        $order = $model = new OrderModel;
        $last_order = $order->with(['grade','oldgrade'])
                            ->where('user_id','=',$this->user['user_id'])
                            ->where('pay_status','=',20)
                            ->order('order_id desc')
                            ->limit(1)
                            ->find();
        return $this->renderSuccess('', compact('list','setting','apply','last_order'));
    }

     /**
     * @param $id
     * 等级购买
     * @return \think\response\Json
     */
    public function buy($id)
    {
        // 用户信息
        $user = $this->getUser();
        $params = $this->request->param();
        // 升级订单
        $model = new OrderModel;
        // 创建订单
        if (!$model->createOrder($user, $id, $params)) {
            return $this->renderError($model->getError() ?: '订单创建失败');
        }
        // 构建支付请求
        $payment = OrderModel::onOrderPayment($user, $model, $params['pay_type'], $params['pay_source']);
        // 返回结算信息
        return $this->renderSuccess(['success' => '支付成功,请等待审核通过', 'error' => '订单未支付'], [
            'order_id' => $model['order_id'],   // 订单id
            'pay_type' => $params['pay_type'],  // 支付方式,仅支持微信
            'payment' => $payment,               // 微信支付参数
        ]);
    }

    /**
     * 获取会员等级详情
     * @Author   linpf
     * @DataTime 2020-11-19T19:49:50+0800
     * @return   [type]                   [description]
     */
    public function getGradeInfo($grade_id)
    {
        // 会员等级详情
        $data = GradeModel::detail($grade_id);

        return $this->renderSuccess('', compact('data'));
    }

    /**
     * 获取会员申请状态
     * @Author   linpf
     * @DataTime 2020-11-20T11:15:09+0800
     * @return   [type]                   [description]
     */
    public function applyState()
    {
        $user = $this->getUser();
        $gradeOrder_mod = new OrderModel();

        if($user){
            $map['user_id'] = $user['user_id'];

            $data['going_status'] = $gradeOrder_mod->where($map)->where('order_status',10)->count();
            $data['reject_status'] = $gradeOrder_mod->where($map)->where('order_status',20)->count();
            $data['has_status'] = $gradeOrder_mod->where($map)->count();

            return $this->renderSuccess('', compact('data'));
        }

        return $this->renderError('请先登录');
    }
}
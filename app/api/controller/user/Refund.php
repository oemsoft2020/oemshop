<?php

namespace app\api\controller\user;

use app\api\controller\Controller;
use app\api\model\settings\Express as ExpressModel;
use app\api\model\order\OrderProduct as OrderProductModel;
use app\api\model\order\OrderRefund as OrderRefundModel;
use app\api\model\settings\Message as MessageModel;
use app\common\model\plus\agent\Referee as RefereeModel;
use app\common\model\plus\agent\User as UserModel;
use app\api\model\settings\Setting as SettingModel;

/**
 * 订单售后服务
 */
class Refund extends Controller
{
    // $user
    private $user;

    /**
     * 构造方法
     */
    public function initialize()
    {
        $this->user = $this->getUser();   // 用户信息
    }

    /**
     * 用户售后单列表
     */
    public function lists($state = -1)
    {
        $model = new OrderRefundModel;
        $postData =$this->postData();
        $list = $model->getList($this->user['user_id'], $postData['state'], $this->postData());
        return $this->renderSuccess('', compact('list'));
    }

    /**
     * 申请售后
     */
    public function apply($order_product_id, $platform = 'wx')
    {
        // 订单商品详情
        $detail = OrderProductModel::detail($order_product_id);
        if (isset($detail['refund']) && !empty($detail['refund'])) {
            return $this->renderError('当前商品已申请售后');
        }
        if ($this->request->isGet()) {
            // 如果来源是小程序, 则获取小程序订阅消息id.获取售后通知.
            $template_arr = MessageModel::getMessageByNameArr($platform, ['order_refund_user']);
            return $this->renderSuccess('', compact('detail', 'template_arr'));
        }
        // 新增售后单记录
        $model = new OrderRefundModel;
        if ($model->apply($this->user, $detail, $this->request->post())) {
            return $this->renderSuccess('提交成功');
        }
        return $this->renderError($model->getError() ?: '提交失败');
    }

    /**
     * 售后单详情
     */
    public function detail($order_refund_id, $platform = '')
    {
        // 售后单详情
        $detail = OrderRefundModel::detail([
            'user_id' => $this->user['user_id'],
            'order_refund_id' => $order_refund_id
        ]);
        if (empty($detail)) {
            return $this->renderError('售后单不存在');
        }
        /**
         * 判断是不是开启代理商弹窗功能用户的售后
         */
        $setting =SettingModel::getItem('homepopup');
        $setting['is_open']=  isset($setting['is_open']) ? $setting['is_open'] : false;
        $detail['is_open'] =$setting['is_open'];
       if($detail['agent_id'] !=0){
           $UserModel = new UserModel();
           $postData = $this->postData();
           $app_id = $postData['app_id'];
           $a = $UserModel->where('user_id',$detail['agent_id'])->field(['real_name','mobile'])->find();
           $agentUser = $UserModel->where('user_id',$this->user['user_id'])->field(['user_id'])->find();
           $agentUser['user_id']=  isset($agentUser['user_id']) ? $agentUser['user_id'] : '';
           $detail['real_name']=$a['real_name'];
           $detail['mobile']=$a['mobile'];
               $detail['agentUser']=$agentUser['user_id'];
       }
        // 物流公司列表
        $model = new ExpressModel();
        $expressList = $model->getAll();
        // 如果来源是小程序, 则获取小程序订阅消息id.获取售后通知.
        $template_arr = MessageModel::getMessageByNameArr($platform, ['order_refund_user']);
        return $this->renderSuccess('', compact('detail', 'expressList', 'template_arr'));
    }

    /**
     * 用户发货
     */
    public function delivery($order_refund_id)
    {
        // 售后单详情
        $model = OrderRefundModel::detail([
            'user_id' => $this->user['user_id'],
            'order_refund_id' => $order_refund_id
        ]);
        if ($model->delivery($this->postData())) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '提交失败');
    }

}
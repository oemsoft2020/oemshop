<?php

namespace app\api\controller\user;

use app\api\controller\Controller;
use app\api\model\order\Order as OrderModel;
use app\common\enum\order\OrderPayTypeEnum;
use app\common\model\user\Sms as SmsModel;
use app\api\model\user\UserWeb as UserModel;

/**
 * h5 web用户管理
 */
class Userweb extends Controller
{

    /**
     * 用户自动登录,默认微信小程序
     */
    public function login()
    {
        $model = new UserModel;
        $user_id = $model->login($this->request->post());
        if($user_id == 0){
            return $this->renderError($model->getError() ?:'登录失败');
        }
        return $this->renderSuccess('',[
            'user_id' => $user_id,
            'token' => $model->getToken()
        ]);
    }

    /**
     * pc端自动登录
     * @Author   linpf
     * @DataTime 2020-10-30T17:19:06+0800
     * @return   [type]                   [description]
     */
    public function autoLogin()
    {
        $model = new UserModel;
        $user_id = $model->autoLogin($this->request->get());
        if($user_id == 0){
            return $this->renderError($model->getError() ?:'登录失败');
        }
        return $this->renderSuccess('',[
            'user_id' => $user_id,
            'token' => $model->getToken()
        ]);
    }
    /**
     * 绑定手机号
     */
    public function bindMobile()
    {
        $model = new UserModel;
        if ($model->bindMobile($this->request->post())) {
            return $this->renderSuccess('绑定成功');
        }
        return $this->renderError($model->getError() ?:'绑定失败');
    }
    /**
     * 短信登录
     */
    public function sendCode($mobile)
    {
        $model = new SmsModel();
        if($model->send($mobile)){
            return $this->renderSuccess();
        }
        return $this->renderError($model->getError() ?:'发送失败');
    }

    public function payH5($order_id){
        $user = $this->getUser();
        // 订单详情
        $model = OrderModel::getUserOrderDetail($order_id, $user['user_id']);
        // 构建支付请求
        $payment = OrderModel::onOrderPayment($user, $model, OrderPayTypeEnum::WECHAT, 'payH5');

        $return_Url = urlencode(base_url()."h5/pages/order/myorder/myorder");
        return $this->renderSuccess('',[
            'order' => $model,  // 订单详情
            'mweb_url' => $payment['mweb_url'],
            'return_Url' => $return_Url
        ]);
    }
}

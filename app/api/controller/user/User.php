<?php

namespace app\api\controller\user;

use app\api\model\user\User as UserModel;
use app\api\controller\Controller;
use app\api\model\settings\Setting as SettingModel;
use app\api\model\user\UserWeb;

/**
 * 用户管理模型
 */
class User extends Controller
{
    /**
     * 用户自动登录,默认微信小程序
     */
    public function login()
    {
        $model = new UserModel;
        $user_id = $model->login($this->request->post());


        return $this->renderSuccess('',[

            'user_id' => $user_id,
            'token' => $model->getToken(),
            'is_phone' => $this->isGetPhone($user_id),
        ]);
    }


    /**
     * 判断是否需要授权手机号
     * @param $user_id
     * @return bool
     */
    private function isGetPhone($user_id)
    {
        $model = new UserModel;
        $user = UserModel::where(['user_id' => $user_id])->with(['address', 'addressDefault', 'grade'])->find();
        $settings = SettingModel::getItem('memberSetting');
        if (isset($settings['is_open_getphone'])) {
            if ($user['mobile'] != '') {
                return false;
            }
            return $settings['is_open_getphone'];
        }
        return false;
    }

    /**
     *
     * 获取用户手机号 peng
     * @return \think\response\Json
     * @throws \EasyWeChat\Kernel\Exceptions\DecryptException
     * @throws \app\common\exception\BaseException]
     */
    public function phone()
    {
        $model = new UserModel;
        $truePhone = $model->phone($this->request->post());
        if ($truePhone) {
            return $this->renderSuccess('获取手机号成功');
        }
    }


    /**
     * 当前用户详情
     */
    public function detail()
    {
        // 当前用户信息
        $userInfo = $this->getUser();
        return $this->renderSuccess(compact('userInfo'));
    }

    /**
     * 绑定手机号
     */
    public function bindMobile()
    {
        $model = $this->getUser();
        if ($model->bindMobile($this->request->post())) {
            return $this->renderSuccess('');
        }
        return $this->renderError('绑定失败');
    }

    /**
     * 完善用户信息
     * @return \think\response\Json
     * @throws \app\common\exception\BaseException
     */
    public function saveInfo()
    {
        $model = $this->getUser();
        if ($model->saveInfo($this->request->post())) {
            return $this->renderSuccess('');
        }
        return $this->renderError('修改失败');
    }

    /*
  * 搜寻上级
  */

    public function searchReferee()
    {
        $data = $this->postData();
        if (isset($data['referee_id']) && !empty($data['referee_id'])) {
            $user_info = UserModel::detail($data['referee_id']);
            return $this->renderSuccess('success', compact('user_info'));
        }

        return $this->renderError('绑定失败');
    }

    /*
    * 绑定上级
    */

    public function bindRefereeId()
    {
        $data = $this->postData();
        $user_info = $this->getUser(false);

        $user_model = new UserModel();
        $user_id = isset($user_info['user_id']) ? $user_info['user_id'] : 0;
        if ($this->request->isGet()) {
            //是否有上级
            $referee_id = $user_model->getUserReferee($user_id);

            if ($referee_id > 0) {
                return $this->renderSuccess('您已存在上级');
            }

        } else {

            if (isset($data['referee_id']) && !empty($data['referee_id'])) {

                if ($user_id == $data['referee_id']) {
                    return $this->renderError('上级id不可与自身id相同');
                }

                $res = $user_model->bindReferee($user_id, $data['referee_id']);

                return $this->renderSuccess('success');
            }

        }


    }

    /* 
    * 切换用户
    */
    public function changeUser()
    {
        $data =  $this->postData();
        
        if(!isset($data['user_id'])){

            return $this->renderError('切换用户id不可为空');
        }
        if(!isset($data['secret'])){
            return $this->renderError('切换秘钥不可为空');
        }
        $values = SettingModel::getItem('memberSetting');

        if($data['secret']!=$values['change_secret']){
            return $this->renderError('切换秘钥不正确');
        }
        $userWebModel = new UserWeb();
        $user_id = $userWebModel->autoLogin($data);
        if($user_id == 0){
            return $this->renderError($userWebModel->getError() ?:'登录失败');
        }
        return $this->renderSuccess('',[
            'user_id' => $user_id,
            'token' => $userWebModel->getToken()
        ]);
        
    }

    /**
     * 修改主播手机
     * @Author   linpf
     * @DataTime 2020-10-29T15:06:19+0800
     * @param    string                   $phone     [手机号码]
     * @return   [type]                              [description]
     */
    public function changePhone($phone = '')
    {
        if(empty($phone)){
            return $this->renderError('请填写手机号码');
        }

        $user_id = $this->getUser()['user_id'];

        $model = new UserModel;
        $res = $model->where('user_id',$user_id)->update(['mobile'=>$phone]);
        return $res ? $this->renderSuccess('修改成功') : $this->renderError('修改失败');
    }
}
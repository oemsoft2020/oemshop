<?php

namespace app\agent\controller;

use app\agent\model\shop\User;

/**
 * 商户认证
 */
class Passport extends Controller
{
    /**
     * 商户后台登录
     */
    public function login()
    {
        //登录前清空session
        session('kmdshop_agent', null);
        $user = $this->postData();
        $user['password'] = salt_hash($user['password']);
        $model = new User();
        $data= $model->checkLogin($user);
        unset($data['password']);
        if ($model->checkLogin($user)) {
            return $this->renderSuccess('登录成功', $data);
        }
        return $this->renderError($model->error?:'登录失败');
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        session('kmdshop_agent', null);
        return $this->renderSuccess('退出成功');
    }

    /*
   * 修改密码
   */
    public function editPass()
    {
        $model = new User();
        if ($model->editPass($this->postData(), $this->store['user'])) {
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError($model->getError()?:'修改失败');
    }
}

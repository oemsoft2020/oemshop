<?php

namespace app\agent\model\shop;

use app\common\model\shop\AgentUser as UserModel;

/**
 * 后台管理员登录模型
 */
class User extends UserModel
{
    // 保存错误信息
    public $error = '';

    /**
     *检查登录
     */
    public function checkLogin($user)
    {
        $where['user_name'] = $user['username'];
        $where['password'] = $user['password'];
        $where['is_delete'] = 0;
        //$where['is_system_agent'] = 1;

        if (!$user = $this->where($where)->with(['app','supply'])->find()) {
            return false;
        }
        if (empty($user['is_system_agent'])) {
            $this->error = '登录失败.';
            return false;
        }
        if (empty($user['app'])) {
            $this->error = '登录失败, 未找到应用信息';
            return false;
        }
        if ($user['app']['is_delete']) {
            $this->error = '登录失败, 当前应用已删除';
            return false;
        }
        if(isset($mod)){
            $user['path']=$mod['path'];
        }else{
            $user['path']='';
        }
        // 保存登录状态
        $this->loginState($user);
        return $user;
    }


    /*
    * 修改密码
    */
    public function editPass($data, $user)
    {
        $user_info = User::detail($user['shop_user_id']);
        if ($data['password'] != $data['confirmPass']) {
            $this->error = '密码错误';
            return false;
        }
        if ($user_info['password'] != salt_hash($data['oldpass'])) {
            $this->error = '两次密码不相同';
            return false;
        }
        $date['password'] = salt_hash($data['password']);
        $user_info->save($date);
        return true;
    }

}
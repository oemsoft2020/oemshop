<?php

namespace app\shop\model\shop;

use app\common\model\shop\LoginLog as LoginLogModel;
use app\common\model\shop\User as UserModel;
use app\shop\model\auth\Role as RoleModel;

/**
 * 后台管理员登录模型
 */
class User extends UserModel
{
    /**
     *检查登录
     */
    public function checkLogin($user)
    {
        $where['user_name'] = $user['username'];
        $where['password'] = $user['password'];
        $where['is_delete'] = 0;
        $model = new UserModel();
        $userdata=$model->where($where)->find();
        if(!empty($userdata)){
            if($userdata['is_super']!=1){
                $data=$this->alias('user')->join('shop_user_role user_role','user.shop_user_id=user_role.shop_user_id')->where('user.shop_user_id','=',$userdata['shop_user_id'])->find();
                if($data){
                    $role_id=$data['role_id'];
                    $mod = new RoleModel();
                    $mod=$mod->where('role_id','=',$role_id)->find();
                }

            }
        }

        if (!$user = $this->where($where)->with(['app','supply'])->find()) {
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
        // 写入登录日志
        LoginLogModel::add($where['user_name'], \request()->ip(), '登录成功');
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
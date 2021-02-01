<?php

namespace app\agent\model;

use app\common\exception\BaseException;
use app\common\model\shop\User as ShopModel;

class Shop extends ShopModel
{
    public $globalScope = [];

    public function app()
    {
        return $this->belongsTo('app\\agent\\model\\app\\App', 'app_id', 'app_id');
    }
    /**
     * 新增商家用户记录
     */
    public function add($app_id, $data)
    {
        if (self::checkExist($data['user_name'])) {
            $this->error = '商家用户名已存在';
            return false;
        }
        return $this->save([
            'user_name' => $data['user_name'],
            'password' => salt_hash($data['password']),
            'app_id' => $app_id,
            'system_agent_parent' => self::$app_id,
            'is_super' => 1
        ]);
    }

    /**
     * 商家用户详情
     */
    public static function detail($where, $with = [])
    {
        !is_array($where) && $where = ['app_id' => (int)$where];
        return static::where(array_merge(['is_delete' => 0], $where))->with($with)->find();
    }

    /**
     * 代理商登录
     */
    public function login($app_id)
    {
        // 验证用户名密码是否正确
        $user = self::detail(['app_id' => $app_id], ['app']);
        if (empty($user)) {
            throw new BaseException(['msg' => '超级管理员用户信息不存在']);
        }
        //代理商进入商城权限判断
        if ($user['system_agent_parent'] != self::$app_id) {
            throw new BaseException(['msg' => '您当前无权限访问']);
        }
        $this->loginState($user);
    }
}
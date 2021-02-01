<?php

namespace app\agent\controller;

use app\common\exception\BaseException;
use app\KmdController;

/**
 * 商户后台控制器基类
 */
class Controller extends KmdController
{
    /** @var array $store 商家登录信息 */
    protected $store;

    /** @var string $route 当前控制器名称 */
    protected $controller = '';

    /** @var string $route 当前方法名称 */
    protected $action = '';

    /** @var string $route 当前路由uri */
    protected $routeUri = '';

    /** @var string $route 当前路由：分组名称 */
    protected $group = '';

    /** @var string $route 当前路由：分组名称 */
    protected $menu = '';
    /** @var array $allowAllAction 登录验证白名单 */
    protected $allowAllAction = [
        // 登录页面
        '/passport/login',
    ];

    /**
     * 后台初始化
     */
    public function initialize()
    {
        // 商家登录信息
        $this->store = session('kmdshop_agent');
        // 当前路由信息
        $this->getRouteinfo();
        //  验证登录状态
        $this->checkLogin();
    }

    /**
     * 解析当前路由参数 （分组名称、控制器名称、方法名）
     */
    protected function getRouteinfo()
    {
        // 控制器名称
        $this->controller = strtolower($this->request->controller());
        $this->controller = str_replace(".","/",$this->controller);
        // 方法名称
        $this->action = Request()->action();
        // 控制器分组 (用于定义所属模块)
        $groupstr = strstr($this->controller, '.', true);
        $this->group = $groupstr !== false ? $groupstr : $this->controller;
        // 当前uri
        $this->routeUri =  '/' . $this->controller . '/' . $this->action;
    }

    /**
     * 验证登录状态
     */
    private function checkLogin()
    {
        // 验证当前请求是否在白名单
        if (in_array($this->routeUri, $this->allowAllAction)) {
            return true;
        }
        // 验证登录状态
        if (!empty($this->store) || $this->store['is_login'] == 1) {
            return true;
        }
        throw new BaseException(['code' => -1, 'msg' => 'not_login']);
        return false;
    }

}

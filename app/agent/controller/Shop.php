<?php

namespace app\agent\controller;

use app\agent\model\app\App as AppModel;
use app\agent\model\Shop as ShopModel;
use app\admin\model\Access as AccessModel;

class Shop extends Controller
{
    /**
     * 小程序列表
     */
    public function index()
    {
        $model = new AppModel;
        $list = $model->getList($this->postData());
        foreach ($list as &$item){
            
        	if($item['access_id']){
        		$item['access_id'] = json_decode($item['access_id']);
        	}else{
                $item['access_id'] = [];
            }
        }
        unset($item);
        return $this->renderSuccess('', compact('list'));
    }

    /**
     * 进入商城
     */
    public function enter($app_id)
    {
        $model = new ShopModel;
        $model->login($app_id);
        return redirect('/shop#/home?from=admin');
    }

    /**
     * 添加应用
     */
    public function add()
    {
        $model = new AppModel;
        if ($model->getLastSystemNum() < 1){
            return $this->renderError('您的子系统数量不足,请联系管理员');
        }
        // 新增记录
        if ($model->add($this->postData())) {
            return $this->renderSuccess('添加成功');
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 添加应用
     */
    public function edit($app_id)
    {
        $model = AppModel::detail($app_id);
        // 新增记录
        if ($model->edit($this->postData())) {
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError($model->getError() ?: '修改失败');
    }

    /**
     * 删除小程序
     */
    public function delete($app_id)
    {
        // 小程序详情
        $model = AppModel::detail($app_id);
        if (!$model->setDelete()) {
            return $this->renderError('操作失败');
        }
        return $this->renderSuccess('操作成功');
    }

    /*
     *启用禁用
    */
    public function updateStatus($app_id)
    {
        $model = AppModel::detail($app_id);
        if (!$model->updateStatus()) {
            return $this->renderError('操作失败');
        }
        return $this->renderSuccess('操作成功');
    }

    /**
     * 把所有权限总的代理商的权限保留,其他的去除
     * @param $menu
     * @param $roleMenu
     */
    protected function takeAllMenuForRole(&$menu, $roleMenu){
        foreach ($menu as $key => &$m) {
            if (!in_array($m['access_id'], $roleMenu)){
                //删除会导致数组变化 前端数据结构有问题
                //unset($menu[$key]);
                //结合前端框架disable属性值
                $menu[$key]['disabled']=true;
            }
            if (isset($m['children']) && !empty($m['children'])){
                $this->takeAllMenuForRole($m['children'], $roleMenu);
            }
        }
    }

    /**
     * 获取每个用户的已拥有的菜单权限列表
     * @param $app_id
     * @return array|mixed
     */
    protected function getRoleSelectAccess($app_id){
        $model = AppModel::detail($app_id);
        $select_menu = $model['access_id']?json_decode($model['access_id'],true):[];
        if (empty($select_menu)){
            $select_menu = $model['access_id']?json_decode($model['access_id'],true):[];
        }
        foreach ($select_menu as $key  => $item) {
            $select_menu[$key] = (int)$item;
        }
        return $select_menu;
    }

    /**
     * 获取当前的代理商的所有可用的菜单
     * @return array|void
     */
    protected function getCurrentRoleAllAccess(){
        $menu = (new AccessModel())->getList();
        $parent_app_id = $this->store['app']['app_id'];
        $parent_select_menu = $this->getRoleSelectAccess($parent_app_id);
        $this->takeAllMenuForRole($menu, $parent_select_menu);
        return $menu;
    }

    /*
    * 权限菜单
    */
    public function roleInfo($app_id=0)
    {
        $select_menu = [];
        $menu = $this->getCurrentRoleAllAccess();
        if($app_id){
            $select_menu = $this->getRoleSelectAccess($app_id);
        }
        return $this->renderSuccess('', compact('menu','select_menu'));
    }

    /* 
    * 权限菜单
    */
    public function roleInfoByAdd()
    {
        $menu = $menu = $this->getCurrentRoleAllAccess();
        $app_id = $this->store['app']['app_id'];
        $select_menu = $this->getRoleSelectAccess($app_id);
        return $this->renderSuccess('', compact('menu','select_menu'));
    }

    /*
    *设置是否为测试系统
   */
    public function updateIsTestStatus($app_id)
    {
        $model = AppModel::detail($app_id);
        if (!$model->updateIsTestStatus()) {
            return $this->renderError('操作失败');
        }
        return $this->renderSuccess('操作成功');
    }
}
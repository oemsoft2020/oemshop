<?php

namespace app\admin\controller;

use app\admin\model\app\App as AppModel;
use app\admin\model\Shop as ShopModel;
use app\admin\model\Access as AccessModel;
use app\common\service\BaseService;

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

            $item['is_system_test'] = $item['is_system_test']?true:false;
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
        BaseService::record($app_id);
        return redirect('/shop#/home?from=admin');
    }

    /**
     * 添加应用
     */
    public function add()
    {
        $model = new AppModel;
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
    /*
     *启用禁用系统代理
    */
    public function updateIsAgentStatus($app_id)
    {
        $model = ShopModel::detail($app_id);
        if (!$model->updateIsAgentStatus()) {
            return $this->renderError('操作失败');
        }
        return $this->renderSuccess('操作成功');
    }

    /* 
    * 权限菜单
    */
    public function roleInfo($app_id=0)
    {   
        $select_menu = [];
        $menu = (new AccessModel())->getList();
        if($app_id){
            $model = AppModel::detail($app_id);
            $select_menu = $model['access_id']?json_decode($model['access_id'],true):[];
            foreach ($select_menu as $key  => $item) {
                $select_menu[$key] = (int)$item;
            }
        }
        return $this->renderSuccess('', compact('menu','select_menu'));
    }

    /*
     *启用禁用系统代理
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
<?php

namespace app\shop\controller\auth;

use app\shop\controller\Controller;

use app\shop\model\auth\Role as RoleModel;
use app\shop\model\shop\Access as AccessModel;
use app\shop\model\app\App;
/**
 * 管理后台角色
 */
class Role extends Controller
{
    /**
     * 角色列表
     */
    public function index()
    {
        $model = new RoleModel();
        $list = $model->getTreeData();
        return $this->renderSuccess('', compact('list'));
    }

    /**
     * 新增get数据
     */
    public function addInfo()
    {
        $menu = $this->getMenu();

        $model = new RoleModel();
        // 角色列表
        $roleList = $model->getTreeData();
        return $this->renderSuccess('', compact('menu', 'roleList'));
    }

    /**
     * 新增
     */
    public function add()
    {
        if($this->request->isGet()){
            return $this->addInfo();
        }
        $data = $this->postData();
        $model = new RoleModel();
        if ($model->add($data)) {
            return $this->renderSuccess('添加成功');
        }
        return $this->renderError($model->getError() ?:'添加失败');
    }

    /**
     * 修改get数据
     */
    public function editInfo($role_id)
    {
        $menu = $this->getMenu();
        
        $model = RoleModel::detail($role_id);
        $select_menu = array_column($model->toArray()['access'], 'access_id');
        // 角色列表
        $roleList = $model->getTreeData();
        return $this->renderSuccess('', compact('model', 'roleList', 'menu', 'select_menu'));
    }

    /**
     * 修改
     */
    public function edit($role_id)
    {
        if($this->request->isGet()){
            return $this->editInfo($role_id);
        }
        $data = $this->postData();
        if (isset($data['access_id']) && count($data['access_id']) == 0) {
            return $this->renderError('请选择权限');
        }

        $model = RoleModel::detail($role_id);

        // 更新记录
        if ($model->edit($data)) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?:'更新失败');
    }

    /**
     * 删除
     */
    public function delete($role_id)
    {
        $model = new RoleModel();
        if($model->del($role_id)){
            return $this->renderSuccess('删除成功');
        }
        return $this->renderError($model->getError() ?:'删除失败');
    }

    /* 
    * 获取当前站点可用菜单（权限）
    */
    protected function getMenu()
    {
        $appModel = new App();
        $accessModel = new AccessModel();
        $appInfo = $appModel->detail($this->store['app']['app_id']);
        $menusArray = json_decode($appInfo['access_id']);

        if(empty($appInfo['access_id'])||empty($menusArray)){

            $menu = $accessModel->getList();

        }else{

            $menu = [];
            $menu =  $accessModel->getListByAdmin($menusArray);
        }
        return $menu;
    }
}

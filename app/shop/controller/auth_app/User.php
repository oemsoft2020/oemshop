<?php

namespace app\shop\controller\auth_app;

use app\shop\model\shop\Access as AccessModel;
use app\common\model\settings\Setting as SettingModel;
use app\shop\controller\Controller;
use app\shop\model\auth\User as UserModel;
use app\shop\model\auth\Role;
use app\shop\model\app\App;
use app\shop\model\auth\User as AuthUserModel;
use app\shop\model\auth\UserRole as UserRoleModel;
use app\shop\model\shop\ShopUserApp as ShopUserAppModel;
use app\shop\service\UserService;
use think\facade\Db;

/**
 * 应用管理员
 */
class User extends Controller
{
    /**
     * 首页列表
     * @return \think\response\Json
     */
    public function index()
    {
        $model = new ShopUserAppModel();
        $data = $this->postData();
       
        $list = $model->getList($data);
        $shopUserAppModel = new ShopUserAppModel();
        $roleshow = 1;

        $where = [
            'type'=>isset($data['type'])?$data['type']:'',
            'data_id'=>isset($data['data_id'])?$data['data_id']:'',
            'app_id'=>$this->store['app']['app_id'],
            'shop_user_id'=>$this->store['user']['shop_user_id'],
        ];
        $userAppNum =  $shopUserAppModel->where($where)->count();
        if($userAppNum){
            $roleshow = 0;
        }
        return $this->renderSuccess('', compact('list','roleshow'));
    }

    /**
     * 新增信息
     * @return \think\response\Json
     */
    public function addInfo()
    {
        $model = new Role();
        // 角色列表
        $roleList = $model->getTreeData();
        return $this->renderSuccess('', compact('roleList'));
    }

    /**
     * 新增
     * @return \think\response\Json
     */
    public function add()
    {
        if($this->request->isGet()){
            return $this->addInfo();
        }
        $data = $this->postData();

        $shopUserAppModel = new ShopUserAppModel();

        if(isset($data['type']) && !empty($data['type'])){
            $where['type'] = $data['type'];
        }

        if(isset($data['data_id']) && !empty($data['data_id'])){
            $where['data_id'] = $data['data_id'];
        }

        $where['app_id'] = $this->store['app']['app_id'];
        $where['shop_user_id'] = $this->store['user']['shop_user_id'];
        // $where = [
        //     'app_id'=>$this->store['app']['app_id'],
        //     'shop_user_id'=>$this->store['user']['shop_user_id'],
        // ];
        $userAppNum =  $shopUserAppModel->where($where)->count();

        if($userAppNum){
            $userRoleModel = new UserRoleModel();
            $role_id_arr = $userRoleModel->getRoleIds($this->store['user']['shop_user_id']);
            $data['role_id'] = $role_id_arr;
            $data['shop_user_id'] = $this->store['user']['shop_user_id'];
        }
        $model = new UserModel();
        $num = $model->getUserName(['user_name' => $data['user_name']]);
        if ($num > 0) {
            return $this->renderError('用户名已存在');
        }
        if (!isset($data['role_id'])) {
            return $this->renderError('请选择所属角色');
        }
        if ($data['confirm_password'] != $data['password']) {
            return $this->renderError('确认密码和登录密码不一致');
        }
        $model = new UserModel();
        if ($model->add($data)) {
            return $this->renderSuccess('添加成功');
        }
        return $this->renderError('添加失败');
    }

    /**
     * 修改信息
     * @param $shop_user_id
     * @return \think\response\Json
     */
    public function editInfo($shop_user_id)
    {
        $info = UserModel::detail(['shop_user_id' => $shop_user_id], ['UserRole']);

        $role_arr = array_column($info->toArray()['UserRole'], 'role_id');

        $model = new Role();
        // 角色列表
        $roleList = $model->getTreeData();
        return $this->renderSuccess('', compact('info', 'roleList', 'role_arr'));
    }

    /**
     * 编辑
     * @param $shop_user_id
     * @return \think\response\Json
     */
    public function edit($shop_user_id)
    {
        $data = $this->postData();
        if($this->request->isGet()){
            return $this->editInfo($shop_user_id);
        }

        $model = new UserModel();
        $num = $model->getUserName(['user_name' => $data['user_name']], $data['shop_user_id']);
        if ($num > 0) {
            return $this->renderError('用户名已存在');
        }
        if (!isset($data['access_id'])) {
            return $this->renderError('请选择所属角色');
        }
        if (isset($data['password']) && !empty($data['password'])) {
            if (!isset($data['confirm_password'])) {
                return $this->renderError('请输入确认密码');
            } else {
                if ($data['confirm_password'] != $data['password']) {
                    return $this->renderError('确认密码和登录密码不一致');
                }
            }
        }
        if (empty($data['password'])) {
            if (isset($data['confirm_password']) && !empty($data['confirm_password'])) {
                return $this->renderError('请输入登录密码');
            }
        }

        // 更新记录
        if ($model->edit($data, ['shop_user_id' => $data['shop_user_id']])) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError()?:'更新失败');


    }

    /**
     * 删除
     */
    public function delete($shop_user_id)
    {
        $model = new UserModel();
        if ($model->del(['shop_user_id' => $shop_user_id])) {
            return $this->renderSuccess('删除成功');
        }
        return $this->renderError('删除失败');
    }

    /**
     * 获取角色菜单信息
     */
    public function getRoleList()
    {
        $user = $this->store['user'];

        $menus = [];
        $user_info = AuthUserModel::find($user['shop_user_id']);

        if ($user_info['is_super'] == 1) {
            
            $appModel = new App();
            $appInfo = $appModel->detail($this->store['app']['app_id']);

            if(empty($appInfo['access_id'])){
                $model = new AccessModel();
                $menus = $model->getList();
            }else{

                $menus = [];
                $menusArray = json_decode($appInfo['access_id']);
                $accessModel = new AccessModel();
            
                $menus =  $accessModel->getListByAdmin($menusArray);
            
                foreach ($menus as $key => $val) {
                    if (!empty($val['children'])&&$val['redirect_name'] != $val['children'][0]['path']) {
                        $menus[$key]['redirect_name'] = $menus[$key]['children'][0]['path'];
                    }
                }
    
            }
        } else {
            $model = new AccessModel();
            $menus = $model->getListByUser($user['shop_user_id']);

            foreach ($menus as $key => $val) {
                if (!empty($val['children'])&&$val['redirect_name'] != $val['children'][0]['path']) {
                    $menus[$key]['redirect_name'] = $menus[$key]['children'][0]['path'];
                }
            }
        }
        return $this->renderSuccess('', compact('menus'));
    }

    /**
     * 获取用户信息
     */
    public function getUserInfo()
    {
        $store = session('kmdshop_store');
        $user = [];
        if (!empty($store)) {
            $user = $store['user'];
        }
        // 商城名称
        $shop_name = SettingModel::getItem('store')['name'];
        //当前系统版本
        $version = get_version();
        return $this->renderSuccess('', compact('user', 'shop_name', 'version'));
    }
}

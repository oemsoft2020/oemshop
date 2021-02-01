<?php

namespace app\shop\model\auth;

use app\common\model\shop\User as UserModel;
use app\common\model\user\User  as ShopUserModel;
use app\common\model\user\Grade as GradeModel;
use app\shop\model\shop\ShopUserApp;

/**
 * 角色模型
 */
class User extends UserModel
{

    public function getList($data,$limit = 20)
    {
        $condition = [];
        //检索：用户名
        if (isset($data['user_name'])&& $data['user_name'] !='') {

            $condition = [
                ['user_name|real_name', 'like','%' . trim($data['user_name']) . '%']
            ];


        }

        return $this->with(['userRole.role'])->where('is_delete', '=', 0)
            ->where($condition)
            ->order(['create_time' => 'desc'])
            ->paginate($limit, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 获取所有上级id集
     */
    public function getTopRoleIds($role_id, &$all = null)
    {
        static $ids = [];
        is_null($all) && $all = $this->getAll();
        foreach ($all as $item) {
            if ($item['role_id'] == $role_id && $item['parent_id'] > 0) {
                $ids[] = $item['parent_id'];
                $this->getTopRoleIds($item['parent_id'], $all);
            }
        }
        return $ids;
    }

    /**
     * 获取所有角色
     */
    private function getAll()
    {
        $data = $this->order(['sort' => 'asc', 'create_time' => 'asc'])->select();
        return $data ? $data->toArray() : [];
    }

    public function add($data)
    {
        $this->startTrans();
        try {
            $arr = [
                'user_name' => trim($data['user_name']),
                'password' => salt_hash($data['password']),
                'real_name' => trim($data['real_name']),
                'role_id' => $data['role_id'],
                'app_id' => self::$app_id,
                'parent_shop_user_id'=>isset($data['shop_user_id'])?$data['shop_user_id']:0,
				'is_delete'=>isset($data['is_delete'])?$data['is_delete']:0
            ];
            $res = self::create($arr);
            $model = new RoleAccess();
            $add_arr = [];
            $model = new UserRole();
            foreach ($data['role_id'] as $val) {
                $add_arr[] = [
                    'shop_user_id' => $res['shop_user_id'],
                    'role_id' => $val,
                    'app_id' => self::$app_id,
                ];
            }
            $model->saveAll($add_arr);

            if(isset($data['type'])&&isset($data['data_id'])){
                $shopUserAppData = [
                    'type' =>$data['type'],
                    'data_id'=>$data['data_id'],
                    'shop_user_id'=>$res['shop_user_id'],
                    'status'=>1,
                    'app_id'=>self::$app_id
                ];
                $shopUserAppModel = new ShopUserApp();
                $res = $shopUserAppModel->save($shopUserAppData);
                $userData = [
                    'mobile' => $data['user_name'],
                    'reg_source' => 'web',
                    'grade_id' => GradeModel::getDefaultGradeId(),
                    'app_id'=>self::$app_id
                ];
                $shopUserModel = new ShopUserModel();
                $shopUserModel->save($userData);
            }

            // 事务提交
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }

    }

    public function getUserName($where, $shop_user_id = 0)
    {
        if ($shop_user_id > 0) {
            return $this->where($where)->where('shop_user_id', '<>', $shop_user_id)->count();
        }
        return $this->where($where)->count();
    }


    public function edit($data)
    {
        $this->startTrans();
        try {
            $arr = [
                'user_name' => trim($data['user_name']),
                'password' => salt_hash($data['password']),
                'real_name' => $data['real_name'],
				'is_delete'=>isset($data['is_delete'])?$data['is_delete']:0
            ];
            if (empty($data['password'])) {
                unset($arr['password']);
            }

            $where['shop_user_id'] = $data['shop_user_id'];
            self::update($arr, $where);

            $model = new UserRole();
            UserRole::destroy($where);
            $add_arr = [];
            foreach ($data['access_id'] as $val) {
                $add_arr[] = [
                    'shop_user_id' => $data['shop_user_id'],
                    'role_id' => $val,
                    'app_id' => self::$app_id
                ];
            }
            $model->saveAll($add_arr);
            // 事务提交
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    public function getChild($where)
    {
        return $this->where($where)->count();
    }

    public function del($where)
    {
        self::update(['is_delete' => 1], $where);
        $shopUserAppModel = new ShopUserApp();
        $shopuserapp = $shopUserAppModel->where('shop_user_id','=',$where['shop_user_id'])->find();
        if($shopuserapp){
            $shopUserAppModel::update(['is_deleted'=>1],$where);
        }
        return UserRole::destroy($where);
    }
}

<?php

namespace app\agent\model\app;

use app\admin\model\page\Page as PageModel;
use app\agent\model\Shop as ShopModel;
use app\agent\model\Shop as ShopUser;
use app\common\model\app\App as AppModel;
use app\agent\model\user\Grade as GradeModel;

class App extends AppModel
{
    public $globalScope = [];
    /**
     * 获取小程序列表
     */
    public function getList($limit, $is_recycle = false)
    {
        $model = $this->alias('app')->field(['app.*,user.user_name'])
            ->join('shop_user user', 'user.app_id = app.app_id','left')
            // ->where('user.is_super', '=', 1)
            ->where('app.is_delete', '=', 0)
            ->where('app.is_recycle', '=', (int)$is_recycle)
            ->where('user.system_agent_parent', '=', self::$app_id)
            ->order(['create_time' => 'asc']);
        if (!empty($limit['keyword'])) {
            $model = $model->where('app.app_name', 'like', '%' . trim($limit['keyword']) . '%');
        }
        $list = $model->paginate($limit, false, [
                'query' => request()->request()
            ]);

        return $list;
    }

    public function getSystemNumTotal(){
        $model = ShopModel::detail(self::$app_id);
        return (int) $model->system_num;
    }

    public function getUsedSystemNum(){
        return (int) $this->alias('app')
            ->join('shop_user user', 'user.app_id = app.app_id','left')
            ->where('app.is_delete', '=', 0)
            ->where('app.is_recycle', '=', 0)
            ->where('user.system_agent_parent', '=', self::$app_id)
            ->count();
    }

    public function getLastSystemNum(){
        return (int) ($this->getSystemNumTotal() - $this->getUsedSystemNum() | 0);
    }

    /**
     * 新增记录
     */
    public function add($data)
    {
        if ($data['password'] !== $data['password_confirm']) {
            $this->error = '确认密码不正确';
            return false;
        }
        if (ShopUser::checkExist($data['user_name'])) {
            $this->error = '商家用户名已存在';
            return false;
        }
        if(!empty($data['access_id'])){
            $data['access_id'] = json_encode($data['access_id']);
        }
        $this->startTrans();
        try {
            // 添加小程序记录
            $this->save($data);
            // 新增商家用户信息
            $ShopUser = new ShopUser;
            if (!$ShopUser->add($this['app_id'], $data)) {
                $this->error = $ShopUser->error;
                return false;
            }
            // 新增应用diy配置
            (new PageModel)->insertDefault($this['app_id']);
            // 默认等级
            (new GradeModel)->insertDefault($this['app_id']);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 修改记录
     */
    public function edit($data)
    {
        if(!empty($data['access_id'])){
            $data['access_id'] = json_encode($data['access_id']);
        }else{
            $data['access_id'] = json_encode([]);
        }
        $this->startTrans();
        try {
            $save_data = [
                'app_name' => $data['app_name'],
                'access_id'=> $data['access_id']
            ];
            $this->save($save_data);
            $user_data = [
                'user_name' => $data['user_name']
            ];
            if (!empty($data['password'])) {
                $user_data['password'] = salt_hash($data['password']);
            }
            $shop_user = (new ShopUser())->where('app_id', '=', $this['app_id'])->where('is_super', '=', 1)->find();
            $shop_user->save($user_data);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }
    /**
     * 移入移出回收站
     */
    public function recycle($is_recycle = true)
    {
        return $this->save(['is_recycle' => (int)$is_recycle]);
    }

    /**
     * 软删除
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]);
    }


    /**
     * 启用系统测试
     * @return bool
     */
    public function updateIsTestStatus()
    {
        return $this->save([
            'is_system_test' => !$this['is_system_test'],
        ]);
    }
}
<?php

namespace app\admin\model\app;

use app\admin\model\page\Page as PageModel;
use app\admin\model\Shop as ShopUser;
use app\common\model\app\App as AppModel;
use app\admin\model\user\Grade as GradeModel;

class App extends AppModel
{
    /**
     * 获取小程序列表
     */
    public function getList($limit, $is_recycle = false)
    {
        $list = $this->alias('app')->field(['app.*,user.user_name,user.is_system_agent,user.system_num,user.system_agent_qrcode,user.system_agent_parent,app.is_system_test,app.test_tips'])->where('app.is_recycle', '=', (int)$is_recycle)
            ->join('shop_user user', 'user.app_id = app.app_id','left')
//            ->join('shop_user parent_user', 'parent_user.app_id = user.system_agent_parent','left')
            ->where('user.is_super', '=', 1)
            ->where('app.is_delete', '=', 0)
            ->order(['create_time' => 'asc'])
            ->paginate($limit, false, [
                'query' => request()->request()
            ]);

        foreach ($list as &$app) {
            // 代理qrcode
            $app['file_url'] = isset($app['image']) ? $app['image']['file_path'] : '';
        }
        return $list;
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
        if(!empty($data['is_system_test'])&&$data['is_system_test']=='true'){
            $data['is_system_test'] = 1;
        }else{
            $data['is_system_test'] = 0;
            $data['test_tips'] = '';
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
                'access_id'=> $data['access_id'],
            ];
            if(!empty($data['is_system_test'])&&$data['is_system_test']=='true'){
                $save_data['is_system_test'] = 1;
                $save_data['test_tips'] = $data['test_tips'];
            }else{
                $save_data['is_system_test'] = 0;
                $save_data['test_tips'] = '';
            }
            $this->save($save_data);
            $user_data = [
                'user_name' => $data['user_name'],
                'is_system_agent'=> (bool) $data['is_system_agent'],
                'system_num'=> (int) $data['system_num'],
                'system_agent_qrcode'=> (int) $data['system_agent_qrcode']
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
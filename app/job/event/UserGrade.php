<?php

namespace app\job\event;

use think\facade\Cache;
use app\job\model\user\Grade as GradeModel;
use app\job\model\user\User as UserModel;
/**
 * 用户等级事件管理
 */
class UserGrade
{
    private $model;

    /**
     * 执行函数
     */
    public function handle($model)
    {
        if (!$model instanceof GradeModel) {
            return new GradeModel and false;
        }
        $this->model = $model;
        if (!$model::$app_id) {
            return false;
        }
        // $cacheKey = "task_space_grade_{$model::$app_id}";
        // if (!Cache::has($cacheKey)||1) {
            // 设置用户的会员等级
        $this->setUserGrade();
        // Cache::set($cacheKey, time(), 60 * 10);
        // }
        return true;
    }

    /**
     * 设置用户的会员等级
     */
    private function setUserGrade()
    {
        // 获取所有等级
        $list = GradeModel::getUsableList(null);
        if ($list->isEmpty()) {
            return false;
        }
        // 用户模型
        $UserModel = new UserModel;
        // 遍历等级，根据升级条件 查询满足消费金额的用户列表，并且他的等级小于该等级
        $data = [];
        foreach ($list as $grade) {
            if($grade['is_default'] == 1){
                continue;
            }
            $userList = $UserModel->getUpgradeUserList($grade, array_keys($data));
            if(!empty($userList)){
                foreach ($userList as $user) {
                    if (!isset($data[$user['user_id']])) {
                        $data[$user['user_id']] = [
                            'user_id' => $user['user_id'],
                            'old_grade_id' => $user['grade_id'],
                            'new_grade_id' => $grade['grade_id'],
                        ];
                    }
                }
            }
            
        }
        $this->dologs('setUserGrade', [
            'size' => count($data)
        ]);
        // 批量修改会员的等级
        return count($data) > 0 && $UserModel->setBatchGrade($data);
    }

    /**
     * 记录日志
     */
    private function dologs($method, $params = [])
    {
        $value = 'UserGrade --' . $method;
        foreach ($params as $key => $val)
            $value .= ' --' . $key . ' ' . $val;
        return log_write($value);
    }
}

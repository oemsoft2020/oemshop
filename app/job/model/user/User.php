<?php

namespace app\job\model\user;

use app\common\model\user\User as UserModel;
use app\job\model\user\GradeLog as GradeLogModel;
use app\common\enum\user\grade\ChangeTypeEnum;
use app\common\model\plus\agent\Referee;

/**
 * 用户模型
 */
class User extends UserModel
{
    /**
     * 获取用户信息
     */
    public static function detail($where, $with = [])
    {
        return parent::detail($where, $with);
    }

    /**
     * 查询满足会员等级升级条件的用户列表
     */
    public function getUpgradeUserList($upgradeGrade, $excludedUserIds = [])
    {
        $model = $this;
        if (!empty($excludedUserIds)) {
            $model = $model->where('user.user_id', 'not in', $excludedUserIds);
        }
        if(empty($upgradeGrade['open_money'])&&empty($upgradeGrade['open_points'])&&empty($upgradeGrade['open_invite'])&&empty($upgradeGrade['open_pay'])&&empty($upgradeGrade['open_achievement'])){
            return false;
        }
        $model = $model->alias('user')
            ->field(['user.user_id', 'user.grade_id'])
            ->join('user_grade grade', 'grade.grade_id = user.grade_id', 'LEFT')
            ->where('user.grade_id', '<', $upgradeGrade['grade_id'])
            ->where('user.is_delete', '=', 0);

        return $model->where(function ($query) use($upgradeGrade) {
            // 按消费升级
            $hasFilter = false;
            if($upgradeGrade['open_money'] == 1){
                $query->where('user.expend_money', '>=', $upgradeGrade['upgrade_money']);
                $hasFilter = true;
            }
            // 按积分升级
            if($upgradeGrade['open_points'] == 1){
                if($hasFilter){
                    $query->whereOr('user.total_points', '>=', $upgradeGrade['upgrade_points']);
                }else{
                    $query->where('user.total_points', '>=', $upgradeGrade['upgrade_points']);
                }
                $hasFilter = true;
            }
            // 按消费升级
            if($upgradeGrade['open_invite'] == 1){
                if($hasFilter){
                    $query->whereOr('user.total_invite', '>=', $upgradeGrade['upgrade_invite']);
                }else{
                    $query->where('user.total_invite', '>=', $upgradeGrade['upgrade_invite']);
                }
                $hasFilter = true;
            }
            //按支付金额升级
            if($upgradeGrade['open_pay'] == 1){
                if($hasFilter){
                    $query->whereOr('user.pay_money', '>=',$upgradeGrade['upgrade_pay']);
                }else{
                    $query->where('user.pay_money', '>=', $upgradeGrade['upgrade_pay']);
                }
                $hasFilter = true;
            }

            //按推荐业绩升级
            if($upgradeGrade['open_achievement'] == 1){
                if($hasFilter){
                    $query->whereOr('user.achievement', '>=',$upgradeGrade['upgrade_achievement']);
                }else{
                    $query->where('user.achievement', '>=', $upgradeGrade['upgrade_achievement']);
                }
            }

        })->select();
    }

    /**
     * 批量设置会员等级
     */
    public function setBatchGrade($data)
    {
        // 批量更新会员等级的数据
        $userData = [];
        // 批量更新会员等级变更记录的数据
        $logData = [];
        foreach ($data as $item) {
            $userData[] = [
                'data' => ['grade_id' => $item['new_grade_id']],
                'where' => [
                    'user_id' => $item['user_id'],
                ],
            ];
            $logData[] = [
                'old_grade_id' => $item['old_grade_id'],
                'new_grade_id' => $item['new_grade_id'],
                'change_type' => ChangeTypeEnum::AUTO_UPGRADE,
                'user_id' => $item['user_id'],
            ];
        }
        // 批量更新会员等级
        $this->updateAll($userData);
        // 批量更新会员等级变更记录
        (new GradeLogModel)->saveAll($logData);
        //批量更新会员代理关系
        file_put_contents('mytext20201016.txt',date('Y-m-d H:i:s')."2222:".var_export($data,true)."\r\n",FILE_APPEND);
        foreach ($data as  $item) {
            $this->updateUserAgent($item['user_id']);
        }
       
        return true;
    }

}

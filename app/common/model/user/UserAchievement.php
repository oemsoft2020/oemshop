<?php


namespace app\common\model\user;

use app\common\model\BaseModel;
/**
 * 用户业绩模型
 */
class UserAchievement extends BaseModel
{
    protected $pk = 'user_achievement_id';
    protected $name = 'user_achievement';

    
    /* 
    * 保存浏览记录
    */
    public function saveUserAchievementRecords($data)
    { 
        $this->startTrans();
        try {
            
            $data['app_id']=  self::$app_id;
            $this::create($data);
            // 事务提交
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /* 
    * 获取订单
    */

    public function order()
    {
        return $this->belongsTo('app\\common\\model\\order\\Order','order_id','order_id');
    }

    /* 
    * 列表
    */
    public function getList($user_id, $apply_status = -1,$limit=15)
    {
        $model = $this;
        $apply_status > -1 && $model = $model->where('flow_type', '=', $apply_status);
        return $model->with(['order'])->where('user_id', '=', $user_id)->order(['create_time' => 'desc'])
            ->paginate($limit, false, [
                'query' => \request()->request()
            ]);
    }

    

}
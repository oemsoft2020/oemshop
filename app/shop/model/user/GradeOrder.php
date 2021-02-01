<?php

namespace app\shop\model\user;

use app\common\model\user\GradeOrder as GradeOrderModel;
use app\common\model\user\Grade;
use app\api\model\user\User as UserModel;

/**
 * 用户升级记录
 */
class GradeOrder extends GradeOrderModel
{
    /**
     * 获取等级升级记录列表
     */
    public function getList($query = [])
    {
        $model = $this;
        //搜索用户id
        if (isset($query['search']) && $query['search'] != '') {
            $model = $model->where('user.user_id', '=', trim($query['search']));
        }
        //搜索时间段
        if (isset($query['value1']) && $query['value1'] != '') {
            $sta_time = array_shift($query['value1']);
            $end_time = array_pop($query['value1']);
            $model = $model->whereBetweenTime('order.create_time', $sta_time, $end_time);
        }
        // 获取列表数据
        return $model->with(['user','grade','oldgrade'])
            ->alias('order')
            ->field('order.*')
            ->join('user', 'user.user_id = order.user_id')
            ->where('order.pay_status','=',20)
            ->order(['order.create_time' => 'desc'])
            ->paginate($query, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 设置查询条件
     */
    private function setQueryWhere($query)
    {
        // 设置默认的检索数据
        $params = $this->setQueryDefaultValue($query, [
            'user_id' => 0,
            'search' => '',
            'start_time' => '',
            'end_time' => '',
        ]);
        // 用户ID
        $params['user_id'] > 0 && $this->where('order.user_id', '=', $params['user_id']);
        // 用户昵称
        !empty($params['search']) && $this->where('user.nickName', 'like', "%{$params['search']}%");
        // 起始时间
        !empty($params['start_time']) && $this->where('order.create_time', '>=', strtotime($params['start_time']));
        // 截止时间
        !empty($params['end_time']) && $this->where('order.create_time', '<', strtotime($params['end_time']) + 86400);
    }

    /**
     * 等级升级审核
     * @param $data
     * @return bool
     */
    public function submit($data)
    {
        if ($data['order_status'] == '20' && empty($data['reject_reason'])) {
            $this->error = '请填写驳回原因';
            return false;
        }
        $this->startTrans();
        $save_data = [
            'order_status' => $data['order_status'],
            'reject_reason' => isset($data['reject_reason']) ? $data['reject_reason'] : '',
            'reject_status' => isset($data['reject_status']) ? $data['reject_status'] : 10,
        ];
        //会员升级
        if ($data['order_status']==30) {
           $this->updateGrade();
        }
        $this->save($save_data);
        $this->commit();
        return true;
    }
    /**
     * 会员升级
     */
    private function updateGrade()
    {
        $model = $this;
        $grade = Grade::detail($model->grade_id);
   
        if (empty($grade)) {
            return false;
        }
        $user = UserModel::detail($model->user_id);
        $user->where('user_id',$model->user_id)
                ->update(['grade_id'=>$grade['grade_id']]);
        (new UserModel)->updateUserAgent($user['user_id'],$user['referee_id']);
    }

}
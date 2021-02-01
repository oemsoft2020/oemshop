<?php

namespace app\shop\model\plus\certification;

use app\common\model\plus\certification\Apply as ApplyModel;

/**
 * 实名认证申请
 */
class Apply extends ApplyModel
{
    /**
     * 获取分销商申请列表
     */
    public function getList($search)
    {
        $model = $this->alias('apply')
            ->field('apply.*, user.nickName, user.avatarUrl')
            // ->with(['file'])
            ->join('user', 'user.user_id = apply.user_id')
            ->order(['apply.create_time' => 'desc']);
        if (!empty($search['nick_name'])) {
            $model = $model->where('user.nickName|apply.name', 'like', '%' . $search['nick_name'] . '%');
        }

        // 获取列表数据
        return $model->paginate($search['list_rows'], false, [
            'query' => \request()->request()
        ]);
    }

    /**
     * 分销商入驻审核
     * @param $data
     * @return bool
     */
    public function submit($data)
    {
        if ($data['apply_status'] == '30' && empty($data['reject_reason'])) {
            $this->error = '请填写驳回原因';
            return false;
        }
        $this->startTrans();
        $save_data = [
            'audit_time' => time(),
            'apply_status' => $data['apply_status'],
            'reject_reason' => $data['reject_reason'],
        ];
        $this->save($save_data);
        $this->commit();
        return true;
    }

}
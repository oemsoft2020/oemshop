<?php

namespace app\shop\model\plus\invitationgift;

use app\common\model\plus\invitationgift\Partake as PartakeModel;

/**
 * Class Partake
 * 参加记录模型
 * @package app\shop\model\plus\invitationgift
 */
class Partake extends PartakeModel
{
    /**
     * @param $id
     * 参与记录列表
     * @return \think\Collection
     */
    public function getList($id)
    {
        $where = [
            'invitation_gift_id' => $id,
            'is_delete' => 0
        ];
        return $this->with(['user', 'partake'])->where($where)
            ->order('create_time', 'desc')
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }
}
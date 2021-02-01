<?php

namespace app\common\model\plus\invitationgift;

use app\common\model\BaseModel;

/**
 * Class Invitation
 * 邀请有礼模型
 * @package app\common\model\plus\invitationgift
 */
class Invitation extends BaseModel
{
    protected $name = 'invitation_gift';
    protected $pk = 'invitation_gift_id';

    /**
     * 关联奖励
     * @return \think\model\relation\HasMany
     */
    public function Reward()
    {
        return $this->hasMany('app\\common\\model\\plus\\invitationgift\\InvitationReward', 'invitation_gift_id', 'invitation_gift_id');
    }

    /**
     * 开始时间
     */
    public function getStartTimeAttr($value)
    {
        return ['text' => date('Y-m-d H:i:s', $value), 'value' => $value];
    }

    /**
     * 有效期-结束时间
     */
    public function getEndTimeAttr($value)
    {
        return ['text' => date('Y-m-d H:i:s', $value), 'value' => $value];
    }


}
<?php

namespace app\common\model\plus\deposit;

use app\common\model\BaseModel;

/**
 * Class Partake
 * 保证金模型
 * @package app\common\model\plus\invitationgift
 */
class Deposit extends BaseModel
{
    protected $name = 'deposit';
    protected $pk = 'deposit_id';

    /**
     * 关联用户表
     */
    public function user()
    {
        return $this->BelongsTo('app\common\model\user\User', 'user_id', 'user_id');
    }
}
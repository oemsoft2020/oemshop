<?php

namespace app\common\model\plus\card;

use app\common\model\BaseModel;
use think\model\relation\BelongsTo;

class CardAuth extends BaseModel
{
    protected $name = 'card_auth';
    protected $pk = 'card_auth_id';

    public function supply()
    {
        return $this->belongsTo('app\\common\\model\\plus\\supply\\Supply', 'supply_id', 'supply_id');
    }

}
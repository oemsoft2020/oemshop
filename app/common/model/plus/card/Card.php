<?php

namespace app\common\model\plus\card;

use app\common\model\BaseModel;
use think\model\relation\BelongsTo;

class Card extends BaseModel
{
    protected $name = 'card';
    protected $pk = 'card_id';

    public function user()
    {
        return $this->belongsTo('app\\common\\model\\user\\User', 'user_id', 'user_id');
    }

    public function supply()
    {
        return $this->belongsTo('app\\common\\model\\plus\\supply\\Supply', 'supply_id', 'supply_id');
    }

    public function cardCount()
    {
        return $this->hasMany('app\\common\\model\\plus\\card\\CardCount', 'card_id', 'card_id');
    }

    /**
     * 获取详情
     */
    public static function getDetail($card_id)
    {
        return self::find($card_id);
    }

}
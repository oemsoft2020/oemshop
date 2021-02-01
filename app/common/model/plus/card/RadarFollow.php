<?php

namespace app\common\model\plus\card;

use app\common\model\BaseModel;
use app\common\model\user\User;
use think\model\relation\BelongsTo;

class RadarFollow extends BaseModel
{
    protected $name = 'radar_follow';
    protected $pk = 'radar_follow_id';

    public function custom()
    {
        return $this->belongsTo('app\\common\\model\\user\\User', 'c_uid', 'user_id');
    }
}
<?php

namespace app\common\model\plus\card;

use app\common\model\BaseModel;
use app\common\model\user\User;
use think\model\relation\BelongsTo;

class RadarLabelClient extends BaseModel
{
    protected $name = 'radar_label_client';
    protected $pk = 'id';


    public function client()
    {
        return $this->hasOne('app\\common\\model\\plus\\card\\RadarClient', 'radar_client_id','client_id');
    }
    
    public function label()
    {
        return $this->belongsTo('app\\common\\model\\plus\\card\\RadarLabel', 'label_id','radar_label_id')->bind(['name']);
    }
    

}
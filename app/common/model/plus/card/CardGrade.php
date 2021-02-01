<?php

namespace app\common\model\plus\card;

use app\common\model\BaseModel;
use think\model\relation\BelongsTo;

class CardGrade extends BaseModel
{
    protected $name = 'card_grade';
    protected $pk = 'card_grade_id';


    /**
     * 获取详情
     */
    public static function detail($grade_id)
    {
        return self::find($grade_id);
    }
}
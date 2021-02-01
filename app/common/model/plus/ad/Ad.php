<?php

namespace app\common\model\plus\ad;

use app\common\model\BaseModel;

/**
 * Class Partake
 *模型
 * @package app\common\model\plus\banner
 */
class Ad extends BaseModel
{
    protected $name = 'ad';
    protected $pk = 'ad_id';


    /**
     * 详情
     */
    public static function detail($where)
    {
        $filter = is_array($where) ? $where : ['ad_id' => $where];
        return static::with(['file'])->where(array_merge(['is_delete' => 0], $filter))->find();
    }

 

    public function file()
    {
        return $this->belongsTo('app\\common\\model\\file\\UploadFile', 'image_id', 'file_id');
    }
     public function supply()
    {
        return $this->belongsTo('app\\common\\model\\plus\\supply\\Supply', 'supply_id', 'supply_id');
    }


}
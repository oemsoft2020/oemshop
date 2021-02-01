<?php

namespace app\common\model\plus\supply;

use app\common\model\BaseModel;

/**
 * Class Partake
 * 供应商模型
 * @package app\common\model\plus\invitationgift
 */
class Supply extends BaseModel
{
    protected $name = 'supply';
    protected $pk = 'supply_id';

    /**
     * 关联用户表
     */
    public function user()
    {
        return $this->BelongsTo('app\common\model\user\User', 'user_id', 'user_id');
    }
    /**
     * 关联商家等级表
     */
    public function kmdGrade()
    {
        return $this->BelongsTo('app\common\model\plus\supply\Grade', 'kmd_grade_id', 'kmd_grade_id');
    }

    /**
     * 关联管理员
     */
    public function shopUser()
    {
        return $this->BelongsTo('app\common\model\shop\User', 'shop_user_id', 'shop_user_id');
    }

    /**
     * 供应商详情
     */
    public static function detail($where)
    {
        $filter = is_array($where) ? $where : ['supply_id' => $where];
        return static::with(['file','user','shop_user','kmd_grade'])->where(array_merge(['is_delete' => 0], $filter))->find();
    }

 

    public function file()
    {
        return $this->belongsTo('app\\common\\model\\file\\UploadFile', 'image_id', 'file_id');
    }
    /**
     * 指定会员等级下是否存在用户
     */
    public static function checkExistByGradeId($gradeId)
    {
        $model = new static;
        return !!$model->where('kmd_grade_id', '=', (int)$gradeId)
            ->where('is_delete', '=', 0)
            ->value('supply_id');
    }

    /* 
    * 获取默认供应商
    */

    public function getDefaultSupply()
    {
        $model = new static;
        return $model->where('is_default', '=', 1)
            ->value('supply_id');
    }

    public function cardauth()
    {
        return $this->hasOne('app\common\model\plus\card\CardAuth', 'supply_id', 'supply_id');
    }
}
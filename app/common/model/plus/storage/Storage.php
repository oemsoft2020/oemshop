<?php

namespace app\common\model\plus\storage;

use app\common\model\BaseModel;


/**
 * Class Partake
 * 仓库模型
 * @package app\common\model\plus\storage
 */
class Storage extends BaseModel
{
    protected $pk = 'storage_id';

    /**
     * 关联用户表
     */
    public function user()
    {
        return $this->BelongsTo('app\common\model\user\User', 'user_id', 'user_id');
    }

    /**
     * 订单商品列表
     * @return \think\model\relation\BelongsTo
     */
    public function image()
    {
        return $this->belongsTo('app\\common\\model\\file\\UploadFile', 'image_id', 'file_id');
    }


    /**
     * 仓库详情
     */
    public static function storagedetail($storage_id)
    {
        // 仓库详情
        $model = new static;
        $detail = $model->where(['storage_id' => $storage_id, 'is_delete' => 0])->with(['image'])
            ->find();
        return $detail;
    }
}
<?php

namespace app\common\model\shop;

use app\common\model\BaseModel;
use app\common\model\settings\Setting as SettingModel;
/**
 * 应用用户模型
 */
class ShopUserApp extends BaseModel
{
    protected $pk = 'shop_user_app_id';
    protected $name = 'shop_user_app';

    /**
     * 关联用户表
     */
    public function user()
    {
        return $this->belongsTo('User', 'shop_user_id', 'shop_user_id');
    }

    /**
     * 关联用户角色表表
     */
    public function role()
    {
        return $this->belongsToMany('app\\common\\model\\auth\\Role', 'app\\common\\model\\auth\\UserRole');
    }

    public function userRole()
    {
        return $this->hasMany('app\\common\\model\\shop\\UserRole', 'shop_user_id', 'shop_user_id');
    }
}

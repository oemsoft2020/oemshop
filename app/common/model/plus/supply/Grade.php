<?php

namespace app\common\model\plus\supply;

use app\common\model\BaseModel;

/**
 * 用户等级模型
 */
class Grade extends BaseModel
{
    protected $pk = 'kmd_grade_id';
    protected $name = 'kmd_grade';
     //附加字段
    protected $append = ['setting'];

    
    /**
     * 用户等级模型初始化
     */
    public static function init()
    {
        parent::init();
        // // 监听行为管理
        // $model = new static;
        // event('UserGrade', $model);
    }
    /**
     * 有效期-开始时间
     * @param $value
     * @return array
     */
    public function getSettingAttr($value, $data)
    {
        return json_decode($data['setting'],true);
    }
    /**
     * 获取详情
     */
    public static function detail($kmd_grade_id)
    {
        return self::find($kmd_grade_id);
    }

    /**
     * 获取可用的等级列表
     */
    public static function getUsableList($appId = null)
    {
        $model = new static;
        $appId = $appId ? $appId : $model::$app_id;
        return $model->where('is_delete', '=', '0')
            ->where('app_id', '=', $appId)
            ->where('type', '=', 'supply')
            ->order(['create_time' => 'asc'])
            ->select();
    }

    
}
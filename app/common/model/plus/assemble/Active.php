<?php

namespace app\common\model\plus\assemble;

use app\common\model\BaseModel;

/**
 * Class Partake
 * 参与记录模型
 * @package app\common\model\plus\invitationgift
 */
class Active extends BaseModel
{
    protected $name = 'assemble_activity';
    protected $pk = 'assemble_activity_id';

    //附加字段
    protected $append = ['status_text', 'start_time_text','end_time_text'];
    /**
     * 有效期-开始时间
     * @param $value
     * @return array
     */
    public function getStartTimeTextAttr($value, $data)
    {
        return date('Y-m-d H:i:s', $data['start_time']);
    }

    /**
     * 有效期-开始时间
     * @param $value
     * @return array
     */
    public function getEndTimeTextAttr($value, $data)
    {
        return date('Y-m-d H:i:s', $data['end_time']);
    }
    /**
     * 状态
     * @param $val
     * @return string
     */
    public function getStatusTextAttr($value, $data)
    {
        if($data['status'] == 0){
            return '未生效';
        }
        if ($data['start_time'] > time()) {
            return '未开始';
        }
        if ($data['end_time'] < time()) {
            return '已结束';
        }
        if ($data['start_time'] < time() && $data['end_time'] > time()) {
            return '生效-进行中';
        }
        return '';
    }

    public static function detail($assemble_activity_id)
    {
        return self::with(['file'])->where('assemble_activity_id', '=', $assemble_activity_id)->find($assemble_activity_id);
    }
    /**
     * 处理过的详情数据
     */
    public static function detailWithTrans($assemble_activity_id)
    {
        $model = static::with(['file'])->where('assemble_activity_id', '=', $assemble_activity_id)->find();

        $detail = [
            'title' => $model['title'],
            'image_id' => $model['image_id'],
            'file_path' => $model['file']['file_path'],
            'status' => $model['status'],
            'sort' => $model['sort'],
            'is_delete' => $model['is_delete'],
            'together_time' => $model['together_time'],
            'start_time' => $model['start_time'],
            'end_time' => $model['end_time'],
            'assemble_type' => $model['assemble_type'],
            'active_time' => [
                date('Y-m-d H:i:s', $model['start_time']),
                date('Y-m-d H:i:s', $model['end_time']),
            ],
        ];

        return $detail;
    }

    public function file()
    {
        return $this->belongsTo('app\\common\\model\\file\\UploadFile', 'image_id', 'file_id');
    }

    public function assembleProduct()
    {
        return $this->hasMany('Product', 'assemble_activity_id', 'assemble_activity_id');
    }

}
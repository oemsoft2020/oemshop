<?php

namespace app\common\model\file;

use app\common\model\BaseModel;
/**
 * 文件库模型
 */
class UploadFile extends BaseModel
{
    protected $name = 'upload_file';
    protected $updateTime = false;
    protected $deleteTime = false;
    protected $append = ['file_path'];

    /**
     * 关联文件库分组表
     */
    public function uploadGroup()
    {
        return $this->belongsTo('UploadGroup', 'group_id');
    }

    /**
     * 获取图片完整路径
     * @param $value
     * @param $data
     * @return string
     */
    public function getFilePathAttr($value, $data)
    {
        if (isset($data['storage']) && $data['storage'] === 'local') {
            return self::$base_url . 'uploads/' . $data['save_name'];
        }
        return  (isset($data['file_url']) && isset($data['file_name'])) ? $data['file_url'] . '/' . $data['file_name'] : '';
    }

    /**
     * 文件详情
     */
    public static function detail($file_id)
    {
        return self::find($file_id);
    }

    /**
     * 根据文件名查询文件id
     */
    public static function getFildIdByName($fileName)
    {
        return (new static)->where(['file_name' => $fileName])->value('file_id');
    }

    /**
     * 查询文件id
     */
    public static function getFileName($fileId)
    {
        return (new static)->where(['file_id' => $fileId])->value('file_name');
    }

    /**
     * 添加新记录
     */
    public function add($data)
    {
        return $this->save($data);
    }

}

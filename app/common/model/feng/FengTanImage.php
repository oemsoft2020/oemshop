<?php

namespace app\common\model\feng;

use app\common\model\BaseModel;
/**
 * 商品图片模型
 */
class FengTanImage extends BaseModel
{
    protected $name = 'fengtan_image';
    protected $updateTime = false;

    /**
     * 关联文件库
     */
    public function file()
    {
        return $this->belongsTo('app\\common\\model\\file\\UploadFile', 'image_id', 'file_id')
            ->bind(['file_path', 'file_name', 'file_url']);
    }

    public function addImage($data){

        foreach ($data['imgs'] as $k=>$v){

            $imgData = [
                'fengtan_id'=>$data['fengtan_id'],
                'image_id'=>$v['file_id'],
                'app_id'=>self::$app_id,
                'create_time'=>time(),
            ];
            $this->insert($imgData);

        }
    }

}

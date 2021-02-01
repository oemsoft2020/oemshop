<?php

namespace app\common\model\plus\Vision;

use app\common\model\BaseModel;

/**
 * 视力数据模型
 */
class Vision extends BaseModel
{
    protected $name = 'vision';
    protected $pk = 'vision_id';



    /**
     * 视力数据详情
     * @param $vision_id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function detail($vision_id)
    {
        return self::find($vision_id);
    }



}

<?php

namespace app\common\model\plus\codetemplate;

use app\common\model\BaseModel;

/**
 * 二维码模板模型
 */
class CodeTemplate extends BaseModel
{
    protected $name = 'code_template';
    protected $pk = 'code_template_id';

    /**
     * 关联二维码模板封面图
     * @return \think\model\relation\HasOne
     */
    public function image()
    {
        return $this->hasOne('app\\common\\model\\file\\UploadFile', 'file_id', 'image_id');
    }

    /**
     * 二维码模板详情
     * @param $code_template_id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function detail($code_template_id)
    {
        return self::with(['image'])->find($code_template_id);
    }
}

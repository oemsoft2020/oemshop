<?php

namespace app\common\model\plus\codebatch;

use app\common\model\BaseModel;

class CodeBatch extends BaseModel
{
    protected $name = 'code_batch';
    protected $pk = 'code_batch_id';

    public function product()
    {
        return $this->belongsTo('app\common\model\product\Product', 'product_id');
    }

    /**
     * 批次详情
     * @param $code_batch_id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function detail($code_batch_id)
    {
        return self::find($code_batch_id);
    }
}
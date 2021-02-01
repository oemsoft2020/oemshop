<?php

namespace app\common\model\plus\point;

use app\common\model\BaseModel;

/**
 * Class Exchange
 * 积分兑换模型
 * @package app\common\model\plus\exchange
 */
class ProductSku extends BaseModel
{
    protected $name = 'point_product_sku';
    protected $pk = 'point_product_sku_id';

    /**
     * 详情
     * @param $point_product_sku_id
     * @param array $with
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function detail($point_product_sku_id, $with = [])
    {
        return self::with($with)->find($point_product_sku_id);
    }

    /**
     *关联商品表
     */
    public function product()
    {
        return $this->belongsTo('app\\common\\model\\plus\\point\\Product', 'point_product_id', 'point_product_id');
    }

    /**
     *关联商品sku表
     */
    public function productSku()
    {
        return $this->belongsTo('app\\common\\model\\product\\ProductSku', 'product_sku_id', 'product_sku_id');
    }

}
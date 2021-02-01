<?php

namespace app\common\model\plus\assemble;

use app\common\model\BaseModel;
use app\common\model\product\SpecValue;

/**
 * Class Partake
 * 参与记录模型
 * @package app\common\model\plus\invitationgift
 */
class Product extends BaseModel
{
    protected $name = 'assemble_product';
    protected $pk = 'assemble_product_id';

    protected $append = ['product_sales'];

    /**
     * 计算显示销量 (初始销量 + 实际销量)
     */
    public function getProductSalesAttr($value, $data)
    {
        return $data['sales_initial'] + $data['total_sales'];
    }

    public static function detail($assemble_product_id, $with = ['product.sku', 'assembleSku'])
    {
        return self::with($with)->where('assemble_product_id', '=', $assemble_product_id)->find();
    }

    public function active()
    {
        return $this->belongsTo('app\\common\\model\\plus\\assemble\\Active', 'assemble_activity_id', 'assemble_activity_id');
    }

    public function product()
    {
        return $this->belongsTo('app\\common\\model\\product\\Product', 'product_id', 'product_id');
    }

    public function assembleSku()
    {
        return $this->hasMany('AssembleSku', 'assemble_product_id', 'assemble_product_id');
    }

}
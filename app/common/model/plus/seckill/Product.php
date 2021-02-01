<?php

namespace app\common\model\plus\seckill;

use app\common\model\BaseModel;
use app\common\model\product\SpecValue;

/**
 * Class Partake
 * 参与记录模型
 * @package app\common\model\plus\invitationgift
 */
class Product extends BaseModel
{
    protected $name = 'seckill_product';
    protected $pk = 'seckill_product_id';


    protected $append = ['product_sales'];

    /**
     * 计算显示销量 (初始销量 + 实际销量)
     */
    public function getProductSalesAttr($value, $data)
    {
        return $data['sales_initial'] + $data['total_sales'];
    }

    public static function detail($seckill_product_id, $with = ['product.sku', 'seckillSku'])
    {
        return self::with($with)->where('seckill_product_id', '=', $seckill_product_id)->find();
    }

    public function active()
    {
        return $this->belongsTo('app\\common\\model\\plus\\seckill\\Active', 'seckill_activity_id', 'seckill_activity_id');
    }

    public function product()
    {
        return $this->belongsTo('app\\common\\model\\product\\Product', 'product_id', 'product_id');
    }

    public function seckillSku()
    {
        return $this->hasMany('seckillSku', 'seckill_product_id', 'seckill_product_id');
    }


}
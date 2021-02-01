<?php

namespace app\common\model\plus\bargain;

use app\common\model\BaseModel;

/**
 * 砍价商品模型
 * @package app\common\model\plus\bargain
 */
class Product extends BaseModel
{
    protected $name = 'bargain_product';
    protected $pk = 'bargain_product_id';

    protected $append = ['product_sales'];

    /**
     * 计算显示销量 (初始销量 + 实际销量)
     */
    public function getProductSalesAttr($value, $data)
    {
        return $data['sales_initial'] + $data['total_sales'];
    }

    /**
     *关联商品主表
     */
    public function product()
    {
        return $this->hasOne('app\\common\\model\\product\\Product', 'product_id', 'product_id');
    }

    /**
     * 详情
     */
    public static function detail($bargain_product_id, $with = [])
    {
        return self::with($with)->find($bargain_product_id);
    }

    /**
     *关联商品规格表
     */
    public function bargainSku()
    {
        return $this->hasMany('app\\common\\model\\plus\\bargain\\BargainSku', 'bargain_product_id', 'bargain_product_id');
    }

    /**
     *关联活动表
     */
    public function active()
    {
        return $this->belongsTo('app\\common\\model\\plus\\bargain\\Active', 'bargain_id', 'bargain_id');
    }
}
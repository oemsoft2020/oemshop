<?php

namespace app\common\model\plus\logistics;

use app\common\library\helper;
use app\common\model\BaseModel;
use app\shop\model\product\Product as ProductModel;

/**
 * 库存模型
 */
class Stock extends BaseModel
{
    protected $name = 'shop_stock';
    protected $pk = 'shop_stock_id';

    public function product()
    {
        $this->belongsTo('ProductModel','product_id','product_id');
    }
}

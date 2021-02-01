<?php

namespace app\shop\model\plus\seckill;

use app\common\model\plus\seckill\SeckillSku as SeckillSkuModel;


/**
 * Class Partake
 * 秒杀商品sku模型
 * @package app\shop\model\plus\invitationgift
 */
class SeckillSku extends SeckillSkuModel
{

    public function delAll($seckill_product_id)
    {
        return $this->where('seckill_product_id', '=', $seckill_product_id)->delete();
    }
}
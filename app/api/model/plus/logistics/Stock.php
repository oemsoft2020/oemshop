<?php

namespace app\api\model\plus\logistics;

use app\common\model\plus\logistics\Stock as StockModel;
use app\common\enum\product\DeductStockTypeEnum;
/**
 * 库存模型
 */
class Stock extends StockModel
{
    /**
     * 隐藏字段
     */
    protected $hidden = [
        'is_delete',
        'app_id',
    ];

    /**
     * 更新库存
     *
     */
    public function updateProductStock($productList,$logistics_id=0)
    {
        $model =$this;
        $productSkuData = [];
        foreach ($productList as $product) {
            // 下单减库存
            
            if ($product['deduct_stock_type'] == DeductStockTypeEnum::CREATE) {
                
                $productSkuData[] = [
                    'data' => ['product_stock' => ['dec', $product['total_num']]],
                    'where' => [
                        'product_id' => $product['product_id'],
                        'product_sku_id' => $product['spec_sku_id'],
                        'logistics_id'=>$logistics_id
                    ],
                ];
            }
        }

        return $this->transaction(function () use ($productSkuData) {
            $result = [];
            foreach ($productSkuData as $key => $item) {
                $result[$key] = self::update($item['data'], $item['where']);
            }
            return $this->toCollection($result);
        });
    }

    /* 
    * 付款更新库存
    */
    public function updateStockSales($productList,$logistics_id=0)
    {
        $model =$this;
        $productSkuData = [];
        foreach ($productList as $product) {
            // 付款减库存
            if ($product['deduct_stock_type'] == DeductStockTypeEnum::PAYMENT) {
                
                $productSkuData[] = [
                    'data' => ['product_stock' => ['dec', $product['total_num']]],
                    'where' => [
                        'product_id' => $product['product_id'],
                        'product_sku_id' => $product['spec_sku_id'],
                        'logistics_id'=>$logistics_id
                    ],
                ];
            }
        }

        return $this->transaction(function () use ($productSkuData) {
            $result = [];
            foreach ($productSkuData as $key => $item) {
                $result[$key] = self::update($item['data'], $item['where']);
            }
            return $this->toCollection($result);
        });
    }
}
?>
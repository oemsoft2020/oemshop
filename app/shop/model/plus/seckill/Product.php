<?php

namespace app\shop\model\plus\seckill;

use app\common\model\plus\seckill\Product as ProductModel;

/**
 * Class Partake
 * 参加记录模型
 * @package app\shop\model\plus\invitationgift
 */
class Product extends ProductModel
{
    /**
     * 获取秒杀商品列表
     * @param $param
     */
    public static function getList($seckill_activity_id)
    {
       return self::with(['product', 'seckillSku'])
            ->where('seckill_activity_id', '=', $seckill_activity_id)
            ->order(['sort' => 'asc', 'create_time' => 'asc'])
            ->select();
    }

    /**
     * 新增
     * @param $data
     */
    public function add($seckill_activity_id, $product_list)
    {
        //添加活动
        foreach ($product_list as $product){
            $this->addProduct($seckill_activity_id, $product);
        }
    }

    private function addProduct($seckill_activity_id, $product, $isUpdate = false){
        //添加商品
        $stock = array_sum(array_column($product['spec_list'], 'seckill_stock'));
        $arr = [
            'product_id' => $product['product_id'],
            'seckill_activity_id' => $seckill_activity_id,
            'app_id' => self::$app_id,
            'supply_id' => self::$supply_id,
            'stock' => $stock,
            'limit_num' => $product['limit_num'],
            'sort' => $product['sort'],
            'sales_initial' => $product['sales_initial'],
            'is_delete' => $product['is_delete'],
        ];
        if($isUpdate){
            $model = static::detail($product['seckill_product_id'])?:new self();
        }else{
            $model = new self();
        }
        $model->save($arr);
        //商品规格
        $sku_model = new SeckillSku();
        $save_data = [];
        $not_in_sku_id = [];
        foreach ($product['spec_list'] as $sku) {
            $sku_data = [
                'seckill_product_id' => $model['seckill_product_id'],
                'product_id' => $product['product_id'],
                'product_sku_id' => $sku['product_sku_id'],
                'seckill_price' => $sku['seckill_price'],
                'product_price' => $sku['product_price'],
                'seckill_stock' => $sku['seckill_stock'],
                'product_attr' => isset($sku['product_attr'])?$sku['product_attr']:'',
                'seckill_activity_id' => $seckill_activity_id,
                'app_id' => self::$app_id,
            ];
            if($sku['seckill_product_sku_id'] > 0){
                $detail = $sku_model->find($sku['seckill_product_sku_id']);
                if($detail){
                    $detail->save($sku_data);
                    array_push($not_in_sku_id, $sku['seckill_product_sku_id']);
                }
            }else{
                $save_data[] = $sku_data;
            }
        }
        //删除规格
        count($not_in_sku_id) > 0 && $sku_model->where('seckill_product_id', '=', $model['seckill_product_id'])
            ->whereNotIn('seckill_product_sku_id', $not_in_sku_id)
            ->delete();
        //新增规格
        count($save_data) > 0 && $sku_model->saveAll($save_data);
    }

    /**
     * 修改
     * @param $data
     * @return bool
     */
    public function edit($seckill_activity_id, $product_list)
    {
        //添加活动
        foreach ($product_list as $product){
            $this->addProduct($seckill_activity_id, $product, true);
        }
    }

    public function del($seckill_product_id)
    {
        $this->startTrans();
        try {
            self::destroy($seckill_product_id);
            $model = new SeckillSku();
            $model->delAll($seckill_product_id);
            // 事务提交
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }


    public function getActivityList($seckill_activity_arr, $param)
    {
        $param = json_decode($param['param'], true);

        $model = $this;
        if (isset($param['search']) && !empty($param['search'])) {
            $model = $model->where('product.product_name', 'like', '%' . trim($param['search']) . '%');
        }
        $str = implode(',', $seckill_activity_arr);
        $res = $model->with(['product.image.file', 'seckillSku', 'active.file'])
            ->alias('kill')->join('product', 'product.product_id = kill.product_id')
            ->where('kill.seckill_activity_id', 'in', $str)
            ->paginate($param, false, [
                'query' => request()->request()
            ]);
        if (!empty($res)) {
            $res = $res->toArray();
            foreach ($res['data'] as $key => $val) {
                $arr = array_column($res['data'][$key]['seckillSku'], 'seckill_price');
                if (count($arr) == 1) {
                    $res['data'][$key]['seckill_price'] = '￥' . current($arr);
                } else {
                    sort($arr);
                    $res['data'][$key]['seckill_price'] = '￥' . current($arr) . ' - ￥' . end($arr);
                }

            }
        }
        return $res;
    }

    /**
     * 商品ID是否存在
     */
    public static function isExistProductId($productId)
    {
        return !!(new static)->where('product_id', '=', $productId)
            ->where('is_delete', '=', 0)
            ->value('seckill_product_id');
    }
}
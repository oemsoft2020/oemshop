<?php

namespace app\api\model\plus\bargain;

use app\common\exception\BaseException;
use app\common\model\plus\bargain\Product as BargainProductModel;
use app\api\model\product\Product as ProductModel;


/**
 * 砍价商品模型
 */
class Product extends BargainProductModel
{
    /**
     * 隐藏字段
     */
    protected $hidden = [
        'sales_initial',
        'total_sales',
        'is_delete',
        'app_id',
        'create_time',
        'update_time'
    ];
    /**
     * 获取首页砍价商品显示
     */
    public function getProductList($bargain_activity_id, $limit)
    {
        // 获取列表数据
        $list = $this->with(['product.image.file', 'bargainSku'])
            ->where('bargain_activity_id', '=', $bargain_activity_id)
            ->where('is_delete', '=', 0)
            ->limit($limit)
            ->visible(['product.product_id','product.product_name','product.file_path'])
            ->select();

        foreach ($list as $product) {
            $bargain_arr = array_column($product['bargainSku']->toArray(), 'bargain_price');
            $product_arr = array_column($product['bargainSku']->toArray(), 'product_price');
            sort($bargain_arr);
            sort($product_arr);
            $product['bargain_price'] = current($bargain_arr);
            $product['product_price'] = current($product_arr);
            $product['product']['file_path'] = $product['product']['image'][0]['file_path'];
            unset($product['bargainSku']);
            unset($product['product']['image']);
        }
        return $list;
    }

    /**
     * 获取商品列表
     */
    public static function getBargainProduct($params)
    {
        // 砍价任务详情
        $bargain = self::detail($params['bargain_product_id'], ['bargainSku']);
        $task = Task::detail($params['bargain_task_id']);
        if (empty($task)) {
            throw new BaseException(['msg' => '任务不存在']);
        }
        if ($task['is_buy'] == 1) {
            throw new BaseException(['msg' => '当前砍价任务商品已购买']);
        }
        if (empty($bargain)) {
            throw new BaseException(['msg' => '商品不存在或已结束']);
        }
        // 商品详情
        $product = ProductModel::detail($bargain['product_id']);

        // 商品sku信息
        $bargain_sku = null;
        if ($product['spec_type'] == 10) {
            $bargain_sku = $bargain['bargainSku'][0];
        } else {
            //多规格
            foreach ($bargain['bargainSku'] as $sku) {
                if ($sku['bargain_product_sku_id'] == $params['bargain_product_sku_id']) {
                    $bargain_sku = $sku;
                    break;
                }
            }
        }
        if ($bargain_sku == null) {
            throw new BaseException(['msg' => '商品规格不存在']);
        }
        // 商品sku信息
        $product['product_sku'] = ProductModel::getProductSku($product, $params['product_sku_id']);

        $product['bargain_sku'] = $bargain_sku;
        // 商品列表
        $productList = [$product->hidden(['category', 'content', 'image', 'sku'])];
        foreach ($productList as &$item) {
            // 商品单价
            $item['product_price'] = $task['actual_price'];
            // 商品购买数量
            $item['total_num'] = 1;
            $item['spec_sku_id'] = $item['product_sku']['spec_sku_id'];
            // 商品购买总金额
            $item['total_price'] = $task['actual_price'];
            $item['bargain_product_sku_id'] = $bargain_sku['bargain_product_sku_id'];
            $item['product_sku_id'] = $params['product_sku_id'];
            $item['product_source_id'] = $bargain_sku['bargain_product_id'];
            $item['sku_source_id'] = $bargain_sku['bargain_product_sku_id'];
            // 砍价订单id
            $item['bill_source_id'] = $params['bargain_task_id'];
        }
        return $productList;
    }


    /**
     * 获取列表页砍价数据
     * 目前未分页，后续有可能会分页
     */
    public function getActivityList($bargain_activity_id)
    {
        // 获取列表数据
        $list = $this->with(['product.image.file', 'bargainSku'])
            ->where('bargain_activity_id', '=', $bargain_activity_id)
            ->where('is_delete', '=', 0)
            ->visible(['product.product_id','product.product_name','product.file_path'])
            ->select();

        foreach ($list as $product) {
            $bargain_arr = array_column($product['bargainSku']->toArray(), 'bargain_price');
            $product_arr = array_column($product['bargainSku']->toArray(), 'product_price');
            sort($bargain_arr);
            sort($product_arr);
            $product['bargain_price'] =  current($bargain_arr);
            $product['product_price'] =  current($product_arr);
            $product['product']['file_path'] = $product['product']['image'][0]['file_path'];
            unset($product['bargainSku']);
            unset($product['product']['image']);
        }
        return $list;
    }


    /**
     * 拼团商品详情
     */
    public function getBargainDetail($bargain_product_id)
    {
        $result = $this->with(['product.image.file', 'bargainSku.productSku.image'])
            ->where('bargain_product_id', '=', $bargain_product_id)->find();

        if (!empty($result)) {
            $bargain_arr = array_column($result->toArray()['bargainSku'], 'bargain_price');
            $product_arr = array_column($result->toArray()['bargainSku'], 'product_price');
            sort($bargain_arr);
            sort($product_arr);
            $result['bargain_price'] =  current($bargain_arr);
            $result['line_price'] = current($product_arr);
            if (count($bargain_arr) > 1) {
                $result['bargain_high_price'] = end($bargain_arr);
                $result['line_high_price'] = end($product_arr);
            }
        }
        return $result;
    }
}
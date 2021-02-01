<?php

namespace app\api\model\plus\assemble;

use app\common\exception\BaseException;
use app\common\model\plus\assemble\Product as AssembleProductModel;
use app\api\model\product\Product as ProductModel;
use think\Paginator;

/**
 * 限时拼团模型
 */
class Product extends AssembleProductModel
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
     * 获取首页拼团商品显示
     */
    public function getProductList($assemble_activity_id, $limit)
    {
        // 获取列表数据
        $list = $this->with(['product.image.file', 'assembleSku'])
            ->where('assemble_activity_id', '=', $assemble_activity_id)
            ->where('is_delete', '=', 0)
            ->limit($limit)
            ->visible(['product.product_id','product.product_name','product.file_path','product.setting'])
            ->select();

        foreach ($list as $product) {
            $assemble_arr = array_column($product['assembleSku']->toArray(), 'assemble_price');
            $product_arr = array_column($product['assembleSku']->toArray(), 'product_price');
            sort($assemble_arr);
            sort($product_arr);
            $product['assemble_price'] = current($assemble_arr);
            $product['product_price'] = current($product_arr);
            $product['product']['file_path'] = $product['product']['image'][0]['file_path'];
            unset($product['assembleSku']);
            unset($product['product']['image']);
        }
        return $list;
    }

    /**
     * 获取列表页拼团数据
     * 目前未分页，后续有可能会分页
     */
    public function getActivityList($assemble_activity_id)
    {
        // 获取列表数据
        $list = $this->with(['product.image.file', 'assembleSku'])
            ->where('assemble_activity_id', '=', $assemble_activity_id)
            ->where('is_delete', '=', 0)
            ->visible(['product.product_id','product.product_name','product.file_path','product.setting'])
            ->select();

        foreach ($list as $product) {
            $assemble_arr = array_column($product['assembleSku']->toArray(), 'assemble_price');
            $product_arr = array_column($product['assembleSku']->toArray(), 'product_price');
            sort($assemble_arr);
            sort($product_arr);
            $product['assemble_price'] =  current($assemble_arr);
            $product['product_price'] =  current($product_arr);
            $product['product']['file_path'] = $product['product']['image'][0]['file_path'];
            unset($product['assembleSku']);
            unset($product['product']['image']);
        }
        return $list;
    }
    /**
     * 获取所有拼团商品数据
     * 
     */
    public function getProductListAll($params)
    {
        // 获取列表数据
        $list = $this->alias('assemble_product')->with(['product.image.file', 'assembleSku','active'])
            ->join('assemble_activity','assemble_product.assemble_activity_id=assemble_activity.assemble_activity_id')
            ->where('assemble_product.is_delete', '=', 0)
            ->where('assemble_activity.end_time', '>', time())
            ->where('assemble_activity.status', '=', 1)
            ->where('assemble_activity.is_delete', '=', 0)
            ->order('assemble_product.create_time','desc')
            ->visible(['product.product_id','product.product_name','product.file_path','product.setting'])
            ->paginate($params, false)->toArray();
        foreach ($list['data'] as &$product) {
            $assemble_arr = array_column($product['assembleSku'], 'assemble_price');
            $product_arr = array_column($product['assembleSku'], 'product_price');
            sort($assemble_arr);
            sort($product_arr);
            $product['assemble_price'] =  current($assemble_arr);
            $product['product_price'] =  current($product_arr);
            $product['product']['file_path'] = $product['product']['image'][0]['file_path'];
            unset($product['assembleSku']);
            unset($product['product']['image']);
        }
        return $list;
    }

    /**
     * 获取拼团商品列表
     */
    public static function getAssembleProduct($params)
    {
        // 拼团详情
        $assemble = self::detail($params['assemble_product_id'], ['assembleSku','active']);
        if (empty($assemble)) {
            throw new BaseException(['msg' => '拼团商品不存在或已结束']);
        }
        // 拼团商品详情
        $product = ProductModel::detail($assemble['product_id']);
        // 拼团商品sku信息
        $assemble_sku = null;
        if ($product['spec_type'] == 10) {
            $assemble_sku = $assemble['assembleSku'][0];
        } else {
            //多规格
            foreach ($assemble['assembleSku'] as $sku) {
                if ($sku['assemble_product_sku_id'] == $params['assemble_product_sku_id']) {
                    $assemble_sku = $sku;
                    break;
                }
            }
        }
        if ($assemble_sku == null) {
            throw new BaseException(['msg' => '拼团商品规格不存在']);
        }

        // 拼团商品sku信息
        $product['product_sku'] = ProductModel::getProductSku($product, $params['product_sku_id']);
        $product['assemble_sku'] = $assemble_sku;
        // 拼团商品列表
        $productList = [$product->hidden(['category', 'content', 'image', 'sku'])];
        foreach ($productList as &$item) {
            // 商品单价
            $item['product_price'] = $assemble_sku['assemble_price'];
            // 商品购买数量
            $item['total_num'] = $params['product_num'];
            $item['spec_sku_id'] = $item['product_sku']['spec_sku_id'];
            // 商品购买总金额
            $item['total_price'] = $assemble_sku['assemble_price'] * $item['total_num'];
            $item['point_num'] = $assemble_sku['point_num'];
            $item['assemble_product_sku_id'] = $assemble_sku['assemble_product_sku_id'];
            $item['product_sku_id'] = $params['product_sku_id'];
            $item['product_source_id'] = $assemble_sku['assemble_product_id'];
            $item['sku_source_id'] = $assemble_sku['assemble_product_sku_id'];
            // 拼团活动id
            $item['activity_id'] = $assemble['assemble_activity_id'];
            // 拼团订单id
            $item['bill_source_id'] = $params['assemble_bill_id'];
            // 拼团最大购买数
            $item['assemble_product'] = [
                'limit_num' => $assemble['limit_num']
            ];
            //拼团类型
            $item['assemble_activity_type']=$assemble['active']['assemble_type'];
        }
        return $productList;
    }

    /**
     * 拼团商品详情
     */
    public function getAssembleDetail($assemble_product_id)
    {
        $result = $this->with(['product.image.file', 'assembleSku.productSku.image'])
            ->where('assemble_product_id', '=', $assemble_product_id)->find();
            
        if (!empty($result)) {
            $assemble_arr = array_column($result->toArray()['assembleSku'], 'assemble_price');
            $product_arr = array_column($result->toArray()['assembleSku'], 'product_price');
            sort($assemble_arr);
            sort($product_arr);
            $result['assemble_price'] =  current($assemble_arr);
            $result['line_price'] = current($product_arr);
            if (count($assemble_arr) > 1) {
                $res['assemble_high_price'] = end($assemble_arr);
                $res['line_high_price'] = end($product_arr);
            }
        }
        return $result;
    }
    /**
     * 更新拼团商品
     */
    public function  edit($data)
    {
        $model = self::detail($data['assemble_product_id']);
        $arr = [
            'limit_num' => $data['limit_num'],
            'stock' => $data['stock'],
            'assemble_num' => $data['assemble_num'],
        ];
        $model->save($arr);
        //商品规格
        $sku_model = new AssembleSku();
        foreach ($data['assembleSku'] as $sku) {
            $sku_data = [
                'assemble_price' => $data['assemble_price'],
                'assemble_stock' => $data['stock'],
            ];
            $detail = $sku_model->find($sku['assemble_product_sku_id']);
            if($detail){
                $detail->save($sku_data);
            }
        }
        return true;
    }
    /**
     * 更新拼团商品
     */
    public function  del($assemble_product_id)
    {
        $model = self::detail($assemble_product_id);
        $model->delete();
        //商品规格
        $sku_model = new AssembleSku();
        $sku_model->where('assemble_product_id','=',$assemble_product_id)->delete();
        return true;
    }
    /**
     * 添加拼团商品
     */
    public function add($activity,$product)
    {
        $this->startTrans();
        try {
           $arr = [
                'product_id' => $product['product_id'],
                'limit_num' => $product['limit_num'],
                'stock' => $product['assemble_stock'],
                'assemble_activity_id' => $activity['assemble_activity_id'],
                'assemble_num' => $product['assemble_num'],
                'sort' => $product['sort'],
                'app_id' => $activity['app_id'],
                'supply_id' =>$activity['supply_id'],
            ];
            $model = new self();

            $model->save($arr);
            //商品规格
            $sku_data = [
                'assemble_product_id' => $model['assemble_product_id'],
                'product_id' => $product['product_id'],
                'product_sku_id' => $product['sku']['product_sku_id'],
                'assemble_price' => $product['assemble_price'],
                'product_price' => $product['product_price'],
                'assemble_stock' => $product['assemble_stock'],
                'assemble_activity_id' =>  $activity['assemble_activity_id'],
                'app_id' =>  $activity['app_id'],
            ];
            $sku_model = new AssembleSku();
            $sku_model->save($sku_data);
            // 事务提交
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
        
    }
}

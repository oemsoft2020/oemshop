<?php

namespace app\common\model\product;

use app\common\model\BaseModel;
/**
 * 商品收藏
 */
class ProductCollect extends BaseModel
{
    protected $name = 'product_collect';
    protected $pk = 'collect_id';
    // protected $updateTime = false;
    protected $autoWriteTimestamp = true;

    /**
     * 商品
     */
    public function product()
    {
        return $this->belongsTo('app\\common\\model\\product\\Product');
    }

    /**
     * 关联商品图片表
     */
    public function image()
    {
        return $this->hasMany('app\\common\\model\\product\\ProductImage')->order(['id' => 'asc']);
    }
    
    /**
     * 获取商品列表
     */
    public function getList($param, $user)
    {
        // 商品列表获取条件
        $params = array_merge([
            'product_id' => 0,       // 关系id
            'list_rows' => 15,       // 每页数量
        ], $param);

        // 筛选条件
        $filter = [];
        $model = $this;
        if ($params['product_id'] > 0) {
            $model = $model->where('product_id', 'IN', $params['product_id']);
        }
        // 排序规则
        $sort = ['update_time'=>'desc'];
        // 商品表名称
        $tableName = $this->getTable();
        // 执行查询
        $list = $model
            ->field('collect_id, product_id, status, create_time')
            ->with(['product.image.file'])
            ->where('status', '=', 1)
            ->where('user_id', '=', $user['user_id'])
            ->where($filter)
            ->order($sort)
            ->paginate($params, false, [
                'query' => \request()->request(),
            ]);

        // 整理列表数据并返回
        return $this->setCollectListData($list, true);
    }

    /**
     * 设置展示的数据
     */
    protected function setCollectListData($data, $isMultiple = true, callable $callback = null)
    {
        if (!$isMultiple) $dataSource = [&$data]; else $dataSource = &$data;
        // 整理商品列表数据
        
        foreach ($dataSource as $product) {
            // 商品主图
            $p = [];
            $p['product_id'] = $product['product']['product_id'];
            $p['product_name'] = $product['product']['product_name'];
            $p['product_image'] = $product['product']['image'][0]['file_path'];
            $p['product_price'] = $product['product']['product_price'];
            $p['product_sales'] = $product['product']['product_sales'];
            $p['product_status'] = $product['product']['product_status'];
            unset($product['product']);
            $product['product'] = $p;
            // 回调函数
            is_callable($callback) && call_user_func($callback, $product);
        }
        return $data;
    }
}

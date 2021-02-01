<?php

namespace app\common\model\plus\icp;

use app\common\model\BaseModel;

/**
 * 优惠券模型
 */
class Icp extends BaseModel
{
    protected $name = 'icp_record';
    protected $pk = 'icp_record_id';

    /**
     * 获取icp查询记录列表
     * @param $param
     * @return mixed
     * @throws \think\db\exception\DbException
     */
    public function getLists($param)
    {
        // 商品列表获取条件
        $params = array_merge([
            'product_status' => 10,         // 商品状态
            'category_id' => 0,     // 分类id
        ], $param);
        // 筛选条件
        $model = $this;
        if (!empty($params['query_string'])) {
            $model = $model->where('query_string', 'like', '%' . trim($params['query_string']) . '%');
        }
        if (!empty($params['start_time']) && !empty($params['end_time'])) {
            $model = $model->where('product_name', 'like', '%' . trim($params['search']) . '%');
        }
        $list = $model
            ->with(['category', 'image.file'])
            ->where('icp_api', '=', 'aizhan')
            ->where('is_deleted', '=', 0)
            ->order(['create_time' => 'asc'])
            ->paginate($params, false, [
                'query' => \request()->request(),
            ]);
        // 整理列表数据并返回
        return $this->setProductListData($list, true);
    }

    /**
     * 设置商品展示的数据
     */
    protected function setProductListData($data, $isMultiple = true, callable $callback = null)
    {
        if (!$isMultiple) $dataSource = [&$data]; else $dataSource = &$data;
        // 整理商品列表数据
        foreach ($dataSource as &$product) {
            // 商品主图
            $product['product_image'] = isset($product['image'][0]) ? $product['image'][0]['file_path'] : '';
            // 商品默认规格
            $product['product_sku'] = isset($product['sku'][0]) ? $product['sku'][0] : '';
            // 回调函数
            is_callable($callback) && call_user_func($callback, $product);
        }
        return $data;
    }


}

<?php

namespace app\shop\model\plus\logistics;

use app\admin\model\page\Page;
use app\shop\model\user\User as UserModel;
use app\common\model\plus\logistics\Stock as StockModel;
use app\common\exception\BaseException;
use app\shop\model\product\Product as ProductModel;
/**
 * 模型模型
 */
class Stock extends StockModel
{
    /**
     * @param $data array 查询条件
     * @return mixed
     */
    public function getList($data)
    {
        $model = $this;
        
        $productModel =new ProductModel();
        return $productModel->with(['product'])->where($data)->
        order(['create_time' => 'desc'])->paginate($data, false, [
            'query' => request()->request()
        ]);
        
    }

    /**
     * 添加新记录
     */
    public function add($data)
    {
        $data['app_id'] = self::$app_id;
        
        $data['region'] = join(',', array_values($data['rule'][0]['citys']));
        unset($data['rule']);       
        return self::create($data);
    }

    /**
     * 编辑记录
     */
    public function edit($data)
    {
        $data['app_id'] = self::$app_id;
        
        $data['region'] = join(',', array_values($data['rule'][0]['citys']));
        unset($data['rule']);       
        return $this->save($data);
    }

    /**
     * 删除记录
     */
    public function remove()
    {
        return $this->delete();
    }
}

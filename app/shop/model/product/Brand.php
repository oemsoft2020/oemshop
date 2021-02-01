<?php

namespace app\shop\model\product;

use think\facade\Cache;
use app\common\model\product\Brand as BrandModel;

/**
 * 商品品牌模型
 */
class Brand extends BrandModel
{
    /**
     * 添加新记录
     */
    public function add($data)
    {
        $data['app_id'] = self::$app_id;
        $this->deleteCache();
        return $this->save($data);
    }

    /**
     * 编辑记录
     */
    public function edit($data)
    {
        
        $this->deleteCache();
        return $this->save($data) !== false;
    }

    /**
     * 删除商品品牌
     */
    public function remove($brand_id)
    {
        // 判断是否存在商品
        $product = new Product();
        $productCount = $product->where('is_delete', '=', 0)->where(['brand_id' => $brand_id])->count();
        if ($productCount) {
            $this->error = '该品牌下存在' . $productCount . '个商品，不允许删除';
            return false;
        }
        $this->deleteCache();
        return $this->delete();
    }

    /**
     * 删除缓存
     */
    private function deleteCache()
    {
        return Cache::delete('kmd_brand_' . self::$app_id);
    }

}

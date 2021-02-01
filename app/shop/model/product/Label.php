<?php

namespace app\shop\model\product;

use think\facade\Cache;
use app\common\model\product\Label as LabelModel;

/**
 * 商品标签模型
 */
class Label extends LabelModel
{
    /**
     * 添加新记录
     */
    public function add($data)
    {
        $data['app_id'] = self::$app_id;
        if(isset($data['type'])&&!empty($data['type'])){
            $data['type'] =  $data['type'];
        }else{
            $data['type'] =  'product';
        }
        
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
     * 删除商品标签
     */
    public function remove($kmd_label_id)
    {
        // 判断是否存在商品
        $product = new Product();
        $productCount = $product->where('is_delete', '=', 0)->where('FIND_IN_SET(:kmd_label_id,kmd_label_ids)',['kmd_label_id' => $kmd_label_id])->count();
        if ($productCount) {
            $this->error = '该标签下存在' . $productCount . '个商品，不允许删除';
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
        return Cache::delete('kmd_label_' . self::$app_id);
    }

}

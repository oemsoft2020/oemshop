<?php

namespace app\shop\model\product;

use think\facade\Cache;
use app\common\model\product\Category as CategoryModel;

/**
 * 商品分类模型
 */
class Category extends CategoryModel
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
        // 验证：一级分类如果存在子类，则不允许移动
        if ($data['parent_id'] > 0 && static::hasSubCategory($this['category_id'])) {
            $this->error = '该分类下存在子分类，不可以移动';
            return false;
        }
        $this->deleteCache();
        !array_key_exists('image_id', $data) && $data['image_id'] = 0;
        return $this->save($data) !== false;
    }

    /**
     * 删除商品分类
     */
    public function remove($categoryId)
    {
        // 判断是否存在商品
        if ($productCount = (new Product)->getProductTotal(['category_id' => $categoryId])) {
            $this->error = '该分类下存在' . $productCount . '个商品，不允许删除';
            return false;
        }
        // 判断是否存在子分类
        if (static::hasSubCategory($categoryId)) {
            $this->error = '该分类下存在子分类，请先删除';
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
        return Cache::delete('category_' . static::$app_id);
    }

    // 添加商品分类
    public function addCate($parent_cate = '',$child_cate = '')
    {
        if(!empty($parent_cate)){
            $parent = [
                'name' => $parent_cate,
                'app_id' => self::$app_id
            ];

            $cate_id = $this->where('name',$parent_cate)->value('category_id');

            if(empty($cate_id)){
                $cate_id = $this->insertGetId($parent);
            }

            if(!empty($cate_id) && !empty($child_cate)){
                // 父分类不为空才添加子分类
                $child_id = $this->where('name',$child_cate)->value('category_id');
                if($child_id){
                    return $child_id;//子分类存在直接返回，是否要修改所属上级分类?
                }else{
                    $child = [
                        'name' => $child_cate,
                        'app_id' => self::$app_id,
                        'parent_id' => $cate_id
                    ];

                    return $this->insertGetId($child);
                }
                
            }

            return $cate_id;
        }
    }
}

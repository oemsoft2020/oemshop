<?php

namespace app\common\model\product;

use think\facade\Cache;
use app\common\model\BaseModel;

/**
 * 产品分类模型
 */
class Category extends BaseModel
{
    protected $pk = 'category_id';
    protected $name = 'category';

    /**
     * 分类图片
     */
    public function images()
    {
        return $this->hasOne('app\\common\\model\\file\\UploadFile', 'file_id', 'image_id');
    }

    /**
     * 充值套餐详情
     */
    public static function detail($category_id)
    {
        return self::find($category_id);
    }


    public function detailWithImage($where)
    {
        return $this->with(['image',])->where($where)->find();
    }

    /**
     * 所有分类
     */
    public static function getALL()
    {
        $model = new static;
        if (!Cache::get('category_' . $model::$app_id)) {
            $data = $model->with(['images'])->order(['sort' => 'asc', 'create_time' => 'asc'])->select();
            $all = !empty($data) ? $data->toArray() : [];
            $tree = [];
            foreach ($all as $first) {
                if ($first['parent_id'] != 0) continue;
                $twoTree = [];
                foreach ($all as $two) {
                    if ($two['parent_id'] != $first['category_id']) continue;
                    $threeTree = [];
                    foreach ($all as $three)
                        $three['parent_id'] == $two['category_id']
                        && $threeTree[$three['category_id']] = $three;
                    !empty($threeTree) && $two['child'] = $threeTree;
                    array_push($twoTree, $two);
                }
                if (!empty($twoTree)) {
                    array_multisort(array_column($twoTree, 'sort'), SORT_ASC, $twoTree);
                    $first['child'] = $twoTree;
                }
                array_push($tree, $first);
            }
            Cache::tag('cache')->set('category_' . $model::$app_id, compact('all', 'tree'));
        }
        return Cache::get('category_' . $model::$app_id);
    }

    /**
     * 获取所有分类
     */
    public static function getCacheAll()
    {
        return self::getALL()['all'];
    }

    /**
     * 获取所有分类(树状结构)
     */
    public static function getCacheTree()
    {
        return self::getALL()['tree'];
    }

    /**
     * 获取所有分类(树状结构)
     * @return string
     */
    public static function getCacheTreeJson()
    {
        return json_encode(static::getCacheTree());
    }

    /**
     * 获取指定分类下的所有子分类id
     */
    public static function getSubCategoryId($parent_id, $all = [])
    {
        $arrIds = [$parent_id];
        empty($all) && $all = self::getCacheAll();
        foreach ($all as $key => $item) {
            if ($item['parent_id'] == $parent_id) {
                unset($all[$key]);
                $subIds = self::getSubCategoryId($item['category_id'], $all);
                !empty($subIds) && $arrIds = array_merge($arrIds, $subIds);
            }
        }
        return $arrIds;
    }

    /**
     * 指定的分类下是否存在子分类
     */
    protected static function hasSubCategory($parentId)
    {
        $all = self::getCacheAll();
        foreach ($all as $item) {
            if ($item['parent_id'] == $parentId) {
                return true;
            }
        }
        return false;
    }


    /**
     * 关联图片
     */
    public function image()
    {
        return $this->belongsTo('app\common\model\file\UploadFile', 'image_id', 'file_id');
    }

    // 获取关联的分类
    public function getCateName($cate_id = '')
    {
        $data = [];
        if(!empty($cate_id)){
            // 判断当前分类层级
            $map['app_id'] = self::$app_id;
            $map['category_id'] = $cate_id;
            $cate = $this->where($map)->find();
            // dump($cate_id);
            if($cate){
                // 一级分类
                if($cate['parent_id'] == 0){
                    return ['cate_name_1' => $cate['name'],'cate_name_2'=>' '];
                
                }else{
                    // 查询上级分类
                    $parent_cate_name = $this->where(['category_id'=>$cate['parent_id'],'app_id'=>self::$app_id])->value('name');

                    $data['cate_name_1'] = $parent_cate_name;
                    $data['cate_name_2'] = $cate['name'];
                }
            }

        }

        return $data;
    }


}

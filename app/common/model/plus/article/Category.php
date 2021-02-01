<?php

namespace app\common\model\plus\article;

use think\facade\Cache;
use app\common\model\BaseModel;

/**
 * 文章分类模型
 */
class Category extends BaseModel
{
    protected $name = 'article_category';
    protected $pk = 'category_id';

    /**
     * 所有分类
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getALL()
    {
        $model = new static;
        return $model->order(['sort' => 'asc', 'create_time' => 'asc'])->select();
    }

    /**
     * 所有分类
     */
    public static function getALLList()
    {
        $model = new static;
       
        $data = $model->order(['sort' => 'asc', 'create_time' => 'asc'])->select();
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
        //     Cache::tag('cache')->set('cardcategory_' . $model::$app_id, compact('all', 'tree'));
        // }
        return compact('all', 'tree');
    }

    /**
     * 获取所有分类
     */
    public static function getCacheAll()
    {
        return self::getALLList()['all'];
    }

    /**
     * 获取所有分类(树状结构)
     */
    public static function getCacheTree()
    {
        return self::getALLList()['tree'];
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

}
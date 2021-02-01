<?php

namespace app\common\model\plus\card;

use app\common\model\BaseModel;
use think\facade\Cache;


class CardCategory extends BaseModel
{
    protected $name = 'card_category';
    protected $pk = 'card_category_id';


    /*
     * 分类详情
     */
    public static function detail($category_id)
    {
        return self::find($category_id);
    }

    /**
     * 所有分类
     */
    public static function getALL()
    {
        $model = new static;
       
        $data = $model->where('is_delete',0)->order(['sort' => 'asc', 'create_time' => 'asc'])->select();
        $all = !empty($data) ? $data->toArray() : [];
        $tree = [];
        foreach ($all as $first) {
            if ($first['parent_id'] != 0) continue;
            $twoTree = [];
            foreach ($all as $two) {
                if ($two['parent_id'] != $first['card_category_id']) continue;
                $threeTree = [];
                foreach ($all as $three)
                    $three['parent_id'] == $two['card_category_id']
                    && $threeTree[$three['card_category_id']] = $three;
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
                $subIds = self::getSubCategoryId($item['card_category_id'], $all);
                !empty($subIds) && $arrIds = array_merge($arrIds, $subIds);
            }
        }
        return $arrIds;
    }

    
}
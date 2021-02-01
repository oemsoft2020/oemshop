<?php

namespace app\shop\model\plus\article;

use think\facade\Cache;
use app\common\model\plus\article\Category as CategoryModel;
use app\shop\model\plus\article\Article as ArticleModel;

/**
 * 文章分类模型
 */
class Category extends CategoryModel
{

    /**
     * 关联image表
     * @return \think\model\relation\HasOne
     */
    public function images()
    {
        return $this->hasOne('app\\common\\model\\file\\UploadFile', 'file_id', 'image_id');
    }


    public function getShowTypeAttr($value)
    {
        $status = [
            0 => ['id' => $value, 'name' => '默认'],
            1 => ['id' => $value, 'name' => '瀑布流']
        ];
        return $status[$value];
    }

    /**
     * @return array
     * 文章分类菜单 (树状)
     */
    public function getTreeAll()
    {
        $data = self::with(['images'])->order(['sort' => 'asc', 'create_time' => 'asc'])->select();

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
        return $tree;
    }


    /**
     * 分类详情
     */
    public static function detail($category_id)
    {
        return static::find($category_id);
    }

    /**
     * 添加新记录
     */
    public function add($data)
    {
        $data['app_id'] = self::$app_id;
        return $this->save($data);
    }

    /**
     * 编辑记录
     */
    public function edit($data)
    {
        $data['create_time'] = strtotime($data['create_time']);
        $data['update_time'] = time();
        return $this->save($data);
    }

    /**
     * 删除商品分类
     */
    public function remove()
    {
        // 判断是否存在文章
        $articleCount = ArticleModel::getArticleTotal(['category_id' => $this['category_id']]);
        if ($articleCount > 0) {
            $this->error = '该分类下存在' . $articleCount . '个文章，不允许删除';
            return false;
        }
        return $this->delete();
    }

    /**
     * 删除缓存
     */
    private function deleteCache()
    {
        return Cache::delete('article_category_' . self::$app_id);
    }


    /**
     * 根据条件查询分类 2020年11月12日
     * @param $where
     */
    public function WithImages($where)
    {
        return $this->with('images')->where($where)->find();
    }
}
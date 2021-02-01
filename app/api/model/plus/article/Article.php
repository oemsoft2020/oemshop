<?php

namespace app\api\model\plus\article;

use app\common\exception\BaseException;
use app\common\model\plus\article\Article as ArticleModel;

/**
 * 文章模型
 */
class Article extends ArticleModel
{
    /**
     * 追加字段
     */
    protected $append = [
        'view_time'
    ];

    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'is_delete',
        'app_id',
        'update_time'
    ];

    /**
     * 文章详情：HTML实体转换回普通字符
     */
    public function getArticleContentAttr($value)
    {
        return htmlspecialchars_decode($value);
    }

    public function getViewTimeAttr($value, $data)
    {
        return $data['virtual_views'] + $data['actual_views'];
    }
    /**
     * 文章详情
     */
    public static function detail($article_id)
    {
        if (!$model = parent::detail($article_id)) {
            throw new BaseException(['msg' => '文章不存在']);
        }
        // 累积阅读数
        $model->where('article_id', '=', $article_id)->inc('actual_views', 1)->update();
        return $model;
    }
//
//    /**
//     * 获取文章列表
//     */
//    public function getList($category_id = 0, $params)
//    {
//        $model = $this;
//
//        $category_id == 0 && $model = $model->hasWhere('category', ['parent_id' => 0]);
//
//        if($category_id > 0){
//            $categoryModel=new Category;
//            $category=$categoryModel->where('parent_id','=',$category_id)->field('category_id')->select()->toArray();
//            $data=array_column($category,'category_id');
//            array_push($data,$category_id+0);
//            $model = $model->where('category_id', 'in', $data);
//        }
//
//        $res = $model
//            ->with(['image', 'category'])
//            ->where('article_status', '=', 1)
//            ->where('is_delete', '=', 0)
//            ->order(['article_sort' => 'asc', 'create_time' => 'desc'])
//            ->paginate($params, false, [
//                'query' => \request()->request()
//            ]);
//        return $res;
//    }

    /**
     * 获取文章列表
     */
    public function getList($category_id = 0,$category_parent_id=0, $params=[])
    {
        $model = $this;
        if ($category_parent_id > 0) {
            $categoryModel = new Category();
            $categoryList =$categoryModel->where('parent_id','=',$category_parent_id)->order(['sort' => 'asc', 'create_time' => 'asc'])->select()->toArray();
            if (sizeof($categoryList) > 0) {
                $category_ids = array_column($categoryList,'category_id');
                $model = $model->where('category_id','in',$category_ids);
            }
        }
        if($category_id > 0){
            $categoryModel = new Category;
            $data = $categoryModel->getSubCategoryId($category_id);
            $model = $model->where('category_id', 'in', $data);
        }
    
        return $model->with(['image', 'category'])
            ->where('article_status', '=', 1)
            ->where('is_delete', '=', 0)
            ->order(['article_sort' => 'asc', 'create_time' => 'desc'])
            ->paginate($params, false, [
                'query' => \request()->request()
            ]);
    }

}
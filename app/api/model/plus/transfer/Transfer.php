<?php

namespace app\api\model\plus\transfer;

use app\common\exception\BaseException;
use app\common\model\plus\transfer\Transfer as TransferModel;

/**
 * 文章模型
 */
class Transfer extends TransferModel
{
    /**
     * 追加字段
     */
//    protected $append = [
//        'view_time'
//    ];

    /**
     * 隐藏字段
     * @var array
     */
//    protected $hidden = [
//        'is_delete',
//        'app_id',
//        'update_time'
//    ];

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

    /**
     * 获取文章列表
     */
    public function getList($category_id = 0, $params)
    {
        $model = $this;

        $category_id > 0 && $model = $model->where('status', '=', $category_id);

        return $model->where('is_delete','=',1)
            ->order('time','desc')
            ->paginate($params, false, [
                'query' => request()->request()
            ]);

//        return $model ->with(['image', 'category'])
//            ->where('article_status', '=', 1)
//            ->where('is_delete', '=', 0)
//            ->order(['article_sort' => 'asc', 'create_time' => 'desc'])
//            ->paginate($params, false, [
//                'query' => \request()->request()
//            ]);
    }

    public function add($data)
    {

        $data['app_id'] = self::$app_id;
        $data['user_id'] = 111;
        $data['time'] = time();
        $data['status'] = 1;
        $id = $this->insertGetId($data);

       $data['status'] =2;
       $data['pid'] =$id;

       return $this->save($data);

    }

}
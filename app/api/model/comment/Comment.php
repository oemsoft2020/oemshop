<?php

namespace app\api\model\comment;
use app\common\model\comment\Comment as CommentModel;




/**
 * 普通评论模型
 */
class Comment extends commentModel
{
    protected $hidden = [
        'app_id',
        'update_time'
    ];


    /**
     * 获取评论总数
     */
    public function getCount($product_id){

        $model = new CommentModel();

        $count = $model->where('product_id','=',$product_id)->count();

        return $count;
    }
}
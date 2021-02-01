<?php

namespace app\api\controller\plus\transfer;

use app\api\controller\Controller;
use app\api\model\plus\transfer\Transfer as TransferModel;
use app\api\model\plus\article\Category as CategoryModel;

/**
 * 文章控制器
 */
class Transfer extends Controller
{
    /**
     *获取分类
     */


    /**
     * 文章列表
     */
    public function index($category_id = 0)
    {

//        return  222;
        $model = new TransferModel;
        $list = $model->getList($category_id, $this->postData());
        $list = $this->chance($list);
        return $this->renderSuccess('', compact('list'));
    }






    /**
     * 功能：转换状态 , 时间
     * $list array();
     * */

    public function chance($list= array())
    {
        foreach ($list as $k =>$v ){
            $list[$k]['time'] = date('Y-m-d H:i:s',$v['time']);
            if($v['status']==1){
                $list[$k]['status'] = '已赠送';
            }else{
                $list[$k]['status'] = '已接收';
            }
        }
        return $list;

    }



    /**
     *文章详情
     */
    public function detail($article_id)
    {
        $detail = ArticleModel::detail($article_id);
        return $this->renderSuccess('', compact('detail'));
    }

}
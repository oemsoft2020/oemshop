<?php

namespace app\api\controller\plus\article;

use app\api\controller\Controller;
use app\api\model\plus\article\Article as ArticleModel;
use app\api\model\plus\article\Category as CategoryModel;
use app\common\model\user\UserThumbsUp as UserThumbUpModel;

/**
 * 文章控制器
 */
class Article extends Controller
{
    /**
     *获取分类
     */
    public function category($category_id = 0)
    {
       
        $model = new CategoryModel;
        $category = CategoryModel::getALLList()['tree'];

        return $this->renderSuccess('', compact('category'));
    }
    public function getCategory($category_id)
    {
        $CategoryModel = new CategoryModel;
        // 文章分类
        $category = $CategoryModel->getCategory($category_id);
        return $this->renderSuccess('', compact('category'));
    }
    /**
     * 文章列表
     */
    public function index($category_id = 0,$category_parent_id=0)
    {
        $model = new ArticleModel;
        $list = $model->getList($category_id,$category_parent_id,$this->postData());
        return $this->renderSuccess('', compact('list'));

    }

    /**
     *文章详情
     */
    public function detail($article_id,$url = '')
    {
        $detail = ArticleModel::detail($article_id);
        $postData = $this->getData();
        $zang = $this->giveLike($article_id,$postData);
        // 微信公众号分享参数
        if(!$url){

            $url = base_url()."pages/article/detail/detail?article_id=".$article_id;
        }
        $share = $this->getShareParams($url, $detail['article_title'], $detail['dec'], '/pages/article/detail/detail', $detail['image']['file_path']);
        return $this->renderSuccess('', compact('detail','zang','share'));
    }

    /**
     *文章点赞
     */
    public function zang(){
        $data = $this->getData();

        $user = $this->getUser();

        $user_id = isset($user['user_id'])?$user['user_id']:0;

        //点赞模型
        $UserThumbUpModel = new UserThumbUpModel();
        $result = $UserThumbUpModel->where([
            ['user_id', '=',$user_id],
            ['type', '=', 'kmd_article'],
            ['data_id', '=', $data['article_id']],
            ['app_id', '=', $data['app_id']],
        ])->field('user_thumbsup_id')->find();

       if($result){
           $res = $UserThumbUpModel->where('user_thumbsup_id','=',$result['user_thumbsup_id'])->update(['is_delete' => $data['islove']]);

           $num  = $UserThumbUpModel->where('data_id','=',$data['article_id'])->where('is_delete',0)->where('type','=','kmd_article')->count();

           return $this->renderSuccess('', compact('num'));
       }else{

           $array=[
             'user_id'  =>$user_id,
             'type'  =>'kmd_article',
             'data_id'  =>$data['article_id'],
             'app_id'  =>$data['app_id'],
             'create_time'  =>time(),
             'update_time'  =>time(),
             'is_delete'  =>$data['islove'],
           ];

           $res = $UserThumbUpModel->save($array);

           $num  = $UserThumbUpModel->where('data_id','=',$data['article_id'])->where('is_delete',0)->where('type','=','kmd_article')->count();

           return $this->renderSuccess('', compact('num'));
       }
    }

    //查看点赞状态，跟点赞数量
    public function giveLike($article_id,$postData){

        $UserThumbUpModel = new UserThumbUpModel();

        $user = $this->getUser();

        $type = $UserThumbUpModel->where([
            ['user_id', '=',$user['user_id']],
            ['type', '=', 'kmd_article'],
            ['data_id', '=', $article_id],
            ['app_id', '=', $postData['app_id']],
        ])->field('is_delete')->find();

        $num  = $UserThumbUpModel->where('app_id','=',$postData['app_id'])
            ->where('is_delete',0)
            ->where('type','=','kmd_article')
            ->where('data_id','=',$article_id)
            ->count();

        $data=[
            'islove'=>$type['is_delete'],
            'num'=>$num,
        ];


        return $data;
    }
}
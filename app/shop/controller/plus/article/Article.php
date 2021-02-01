<?php

namespace app\shop\controller\plus\article;

use app\shop\controller\Controller;
use app\shop\model\plus\article\Article as ArticleModel;
use app\shop\model\plus\article\Category as CategoryModel;
use app\shop\model\settings\Setting as SettingModel;
use app\common\library\easywechat\AppMp as WxModel;


/**
 * 文章控制器
 */
class Article extends Controller
{
    /**
     * 文章列表
     */
    public function index()
    {
        $model = new ArticleModel;
        $list = $model->getList($this->postData());
        return $this->renderSuccess('', compact('list'));
    }

    /**
     * 添加文章
     */
    public function add()
    {
        if($this->request->isGet()){
            // 文章分类
           // $catgory = CategoryModel::getAll();
            $model=new CategoryModel;
            $catgory=$model->getTreeAll();
            return $this->renderSuccess('', compact('catgory'));
        }
        $model = new ArticleModel;
        // 新增记录
        if ($model->add($this->postData())) {
            return $this->renderSuccess('添加成功');
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     *文章详情
     */
    public function detail($article_id)
    {
        $model = new ArticleModel;
        return $this->renderSuccess('', $model->detail($article_id));
    }

    /**
     * 更新文章
     */
    public function edit($article_id)
    {
        if($this->request->isGet()){
            // 文章分类
            //$catgory = CategoryModel::getAll();
            $model=new CategoryModel;
            $catgory=$model->getTreeAll();
            $model = ArticleModel::detail($article_id);
            return $this->renderSuccess('', compact('catgory', 'model'));
        }
        // 文章详情
        $model = ArticleModel::detail($article_id);
        // 更新记录
        if ($model->edit($this->postData())) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除文章
     */
    public function delete($article_id)
    {
        // 文章详情
        $model = ArticleModel::detail($article_id);
        if ($model->setDelete()) {
            return $this->renderSuccess('删除成功');
        }
        return $this->renderError($model->getError() ?:'删除失败');
    }


    /* 
    * 文章配置
    */
    public function setting()
    {
        if ($this->request->isGet()) {
            $values = SettingModel::getItem('article');
            if(empty($values)){
                $values = [
                    'share_article_point' =>0
                ];
            }
            return $this->renderSuccess('', compact('values'));
        }else{
            $model = new SettingModel;
            if ($model->edit('article', $this->postData())) {
                return $this->renderSuccess('操作成功');
            }
            return $this->renderError($model->getError() ?: '操作失败');
        }
    }


    /**
     * 获取公众号素材统计
     * @Author   linpf
     * @DataTime 2020-11-26T14:10:46+0800
     * @return   [type]                   [description]
     */
    public function getMpInfoCount()
    {
        $app_id = $this->store['app']['app_id'];

        $mod = new WxModel();
        $app = $mod::getApp($app_id);

        $type = 'news';
        $count = $app->material->stats();

        return $this->renderSuccess('', compact('count'));
    }

    /**
     * 同步公众号文章
     * @Author   linpf
     * @DataTime 2020-11-26T15:11:15+0800
     * @return   [type]                   [description]
     */
    public function postMpInfo($type = '',$total = 0)
    {
        $app_id = $this->store['app']['app_id'];

        $mod = new WxModel();
        $article_mod = new ArticleModel();
        $app = $mod::getApp($app_id);

        $type = $type;
        $total = $total;

        $res = $app->material->list($type, 0, $total);
        
        if($res){
            $result = $article_mod->syncMpArticle($res['item']);
            return $this->renderSuccess('恭喜导入成功，请设置文章分类，并且发布');
        }else{
            return $this->renderError('公众号未配置或者认证失败');
        }
    }

    /**
     * 批量修改文章状态
     * @Author   linpf
     * @DataTime 2020-12-02T14:25:12+0800
     * @param    integer                  $status [description]
     * @param    string                   $ids    [description]
     * @return   [type]                           [description]
     */
    public function batchEditStatus($status = 1,$ids = '')
    {
        $article_mod = new ArticleModel();

        $res = $article_mod->whereIn('article_id',explode(',', $ids))->update(['article_status'=>$status]);

        return $res ? $this->renderSuccess('修改成功') : $this->renderError('修改失败');
    }

}
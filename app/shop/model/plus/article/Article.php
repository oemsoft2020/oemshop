<?php

namespace app\shop\model\plus\article;

use app\common\model\plus\article\Article as ArticleModel;
use app\common\model\file\UploadFile as fileModel;

/**
 * 文章模型
 */
class Article extends ArticleModel
{
    /**
     * 获取文章列表
     */
    public function getList($params)
    {

        $mod = $this->with(['image', 'category']);

        if(isset($params['category_id']) && !empty($params['category_id'])){
            $mod->where('category_id',$params['category_id']);
        }

        if(isset($params['article_name']) && !empty($params['article_name'])){
            $article_name = $params['article_name'];
            $mod->where('article_title','like',"%$article_name%");
        }

        $result = $mod
            ->where('is_delete', '=', 0)
            ->order(['article_sort' => 'asc', 'create_time' => 'desc'])
            ->paginate($params, false, [
                'query' => request()->request()
            ]);

        foreach ($result as $key => $value) {

            $category=$value['category'];
            if ($category['parent_id'] != 0) {
                $category['parent_name'] = Category::find($category['parent_id'])['name'];
                $category['origin_name']=$category['name'];
            }
        }
        foreach ($result as $key => $value) {

            $category=$value['category'];
            if (!empty($category['parent_name'])) {
                $category['name'] = $category['parent_name'] . ' / ' . $category['origin_name'];
            }
        }

        return $result;
    }

    /**
     * 新增记录
     */
    public function add($data)
    {
        if (empty($data['article_content'])) {
            $this->error = '请输入文章内容';
            return false;
        }

        $data['app_id'] = self::$app_id;
        return $this->save($data);
    }

    /**
     * 更新记录
     */
    public function edit($data)
    {
        if (empty($data['article_content'])) {
            $this->error = '请输入文章内容';
            return false;
        }
        return $this->save($data);
    }

    /**
     * 软删除
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]);
    }

    /**
     * 获取文章总数量
     */
    public static function getArticleTotal($where)
    {
        $model = new static;
        return $model->where($where)->where('is_delete', '=', 0)->count();
    }

    /**
     * 同步公众号文章
     * @Author   linpf
     * @DataTime 2020-11-26T15:52:11+0800
     * @param    array                    $data [description]
     * @return   [type]                         [description]
     */
    public function syncMpArticle($data = array())
    {
        if(empty($data)){
            return false;
        }

        $img_mod = new fileModel();

        foreach ($data as $key => $value) {
             for ($i=0; $i < count($value['content']['news_item']); $i++) { 
                $is_exist = $this->findArtByTitle($value['content']['news_item'][$i]['title']);
                if($is_exist <= 0){
                    if(strlen($value['content']['news_item'][$i]['content']) >= 100000){
                        continue;
                    }
                   
                   $saveData['article_title'] = $value['content']['news_item'][$i]['title'];
                   $saveData['article_content'] = str_replace('data-src','src',$value['content']['news_item'][$i]['content']);     
                   $saveData['article_status'] = 0;
                   $saveData['create_time'] = $value['content']['create_time'];     
                   $saveData['update_time'] = $value['content']['update_time'];     
                   $saveData['dec'] = $value['content']['news_item'][$i]['digest'];      
                   $saveData['from'] = 'mp';
                   $saveData['media_id'] = $value['media_id'];
                   $saveData['app_id'] = self::$app_id;

                   $img_id = $img_mod->where('file_name',$value['content']['news_item'][$i]['thumb_url'])->value('file_id');

                   if(!empty($img_id)){
                        $saveData['image_id'] = $img_id;
                   }else{
                        
                        // 下载图片到本地
                        $img_name = time().rand(1000,99999).'.png';
                        $backdrop = root_path(). 'public/uploads/'.$img_name;
                       
                        file_put_contents($backdrop, file_get_contents($value['content']['news_item'][$i]['thumb_url']));

                        $img_data['storage'] = 'wx_mp';
                        $img_data['file_url'] = base_url(). 'uploads';
                        $img_data['file_name'] = $img_name;
                        $img_data['file_type'] = 'image';
                        $img_data['extension'] = 'jpeg';
                        $img_data['app_id'] = self::$app_id;

                        // 上传图片
                        $saveData['image_id'] = $img_mod->insertGetId($img_data);
                   }

                   $this->insert($saveData);

                }
                
             }
             
        }
        
        return true;
    }

    /**
     * 判断文章是否已同步过
     * @Author   linpf
     * @DataTime 2020-11-26T16:14:53+0800
     * @param    string                   $title [文章标题]
     * @return   [type]                             [description]
     */
    public function findArtByTitle($title = '')
    {
        if(empty($title)){
            return 0;
        }

        return $this->where('article_title',$title)->count();
    }

}
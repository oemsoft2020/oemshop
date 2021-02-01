<?php


namespace app\api\controller\plus\article;


use app\api\controller\Controller;
use app\api\model\plus\article\Category as CategoryMode;



class Category extends Controller
{
    public function getOne($category_id){
        $model=new CategoryMode;
        $category=$model->with('image')->find($category_id);
        return $this->renderSuccess('',compact('category'));
    }
}
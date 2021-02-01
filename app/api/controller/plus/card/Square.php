<?php

namespace app\api\controller\plus\card;

use app\api\controller\Controller;
use app\api\model\plus\card\Card;
use app\api\model\plus\card\CardCategory as CardCategoryModel;


class Square extends Controller
{

    /* 人脉圈信息 */

    public function getSquare()
    {
       $cardCategoryModel = new  CardCategoryModel();
       $category =  $cardCategoryModel->getList();
        
       return $this->renderSuccess('成功',compact('category'));

    }

    /* 获取名片列表 */
    public function getCardList()
    {   
        $data = $this->postData();
        $cardModel = new Card();
        $cardList = $cardModel->getList($data);
        return $this->renderSuccess('成功',compact('cardList'));

    }

    /* 获取子分类 */
    public function getSubCategory()
    {
        $data = $this->postData();
        $cardCategoryModel = new CardCategoryModel();
        $categoryList = $cardCategoryModel->getSubList($data);
        return $this->renderSuccess('成功',compact('categoryList'));
    }
    
    
}

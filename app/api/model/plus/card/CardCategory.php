<?php

namespace app\api\model\plus\card;

use app\common\model\plus\card\CardCategory as CardCategoryModel;


class CardCategory extends CardCategoryModel
{
    /* 
    * 获取分类
    */
    public function getList()
    {   
        $list  = $this->getALL()['tree'];
        return $list;
    }

    /* 
    * 获取子分类
    */
    public function getSubList($params)
    {       
        if(empty($params['card_category_id'])){
            return [];
        } 

        $list = $this->where('is_delete',0)->where('parent_id',$params['card_category_id'])->order(['sort' => 'asc', 'create_time' => 'asc'])->select();
        return $list;
    }
}

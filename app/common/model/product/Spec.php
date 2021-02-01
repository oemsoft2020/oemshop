<?php

namespace app\common\model\product;

use app\common\model\BaseModel;
/**
 * 规格/属性(组)模型
 */
class Spec extends BaseModel
{
    protected $name = 'spec';
    protected $pk = 'spec_id';
    protected $updateTime = false;

    // 商品规格
    public function makeSpec($spec= '')
    {
    	if(!empty($spec)){
    		
    	}	
    }
}

<?php

namespace app\api\model\plus\feng;

use app\common\exception\BaseException;
use app\common\model\BaseModel;
use app\api\model\user\UserAddress as addressModel;

Class Feng extends  BaseModel{


    protected $name = 'code_product_fengtan';
    protected $pk = 'fengtan_id';






    /**
     * 关联物流公司表
     */
    public function express()
    {
        return $this->belongsTo('app\\api\\model\\settings\\Express');
    }


}

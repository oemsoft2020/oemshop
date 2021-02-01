<?php

namespace app\common\model\plus\agent;

use app\common\model\BaseModel;

/**
 * 分销商规则模型
 */
class Rules extends BaseModel
{
    protected $name = 'agent_rules';
    protected $pk = 'agent_rules_id';

     /**
     * 关联等级表
     */
    public function referee()
    {
        return $this->belongsTo('app\\common\\model\\user\\Grade','referee_grade_id','grade_id');
    }

    /**
     * 关联等级表
     */
    public function invited()
    {
        return $this->belongsTo('app\\common\\model\\user\\Grade','invited_grade_id','grade_id');
    }

    /* 
    * 查询规则
    * 推荐人等级id $referee_grade_id
    * 邀请人等级id $invited_grade
    * 商品id $product_id
    */

    public function getCommissionRules($referee_grade_id,$invited_grade_id,$product_id)
    {   
        $model = new self;
        $rule_info = $model->where('is_delete','=',0)
        ->where('FIND_IN_SET(:product_id,product_ids)',['product_id' => $product_id])
        ->where('referee_grade_id','=',$referee_grade_id)
        ->where('invited_grade_id','=',$invited_grade_id)
        ->find();

        return $rule_info;
    }

}

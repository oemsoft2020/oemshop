<?php

namespace app\shop\model\plus\agent;

use app\common\model\plus\agent\Rules as RulesModel;


/**
 * 分销商规则模型
 */
class Rules extends RulesModel
{
    /**
     * 获取分销商规则列表
     */
    public function getList()
    {
        $model = $this;
        
        // 获取分销商订单列表
        $data = $model->with(['referee','invited'])->order(['create_time' => 'desc'])
            ->where('is_delete', '=', 0)
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
        return $data;
    }

    public function edit($params)
    {

        $this->startTrans();
        try {
            $data = $this->setData($params);
        
            if(isset($params['agent_rules_id'])){
                $where['agent_rules_id'] = $params['agent_rules_id'];

                self::update($data, $where);
            }else{

                $data['app_id'] = self::$app_id;
                self::create($data);
            }
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    public function setData($data)
    {
        if(isset($data['productList'])&&!empty($data['productList'])){
            $product_id_arr = array_column($data['productList'],'product_id');
            if(!empty($product_id_arr)){
                $data['product_ids']  = implode(',',$product_id_arr);
            }
        }
        return $data;
    }
}
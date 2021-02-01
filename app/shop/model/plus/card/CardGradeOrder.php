<?php

namespace app\shop\model\plus\card;

use app\common\model\plus\card\CardGradeOrder as CardGradeOrderModel;



/**
 * 名片权限列表
 */
class CardGradeOrder extends CardGradeOrderModel
{
    public function getList($param)
    {
        $model = $this;

        if (isset($param['pay_status']) && $param['pay_status'] >0) {
            $model = $model->where('pay_status', '=', $param['pay_status']);
        }

        if (isset($param['search']) && !empty($param['search'])) {
            $model = $model->where('order_no|user_id', 'like', '%' . trim($param['search']) . '%');
        }

        if(!empty(self::$supply_id)){
            $model = $model->where('supply_id', '=', self::$supply_id); 
        }
        $list = $model->with(['cardGrade','user'])
            ->order('create_time', 'desc')
            ->paginate($param, false, [
                'query' => request()->request()
            ]);
        return $list;

    }
    
    /* 
    *  更新
    */
    public function edit($params)
    {   

        $this->startTrans();
        try{
            if(empty($params['supply_id'])){
                
                return false;
            }
            $where = [
                'supply_id'=>$params['supply_id']
            ];
            $info =  $this->where($where)->find();

            if(!empty($info)){
                $updatedata = [
                    'number'=>$params['number']
                ];
                $res = $info->save($updatedata);
            }else{
                $insertdata = [
                    'supply_id'=>$params['supply_id'],
                    'number'=>$params['number'],
                    'app_id'=>self::$app_id
                ];
                $res =  $this->save($insertdata);
            }
            $this->commit();
            return ['state'=>1,'msg'=>'更新成功'];
        }catch(\Exception $e){
            $this->rollback();
            $returnData['state'] = 0;
            $returnData['msg'] = "更新失败";
            return $returnData;
        }
            
    }

}
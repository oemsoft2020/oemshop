<?php

namespace app\shop\model\plus\card;

use app\common\model\plus\card\CardAuth as CardAuthModel;
use app\common\model\plus\supply\Supply;


/**
 * 名片权限列表
 */
class CardAuth extends CardAuthModel
{
    public function getList($param)
    {
        $model = new Supply();

        if (isset($param['status']) && $param['status'] > -1) {
            $model = $model->where('status', '=', $param['status']);
        }

        if (isset($param['name']) && !empty($param['name'])) {
            $model = $model->where('name', 'like', '%' . trim($param['name']) . '%');
        }
        $list = $model->with(['cardauth'])
            ->where('is_delete', '=', 0)
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
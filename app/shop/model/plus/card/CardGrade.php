<?php

namespace app\shop\model\plus\card;

use app\common\model\plus\card\CardGrade as CardGradeModel;
use app\common\model\plus\supply\Supply;


/**
 * 名片权限列表
 */
class CardGrade extends CardGradeModel
{
    public function getList($param)
    {
        $list = $this->where('is_delete', '=', 0)
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

            $info =  false;

            if(!empty($params['card_grade_id'])){

                $where = [
                    'card_grade_id'=>$params['card_grade_id']
                ];
                $info =  $this->where($where)->find();
            }

            if(!empty($info)){
                $updatedata = [
                    'name'=>$params['name'],
                    'time'=>$params['time'],
                    'money'=>$params['money'],
                ];
                $res = $info->save($updatedata);
            }else{
                $insertdata = [
                    'name'=>$params['name'],
                    'time'=>$params['time'],
                    'money'=>$params['money'],
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
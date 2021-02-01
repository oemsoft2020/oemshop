<?php


namespace app\common\model\user;

use app\common\model\BaseModel;
/**
 * 用户浏览记录模型
 */
class UserBrowseRecords extends BaseModel
{
    protected $pk = 'user_browse_records_id';
    protected $name = 'user_browse_records';

    /* 
    * 获取最后一次记录
    */
    public function getLastRecordByType($type='',$user_id='')
    {
        $model = $this;
        $where = [
            'type'=>$type,
            'user_id'=>$user_id
        ];
        $records = $model->where($where)->order('update_time','desc')->find();
        return $records;
    }

    /* 
    * 保存浏览记录
    */
    public function saveBrowseRecords($data)
    { 
        $this->startTrans();
        try {
            
            $data['app_id']=  self::$app_id;
            $info = $this->where($data)->find();
            if($info){

                $info->save($data);

            }else{

                $this->save($data);
            }
            // 事务提交
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /* 
    * 获取列表
    */

    public function getList($user_id,$params)
    {   
        $model = $this;
        if(isset($params['data_type'])){

           $model = $model->where('type','=',$params['data_type']);
        }   
        return  $model->where('user_id', '=', $user_id)
            ->order(['create_time' => 'desc'])
            ->paginate(30, false, [
                'query' => request()->request()
            ]);

    }

}
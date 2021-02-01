<?php

namespace app\shop\model\plus\card;

use app\common\enum\order\OrderSourceEnum;
use app\common\model\plus\card\Card as CardModel;
use app\shop\model\file\UploadFile as UploadFileModel;
use app\common\model\product\Label as LabelModel;
use think\facade\Db;

/**
 * 名片列表
 */
class Card extends CardModel
{
    public function getList($param)
    {
        $model = $this;
        if(isset($param['type'])&&!empty($param['type'])){
            $model = $model->where('type', '=', $param['type']);
        }
        if(isset($param['supply_id'])&&!empty($param['supply_id'])){
            $model = $model->where('supply_id', '=', $param['supply_id']);
        }
        if(isset($param['title'])&&!empty($param['title'])){
            $model = $model->where('name', 'like', "%".$param['title']."%");
        }
        $order = [
            'istop'=>'desc',
            'displayorder'=>'asc',
            'isdefault'=>'desc',
            'create_time'=>'desc',
        ];

        $list = $model->with(['user'])->where('is_delete', '=', 0)
            ->order($order)
            ->paginate($param, false, [
                'query' => \request()->request()
            ]);  
        return $list;

    }

    public function  detail($card_id)
    {
        $model = $this;
        $info =  $model->with(['user'])->find($card_id);
        $info['images'] = []; 
        if(!empty($info['images_id'])){
          $info['images']  =  UploadFileModel::where('file_id','in',explode(',',$info['images_id']))->select();
        }
        $info['labelList'] = [];
        if(!empty($info['kmd_label_ids'])){
            $info['labelList']  =  LabelModel::where('kmd_label_id','in',explode(',',$info['kmd_label_ids']))->select();
        }
        $info['active_time'] = [
            date('Y-m-d H:i:s', $info['start_time']),
            date('Y-m-d H:i:s', $info['end_time']),
        ];
        return $info;
         
    }


    public function add($data)
    {
        $this->startTrans();
        try {
            $arr  =[];
            $arr = $this->setData($data);
            $this->save($arr);
            // 事务提交
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    public function edit($data)
    {
        $this->startTrans();
        try {
            $arr = $this->setData($data);
            $this->save($arr);
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
    * 整理数据
    */

    public function setData($data)
    {
        if(isset($data['active_time'])){
            $data['active_time'][0] = substr($data['active_time'][0],0, 16);
            $data['active_time'][1] = substr($data['active_time'][1],0, 16);
            $data['start_time'] = strtotime( $data['active_time'][0].":00");
            $data['end_time'] = strtotime( $data['active_time'][1].":59");
        }
       

        if(isset($data['images'])&&!empty($data['images'])){
            $images_id_arr = array_column($data['images'],'file_id');
            $images_arr = array_column($data['images'],'file_path');
            if(!empty($images_id_arr)){
                $data['images_id']  = implode(',',$images_id_arr);
            }
            if(!empty($images_arr)){
                $data['images_str']  = implode(',',$images_arr);
            }
        }

        /* 处理标签 */
        if(isset($data['labelList'])&&!empty($data['labelList'])){
            $label_id_arr = array_column($data['labelList'],'kmd_label_id');
            if(!empty($images_id_arr)){
                $data['kmd_label_ids']  = implode(',',$label_id_arr);
            }
        }
        if(isset($data['isdefault'])){
            $data['isdefault'] = $data['isdefault']==='true'||$data['isdefault']==1?1:0;
        }
        if(isset($data['istop'])){
           
            $data['istop'] = $data['istop']==='true'||$data['istop']==1?1:0;
        }
        if(isset($data['displayorder'])){
            $data['displayorder'] = $data['displayorder'];
        }
        if(isset($data['more_supply'])){
            $data['more_supply'] = $data['more_supply'];
        }
        $data['app_id'] = self::$app_id;
        return $data;

    }

    /**
     * 活动删除
     */
    public function del()
    {
        // 如果有未付款订单不能删除
        return $this->save([
            'is_delete' => 1
        ]);
    }

    
}
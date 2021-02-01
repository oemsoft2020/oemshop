<?php

namespace app\api\model\plus\card;

use app\common\model\plus\card\RadarLabel as RadarLabelModel;

use think\facade\Db;

class RadarLabel extends RadarLabelModel
{

    
    public function getListByParams($params,$user_id)
    {
        $model = $this;

        $where = [
            ['user_id','=',$user_id]
        ];

        if(isset($params['keywords'])&&!empty($params['keywords'])){

            $where[]= array('name','like', '%' . $params['keywords'] . '%');
            $model =   $model->where($where);
        }

        $list = $model->where($where)->paginate(15, false, [
                'query' => request()->request()
            ]);
        return $list;
    }

    public function getTagsCount($user_id)
    {
        $where = [
            'user_id'=>$user_id,
        ];
        $count = $this->where($where)->count();
        return $count?$count:0;
    }

    public function getAllTags($user_id)
    {
        $where = [
            'user_id'=>$user_id
        ];
        return $this->where($where)->select();
    }

    public function updateLabelCount($label_id,$user_id)
    {
        $radarLabelClientModel = new RadarLabelClient();
        $radar_info = $this->find($label_id);
        if(empty($radar_info)){
            return false;
        }
        $where = [
            'label_id'=>$label_id,
            'user_id'=>$user_id
        ];
        $clients = $radarLabelClientModel->where($where)->count();
        $data = [
            'clients'=>$clients
        ];
       
        return  $radar_info->save($data);
    }

    public function addLabel($params)
    {   
        $params['app_id'] = self::$app_id;
        $this->save($params);
        return $this->radar_label_id;
    }


}
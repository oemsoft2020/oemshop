<?php

namespace app\api\model\plus\card;

use app\common\model\plus\card\RadarLabelClient as RadarLabelClientModel;
use app\common\model\plus\card\CardCount;
use think\facade\Db;

class RadarLabelClient extends RadarLabelClientModel
{
    
    public function getLabelList($client_id,$user_id)
    {
        $where = [
            'client_id'=>$client_id,
            'user_id'=>$user_id,
        ];
        return $this->with(['label'])->select();
    }

    public function getListByParams($params,$user_id)
    {
        $model = $this;

        $where = [
            ['lc.user_id','=',$user_id]
        ];

        if(isset($params['keywords'])&&!empty($params['keywords'])){

            $where[]= array('c.nick_name','like', '%' . $params['keywords'] . '%');

        }

        if(isset($params['label_id'])&&!empty($params['label_id'])){

            $where[]= array('lc.label_id','=', $params['label_id']);

        }

        if(isset($params['is_star'])&&!empty($params['is_star'])){
            $where[]= array('c.is_star','=', 1);
        }

        $list = $model->alias('lc')->leftJoin('RadarClient c','c.radar_client_id = lc.client_id')->where($where)->group('lc.client_id')->field('c.*,lc.client_id,lc.label_id')->paginate(15, false, [
                'query' => request()->request()
            ]);
        $cardCountModel =  new CardCount();
        foreach ($list as $key => &$item) {
            $where  = [
                'to_user_id'=>$user_id,
                'user_id'=>$item['user_id']
            ];
            $item['count'] =  $cardCountModel->where($where)->count();
        }
        unset($item);
    
        return $list;
    }

    public function operateLabelClient($user_id,$client_id,$label_id,$operate='add')
    {
        $this->startTrans();
        try {
            $where = [
                ['user_id','=',$user_id],
                ['label_id','=',$label_id],
                ['client_id','=',$client_id]
            ];
            
            $res =  $this->where($where)->find();
            
            if(empty($res)&&$operate=='add'){
                $data = [
                    'user_id'=>$user_id,
                    'client_id'=>$client_id,
                    'label_id'=>$label_id,
                    'app_id'=>self::$app_id
                ];
                $this->save($data);
            }
            if(!empty($res)&&$operate=='reduce'){

                $res->delete();
            }
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }
    /* 
    * 获取用户标签
    */
    public function getClientTags($client_id,$user_id)
    {
        $where = [
            'client_id'=>$client_id,
            'user_id'=>$user_id,
        ];
        return $this->with(['label'])->where($where)->select();
    }
}
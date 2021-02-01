<?php

namespace app\api\model\plus\card;

use app\common\model\plus\card\RadarFollow as RadarFollowModel;

use think\facade\Db;

class RadarFollow extends RadarFollowModel
{
    public function addFollow($params)
    {

        $data = [
            'user_id'=>$params['user_id'],
            'c_uid'=>$params['c_uid'],
            'type'=>$params['type'],
            'content'=>$params['content'],
            'app_id'=>self::$app_id,
        ];
        $this->save($data);
    }

    public function getFollowList($user_id,$c_uid)
    {
        $model = $this;

        $where = [
            ['user_id','=',$user_id],
            ['c_uid','=',$c_uid]
        ];

        $lists = $model->with(['custom'])->where($where)->order('create_time','desc')->paginate(15, false, [
                'query' => request()->request()
            ]);
        if($lists){
            foreach ($lists as &$list) {
               
                $list['create_time1'] = date('H:i',strtotime($list['create_time']));
                $list['date'] = date('Y/m/d', strtotime($list['create_time']));
                $list['nick_name'] = $list['user']? $list['user']['nickName']:"";
                $list['person_avatar'] =  $list['user']? $list['user']['avatarUrl']:"";
            }
            unset($list);
        }
        
        return $lists;
    }
    
}
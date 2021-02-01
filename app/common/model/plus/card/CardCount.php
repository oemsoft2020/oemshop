<?php

namespace app\common\model\plus\card;

use app\common\model\BaseModel;
use think\facade\Db;
use think\model\relation\BelongsTo;

class CardCount extends BaseModel
{
    protected $name = 'card_count';
    protected $pk = 'card_count_id';

    public function user()
    {
        return $this->belongsTo('app\\common\\model\\user\\User', 'user_id', 'user_id');
   
    }

    public function radar($user_info,$data)
    {
        $behaviorInfo=[
            'view_goods_count'=>0,
            'view_web_count'=>0,
            'copy_wechat_count'=>0,
            'share_card_count'=>0,
        ];

        $behaviorList = [
            'view_card_count'=>0,
            'save_phone_count'=>0,
            'copy_email_count'=>0,
            'call_phone_count'=>0,
            'thumbs_count'=>0,
        ];
        $model = $this;
        $beginTime = mktime(0, 0, 0, date("m"), date("d") - 7, date("Y"));

        if (isset($data['type'])&&$data['type'] == 2) {
            $beginTime = mktime(0, 0, 0, date("m"), date("d") - 30, date("Y"));
        }
        $where = [
            [ 'to_user_id',"=",$user_info['user_id']],
            ['create_time','>',$beginTime],
           
        ];
        //查看商品
        $otherData['type']=2;
        $otherData['sign']= 'view';
        $behaviorInfo['view_goods_count'] =  $model->where($where)->where($otherData)->count('user_id');
    
        // var_dump(Db::getLastSql());die;
        //查看官网
        $otherData['type']=6;
        $otherData['sign']= 'view';
        $behaviorInfo['view_web_count'] = $model->where($where)->where($otherData)->count('user_id');
        //复制微信
        $otherData['type'] = 4;
        $otherData['sign'] = 'copy';
        $behaviorInfo['copy_wechat_count'] = $model->where($where)->where($otherData)->count('user_id');

        //分享名片
        $otherData['type'] = 4;
        $otherData['sign'] = 'praise';
        $behaviorInfo['share_card_count'] = $model->where($where)->where($otherData)->count('user_id');

        //浏览名片记录

        $otherData['type'] = 1;
        $otherData['sign'] = 'view';
        $behaviorList['view_card_count'] = $model->where($where)->where($otherData)->count('user_id');
        //保存电话
        $otherData['type'] = 1;
        $otherData['sign'] = 'copy';
        $behaviorList['save_phone_count'] = $model->where($where)->where($otherData)->count('user_id');

        //靠谱
        $otherData['type'] = 3;
        $otherData['sign'] = 'praise';
        $behaviorList['thumbs_count'] = $model->where($where)->where($otherData)->count('user_id');
        //复制邮箱
        $otherData['type'] = 5;
        $otherData['sign'] = 'copy';
        $behaviorList['copy_email_count'] = $model->where($where)->where($otherData)->count('user_id');

        //拨打电话
        $otherData['type'] = 2;
        $otherData['sign'] = 'copy';
        $behaviorList['call_phone_count'] = $model->where($where)->where($otherData)->count('user_id');

        return [
            'behaviorInfo'=>$behaviorInfo,
            'behaviorList'=>$behaviorList,
        ];

    }

    /* 
    * 时间线
    */

    public function timeStatis($user_id,$params,$c_user_id=0)
    {
        $where = [
            ['user_id','<>',0],
            ['to_user_id','=',$user_id]
        ];
        if($c_user_id){
            $where[] = ['user_id','=',$c_user_id];
        }
        $lists = $this->with(['user'])->where($where)->order('card_count_id desc')->paginate(50, false, [
            'query' => \request()->request()
        ]);
		if ($lists) {
            
			foreach ($lists as &$list) {
				$time_count = $this->getTimeCount($list['user_id'],$user_id, $list['sign'], $list['type'],$list['create_time']);

                $list['count'] =   $time_count;
				$list['date'] = date('Y/m/d', strtotime($list['create_time']));
				$list['time'] = date('H:i',  strtotime($list['create_time']));
			
				$list['nick_name'] = $list['user']? $list['user']['nickName']:"";
				$list['person_avatar'] =  $list['user']? $list['user']['avatarUrl']:"";
            }
            unset($list);
        }
        
        return $lists;
    }

    /* 
    * 计算次数
    */
    public function getTimeCount($user_id,$to_user_id,$sign,$type,$create_time)
    {
        $where = [
            ['user_id','=',$user_id],
            ['to_user_id','=',$to_user_id],
            ['sign','=',$sign],
            ['type','=',$type],
            ['create_time','<=',strtotime($create_time)]
        ];
        return $this->where($where)->count();
    }
}
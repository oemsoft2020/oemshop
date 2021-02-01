<?php

namespace app\api\model\plus\card;

use app\common\model\plus\card\CardChat as CardChatModel;
use app\api\model\plus\card\CardMessage as CardMessageModel;
use think\facade\Db;

class CardChat extends CardChatModel
{
 
    public function getChatInfo($user_id,$to_user_id)
    {
        $where = [
            'user_id'=>$user_id,
            'target_id'=>$to_user_id
        ];
        $chatRoom1 = $this->where($where)->find();
       

        if(!empty($chatRoom1)){

            return  $chatRoom1['card_chat_id']; 
        }

        $where = [
            'user_id'=>$to_user_id,
            'target_id'=>$user_id
        ];

        $chatRoom2 = $this->where($where)->find();

        if(!empty($chatRoom2)){

            return  $chatRoom2['card_chat_id']; 
        }
        
        $data = [
            'user_id'=>$user_id,
            'target_id'=>$to_user_id,
            'app_id'=>self::$app_id,
        ];
        $this->save($data);

        $card_chat_id = $this->card_chat_id;

        return $card_chat_id;
    }

    /* 
    * 获取聊天列表
    */

    public function getChatListByUid($user_id)
    {

      
        $where = [
           [ 'cm.user_id','=',$user_id],
           [ 'cm.target_id','<>',$user_id],
        ];
        $whereOr = [
            [ 'cm.user_id','<>',$user_id],
            [ 'cm.target_id','=',$user_id],
        ];

        $lists =  $this->alias('cc')->leftJoin('CardMessage cm','cc.card_chat_id = cm.chat_id')->where(function($query)use($where){
           return  $query->where($where);
        })->whereOr(function($query)use($whereOr){
            return  $query->where($whereOr);
         })->order('cm.create_time','desc')->field('cc.*,cm.content,cm.message_type')->select();

        return $lists;
    }
    
}

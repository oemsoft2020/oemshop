<?php

namespace app\api\model\plus\card;

use app\common\model\plus\card\CardMessage as CardMessageModel;


class CardMessage extends CardMessageModel
{
 
    public function addMessage($data)
    {
        $messageData = [
            'chat_id'=>$data['chat_id'],
            'user_id'=>$data['user_id'],
            'target_id'=>$data['target_id'],
            'content'=>$data['content'],
            'message_type'=>isset($data['message_type'])?$data['message_type']:'text',
            'app_id'=>self::$app_id
        ];

       $this->save($messageData);
       return  true;
    }

    /* 
    *获取信息列表
    */
    public function getMessageList($data)
    {
        $where = [
            ['chat_id' ,'=',$data['chat_id']],
           
            ['create_time','>',time() - 24 * 3600]
        ];
        return $this->where($where)->order('card_message_id','asc')->select();
    }

    public function getMessageCount($param)
    {
        
        if(isset($param['target_id'])&&$param['target_id']>0){

            $where['target_id']=$param['target_id'];
        }
        if(isset($param['user_id'])&&$param['user_id']>0){

            $where['user_id']=$param['user_id'];
        }
        if(isset($param['status'])&&$param['status']>0){

            $where['status']=$param['status'];
        }
        
        if(isset($param['chat_id'])&&$param['chat_id']>0){

            $where['chat_id']=$param['chat_id'];
        }

        return $this->where($where)->count();

    }

}

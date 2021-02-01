<?php

namespace app\api\controller\plus\card;

use app\api\controller\Controller;
use app\api\model\plus\card\Card;
use app\api\model\plus\card\CardChat;
use app\api\model\plus\card\CardMessage;
use app\api\model\plus\card\RadarClient;
use app\api\model\user\User;
use app\common\service\message\MessageService;
use app\api\model\settings\Message as MessageModel;
use Common;

class Chat extends Controller
{

    /* 
    * 获取聊天室id
    */
    public function getChatId($chat_to_uid)
    {
        
        $user_info = $this->getUser();

        $cardChatModel =  new CardChat();

        $clientModel = new RadarClient();

        $user_info = $this->getUser();

        if (!$chat_to_uid || !$user_info['user_id']) {
            return $this->renderError("无法找到客户");
        }
        $radarClientModel = new RadarClient();
           
        $radarClientModel->addClient($user_info['user_id'],$chat_to_uid);

        $chat_id =  $cardChatModel->getChatInfo($user_info['user_id'],$chat_to_uid);

        if(empty($chat_id)){

            return $this->renderError('聊天链接失败');
        }   

        $userModel = new User();
        
        $target_info =  $userModel->detail($chat_to_uid);
        $user_info =  $userModel->detail($user_info['user_id']);
        $clientSoure  = $clientModel->getClientByUid($user_info['user_id'],$chat_to_uid);

        return $this->renderSuccess('success',compact('chat_id','target_info','user_info','clientSoure'));
       
    }

    /* 
    * 发送消息
    */
    public function message()
    {
        $data = $this->postData();    
        
        $user_info = $this->getUser();

        if(!isset($data['chat_to_uid'])||empty($data['chat_to_uid'])){

            return $this->renderError('聊天链接失败');
        }

        $messageModel =  new CardMessage();
        
        $messageData = [
            'chat_id'=>$data['chat_id'],
            'user_id'=>$user_info['user_id'],
            'target_id'=>$data['chat_to_uid'],
            'content'=>$data['content'],
            'message_type'=>isset($data['message_type'])?$data['message_type']:'text',
        ];
       
        $messageModel->addMessage($messageData);
        $messageData = [
            'chat_id'=>$data['chat_id'],
            'status'=>1,
            'target_id'=>$data['chat_to_uid']
        ];

        $message =  $messageModel->where($messageData)->order('create_time','desc')->find();
        $card_id = isset($data['card_id'])?$data['card_id']:0;
        (new MessageService)->chat($message,$card_id);
        
        return $this->renderSuccess('成功',compact('message'));
    }

    /* 
    * 获取信息列表
    */
    public function getMessage()
    {
        $data = $this->postData();    
        
        $user_info = $this->getUser();

        if(!isset($data['chat_to_uid'])||empty($data['chat_to_uid'])){

            return $this->renderError('聊天链接失败');
        }

        $messageModel =  new CardMessage();

        $messageData = [
            'chat_id'=>$data['chat_id']
        ];
        $messageList =  $messageModel->getMessageList($messageData);

        $updateData = [
            'status'=>2
        ];

        $messageData = [
            'status'=>1,
            'chat_id'=>$data['chat_id']
        ];

        $messageModel->where($messageData)->update($updateData);
        

        $toView_id = 0;
        if(count($messageList)){

            $toView_id = $messageList[count($messageList)-1]['card_message_id'];
        }
       
        return $this->renderSuccess('成功',compact('messageList','toView_id'));
        
    }

    /* 
    * 获取新信息
    */
    public function getNewsMessage()
    {
        $data = $this->postData();    
        
        $user_info = $this->getUser();

        if(!isset($data['chat_to_uid'])||empty($data['chat_to_uid'])){

            return $this->renderError('聊天链接失败');
        }

        $messageModel =  new CardMessage();

        $messageData = [
            'chat_id'=>$data['chat_id'],
            'status'=>1,
            'user_id'=>$data['chat_to_uid']
        ];

        $message =  $messageModel->where($messageData)->find();
        if(!empty($message)){
            $updateData = [
                'status'=>2
            ];
            $message->save($updateData);
        }
       
        return $this->renderSuccess('成功',compact('message'));
    }

    /* 
    * 获取聊天列表
    */
    public function getChatList()
    {
        $data = $this->postData();

        $user_info = $this->getUser();
        
        $cardChatModel =  new CardChat();
        $cardMessageModel = new CardMessage();
        $clientModel = new RadarClient();
        $userModel = new User();

        $cardModel = new Card();

        $temlIds_arr = MessageModel::getMessageByNameArr('wx', ['card_chat_message']);

        $data = [
            'user_id'=>$user_info['user_id']
        ];

        $chatList = $cardChatModel->getChatListByUid($user_info['user_id']);

        //当前身份
        $cardInfo = $cardModel->getSelfCard($user_info['user_id']);
        $card_id = isset($cardInfo['card_id'])?$cardInfo['card_id']:0;

        $tmp1 = array();
        $tmp2 = array();
        foreach ($chatList as $index => $item) {
            if (in_array($item["card_chat_id"], $tmp1)) {
                continue;
            }
            array_push($tmp1, $item["card_chat_id"]);
            array_push($tmp2, $item);
        }
    
        $chat = $tmp2;
        
        $tmp = array();
        foreach ($chat as $k => $v) {
            if ($v["content"]) {
                array_push($tmp, $v);
            }
        }
        // var_dump($tmp,array_column($tmp, "create_time"));die;
        array_multisort(array_column($tmp, "create_time"), SORT_DESC, $tmp);
        $limit = array(1, 15);
        $curr = isset($data["page"])?$data["page"]:1;

        $offset = ($curr - 1) * 15;
        $count = count($tmp);
        $array = array_slice($tmp, $offset, 15);

        $user_id_arr = array();
        foreach ($array as $index => $item) {

            if($item["user_id"] ==$user_info['user_id']){

                $tid =  $item["target_id"];
            }else{
                $tid =  $item["user_id"];
            }
            //查询用户
            $target_info = $clientModel->getClientByUid($user_info['user_id'],$tid);

            //查询未读信息
            $params = [
                'user_id'=>$tid,  //发送信息
                'target_id'=>$user_info['user_id'], //接受者
                'status'=>1,
                'chat_id'=>$item['chat_id']
            ];
            $unReadMessage = $cardMessageModel->getMessageCount($params);

            $array[$index]["chat_to_uid"] = $tid;
            $array[$index]["message_not_read_count"] = $unReadMessage;
            $array[$index]["user"] =  $target_info?$target_info:[];
            $array[$index]["phone"] = $target_info['tel'];

        

        }
        $chatList = array("page" => $curr, "total_page" => ceil($count / 15), "list" => $array);
        return $this->renderSuccess('成功',compact('chatList','temlIds_arr','card_id'));

    }
}

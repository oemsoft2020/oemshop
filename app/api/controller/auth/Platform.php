<?php

namespace app\api\controller\auth;

use app\api\controller\Controller;
use EasyWeChat\OpenPlatform\Server\Guard;
use EasyWeChat\Kernel\Messages\Message;
use app\common\library\easywechat\AppWxPlatform;
use app\KmdController;
use app\shop\model\app\AppWx as AppWxModel;

class Platform extends KmdController
{
   
    public function notify()
    {
        $platform =  new AppWxPlatform();
        $openPlatform =$platform::getApp();
        $server  = $openPlatform->server;
        // 处理授权成功事件
        $server->push(function ($message) {
            // ...
        }, Guard::EVENT_AUTHORIZED);

        // 处理授权更新事件
        $server->push(function ($message) {
            // ...
        }, Guard::EVENT_UPDATE_AUTHORIZED);

        // 处理授权取消事件
        $server->push(function ($message) {
            // ...
        }, Guard::EVENT_UNAUTHORIZED);
        $response = $server->serve();
        return $response;
    }

    /* 
    * 授权接收事件
    */

    public function openIndex()
    {

        $open_platform = AppWxPlatform::getApp();
        $postData =  $this->postData();
        $authorizer_appid = ltrim($postData['wxappid'],'/');

        /**
         * 全网发布
         */
        file_put_contents('mytest20200820.txt',date('Y-m-d H:i:s').":".var_export($this->postData(),true)."\r\n",FILE_APPEND);
        if ($authorizer_appid == 'wx570bc396a51b8ff8'||$authorizer_appid == 'wxd101a85aa106f53e') {
            return $this->releaseToGlobalNet($open_platform, $authorizer_appid);
        }

        //查询应用
        $condition = [
            'wxapp_id'=>$authorizer_appid
        ];
        $app_account_info  =  AppWxModel::where($condition)->find();
        if (empty($app_account_info)) {
            return $this->renderSuccess('official account not authorization');
        }
        if($app_account_info['type']=='wxapp'){
            $official_account_client = $open_platform->miniProgram($app_account_info['wxapp_id'], $app_account_info['auth_refresh_token']);
        }else{
            $official_account_client = $open_platform->officialAccount($app_account_info['wxapp_id'], $app_account_info['auth_refresh_token']);
        }
        

        $server = $official_account_client->server;
        /**
         * 简单的处理 文本消息和事件
         */
        $server->push(TextMessageHandler::class, Message::TEXT);
        $server->push(EventMessageHandler::class, Message::EVENT);

        $response = $server->serve();
        return $response;
    }

    public function releaseToGlobalNet($open_platform, $authorizer_appid)
    {
        $message = $open_platform->server->getMessage();
        
        if($authorizer_appid == 'wx570bc396a51b8ff8'){
            $official_account_client = $open_platform->officialAccount($authorizer_appid);
            
        }else if($authorizer_appid == 'wxd101a85aa106f53e'){
            $official_account_client = $open_platform->miniProgram($authorizer_appid);
            
        }
        //返回API文本消息
        if ($message['MsgType'] == 'text' && strpos($message['Content'], "QUERY_AUTH_CODE:") !== false) {
            $auth_code = str_replace("QUERY_AUTH_CODE:", "", $message['Content']);
            $authorization = $open_platform->handleAuthorize($auth_code);
            if($authorizer_appid == 'wx570bc396a51b8ff8'){
                $official_account_client = $open_platform->officialAccount($authorizer_appid, $authorization['authorization_info']['authorizer_refresh_token']);
                
            }else if($authorizer_appid == 'wxd101a85aa106f53e'){
                $official_account_client = $open_platform->miniProgram($authorizer_appid, $authorization['authorization_info']['authorizer_refresh_token']);
                
            }
            $content = $auth_code . '_from_api';
            $official_account_client['customer_service']->send([
                'touser' => $message['FromUserName'],
                'msgtype' => 'text',
                'text' => [
                    'content' => $content
                ]
            ]);

            //返回普通文本消息
        } elseif ($message['MsgType'] == 'text' && $message['Content'] == 'TESTCOMPONENT_MSG_TYPE_TEXT') {
           
            $official_account_client->server->push(function ($message) {
                return $message['Content'] . "_callback";
            });
            //发送事件消息
        } elseif ($message['MsgType'] == 'event') {
            
            $official_account_client->server->push(function ($message) {
                return $message['Event'] . 'from_callback';
            });
        }
        $response = $official_account_client->server->serve();
        return $response;
    }

}
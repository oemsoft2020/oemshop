<?php

namespace app\common\library\sms\engine;

use app\common\library\sms\package\aliyun\SignatureHelper;

/**
 * 短信模块引擎
 */
class Aliyun extends Server
{
    private $config;

    /**
     * 构造方法
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * 发送短信通知
     */
    public function sendSms($mobile, $template_code, $templateParams)
    {

        //平台账号
        $account = $this->account();

        //平台密钥
        $secret = $this->config['AccessKeySecret'];

        //短信签名,不写默认'短信通'
//        $template_sign=  '短信通';

        //短信模板
        $template_code = $template_code;

        //手机号码
        $phone = $mobile;

        //发送内容
        $template_param = json_encode($templateParams);

        //请求地址
        $host = $this->getUrl();

        //开发者id (签名)
        $dev_id = $this->config['sign'];

        //请求编号
        $request_id = md5(time() . mt_rand(100000, 999999));

        //请求签名
        $sign = md5(md5($account . $secret) . $request_id);

        //当前的详细请求地址
        $request_url = $_SERVER["REQUEST_SCHEME"] . '://' . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"] . $_SERVER['QUERY_STRING'];


        // 參数数组

        $params = array(

            'account' => $account,

            'sign' => $sign,

            'request_url' => $request_url,

            'dev_id' => $dev_id,

            'request_id' => $request_id,

            'template_code' => $template_code,

            'phone' => $phone,

            'template_param' => $template_param

        );

        $response = $this->fetchContent($host, $params);
        $response = json_decode($response, 1);


        // 记录日志
        log_write([
            'config' => $this->config,
            'params' => $params
        ]);

        log_write($response);

        $this->error = $response['Message'];
        return $response;
    }

    /**
     * 获取账号
     * @return bool|mixed
     */
    private function account()
    {
        $user_account = $this->config['AccessKeyId'];
        $account_arr = explode('@', $user_account);
        if (count($account_arr) != 3) return false;

        return $account_arr[2];
    }


    /**
     * 获取请求url
     * @return bool|string
     */
    private function getUrl()
    {
        $user_account = $this->config['AccessKeyId'];
        $account_arr = explode('@', $user_account);
        if (count($account_arr) != 3) return false;

        $url = "http://{$account_arr[0]}.kemanduo.net/v2/api/sms/send";
        return $url;
    }

    /**
     * @param $url 请求url
     * @param $data 请求数据
     * @return bool|string
     */
    public function fetchContent($url, $data)
    {
        $curl = curl_init();

        $headers = array();

        curl_setopt($curl, CURLOPT_URL, $url);

        curl_setopt($curl, CURLOPT_POST, 1);

        curl_setopt($curl, CURLOPT_HEADER, 0);

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($curl, CURLOPT_FAILONERROR, 1);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        if (1 == strpos("$" . $url, "https://")) {

            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        }

        $result = curl_exec($curl);//接口数据返回
        return $result;
    }
}

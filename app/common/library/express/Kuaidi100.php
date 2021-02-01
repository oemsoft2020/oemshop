<?php

namespace app\common\library\express;

use think\facade\Cache;


/**
 * 快递100API模块
 */
class Kuaidi100
{
    // 配置
    private $config;

    // 错误信息
    private $error;

    /**
     * 构造方法
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * 执行查询
     */
    public function query($express_code, $express_no)
    {
        // 缓存索引
        $cacheIndex = 'express_' . $express_code . '_' . $express_no;
        if ($data = Cache::get($cacheIndex)) {
            return $data;
        }

        // 参数设置
        $time = time();
        $customer=explode('@',$this->config['customer']);
        if(count($customer)<2){
            $this->error="请在后台配置物流帐号";
            return  false;
        }
        $appid=$customer[2];
        $key=$this->config['key'];

        $postData = [
            'appid' => $appid,
            'no' => $express_no,
            'time' => $time,

        ];
        $postData['key'] = md5(md5( $appid. $key) . $time);

        $url = 'http://api2.kemanduo.net/v2/api/logistics/searchdo';
        $result = curlPost($url, http_build_query($postData));

        $express = json_decode($result, true);
        // 记录错误信息
        if ($express['status'] != 0 || !isset($express['result'])) {
            $this->error = isset($express['message']) ? $express['message'] : '查询失败';
            return false;
        }
        // 记录缓存, 时效5分钟
        Cache::set($cacheIndex, $express['result'], 300);
        return $express['result'];
    }

    /**
     * 返回错误信息
     */
    public function getError()
    {
        return $this->error;
    }

}

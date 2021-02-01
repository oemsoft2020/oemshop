<?php

namespace app\common\library\icp\engine;

use GuzzleHttp\Client;
use think\Exception;
use \app\shop\model\plus\icp\Icp as IcpModel;

/**
 * aizhan 接口
 */
class Aizhan extends Server
{
    private $config;
    private $privateKey;
    private $apiName = 'aizhan';
    private $baseUrl = 'https://apistore.aizhan.com/site/icpinfos/';

    public static $statusCode = [
        '200000' => '请求成功',
        '200001' => '参数错误',
        '200801' => '提交域名数量已达上限',
        '100000' => '未知错误',
        '100001' => '缺少hash',
        '100002' => '无效hash',
        '100003' => '接口维护',
        '100004' => '接口停用',
        '100005' => '余额不足,请充值',
        '100006' => '支付失败,请重试',
    ];

    /**
     * 构造方法
     * @param array $config
     * @throws Exception
     */
    public function __construct(array $config)
    {
        parent::__construct();
        $this->config = $config;
        $this->init($this->config);
    }

    /**
     * 初始化密钥
     * @param array $config
     * @throws Exception
     */
    private function init(array $config){
        if (!$config['private_key']) throw new Exception('请配置好密钥');
        $this->privateKey = $config['private_key'];
    }

    /**
     * 查询icp信息
     * @param string $webSite 查询的域名
     * @return string
     * @throws Exception
     */
    public function icpQurey(string $webSite)
    {
        $url = $this->getUrl($webSite);

        $client = new Client();

        $response = $client->get($url);

        $res = $response->getBody()->getContents();
        $code = $response->getStatusCode();
        if ($code === 200) $res = json_decode($res, true);

        // log
        $data = [
            'website' => $webSite,
            'res' => $res,
            'code' => $code === 200 && $res['code'] === 200000
        ];
        $rrr = $this->log($data);

        if ($code !== 200)throw new Exception('请求接口错误');

        return $res;
    }

    /**
     * 请求示例：https://apistore.aizhan.com/site/icpinfos/[私钥]?domains=www.aizhan.com
     * @param string $webSite
     * @return string
     * @throws Exception
     */
    private function getUrl(string $webSite){
        if (!$webSite) throw new Exception('请传入需要查询的域名字符串');
        return $this->baseUrl.$this->privateKey.'?domains='.$webSite;
    }

    /**
     * 记录
     * @param array $data
     * @return IcpModel|\think\Model
     */
    protected function log(array $data)
    {
        $icp = new IcpModel();
        $data = [
            'query_string' => $data['website'],
            'result' => json_encode($data['res']),
            'status' => $data['code'],
            'icp_api' => $this->apiName,
            'create_time' => time(),
        ];
        return $icp->add($data);
    }


}

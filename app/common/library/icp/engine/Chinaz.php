<?php

namespace app\common\library\icp\engine;

use GuzzleHttp\Client;
use think\Exception;
use \app\shop\model\plus\icp\Icp as IcpModel;

/**
 * aizhan 接口
 */
class Chinaz extends Server
{
    private $config;
    private $privateKey;
    private $apiName = 'chinaz';
    private $baseUrl = 'https://apidata.chinaz.com/CallAPI/Domain';
    private $baseNewUrl = 'https://apidata.chinaz.com/CallAPI/NewDomain';

    public static $statusCode = [
        '1' => '成功',
        '0' => '获取数据状态信息',
        '-1' => '系统异常',
        '10001	' => '错误的请求KEY',
        '10002' => '该KEY无请求权限',
        '10003' => 'KEY过期',
        '10004' => '错误的SDK KEY',
        '10005' => '应用未审核，请提交认证',
        '10007' => '未知的请求源，（服务器没有获取到IP地址）',
        '10008' => '被禁止的IP',
        '10009' => '被禁止的KEY',
        '10011' => '当前IP请求超过限制',
        '10012' => '当前Key请求超过限制',
        '10013' => '测试KEY超过请求限制',
        '10020' => '接口维护',
        '10021' => '接口停用',
        '10022' => 'appKey剩余请求次数不足',
        '10023' => '请求IP无效',
        '10024' => '网络错误',
        '10025' => '没有查询到结果',
        '10026' => '当前请求频率过高超过权限限制',
        '10027' => '账号违规被冻结',
        '10028' => '传递参数错误',
        '10029' => '系统内部异常，请重试',
        '10030' => '校验值sign错误',
        '10031' => '套餐产品编号不存在',
        '10032' => '虚拟账号余额不足',
        '10033' => '提交的订单号不能重复',
        '10034' => '通道请求超时',
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
        $codeFalg = false;
        if ($code === 200){
            $res = json_decode($res, true);
            // 统一好数据
            $res = $this->uniteData($webSite, $res);
            $codeFalg = $res['code'] === 200000;
        }

        // log
        $data = [
            'website' => $webSite,
            'res' => is_array($res) ? json_encode($res) : $res,
            'code' => $code === 200 && $codeFalg
        ];
        $rrr = $this->log($data);

        if ($code !== 200)throw new Exception('请求接口错误');

        return $res;
    }

    /**
     * 请求示例：http://apidata.chinaz.com/CallAPI/Domain?key=申请的key&domainName=chinaz.com
     * @param string $webSite
     * @return string
     * @throws Exception
     */
    private function getUrl(string $webSite){
        if (!$webSite) throw new Exception('请传入需要查询的域名字符串');
        if (isset($this->config['new']['private_key'])){
            $url = $this->baseNewUrl.'?key='.$this->config['new']['private_key'].'&domainName='.$webSite;
        }else{
            $url = $this->baseUrl.'?key='.$this->privateKey.'&domainName='.$webSite;
        }
        return $url;
    }

    /**
     * 统一数据格式
     * @param string $webSite
     * @param array $data
     * @return array
     */
    private function uniteData(string $webSite, array $data){
        $res['code'] = !empty($data['Result']['CompanyName']) ? 200000 : 100000;
        $res['status'] = !empty($data['Result']['CompanyName']) == 1 ? 'success' : 'fail';
        $res['msg'] = $data['Reason'];
        if (!empty($data['Result']['CompanyName'])){
            $res['data']['success'][0]['domain'] = $webSite;
            $res['data']['success'][0]['domains'] = $webSite;
            $res['data']['success'][0]['homes'] = $data['Result']['MainPage'];
            $res['data']['success'][0]['company'] = $data['Result']['CompanyName'];
            $res['data']['success'][0]['type'] = $data['Result']['CompanyType'];
            $res['data']['success'][0]['icp'] = $data['Result']['SiteLicense'];
            $res['data']['success'][0]['name'] = $data['Result']['SiteName'];
            $res['data']['success'][0]['icp_time'] = $data['Result']['VerifyTime'];
        }
        return $res;
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
            'result' => $data['res'],
            'status' => $data['code'],
            'icp_api' => $this->apiName,
            'create_time' => time(),
        ];
        return $icp->add($data);
    }


}

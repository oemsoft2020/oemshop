<?php


namespace app\common\library\elasticsearch;

use Elasticsearch\ClientBuilder;
use Exception;
use think\facade\Config;

class ElasticSearch
{

    static private $hosts;

    //创建静态私有的变量保存该类对象
    static private $instance;

    /**
     * 构造方法,防止使用new直接创建对象
     * ElasticSearch constructor.
     * @throws Exception
     */
    private function __construct(){ }

    //防止使用clone克隆对象
    private function __clone(){}

    /**
     * 初始化es配置
     * @throws Exception
     */
    static private function init(){
        $config = Config::get('database.connections.es');
        if (!isset($config['hosts'])) throw new Exception('参数错误');
        self::$hosts = $config['hosts'];
    }

    /**
     * 获取单例ES客户端
     * @return \Elasticsearch\Client
     * @throws Exception
     */
    static public function getInstance()
    {
        //判断$instance是否是Singleton的对象，不是则创建
        if (!self::$instance instanceof self) {
            self::init();
            self::$instance = ClientBuilder::create()->setHosts(self::$hosts)->build();
        }
        return self::$instance;
    }



}
<?php

namespace app\common\library\icp\engine;


/**
 * icp接口查询抽象类
 */
abstract class Server
{

    /**
     * 构造函数
     */
    protected function __construct() { }

    /**
     * 查询icp域名
     * @param string $webSite 查询的域名
     */
    abstract protected function icpQurey(string $webSite);

    /**
     * 记录接口查询历史
     * @param $data
     * @return mixed
     */
    abstract protected function log(array $data);

}

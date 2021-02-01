<?php

namespace app\common\library\icp;

use think\Exception;

/**
 * icp查询类
 */
class IcpQurey
{
    private $config;    // 接口 配置
    private $icpApi;    // 当前接口类

    /**
     * 构造方法
     * @param $config
     * @param null $icpApi
     * @throws Exception
     */
    public function __construct($config, $icpApi = null)
    {
        $this->config = $config;
        // 实例化当前接口
        $this->icpApi = $this->getIcpApiClass($icpApi);
    }

    /**
     * 根据 $website 查询备案信息
     * @param string $website
     * @return string
     */
    public function qureyICP(string $website){
        return $this->icpApi->icpQurey($website);
    }

    /**
     * 获取icp查询接口类
     * @param null $icpApi
     * @return mixed
     * @throws Exception
     */
    private function getIcpApiClass($icpApi = null)
    {
        $engineName = is_null($icpApi) ? $this->config['default'] : $icpApi;
        $classSpace = __NAMESPACE__ . '\\engine\\' . ucfirst($engineName);
        if (!class_exists($classSpace)) {
            throw new Exception('未找到查询接口类: ' . $engineName);
        }
        return new $classSpace($this->config['api'][$engineName]);
    }

}

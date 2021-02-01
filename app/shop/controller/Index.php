<?php

namespace app\shop\controller;

use app\api\model\settings\Setting as SettingModel;
use app\shop\model\app\AppMp;
use app\shop\model\app\AppWx;
use app\shop\service\ShopService;

/**
 * 后台首页控制器
 */
class Index extends Controller
{
    /**
     * 后台首页
     */
    public function index()
    {
        $service = new ShopService;
        $app = array();
        $wxApp = AppWx::detail();
        $wxApp['app_name'] = $wxApp['wxapp_name']?:'';
        $wxApp['app_type'] = "小程序";
        $mpApp = AppMp::detail();
        $mpApp['app_name'] = $mpApp['mpapp_name']?:'';
        $mpApp['app_type'] = "公众号";

        if (!empty($wxApp['qrcode'])) array_push($app, $wxApp);
        if (!empty($mpApp['qrcode'])) array_push($app, $mpApp);
        return $this->renderSuccess('', ['data' => $service->getHomeData(), 'app' => $app]);
    }
}
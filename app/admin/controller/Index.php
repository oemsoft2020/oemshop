<?php

namespace app\admin\controller;
/**
 * 后台首页
 */
class Index extends Controller
{
    /**
     * 后台首页
     */
    public function index()
    {
        $version = get_version();
        $data['site_name']="帮助中心";
        $data['site_url']="http://www.vkshop.cn/";
        return $this->renderSuccess('', compact('version','data'));
    }
}
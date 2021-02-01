<?php

namespace app\api\controller\plus\agent;

use app\api\controller\Controller;
use app\api\model\plus\agent\Setting;
use app\api\model\plus\agent\User as AgentUserModel;
use app\common\service\qrcode\PosterService;

/**
 * 推广二维码
 */
class Qrcode extends Controller
{
    // 当前用户
    private $user;
    // 分销商
    private $agent;
    // 分销设置
    private $setting;

    /**
     * 构造方法
     */
    public function initialize()
    {
        // 用户信息
        $this->user = $this->getUser();
        // 分销商用户信息
        $this->agent = AgentUserModel::detail($this->user['user_id']);
        // 分销商设置
        $this->setting = Setting::getAll();
    }

    /**
     * 获取推广二维码
     */
    public function poster($source)
    {
        if(empty($this->agent)){
            $Qrcode_url = "http://h5.y01.cn/image/forbidden.jpg";
            return $this->renderSuccess('', [
                // 二维码图片地址
                'qrcode' => $Qrcode_url
            ]);
        }
        $Qrcode = new PosterService($this->agent, $source);
        $Qrcode_url = $Qrcode->getImage();
        return $this->renderSuccess('', [
            // 二维码图片地址
            'qrcode' => $Qrcode_url
        ]);
    }

    /**
     * 生成印刷二维吗
     * @Author   linpf
     * @DataTime 2020-11-03T11:41:04+0800
     * @return   [type]                   [description]
     */
    public function copyPoster($source)
    {
        $Qrcode = new PosterService($this->agent, $source);
        return $this->renderSuccess('', [
            // 二维码图片地址
            'qrcode' => $Qrcode->getCopyImg(1),
        ]);
    }

    /**
     * 重新制作印刷码
     * @Author   linpf
     * @DataTime 2020-11-03T13:45:06+0800
     * @param    [type]                   $source [description]
     * @return   [type]                           [description]
     */
    public function reMakePoster($source)
    {
        $Qrcode = new PosterService($this->agent, $source);
        return $this->renderSuccess('', [
            // 二维码图片地址
            'qrcode' => $Qrcode->getCopyImg(2),
        ]);
    }

    /**
     * 重新生成二维码
     * @Author   linpf
     * @DataTime 2020-11-03T17:46:53+0800
     * @param    [type]                   $source [description]
     * @return   [type]                           [description]
     */
    public function reMakeQrcode($source)
    {
       $Qrcode = new PosterService($this->agent, $source);
        return $this->renderSuccess('', [
            // 二维码图片地址
            'qrcode' => $Qrcode->getImage(2),
        ]);
    }

}
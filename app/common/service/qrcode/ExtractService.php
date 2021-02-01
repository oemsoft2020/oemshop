<?php

namespace app\common\service\qrcode;

use app\common\enum\order\OrderTypeEnum;
use Endroid\QrCode\QrCode;

/**
 * 订单核销二维码
 */
class ExtractService extends Base
{
    private $appId;

    //用户
    private $user;

    private $orderId;

    private $orderType;

    private $source;

    /**
     * 构造方法
     */
    public function __construct($appId, $user, $orderId, $source, $orderType = OrderTypeEnum::MASTER)
    {
        parent::__construct();
        $this->appId = $appId;
        $this->user = $user;
        $this->orderId = $orderId;
        $this->orderType = $orderType;
        $this->source = $source;
    }

    /**
     * 获取小程序码
     */
    public function getImage()
    {
        // 判断二维码文件存在则直接返回url
        if (file_exists($this->getPosterPath())) {
            return $this->getPosterUrl();
        }
        if($this->source == 'wx') {
            // 下载小程序码
            $qrcode = $this->saveQrcode(
                $this->appId,
                "oid:{$this->orderId}",
                'pages/store/clerkorder'
            );
        } else if($this->source == 'mp'){
            $scene = "oid:{$this->orderId},otype:" . ($this->orderType ?: '');
            $qrcode = new QrCode(base_url().'h5/pages/store/clerkorder?order_id='.$this->orderId.'&order_type='.$this->orderType ?: '');
            $qrcode = $this->saveMpQrcode($qrcode, $this->appId, $scene);
        }
        return $this->savePoster($qrcode);
    }

    private function savePoster($qrcode)
    {
        copy($qrcode, $this->getPosterPath());
        return $this->getPosterUrl();
    }

    /**
     * 二维码文件路径
     */
    private function getPosterPath()
    {
        $web_path = $_SERVER['DOCUMENT_ROOT'];
        // 保存路径
        $tempPath = $web_path . "/temp/{$this->appId}/{$this->source}/";
        !is_dir($tempPath) && mkdir($tempPath, 0755, true);
        return $tempPath . $this->getPosterName();
    }

    /**
     * 二维码文件名称
     */
    private function getPosterName()
    {
        return 'clerk_' . md5("{$this->orderId}_{$this->user['user_id']}}") . '.png';
    }

    /**
     * 二维码url
     */
    private function getPosterUrl()
    {
        return \base_url() . "temp/{$this->appId}/{$this->source}/{$this->getPosterName()}" . '?t=' . time();
    }

}
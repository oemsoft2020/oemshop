<?php

namespace app\common\service\qrcode;

use app\common\library\easywechat\AppWx;
use Grafika\Color;
use Grafika\Grafika;
use Endroid\QrCode\QrCode;

class CardService extends Base
{
    // 名片信息
    private $card;

    // 用户id
    private $user_id;

    // 来源，微信小程序，公众号
    private $source;

    // 小程序码链接
    private $pages = 'card/pages/card/index';

    /**
     * 构造方法
     */
    public function __construct($card, $user_id,$source)
    {
        parent::__construct();
        // 商品信息
        $this->card = $card;
        // 当前用户id
        $this->user_id = $user_id ? $user_id : 0;

        //来源
        $this->source = $source;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        // 判断海报图文件存在则直接返回url
        if (file_exists($this->getPosterPath())) {
            return $this->getPosterUrl();
        }
        // 小程序id
        $appId = $this->card['app_id'];
        
        if($this->source == 'wx'){
            // 小程序码参数
            $scene = "card_id:{$this->card['card_id']},uid:" . ($this->user_id ?: '');
            // 下载小程序码
            $qrcode = $this->saveQrcode($appId, $scene, $this->pages);
        }else if($this->source == 'mp'){
            $scene = "card_id:{$this->card['card_id']},uid:" . ($this->user_id ?: '');
            $qrcode = new QrCode(base_url().'h5/pages/card/index?card_id='.$this->card['card_id'].'&referee_id='.$this->user_id ?: '');
            $qrcode = $this->saveMpQrcode($qrcode, $appId, $scene);
        }
        // 拼接海报图
        return $this->savePoster($qrcode);
    }

    /**
     * 拼接海报图
     */
    private function savePoster($qrcode)
    {
        // 实例化图像编辑器
        $editor = Grafika::createEditor(['Gd']);
        // 字体文件路径
        $fontPath = Grafika::fontsDir() . '/' . 'st-heiti-light.ttc';
        // 打开小程序码
        $editor->open($qrcodeImage, $qrcode);
        // 保存图片
        $editor->save($qrcodeImage, $this->getPosterPath());
        return $this->getPosterUrl();
    }
    /**
     * 海报图文件路径
     */
    private function getPosterPath()
    {
        // 保存路径
        $tempPath = root_path('public') . 'temp' . '/' . $this->card['app_id'] . '/' . $this->source. '/';
        !is_dir($tempPath) && mkdir($tempPath, 0755, true);
        return $tempPath . $this->getPosterName();
    }

    /**
     * 海报图文件名称
     */
    private function getPosterName()
    {
        return 'card_' . md5("{$this->user_id}_{$this->card['card_id']}") . '.png';
    }

    /**
     * 海报图url
     */
    private function getPosterUrl()
    {
        return \base_url() . 'temp/' . $this->card['app_id'] . '/' .$this->source . '/' . $this->getPosterName() . '?t=' . time();
    }


}
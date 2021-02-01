<?php

namespace app\common\service\qrcode;

use app\common\library\easywechat\AppWx;
use Grafika\Color;
use Grafika\Grafika;
use Endroid\QrCode\QrCode;

class StorageService extends Base
{
    // 名片信息
    private $storage;
    // 名片信息
    private $code_template_id;
    // 用户id
    // 来源，微信小程序，公众号
    private $source;
    // 用户id
    private $user_id;

    // 小程序码链接
    private $pages = 'pages/user/storage/qrcodedetail';

    /**
     * 构造方法
     */
    public function __construct($storage,$code_template_id,$user,$source='wx')
    {
        parent::__construct();
        // 商品信息
        $this->storage = $storage;
        $this->code_template_id = $code_template_id;
        // 当前用户id
        $this->user_id = $user ? $user['user_id'] : 0;

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
        $appId = $this->storage['app_id'];
        
        if($this->source == 'wx'){
            // 小程序码参数
            $scene = "storage_id:{$this->storage['storage_id']},code_template_id:{$this->code_template_id}";
            // 下载小程序码
            return $this->saveQrcode($appId, $scene, $this->pages);
        }
//        else if($this->source == 'mp'){
//            $scene = "storage_id:{$this->storage['storage_id']},code_template_id:{$this->code_template_id},desc:{$this->desc},type:{$this->type}";
//            $qrcode = new QrCode(base_url().'h5/pages/card/qrcodeinfo?card_id='.$this->card['card_id'].'&referee_id='.$this->user_id ?: '');
//            $qrcode = $this->saveMpQrcode($qrcode, $appId, $scene);
//        }
        // 拼接海报图
//        return $this->savePoster($qrcode);
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
        $tempPath = root_path('public') . 'temp' . '/' . $this->storage['app_id'] . '/' . $this->source. '/';
        !is_dir($tempPath) && mkdir($tempPath, 0755, true);
        return $tempPath . $this->getPosterName();
    }

    /**
     * 海报图文件名称
     */
    private function getPosterName()
    {
        return 'card_' . md5("{$this->user_id}_{$this->storage['storage_id']}") . '.png';
    }

    /**
     * 海报图url
     */
    private function getPosterUrl()
    {
        return \base_url() . 'temp/' . $this->storage['app_id'] . '/' .$this->source . '/' . $this->getPosterName() . '?t=' . time();
    }


}
<?php

namespace app\common\service\qrcode;

use app\common\library\easywechat\AppWx;
use Grafika\Color;
use Grafika\Grafika;
use Endroid\QrCode\QrCode;

class QrcodeService extends Base
{
    // 信息
    private $info;

    // 来源，微信小程序，公众号
    private $source;

    // 链接
    private $page;

    // 参数
    private $scene;


    /**
     * 构造方法
     * $scene 参数 array
     * $page  路径 需要与source对应,不需要带域名
     */
    public function __construct($info,$source,$page,$scene)
    {
        parent::__construct();
        // 商品信息
        $this->info = $info;
        //来源
        $this->source = $source;
        // 路径
        $this->page = $page;
        // 参数
        $this->scene = $scene;
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
        $appId = $this->info['app_id'];
        $scene = '';
        if($this->scene&&is_array($this->scene)){
            foreach ($this->scene as $key => $value) {
                if(empty($scene)){
                    $scene = $key.":".$value;
                }else{
                    $scene = ",".$key.":".$value;
                }
                
            }
        }
        
        if($this->source == 'wx'){
            // 下载小程序码
            $qrcode = $this->saveQrcode($appId, $scene, $this->page);
        }else if($this->source == 'mp'){
            $query= http_build_query($this->scene); 
            $qrcode = new QrCode(base_url().'h5/'.$this->page.'?'.$query);
            $qrcode = $this->saveMpQrcode($qrcode, $appId, $scene);
        }

        $editor = Grafika::createEditor(['Gd']);
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
        $tempPath = root_path('public') . 'temp' . '/' . $this->info['app_id'] . '/' . $this->source. '/';
        !is_dir($tempPath) && mkdir($tempPath, 0755, true);
        return $tempPath . $this->getPosterName();
    }

    /**
     * 海报图文件名称
     */
    private function getPosterName()
    {   
        if(is_array($this->info)){
            $temp = $this->info['app_id'] . http_build_query( $this->scene ). $this->page;
        }else{
            $temp = json_encode($this->info);
        }
       
        return 'info_' . md5("{$temp}") . '.png';
    }

    /**
     * 海报图url
     */
    private function getPosterUrl()
    {
        return \base_url() . 'temp/' . $this->info['app_id'] . '/' .$this->source . '/' . $this->getPosterName() . '?t=' . time();
    }


}
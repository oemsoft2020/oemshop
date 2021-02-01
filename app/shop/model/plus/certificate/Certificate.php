<?php

namespace app\shop\model\plus\certificate;

use app\common\model\plus\certificate\Certificate as CertificateModel;
use app\common\model\file\UploadFile as UploadFileModel;
use app\api\model\user\User as UserModel;
use Grafika\Color;
use Grafika\Grafika;
use Endroid\QrCode\QrCode;

/**
 * 授权证书
 */
class Certificate extends CertificateModel
{
    /**
     * 获取授权证书列表
     */
    public function getList($search)
    {
        $model = $this;

        if (!empty($search['keyword'])) {
            $model =$model->where('nick_name|phone', 'like', '%' . $search['keyword'] . '%');
        }

        if (!empty($search['status'])) {
           switch ($search['status']) {
                case 'will':
                   $model = $model->where('status',0);
                   break;
                case 'success':
                   $model = $model->where('status',1);
                   break;
                case 'fail':
                    $model = $model->where('status',2);
                    break;   
               default:
                   
                   break;
           }
        }   

        $file_mod = new UploadFileModel();

        // 获取列表数据
        $data = $model->paginate($search['list_rows'], false, [
            'query' => \request()->request()
        ])->each(function($item,$key)use($file_mod){
            switch ($item->status) {
                case 0:
                    $item->status_text = '待审核';
                    break;
                case 1:
                    $item->status_text = '审核通过';
                    break;
                case 2:
                    $item->status_text = '审核不通过';
                    break;
                default:
                    # code...
                    break;
            }

            if(!empty($item->img_id)){
                $file_info = $file_mod->where('file_id',$item->img_id)->find();
                if($file_info){
                    $item->img_url = $file_info['file_url'].'/'.$file_info['file_name'];
                }else{
                    $item->img_url = '';
                }
            }else{
                $item->img_url = '';
            }

        });

        return $data;
    }

    /**
     * 添加证书
     * @param $data
     * @return bool
     */
    public function submit($data = array())
    {
        if(empty($data)){
            return false;
        }

        $save_data = [
            'img_id' => $data['image_id'],
            'nick_name' => $data['nick_name'],
            'phone' => $data['phone'],
            'status' => $data['status'],
            'app_id' => self::$app_id
        ];

        $this->save($save_data);
        return true;
    }

    /**
     * 查询证书
     * @Author   linpf
     * @DataTime 2020-10-28T16:25:50+0800
     * @param    string                   $keyword [关键词]
     * @return   [type]                            [description]
     */
    public function findCertByWord($keyword = '')
    {
        $file_mod = new UploadFileModel();

        if(empty($keyword)){
            return false;
        }

        $model = $this;
        $info = $model->where('nick_name|phone',$keyword)->find();
        if(in_array($info['status'], [0,2])){
            return false;
        }

        if(!empty($info['img_id'])){
            $file_info = $file_mod->where('file_id',$info['img_id'])->find();
            if($file_info){
                $info['img_url'] = $file_info['file_url'].'/'.$file_info['file_name'];
            }else{
                return false;
            }
        }else{
            return false;
        }

        return $info;
    }


    /**
     * 合成证书
     * @Author   linpf
     * @DataTime 2020-10-28T17:22:50+0800
     * @param    string                   $user_id   [用户id]
     * @return   [type]                          [description]
     */
    public function madeCertImg($user_id = '',$path = '',$code = '')
    {
        if(empty($user_id) || empty($path)){
            return false;
        }
        
        // 查询证书
        $cert_info = $this->where(['user_id'=>$user_id])->find();

        if($cert_info){

            // 查询会员编号
            $user_code = UserModel::makeMyCode($user_id,$code);
            
            $id = $cert_info['certificate_id'];
            if(!empty($cert_info['path'])){
                return $cert_info['path'];
            }
            // $file_mod = new UploadFileModel();
            // $file_info = $file_mod->where('file_id',$cert_info['img_id'])->find();
            // if($file_info){
            //     $img = $file_info['file_url'].'/'.$file_info['file_name'];
            // }
            
            $time = explode(' ', $cert_info['create_time'])[0];
            $name = $cert_info['nick_name'];
            $english_name = $cert_info['english_name'];

            // 下载图片到本地
            $backdrop = root_path(). '/runtime/'.time().rand(1000,99999).'.png';
            @file_put_contents($backdrop, file_get_contents($path));
            // 实例化图像编辑器
            $editor = Grafika::createEditor(['Gd']);
            // 字体文件路径
            $fontPath = Grafika::fontsDir() . '/' . 'st-heiti-light.ttc';
            // 打开海报背景图
            $editor->open($backdropImage, $backdrop);
            $fontSize = 20;
            $nickName = $this->wrapText($fontSize, 0, $fontPath, $name, 680, 2);
            // 写入昵称
            $editor->text($backdropImage, $nickName, $fontSize, 190, 600, new Color('#e2231a'), $fontPath);
            // 写入英文昵称
            $editor->text($backdropImage, $english_name, $fontSize, 360, 630, new Color('#e2231a'), $fontPath);
            // 写入时间
            $editor->text($backdropImage, $time, 11, 600, 747, new Color('#5f7475'), $fontPath);
            // 写入编码
            $editor->text($backdropImage, $user_code, 11, 360, 840, new Color('#5f7475'), $fontPath);

            @unlink($backdrop);
            $img_info = $this->getPosterPath();

            // 保存图片
            $editor->save($backdropImage, $img_info['url']);
            $img_path = $this->getPosterUrl($img_info['name']);

            if(!empty($img_path)){
                $this->where('certificate_id',$id)->update(['path'=>$img_path]);

                return $img_path;
            }else{
                return false;
            }
        }else{
            return false;
        }

    }

    /**
     * 海报图文件路径
     */
    private function getPosterPath()
    {
        // 保存路径
        $tempPath = root_path('public') . 'temp' . '/' . self::$app_id . '/certificate/';
        !is_dir($tempPath) && mkdir($tempPath, 0755, true);
        $name = $this->getPosterName();

        return ['url'=>$tempPath . $name,'name'=>$name];
    }

    /**
     * 海报图文件名称
     */
    private function getPosterName()
    {
        return 'certificate_' . md5(time().rand(0,999)) . '.png';
    }

    /**
     * 海报图url
     */
    private function getPosterUrl($name = '')
    {
        return \base_url() . 'temp/' . self::$app_id . '/certificate/' .$name . '?t=' . time();
    }

        /**
     * 处理文字超出长度自动换行
     */
    private function wrapText($fontsize, $angle, $fontface, $string, $width, $max_line = null)
    {
        // 这几个变量分别是 字体大小, 角度, 字体名称, 字符串, 预设宽度
        $content = "";
        // 将字符串拆分成一个个单字 保存到数组 letter 中
        $letter = [];
        for ($i = 0; $i < mb_strlen($string, 'UTF-8'); $i++) {
            $letter[] = mb_substr($string, $i, 1, 'UTF-8');
        }
        $line_count = 0;
        foreach ($letter as $l) {
            $testbox = imagettfbbox($fontsize, $angle, $fontface, $content . ' ' . $l);
            // 判断拼接后的字符串是否超过预设的宽度
            if (($testbox[2] > $width) && ($content !== "")) {
                $line_count++;
                if ($max_line && $line_count >= $max_line) {
                    $content = mb_substr($content, 0, -1, 'UTF-8') . "...";
                    break;
                }
                $content .= "\n";
            }
            $content .= $l;
        }
        return $content;
    }
}
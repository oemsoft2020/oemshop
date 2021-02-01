<?php

namespace app\api\controller\user;

use app\api\controller\Controller;
use app\shop\model\plus\certificate\Certificate as CertificateModel;
use app\shop\model\settings\Setting as SettingModel;
use think\facade\Db;

/**
 * 授权证书
 */
class Certificate extends Controller
{

    /**
     * 查询授权证书
     */
    public function findCert()
    {
        $set_mod = new SettingModel;
        $cert_mod = new CertificateModel;

        $vars = $set_mod::getItem('certificate');
        if(empty($vars) || !$vars['is_open_cert']){
            return $this->renderError('授权证书配置未开启');
        }

        $params = $this->postData();
        
        if(!isset($params['search']) || empty($params['search'])){
            return $this->renderError('请输入手机号码或者昵称');
        }

        $data = $cert_mod->findCertByWord($params['search']);
        
        if($data){
            return $this->renderSuccess('', compact('data'));
        }else{
            return $this->renderError('证书不存在或者审核未通过');
        }
        
    }

    /**
     * 合成证书
     * @Author   linpf
     * @DataTime 2020-10-28T17:34:51+0800
     * @return   [type]                   [description]
     */
    public function makeImgCert()
    {
        $cert_mod = new CertificateModel;
        $user = $this->getUser();
        // $params = $this->postData();
        // $id = $params['id'];
        $set_mod = new SettingModel;
        $vars = $set_mod::getItem('certificate');
        if(isset($vars['image']) && !empty($vars)){
            $imgs = json_decode($vars['image']);
            $path = isset($imgs[0]) ? $imgs[0] : '';
        }

        if(empty($path)){
            return $this->renderError('后台还未上传证书背景图');
        }

        $info = $cert_mod->madeCertImg($user['user_id'],$path,$user['grade']['grade_code']);

        if($info){
            return $this->renderSuccess('', compact('info'));
        }else{
            return $this->renderError('证书生成失败');
        }        
    }

    /**
     * 提交证书数据
     * @Author   linpf
     * @DataTime 2020-11-27T10:44:48+0800
     * @return   [type]                   [description]
     */
    public function postImgCert()
    {
        $params = $this->postData();
        $set_mod = new SettingModel;
        $cert_mod = new CertificateModel;
        $user = $this->getUser();

        $vars = $set_mod::getItem('certificate');

        if(empty($vars) || !$vars['is_open_cert']){
            return $this->renderError('授权证书配置未开启');
        }

        $save_data = array(
            'nick_name' => $params['name'],
            'phone' => $params['mobile'],
            'user_id' => $user['user_id'],
            'status' => 1,
            'create_time' => time(),
            'update_time' => time(),
            'english_name' => $params['english_name'],
            'app_id' => $this->app_id
        );

        $res = $cert_mod->insert($save_data);

        return $res ?  $this->renderSuccess('提交成功') : $this->renderError('提交失败');
    }

}
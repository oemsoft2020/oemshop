<?php

namespace app\admin\controller;

use app\common\library\easywechat\AppWxPlatform as AppWxPlatform;
use app\common\model\plus\port\PortTester;
use app\shop\model\plus\port\Port as PortModel;
use app\shop\model\plus\port\PortTester as PortTesterModel;
use app\shop\model\app\AppMp as AppMpModel;
use app\shop\model\app\AppWx as AppWxModel;
use app\shop\model\plus\port\PortVersion;

/**
 * 渠道控制器
 */
class Port extends Controller
{
   
    /* 
    * 同步代码模板
    */

    public function codeTemplateList()
    {
        $platform =  new AppWxPlatform;
        $app = $platform::getApp();
        $code_template= $app->code_template;
        $res = $code_template->list();
        $port_version_model = new PortVersion();
        if($res['errcode']==0){
            $data = [];
            $where = [
                ['type','=','wxapp'],
                ['template_id','<>',0]
            ];
            $port_version_model->where($where)->delete();
            foreach($res['template_list'] as $draf){
                $data[]=[
                    'version_name'=>$draf['user_version'],
                    'version_desc'=>$draf['user_desc'],
                    'type'=>'wxapp',
                    'template_id'=>$draf['template_id'],
                    'create_tiem'=>time(),
                ];

            }
            $port_version_model->saveAll($data);
        }

    }

    /* 
    * 获取代码列表
    */

    public function versionList()
    {
        $port_version_model = new PortVersion();
        $list = $port_version_model->getlist($this->postData());

        return $this->renderSuccess('',compact('list'));
    }

    
    
}
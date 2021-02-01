<?php

namespace app\shop\controller\setting;

use app\shop\controller\Controller;
use app\shop\model\settings\Setting as SettingModel;
use app\common\library\sms\Driver as SmsDriver;

/**
 * 短信配置控制器
 */
class Sms extends Controller
{
    /**
     * 短信配置
     */
    public function index()
    {
        if($this->request->isGet()){
            return $this->fetchData();
        }
        $model = new SettingModel;
        $data = $this->request->param();
        $arr = [
            'default' => 'aliyun',
            'engine' => [
                'aliyun' => [
                    'AccessKeyId' => $data['AccessKeyId'],
                    'AccessKeySecret' => $data['AccessKeySecret'],
                    'sign' => $data['sign'],
                    'accept_phone' => $data['accept_phone'],
                    'template_code' => $data['template_code'],
                ]
            ]
        ];
        if ($model->edit('sms', $arr)) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 获取短信配置
     */
    public function fetchData()
    {
        $key = 'sms';
        $vars['values'] = SettingModel::getItem($key);
        return $this->renderSuccess('', compact('vars'));
    }

}
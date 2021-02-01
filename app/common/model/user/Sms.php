<?php

namespace app\common\model\user;

use app\common\library\sms\Driver as SmsDriver;
use app\common\model\BaseModel;
use app\common\model\settings\Setting as SettingModel;
use app\common\model\shop\OptLog as OptLogModel;
/**
 * 短信模型
 */
class Sms extends BaseModel
{
    protected $pk = 'sms_id';
    protected $name = 'sms';

    /**
     * 短信发送
     */
    public function send($mobile)
    {
        $smsConfig = SettingModel::getItem('sms', self::$app_id);
        $template_code = $smsConfig['engine'][$smsConfig['default']]['template_code'];
        if (empty($template_code)) {
            $this->error = '短信登录未开启';
            return false;
        }


        $code = str_pad(mt_rand(100000, 999999), 6, "0", STR_PAD_BOTH);

        //$code  = 1234;
        $this->saveMobileLog($mobile, $code);

        $SmsDriver = new SmsDriver($smsConfig);
        $send_data = [
            'code' => $code
        ];

//        //短信模板
        $flag = $SmsDriver->sendSms($mobile, $template_code, $send_data);

        // $flag = true;

        if ($flag['Code'] == "FAIL") {
            $this->error = $flag['Message'];
        }

        if ($flag) {
            $this->save([
                'mobile' => $mobile,
                'code' => $code,
                'app_id' => self::$app_id
            ]);
        }
        return $flag;
    }

    /**
     * 操作日志
     */
    protected function saveMobileLog($mobile, $code)
    {
        $model = new OptLogModel();
        $model->save([
            'shop_user_id' => '',
            'ip' => \request()->ip(),
            'request_type' => 'Get',
            'url' => 'page/index/weblogin',
            'content' => "手机号：" . $mobile . "\r验证码：" . $code,
            'browser' => get_client_browser(),
            'agent' => $_SERVER['HTTP_USER_AGENT'],
            'title' => "手机号：" . $mobile . "验证码：" . $code,
            'app_id' => self::$app_id
        ]);
    }
}

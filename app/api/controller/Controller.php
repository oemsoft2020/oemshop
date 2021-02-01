<?php

namespace app\api\controller;

use app\api\model\user\User as UserModel;
use app\api\model\App as AppModel;
use app\common\exception\BaseException;
use app\common\library\easywechat\AppMp;
use app\common\model\user\User;
use app\shop\model\settings\Setting as SettingModel;
use app\KmdController;
use app\api\model\plus\agent\Apply as AgentApplyModel;
use think\facade\Env;

/**
 * API控制器基类
 */
class Controller extends KmdController
{

    // app_id
    protected $app_id;

    /**
     * 后台初始化
     */
    public function initialize()
    {
        // 当前小程序id
        $this->app_id = $this->getAppId();
        // 验证当前小程序状态
        $this->checkWxapp();
    }

    /**
     * 获取当前应用ID
     */
    private function getAppId()
    {
        if (!$app_id = $this->request->param('app_id')) {
            throw new BaseException(['msg' => '缺少必要的参数：app_id']);
        }
        return $app_id;
    }

    /**
     * 验证当前小程序状态
     */
    private function checkWxapp()
    {
        $app = AppModel::detail($this->app_id);
        if (empty($app)) {
            throw new BaseException(['msg' => '当前应用信息不存在']);
        }
        if ($app['is_recycle'] || $app['is_delete']) {
            throw new BaseException(['msg' => '当前应用已删除']);
        }
    }

    /**
     * 获取当前用户信息
     */
    protected function getUser($is_force = true)
    {
        if (!$token = $this->request->param('token')) {
            if ($is_force) {
                throw new BaseException(['msg' => '缺少必要的参数：token', 'code' => -1]);
            }
            return false;
        }
        if (!$user = UserModel::getUser($token)) {
            if ($is_force) {
                throw new BaseException(['msg' => '没有找到用户信息', 'code' => -1]);
            }
            return false;
        }
        //判断当前用户是否被禁用
        if ($user['is_forbidden']==1) {
            $msg = '该用户已被禁用，请联系管理员解封';
            $values = SettingModel::getItem('store');
            if (isset($values['msg']) && !empty($values['msg'])) {
                $msg = $values['msg'];
            }
            throw new BaseException(['msg' => $msg, 'code' => 0]);
            return false;
        }
        //无条件成为成为分销
        $model = new AgentApplyModel;
        $model->becomeAgentUserWithoutCondition($user['user_id'],$this->app_id);
        
        return $user;
    }

    protected function getShareParams($url, $title = '', $desc = '', $link = '', $imgUrl = '')
    {
        $signPackage = '';
        $shareParams = '';
        if (Env::get('APP_DEBUG')) {
            return [
                'signPackage' => $signPackage,
                'shareParams' => $shareParams
            ];
        }
        if ($url != '') {
            $app = AppMp::getApp($this->app_id);
            $app->jssdk->setUrl($url);
            $signPackage = $app->jssdk->buildConfig(array('updateAppMessageShareData', 'updateTimelineShareData'), false);
            $shareParams = [
                'title' => $title,
                'desc' => $desc,
                'link' => $link,
                'imgUrl' => $imgUrl,
            ];
        }
        return [
            'signPackage' => $signPackage,
            'shareParams' => $shareParams
        ];
    }

    /* 
    * 是否开启绑定上级
    */
    protected function isOpenBindReferee($user)
    {   
        if(empty($user)){
            return false;
        }

        $values = SettingModel::getItem('memberSetting');
        if(isset($values['is_open_bindReferee'])&&!empty($values['is_open_bindReferee'])){
            $user_model = new User();
            $referee_id = $user_model->getUserReferee($user['user_id']);
            if($referee_id<=0){
                
                throw new BaseException(['msg' => '该用户尚无绑定上级', 'code' => -10]);
                return false;
            }
           
        }
    }
    
}

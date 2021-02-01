<?php

namespace app\api\controller\plus\icp;

use app\api\controller\Controller;
use app\common\library\icp\IcpQurey;
use app\shop\model\settings\Setting as SettingModel;
use think\Exception;

/**
 * ICP备案查询控制器
 */
class Icp extends Controller
{

    /**
     * 获取icp接口配置
     * @param int|null $appId 如果没有登录没有appid,手动传入进来
     * @return mixed
     */
    public function fetchData()
    {
        $key = 'icpApi';
        $vars['values'] = SettingModel::getItem($key);
        return $vars;
    }

    /**
     * 查询
     */
    public function query()
    {
        try {
            $data = $this->postData();

            if (!$data['website']) return $this->renderError('请提供域名');
            if (!isset($data['config']['api'][$data['config']['default']]['private_key']) || !isset($data['config']['default'])) throw new Exception('参数错误');
            $config = $data['config'];

//            $config = $this->fetchData()['values'];
//            if (!$config) return $this->renderError('参数错误');

            $icpQurey = new IcpQurey($config);
            $res = $icpQurey->qureyICP($data['website']);
            // $res = json_decode($res, true);
            return $this->renderSuccess('', compact('res'));
        } catch (Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 查询2,根据系统配置的密钥查询接口,无需前端传递参数
     */
    public function query2()
    {
        try {
            $data = $this->postData();

            if (!$data['website']) return $this->renderError('请提供域名');

            $webSite = $data['website'];

            $config = $this->fetchData()['values'];
            if (!$config) return $this->renderError('参数错误');

            $aizhan = new IcpQurey($config);

            $res = $aizhan->qureyICP($webSite);

            // $res = json_decode($res, true);
            return $this->renderSuccess('', compact('res'));
        } catch (Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }

    public function setApi(){
        try {
            $data = $this->postData();

            if (!$data['private_key']) return $this->renderError('请提供密钥');

            $private_key = $data['private_key'];

            $jsonArr = [
                'default' => 'aizhan',
                'api' => [
                    'aizhan' => [
                        'baseUrl' => 'https://apistore.aizhan.com/site/icpinfos/',
                        'private_key' => $private_key
                    ]
                ]
            ];
            $model = new SettingModel;
            if ($model->edit('icpApi', $jsonArr)) {
                return $this->renderSuccess('操作成功');
            }
            return $this->renderError($model->getError() ?: '操作失败');
        } catch (Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }

}
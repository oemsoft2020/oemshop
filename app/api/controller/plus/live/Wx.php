<?php

namespace app\api\controller\plus\live;

use app\api\controller\Controller;
use app\api\model\plus\live\WxLive as WxLiveModel;
use app\common\model\plus\live\AnchorNotice as AnchorNoticeMod;
use app\shop\model\plus\anchor\Anchor as AnchorModel;
use app\common\model\plus\live\AnchorCoin as AnchorCoinModel;

/**
 * 微信直播控制器
 */
class Wx extends Controller
{
    /**
     * 微信直播列表
     */
    public function lists()
    {
        $model = new WxLiveModel();
        $list = $model->getList($this->postData());
        return $this->renderSuccess('', compact('list'));
    }

    /**
     * 提交直播预告审核数据
     * @Author   linpf
     * @DataTime 2020-11-09T11:47:18+0800
     * @return   [type]                   [description]
     */
    public function postLiveInfo()
    {
    	$params = $this->postData();

    	$notice_mod = new AnchorNoticeMod();

    	$msg = $notice_mod->addLiveData($params);
    	
    	return $msg['status'] ? $this->renderSuccess($msg['msg']) : $this->renderError($msg['msg']);
    }

    /**
     * 直播预告列表
     * @Author   linpf
     * @DataTime 2020-11-10T10:20:17+0800
     * @return   [type]                   [description]
     */
    public function liveList()
    {
    	$params = $this->postData();

    	$notice_mod = new AnchorNoticeMod();

    	$data = $notice_mod->getLiveData($params);

    	return $this->renderSuccess('', compact('data'));
    }

    /**
     * 直播预告详情
     * @Author   linpf
     * @DataTime 2020-11-10T15:42:30+0800
     * @return   [type]                   [description]
     */
    public function liveInfo($id = '',$user_id = '')
    {
    	$notice_mod = new AnchorNoticeMod();

    	$info = $notice_mod->getLiveInfo($id,$user_id);

    	return $this->renderSuccess('', compact('info'));
    }

    /**
     * 检查当前用户是否是主播
     * @Author   linpf
     * @DataTime 2020-11-10T17:32:29+0800
     * @param    string                   $user_id [description]
     * @return   [type]                            [description]
     */
    public function checkAnchor($user_id = '')
    {
    	$anchor_mod = new AnchorModel();

    	$info = $anchor_mod->where('user_id',$user_id)->find();

    	return !empty($info) ? $this->renderSuccess('', compact('info')) :  $this->renderError('该用户不是主播');
    }

    /**
     * 提交打卡任务
     * @Author   linpf
     * @DataTime 2020-11-11T17:34:59+0800
     * @return   [type]                   [description]
     */
    public function postAnchorCoin()
    {
    	$coin_mod = new AnchorCoinModel();

    	$params = $this->postData();

    	$res = $coin_mod->addLiveCoinData($params);

    	return $res['status'] ? $this->renderSuccess($res['msg']) : $this->renderError($res['msg']);
    }

}
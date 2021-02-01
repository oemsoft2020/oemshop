<?php

namespace app\api\controller\plus\friends;

use app\api\controller\Controller;
use app\api\model\plus\friends\Friends as FriendsModel;
use app\api\model\settings\Setting;

/**
 * 好友圈控制器
 */
class Friends extends Controller
{
    /**
     * 微信好友列表
     */
    public function lists()
    {
        $model = new FriendsModel();
        $list = $model->getList();
        $vars = Setting::getItem('friend');
        $user = $this->getUser();
        return $this->renderSuccess('', compact('list','vars','user'));
    }

    /**
     * 获取好友设置
     * @param $friends_id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function detail($friends_id)
    {
        $detail = FriendsModel::detail($friends_id);
        $vars = Setting::getItem('friend');
        return $this->renderSuccess('', compact('vars','detail'));
    }

    /**
     * 获取好友圈配置
     * @return \think\response\Json
     */
    public function vars()
    {
        $vars = Setting::getItem('friend');
        return $this->renderSuccess('', compact('vars'));
    }

    /**
     * 添加好友
     * @return \think\response\Json
     * @throws \app\common\exception\BaseException
     */
    public function addFriend()
    {
        $user = $this->getUser();
        $postData = $this->postData();
        $model = new FriendsModel();
        $res = $model->addFriend($user['user_id'],$postData);
        if ($res) {
            return $this->renderSuccess('添加成功');
        }
        return $this->renderError('添加失败');
    }

    /**
     * 修改好友
     * @return \think\response\Json
     * @throws \app\common\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function editFriend()
    {
        $user = $this->getUser();
        $postData = $this->postData();
        $model = new FriendsModel();
        $res = $model->editFriend($user['user_id'],$postData);
        if ($res) {
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError('修改失败');
    }

    /**
     * 删除好友
     * @param $friends_id
     * @return \think\response\Json
     * @throws \Exception
     */
    public function delete($friends_id)
    {
        $model = new FriendsModel();
        $res = $model->deleteFriend($friends_id);
        if ($res) {
            return $this->renderSuccess('删除成功');
        }
        return $this->renderError('删除失败');
    }

    /**
     * 交换生日
     * @return \think\response\Json
     * @throws \app\common\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function fillInFriend()
    {
        $user = $this->getUser();//自己的用户id
        $postData = $this->postData();

        $model = new FriendsModel();
        $res = $model->fillInFriend($user['user_id'],$postData);
        if ($res) {
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError('修改失败');
    }

}
<?php

namespace app\api\controller\user;

use app\api\controller\Controller;
use app\api\model\user\UserNews;

/**
 * 消息控制器
 */
class News extends Controller
{
    /**
     * 消息列表
     */
    public function lists()
    {
        $user = $this->getUser();
        $model = new UserNews();
        $list = $model->getList($user['user_id'], $this->postData());
        return $this->renderSuccess('', [
            'list' => $list
        ]);
    }
}
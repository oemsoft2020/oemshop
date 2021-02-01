<?php

namespace app\api\controller\plus\certification;

use app\api\controller\Controller;
use app\api\model\plus\certification\Apply as ApplyModel;

/**
 * 分销商申请
 */
class Apply extends Controller
{
    // 当前用户
    private $user;

    /**
     * 构造方法
     */
    public function initialize()
    {
        $this->user = $this->getUser();   // 用户信息
    }

    /**
     * 提交分销商申请
     */
    public function submit()
    {
        $data = $this->postData();
        $model = new ApplyModel;
        if ($model->submit($this->user, $data)) {
            return $this->renderSuccess('成功');
        }
        return $this->renderError($model->getError() ?: '提交失败');
    }

    public function getDetail()
    {
        $detail = ApplyModel::detail([
            'user_id' => $this->user['user_id']
        ]);
        return $this->renderSuccess('', compact('detail'));
    }

}
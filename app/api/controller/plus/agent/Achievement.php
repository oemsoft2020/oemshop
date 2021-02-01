<?php

namespace app\api\controller\plus\agent;

use app\api\controller\Controller;
use app\api\model\plus\agent\Setting;
use app\api\model\plus\agent\User as AgentUserModel;
use app\api\model\plus\agent\Cash as CashModel;
use app\common\model\user\UserAchievement;

/**
 * 分销商业绩
 */
class Achievement extends Controller
{
    private $user;
    /**
     * 构造方法
     */
    public function initialize()
    {
        // 用户信息
        $this->user = $this->getUser();
    }

    /**
     * 分销商提现明细
     */
    public function lists($status = -1)
    {

        $model = new UserAchievement();
        $list =  $model->getlist($this->user['user_id'], (int)$status);
        return $this->renderSuccess('', [
            // 提现业绩列表
            'list' => $list,
        ]);
    }

}
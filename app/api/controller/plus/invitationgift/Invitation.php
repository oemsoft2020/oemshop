<?php

namespace app\api\controller\plus\invitationgift;


use app\api\controller\Controller;
use app\api\model\plus\invitationgift\Invitation as InvitationModel;
use app\common\service\qrcode\ExtensionService;
use app\api\model\plus\invitationgift\Partake;

/**
 * 邀请有礼控制器
 */
class Invitation extends Controller
{
    /* @var \app\api\model\User $user */
    private $user;

    /**
     * 构造方法
     */
    public function initialize()
    {
        parent::initialize();
        $this->user = $this->getUser();   // 用户信息
    }

    /**
     * 获取数据
     */
    public function getDatas()
    {
        $model = new InvitationModel();
        $data = $model->getDatas($this->user['user_id']);
        return $this->renderSuccess('', compact('data'));
    }


    /**
     * 获取推广二维码
     */
    public function qrcode($id)
    {
        $Qrcode = new ExtensionService($id, 'invitation');
        return $this->renderSuccess('', [
            'qrcode' => $Qrcode->getImage(),
        ]);
    }

    /**
     * 参与记录
     */
    public function partakelist($id)
    {
        $model = new Partake();
        $list = $model->getList($id);
        return $this->renderSuccess('', compact('list'));
    }
}
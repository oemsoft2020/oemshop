<?php

namespace app\api\model\plus\agent;

use app\common\model\plus\agent\User as UserModel;
use app\common\model\user\User as ShopUserModel;

/**
 * 分销商用户模型
 */
class User extends UserModel
{
    /**
     * 隐藏字段
     */
    protected $hidden = [
        'create_time',
        'update_time',
    ];

    /**
     * 资金冻结
     */
    public function freezeMoney($money,$user_id)
    {
        $model = new ShopUserModel();
        $model = $model->where('user_id','=',$user_id)->find();
        return $model->save([
            'money' => $model['money'] - $money,
            'freeze_money' => $model['freeze_money'] + $money,
        ]);
    }

    /**
     * 累计分销商成员数量
     */
    public static function setMemberInc($agent_id, $level)
    {
        $fields = [1 => 'first_num', 2 => 'second_num', 3 => 'third_num'];
        $model = static::detail($agent_id);
        return $model->where('user_id', '=', $agent_id)->inc($fields[$level])->update();
    }

}
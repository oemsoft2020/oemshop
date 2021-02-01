<?php

namespace app\common\model\plus\friends;

use app\common\model\BaseModel;

class Friends extends BaseModel
{
    protected $name = 'user_friends';
    protected $pk = 'friends_id';

    /**
     * 好友圈详情
     * @param $friends_id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function detail($friends_id)
    {
        return self::find($friends_id);
    }
}
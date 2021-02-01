<?php

namespace app\shop\model\plus\friends;

use app\common\model\plus\friends\Friends as FriendsModel;

/**
 * 好友圈模型
 */
class Friends extends FriendsModel
{
    /**
     * 获取好友圈列表
     * @param $data
     * @return \think\Paginator
     * @throws \think\db\exception\DbException
     */
    public function getList($data)
    {
        $list = $this->order(['create_time' => 'asc'])
            ->paginate($data, false, [
                'query' => request()->request()
            ]);
        return $list;
    }

    /**
     * 新增好友圈
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        $data['app_id'] = self::$app_id;
        $data['birthday_time'] = strtotime($data['birthday_time']);
        return $this->save($data);
    }

    /**
     * 编辑好友圈
     * @param $data
     * @return bool
     */
    public function edit($data)
    {
        $data['birthday_time'] = strtotime($data['birthday_time']);
        return $this->save($data);
    }

    /**
     * 删除好友圈
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public function friendsDelete($id)
    {
        return $this->where('friends_id', $id)->delete();
    }
}
<?php

namespace app\api\model\plus\friends;

use app\api\model\user\User;
use app\common\model\plus\friends\Friends as FriendsModel;

/**
 * 好友圈模型
 */
class Friends extends FriendsModel
{
    /**
     * 隐藏字段
     */
    protected $hidden = [
        'app_id',
    ];

    /**
     * 获取好友圈列表
     * @param $params
     * @return \think\Paginator
     * @throws \think\db\exception\DbException
     */
    public function getList()
    {
        $list = $this->where('birthday_time', '>', 0)
            ->order(['create_time' => 'desc'])
            ->select()->toArray();
        $keys = [];
        foreach ($list as $k=>$v) {
            $list[$k]['day'] = $this->getBirthdayTime(date('m-d', $v['birthday_time']));
            $keys[]=$list[$k]['day'];
        }
        array_multisort($keys,SORT_ASC,$list);
        return $list;
    }

    /**
     * 添加好友
     * @param $user_id
     * @param $params
     * @return bool
     */
    public function addFriend($user_id, $params)
    {
        $data = [
            'user_id' => $user_id,
            'name' => $params['name'],
            'birthday_time' => strtotime($params['date']),
            'remark' => isset($params['remark']) ? $params['remark'] : null,
            'app_id' => self::$app_id
        ];
        return $this->save($data);
    }

    /**
     * 获取生日剩余多少天
     * @param $birthday
     * @return float|int
     */
    function getBirthdayTime($birthday)
    {
        $birthday = strtotime(date('Y') . '-' . $birthday);
        $nowDay = strtotime(date('Y-m-d'));
        $birthday = $birthday < $nowDay ? strtotime('+1 years', $birthday) : $birthday;
        $day = ($birthday - $nowDay) / 3600 / 24;
        return $day;
    }

    /**
     * 删除好友生日
     * @param $friends_id
     * @return bool
     * @throws \Exception
     */
    public function deleteFriend($friends_id)
    {
        return $this->where('friends_id',$friends_id)->delete();
    }

    /**
     * 修改好友生日
     * @param $user_id
     * @param $params
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function editFriend($user_id, $params)
    {
        $birthday_time = strtotime($params['date']);
        $remark = $params['remark'];
        $name = $params['name'];
        $friends_id = $params['friends_id'];
        $detail = parent::detail($friends_id);
        if ($detail['my_user_id'] == $user_id) {
            $userData = [
                'name' => $name,
                'birthday' => $birthday_time
            ];
            $userModel = new User();
            $userModel->where('user_id', $user_id)->update($userData);
        }
        $friendData = [
            'name' => $name,
            'birthday_time' => $birthday_time,
            'remark' => $remark,
        ];
        $res = $this->where('friends_id', $friends_id)->update($friendData);
        if (!$res) {
            return false;
        }
        return true;
    }

    /**
     * 交换生日卡
     * @param $user_id
     * @param $params
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function fillInFriend($user_id, $params)
    {
        $associated_user_id = $params['associated_user_id'];//邀请人用户id
        $birthday_time = strtotime($params['date']);
        $userModel = new User();
        $userInfo = $userModel::detail($user_id);
        $associatedUserInfo = $userModel::detail($associated_user_id);
        $friendDetail = $this->where('my_user_id', $user_id)->where('user_id', $user_id)->find();
        //添加自己的
        if ($friendDetail) { //有就修改
            $myData = [
                'birthday_time' => $birthday_time
            ];
            $this->where('my_user_id', $user_id)->update($myData);
        } else { //没有就新增
            $myData = [
                'name' => $userInfo['nickName'],
                'birthday_time' => $birthday_time,
                'user_id' => $user_id,
                'my_user_id' => $user_id,
                'associated_user_id' => $associated_user_id,
                'app_id' => self::$app_id,
                'remark' => '这是朋友向你公开的生日哦'
            ];
            $this->save($myData);
        }

        //给对方填自己的
        $toFriend = $this->where('user_id', $associated_user_id)->where('associated_user_id', $user_id)->find();
        if (!$toFriend) {
            $toData = [
                'name' => $userInfo['nickName'],
                'birthday_time' => $birthday_time,
                'user_id' => $associated_user_id,
                'associated_user_id' => $user_id,
                'app_id' => self::$app_id,
                'remark' => '这是朋友向你公开的生日哦'
            ];
            $this->save($toData);
        }

        //添加对方的
        $toFriendDetail = $this->where('associated_user_id', $associated_user_id)->where('user_id', $user_id)->find();
        if (!$toFriendDetail) {
            $toUserData = [
                'name' => $associatedUserInfo['name'] ? $associatedUserInfo['name'] : $associatedUserInfo['nickName'],
                'birthday_time' => $associatedUserInfo['birthday'],
                'associated_user_id' => $associated_user_id,
                'user_id' => $user_id,
                'app_id' => self::$app_id,
                'remark' => '这是朋友向你公开的生日哦'
            ];
            $this->save($toUserData);
        }
        return true;
    }
}
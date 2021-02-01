<?php

namespace app\api\model\user;

use app\common\model\user\UserNews as UserNewsModel;

/**
 * 用户消息模型
 */
class UserNews extends UserNewsModel
{
    /**
     * 隐藏字段
     */
    protected $hidden = [
        'app_id',
        'update_time'
    ];
    protected $dateFormat = 'Y年m月d日 H:i:s';


    /**
     * 获取消息列表
     */
    public function getList($user_id, $limit)
    {
        // 获取列表数据
        $list = $this->where('user_id', '=', $user_id)
            ->order(['create_time' => 'desc'])
            ->paginate($limit, false, [
                'query' => \request()->request()
            ]);
        $this->setNewsRead($user_id);
        return $list;
    }

    /**
     * 添加用户消息
     * @param $data
     * @param $user_id
     * @return bool
     */
    public static function add($data, $user_id)
    {
        $model = new self;
        return $model->save([
            'title' => $data['title'],
            'content' => $data['content'],
            'user_id' => $user_id,
            'is_read' => 0,
            'app_id' => self::$app_id
        ]);
    }

    /**
     * 设置用户消息为已读
     * @param $user_id
     * @return UserNews
     */
    public function setNewsRead($user_id)
    {
        return $this->where('user_id', $user_id)->update(['is_read' => 1]);
    }
}
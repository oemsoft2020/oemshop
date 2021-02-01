<?php

namespace app\common\model\plus\storage;

use app\common\model\BaseModel;


/**
 * Class Partake
 * 仓库记录模型
 * @package app\common\model\plus\storage
 */
class StorageLog extends BaseModel
{
    protected $pk = 'storage_log_id';

    /**
     * 云仓
     * @return \think\model\relation\BelongsTo
     */
    public function storage()
    {
        return $this->BelongsTo('app\common\model\plus\storage\Storage', 'storage_id', 'storage_id');
    }

    /**
     * 用户
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->BelongsTo('app\common\model\user\User', 'user_id', 'user_id');
    }


    public function getList($user_id, $param)
    {
        $model = $this;
        $model = $model->with(['storage', 'storage.image', 'user'])
            ->alias('log')
            ->join('user source_user', 'source_user.user_id=log.source_user')
            ->where('log.user_id', '=', $user_id)
            ->where('log.status', '=', 1);
        if ($param['type_active'] == 0) {
            $model = $model->where(function ($query) {
                $query->where('log.operation', '=', '转赠')->whereOr('log.operation', '=', '接收');
            });
        } elseif ($param['type_active'] == 1) {
            $model = $model->where('log.operation', '=', '转赠');
        } elseif ($param['type_active'] == 2) {
            $model = $model->where('log.operation', '=', '接收');
        }
        return $model->order(['log.create_time' => 'desc'])
            ->field('log.*,source_user.name as source_name,source_user.nickName')
            ->paginate($param, false, [
                'query' => \request()->request()
            ])->each(function ($item) {
                if (!$item['source_name']) {
                    $item['source_name'] = $item['nickName'];
                    return $item;
                }
            });

    }

    public function addLog($storage_id, $operation, $user_id, $number, $status, $source_user, $app_id, $remark = '', $mobile = '', $name = '', $giving_time = '')
    {
        if ($giving_time) {
            $giving_time = strtotime($giving_time);
        }
        $data = [
            'storage_id' => $storage_id,
            'operation' => $operation,
            'user_id' => $user_id,
            'number' => $number,
            'status' => $status,
            'remark' => $remark,
            'source_user' => $source_user,
            'app_id' => $app_id,
            'create_time' => time(),
            'mobile' => $mobile,
            'name' => $name,
            'giving_time' => $giving_time
        ];
        return $this->insert($data);
    }
}
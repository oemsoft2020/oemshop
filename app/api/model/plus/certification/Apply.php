<?php

namespace app\api\model\plus\certification;

use app\common\model\plus\certification\Apply as ApplyModel;

/**
 * 分销商申请模型
 */
class Apply extends ApplyModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'create_time',
        'update_time',
    ];

    /**
     * 是否实名认证
     */
    public static function isCertify($user_id)
    {
        $detail = self::detail(['user_id' => $user_id]);
        return $detail ? ((int)$detail['apply_status']['value'] === 20) : false;
    }

    /**
     * 提交申请
     */
    public function submit($user, $data)
    {
        // 数据整理
        $data = [
            'user_id' => $user['user_id'],
            'name' => trim($data['name']),
            'certificate_no' => trim($data['certificate_no']),
            'certify_type' => $data['certify_type'],
            'front_idcard' => $data['front_idcard'],
            'back_idcard' => $data['back_idcard'],
            'business_license' => $data['business_license'],
            'apply_time' => time(),
            'app_id' => self::$app_id,
        ];
        return $this->add($user, $data);
    }

    /**
     * 更新分销商申请信息
     */
    private function add($user, $data)
    {
        // 实例化模型
        $model = self::detail(['user_id' => $user['user_id']]) ?: $this;
        // 更新记录
        $this->startTrans();
        try {
            // 保存申请信息
            $model->save($data);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

}

<?php

namespace app\common\model\plus\certificate;

use app\common\model\BaseModel;

/**
 * 授权证书模型
 */
class Certificate extends BaseModel
{
    protected $name = 'certificate';
    protected $pk = 'certificate_id';

    /**
     * 申请状态
     * @var array
     */
    public $applyStatus = [
        0 => '待审核',
        1 => '审核通过',
        2 => '驳回',
    ];

    /**
     * 申请时间
     * @param $value
     * @return false|string
     */
    public function getApplyTimeAttr($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

    /**
     * 审核时间
     * @param $value
     * @return false|int|string
     */
    public function getAuditTimeAttr($value)
    {
        return $value > 0 ? date('Y-m-d H:i:s', $value) : 0;
    }

    /**
     * 销商申请记录详情
     * @param $where
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function detail($where)
    {
        $filter = is_array($where) ? $where : ['certificate_apply_id' => $where];
        return static::where($filter)->find();
    }

    /**
     * 审核状态
     * @param $value
     * @return array
     */
    public function getApplyStatusAttr($value)
    {
        $method = [10 => '待审核', 20 => '审核通过', '30' => '驳回'];
        return ['text' => $method[$value], 'value' => $value];
    }
    /**
     * 审核状态
     * @param $value
     * @return array
     */
    public function getCertifyTypeAttr($value)
    {
        $method = [10 => '个人认证', 20 => '企业认证'];
        return ['text' => $method[$value], 'value' => $value];
    }

    /**
     * 审核方式
     * @param $value
     * @return array
     */
    public function getApplyTypeAttr($value)
    {
        $method = [10 => '后台审核', 20 => '无需审核'];
        return ['text' => $method[$value], 'value' => $value];
    }

}
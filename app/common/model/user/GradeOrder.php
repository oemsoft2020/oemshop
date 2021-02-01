<?php

namespace app\common\model\user;

use app\common\model\BaseModel;
use app\common\service\order\OrderService;

/**
 * Class Order
 * 会员升级订单模型
 * @package app\common\model\user
 */
class GradeOrder extends BaseModel
{
    protected $pk = 'order_id';
    protected $append = [
        'state_text',
        'order_text',
        'pay_text',
    ];
    /**
     * @return $this
     * 关联用户表
     */
    public function user()
    {
        return $this->belongsTo("app\\common\\model\\user\\User", 'user_id', 'user_id')->field('user_id,nickName,avatarUrl,mobile');
    }
    /**
     * @return $this
     * 关联用户等级表
     */
    public function grade()
    {
        return $this->belongsTo("app\\common\\model\\user\\Grade", 'grade_id', 'grade_id')->field('grade_id,name');
    }
    /**
     * @return $this
     * 关联用户等级表
     */
    public function oldgrade()
    {
        return $this->belongsTo("app\\common\\model\\user\\Grade", 'old_grade_id', 'grade_id')->field('grade_id,name');
    }
    public function getStateTextAttr($value, $data)
    {
        // 订单状态
        if (in_array($data['pay_status'], [10, 20])) {
            $orderStatus = [10 => '未付款', 20 => '已付款'];
            return $orderStatus[$data['pay_status']];
        }
        return $value;
    } 
    public function getOrderTextAttr($value, $data)
    {
        // 订单状态
        if (in_array($data['order_status'], [10, 20,30])) {
            $orderStatus = [10 => '待审核', 20 => '拒绝', 30 => '已通过'];
            return $orderStatus[$data['order_status']];
        }
        return $value;
    } 
    public function getPayTextAttr($value, $data)
    {
        // 订单状态
        if (in_array($data['pay_type'], [10, 20])) {
            $orderStatus = [10 => '余额支付', 20 => '微信支付'];
            return $orderStatus[$data['pay_type']];
        }
        return $value;
    }
     public static function detail($where)
    {
        $filter = is_array($where) ? $where : ['order_id' => $where];
        return static::where($filter)->find();
    }
    /**
     * 生成订单号
     */
    public function orderNo()
    {
        return OrderService::createOrderNo();
    }
}
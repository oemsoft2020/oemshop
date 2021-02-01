<?php

namespace app\common\model\plus\deposit;

use app\common\model\BaseModel;
use app\common\service\order\OrderService;

/**

 *  * 供应商申请订单模型
 * @package app\common\model\plus\deposit
 */
class Record extends BaseModel
{
    protected $pk = 'order_id';
    protected $name = 'deposit_order';
    protected $append = [
        'state_text',
        'pay_text',
    ];
    /**
     * @return $this
     * 关联用户表
     */
    public function user()
    {
        return $this->belongsTo("app\\common\\model\\user\\User", 'user_id', 'user_id')->field('user_id,nickName,avatarUrl');
    }
    /**
     * @return $this
     * 关联用户等级表
     */
    public function grade()
    {
        return $this->belongsTo("app\\common\\model\\user\\Grade", 'grade_id', 'grade_id')->field('grade_id,name');
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
    public function getPayTextAttr($value, $data)
    {
        // 订单状态
        if (in_array($data['pay_type'], [10, 20])) {
            $orderStatus = [10 => '余额支付', 20 => '微信支付'];
            return $orderStatus[$data['pay_type']];
        }
        return $value;
    }
    /**
     * 生成订单号
     */
    public function orderNo()
    {
        return OrderService::createOrderNo();
    }
}
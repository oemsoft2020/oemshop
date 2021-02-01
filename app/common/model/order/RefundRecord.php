<?php

namespace app\common\model\order;


use app\common\model\BaseModel;

Class RefundRecord extends BaseModel{

    protected $pk = 'id';
    protected $name = 'refund_record';

    //新增微信退款的订单号的记录
    public function addRecord($transaction_id){
        $time = time();
        $data = [
            'transaction_id'=>$transaction_id,
            'refund_number'=>$time,
            'create_time'=>$time,
            'app_id'=>self::$app_id,
        ];
        //新增
        $result = $this->insert($data);
        $refund_number = $this->where('transaction_id',$transaction_id)->field('refund_number')->find();
        //返回
        return $refund_number;
    }
}
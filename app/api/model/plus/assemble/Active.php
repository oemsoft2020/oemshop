<?php

namespace app\api\model\plus\assemble;

use app\common\model\plus\assemble\Active as ActiveModel;
use app\common\enum\order\OrderSourceEnum;
use app\shop\model\order\Order as OrderModel;
use app\common\model\plus\assemble\Bill as BillModel;

/**
 * 拼团模型
 */
class Active extends ActiveModel
{
    /**
     * 参与记录列表
     */
    public function getList($param)
    {
        $model = $this;
        if (isset($param['status']) && $param['status'] > -1) {
            $model = $model->where('status', '=', $param['status']);
        }
        if (isset($param['title']) && !empty($param['title'])) {
            $model = $model->where('title', 'like', '%' . trim($param['title']) . '%');
        }
        $res = $model->with(['file'])
            ->order('create_time', 'desc')
            ->paginate($param, false, [
                'query' => request()->request()
            ]);

        foreach ($res as $key => $val) {
            $res[$key]['start_time'] = format_time($val['start_time']);
            $res[$key]['end_time'] = format_time($val['end_time']);
        }
        return $res;
    }

    /**
     * 取最近要结束的一条记录
     */
    public static function getActive()
    {
        return self::where('start_time', '<', time())
            ->where('end_time', '>', time())
            ->where('status', '=', 1)
            ->where('is_delete', '=', 0)
            ->order(['sort' => 'asc', 'end_time' => 'asc'])
            ->find();
    }

    /**
     * 获取拼团商品列表
     */
    public function activityList()
    {
        return  $this->where('start_time', '<=', time())
            ->where('end_time', '>=', time())
            ->where('status', '=', 1)
            ->where('is_delete', '=', 0)
            ->order(['sort' => 'asc', 'create_time' => 'asc'])
            ->select();
    }
    public function del()
    {
        //如果有正在拼团的商品
        $count = (new BillModel())->where('status', '=', 10)
            ->where('assemble_activity_id', '=', $this['assemble_activity_id'])
            ->count();
        if($count > 0){
            $this->error = '该活动下有正在拼团的订单';
            return false;
        }
        // 如果有未付款订单不能删除
        $count = (new OrderModel())->where('pay_status', '=', 10)
            ->where('order_source', '=', OrderSourceEnum::ASSEMBLE)
            ->where('is_delete', '=', 0)
            ->count();
        if($count > 0){
            $this->error = '该活动下有未付款的订单';
            return false;
        }
        $p = new Product();
        $p->where('assemble_activity_id', '=', $this['assemble_activity_id'])
          ->save([
            'is_delete' => 1
        ]);
        return $this->save([
            'is_delete' => 1
        ]);
    }

}

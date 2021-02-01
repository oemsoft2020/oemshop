<?php

namespace app\shop\model\plus\bargain;

use app\common\enum\order\OrderSourceEnum;
use app\common\model\plus\bargain\Task as TaskModel;
use app\common\model\plus\bargain\Active as ActiveModel;
use app\shop\model\order\Order as OrderModel;
use app\shop\model\plus\bargain\Product as BargainProductModel;
use app\shop\model\plus\bargain\BargainSku as BargainSkuModel;

/**
 * 砍价模型
 */
class Active extends ActiveModel
{
    /**
     *列表
     */
    public function getList($param)
    {
        $model = $this;
        if (!empty(self::$supply_id)) {
            $model = $model->where('supply_id', '=', self::$supply_id);
        }
        $list = $model->with(['file'])
            ->order('sort', 'desc')
            ->where('is_delete', '=', 0)
            ->paginate($param, false, [
                'query' => request()->request()
            ]);
        foreach ($list as $active) {
            //商品数
            $product_model = new BargainProductModel();
            $active['product_num'] = $product_model->where('bargain_activity_id', '=', $active['bargain_activity_id'])->count();
            //订单数
            $active['total_sales'] = $product_model->where('bargain_activity_id', '=', $active['bargain_activity_id'])->sum('total_sales');
        }
        return $list;
    }

    /**
     * 添加
     * @param $data
     */
    public function add($data)
    {
        $this->startTrans();
        try {
            $arr = $this->setData($data);
            $this->save($arr);
            //添加商品
            $product_model = new BargainProductModel();
            $product_model->add($this['bargain_activity_id'], $data['product_list']);
            // 事务提交
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 修改
     */
    public function edit($data)
    {
        $this->startTrans();
        try {
            $arr = $this->setData($data);
            $this->save($arr);
            //添加商品
            $product_model = new BargainProductModel();
            $product_model->edit($this['bargain_activity_id'], $data['product_list']);
            //删除商品
            if(isset($data['product_del_ids']) && count($data['product_del_ids']) > 0){
                $product_model->where('bargain_product_id', 'in', $data['product_del_ids'])->delete();
                (new BargainSkuModel)->where('bargain_product_id', 'in', $data['product_del_ids'])->delete();
            }
            // 事务提交
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     *删除
     */
    public function del()
    {
        //如果有正在拼团的商品
        $count = (new TaskModel())->whereRaw('(status = 0) OR (status = 1 and is_buy = 0)')
            ->where('bargain_activity_id', '=', $this['bargain_activity_id'])
            ->where('is_delete', '=', 0)
            ->count();
        if($count > 0){
            $this->error = '该活动下有正在砍价的订单';
            return false;
        }
        // 如果有未付款订单不能删除
        $count = (new OrderModel())->where('pay_status', '=', 10)
            ->where('order_source', '=', OrderSourceEnum::BARGAIN)
            ->where('is_delete', '=', 0)
            ->count();
        if($count > 0){
            $this->error = '该活动下有未付款的订单';
            return false;
        }
        return $this->save([
            'is_delete' => 1
        ]);
    }



    /**
     * 修改信息
     * @param $data
     */
    public function editBargain($param)
    {
        $data = array(
            'name' => $param['name'],
            'start_time' => $param['start_time']['value'],
            'end_time' => $param['end_time']['value'],
            'image_id' => $param['image_id'],
            'conditions' => $param['conditions'],
            'status' => $param['status']['value'],
            'sort' => $param['sort'],
        );
        $this->where('bargain_id', '=', $param['bargain_id'])->save($data);
        return true;
    }


    /**
     * 验证并组装数据
     * @param $data array  添加/新增数据
     * @return array
     */
    private function setData($data)
    {
        $data['active_time'][0] = substr($data['active_time'][0],0, 16);
        $data['active_time'][1] = substr($data['active_time'][1],0, 16);
        $arr = [
            'image_id' => $data['image_id'],
            'title' => $data['title'],
            'start_time' => strtotime($data['active_time'][0] . ':00'),
            'end_time' => strtotime($data['active_time'][1]. ':59'),
            'conditions' => $data['conditions'],
            'together_time' => $data['together_time'],
            'status' => $data['status'],
            'sort' => $data['sort'],
            'app_id' => self::$app_id,
        ];
        return $arr;
    }
}

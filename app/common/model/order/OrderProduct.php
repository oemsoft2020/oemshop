<?php

namespace app\common\model\order;

use app\common\model\BaseModel;
use app\common\model\order\Order as OrderMod;
use app\common\model\order\OrderRefund as returnMod;
use app\common\model\file\UploadFile as imgMod;
use app\common\model\product\ProductSku as skuMod;
use app\common\model\product\SpecValue as specMod;
use app\common\model\product\Product as goodsMod;

/**
 * 订单商品模型
 */
class OrderProduct extends BaseModel
{
    protected $name = 'order_product';
    protected $pk = 'order_product_id';

    /**
     * 订单商品列表
     * @return \think\model\relation\BelongsTo
     */
    public function image()
    {
        return $this->belongsTo('app\\common\\model\\file\\UploadFile', 'image_id', 'file_id');
    }

    /**
     * 关联商品表
     * @return \think\model\relation\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo('app\\common\\model\\product\\Product');
    }

    /**
     * 关联商品sku表
     * @return \think\model\relation\BelongsTo
     */
    public function sku()
    {
        return $this->belongsTo('app\\common\\model\\product\\ProductSku', 'spec_sku_id', 'spec_sku_id');
    }

    /**
     * 关联订单主表
     * @return \think\model\relation\BelongsTo
     */
    public function orderM()
    {
        return $this->belongsTo('Order','order_id','order_id');
    }

    /**
     * 售后单记录表
     * @return \think\model\relation\HasOne
     */
    public function refund()
    {
        return $this->hasOne('app\\common\\model\\order\\OrderRefund');
    }

    /**
     * 关联分销商
     * @return \think\model\relation\BelongsTo
     */
    public function agent()
    {
        return $this->belongsTo('app\\common\\model\\agent\\Apply', 'agent_user_id', 'user_id');
    }

    /**
     * 订单商品详情
     * @param $where
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function detail($where)
    {
        return static::with(['image', 'refund', 'orderM'])->find($where);
    }

    /**
     * 获取主播购买的商品列表
     * @Author   linpf
     * @DataTime 2020-11-04T16:58:08+0800
     * @param    string                   $user_id [description]
     * @return   [type]                            [description]
     */
    public function getAnchorGoods($user_id = '',$param)
    {
        if(empty($user_id)){
            return false;
        }

        // 获取符合条件的订单
        $order_mod = new OrderMod();
        $img_mod = new imgMod();
        $sku_mod = new skuMod();
        $spec_mod = new specMod();
        $goods_mod = new goodsMod();

         // 商品列表获取条件
        $params = array_merge([
            'list_rows' => 15,       // 每页数量
        ], $param);

        // 筛选查询字段
        $field = [
            'product_name',
            'image_id',
            'product_id',
            'product_price',
            'line_price',
            'product_sku_id',
            'product_attr'
        ];

        $model = $this;

        // 获取退款和退货的订单id
        $return_mod = new returnMod();
        $where['user_id'] = $user_id;
        $where['status'] = 20;

        $order_arr = $return_mod->where($where)->column('order_id');

        $map['pay_status'] = 20; 
        $map['delivery_status'] = 20; 
        $map['receipt_status'] = 20; 
        $map['order_status'] = 30; 
        $map['user_id'] = $user_id;

        $order_obj = $order_mod->where($map);

        if(!empty($order_arr)){
            $order_obj = $order_obj->whereNotIn('order_id',$order_arr);
        }

        $order_ids = $order_obj->column('order_id');
        $goods_arr = $this->whereIn('order_id',$order_ids)->column('order_product_id','product_id');
        
        if(!empty($goods_arr)){
            $data = $this->field($field)->whereIn('order_product_id',$goods_arr)->select()->each(function($item,$key)use($img_mod,$sku_mod,$spec_mod,$goods_mod){
                // 查询图片
                $item->img_url = '';
                if(!empty($item['image_id'])){
                    $img_data = $img_mod->where('file_id',$item['image_id'])->find();
                    if($img_data){
                        $item->img_url = $img_data['file_url'].'/'.$img_data['file_name'];
                    }
                }

                // 统计销量
                if(!empty($item['product_id'])){
                    $item->sales_total = $goods_mod->field(['sales_initial','sales_actual'])->where('product_id',$item['product_id'])->find()->toArray()['product_sales'];
                   
                }else{
                    $item->sales_total = 0;
                }

            })->toArray();

        }else{
            $data = [];
        }
      
        return $data;
    }
}
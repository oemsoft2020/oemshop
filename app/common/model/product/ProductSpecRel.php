<?php

namespace app\common\model\product;
use app\common\model\product\Spec as SpecModel;
use app\common\model\product\SpecValue as SpecValueModel;
use app\common\model\product\ProductSku as ProductSkuModel;
use app\common\model\BaseModel;
/**
 * 商品规格关系模型
 */
class ProductSpecRel extends BaseModel
{
    protected $name = 'product_spec_rel';
    protected $pk = 'id';
    protected $updateTime = false;

    /**
     * 关联规格组
     */
    public function spec()
    {
        return $this->belongsTo('Spec');
    }

    /**
     * 获取商品规格信息
     * @Author   linpf
     * @DataTime 2020-11-02T10:55:23+0800
     * @param    string                   $goods_id  [商品id]
     * @param    string                   $spec_name [规格分类名称]
     * @param    string                   $val_name  [规格名称]
     * @return   [type]                              [description]
     */
    public function findSpecInfo($goods_id = '',$spec_name = '',$val_name = '')
    {
        if(empty($goods_id) || empty($spec_name) || empty($val_name)){
            return [];
        }

        $spec_mod = new SpecModel();
        $spec_val_mod = new SpecValueModel();
        $sku_mod = new ProductSkuModel();

        // 判断商品规格是否存在
        $spec_val_info = $spec_val_mod->where('spec_value',$val_name)->column('spec_value_id');
        $spec_info = $spec_mod->where('spec_name',$spec_name)->column('spec_id');

        if($spec_val_info && $spec_info){
            
            $map['product_id'] = $goods_id; 
            $goods_spec_id = $this->where($map)->whereIn('spec_id',$spec_info)->whereIn('spec_value_id',$spec_val_info)->value('spec_value_id');
            
            if($goods_spec_id){
                $sku_map['product_id'] = $goods_id; 
                $sku_map['spec_sku_id'] = $goods_spec_id;
                
                return $sku_mod->where($sku_map)->find();
            }else{
                return [];
            }

        }
       
        return [];
    }
}

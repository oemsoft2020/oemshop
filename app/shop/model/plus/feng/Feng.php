<?php

namespace app\shop\model\plus\feng;
use app\common\model\BaseModel;
use app\common\model\settings\Region as RegionModel;
use think\facade\Db;

/*
 *
 * 封坛模型
 *
 * */
Class Feng extends BaseModel{

    protected $name = 'code_product_fengtan';
    protected $pk = 'fengtan_id';


    public function getList($params)
    {

         $list=  $this->where('is_delete', '=', 0)
            ->order([ 'fengtan_id'=>'desc'])
            ->paginate($params, false, [
                'query' => request()->request()
            ]);


        $this->changeType($list);
        return $this->changeAddress($list);




    }

    //转换地址
    public function changeAddress($list){

        if(empty($list)){
            return;
        }


        $model = new RegionModel();

        foreach ($list as $k=>$v){

            $a = db::table('kmdshop_region')->where('id',$v['province_id'])->field('name')->find();

            $a1 = db::table('kmdshop_region')->where('id',$v['city_id'])->field('name')->find();
            $a2 = db::table('kmdshop_region')->where('id',$v['region_id'])->field('name')->find();

            $list[$k]['address']=$a['name'] .$a1['name'].$a2['name'].$v['detail'];

            $list[$k]['start_time'] = date('Y-m-d h:i:s',$v['start_time']);
            if($v['end_time'] ==0){
                $list[$k]['end_time'] ='暂未封坛';
            }else{
                $list[$k]['end_time'] =date('Y-m-d h:i:s',$v['end_time']);
            }


        }

        return $list;

    }


    //变换中文状态
    public function changeType($list){

        foreach ($list as $k => $v){

            if($v['type'] == 1 ){
                $list[$k]['type'] = '申请中';
            }
            if($v['type'] == 2 ){
                $list[$k]['type'] = '已发货';
            }
            if($v['type'] == 3 ){
                $list[$k]['type'] = '回寄中';
            }
            if($v['type'] == 4 ){
                $list[$k]['type'] = '封已坛';
            }
//            if($v['type'] == 5 ){
//                $list[$k]['type'] = '已封坛';
//            }

        }
        return $list;
    }


    /**
     * 关联物流公司表
     */
    public function express()
    {
        return $this->belongsTo('app\\api\\model\\settings\\Express');
    }

    /**
     * 关联商品图片表
     */
    public function image()
    {
        return $this->hasMany('app\\common\\model\\feng\\FengTanImage')->order(['id' => 'asc']);
    }

//    /**
//     * 关联商品图片表
//     */
//    public function image()
//    {
//        return $this->hasMany('app\\common\\model\\feng\\FengTanImage')->order(['id' => 'asc']);
//    }

}

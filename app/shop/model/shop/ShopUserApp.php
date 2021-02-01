<?php

namespace app\shop\model\shop;


use app\common\model\shop\ShopUserApp as ShopUserAppModel;
/**
 * 模型
 */
class ShopUserApp extends ShopUserAppModel
{
    public function getList($data,$limit=15)
    {   
        $model = $this;
        if(isset($data['type'])&&isset($data['data_id'])){
            $where = [
                'type'=>$data['type'],
                'data_id'=>$data['data_id']
            ];
            $model =  $model->where($where);
        }
        $condition = [];
        //检索：用户名
        if (isset($data['user_name'])&& !empty($data['user_name'])) {

            $condition = [
                ['user_name|real_name', 'like','%' . trim($data['user_name']) . '%']
            ];


        }

        return $model->alias('sua')->join('shop_user user','user.shop_user_id=sua.shop_user_id')
            ->with(['user.userRole.role'])->where('is_deleted', '=', 0)
            ->where($condition)->
        order(['sua.create_time' => 'desc'])
            ->paginate($limit, false, [
                'query' => \request()->request()
            ]);
    }

    public function del($where)
    {
        $data=self::update(['is_deleted' => 1], $where);
        if($data){
            return 1;
        }else{
            return false;
        }

    }
    //
    public function detail($where)
    {
        $model = $this;
        !is_array($where) && $where = ['shop_user_id' => (int)$where];
        $model =  $model->where($where)->find();
        return $model;


    }

}
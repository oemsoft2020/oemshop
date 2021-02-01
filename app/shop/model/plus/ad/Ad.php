<?php

namespace app\shop\model\plus\ad;

use app\shop\controller\Controller;
use app\common\model\plus\ad\Ad as AdModel;
/**
 * 广告位
 */
class Ad extends AdModel
{
    /**
     * @param $id
     * 广告位列表
     * @return \think\Collection
     */
    public function getList($param)
    {
        $model = $this;
        if (!empty(self::$supply_id)) {
            $model = $model->where('supply_id', '=', self::$supply_id);
        }
        $list = $model->with(['file','supply'])
            ->where('is_delete', '=', 0)
            ->order('create_time', 'desc')
            ->paginate($param, false, [
                'query' => request()->request()
            ]);

        return $list;
    }


    public function add($data)
    {  
        $res =  $this->isImageExists($data);
        if(!empty($res)){
            return false;
        }
        $this->startTrans();
        try {
            $arr = $this->setData($data);
            $this->save($arr);
           $this->commit();
            return true;
           
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    public function edit($data)
    {
        $res =  $this->isImageExists($data);
        if(!empty($res)){
            return false;
        }
        $this->startTrans();
        try {
            $arr = $this->setData($data);
            $this->save($arr);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 删除
     */
    public function del()
    {
        return $this->save([
            'is_delete' => 1
        ]);
    }

    /**
     * 验证并组装数据
     * @param $data array  添加/新增数据
     * @param $type  string 类型
     * @return array
     */
    private function setData($data)
    {
        $arr = [
            'image_id' => $data['image_id'],
            'name' => $data['name'],
            'status' => $data['status'],
            'image_url' =>$data['file_path'],
            'position' => $data['position'],
            'sort' => $data['sort'],
            'app_id' => self::$app_id,
            'supply_id' => self::$supply_id,
        ];

        return $arr;
    }

    /* 
    *　检查数据是否存在
    */

    public function isImageExists($data)
    {
        if(isset($data['position'])){
            $where = [
                'position'=>$data['position'],
                'is_delete'=>0,
                'status'=>1,
                'supply_id' => self::$supply_id,
            ];
            $count =  $this->where($where)->count();
            if($count>1){
                $this->error ="该位置已有广告图片了";
                return true;
            }
        }
        return false;
    }
}
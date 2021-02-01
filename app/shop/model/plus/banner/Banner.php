<?php

namespace app\shop\model\plus\banner;

use app\shop\controller\Controller;
use app\common\model\plus\banner\Banner as BannerModel;
/**
 * 供应商
 */
class Banner extends BannerModel
{
    /**
     * @param $id
     * 供应商列表
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
        $count = (new BannerModel)->where('supply_id','=',self::$supply_id)->count();
        if(self::$supply_id>0&&$count>=10){
            $this->error = '已超出最多10个轮播图的限额，不能继续添加';
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
            'product_id' => $data['product_id'],
            'image_url' =>$data['file_path'],
            'sort' => $data['sort'],
            'app_id' => self::$app_id,
            'supply_id' => self::$supply_id,
        ];

        return $arr;
    }
}
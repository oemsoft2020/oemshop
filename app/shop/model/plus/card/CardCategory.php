<?php

namespace app\shop\model\plus\card;

use app\common\model\plus\card\CardCategory as CardCategoryModel;
use think\facade\Db;

/**
 * 名片列表
 */
class CardCategory extends CardCategoryModel
{
    public function getList($param)
    {
        $model = $this;
    
        $list = $model->order('create_time', 'desc')
            ->paginate($param, false, [
                'query' => \request()->request()
            ]);  
        return $list;

    }

    public function add($data)
    {
        $this->startTrans();
        try {
            $arr  =[];
            $arr = $this->setData($data);
            $this->save($arr);
            // 事务提交
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
            // 事务提交
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /* 
    * 整理数据
    */

    public function setData($data)
    {
        $data['app_id'] = self::$app_id;
        return $data;

    }

    /**
     * 活动删除
     */
    public function del()
    {

        return $this->save([
            'is_delete' => 1
        ]);
    }

}
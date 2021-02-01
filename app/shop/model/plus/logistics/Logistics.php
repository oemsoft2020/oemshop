<?php

namespace app\shop\model\plus\logistics;

use app\shop\model\user\User as UserModel;
use app\common\model\plus\logistics\Logistics as LogisticsModel;
use app\common\exception\BaseException;
use app\shop\model\settings\Setting as SettingModel;
use app\shop\model\settings\Region;
/**
 * 模型模型
 */
class Logistics extends LogisticsModel
{
    /**
     * @param $data array 查询条件
     * @return mixed
     */
    public function getList($data)
    {
        $model = $this;
        if(isset($data['logistics_id_array'])){
            $model = $model->where([
                'logistics_id'=>['in',implode(',',$data['logistics_id_array'])]
            ]);
        }
        return $model->order(['create_time' => 'desc'])
            ->paginate($data, false, [
                'query' => request()->request()
            ]);
    }

    /**
     * @param $data array 查询条件
     * @return mixed
     */
    public function getAllList($data)
    {
        $model = $this;
        if(isset($data['logistics_id'])){
            $model = $model->where([
                'logistics_id'=>['in',$data['logistics_id']]
            ]);
        }
        return $model->order(['create_time' => 'desc'])
            ->select();
    }

    /**
     * 添加新记录
     */
    public function add($data)
    {
        $data['app_id'] = self::$app_id;
        
        $data['region'] = join(',', array_values($data['rule'][0]['citys']));
        unset($data['rule']);       
        return self::create($data);
    }

    /**
     * 编辑记录
     */
    public function edit($data)
    {
        $data['app_id'] = self::$app_id;
        
        $data['region'] = join(',', array_values($data['rule'][0]['citys']));
        unset($data['rule']);       
        return $this->save($data);
    }

    /**
     * 删除记录
     */
    public function remove()
    {
        return $this->delete();
    }

    public function getFormList()
    {
        $regions = Region::getCacheAll();
        $list = [];
        
        $citys = explode(',', $this['region']);
        $province = [];
        foreach ($citys as $cityId) {
            if (!isset($regions[$cityId])) continue;
            !in_array($regions[$cityId]['pid'], $province) && $province[] = $regions[$cityId]['pid'];
        }
        $list[] = [
            'province' => $province,
            'citys' => $citys,
        ];
        
        return $list;
    }

}

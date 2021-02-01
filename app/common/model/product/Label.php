<?php

namespace app\common\model\product;

use think\facade\Cache;
use app\common\model\BaseModel;

/**
 * 产品标签模型
 */
class Label extends BaseModel
{
    protected $pk = 'kmd_label_id';
    protected $name = 'kmd_label';


    public function initialize()
    {   
        parent::initialize();
        $session = session('kmdshop_store');
        $this->supply_id = $session['supply_id'];
    }
    /**
     * 标签详情
     */
    public static function detail($kmd_label_id)
    {
        return self::find($kmd_label_id);
    }
    /**
     * 获取标签列表
     */
    public function getList($param)
    {
        // 标签列表获取条件
        $params =$param;
        // 筛选条件
        $model = $this;
        if (!empty($params['name'])) {
            $model = $model->where('name', 'like', '%' . trim($params['name']) . '%');
        }
        if (!empty($params['search'])) {
            $model = $model->where('name', 'like', '%' . trim($params['search']) . '%');
        }
        if(isset($params['type'])&&!empty($params['type'])){
            $model = $model->where('type', $params['type']);
        }
        if($this->supply_id){
            $model = $model->where('supply_id', $this->supply_id);
        }

        $list = $model
            ->where('is_delete', '=', 0)
            ->paginate($params, false, [
                'query' => \request()->request(),
            ]);
        // 整理列表数据并返回
        return $list;
    }


    /**
     * 所有标签
     */
    public static function getALL($type)
    {
        $model = new static;
        if (!empty($type)) {
            $model =  $model->where('type','=',$type);
        }
        if (!Cache::get('kmd_label_' . self::$app_id)) {
            $data = $model->order(['sort' => 'asc', 'create_time' => 'asc'])->select();
            $all = !empty($data) ? $data->toArray() : [];
            Cache::tag('cache')->set('kmd_label_' . self::$app_id, compact('all'));
        }
        return Cache::get('kmd_label_' . self::$app_id);
    }

    /**
     * 获取所有标签
     */
    public static function getCacheAll($type)
    {
        return self::getALL($type)['all'];
    }
}

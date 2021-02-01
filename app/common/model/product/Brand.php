<?php

namespace app\common\model\product;

use think\facade\Cache;
use app\common\model\BaseModel;
use app\common\model\file\UploadFile as FileModel;

/**
 * 产品标签模型
 */
class Brand extends BaseModel
{
    protected $pk = 'brand_id';
    protected $name = 'brand';

    /**
     * 标签详情
     */
    public static function detail($brand_id)
    {
        return self::find($brand_id);
    }

    /**
     * 获取品牌列表
     */
    public function getList($param)
    {
        $file_mod = new FileModel;

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
        $list = $model
            ->where('is_delete', '=', 0)
            ->paginate($params, false, [
                'query' => \request()->request(),
            ]);

        foreach ($list as $key => $value) {
            if(!empty($value->image_id)){
                $res = $file_mod->where('file_id',$value->image_id)->find();
                $value->image_url = $res->file_url.'/'.$res->file_name;
            }else{
                $value->image_url = '';
            }
        }
        
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
        if (!Cache::get('kmd_brand_' . self::$app_id)) {
            $data = $model->order(['sort' => 'asc', 'create_time' => 'asc'])->select();
            $all = !empty($data) ? $data->toArray() : [];
            Cache::tag('cache')->set('kmd_brand_' . self::$app_id, compact('all'));
        }
        return Cache::get('kmd_brand_' . self::$app_id);
    }

    /**
     * 获取所有标签
     */
    public static function getCacheAll($type)
    {
        return self::getALL($type)['all'];
    }

    public function detailWithImage($where)
    {
        return $this->with(['image'])->where($where)->find();
    }

    /**
     * 关联图片
     */
    public function image()
    {
        return $this->belongsTo('app\common\model\file\UploadFile', 'image_id', 'file_id');
    }

    /**
     * 获取品牌列表
     */
    public static function getBrandCate($appId = null)
    {
        $model = new static;
        $appId = $appId ? $appId : $model::$app_id;
        return $model->where('is_delete', '=', '0')
            ->where('app_id', '=', $appId)
            ->order(['create_time' => 'asc'])
            ->select();
    }

    // 查询品牌信息
    public function findBrandInfo($name = '')
    {
        $brand_id = $this->where('name',$name)->value('brand_id');

        if(empty($brand_id)){
            return $this->insertGetId(['name'=>$name,'app_id'=>self::$app_id]);
        }

        return $brand_id;
    }
}

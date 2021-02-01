<?php

namespace app\api\model\plus\article;

use app\common\model\plus\article\Category as CategoryModel;

/**
 * 文章分类模型
 */
class Category extends CategoryModel
{
    /**
     * 隐藏字段
     */
    protected $hidden = [
        'app_id',
        'update_time'
    ];

    public function image()
    {
        return $this->hasOne('app\\common\\model\\file\\UploadFile', 'file_id', 'image_id');
    }

    public function getCategory($category_id)
    {
        return $this->where('parent_id',$category_id)->order(['sort' => 'asc', 'create_time' => 'asc'])->select();
    }


    /**
     * 文章分类带封面图片
     * 2020年11月16日
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAllWithImg($category_id = 0)
    {
        $model = $this;
        $category_id == 0 && $model = $model->where('parent_id', '=', 0);
        $category_id > 0 && $model = $model->where('category_id', '=', $category_id);
        $result = $model
            ->with(['image'])
            ->order(['sort' => 'asc', 'create_time' => 'asc'])
            ->select()
        ->toArray();
        if ($result&&$result[0]['parent_id'] == 0) {
            $result[0]['child'] = $this
                ->with(['image'])
                ->where('parent_id', '=', $result[0]['category_id'])
                ->order(['sort' => 'asc', 'create_time' => 'asc'])
                ->select();
        }
        return $result;


    }
}

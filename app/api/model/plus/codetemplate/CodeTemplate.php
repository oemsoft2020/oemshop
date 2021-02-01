<?php

namespace app\api\model\plus\codetemplate;

use app\common\model\plus\codetemplate\CodeTemplate as CodeTemplateModel;

/**
 * 任务模型
 */
class CodeTemplate extends CodeTemplateModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'is_delete',
        'app_id',
        'update_time'
    ];

    /**
     * 获取二维码模板列表
     * @param $params
     * @return \think\Paginator
     * @throws \think\db\exception\DbException
     */
    public function getList($params)
    {
        $model = $this;
        if ($params['type']) {
            $model = $model->where('type', '=', $params['type']);
        }
        return $model->with(['image'])
            ->where('is_delete', '=', 0)
            ->order(['create_time' => 'desc'])
            ->paginate($params, false, [
                'query' => \request()->request()
            ]);

    }

    public function getDefault()
    {
        return $this->with(['image'])->where('is_delete',0)->order('create_time')->find();
    }
}
<?php

namespace app\shop\model\plus\codetemplate;

use app\common\model\plus\codetemplate\CodeTemplate as CodeTemplateModel;

/**
 * 二维码模板模型
 */
class CodeTemplate extends CodeTemplateModel
{
    /**
     * 获取二维码模板列表
     * @param $params
     * @return \think\Paginator
     * @throws \think\db\exception\DbException
     */
    public function getList($params)
    {
        return $this->with(['image'])->where('is_delete', '=', 0)
            ->order('create_time','desc')
            ->paginate($params, false, [
                'query' => request()->request()
            ]);
    }

    /**
     * 新增记录
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        $data['app_id'] = self::$app_id;
        return $this->save($data);
    }

    /**
     * 更新记录
     * @param $data
     * @return bool
     */
    public function edit($data)
    {
        return $this->save($data);
    }

    /**
     * 软删除
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]);
    }
}
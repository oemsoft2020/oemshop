<?php

namespace app\shop\model\plus\anchor;

use app\common\model\plus\anchor\Anchor as AnchorModel;

/**
 * 主播模型
 */
class Anchor extends AnchorModel
{
    /**
     * 获取主播列表
     */
    public function getList($params)
    {   
        $model = $this;
        //检索：用户名|手机号
        if (!empty($params['search'])) {
            $model = $model->where('name|mobile', 'like', '%' . $params['search'] . '%');
        }
        
        return $model->where('is_delete', '=', 0)
            ->order(['create_time' => 'desc'])
            ->paginate($params, false, [
                'query' => request()->request()
            ]);

    }

    public  function detail($anchor_id)
    {
        return $this->find($anchor_id);
    }

}
<?php

namespace app\shop\model\plus\port;

use app\common\model\plus\port\Port as PortModel;
use think\facade\Cache;

/**
 * 应用模型
 */
class Port extends PortModel
{
   

    public function count($where)
    {
        return $this->where($where)->count();
    }

    public function getlist($data)
    {
        $model = $this;
       
        return $model->order(['create_time' => 'desc'])
            ->paginate($data, false, [
                'query' => \request()->request()
            ]);
    }


}

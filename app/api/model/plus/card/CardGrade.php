<?php

namespace app\api\model\plus\card;

use app\common\model\plus\card\CardGrade as CardGradeModel;
use app\api\model\plus\card\CardMessage as CardMessageModel;
use think\facade\Db;

class CardGrade extends CardGradeModel
{
 
    public function getList($param)
    {
        $list = $this->where('is_delete', '=', 0)
            ->order('create_time', 'desc')
            ->paginate($param, false, [
                'query' => request()->request()
            ]);
        return $list;

    }
    
}

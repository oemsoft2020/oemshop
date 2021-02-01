<?php

namespace app\shop\model\plus\supply;

use app\common\model\plus\supply\Grade as GradeModel;
use app\shop\model\plus\supply\Supply as SupplyModel;

/**
 * 用户会员等级模型
 */
class Grade extends GradeModel
{
    /**
     * 获取列表记录
     */
    public function getList($data)
    {
        return $this->where('is_delete', '=', 0)
            ->where('type', '=', 'supply')
            ->order(['create_time' => 'asc'])
            ->paginate($data, false, [
                'query' => request()->request()
            ]);
    }

    /**
     * 获取列表记录
     */
    public function getLists()
    {
        return $this->where('is_delete', '=', 0)
            ->where('type', '=', 'supply')
            ->field('kmd_grade_id,name')
            ->order(['create_time' => 'asc'])
            ->select();
    }


    /**
     * 新增记录
     */
    public function add($data)
    {
        $data['app_id'] = self::$app_id;
        $data['is_default'] = 0;
        $data['type'] = 'supply';
        $data['setting'] = $this->setData($data);
        return $this->save($data);
    }

    /**
     * 编辑记录
     */
    public function edit($data)
    {
        $data['setting'] = $this->setData($data);
        return $this->save($data);
    }

    private function setRemark($data){
        $remark = '';
       
        return $remark;
    }
     private function setData($data){
        $setting = json_encode($data['setting']);
        return $setting;
    }

    /**
     * 软删除
     */
    public function setDelete()
    {
        // 判断该等级下是否存在供应商
        if (SupplyModel::checkExistByGradeId($this['kmd_grade_id'])) {
            return false;
        }
        return $this->save(['is_delete' => 1]);
    }

}
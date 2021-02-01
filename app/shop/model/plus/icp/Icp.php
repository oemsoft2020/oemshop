<?php

namespace app\shop\model\plus\icp;

use app\common\model\plus\icp\Icp as IcpModel;
use app\shop\model\settings\Setting as SettingModel;

/**
 * 优惠券模型
 */
class Icp extends IcpModel
{
    /**
     * 获取记录列表
     */
    public function getList($data)
    {
        $model = $this;
        if (!empty($data['query_string'])) {
            $model = $model->where('query_string', 'like', '%' . trim($data['query_string']) . '%');
        }
        if (!empty($data['timeInterval'])){
            $startTime = strtotime($data['timeInterval'][0]);
            $endTime = strtotime($data['timeInterval'][1]);
            $model = $model->where('create_time', '>=', $startTime)
                ->where('create_time', '<=', $endTime);
        }
        return $model->where('is_deleted', '=', 0)
            ->order(['create_time' => 'desc'])
            ->paginate($data, false, [
                'query' => request()->request()
            ]);
    }

    /**
     * 获取icp接口配置
     * @param int|null $appId
     * @return mixed
     */
    public function getIcpSetting(int $appId = null)
    {
        $key = 'icpApi';
        $vars['values'] = SettingModel::getItem($key, $appId);
        return $vars;
    }

    /**
     * 添加新记录
     */
    public function add($data)
    {
        $data['app_id'] = self::$app_id;
        return self::create($data);
    }

}

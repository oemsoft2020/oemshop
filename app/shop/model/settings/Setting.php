<?php

namespace app\shop\model\settings;

use think\facade\Cache;
use app\common\model\settings\Setting as SettingModel;
use app\common\enum\settings\SettingEnum;

class Setting extends SettingModel
{
    /**
     * 更新系统设置
     */
    public function edit($key, $values)
    {
        $model = self::detail($key) ?: $this;
        // 删除系统设置缓存
        Cache::delete('setting_' . self::$app_id);
        return $model->save([
                'key' => $key,
                'describe' => SettingEnum::data()[$key]['describe'],
                'values' => $values,
                'app_id' => self::$app_id,
            ]) !== false;
    }

    /**
     * 数据验证
     */
    private function validValues($key, $values)
    {
        $callback = [
            'store' => function ($values) {
                return $this->validStore($values);
            },
            'printer' => function ($values) {
                return $this->validPrinter($values);
            },
        ];
        // 验证商城设置
        return isset($callback[$key]) ? $callback[$key]($values) : true;
    }

    /**
     * 验证商城设置
     */
    private function validStore($values)
    {
        if (!isset($values['delivery_type']) || empty($values['delivery_type'])) {
            $this->error = '配送方式至少选择一个';
            return false;
        }
        return true;
    }

    /**
     * 验证小票打印机设置
     */
    private function validPrinter($values)
    {
        if ($values['is_open'] == false) {
            return true;
        }
        if (!$values['printer_id']) {
            $this->error = '请选择订单打印机';
            return false;
        }
        if (empty($values['order_status'])) {
            $this->error = '请选择订单打印方式';
            return false;
        }
        return true;
    }


    /**
     * 添加配置
     * @Author   linpf
     * @DataTime 2020-10-28T14:33:28+0800
     * @param array $params [配置数据]
     */
    public function addSet($params = array())
    {
        if (empty($params)) {
            return false;
        }

        $addData['key'] = $params['key'];
        $addData['describe'] = $params['describe'];
        $addData['values'] = $params['values'];
        $addData['app_id'] = self::$app_id;
        $addData['update_time'] = time();

        return $this->insert($addData);

    }
        /**
         * 添加setting表应用设置
         */


        public  function addItem($data)
        {
            $data['app_id'] = self::$app_id;
            $data['update_time'] = time();
            $this->save($data);

        }


}

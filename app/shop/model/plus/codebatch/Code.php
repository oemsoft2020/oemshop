<?php

namespace app\shop\model\plus\codebatch;

use app\common\model\plus\codebatch\Code as CodeModel;

/**
 * 编码模型
 */
class Code extends CodeModel
{
    /**
     * 获取编码列表
     * @param $data
     * @return \think\Paginator
     * @throws \think\db\exception\DbException
     */
    public function getList($data)
    {
        $model = $this;
        if (!empty($data['search'])) {
            $model = $model->where('code', '=', $data['search']);
        }
        $list = $model->where('code_batch_id', '=', $data['code_batch_id'])
            ->paginate($data, false, [
                'query' => request()->request()
            ]);
        return $list;
    }

    /**
     * 添加编码
     * @param $code_batch_id
     * @param $rule
     * @param $rule_start
     * @param $rule_end
     * @param $zero_number
     * @param $prefix
     */
    public function saveCodeData($code_batch_id, $rule, $rule_start, $rule_end, $zero_number, $prefix)
    {
        $data = [];
        for ($i = $rule_start; $i <= $rule_end; $i++) {
            $end_code = null;
            if ($zero_number) {
                $end_code = str_pad($i, $zero_number, '0', STR_PAD_LEFT);
            }
            $rule == 'randomly' ? $code = rand($rule_start, $rule_end) : $code = $prefix . $end_code;
            $data[$i] = [
                'code_batch_id' => $code_batch_id,
                'code' => $code,
                'app_id' => self::$app_id,
                'end_code' => $end_code
            ];

        }
        $this->saveAll($data);
    }
}
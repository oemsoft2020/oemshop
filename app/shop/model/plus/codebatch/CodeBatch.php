<?php

namespace app\shop\model\plus\codebatch;

use app\common\model\plus\codebatch\CodeBatch as CodeBatchModel;

/**
 * 批次模型
 */
class CodeBatch extends CodeBatchModel
{
    /**
     * 获取批次列表
     * @param $data
     * @return \think\Paginator
     * @throws \think\db\exception\DbException
     */
    public function getList($data)
    {
        $model = $this;
        if (!empty($data['search'])) {
            $model = $model->where('title', 'like', '%' . $data['search'] . '%');
        }
        $list = $model->with(['product.image.file'])
            ->order(['code_batch_id' => 'desc'])
            ->paginate($data, false, [
                'query' => request()->request()
            ]);
        return $list;
    }

    /**
     * 获取排除id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getExcludeIds()
    {
        // 获取列表数据
        return $this->field(['product_id'])->select()->toArray();
    }

    /**
     * 添加编码批次
     * @param $data
     * @return bool
     */
    public function saveCodeBatch($data)
    {
        $model = $this;
        $is_batch = $data['is_batch'];
        $add_time = $data['add_time'];
        $rule = $data['rule'];
        $is_zeroize = $data['is_zeroize'];
        $zero_number = $data['zero_number'];
        $prefix = $data['prefix'];
        $rule_start = $data['rule_start'];
        $rule_end = $data['rule_end'];
        $title = $data['title'];
        $product_id = $data['product_id'];
        $model->startTrans();
        $codeModel = new Code();
        try {
            if ($is_batch == 'false') {
                $code_batch_id = $model->insertGetId([
                    'title' => $title,
                    'product_id' => $product_id,
                    'rule' => $rule,
                    'rule_start' => $rule_start,
                    'rule_end' => $rule_end,
                    'prefix' => $rule == 'birthday' ? $prefix = date('Ymd', time()) : $prefix,
                    'is_zeroize' => $is_zeroize,
                    'zero_number' => $zero_number,
                    'app_id' => self::$app_id,
                    'create_time' => time()
                ]);
                $codeModel->saveCodeData($code_batch_id, $rule, $rule_start, $rule_end, $zero_number, $prefix);
            } else {
                set_time_limit(0);
                $start_time = $add_time[0];
                $end_time = $add_time[1];
                $days = $this->getDatesBetweenTwoDays($start_time, $end_time);
                foreach ($days as $day) {
                    $code_batch_id = $model->insertGetId([
                        'title' => $title,
                        'product_id' => $product_id,
                        'rule' => $rule,
                        'rule_start' => $rule_start,
                        'rule_end' => $rule_end,
                        'prefix' => $rule == 'birthday' ? $prefix = date('Ymd', strtotime($day)) : $prefix,
                        'is_zeroize' => $is_zeroize,
                        'zero_number' => $zero_number,
                        'app_id' => self::$app_id,
                        'create_time' => strtotime($day)
                    ]);
                    $codeModel->saveCodeData($code_batch_id, $rule, $rule_start, $rule_end, $zero_number, $prefix);
                }
            }
            $model->commit();
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
        return true;
    }

    /**
     * 获取两个时间之间的日期
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public function getDatesBetweenTwoDays($startDate, $endDate)
    {
        $dates = [];
        if (strtotime($startDate) > strtotime($endDate)) {
            // 如果开始日期大于结束日期，直接return 防止下面的循环出现死循环
            return $dates;
        } elseif ($startDate == $endDate) {
            // 开始日期与结束日期是同一天时
            array_push($dates, $startDate);
            return $dates;
        } else {
            array_push($dates, $startDate);
            $currentDate = $startDate;
            do {
                $nextDate = date('Y-m-d', strtotime($currentDate . ' +1 days'));
                array_push($dates, $nextDate);
                $currentDate = $nextDate;
            } while ($endDate != $currentDate);

            return $dates;
        }
    }
}
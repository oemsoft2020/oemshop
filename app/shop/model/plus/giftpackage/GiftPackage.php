<?php

namespace app\shop\model\plus\giftpackage;

use app\common\model\plus\giftpackage\GiftPackage as GiftPackageModel;
use app\shop\model\plus\coupon\Coupon;

/**
 * Class GiftPackage
 * @package app\shop\model\plus\giftpackage
 * 礼包购模型
 */
class GiftPackage extends GiftPackageModel
{
    /**
     * 礼包列表
     * @param $data
     */
    public function getList($data)
    {
        $model = $this;
        //检索活动名称
        if ($data['search'] != '') {
            $model = $model->where('name', 'like', '%' . trim($data['search']) . '%');
        }
        $list = $model->where('is_delete', '=', 0)
            ->order('create_time', 'desc')
            ->paginate($data, false, [
                'query' => request()->request()
            ]);
        foreach ($list as $k => $val) {
            if ($val['start_time']['value'] > time()) {
                $list[$k]['text'] = '未开始';
            }
            if ($val['end_time']['value'] < time()) {
                $list[$k]['text'] = '已结束';
            }
            if ($val['start_time']['value'] < time() && $val['end_time']['value'] > time()) {
                $list[$k]['text'] = '进行中';
            }
        }
        return $list;
    }
    /**
     *获取为开始的数据列表
     */
    public function getDatas()
    {
        return $this->where('end_time', '<', time())->select();
    }
    /**
     * 获取数据
     * @param $id
     */
    public function getGiftPackage($id)
    {
        $data = $this->where('gift_package_id', '=', $id)->find();
        $data = $data->toArray();
        $data['value1'][] = $data['start_time']['text'];
        $data['value1'][] = $data['end_time']['text'];
        $data['grade_ids'] = explode(',', $data['grade_ids']);
        if ($data['is_coupon'] == '1') {
            $data['is_coupon'] = true;
        }
        if ($data['is_point'] == '1') {
            $data['is_point'] = true;
        }
        if ($data['coupon_ids'] != '') {
            $model = new Coupon();
            $coupon = $model->getCoupon($data['coupon_ids']);
            $data['coupon_list'] = $coupon->toArray();
            $data['coupon'] = explode(',', $data['coupon_ids']);
        }
        return $data;
    }

    /**
     * 保存数据
     * @param $data
     */
    public function saveGift($data)
    {
        if ($data['is_coupon'] == 'true') {
            $data['is_coupon'] = 1;
            $data['coupon_ids'] = implode(',', $data['coupon']);
        } else {
            $data['is_coupon'] = 0;
            $data['coupon'] = '';
        }
        if ($data['is_point'] == 'true') {
            $data['is_point'] = 1;
        } else {
            $data['is_point'] = 0;
        }
        if ($data['is_grade'] == 1) {
            $data['grade_ids'] = implode(',', $data['grade_ids']);
        } else {
            $data['grade_ids'] = '';
        }

        if ($data['is_times'] == 0) {
            $data['times'] = 0;
        }
        $data['start_time'] = strtotime(array_shift($data['value1']));
        $data['end_time'] = strtotime(array_pop($data['value1']));
        $data['app_id'] = 10001;
        return $this->save($data);
    }

    /**
     * 修改
     * @param $value
     */
    public function edit($data)
    {
        $data['grade_ids'] = implode(',', $data['grade_ids']);
        $data['start_time'] = strtotime(array_shift($data['value1']));
        $data['end_time'] = strtotime(array_pop($data['value1']));
        unset($data['value1']);
        unset($data['status']);
        unset($data['create_time']);

        $data['update_time'] = time();
        if ($data['is_point'] == 'true') {
            $data['is_point'] = '1';
        } else {
            $data['is_point'] = '0';
        }

        if ($data['is_coupon'] == 'true') {
            $data['is_coupon'] = '1';
            $data['coupon_ids'] = implode(',', $data['coupon']);
        } else {
            $data['is_coupon'] = '0';
            $data['coupon_ids'] = '';
        }
        unset($data['coupon_list']);
        unset($data['coupon']);
        return $this->where('gift_package_id', '=', $data['gift_package_id'])->update($data);
    }

    /**
     * 发布
     * @param $id
     */
    public function send($id)
    {
        return $this->where('gift_package_id', '=', $id)->update(['status' => 1]);
    }

    /**
     * 终止
     * @param $id
     */
    public function end($id)
    {
        return $this->where('gift_package_id', '=', $id)->update(['status' => 0]);
    }

    /**
     * 删除
     * @param $id
     */
    public function del($id)
    {
        return $this->where('gift_package_id', '=', $id)->update(['is_delete' => 1]);
    }
}
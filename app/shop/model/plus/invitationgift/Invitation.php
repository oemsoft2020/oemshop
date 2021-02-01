<?php

namespace app\shop\model\plus\invitationgift;

use app\common\model\plus\invitationgift\Invitation as InvitationModel;
use app\shop\model\plus\coupon\Coupon;
use app\common\exception\BaseException;

/**
 * Class Invitation
 * 邀请有礼模型
 * @package app\shop\model\plus\invitationgift
 */
class Invitation extends InvitationModel
{
    /**
     * 获取列表
     * @param $data
     */
    public function getList($data)
    {
        $model = $this;
        //检索活动名称
        if ($data['search'] != '') {
            $model = $model->where('name', 'like', '%' . trim($data['search']) . '%');
        }
        $list = $model->with(['Reward'])->where('is_delete', '=', 0)
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
     * @param $data
     * 保存
     * @return string
     */
    public function add($data)
    {
        $this->startTrans();
        try {

            $arr = [
                'name' => $data['name'],
                'start_time' => strtotime(array_shift($data['value1'])),
                'end_time' => strtotime(array_pop($data['value1'])),
                'inv_condition' => $data['inv_condition'],
                'app_id' => self::$app_id
            ];
            $this->save($arr);
            $arr1 = [];
            foreach ($data['reward_data'] as $val) {

                if ($val['is_coupon'] == 'true') {
                    $is_coupon = 1;
                    $coupon_ids = $val['coupon_ids'];
                } else {
                    $is_coupon = 0;
                    $coupon_ids = '';

                }
                if ($val['is_point'] == 'true') {
                    $is_point = 1;
                    $point = $val['point'];
                } else {
                    $is_point = 0;
                    $point = 0;
                }
                $arr1[] = [
                    'invitation_gift_id' => $this->invitation_gift_id,
                    'invitation_num' => $val['invitation_num'],
                    'is_point' => $is_point,
                    'point' => $point,
                    'is_coupon' => $is_coupon,
                    'coupon_ids' => $coupon_ids,
                    'app_id' => self::$app_id,
                    'coupon_name' => $val['coupon_name'],
                ];
            }
            $model = new InvitationReward();
            $model->saveAll($arr1);
            // 事务提交
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * @param $data
     * 修改
     */
    public function edit($data)
    {
        $this->startTrans();
        try {
            $arr = [
                'name' => $data['name'],
                'start_time' => strtotime(array_shift($data['value1'])),
                'end_time' => strtotime(array_pop($data['value1'])),
                'inv_condition' => $data['inv_condition'],
                'app_id' => self::$app_id
            ];

            $this->where('invitation_gift_id', '=', $data['invitation_gift_id'])->save($arr);
            $arr1 = [];
            foreach ($data['reward_data'] as $val) {

                if ($val['is_coupon'] == 'true') {
                    $is_coupon = 1;
                    $coupon_ids = $val['coupon_ids'];
                } else {
                    $is_coupon = 0;
                    $coupon_ids = '';

                }
                if ($val['is_point'] == 'true') {
                    $is_point = 1;
                    $point = $val['point'];
                } else {
                    $is_point = 0;
                    $point = 0;
                }
                $arr1[] = [
                    'invitation_reward_id' => $val['invitation_reward_id'],
                    'invitation_gift_id' => $data['invitation_gift_id'],
                    'invitation_num' => $val['invitation_num'],
                    'is_point' => $is_point,
                    'point' => $point,
                    'is_coupon' => $is_coupon,
                    'coupon_ids' => $coupon_ids,
                    'app_id' => self::$app_id
                ];
            }
            $model = new InvitationReward();
            $model->saveAll($arr1);
            // 事务提交
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * @param $id
     * 发布
     * @return string
     */
    public function send($id)
    {
        return $this->where('invitation_gift_id', '=', $id)->update(['status' => 1]);
    }

    /**
     * @param $id
     * 终止
     * @return static
     */
    public function end($id)
    {
        return $this->where('invitation_gift_id', '=', $id)->update(['status' => 0]);
    }

    /**
     * @param $id
     * 删除
     * @return string
     */
    public function del($id)
    {
        return $this->where('invitation_gift_id', '=', $id)->update(['is_delete' => 1]);
    }

    /**
     * 后去数据
     * @param $id
     */
    public function getDatas($id)
    {
        $data = $this->with(['Reward'])->where('invitation_gift_id', '=', $id)->find();
        if (empty($data)) {
            throw new BaseException(['msg' => '没有相关信息']);
            return false;
        }
        $data = $data->toArray();
        $data['value1'][] = $data['start_time']['text'];
        $data['value1'][] = $data['end_time']['text'];
        $data['inv_condition'] = $data['inv_condition'] . '';
        foreach ($data['Reward'] as $k => $val) {
            if ($val['is_point'] == 1) {
                $data['Reward'][$k]['is_point'] = true;
            } else {
                $data['Reward'][$k]['is_point'] = false;
            }
            if ($val['is_coupon'] == 1) {
                $data['Reward'][$k]['is_coupon'] = true;
            } else {
                $data['Reward'][$k]['is_coupon'] = false;
            }
            $model = new Coupon();
            $coupon = $model->getCoupons($val['coupon_ids']);
            $data['Reward'][$k]['coupon_name'] = $coupon;
        }
        return $data;
    }
}
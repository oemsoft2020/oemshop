<?php

namespace app\api\model\plus\coupon;

use app\common\library\helper;
use app\common\model\plus\coupon\UserCoupon as UserCouponModel;
use app\api\model\plus\coupon\Coupon as CouponModel;
use app\api\model\user\User as UserModel;

/**
 * 用户优惠券模型
 */
class UserCoupon extends UserCouponModel
{
    /**
     * 获取用户优惠券列表
     */
    public function getList($user_id, $is_use = false, $is_expire = false)
    {
        return $this->where('user_id', '=', $user_id)
            ->where('is_use', '=', $is_use ? 1 : 0)
            ->where('is_expire', '=', $is_expire ? 1 : 0)
            ->select();
    }

    /**
     * 获取用户优惠券总数量(可用)
     */
    public function getCount($user_id)
    {
        return $this->where('user_id', '=', $user_id)
            ->where('is_use', '=', 0)
            ->where('is_expire', '=', 0)
            ->count();
    }

    /**
     * 获取用户优惠券ID集
     */
    public function getUserCouponIds($user_id)
    {
        return $this->where('user_id', '=', $user_id)->column('coupon_id');
    }

    /**
     * 领取优惠券
     */
    public function receive($user, $coupon_id)
    {
        // 获取优惠券信息
        $coupon = Coupon::detail($coupon_id);
        // 验证优惠券是否可领取
        if (!$this->checkReceive($user, $coupon)) {
            return false;
        }
        // 添加领取记录
        return $this->add($user, $coupon);
    }
    /**
     * 批量领取优惠券
     */
    public function receiveList($user, $coupon_ids)
    {
        $coupon_arr = json_decode($coupon_ids, true);
        foreach($coupon_arr as $coupon_id){
            try{
                $this->receive($user, $coupon_id);
            }catch (\Exception $e){

            }
        }
        return true;
    }

    /**
     * 添加领取记录
     */
    private function add($user, Coupon $coupon)
    {
        // 计算有效期
        if ($coupon['expire_type'] == 10) {
            $start_time = time();
            $end_time = $start_time + ($coupon['expire_day'] * 86400);
        } else {
            $start_time = $coupon['start_time']['value'];
            $end_time = $coupon['end_time']['value'];
        }
        // 整理领取记录
        $data = [
            'coupon_id' => $coupon['coupon_id'],
            'name' => $coupon['name'],
            'color' => $coupon['color']['value'],
            'coupon_type' => $coupon['coupon_type']['value'],
            'reduce_price' => $coupon['reduce_price'],
            'discount' => $coupon->getData('discount'),
            'min_price' => $coupon['min_price'],
            'expire_type' => $coupon['expire_type'],
            'expire_day' => $coupon['expire_day'],
            'start_time' => $start_time,
            'end_time' => $end_time,
            'apply_range' => $coupon['apply_range'],
            'user_id' => $user['user_id'],
            'app_id' => self::$app_id
        ];
        return $this->transaction(function () use ($data, $coupon) {
            // 添加领取记录
            $status = $this->save($data);
            if ($status) {
                // 更新优惠券领取数量
                $coupon->setIncReceiveNum();
            }
            return $status;
        });
    }

    /**
     * 邀请有礼优惠券奖励
     * @param $coupon_ids
     * @param $user_id
     */
    public function addUserCoupon($coupon_ids, $user_id)
    {
        $model = new CouponModel();
        $list = $model->where('coupon_id', 'in', $coupon_ids)->select();
        foreach ($list as $coupon) {
            // 计算有效期
            if ($coupon['expire_type'] == 10) {
                $start_time = time();
                $end_time = $start_time + ($coupon['expire_day'] * 86400);
            } else {
                $start_time = $coupon['start_time']['value'];
                $end_time = $coupon['end_time']['value'];
            }
            // 整理领取记录
            $data[] = [
                'coupon_id' => $coupon['coupon_id'],
                'name' => $coupon['name'],
                'color' => $coupon['color']['value'],
                'coupon_type' => $coupon['coupon_type']['value'],
                'reduce_price' => $coupon['reduce_price'],
                'discount' => $coupon->getData('discount'),
                'min_price' => $coupon['min_price'],
                'expire_type' => $coupon['expire_type'],
                'expire_day' => $coupon['expire_day'],
                'start_time' => $start_time,
                'end_time' => $end_time,
                'apply_range' => $coupon['apply_range'],
                'user_id' => $user_id,
                'app_id' => self::$app_id
            ];

        }
        $this->saveAll($data);
        return true;
    }

    /**
     * 验证优惠券是否可领取
     */
    private function checkReceive($user, $coupon)
    {
        if (!$coupon) {
            $this->error = '优惠券不存在';
            return false;
        }
        if (!$coupon->checkReceive()) {
            $this->error = $coupon->getError();
            return false;
        }
        // 验证是否已领取
        $userCouponIds = $this->getUserCouponIds($user['user_id']);
        if (in_array($coupon['coupon_id'], $userCouponIds)) {
            $this->error = '该优惠券已领取';
            return false;
        }
        return true;
    }

    /**
     * 订单结算优惠券列表
     */
    public static function getUserCouponList($user_id, $orderPayPrice)
    {
        //     新增筛选条件: 最低消费金额
        // 获取用户可用的优惠券列表
        $list = (new self)->getList($user_id);
        $data = [];
        foreach ($list as $coupon) {
            // 最低消费金额
            if ($orderPayPrice < $coupon['min_price']) continue;
            // 有效期范围内
            if ($coupon['start_time']['value'] > time()) continue;
            $key = $coupon['user_coupon_id'];
            $data[$key] = [
                'user_coupon_id' => $coupon['user_coupon_id'],
                'name' => $coupon['name'],
                'color' => $coupon['color'],
                'coupon_type' => $coupon['coupon_type'],
                'reduce_price' => $coupon['reduce_price'],
                'discount' => $coupon['discount'],
                'min_price' => $coupon['min_price'],
                'expire_type' => $coupon['expire_type'],
                'start_time' => $coupon['start_time'],
                'end_time' => $coupon['end_time'],
                'expire_day' => $coupon['expire_day'],
            ];
            // 计算打折金额
            if ($coupon['coupon_type']['value'] == 20) {
                $reducePrice = helper::bcmul($orderPayPrice, $coupon['discount'] / 10);
                $data[$key]['reduced_price'] = bcsub($orderPayPrice, $reducePrice, 2);
            } else
                $data[$key]['reduced_price'] = $coupon['reduce_price'];
        }
        // 根据折扣金额排序并返回
        return array_sort($data, 'reduced_price', true);
    }

    /**
     * @param $user_id int 用户id
     * @param $days int 连续签到天数
     * @param $sign_conf array 签到配置
     * @return int
     */
    public function setCoupon($user_id, $days, $sign_conf)
    {
        $coupon_num = 0;
        $arr = array_column($sign_conf['reward_data'], 'day');
        if (in_array($days, $arr)) {
            $key = array_search($days, $arr);
            if ($sign_conf['reward_data'][$key]['is_coupon'] == 'true') {
                $coupon = $sign_conf['reward_data'][$key]['coupon'];
                $coupon_arr = array_column($coupon, 'coupon_id');
                $coupon_str = implode(',', $coupon_arr);
                $model = new CouponModel();
                $res = $model->getWhereData($coupon_str)->toArray();
                $res_arr = array_column($res, 'coupon_id');
                foreach ($coupon as $key => $val) {
                    $j = array_search($coupon[$key]['coupon_id'], $res_arr);
                    for ($i = 0; $i < $coupon[$key]['num']; $i++) {
                        $coupon_num = $coupon[$key]['num'];
                        $result[] = [
                            'coupon_id' => $res[$j]['coupon_id'],
                            'name' => $res[$j]['name'],
                            'color' => $res[$j]['color']['value'],
                            'coupon_type' => $res[$j]['coupon_type']['value'],
                            'reduce_price' => $res[$j]['reduce_price'],
                            'discount' => $res[$j]['discount'],
                            'min_price' => $res[$j]['min_price'],
                            'expire_type' => $res[$j]['expire_type'],
                            'expire_day' => $res[$j]['expire_day'],
                            'start_time' => $res[$j]['start_time']['value'],
                            'end_time' => $res[$j]['end_time']['value'],
                            'apply_range' => $res[$j]['apply_range'],
                            'user_id' => $user_id,
                            'app_id' => self::$app_id,
                        ];
                    }
                }
                self::saveAll($result);
            }
        }
        return $coupon_num;
    }

    /**
     * peng
     *  获取用户可操作优惠券
     */
    public function getCouponType($user_id){
        $ids = array_merge($this->getUserCouponIds($user_id));
        $coupon =  (new CouponModel())->getCouponCount($ids);
        foreach ($coupon as $k => $v){
            $coupon[$k]["user_true_sum"] = $this->where(["coupon_id" => $ids[$k],"user_id" => $user_id,"is_use" => 0,"is_expire" => 0])->count();
        }
        return $coupon;
    }

    /**
     * peng
     * 验证优惠券数量
     * @param $transfer_id  转让人id
     * @param $receive_id  接收人id
     * @param $couponID
     * @param $couponSum
     * @return bool
     */
    public function validationCoupon($transfer_id,$receive_id,$couponID,$couponSum){
        for ($i = 0;$i < count($couponID);$i++){
            $count = $this->where(["is_expire" => 0,"is_use" => 0,"coupon_id" => $couponID[$i],"user_id" => $transfer_id])->count();
            if($count < $couponSum[$i]){
                return false;
            }
            try{
                $this->where(["is_expire" => 0,"is_use" => 0,"coupon_id" => $couponID[$i],"user_id" => $transfer_id])->limit($couponSum[$i])->update(["user_id" => $receive_id]);
                $this->couponTransferLog($transfer_id,$receive_id,$couponID[$i],$couponSum[$i],2);
            }catch (\Exception $e){
                $this->couponTransferLog($transfer_id,$receive_id,$couponID[$i],$couponSum[$i],1,$e);
                return false;
            }
        }
        return true;
    }

    /**
     * peng
     *  增加优惠券转让记录
     */
    public function couponTransferLog($transfer_id,$receive_id,$coupon_type_id,$count,$state,$note='SUCCESS'){
        $add = [
            "uid" => $transfer_id,
            "receive_id" => $receive_id,
            "coupon_type_id" => $coupon_type_id,
            "count" => $count,
            "transfer_time" => time(),
            "transfer_state" => $state,
            "transfer_note" => $note
        ];
        $this->table('kmdshop_kmd_coupon_log')->insert($add);
    }
    /**
     * peng
     * 优惠券转让记录
     */
    public function getCouponList($user_id,$data_type){
        switch ($data_type) {
            case 'transfer':
                $condition['uid'] = $user_id;
                break;
            case 'receive':
                $condition['receive_id'] = $user_id;
                break;
            case 'all':
                $condition['receive_id|uid'] = $user_id;
                break;
        }
        $condition["transfer_state"] = 2;
        $log = \think\facade\Db::table('kmdshop_kmd_coupon_log')->where($condition)->order('transfer_time desc')->select()->toArray();
        $usermodel = new UserModel();
        foreach ($log as $k => $v){
            $log[$k]["coupon_name"] = (new CouponModel())->where('coupon_id',$v["coupon_type_id"])->value('name');
            $log[$k]["transfer_name"] = $usermodel->where('user_id',$v["uid"])->value('Nickname');
            $log[$k]["receive_name"] = $usermodel->where('user_id',$v["receive_id"])->value('Nickname');
            $user_id == $v["uid"]?$log[$k]["type"] = 1:$log[$k]["type"] = 0;
            $log[$k]["transfer_time"] = date('Y-m-d H:i:s',$v["transfer_time"]);
        }
        return $log;
    }

}
<?php

namespace app\shop\model\plus\coupon;

use app\common\exception\BaseException;
use app\shop\model\user\User;
use app\shop\model\plus\coupon\Coupon;
use app\common\model\plus\coupon\UserCoupon as UserCouponModel;

/**
 * 用户优惠券模型
 */
class UserCoupon extends UserCouponModel
{
    /**
     * 获取优惠券列表
     */
    public function getList($data,$limit = 20)
    {
        $condition = [];
        $param =[];
        $params =[];
        //检索：用户名
        if (isset($data['user_name'])&& !empty($data['user_name'])) {

            $condition = [
                ['user.nickName', 'like','%' . trim($data['user_name']) . '%']
            ];

        }
        //检索：优惠券名
        if (isset($data['name'])&& !empty($data['name'])) {

            $params = [
                ['coupon.name', 'like','%' . trim($data['name']) . '%']
            ];


        }
        //检索：优惠券使用情况
        if (isset($data['is_use'])) {
            if($data['is_use']> -1){
                $param=[

                   'coupon.is_use' => $data['is_use']
                ];
            }



        }
        return $this->alias('coupon')->join('user','user.user_id=coupon.user_id')->with(['user'])
            ->where($param)
            ->where($params)
            ->where($condition)
            ->order(['coupon.create_time' => 'desc'])
            ->paginate($limit, false, [
                'query' => request()->request()
            ]);
    }

    /**
     * 发送优惠券
     * @param int $send_type 1给所有会员 2给指定等级的用户 3给指定用户发送
     * @param int $coupon_id
     * @param int $user_level
     * @param string $user_ids
     */
    public function SendCoupon($data)
    {
        $send_type = $data['send_type'];
        $coupon_id = $data['coupon_id'];
        $user_level = $data['user_level'];
        $user_ids = $data['user_ids'];
        $num = $data["num"];

        // 发放数量验证
        if ($num < 1){
            throw new BaseException(['msg' => '发放数量不能小于1']);
            return false;
        }

        $user = new User();
        $coupon = Coupon::detail($coupon_id);
        if (empty($coupon)) {
            throw new BaseException(['msg' => '未找到优惠券信息']);
            return false;
        }
        if ($send_type == 1) {
            $user_arr = $user->getUsers();
            if (count($user_arr) == 0) {
                throw new BaseException(['msg' => '没有符合条件的会员']);
                return false;
            }
            $data = $this->setData($coupon, $user_arr,$num);
        } elseif ($send_type == 2) {
            $user_arr = $user->getUsers(['grade_id' => $user_level]);
            if (count($user_arr) == 0) {
                throw new BaseException(['msg' => '没有符合条件的会员']);
                return false;
            }
            $data = $this->setData($coupon, $user_arr,$num);
        } elseif ($send_type == 3) {
            if ($user_ids == '') {
                throw new BaseException(['msg' => '请选择用户']);
                return false;
            }
            $user_ids = explode(',', $user_ids);
            $user_arr = [];
            foreach ($user_ids as $val) {
                $user_arr[]['user_id'] = $val;
            }
            $data = $this->setData($coupon, $user_arr,$num);
        }
        return $this->saveAll($data);
    }

    /**
     * 数组重组
     * @param $coupon
     * @param $user_arr
     */
    public function setData($coupon, $user_arr,$num=0)
    {
        $data = [];


        foreach ($user_arr as $k => $val) {
            if ($coupon['expire_type'] == 10) {
                $start_time = time();
                $end_time = $start_time + ($coupon['expire_day'] * 86400);
            } else {
                $start_time = $coupon['start_time']['value'];
                $end_time = $coupon['end_time']['value'];
            }
            $data[$k]['coupon_id'] = $coupon['coupon_id'];
            $data[$k]['name'] = $coupon['name'];
            $data[$k]['color'] = $coupon['color']['value'];
            $data[$k]['coupon_type'] = $coupon['coupon_type']['value'];
            $data[$k]['reduce_price'] = $coupon['reduce_price'];
            $data[$k]['discount'] = $coupon['discount'];
            $data[$k]['min_price'] = $coupon['min_price'];
            $data[$k]['expire_type'] = $coupon['expire_type'];
            $data[$k]['expire_day'] = $coupon['expire_day'];
            $data[$k]['start_time'] = $start_time;
            $data[$k]['end_time'] = $end_time;
            $data[$k]['apply_range'] = $coupon['apply_range'];
            $data[$k]['app_id'] = self::$app_id;
            $data[$k]['user_id'] = $val['user_id'];
        }

        // 新增多张优惠券发放
        $zho = [];
        $count = count($data);
        for ($i=1;$i<$num;$i++){
            for ($t=0;$t<$count;$t++){
                $zho[count($data) + $t] = $data[$t];
            }
            $data = array_merge($data,$zho);
            $zho = [];
        }
        return $data;
    }
}
<?php

namespace app\api\controller\coupon;

use app\api\controller\Controller;
use app\api\model\plus\coupon\Coupon as CouponModel;

/**
 * 优惠券中心
 */
class Coupon extends Controller
{
    /**
     * 优惠券列表
     */
    public function lists()
    {
        $model = new CouponModel;
        $list = $model->getWaitList($this->getUser(false));
        return $this->renderSuccess('', compact('list'));
    }

}
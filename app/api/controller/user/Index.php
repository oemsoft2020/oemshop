<?php

namespace app\api\controller\user;

use app\api\controller\Controller;
use app\api\model\plus\agent\Setting;
use app\api\model\user\User as UserModel;
use app\api\model\order\Order as OrderModel;
use app\api\model\page\Page as PageModel;
use app\api\model\settings\Setting as SettingModel;
use app\api\model\plus\coupon\UserCoupon as UserCouponModel;
use app\api\model\page\Page as AppPage;
use app\api\model\plus\card\Card;
use app\common\enum\settings\GetPhoneTypeEnum;
use app\common\model\plus\supply\Supply as SupplyModel;
use app\common\model\plus\sign\Sign as SignModel;
use app\common\model\plus\agent\PlanSettle as SettleMod;
use think\facade\Cache;

/**
 * 个人中心主页
 */
class Index extends Controller
{
    /**
     * 获取当前用户信息
     */
    public function detail()
    {
        // 商品推荐设置
        $recommend = SettingModel::getItem('recommend');
        // 当前用户信息
        $user = $this->getUser();

        $coupon_model = new UserCouponModel();
        $coupon = count($coupon_model->getList($user['user_id'], false, false));
        // 订单总数
        $model = new OrderModel;

        // 分销商基本设置
        $setting = Setting::getItem('basic');
        // 是否开启分销功能
        $agent_open = $setting['is_open'];

        $page_model = new PageModel();
        $page_info = $page_model->where('page_type', '30')->where('is_delete', 0)->find();
        // 生成个人编码
        $user_code = UserModel::makeMyCode($user['user_id'], $user['grade']['grade_code']);

        $code_info = array(
            'code_status' => isset($page_info['page_data']['page']['style']['code_status']) ? intval($page_info['page_data']['page']['style']['code_status']) : 0,
            'code_desc' => isset($page_info['page_data']['page']['params']['code_desc']) ? $page_info['page_data']['page']['params']['code_desc'] : '',
            'user_code' => $user_code
        );
        $items = [];
        if (empty($page_info)) {
            $menu = UserModel::getMenus();
            $diymenus = 0;
        } else {

            $items = AppPage::getPageData($this->getUser(false), $page_info['page_id']);
            $jsonData = $page_info['page_data'];
            jsonRecursive($jsonData);
            $menu = [];
            if (count($jsonData['items']) == 1) {
                $menu = $jsonData['items'];
                foreach ($menu[0]['data'] as $key => &$value) {
                    if (isset($value['appid']) && strpos($value['linkUrl'], '$user_id') !== false) {
                        $value['linkUrl'] = str_replace('$user_id', $user['user_id'], $value['linkUrl']);
                    }
                }
                unset($value);
            }


            $diymenus = 1;
        }

        // 判断当前用户是否是供应商
        $supply_mod = new SupplyModel();
        $user['is_supply'] = $supply_mod->where('user_id', $user['user_id'])->count();

        // 统计签到次数
        $sign_mod = new SignModel();
        $sign_count = $sign_mod->where('user_id', $user['user_id'])->count();

        // 统计收益
        $income_mod = new SettleMod();
        $income_mod->where('user_id', $user['user_id'])->sum('money');

        //名片信息
        $cardModel = new Card();
        $cardInfo =  $cardModel->getSelfCard($user['user_id']);

        /* 获取交易设置 */

        $vars['values'] = SettingModel::getItem('trade');
        $friend = SettingModel::getItem('friend');
        return $this->renderSuccess('', [
            'coupon' => $coupon,
            'userInfo' => $user,
            'orderCount' => [
                'payment' => $model->getCount($user, 'payment'),
                'received' => $model->getCount($user, 'received'),
                'comment' => $model->getCount($user, 'comment'),
                'total' => $model->where('user_id', $user['user_id'])->count(),
            ],
            'setting' => [
                'points_name' => SettingModel::getPointsName(),
                'agent_open' => $agent_open
            ],
            'is_recommend' => $this->checkRecommend($recommend),
            'recommendData' => $recommend,
            'sign' => SettingModel::getItem('sign'),
            'menus' => $menu,   // 个人中心菜单列表
            'getPhone' => $this->isGetPhone(),
            'diymenus' => $diymenus,
            'code_info' => $code_info,
            'balance_text' => empty($vars['values']['balance_text']) ? '余额' : $vars['values']['balance_text'],
            'sign_total' => $sign_count,
            'items' => $items,
            'friend' => $friend,
            'card_id'=> !empty($cardInfo)?$cardInfo['card_id']:0
        ]);
    }

    public function checkRecommend($data)
    {
        $is_recommend = true;
        if ($data['is_recommend'] > 0) {
            if (!in_array(20, $data['location'])) {
                $is_recommend = false;
            }
        } else {
            $is_recommend = false;
        }
        return $is_recommend;
    }

    /**
     * 当前用户设置
     */
    public function setting()
    {
        // 当前用户信息
        $user = $this->getUser();

        return $this->renderSuccess('', [
            'userInfo' => $user
        ]);
    }

    private function isGetPhone()
    {
        $user = $this->getUser();
        $settings = SettingModel::getItem('memberSetting');
        if (isset($settings['is_open_getphone'])) {
            if (!empty($user['mobile'])) {
                return false;
            }
            return $settings['is_open_getphone'];
        }

        return false;
    }
}
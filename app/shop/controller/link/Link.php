<?php

namespace app\shop\controller\link;

use app\shop\controller\Controller;
use app\shop\model\plus\assemble\Active;
use app\shop\model\plus\seckill\Active as ActiveModel;
use app\shop\model\plus\bargain\Bargain;
use app\shop\model\plus\giftpackage\GiftPackage;
use app\shop\model\page\Page as PageModel;

/**
 * Class Link
 * @package app\shop\controller\link
 * 超链接控制器
 */
class Link extends Controller
{
    /**
     *获取数据
     */
    public function index($activeName)
    {
        if ($activeName == 'market') {
            //拼团
            $model = new Active();
            $data = $model->getDatas($this->postData());
            foreach ($data as $k => $v) {
                $data[$k]['type'] = '营销';
                $data[$k]['url'] = 'pages/plus/group/list/list';
            }
        } else if ($activeName == 'second') {
            $model = new ActiveModel();
            $data = $model->getDatas($this->postData());
            foreach ($data as $k => $v) {
                $data[$k]['type'] = '营销';
                $data[$k]['url'] = 'pages/plus/sharpproduct/list/list';
            }
        } else if ($activeName == 'third') {
            $model = new Bargain();
            $data = $model->getDatas($this->postData());
            foreach ($data as $k => $v) {
                $data[$k]['title'] = $v['name'];
                $data[$k]['type'] = '营销';
                $data[$k]['url'] = 'pages/plus/bargaining/list/list';
            }
        } else if ($activeName == 'fourth') {
            $model = new GiftPackage();
            $date['search'] = '';
            $data = $model->getDatas($date);
            foreach ($data as $k => $v) {
                $data[$k]['title'] = $v['name'];
                $data[$k]['type'] = '营销';
                $data[$k]['url'] = 'pages/plus/giftpackage/giftpackage&id=' . $v['gift_package_id'];
            }
        }
        return $this->renderSuccess('', compact('data'));
    }

    /**
     * 获取自定义页面
     */
    public function getPageList()
    {
        $model = new PageModel;
        $list = $model->getLists();
        return $this->renderSuccess('', compact('list'));
    }

    /*
    * 获取营销数据页面
    */

    public function getMarketingList()
    {
        $data = [
            [
                'url' => 'pages/user/userinfo/userinfo',
                'title' => '用户信息',
            ],

            [
                'url' => 'pages/product/list/list',
                'title' => '商品列表',
            ],
            [
                'url' => 'pages/user/address/address',
                'title' => '设置地址',
            ],
            [
                'url' => 'pages/user/index/index',
                'title' => '个人中心',
            ],
            [
                'url' => 'pages/order/myorder/myorder',
                'title' => '我的订单',
            ],

            [
                'url' => 'pages/user/my-wallet/my-wallet',
                'title' => '我的钱包',
            ],

            [
                'url' => 'pages/product/search/search',
                'title' => '搜索',
            ],

            [
                'url' => 'pages/order/express/express',
                'title' => '物流',
            ],

            [
                'url' => 'pages/coupon/coupon',
                'title' => '领券中心',
            ],
            [
                'url' => 'pages/user/my-coupon/my-coupon',
                'title' => '我的优惠券',
            ],
            [
                'url' => 'pages/agent/index/index',
                'title' => '分销中心',
            ],

            [
                'url' => 'pages/user/points/points',
                'title' => '积分',
            ],
            [
                'url' => 'pages/agent/order/order',
                'title' => '分销订单',
            ],
            [
                'url' => 'pages/agent/team/team',
                'title' => '我的团队',
            ],
            [
                'url' => 'pages/agent/qrcode/qrcode',
                'title' => '推广二维码',
            ],
            [
                'url' => 'pages/store/address/address',
                'title' => '门店列表',
            ],
            [
                'url' => 'pages/store/clerkorder',
                'title' => '订单核销',
            ],
            [
                'url' => 'pages/diy-page/diy-page',
                'title' => '页面',
            ],
            [
                'url' => 'pages/article/list/list',
                'title' => '文章列表',
            ],
            [
                'url' => 'pages/user/collect/collect',
                'title' => '我的收藏',
            ],

            [
                'url' => 'pages/plus/signin/signin',
                'title' => '签到',
            ],
            [
                'url' => 'pages/plus/giftpackage/giftpackage',
                'title' => '礼包购',
            ],
            [
                'url' => 'pages/plus/points/list/list',
                'title' => '积分商城',
            ],

            [
                'url' => 'pages/user/invite/invite',
                'title' => '邀请有礼',
            ],
            [
                'url' => 'pages/plus/seckill/list/list',
                'title' => '秒杀列表',
            ],
            [
                'url' => 'pages/plus/assemble/list/list',
                'title' => '拼团列表',
            ],
            [
                'url' => 'pages/plus/assemble/list/list2',
                'title' => '拼团列表2',
            ],
            [
                'url' => 'pages/plus/bargain/list/list',
                'title' => '砍价列表',
            ],

            [
                'url' => 'pages/plus/bargain/haggle/haggle',
                'title' => '砍价购',
            ],
            [
                'url' => 'pages/user/my-bargain/my-bargain',
                'title' => '我的砍价',
            ],
            [
                'url' => 'pages/plus/live/wx/list',
                'title' => '微信直播',
            ],
            [
                'url' => 'pages/user/storage/list',
                'title' => '仓库',
            ],
            [
                'url' => 'pages/auction/auction/auction',
                'title' => '拍卖',
            ],
            [
                'url' => 'pages/plus/certify/index',
                'title' => '实名认证',
            ],
            [
                'url' => 'pages/user/grade/apply',
                'title' => '会员升级',
            ],
            [
                'url' => 'pages/supply/apply',
                'title' => '供应商申请',
            ],
            [
                'url' => 'pages/supply/index/index',
                'title' => '商家中心',
            ],
            [
                'url' => 'pages/supply/product/list/list',
                'title' => '商家商品管理',
            ],
            [
                'url' => 'pages/supply/product/assemble/list',
                'title' => '商家拼团管理',

            ],
            [
                'url' => 'pages/user/changeUser/changeUser',
                'title' => '切换用户',
            ],
            [
                'url' => 'pages/plus/live/product/list',
                'title' => '我的品单',
            ],
            [
                'url' => 'pages/website/website',
                'title' => '微官网',
            ],
            [
                'url' => 'pages/plus/live/user/list?type=1',
                'title' => '我的直播预告列表',
            ],
            [
                'url' => 'pages/plus/live/user/list?type=2',
                'title' => '直播预告列表',
            ],
            [
                'url' => 'pages/plus/carousel/index',
                'title' => '操作指引',
            ],
            [
                'url' => 'pages/plus/task/index',
                'title' => '任务',
            ],
            [
                'url' => 'pages/user/storage/record',
                'title' => '赠收记录',
            ],
            [
                'url' => 'card/pages/card/square/square',
                'title' => '人脉圈',
            ]
        ];

        return $this->renderSuccess('', compact('data'));

    }

}

<?php

namespace app\common\enum\settings;

use MyCLabs\Enum\Enum;

/**
 * 商城设置枚举类
 */
class SettingEnum extends Enum
{
    // 商城设置
    const STORE = 'store';
    // 商城设置
    const MP_SERVICE = 'mp_service';
    // 商城设置
    const DEPOT = 'depot';
    // 交易设置
    const TRADE = 'trade';
    // 短信通知
    const SMS = 'sms';
    // 模板消息
    const TPL_MSG = 'tplMsg';
    // 上传设置
    const STORAGE = 'storage';
    // 仓库设置
    const HOMEPOPUP = 'homepopup';
    // 小票打印
    const PRINTER = 'printer';
    // 满额包邮设置
    const FULL_FREE = 'full_free';
    // 充值设置
    const RECHARGE = 'recharge';
    // 积分设置
    const POINTS = 'points';
    // 公众号设置
    const OFFICIA = 'officia';
    // 商品推荐
    const RECOMMEND = 'recommend';
    // 签到有礼
    const SIGN = 'sign';
    // 首页推送
    const HOMEPUSH = 'homepush';
    // 引导收藏
    const COLLECTION = 'collection';
    // 好物圈
    const BASIC = 'basic';
    // 积分商城
    const POINTSMALL = 'pointsmall';
    // ICP查询接口设置
    const ICPAPI = 'icpApi';
    // ES配置
    const ESCONFIG = 'esConfig';
    // 限时秒杀
    const SECKILL = 'seckill';
    // 限时拼团
    const ASSEMBLE = 'assemble';
    const SUPPLY = 'supply';
    // 商品设置
    const PRODUCT = 'product';
    // 限时砍价
    const BARGAIN = 'bargain';
    // 高级签到
    const SIGNADV = 'signadv';
    const LOGISTICS = 'logistics';
    const CUSTOMER = 'customer';
    // 获取手机号
    const GETPHOME = 'getPhone';
    const MEMBERSETTING = 'memberSetting';
    //名片
    const CARD  ='card';
    // 授权证书
    const CERT = 'certificate';
    // 授权证书
    const TASK = 'task';
    // 拍卖
    const AUCTION = 'auction';
    // 拍卖
    const FRIEND = 'friend';
    // 授权证书
    const ARTICLE = 'article';

    /**
     * 获取订单类型值
     */
    public static function data()
    {
        return [
            self::STORE => [
                'value' => self::STORE,
                'describe' => '商城设置',
            ],
            self::MP_SERVICE => [
                'value' => self::MP_SERVICE,
                'describe' => '客服设置',
            ],
            self::DEPOT => [
                'value' => self::DEPOT,
                'describe' => '仓库设置',
            ],
            self::FRIEND => [
                'value' => self::FRIEND,
                'describe' => '好友圈设置',
            ],
            self::TASK => [
                'value' => self::TASK,
                'describe' => '任务设置',
            ],
            self::AUCTION => [
                'value' => self::AUCTION,
                'describe' => '拍卖设置',
            ],
            self::TRADE => [
                'value' => self::TRADE,
                'describe' => '交易设置',
            ],
            self::SMS => [
                'value' => self::SMS,
                'describe' => '短信通知',
            ],
            self::TPL_MSG => [
                'value' => self::TPL_MSG,
                'describe' => '模板消息',
            ],
            self::STORAGE => [
                'value' => self::STORAGE,
                'describe' => '上传设置',
            ],
            self::PRINTER => [
                'value' => self::PRINTER,
                'describe' => '小票打印',
            ],
            self::FULL_FREE => [
                'value' => self::FULL_FREE,
                'describe' => '满额包邮设置',
            ],
            self::RECHARGE => [
                'value' => self::RECHARGE,
                'describe' => '充值设置',
            ],
            self::POINTS => [
                'value' => self::POINTS,
                'describe' => '积分设置',
            ],
            self::OFFICIA => [
                'value' => self::OFFICIA,
                'describe' => '公众号设置',
            ],
            self::RECOMMEND => [
                'value' => self::RECOMMEND,
                'describe' => '商品推荐',
            ],
            self::SIGN => [
                'value' => self::SIGN,
                'describe' => '签到有礼',
            ],
            self::HOMEPUSH => [
                'value' => self::HOMEPUSH,
                'describe' => '首页推送',
            ],
            self::COLLECTION => [
                'value' => self::COLLECTION,
                'describe' => '引导收藏',
            ],
            self::BASIC => [
                'value' => self::BASIC,
                'describe' => '好物圈',
            ],
            self::POINTSMALL => [
                'value' => self::POINTSMALL,
                'describe' => '积分商城',
            ],
            self::ICPAPI => [
                'value' => self::ICPAPI,
                'describe' => 'ICP查询接口配置',
            ],
            self::ESCONFIG => [
                'value' => self::ESCONFIG,
                'describe' => 'elasticsearch配置',
            ],
            self::SECKILL => [
                'value' => self::SECKILL,
                'describe' => '限时秒杀',
            ],
            self::ASSEMBLE => [
                'value' => self::ASSEMBLE,
                'describe' => '限时拼团',
            ],
            self::SUPPLY => [
                'value' => self::SUPPLY,
                'describe' => '供应商',
            ],
            self::PRODUCT => [
                'value' => self::PRODUCT,
                'describe' => '商品',
            ],
            self::BARGAIN => [
                'value' => self::BARGAIN,
                'describe' => '限时砍价',
            ],
            self::SIGNADV => [
                'value' => self::SIGNADV,
                'describe' => '高级签到',
            ],
            self::LOGISTICS => [
                'value' => self::LOGISTICS,
                'describe' => '物流中心',
            ],
            self::CUSTOMER => [
                'value' => self::CUSTOMER,
                'describe' => '客户资源',
            ],
            self::GETPHOME => [
                'value' => self::GETPHOME,
                'describe' => '获取手机号',
            ],
            self::MEMBERSETTING => [
                'value' => self::MEMBERSETTING,
                'describe' => '会员设置',
            ],
            self::CARD=>[
                'value' => self::CARD,
                'describe' => '名片设置',
            ],

            self::CERT=>[
                'value' => self::CERT,
                'describe' => '授权证书设置',
            ],

            self::HOMEPOPUP => [
                'value' => self::HOMEPOPUP,
                'describe' => '首页弹窗',
            ],
            self::ARTICLE => [
                'value' => self::ARTICLE,
                'describe' => '文章设置',
            ],


        ];
    }

}
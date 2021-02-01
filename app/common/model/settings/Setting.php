<?php

namespace app\common\model\settings;

use think\facade\Cache;
use app\common\enum\settings\DeliveryTypeEnum;
use app\common\model\BaseModel;

/**
 * 系统设置模型
 */
class Setting extends BaseModel
{
    protected $name = 'setting';
    protected $createTime = false;

    /**
     * 获取器: 转义数组格式
     */
    public function getValuesAttr($value)
    {
        return json_decode($value, true);
    }

    /**
     * 修改器: 转义成json格式
     */
    public function setValuesAttr($value)
    {
        return json_encode($value);
    }

    /**
     * 获取指定项设置
     */
    public static function getItem($key, $app_id = null)
    {
        $data = self::getAll($app_id ? : self::$app_id);
        if(isset($data[$key])){
            $data_key = $data[$key]['values'];
            jsonRecursive($data_key);
        }else{
            $data_key = [];
        }

        return $data_key;
    }

    /**
     * 获取设置项信息
     */
    public static function detail($key)
    {
        return self::where('key', '=', $key)->find();
    }

    /**
     * 添加setting表应用设置
     */
    public  function addItem($data)
    {
        $data['update_time'] = time();
        $this->save($data);

    }

    /**
     * 全局缓存: 系统设置
     */
    public static function getAll($app_id = null)
    {
        $static = new static;
        is_null($app_id) && $app_id = $static::$app_id;

        if (!$data = Cache::get('setting_' . $app_id)) {
            $setting = $static->where(compact('app_id'))->select();
            $data = empty($setting) ? [] : array_column($static->collection($setting)->toArray(), null, 'key');
            Cache::tag('cache')->set('setting_' . $app_id, $data);
        }
        return $static->getMergeData($data);
    }

    /**
     * 数组转换为数据集对象
     */
    public function collection($resultSet)
    {
        $item = current($resultSet);
        if ($item instanceof Model) {
            return \think\model\Collection::make($resultSet);
        } else {
            return \think\Collection::make($resultSet);
        }
    }


    /**
     * 合并用户设置与默认数据
     */
    private function getMergeData($userData)
    {
        $defaultData = $this->defaultData();
        // 商城设置：配送方式
        if (isset($userData['store']['values']['delivery_type'])) {
            unset($defaultData['store']['values']['delivery_type']);
        }
        return array_merge_multiple($defaultData, $userData);
    }

    /**
     * 默认配置
     */
    public function defaultData($storeName = null)
    {
        return [
            'store' => [
                'key' => 'store',
                'describe' => '商城设置',
                'values' => [
                    // 商城名称
                    'name' => $storeName ?: 'XXX商城',
                    // 配送方式
                    'delivery_type' => array_keys(DeliveryTypeEnum::data()),
                    // 快递100
                    'kuaidi100' => [
                        'customer' => '',
                        'key' => '',
                    ]
                ],
            ],
            'mp_service' => [
                'key' => 'mp_service',
                'describe' => '公众号客服设置',
                'values' => [
                    // qq
                    'qq' => '',
                    // 微信
                    'wechat' => '',
                    // 微信公众号图片
                    'mp_image' => '',
                ],
            ],
            'depot' => [
                'key' => 'depot',
                'describe' => '仓库设置',
                'values' => [
                    // 图片
                    'image' => '',
                ],
            ],
            'task' => [
                'key' => 'task',
                'describe' => '任务设置',
                'values' => [
                    // 图片
                    'is_open_task' => false,
                ],
            ],
            'auction' => [
                'key' => 'auction',
                'describe' => '拍卖设置',
                'values' => [
                    // 图片
                    'image' => false,
                ],
            ],
            'friend' => [
                'key' => 'friend',
                'describe' => '拍卖设置',
                'values' => [
                    // 图片
                    'image' => false,
                    'images' => false,
                ],
            ],
            'trade' => [
                'key' => 'trade',
                'describe' => '交易设置',
                'values' => [
                    'order' => [
                        'close_days' => '3',
                        'receive_days' => '10',
                        'refund_days' => '7'
                    ],
                    'freight_rule' => '10',
                    'price_mode' => 0,
                    'buy_auth' => [
                        'is_open'=>0,
                        'show_price_id'=>[],
                        'buy_auth_id'=>[],
                        'no_price'=>0,
                        'linkUrl'=>'',
                        'name'=> '',
                        'tips'=> '',
                    ],
                ]
            ],
            'memberSetting' => [
                'key' => 'memberSetting',
                'describe' => '会员设置',
                'values' => [
                    'is_open_getphone'=>false,
                    'auth_setting' => [
                        'is_open'=>0,
                        'need_certification'=>0,
                        'grade_id'=>[],
                        'file_path'=>'',
                    ],
                ]
            ],
            'storage' => [
                'key' => 'storage',
                'describe' => '上传设置',
                'values' => [
                    'default' => 'local',
                    'engine' => [
                        'local' => [],
                        'qiniu' => [
                            'bucket' => '',
                            'access_key' => '',
                            'secret_key' => '',
                            'domain' => 'http://'
                        ],
                        'aliyun' => [
                            'bucket' => '',
                            'access_key_id' => '',
                            'access_key_secret' => '',
                            'domain' => 'http://'
                        ],
                        'qcloud' => [
                            'bucket' => '',
                            'region' => '',
                            'secret_id' => '',
                            'secret_key' => '',
                            'domain' => 'http://'
                        ],
                    ]
                ],
            ],
            'sms' => [
                'key' => 'sms',
                'describe' => '短信通知',
                'values' => [
                    'default' => 'aliyun',
                    'engine' => [
                        'aliyun' => [
                            'AccessKeyId' => '',
                            'AccessKeySecret' => '',
                            'sign' => '商城',
                            'accept_phone' => '',
                            'template_code' => ''
                        ],
                    ],
                ],
            ],
            'tplMsg' => [
                'key' => 'tplMsg',
                'describe' => '模板消息',
                'values' => [
                    'payment' => [
                        'is_enable' => '0',
                        'template_id' => '',
                    ],
                    'delivery' => [
                        'is_enable' => '0',
                        'template_id' => '',
                    ],
                    'refund' => [
                        'is_enable' => '0',
                        'template_id' => '',
                    ],
                ],
            ],
            'printer' => [
                'key' => 'printer',
                'describe' => '小票打印机设置',
                'values' => [
                    'is_open' => '0',   // 是否开启打印
                    'printer_id' => '', // 打印机id
                    'order_status' => [], // 订单类型 10下单打印 20付款打印 30确认收货打印
                ],
            ],
            'full_free' => [
                'key' => 'full_free',
                'describe' => '满额包邮设置',
                'values' => [
                    'is_open' => '0',   // 是否开启满额包邮
                    'money' => '',      // 单笔订单额度
                    'notin_region' => [ // 不参与包邮的地区
                        'province' => [],
                        'citys' => [],
                        'treeData' => [],
                    ],
                    'notin_product' => [],  // 不参与包邮的商品   (商品id集)
                ],
            ],
            'recharge' => [
                'key' => 'recharge',
                'describe' => '用户充值设置',
                'values' => [
                    'is_entrance' => '1',   // 是否允许用户充值
                    'is_custom' => '1',   // 是否允许自定义金额
                    'is_match_plan' => '1',   // 自定义金额是否自动匹配合适的套餐
                    'describe' => "1. 账户充值仅限微信在线方式支付，充值金额实时到账；\n" .
                        "2. 账户充值套餐赠送的金额即时到账；\n" .
                        "3. 账户余额有效期：自充值日起至用完即止；\n" .
                        "4. 若有其它疑问，可拨打客服电话400-000-1234",     // 充值说明
                ],
            ],
            'points' => [
                'key' => 'points',
                'describe' => '积分设置',
                'values' => [
                    'points_name' => '积分',         // 积分名称自定义
                    'is_shopping_gift' => '0',      // 是否开启购物送积分
                    'gift_ratio' => '100',            // 是否开启购物送积分
                    'is_shopping_discount' => '0',    // 是否允许下单使用积分抵扣
                    'balance_ratio'=>10, //积分充值比例
                    'discount' => [     // 积分抵扣
                        'discount_ratio' => '0.01',       // 积分抵扣比例
                        'full_order_price' => '100.00',       // 订单满[?]元
                        'max_money_ratio' => '10',             // 最高可抵扣订单额百分比
                    ],
                    // 充值说明
                    'describe' => "a) 积分不可兑现、不可转让,仅可在本平台使用;\n" .
                        "b) 您在本平台参加特定活动也可使用积分,详细使用规则以具体活动时的规则为准;\n" .
                        "c) 积分的数值精确到个位(小数点后全部舍弃,不进行四舍五入)\n" .
                        "d) 买家在完成该笔交易(订单状态为“已签收”)后才能得到此笔交易的相应积分,如购买商品参加店铺其他优惠,则优惠的金额部分不享受积分获取;",
                ],
            ],
            'officia' => [
                'key' => 'officia',
                'describe' => '公众号关注',
                'values' => [
                    'status' => 0
                ],
            ],
            'collection' => [
                'key' => 'collection',
                'describe' => '引导收藏',
                'values' => [
                    'status' => 0
                ],
            ],
            'recommend' => [
                'key' => 'recommend',
                'describe' => '商品推荐',
                'values' => [
                    'is_recommend' => 0,
                    'name' => '商品推荐',
                    'location' => [10, 20, 30],
                    'choice' => 0
                ],
            ],
            'basic' => [
                'key' => 'basic',
                'describe' => '好物圈',
                'values' => [
                    // 是否开启
                    'status' => 0,
                    // 是否同步购物车 (商品收藏)
                    'is_shopping' => '0',
                    // 是否同步订单
                    'is_order' => '0',
                ]
            ],
            'homepush' => [
                'key' => 'homepush',
                'describe' => '首页推送',
                'values' => [
                    // 是否开启
                    'is_open' => 0,
                ]
            ],
            'pointsmall' => [
                'key' => 'pointsmall',
                'describe' => '积分商城',
                'values' => [
                    // 是否开启
                    'is_open' => false,
                    // 是否使用优惠券
                    'is_coupon' => false,
                    // 是否分销
                    'is_agent' => false,
                ]
            ],
            'bargain' => [
                'key' => 'bargain',
                'describe' => '限时砍价',
                'values' => [
                    // 是否使用优惠券
                    'is_coupon' => false,
                    // 是否分销
                    'is_agent' => false,
                    // 是否开启积分
                    'is_point' => false,
                    // 规则
                    'bargain_rules' => ''
                ]
            ],
            'sign' => [
                'key' => 'sign',
                'describe' => '签到有礼',
                'values' => [
                    // 是否开启
                    'is_open' => false
                ]
            ],
            'seckill' => [
                'key' => 'seckill',
                'describe' => '限时秒杀',
                'values' => [
                    // 是否开启积分
                    'is_point' => false,
                    // 是否开启分销
                    'is_agent' => false,
                    //未付款订单自动关闭时间,分钟
                    'order_close' => 10
                ]
            ],
            'assemble' => [
                'key' => 'assemble',
                'describe' => '限时拼团',
                'values' => [
                    // 是否开启
                    'is_open' => false,
                    // 是否开启积分
                    'is_point' => false,
                    // 是否开启分销
                    'is_agent' => false,
                ]
            ],
            'supply' => [
                'key' => 'supply',
                'describe' => '供应商',
                'values' => [
                    // 是否开启
                    'is_open' => 0,
                    'is_apply' => 0,
                    // 商品刷新积分
                    'promotion_points' => 0,
                    'license' =>'',
                    // 是否需要实名认证
                    'need_certification' => 0,
                    'grade' => [],
                    'image_id'=>'',
                    'file_path'=>'',
                    'banner_image_id'=>'',
                    'banner_file_path'=>'',
                ]
            ],
            'product' => [
                'key' => 'product',
                'describe' => '商品',
                'values' => [
                    'product_no_text'=>'',
                    'product_diy_no_text'=>'',
                    'product_no_show'=>0,
                    'product_diy_no_show'=>0,
                    'product_price_show'=>1,
                    'supply_show'=>0,
                    'label_show'=>0,
                    'label_in_title'=>0,
                    'single_spec'=>0,
                ]
            ],
        ];
    }

}

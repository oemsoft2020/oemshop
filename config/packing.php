<?php
return [
    //站点
    'site' => [
        'free' => "oemshop.com",
        'biz' => "vkmallbiz.1.y01.cn"
    ],
    //应用程序
    'application' => [
        'main' => [
            '/home',//概况
            '/user',//用户
            '/setting',//设置
            '/product',//商品
            '/page',//装修
            '/order',//订单
            '/auth',//权限
            '/appsetting',//渠道
            '/statistics'//统计
        ],
        //插件
        'plus' => [
            'default' => '/plus',
            'free' => [
                '/api',//api
                '/article',//文章
                //'/assemble',//拼团
                '/bargain',//砍价
                '/coupon',//优惠券
                '/collection',//引导收藏
                '/seckill',//秒杀
                '/officia',//公众号关注
                //'/points',//积分商城
                '/recommend',//商品推荐
                '/sign',//签到
                '/package',//礼包购
                '/homepush',//首页推送
                //'/live',//直播
                '/fullfree'//满额包邮
            ],
            'biz' => [],
        ],

    ],
    //应用

    //env模板
    'envTemplate' => [
        "free" =>
            'APP_DEBUG = true
[APP]
DEFAULT_TIMEZONE = Asia/Shanghai
[DATABASE]
TYPE = mysql
HOSTNAME = 127.0.0.1
DATABASE = %s
USERNAME = root
PASSWORD = 123456
HOSTPORT = 3306
CHARSET = utf8
prefix = %s
default_lang = zh-cn
[ELASTICSEARCH]
HOSTS = 127.0.0.1:9200
[LANG]
default_lang = zh-cn
[KMD_UPLOAD]
upload_type = 
upload_bucket = 
upload_access_key = 
upload_secret_key = 
upload_domain = 
[PLATFORM]
APPID = 
APPSECRET = 
TOKEN = 
AESKEY = 
EXTAPPID =
',
        "biz" => 'APP_DEBUG = false
                [APP]
                DEFAULT_TIMEZONE = Asia/Shanghai
                [DATABASE]
                TYPE = mysql
                HOSTNAME = 127.0.0.1
                DATABASE = %s
                USERNAME = root
                PASSWORD = 123456
                HOSTPORT = 3306
                CHARSET = utf8
                prefix = %s
                [ELASTICSEARCH]
                HOSTS = 127.0.0.1:9200
                [LANG]
                default_lang = zh-cn
                [KMD_UPLOAD]
                upload_type = qiniu
                upload_bucket = bucket
                upload_access_key = access_key
                upload_secret_key = secret_key
                upload_domain = http://qniu.q.com
                [PLATFORM]
                APPID = wx17fca2c04e6dada6
                APPSECRET = 1e2rewer
                TOKEN = sfdsdfsd
                AESKEY = sdfsfs
                EXTAPPID =2222222
        '
    ],
    'database' => [
        'prefix'=>'oemshop_',
        'table' => [
            'app_mp',
            'app_wx',
            'storage_log',
            'import_log',
            'error_log',
            'shop_login_log',
            'shop_opt_log',
            'sms',
            'user_news',
            'user_point_log'
        ],
        'key' => [
            'setting' => ['kmd', 'sms', 'store']
        ]
    ]

];
$copy_config = array(
    "free" => array("path" => "../vkmallbiz.1.y01.cn/"),
    "biz" => array("path" => "../vkmallbiz.1.y01.cn/"),
);
$data_config = array(
    "free" => array(
        "db_name" => "vkmallfree_1_y01",
        "db_user" => "root",
        "db_pwd" => "root",
        'userful_app' => explode(",", "/product,"),
        'useful_table' => explode(",", "kmdshop_admin_user,kmdshop_user"),
    ),
    "biz" => array("path" => "../vkmallbiz.1.y01.cn/"),
);

?>
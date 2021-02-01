<?php
// 事件定义文件
return [
    'listen' => [
        'AppInit' => [],
        'HttpRun' => [],
        'HttpEnd' => [],
        'LogLevel' => [],
        'LogWrite' => [],
        'PaySuccess' => [
            \app\api\event\PaySuccess::class
        ],
        /*订单事件*/
        'Order' => [
            \app\job\event\Order::class
        ],
        /*领取优惠券事件*/
        'UserCoupon' => [
            \app\job\event\UserCoupon::class
        ],
        /*分销商订单*/
        'AgentOrder' => [
            \app\job\event\AgentOrder::class
        ],
        /*拼团订单*/
        'AssembleBill' => [
            \app\job\event\AssembleBill::class
        ],
        /*砍价任务*/
        'BargainTask' => [
            \app\job\event\BargainTask::class
        ],
        /*用户等级*/
        'UserGrade' => [
            \app\job\event\UserGrade::class
        ],
    ],

    'subscribe' => [
    ],
];

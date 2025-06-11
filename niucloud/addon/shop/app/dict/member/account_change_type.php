<?php

use app\dict\member\MemberAccountTypeDict;

return [
    MemberAccountTypeDict::POINT => [
        //兑换订单
        'account_point_exchange_close' => [
            //名称
            'name' => get_lang('dict_member.account_point_exchange_close'),
            //是否增加
            'inc' => 0,
            //是否减少
            'dec' => 1,
        ],
        //兑换订单关闭
        'account_point_exchange_order' => [
            //名称
            'name' => get_lang('dict_member.account_point_exchange_order'),
            //是否增加
            'inc' => 1,
            //是否减少
            'dec' => 0,
        ],
        //兑换订单售后
        'account_point_exchange_refund' => [
            //名称
            'name' => get_lang('dict_member.account_point_exchange_refund'),
            //是否增加
            'inc' => 1,
            //是否减少
            'dec' => 0,
        ],
        //消费奖励
        'consume_reward' => [
            //名称
            'name' => get_lang('dict_member.account_point_consume_reward'),
            //是否增加
            'inc' => 1,
            //是否减少
            'dec' => 0,
        ],
        //满减送活动赠品发放
        'manjian_gift_give' => [
            //名称
            'name' => get_lang('dict_member.account_point_manjian_gift_give'),
            //是否增加
            'inc' => 1,
            //是否减少
            'dec' => 0,
        ],
        //满减送活动赠品退还
        'manjian_gift_back' => [
            //名称
            'name' => get_lang('dict_member.account_point_manjian_gift_back'),
            //是否增加
            'inc' => 0,
            //是否减少
            'dec' => 1,
        ],
        //会员充值赠品发放
        'recharge' => [
            //名称
            'name' => get_lang('dict_member.recharge_point_give'),
            //是否增加
            'inc' => 1,
            //是否减少
            'dec' => 0,
            //是否累增
            'is_change_get' => 1,
        ]
    ],
    MemberAccountTypeDict::BALANCE => [
        //满减送活动赠品发放
        'manjian_gift_give' => [
            //名称
            'name' => get_lang('dict_member.account_balance_manjian_gift_give'),
            //是否增加
            'inc' => 1,
            //是否减少
            'dec' => 0,
        ],
        //满减送活动赠品退还
        'manjian_gift_back' => [
            //名称
            'name' => get_lang('dict_member.account_balance_manjian_gift_back'),
            //是否增加
            'inc' => 0,
            //是否减少
            'dec' => 1,
        ]
    ]
];

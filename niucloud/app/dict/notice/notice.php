<?php
return [
    'verify_code' => [
        'key' => 'verify_code',
        'receiver_type' => 0,
        'name' => '管理端手机验证码',
        'title' => '管理端验证码登录',
        'async' => false,
        'variable' => [
            'code' => '验证码'
        ],
    ],
    //手机验证码，站点应用发送
    'member_verify_code' => [
        'key' => 'member_verify_code',
        'receiver_type' => 1,
        'name' => '客户端手机验证码',
        'title' => '前端验证码登录，注册，手机验证',
        'async' => false,
        'variable' => [
            'code' => '验证码'
        ],
    ],

    //提示用户收款
//    'member_transfer' => [
//        'key' => 'member_transfer',
//        'receiver_type' => 1,
//        'name' => '提示用户收款',
//        'title' => '后台通过提现申请后，提示用户收款',
//        'async' => false,
//        'variable' => [
//            'transfer_no' => '转账单号',
//
//        ],
//    ]
];

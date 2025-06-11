<?php
return [
    //手机验证码
    'verify_code' => [
        'is_need_closure_content' => 0,
        'content' => '您的手机验证码{code}，请不要轻易告诉其他人'
    ],
    'member_verify_code' => [
        'is_need_closure_content' => 0,
        'content' => '您的手机验证码{code}，请不要轻易告诉其他人',
    ],
    'recharge_success' => [
        'is_need_closure_content' => 0,
        'content' => '您充值金额￥{price}, 充值后金额￥{balance}',
    ],

//    'member_transfer' => [
//        'is_need_closure_content' => 0,
//        'content' => '你的编号为{transfer_no}的提现申请已通过,您现在可以在提现中点击提现收款了',
//    ]

];
<?php
return [
    'shop_order_pay' => [
        'addon' => 'shop',
        'key' => 'shop_order_pay',
        'receiver_type' => 1,
        'name' => '商城订单支付成功通知',
        'title' => '订单支付成功后发送',
        'async' => true,
        'variable' => [
            'order_money' => '订单总额',
            'pay_time' => '支付时间',
            'create_time' => '支付时间',
            'body' => '订单内容',
            'order_no' => '订单编号',
            'url' => '订单链接'
        ],
    ],
    'shop_order_delivery' => [
        'addon' => 'shop',
        'key' => 'shop_order_delivery',
        'receiver_type' => 1,
        'name' => '商城订单发货通知',
        'title' => '订单发货之后通知买家',
        'async' => true,
        'variable' => [
            'delivery_time' => '发货时间',
            'order_money' => '订单总额',
            'body' => '订单内容',
            'order_no' => '订单编号',
            'url' => '订单链接'
        ],
    ],
    'shop_refund_agree' => [
        'addon' => 'shop',
        'key' => 'shop_refund_agree',
        'receiver_type' => 1,
        'name' => '商城商家同意退款申请',
        'title' => '商家同意买家退款申请后发送',
        'async' => true,
        'variable' => [
            'order_no' => '订单编号',
            'refund_money' => '退款金额',
        ],
    ],
    'shop_refund_refuse' => [
        'addon' => 'shop',
        'key' => 'shop_refund_refuse',
        'receiver_type' => 1,
        'name' => '商城商家拒绝退款申请',
        'title' => '商家拒绝买家退款申请后发送',
        'async' => true,
        'variable' => [
            'order_no' => '订单编号',
            'refund_money' => '退款金额',
        ]
    ]
];

<?php

return [
    'grant' => [
        'shop_buy_goods' => [
            'key' => 'shop_buy_goods',
            'name' => '购买商品',
            'desc' => '订单交易成功后按订单交易金额发放积分',
            'component' => '/src/addon/shop/views/member/components/point-rule-buygoods.vue',
            'calculate' => function (array $config, array $data) {
                $order_money = (float)($data['order_money'] ?? 0);
                if ($order_money <= 0) return 0;
                $point = ((float)$config['money'] * (float)$config['point']);
                if ($point <= 0) return 0;
                return (int)round($order_money / $point);
            },
            'content' => [
                'admin' => function ($config) {
                    return "购买商品可获得{$config['point']}积分/{$config['money']}元";
                },
                'task' => function ($config) {
                    return [
                        'icon' => '/addon/shop/rule/growth-rule-cart.png',
                        'title' => '购买商品',
                        'desc' => "每消费{$config['money']}元，可获得{$config['point']}积分",
                        'button' => [
                            'text' => '去购买',
                            'wap_redirect' => '/addon/shop/pages/index'
                        ]
                    ];
                }
            ]
        ],
    ],
    'consume' => [
//        'shop_order_deduction' => [
//            'key' => 'shop_order_deduction',
//            'name' => '积分抵现',
//            'desc' => '订单交易时积分可抵部分现金',
//            'component' => '/src/addon/shop/views/member/components/point-rule-orderdeduction.vue',
//            'content' => [
//                'admin' => function($config) {
//                    return "订单交易时{$config['point']}积分可抵{$config['money']}元";
//                }
//            ]
//        ]
    ]
];

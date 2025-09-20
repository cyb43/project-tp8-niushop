<?php

return [
    'shop_buy_goods' => [
        'key' => 'shop_buy_goods',
        'name' => '购买商品',
        'desc' => '订单交易成功后按订单交易金额发放成长值',
        'component' => '/src/addon/shop/views/member/components/growth-rule-buygoods.vue',
        'calculate' => function (array $config, array $data) {
            $order_money = (float)($data['order_money'] ?? 0);
            if ($order_money <= 0) return 0;
            $growth = ((float)$config['money'] * (float)$config['growth']);
            if ($growth <= 0) return 0;
            return (int)round($order_money / $growth);
        },
        'content' => [
            'admin' => function ( $config) {
                return "购买商品可获得{$config['growth']}成长值/{$config['money']}元";
            },
            'task' => function ( $config) {
                return [
                    'icon' => '/addon/shop/rule/growth-rule-cart.png',
                    'title' => '购买商品',
                    'desc' => "购买商品可获得{$config['growth']}成长值/{$config['money']}元",
                    'button' => [
                        'text' => '去购买',
                        'wap_redirect' => '/addon/shop/pages/index'
                    ]
                ];
            }
        ]
    ],
    'shop_buy_order' => [
        'key' => 'shop_buy_order',
        'name' => '完成下单',
        'desc' => '订单交易成功后发放成长值',
        'component' => '/src/addon/shop/views/member/components/growth-rule-buyorder.vue',
        'calculate' => '', // 计算成长值 可以是匿名函数 也可以是类
        'content' => [
            'admin' => function ( $config) {
                return "订单交易成功后可获得{$config['growth']}成长值/单";
            },
            'task' => function ( $config) {
                return [
                    'icon' => '/addon/shop/rule/growth-rule-buy.png',
                    'title' => '完成下单',
                    'desc' => "订单交易成功后可获得{$config['growth']}成长值/单",
                    'button' => [
                        'text' => '去下单',
                        'wap_redirect' => '/addon/shop/pages/index'
                    ]
                ];
            }
        ]
    ],
];

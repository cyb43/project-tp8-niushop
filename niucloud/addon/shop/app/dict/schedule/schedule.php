<?php

return [
    [
        'key' => 'shop_order_close',
        'name' => '未支付订单自动关闭',
        'desc' => '',
        'time' => [
            'type' => 'min',
            'min' => 1
        ],
        'class' => 'addon\shop\app\job\order\OrderClose',
        'function' => ''
    ],
    [
        'key' => 'shop_order_close_allow_refund',
        'name' => '订单完成自动关闭售后',
        'desc' => '',
        'time' => [
            'type' => 'min',
            'min' => 1
        ],
        'class' => 'addon\shop\app\job\order\OrderCloseAllowRefund',
        'function' => ''
    ],
    [
        'key' => 'shop_order_finish',
        'name' => '待收货订单自动完成',
        'desc' => '',
        'time' => [
            'type' => 'min',
            'min' => 1
        ],
        'class' => 'addon\shop\app\job\order\OrderFinish',
        'function' => ''
    ],
    [
        'key' => 'shop_coupon_member_expire',
        'name' => '优惠券到期自动过期',
        'desc' => '',
        'time' => [
            'type' => 'min',
            'min' => 1
        ],
        'class' => 'addon\shop\app\job\marketing\CouponMemberExpire',
        'function' => ''
    ],
    [
        'key' => 'shop_coupon_start',
        'name' => '优惠券限时自动开启',
        'desc' => '',
        'time' => [
            'type' => 'min',
            'min' => 1
        ],
        'class' => 'addon\shop\app\job\marketing\CouponStart',
        'function' => ''
    ],
    [
        'key' => 'shop_coupon_end',
        'name' => '优惠券限时自动结束',
        'desc' => '',
        'time' => [
            'type' => 'min',
            'min' => 1
        ],
        'class' => 'addon\shop\app\job\marketing\CouponEnd',
        'function' => ''
    ],
    [
        'key' => 'shop_coupon_send',
        'name' => '优惠券自动发放',
        'desc' => '',
        'time' => [
            'type' => 'min',
            'min' => 1
        ],
        'class' => 'addon\shop\app\job\marketing\CouponSend',
        'function' => '',
        'params'=>['record_id'=>'']
    ],
    [
        'key' => 'shop_active_start',
        'name' => '营销活动自动开启',
        'desc' => '',
        'time' => [
            'type' => 'min',
            'min' => 1
        ],
        'class' => 'addon\shop\app\job\marketing\ActiveStart',
        'function' => ''
    ],
    [
        'key' => 'shop_active_end',
        'name' => '营销活动自动结束',
        'desc' => '',
        'time' => [
            'type' => 'min',
            'min' => 1
        ],
        'class' => 'addon\shop\app\job\marketing\ActiveEnd',
        'function' => ''
    ],
    [
        'key' => 'shop_manjiansong_start',
        'name' => '满减送自动开始',
        'desc' => '',
        'time' => [
            'type' => 'min',
            'min' => 1
        ],
        'class' => 'addon\shop\app\job\marketing\ManjianStart',
        'function' => ''
    ],
    [
        'key' => 'shop_manjiansong_end',
        'name' => '满减送自动结束',
        'desc' => '',
        'time' => [
            'type' => 'min',
            'min' => 1
        ],
        'class' => 'addon\shop\app\job\marketing\ManjianEnd',
        'function' => ''
    ],
    [
        'key' => 'shop_goods_statistical_update',
        'name' => '商品统计更新',
        'desc' => '',
        'time' => [
            'type' => 'day',
            'day' => 1,
            'min' => 1,
            'hour' => 0
        ],
        'class' => 'addon\shop\app\job\goods\GoodsStatisticalUpdate',
        'function' => ''
    ],
    [
        'key' => 'shop_discount_start',
        'name' => '限时折扣自动开启',
        'desc' => '',
        'time' => [
            'type' => 'min',
            'min' => 1
        ],
        'class' => 'addon\shop\app\job\marketing\DiscountStart',
        'function' => ''
    ],
    [
        'key' => 'shop_discount_end',
        'name' => '限时折扣自动结束',
        'desc' => '',
        'time' => [
            'type' => 'min',
            'min' => 1
        ],
        'class' => 'addon\shop\app\job\marketing\DiscountEnd',
        'function' => ''
    ],

];

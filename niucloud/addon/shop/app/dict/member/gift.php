<?php

return [
    'shop_coupon' => [
        'key' => 'shop_coupon',
        'name' => '优惠券', // 礼包名称
        'desc' => '发放优惠券', // 礼包说明
        'component' => '/src/addon/shop/views/member/components/gift-coupon.vue',
        // 礼包发放调用方法
        'grant' => function ($member_id, $config) {
            if (!empty($config[ 'coupon_id' ])) {
                $service = new \addon\shop\app\service\core\coupon\CoreCouponMemberService();
                foreach ($config[ 'coupon_id' ] as $coupon_id) {
                    if (!isset($config[ 'coupon_list' ][ 'id_' . $coupon_id ])) continue;
                    $service->sendCoupon($member_id, $coupon_id, $config[ 'coupon_list' ][ 'id_' . $coupon_id ]);
                }
            }
        },
        "content" => [
            // 管理端
            'admin' => function ($config) {
                if (empty($config[ 'coupon_id' ])) {
                    return '';
                }
                $content = [];
                $model = ( new addon\shop\app\model\coupon\Coupon() );
                $coupon_list = $model->where([ [ 'id', 'in', $config[ 'coupon_id' ] ] ])->field('id,title')->select()->toArray();
                if (!empty($coupon_list)) {
                    foreach ($coupon_list as $item) {
                        $content[] = $item[ 'title' ] . '*' . $config[ 'coupon_list' ][ 'id_' . $item[ 'id' ] ];
                    }
                }
                return implode(',', $content);
            },
            // 会员等级
            'member_level' => function ($config) {
                if (empty($config[ 'coupon_id' ])) {
                    return '';
                }
                $content = [];
                $model = ( new addon\shop\app\model\coupon\Coupon() );
                $coupon_list = $model->where([ [ 'id', 'in', $config[ 'coupon_id' ] ] ])->field('id,price')->select()->toArray();
                if (!empty($coupon_list)) {
                    foreach ($coupon_list as $item) {
                        $content[] = [
                            'text' => round($item[ 'price' ]) . "元优惠券",
                            'background' => '/addon/shop/gift/gift_coupon_bg.png'
                        ];
                    }
                }
                return $content;
            },
            // 会员签到（日签）
            'member_sign' => function ($config) {
                if (empty($config[ 'coupon_id' ])) {
                    return [];
                }
                $content = [];
                $model = ( new addon\shop\app\model\coupon\Coupon() );
                $coupon_list = $model->where([ [ 'id', 'in', $config[ 'coupon_id' ] ] ])->field('id,price')->select()->toArray();
                if (!empty($coupon_list)) {
                    foreach ($coupon_list as $item) {
                        $content[] = [
                            'text' => round($item[ 'price' ]) . "元优惠券",
                            'icon' => '/addon/shop/sign/coupon.png'
                        ];
                    }
                }
                return $content;
            },
            // 会员签到（连签）
            'member_sign_continue' => function ($config) {
                if (empty($config[ 'coupon_id' ])) {
                    return [];
                }
                $content = [];
                $model = ( new addon\shop\app\model\coupon\Coupon() );
                $coupon_list = $model->where([ [ 'id', 'in', $config[ 'coupon_id' ] ] ])->field('id,price')->select()->toArray();
                if (!empty($coupon_list)) {
                    foreach ($coupon_list as $item) {
                        $content[] = [
                            'text' => round($item[ 'price' ]) . "元优惠券",
                            'icon' => '/addon/shop/sign/coupon01.png'
                        ];
                    }
                }
                return $content;
            }
        ]
    ]
];

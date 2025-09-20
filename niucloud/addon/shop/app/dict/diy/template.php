<?php

return [
    'DIY_SHOP_INDEX' => [
        'title' => get_lang('dict_diy.page_shop_index'),
        'page' => '/addon/shop/pages/index',
        'action' => 'decorate',
        'type' => 'index'
    ],
    'DIY_SHOP_MEMBER_INDEX' => [
        'title' => get_lang('dict_diy.page_shop_member_index'),
        'page' => '/addon/shop/pages/member/index',
        'action' => 'decorate',
        'type' => 'member_index'
    ],
    'DIY_SHOP_POINT_INDEX' => [
        'title' => get_lang('dict_diy.page_shop_point_index'),
        'page' => '/addon/shop/pages/point/index',
        'action' => 'decorate',
        'type' => ''
    ],
    'DIY_SHOP_GOODS_DETAIL' => [
        'title' => get_lang('dict_diy.page_shop_detail'),
        'page' => '/addon/shop/pages/goods/detail',
        'action' => 'decorate',
        'type' => '',
        'ignoreComponents' => [ 'CarouselSearch' ], // 忽略组件名单，商品详情不支持添加轮播搜索组件
        // 页面数据结构，初始化时覆盖
        'global' => [
            'topStatusBar' => [
                'control' => false, // 隐藏顶部导航栏，禁止编辑
            ],
            'bottomTabBar' => [
                'control' => false, // 隐藏底部导航，禁止编辑
                'isShow' => false
            ],
        ],

    ],
];

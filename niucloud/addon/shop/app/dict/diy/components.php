<?php

return [
    'SHOP_COMPONENT' => [
        'title' => get_lang('dict_diy.shop_component_type_basic'),
        'list' => [
            'GoodsList' => [
                'title' => '商品列表',
                'icon' => 'iconfont iconshangpinliebiaopc',
                'path' => 'edit-goods-list',
                'support_page' => [],
                'uses' => 0,
                'sort' => 10011,
                'value' => [
                    'style' => 'style-1',
                    'source' => 'all',
                    'num' => 10,
                    'goods_category' => '',
                    "goods_category_name" => "请选择",
                    'goods_ids' => [],
                    "sortWay" => "default", // 排序方式，default：综合，sale_num：销量，price：价格
                    "goodsNameStyle" => [
                        "color" => "#303133",
                        "control" => true,
                        "fontWeight" => 'normal',
                        "isShow" => true
                    ],
                    "priceStyle" => [
                        "color" => "#FF4142",
                        "control" => true,
                        "isShow" => true
                    ],
                    "saleStyle" => [
                        "color" => "#999999",
                        "control" => true,
                        "isShow" => true
                    ],
                    "labelStyle" => [
                        "control" => true,
                        "isShow" => true
                    ],
                    "btnStyle" => [
                        "fontWeight" => false,
                        "padding" => 0,
                        "aroundRadius" => 25,
                        "cartEvent" => "detail",
                        "text" => "购买",
                        "textColor" => "#FFFFFF",
                        "startBgColor" => "#FF4142",
                        "endBgColor" => "#FF4142",
                        "style" => "button",
                        "control" => true
                    ],
                    "imgElementRounded" => 10,// 图片圆角
                ],
                // 组件属性
                'template' => [
                    "textColor" => "#303133", // 文字颜色
                    'pageStartBgColor' => '', // 底部背景颜色（开始）
                    'pageEndBgColor' => '', // 底部背景颜色（结束）
                    'pageGradientAngle' => 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）
                    'componentBgUrl' => '', // 组件背景图片
                    'componentBgAlpha' => 2, // 组件背景图片的透明度，0~10
                    "componentStartBgColor" => '', // 组件背景颜色（开始）
                    "componentEndBgColor" => '', // 组件背景颜色（结束）
                    "componentGradientAngle" => 'to bottom', // 渐变角度，上下（to bottom）、左右（to right）
                    "topRounded" => 0, // 组件上圆角
                    "bottomRounded" => 0, // 组件下圆角
                    "elementBgColor" => '', // 元素背景颜色
                    "topElementRounded" => 10,// 元素上圆角
                    "bottomElementRounded" => 10, // 元素下圆角
                    "margin" => [
                        "top" => 0, // 上边距
                        "bottom" => 0, // 下边距
                        "both" => 10 // 左右边距
                    ]
                ]
            ],
            'ShopSearch' => [
                'title' => '搜索',
                'icon' => 'iconfont iconsousuopc-1',
                'path' => 'edit-shop-search',
                'support_page' => [],
                'uses' => 1,
                'sort' => 10012,
                'value' => [
                    "searchStyle" => "style-1",
                    "searchLink" => [
                        "name" => ""
                    ],
                    "text" => "请输入搜索关键词",
                    "iconType" => "img",
                    "icon" => "",
                    "imageUrl" => ""
                ],
                // 组件属性
                'template' => [
                    "textColor" => "#303133", // 文字颜色
                    'pageStartBgColor' => '', // 底部背景颜色（开始）
                    'pageEndBgColor' => '', // 底部背景颜色（结束）
                    'pageGradientAngle' => 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）
                    'componentBgUrl' => '', // 组件背景图片
                    'componentBgAlpha' => 2, // 组件背景图片的透明度，0~10
                    "componentStartBgColor" => '', // 组件背景颜色（开始）
                    "componentEndBgColor" => '', // 组件背景颜色（结束）
                    "componentGradientAngle" => 'to bottom', // 渐变角度，上下（to bottom）、左右（to right）
                    "topRounded" => 0, // 组件上圆角
                    "bottomRounded" => 0, // 组件下圆角
                    "elementBgColor" => '', // 元素背景颜色
                    "topElementRounded" => 0,// 元素上圆角
                    "bottomElementRounded" => 0, // 元素下圆角
                    "margin" => [
                        "top" => 10, // 上边距
                        "bottom" => 10, // 下边距
                        "both" => 10 // 左右边距
                    ]
                ]
            ],
            'ManyGoodsList' => [
                'title' => '多商品组',
                'icon' => 'iconfont iconduoshangpinzupc',
                'path' => 'edit-many-goods-list',
                'support_page' => [],
                'uses' => 0,
                'sort' => 10013,
                'value' => [
                    'style' => 'style-2',
                    'num' => 6,
                    "sortWay" => "default", // 排序方式，default：综合，sale_num：销量，price：价格
                    "headStyle" => "style-1",
                    "aroundRadius" => 25,
                    "source" => "custom",
                    "goods_category" => '',
                    "goods_category_name" => '请选择',
                    "goodsNameStyle" => [
                        "color" => "#303133",
                        "control" => true,
                        "fontWeight" => 'normal',
                        "isShow" => true
                    ],
                    "priceStyle" => [
                        "color" => "#FF4142",
                        "control" => true,
                        "isShow" => true
                    ],
                    "saleStyle" => [
                        "color" => "#999999",
                        "control" => true,
                        "isShow" => true
                    ],
                    "labelStyle" => [
                        "control" => true,
                        "isShow" => true
                    ],
                    "btnStyle" => [
                        "fontWeight" => false,
                        "padding" => 0,
                        "aroundRadius" => 25,
                        "cartEvent" => "detail",
                        "text" => "购买",
                        "textColor" => "#FFFFFF",
                        "startBgColor" => "#FF4142",
                        "endBgColor" => "#FF4142",
                        "style" => "button",
                        "control" => true
                    ],
                    "list" => [
                        [
                            "title" => "推荐",
                            "desc" => "猜你喜欢",
                            "source" => "all",
                            "goods_category" => '',
                            "goods_category_name" => '请选择',
                            "goods_ids" => [],
                            "imageUrl" => ''
                        ]
                    ],
                    "imgElementRounded" => 0 // 图片圆角
                ],
                // 组件属性
                'template' => [
                    "textColor" => "#303133", // 文字颜色
                    'pageStartBgColor' => '', // 底部背景颜色（开始）
                    'pageEndBgColor' => '', // 底部背景颜色（结束）
                    'pageGradientAngle' => 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）
                    'componentBgUrl' => '', // 组件背景图片
                    'componentBgAlpha' => 2, // 组件背景图片的透明度，0~10
                    "componentStartBgColor" => '', // 组件背景颜色（开始）
                    "componentEndBgColor" => '', // 组件背景颜色（结束）
                    "componentGradientAngle" => 'to bottom', // 渐变角度，上下（to bottom）、左右（to right）
                    "topRounded" => 0, // 组件上圆角
                    "bottomRounded" => 0, // 组件下圆角
                    "elementBgColor" => '', // 元素背景颜色
                    "topElementRounded" => 0,// 元素上圆角
                    "bottomElementRounded" => 0, // 元素下圆角
                    "margin" => [
                        "top" => 0, // 上边距
                        "bottom" => 0, // 下边距
                        "both" => 10 // 左右边距
                    ]
                ]
            ],
            'GoodsCoupon' => [
                'title' => '优惠券',
                'icon' => 'iconfont iconyouhuiquanpc',
                'path' => 'edit-goods-coupon',
                'support_page' => [],
                'uses' => 0,
                'sort' => 10014,
                'value' => [
                    'style' => 'style-1',
                    "styleName" => "风格一",
                    'source' => 'all',
                    'num' => 6,
                    'couponIds' => [],
                    "btnText" => "立即领取",
                    'couponTitle' => '先领券 再购物',
                    'couponSubTitle' => '领券下单 享购物优惠',
                    "titleColor" => "#ffffff",
                    "subTitleColor" => "#ffffff",
                    "couponItem" => [
                        "bgColor" => "#ffffff",
                        "textColor" => "#333333",
                        "subTextColor" => "#666666",
                        "moneyColor" => "#333333",
                        "aroundRadius" => 12
                    ]
                ],
                // 组件属性
                'template' => [
                    "textColor" => "#303133", // 文字颜色
                    'pageStartBgColor' => '', // 底部背景颜色（开始）
                    'pageEndBgColor' => '', // 底部背景颜色（结束）
                    'pageGradientAngle' => 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）
                    'componentBgUrl' => '', // 组件背景图片
                    'componentBgAlpha' => 2, // 组件背景图片的透明度，0~10
                    "componentStartBgColor" => '', // 组件背景颜色（开始）
                    "componentEndBgColor" => '', // 组件背景颜色（结束）
                    "componentGradientAngle" => 'to bottom', // 渐变角度，上下（to bottom）、左右（to right）
                    "topRounded" => 0, // 组件上圆角
                    "bottomRounded" => 0, // 组件下圆角
                    "elementBgColor" => '', // 元素背景颜色
                    "topElementRounded" => 0,// 元素上圆角
                    "bottomElementRounded" => 0, // 元素下圆角
                    "margin" => [
                        "top" => 10, // 上边距
                        "bottom" => 10, // 下边距
                        "both" => 10 // 左右边距
                    ]
                ]
            ],
            'ShopMemberInfo' => [
                'title' => '会员信息',
                'icon' => 'iconfont iconhuiyuanqiandaopc',
                'path' => 'edit-shop-member-info',
                'support_page' => [ 'DIY_SHOP_MEMBER_INDEX', 'DIY_SHOP_GIFTCARD_MEMBER_INDEX' ],
                'uses' => 1,
                'sort' => 10015,
                'value' => [
                    "style" => "style-1",
                    "styleName" => "风格1",
                    'bgUrl' => '',
                    'isShowAccount' => true,
                    'uidTextColor' => '#666666',
                    'accountTextColor' => '#666666'
                ],
            ],
            'ShopOrderInfo' => [
                'title' => '订单中心',
                'icon' => 'iconfont icondingdanzhongxinPC-1',
                'path' => 'edit-shop-order-info',
                'support_page' => [ 'DIY_SHOP_MEMBER_INDEX' ],
                'uses' => 1,
                'sort' => 10016,
                'value' => [
                    "textColor" => "#303133",
                    "fontSize" => 16,
                    "fontWeight" => "normal",
                    "text" => "订单中心",
                    "more" => [
                        "text" => "全部订单",
                        "color" => "#999999",
                    ],
                    "item" => [
                        "fontSize" => 12,
                        "fontWeight" => "normal",
                        "color" => "#303133"
                    ],
                ]
            ],
            'ShopExchangeInfo' => [
                'title' => '积分兑换',
                'icon' => 'iconfont iconjifenpc',
                'path' => 'edit-shop-exchange-info',
                'support_page' => [],
                'uses' => 0,
                'sort' => 10017,
                'value' => [
                    'bgUrl' => 'addon/shop/diy/point/point_index_bg.jpg',
                ],
            ],
            'ShopExchangeGoods' => [
                'title' => '积分商品',
                'icon' => 'iconfont iconjifenshangpinpc',
                'path' => 'edit-shop-exchange-goods',
                'support_page' => [],
                'uses' => 0,
                'sort' => 10018,
                'value' => [
                    'style' => 'style-2',
                    'source' => 'all',
                    'num' => 10,
                    'goods_category' => '',
                    "goods_category_name" => "请选择",
                    'goods_ids' => [],
                    "sortWay" => "total_order_num", // 排序方式，total_order_num：综合，total_exchange_num：销量，price：价格
                    "goodsNameStyle" => [
                        "color" => "#333",
                        "control" => true,
                        "fontWeight" => 'normal'
                    ],
                    "priceStyle" => [
                        "mainColor" => "#FF4142",
                        "mainControl" => true,
                        "lineColor" => "#999CA7",
                        "lineControl" => true
                    ],
                    "saleStyle" => [
                        "color" => "#999999",
                        "control" => true
                    ],
                    "imgElementRounded" => 10,// 图片圆角
                ],
                // 组件属性
                'template' => [
                    "textColor" => "#303133", // 文字颜色
                    'pageStartBgColor' => '', // 底部背景颜色（开始）
                    'pageEndBgColor' => '', // 底部背景颜色（结束）
                    'pageGradientAngle' => 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）
                    'componentBgUrl' => '', // 组件背景图片
                    'componentBgAlpha' => 2, // 组件背景图片的透明度，0~10
                    "componentStartBgColor" => '', // 组件背景颜色（开始）
                    "componentEndBgColor" => '', // 组件背景颜色（结束）
                    "componentGradientAngle" => 'to bottom', // 渐变角度，上下（to bottom）、左右（to right）
                    "topRounded" => 0, // 组件上圆角
                    "bottomRounded" => 0, // 组件下圆角
                    "elementBgColor" => '', // 元素背景颜色
                    "topElementRounded" => 0,// 元素上圆角
                    "bottomElementRounded" => 0, // 元素下圆角
                    "margin" => [
                        "top" => 0, // 上边距
                        "bottom" => 0, // 下边距
                        "both" => 10 // 左右边距
                    ],
                ]
            ],
            'ShopGoodsRecommend' => [
                'title' => '商品推荐',
                'icon' => 'iconfont icona-shangpintuijianpc30',
                'path' => 'edit-shop-goods-recommend',
                'support_page' => [],
                'uses' => 0,
                'sort' => 10019,
                'value' => [
                    "priceStyle" => [
                        "mainColor" => "#333333",
                    ],
                    'source' => 'all',
                    "goods_ids" => [],
                    'list' => [
                        [
                            "title" => [
                                "text" => "今日推荐",
                                "textColor" => "#303133"
                            ],
                            "moreTitle" => [
                                "text" => "精选",
                                "textColor" => "#FFFFFF",
                                "startColor" => "#FF7234",
                                "endColor" => "#FF213F",
                            ],
                            "listFrame" => [
                                "startColor" => "#FFE5E5",
                                "endColor" => "#FFF5F0",
                            ],
                            "button" => [
                                "text" => "首单",
                                "textColor" => "#FFFFFF",
                                "color" => "#FF1128",
                            ]
                        ],
                        [
                            "title" => [
                                "text" => "品质好物",
                                "textColor" => "#303133"
                            ],
                            "moreTitle" => [
                                "text" => "精选",
                                "textColor" => "#FFFFFF",
                                "startColor" => "#F2C719",
                                "endColor" => "#FBBA08",
                            ],
                            "listFrame" => [
                                "startColor" => "#FFEFBA",
                                "endColor" => "#FFF5D7",
                            ],
                            "button" => [
                                "text" => "首单",
                                "textColor" => "#FFFFFF",
                                "color" => "#FF1128",
                            ]
                        ],
                        [
                            "title" => [
                                "text" => "热销爆款",
                                "textColor" => "#303133"
                            ],
                            "moreTitle" => [
                                "text" => "精选",
                                "textColor" => "#FFFFFF",
                                "startColor" => "#FFA629",
                                "endColor" => "#FF8E1E",
                            ],
                            "listFrame" => [
                                "startColor" => "#FFE4D9",
                                "endColor" => "#FFFBF9",
                            ],
                            "button" => [
                                "text" => "首单",
                                "textColor" => "#FFFFFF",
                                "color" => "#FF1128",
                            ]
                        ]
                    ],
                    "imgElementRounded" => 10,// 图片圆角
                ],
                // 组件属性
                'template' => [
                    "textColor" => "#303133", // 文字颜色
                    'pageStartBgColor' => '', // 底部背景颜色（开始）
                    'pageEndBgColor' => '', // 底部背景颜色（结束）
                    'pageGradientAngle' => 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）
                    'componentBgUrl' => '', // 组件背景图片
                    'componentBgAlpha' => 2, // 组件背景图片的透明度，0~10
                    "componentStartBgColor" => '', // 组件背景颜色（开始）
                    "componentEndBgColor" => '', // 组件背景颜色（结束）
                    "componentGradientAngle" => 'to bottom', // 渐变角度，上下（to bottom）、左右（to right）
                    "topRounded" => 0, // 组件上圆角
                    "bottomRounded" => 0, // 组件下圆角
                    "elementBgColor" => '', // 元素背景颜色
                    "topElementRounded" => 10,// 元素上圆角
                    "bottomElementRounded" => 10, // 元素下圆角
                    "margin" => [
                        "top" => 10, // 上边距
                        "bottom" => 10, // 下边距
                        "both" => 10 // 左右边距
                    ],
                ]
            ],
            'SingleRecommend' => [
                'title' => '精选推荐',
                'icon' => 'iconfont icona-jingxuantuijianpc30-12',
                'path' => 'edit-single-recommend',
                'support_page' => [],
                'uses' => 0,
                'sort' => 10019,
                'value' => [
                    "titleStyle" => [
                        'title' => '风格1',
                        'value' => 'style-1'
                    ],
                    'textImg' => 'addon/shop/diy/index/style3/single_recommend_text1.png',
                    "textLink" => [
                        "name" => ""
                    ],
                    "titleColor" => "#999999",
                    "subTitle" => [
                        "text" => "更多",
                        "textColor" => "#999999",
                        "link" => [
                            "name" => ""
                        ]
                    ],
                    'source' => 'all',
                    'goods_ids' => [],
                    "imageHeight" => 250,
                    "list" => [
                        [
                            "link" => [
                                "name" => ""
                            ],
                            "imageUrl" => "addon/shop/diy/index/style3/single_recommend_banner1.jpg",
                            "imgWidth" => 345,
                            "imgHeight" => 495
                        ],
                        [
                            "link" => [
                                "name" => ""
                            ],
                            "imageUrl" => "addon/shop/diy/index/style3/single_recommend_banner2.jpg",
                            "imgWidth" => 345,
                            "imgHeight" => 495
                        ]
                    ],
                    "goodsNameStyle" => [
                        "color" => "#303133",
                        "control" => true,
                        "fontWeight" => 'normal'
                    ],
                    "priceStyle" => [
                        "mainColor" => "#FF4142",
                        "mainControl" => true,
                        "lineColor" => "#999CA7",
                        "lineControl" => true
                    ],
                    "saleStyle" => [
                        "color" => "#FF0000",
                        "control" => true
                    ],
                    'topCarouselRounded' => 12,
                    'bottomCarouselRounded' => 12,
                    'indicatorColor' => 'rgba(255, 255, 255, 0.6)',
                    'indicatorActiveColor' => '#ffffff',
                ],
                // 组件属性
                'template' => [
                    "textColor" => "#303133", // 文字颜色
                    'pageStartBgColor' => '', // 底部背景颜色（开始）
                    'pageEndBgColor' => '', // 底部背景颜色（结束）
                    'pageGradientAngle' => 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）
                    'componentBgUrl' => '', // 组件背景图片
                    'componentBgAlpha' => 2, // 组件背景图片的透明度，0~10
                    "componentStartBgColor" => '', // 组件背景颜色（开始）
                    "componentEndBgColor" => '', // 组件背景颜色（结束）
                    "componentGradientAngle" => 'to bottom', // 渐变角度，上下（to bottom）、左右（to right）
                    "topRounded" => 0, // 组件上圆角
                    "bottomRounded" => 0, // 组件下圆角
                    "elementBgColor" => '', // 元素背景颜色
                    "topElementRounded" => 0,// 元素上圆角
                    "bottomElementRounded" => 0, // 元素下圆角
                    "margin" => [
                        "top" => 10, // 上边距
                        "bottom" => 10, // 下边距
                        "both" => 10 // 左右边距
                    ]
                ]
            ],
            'ShopNewcomer' => [
                'title' => '新人专享',
                'icon' => 'iconfont icona-xinrenzhuanxiangpc30',
                'path' => 'edit-shop-newcomer',
                'support_page' => [],
                'uses' => 1,
                'sort' => 10020,
                'value' => [
                    "style" => [
                        'title' => '风格1',
                        'value' => 'style-1'
                    ],
                    'textImg' => 'addon/shop/diy/newcomer/style_title_01.png',
                    "subTitle" => [
                        "text" => "查看更多",
                        "textColor" => "#FFFFFF",
                        "startColor" => "#FB792F",
                        "endColor" => "#F91700",
                        "link" => [
                            "name" => ""
                        ],
                    ],
                    "countDown" => [
                        "numberColor" => "rgba(255, 0, 0, 1)",
                        "numberBg" => [
                            "startColor" => "rgba(255, 255, 255, 1)",
                            "endColor" => ""
                        ],
                        "otherColor" => "rgba(255, 255, 255, 1)"
                    ],
                    'source' => 'all',
                    'num' => 10,
                    'goods_category' => '',
                    "goods_category_name" => "请选择",
                    'goods_ids' => [],
                    "imgElementRounded" => 10 // 图片圆角
                ],
                // 组件属性
                'template' => [
                    "textColor" => "#303133", // 文字颜色
                    'pageStartBgColor' => '', // 底部背景颜色（开始）
                    'pageEndBgColor' => '', // 底部背景颜色（结束）
                    'pageGradientAngle' => 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）
                    "componentStartBgColor" => '#ff6D1A', // 组件背景颜色（开始）
                    "componentEndBgColor" => 'rgba(255, 70, 56, 1)', // 组件背景颜色（结束）
                    "componentGradientAngle" => 'to right', // 渐变角度，上下（to bottom）、左右（to right）
                    "topRounded" => 12, // 组件上圆角
                    "bottomRounded" => 12, // 组件下圆角
                    "elementBgColor" => '', // 元素背景颜色
                    "topElementRounded" => 10,// 元素上圆角
                    "bottomElementRounded" => 10, // 元素下圆角
                    "margin" => [
                        "top" => 10, // 上边距
                        "bottom" => 10, // 下边距
                        "both" => 10 // 左右边距
                    ]
                ]
            ],
            'ShopGoodsRanking' => [
                'title' => '排行榜',
                'icon' => 'iconfont icona-paihangbangpc30',
                'path' => 'edit-shop-goods-ranking',
                'support_page' => [],
                'uses' => 0,
                'sort' => 10021,
                'value' => [
                    "list" => [
                        [
                            'bgUrl' => 'addon/shop/rank/rank_bg_01.jpg', // 榜单背景图
                            'text' => '热销排行榜',
                            "textColor" => "#FFFFFF",
                            "imgUrl" => "addon/shop/rank/rank_trophy.png", // 图标
                            'subTitle' => [
                                'text' => '查看更多',
                                'textColor' => '#FFFFFF',
                                'link' => [
                                    'name' => 'SHOP_GOODS_RANK',
                                    "parent" => "SHOP_LINK",
                                    'title' => '商品排行榜',
                                    'url' => '/addon/shop/pages/goods/rank',
                                    'action' => '',
                                ]
                            ],
                            'listFrame' => [
                                'startColor' => '#FEA715',
                                'endColor' => '#FE1E00',
                            ],
                            'source' => 'default',
                            'rankIds' => []
                        ],
                        [
                            'bgUrl' => 'addon/shop/rank/rank_bg_02.jpg', // 榜单背景图
                            'text' => '人气排行榜',
                            "textColor" => "#FFFFFF",
                            "imgUrl" => "addon/shop/rank/rank_top.png", // 图标
                            'subTitle' => [
                                'text' => '查看更多',
                                'textColor' => '#FFFFFF',
                                'link' => [
                                    'name' => 'SHOP_GOODS_RANK',
                                    "parent" => "SHOP_LINK",
                                    'title' => '商品排行榜',
                                    'url' => '/addon/shop/pages/goods/rank',
                                    'action' => '',
                                ]
                            ],
                            'listFrame' => [
                                'startColor' => '#FEA715',
                                'endColor' => '#FE1E00',
                            ],
                            'source' => 'default',
                            'rankIds' => []
                        ]
                    ],
                ],
                // 组件属性
                'template' => [
                    "textColor" => "#303133", // 文字颜色
                    'pageStartBgColor' => '', // 底部背景颜色（开始）
                    'pageEndBgColor' => '', // 底部背景颜色（结束）
                    'pageGradientAngle' => 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）
                    'componentBgUrl' => '', // 组件背景图片
                    'componentBgAlpha' => 2, // 组件背景图片的透明度，0~10
                    "componentStartBgColor" => '', // 组件背景颜色（开始）
                    "componentEndBgColor" => '', // 组件背景颜色（结束）
                    "componentGradientAngle" => 'to bottom', // 渐变角度，上下（to bottom）、左右（to right）
                    "topRounded" => 0, // 组件上圆角
                    "bottomRounded" => 0, // 组件下圆角
                    "elementBgColor" => '', // 元素背景颜色
                    "topElementRounded" => 10,// 元素上圆角
                    "bottomElementRounded" => 10, // 元素下圆角
                    "margin" => [
                        "top" => 10, // 上边距
                        "bottom" => 10, // 下边距
                        "both" => 10 // 左右边距
                    ]
                ]
            ]
        ]
    ],
    'SHOP_GOODS_DETAIL_COMPONENT' => [
        'title' => get_lang('dict_diy.shop_goods_detail_component_type_basic'),
        'list' => [
            'ShopGoodsDetailBasicInfo' => [
                'title' => '基础信息',
                'icon' => 'iconfont iconjichuxinxi',
                'path' => 'edit-shop-goods-detail-basic-info',
                'support_page' => [ 'DIY_SHOP_GOODS_DETAIL' ],
                'uses' => 1,
                'sort' => 10011,
                'position' => 'top_fixed', // 组件置顶标识，不能拖拽
                'value' => [
                    'menuContent' => 'index,search,cart,member',
                    'medium' => [
                        'type' => 'square_img',
                        'indicator' => true
                    ],
                    'priceRegion' => [
                        'showWay' => 'normal',
                        'bgImg' => 'addon/shop/diy/goods_detail/style_01.jpg',
                        'marketingBgImg' => 'addon/shop/diy/goods_detail/marketing_style_01.jpg',
                        'goodsStyle' => [
                            'title' => '风格1',
                            'value' => 'style-1'
                        ],
                        'marketingStyle' => [
                            'title' => '风格1',
                            'value' => 'style-1'
                        ]
                    ],
                    'goodsInfo' => [
                        'titleColor' => '#333333',
                        'subTitleColor' => '#999999',
                        'saleInfoColor' => '#999999',
                        'priceTopRounded' => 20,
                        'priceBottomRounded' => 0,
                        'topRounded' => 20,
                        'bottomRounded' => 0,
                        "priceBgColor" => '',
                        "startBgColor" => '#ffffff',
                        "endBgColor" => '#f6f6f6',
                        "priceAboutMargin" => 0,
                        "priceTopMargin" => -22,
                        "topMargin" => -17,
                        "aboutMargin" => 0
                    ],
                    'saleInfo' => ['underlined_price','sales','stock']
                ],
                // 组件属性
                'template' => [
                    "textColor" => "#303133", // 文字颜色
                    'pageStartBgColor' => '', // 底部背景颜色（开始）
                    'pageEndBgColor' => '', // 底部背景颜色（结束）
                    'pageGradientAngle' => 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）
                    'componentBgUrl' => '', // 组件背景图片
                    'componentBgAlpha' => 2, // 组件背景图片的透明度，0~10
                    "componentStartBgColor" => '', // 组件背景颜色（开始）
                    "componentEndBgColor" => '', // 组件背景颜色（结束）
                    "componentGradientAngle" => 'to bottom', // 渐变角度，上下（to bottom）、左右（to right）
                    "topRounded" => 0, // 组件上圆角
                    "bottomRounded" => 0, // 组件下圆角
                    "elementBgColor" => '', // 元素背景颜色
                    "topElementRounded" => 0,// 元素上圆角
                    "bottomElementRounded" => 0, // 元素下圆角
                    "margin" => [
                        "top" => 0, // 上边距
                        "bottom" => 0, // 下边距
                        "both" => 0 // 左右边距
                    ]
                ]
            ],
            'ShopGoodsDetailPurchaseService' => [
                'title' => '商品服务',
                'icon' => 'iconfont iconshangpinfuwu1',
                'path' => 'edit-shop-goods-detail-purchase-service',
                'support_page' => [ 'DIY_SHOP_GOODS_DETAIL' ],
                'uses' => 1,
                'sort' => 10012,
                'value' => [
                    'serviceConfig' => ['goods_service','spec_select','delivery_info','coupons','activity']
                ],
                // 组件属性
                'template' => [
                    "textColor" => "#303133", // 文字颜色
                    'pageStartBgColor' => '', // 底部背景颜色（开始）
                    'pageEndBgColor' => '', // 底部背景颜色（结束）
                    'pageGradientAngle' => 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）
                    'componentBgUrl' => '', // 组件背景图片
                    'componentBgAlpha' => 2, // 组件背景图片的透明度，0~10
                    "componentStartBgColor" => '#FFFFFF', // 组件背景颜色（开始）
                    "componentEndBgColor" => '', // 组件背景颜色（结束）
                    "componentGradientAngle" => 'to bottom', // 渐变角度，上下（to bottom）、左右（to right）
                    "topRounded" => 12, // 组件上圆角
                    "bottomRounded" => 12, // 组件下圆角
                    "elementBgColor" => '', // 元素背景颜色
                    "topElementRounded" => 0,// 元素上圆角
                    "bottomElementRounded" => 0, // 元素下圆角
                    "margin" => [
                        "top" => 12, // 上边距
                        "bottom" => 0, // 下边距
                        "both" => 10 // 左右边距
                    ]
                ]
            ],
            'ShopGoodsDetailEvaluate' => [
                'title' => '商品评价',
                'icon' => 'iconfont iconshangpinpingjia1',
                'path' => 'edit-shop-goods-detail-evaluate',
                'support_page' => [ 'DIY_SHOP_GOODS_DETAIL' ],
                'uses' => 1,
                'sort' => 10014,
                'value' => [
                    'isShow' => true
                ],
                // 组件属性
                'template' => [
                    "textColor" => "#303133", // 文字颜色
                    'pageStartBgColor' => '', // 底部背景颜色（开始）
                    'pageEndBgColor' => '', // 底部背景颜色（结束）
                    'pageGradientAngle' => 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）
                    'componentBgUrl' => '', // 组件背景图片
                    'componentBgAlpha' => 2, // 组件背景图片的透明度，0~10
                    "componentStartBgColor" => '#FFFFFF', // 组件背景颜色（开始）
                    "componentEndBgColor" => '', // 组件背景颜色（结束）
                    "componentGradientAngle" => 'to bottom', // 渐变角度，上下（to bottom）、左右（to right）
                    "topRounded" => 12, // 组件上圆角
                    "bottomRounded" => 12, // 组件下圆角
                    "elementBgColor" => '', // 元素背景颜色
                    "topElementRounded" => 0,// 元素上圆角
                    "bottomElementRounded" => 0, // 元素下圆角
                    "margin" => [
                        "top" => 12, // 上边距
                        "bottom" => 0, // 下边距
                        "both" => 10 // 左右边距
                    ]
                ]
            ],
            'ShopGoodsDetailAttr' => [
                'title' => '商品属性',
                'icon' => 'iconfont iconshangpinshuxing',
                'path' => 'edit-shop-goods-detail-attr',
                'support_page' => [ 'DIY_SHOP_GOODS_DETAIL' ],
                'uses' => 1,
                'sort' => 10015,
                'value' => [
                    'isShow' => true
                ],
                // 组件属性
                'template' => [
                    "textColor" => "#303133", // 文字颜色
                    'pageStartBgColor' => '', // 底部背景颜色（开始）
                    'pageEndBgColor' => '', // 底部背景颜色（结束）
                    'pageGradientAngle' => 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）
                    'componentBgUrl' => '', // 组件背景图片
                    'componentBgAlpha' => 2, // 组件背景图片的透明度，0~10
                    "componentStartBgColor" => '#FFFFFF', // 组件背景颜色（开始）
                    "componentEndBgColor" => '', // 组件背景颜色（结束）
                    "componentGradientAngle" => 'to bottom', // 渐变角度，上下（to bottom）、左右（to right）
                    "topRounded" => 12, // 组件上圆角
                    "bottomRounded" => 12, // 组件下圆角
                    "elementBgColor" => '', // 元素背景颜色
                    "topElementRounded" => 0,// 元素上圆角
                    "bottomElementRounded" => 0, // 元素下圆角
                    "margin" => [
                        "top" => 12, // 上边距
                        "bottom" => 0, // 下边距
                        "both" => 10 // 左右边距
                    ]
                ]
            ],
            'ShopGoodsDetailDesc' => [
                'title' => '商品详情',
                'icon' => 'iconfont iconshangpinxiangqing',
                'path' => 'edit-shop-goods-detail-desc',
                'support_page' => [ 'DIY_SHOP_GOODS_DETAIL' ],
                'uses' => 1,
                'sort' => 10016,
                'value' => [
                    'isShow' => true
                ],
                // 组件属性
                'template' => [
                    "textColor" => "#303133", // 文字颜色
                    'pageStartBgColor' => '', // 底部背景颜色（开始）
                    'pageEndBgColor' => '', // 底部背景颜色（结束）
                    'pageGradientAngle' => 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）
                    'componentBgUrl' => '', // 组件背景图片
                    'componentBgAlpha' => 2, // 组件背景图片的透明度，0~10
                    "componentStartBgColor" => '#FFFFFF', // 组件背景颜色（开始）
                    "componentEndBgColor" => '', // 组件背景颜色（结束）
                    "componentGradientAngle" => 'to bottom', // 渐变角度，上下（to bottom）、左右（to right）
                    "topRounded" => 12, // 组件上圆角
                    "bottomRounded" => 12, // 组件下圆角
                    "elementBgColor" => '', // 元素背景颜色
                    "topElementRounded" => 0,// 元素上圆角
                    "bottomElementRounded" => 0, // 元素下圆角
                    "margin" => [
                        "top" => 12, // 上边距
                        "bottom" => 20, // 下边距
                        "both" => 10 // 左右边距
                    ]
                ]
            ],
            'ShopGoodsDetailBottom' => [
                'title' => '底部菜单',
                'icon' => 'iconfont icondibucaidan',
                'path' => 'edit-shop-goods-detail-bottom',
                'support_page' => [ 'DIY_SHOP_GOODS_DETAIL' ],
                'position' => 'bottom_fixed', // 组件置底标识，不能拖拽
                'uses' => 1,
                'sort' => 10017,
                'value' => [
                    'menuContent' => ['index','service','cart'],
                    'cartName' => '加入购物车',
                    'buyName' => '立即购买',
                    'cartIsShow' => true,
                    'cartStyle' => [
                        "textColor" => "#FFFFFF", // 文字颜色
                        "fontSize" => 16,
                        "gradientAngle" => "to right",
                        "startColor" => "#FFB000",
                        "endColor" => "#FFA029"
                    ],
                    'buyStyle' => [
                        "textColor" => "#FFFFFF", // 文字颜色
                        "fontSize" => 16,
                        "gradientAngle" => "to right",
                        "startColor" => "#FB7939",
                        "endColor" => "#FF4142"
                    ],
                ],
                // 组件属性
                'template' => [
                    "textColor" => "#303133", // 文字颜色
                    'pageStartBgColor' => '', // 底部背景颜色（开始）
                    'pageEndBgColor' => '', // 底部背景颜色（结束）
                    'pageGradientAngle' => 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）
                    'componentBgUrl' => '', // 组件背景图片
                    'componentBgAlpha' => 2, // 组件背景图片的透明度，0~10
                    "componentStartBgColor" => '#FFFFFF', // 组件背景颜色（开始）
                    "componentEndBgColor" => '', // 组件背景颜色（结束）
                    "componentGradientAngle" => 'to bottom', // 渐变角度，上下（to bottom）、左右（to right）
                    "topRounded" => 0, // 组件上圆角
                    "bottomRounded" => 0, // 组件下圆角
                    "elementBgColor" => '', // 元素背景颜色
                    "topElementRounded" => 0,// 元素上圆角
                    "bottomElementRounded" => 0, // 元素下圆角
                    "margin" => [
                        "top" => 0, // 上边距
                        "bottom" => 0, // 下边距
                        "both" => 0 // 左右边距
                    ]
                ]
            ],
            'ShopGoodsDetailSow' => [
                'title' => '种草秀',
                'icon' => 'iconfont iconzhongcaoxiu',
                'path' => 'edit-shop-goods-detail-sow',
                'support_page' => [ 'DIY_SHOP_GOODS_DETAIL' ],
                'uses' => 0,
                'sort' => 10018,
                'value' => [
                    'isShow' => true
                ],
                // 组件属性
                'template' => [
                    "textColor" => "#303133", // 文字颜色
                    'pageStartBgColor' => '', // 底部背景颜色（开始）
                    'pageEndBgColor' => '', // 底部背景颜色（结束）
                    'pageGradientAngle' => 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）
                    'componentBgUrl' => '', // 组件背景图片
                    'componentBgAlpha' => 2, // 组件背景图片的透明度，0~10
                    "componentStartBgColor" => '#FFFFFF', // 组件背景颜色（开始）
                    "componentEndBgColor" => '', // 组件背景颜色（结束）
                    "componentGradientAngle" => 'to bottom', // 渐变角度，上下（to bottom）、左右（to right）
                    "topRounded" => 12, // 组件上圆角
                    "bottomRounded" => 12, // 组件下圆角
                    "elementBgColor" => '', // 元素背景颜色
                    "topElementRounded" => 0,// 元素上圆角
                    "bottomElementRounded" => 0, // 元素下圆角
                    "margin" => [
                        "top" => 12, // 上边距
                        "bottom" => 0, // 下边距
                        "both" => 10 // 左右边距
                    ]
                ]
            ]
        ]
    ],
];

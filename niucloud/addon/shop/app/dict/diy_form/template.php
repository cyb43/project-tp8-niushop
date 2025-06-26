<?php

return [
    'DIY_FORM_GOODS_DETAIL' => [
        'shop_goods_detail_form_data' => [ // 页面标识
            "title" => "商品表单", // 页面名称
            'cover' => '', // 页面封面图
            'preview' => '', // 页面预览图
            'desc' => '', // 页面描述
            // 页面数据源
            "data" => [
                "global" => [
                    "title" => "商品表单",
                    "pageStartBgColor" => "",
                    "pageEndBgColor" => "",
                    "pageGradientAngle" => "to bottom",
                    "bgUrl" => "",
                    "bgHeightScale" => 100,
                    "imgWidth" => "",
                    "imgHeight" => "",
                    "topStatusBar" => [
                        "isShow" => true,
                        "bgColor" => "#ffffff",
                        "style" => "style-1",
                        "textColor" => "#333333",
                        "textAlign" => "center",
                        "rollBgColor" => "#ffffff",
                        "styleName" => "风格1",
                        "rollTextColor" => "#333333",
                        "inputPlaceholder" => "请输入搜索关键词",
                        "imgUrl" => "",
                        "link" => [
                            "name" => ""
                        ]
                    ],
                    "bottomTabBarSwitch" => true,
                    "popWindow" => [
                        "imgUrl" => "",
                        "imgWidth" => "",
                        "imgHeight" => "",
                        "count" => 'once',
                        "show" => 0,
                        "link" => [
                            "name" => ""
                        ]
                    ],
                    "template" => [
                        "textColor" => "#303133",
                        "pageStartBgColor" => "",
                        "pageEndBgColor" => "",
                        "pageGradientAngle" => "to bottom",
                        "componentBgUrl" => "",
                        "componentBgAlpha" => 2,
                        "componentStartBgColor" => "",
                        "componentEndBgColor" => "",
                        "componentGradientAngle" => "to bottom",
                        "topRounded" => 0,
                        "bottomRounded" => 0,
                        "elementBgColor" => "",
                        "topElementRounded" => 0,
                        "bottomElementRounded" => 0,
                        "margin" => [
                            "top" => 0,
                            "bottom" => 0,
                            "both" => 0
                        ],
                        "isHidden" => false
                    ],
                    "completeLayout" => "style-2",
                    "completeAlign" => "left",
                    "borderControl" => false,
                ],
                "value" => [
                    [
                        "path" => "edit-form-input",
                        "uses" => 0,
                        "id" => "4n7ik4t1dsa0",
                        "componentName" => "FormInput",
                        "componentTitle" => "单行文本",
                        "ignore" => [
                            "componentBgUrl"
                        ],
                        "textColor" => "#303133",
                        "pageStartBgColor" => "#FFFFFF",
                        "pageEndBgColor" => "",
                        "pageGradientAngle" => "to bottom",
                        "componentBgUrl" => "",
                        "componentBgAlpha" => 2,
                        "componentEndBgColor" => "",
                        "componentGradientAngle" => "to bottom",
                        "topRounded" => 0,
                        "bottomRounded" => 0,
                        "elementBgColor" => "",
                        "topElementRounded" => 0,
                        "bottomElementRounded" => 0,
                        "margin" => [
                            "top" => 0,
                            "bottom" => 0,
                            "both" => 0
                        ],
                        "fontSize" => 14,
                        "fontWeight" => "normal",
                        "position" => "",
                        "componentType" => "diy_form",
                        "field" => [
                            "name" => "姓名",
                            "remark" => [
                                "text" => "",
                                "color" => "#999999",
                                "fontSize" => 14
                            ],
                            "required" => false,
                            "unique" => false,
                            "autofill" => false,
                            "privacyProtection" => false,
                            'cache' => true,
                            "default" => "",
                            "value" => ""
                        ],
                        "placeholder" => "请输入姓名",
                        "componentStartBgColor" => "",
                        "isHidden" => false
                    ],
                    [
                        "path" => "edit-form-mobile",
                        "uses" => 1,
                        "componentType" => "diy_form",
                        "id" => "1dufay5r2h7k",
                        "componentName" => "FormMobile",
                        "componentTitle" => "手机号",
                        "ignore" => [
                            "componentBgUrl"
                        ],
                        "field" => [
                            "name" => "联系电话",
                            "remark" => [
                                "text" => "",
                                "color" => "#999999",
                                "fontSize" => 14
                            ],
                            "required" => false,
                            "unique" => true,
                            "autofill" => false,
                            "privacyProtection" => false,
                            'cache' => true,
                            "default" => "",
                            "value" => ""
                        ],
                        "placeholder" => "请输入联系电话",
                        "fontSize" => 14,
                        "fontWeight" => "normal",
                        "textColor" => "#303133",
                        "pageStartBgColor" => "#FFFFFF",
                        "pageEndBgColor" => "",
                        "pageGradientAngle" => "to bottom",
                        "componentBgUrl" => "",
                        "componentBgAlpha" => 2,
                        "componentStartBgColor" => "",
                        "componentEndBgColor" => "",
                        "componentGradientAngle" => "to bottom",
                        "topRounded" => 0,
                        "bottomRounded" => 0,
                        "elementBgColor" => "",
                        "topElementRounded" => 0,
                        "bottomElementRounded" => 0,
                        "margin" => [
                            "top" => 0,
                            "bottom" => 0,
                            "both" => 0
                        ],
                        "isHidden" => false
                    ],
                    [
                        "path" => "edit-form-identity",
                        "uses" => 1,
                        "componentType" => "diy_form",
                        "id" => "46n4aaxy0uc0",
                        "componentName" => "FormIdentity",
                        "componentTitle" => "身份证号",
                        "ignore" => [
                            "componentBgUrl"
                        ],
                        "field" => [
                            "name" => "身份证号",
                            "remark" => [
                                "text" => "",
                                "color" => "#999999",
                                "fontSize" => 14
                            ],
                            "required" => false,
                            "unique" => false,
                            "autofill" => false,
                            "privacyProtection" => false,
                            'cache' => true,
                            "default" => "",
                            "value" => ""
                        ],
                        "placeholder" => "请输入身份证号",
                        "fontSize" => 14,
                        "fontWeight" => "normal",
                        "textColor" => "#303133",
                        "pageStartBgColor" => "#FFFFFF",
                        "pageEndBgColor" => "",
                        "pageGradientAngle" => "to bottom",
                        "componentBgUrl" => "",
                        "componentBgAlpha" => 2,
                        "componentStartBgColor" => "",
                        "componentEndBgColor" => "",
                        "componentGradientAngle" => "to bottom",
                        "topRounded" => 0,
                        "bottomRounded" => 0,
                        "elementBgColor" => "",
                        "topElementRounded" => 0,
                        "bottomElementRounded" => 0,
                        "margin" => [
                            "top" => 0,
                            "bottom" => 0,
                            "both" => 0
                        ],
                        "isHidden" => false
                    ]
                ]
            ]
        ],
    ],
    'DIY_FROM_ORDER_PAYMENT' => [
        'shop_order_form_data' => [ // 页面标识
            "title" => "待付款表单", // 页面名称
            'cover' => '', // 页面封面图
            'preview' => '', // 页面预览图
            'desc' => '', // 页面描述
            // 页面数据源
            "data" => [
                "global" => [
                    "title" => "待付款表单",
                    "pageStartBgColor" => "",
                    "pageEndBgColor" => "",
                    "pageGradientAngle" => "to bottom",
                    "bgUrl" => "",
                    "bgHeightScale" => 100,
                    "imgWidth" => "",
                    "imgHeight" => "",
                    "topStatusBar" => [
                        "isShow" => true,
                        "bgColor" => "#ffffff",
                        "style" => "style-1",
                        "textColor" => "#333333",
                        "textAlign" => "center",
                        "rollBgColor" => "#ffffff",
                        "styleName" => "风格1",
                        "rollTextColor" => "#333333",
                        "inputPlaceholder" => "请输入搜索关键词",
                        "imgUrl" => "",
                        "link" => [
                            "name" => ""
                        ]
                    ],
                    "bottomTabBarSwitch" => true,
                    "popWindow" => [
                        "imgUrl" => "",
                        "imgWidth" => "",
                        "imgHeight" => "",
                        "count" => 'once',
                        "show" => 0,
                        "link" => [
                            "name" => ""
                        ]
                    ],
                    "template" => [
                        "textColor" => "#303133",
                        "pageStartBgColor" => "",
                        "pageEndBgColor" => "",
                        "pageGradientAngle" => "to bottom",
                        "componentBgUrl" => "",
                        "componentBgAlpha" => 2,
                        "componentStartBgColor" => "",
                        "componentEndBgColor" => "",
                        "componentGradientAngle" => "to bottom",
                        "topRounded" => 0,
                        "bottomRounded" => 0,
                        "elementBgColor" => "",
                        "topElementRounded" => 0,
                        "bottomElementRounded" => 0,
                        "margin" => [
                            "top" => 0,
                            "bottom" => 0,
                            "both" => 0
                        ],
                        "isHidden" => false
                    ],
                    "completeLayout" => "style-2",
                    "completeAlign" => "left",
                    "borderControl" => false
                ],
                "value" => [
                    [
                        "path" => "edit-form-mobile",
                        "uses" => 1,
                        "id" => "pe5nd1wsn28",
                        "componentName" => "FormMobile",
                        "componentTitle" => "手机号",
                        "ignore" => [
                            "componentBgUrl"
                        ],
                        "textColor" => "#303133",
                        "pageStartBgColor" => "#FFFFFF",
                        "pageEndBgColor" => "",
                        "pageGradientAngle" => "to bottom",
                        "componentBgUrl" => "",
                        "componentBgAlpha" => 2,
                        "componentEndBgColor" => "",
                        "componentGradientAngle" => "to bottom",
                        "topRounded" => 0,
                        "bottomRounded" => 0,
                        "elementBgColor" => "",
                        "topElementRounded" => 0,
                        "bottomElementRounded" => 0,
                        "margin" => [
                            "top" => 1,
                            "bottom" => 0,
                            "both" => 0
                        ],
                        "fontSize" => 14,
                        "fontWeight" => "normal",
                        "componentType" => "diy_form",
                        "field" => [
                            "name" => "联系电话",
                            "remark" => [
                                "text" => "",
                                "color" => "#999999",
                                "fontSize" => 14
                            ],
                            "required" => false,
                            "unique" => false,
                            "autofill" => false,
                            "privacyProtection" => false,
                            'cache' => true,
                            "default" => "",
                            "value" => ""
                        ],
                        "placeholder" => "请输入联系电话",
                        "componentStartBgColor" => "",
                        "isHidden" => false,
                        "sort" => 10008,
                        "pageStyle" => "background-color:#FFFFFF;padding-top:2rpx;padding-bottom:0rpx;padding-right:0rpx;padding-left:0rpx;"
                    ],
                    [
                        "path" => "edit-form-date",
                        "uses" => 0,
                        "componentType" => "diy_form",
                        "id" => "2230h04r8za8",
                        "componentName" => "FormDate",
                        "componentTitle" => "日期",
                        "ignore" => [
                            "componentBgUrl"
                        ],
                        "field" => [
                            "name" => "配送日期",
                            "remark" => [
                                "text" => "",
                                "color" => "#999999",
                                "fontSize" => 14
                            ],
                            "required" => false,
                            "unique" => false,
                            "autofill" => false,
                            "privacyProtection" => false,
                            'cache' => true,
                            "default" => [
                                "date" => "2025-01-15",
                                "timestamp" => 1736913088.477
                            ],
                            "value" => [
                                "date" => "2025/01/15",
                                "timestamp" => 1736870400
                            ]
                        ],
                        "placeholder" => "请选择",
                        "fontSize" => 14,
                        "fontWeight" => "normal",
                        "textColor" => "#303133",
                        "pageStartBgColor" => "#FFFFFF",
                        "pageEndBgColor" => "",
                        "pageGradientAngle" => "to bottom",
                        "componentBgUrl" => "",
                        "componentBgAlpha" => 2,
                        "componentStartBgColor" => "",
                        "componentEndBgColor" => "",
                        "componentGradientAngle" => "to bottom",
                        "topRounded" => 0,
                        "bottomRounded" => 0,
                        "elementBgColor" => "",
                        "topElementRounded" => 0,
                        "bottomElementRounded" => 0,
                        "margin" => [
                            "top" => 1,
                            "bottom" => 0,
                            "both" => 0
                        ],
                        "isHidden" => false,
                        "convert" => [],
                        "dateFormat" => "YYYY/MM/DD",
                        "dateWay" => "current",
                        "defaultControl" => true,
                        "pageStyle" => "background-color:#FFFFFF;padding-top:2rpx;padding-bottom:0rpx;padding-right:0rpx;padding-left:0rpx;"
                    ]
                ]
            ]
        ]
    ]
];

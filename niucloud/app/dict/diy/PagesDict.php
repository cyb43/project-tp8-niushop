<?php
// +----------------------------------------------------------------------
// | Niucloud-admin 企业快速开发的多应用管理平台
// +----------------------------------------------------------------------
// | 官方网址：https://www.niucloud.com
// +----------------------------------------------------------------------
// | niucloud团队 版权所有 开源版本可自由商用
// +----------------------------------------------------------------------
// | Author: Niucloud Team
// +----------------------------------------------------------------------

namespace app\dict\diy;

use app\service\admin\sys\ConfigService;
use core\dict\DictLoader;

/**
 * 页面数据
 * Class PagesDict
 * @package app\dict\diy
 */
class PagesDict
{

    public static function getPages($params = [])
    {
        $default_index_value = [
            "path" => "edit-graphic-nav",
            "id" => unique_random(10),
            "componentName" => "GraphicNav",
            "componentTitle" => "图文导航",
            "uses" => 0,
            "layout" => "horizontal",
            "mode" => "graphic",
            "showStyle" => "fixed",
            "rowCount" => 4,
            "pageCount" => 2,
            "carousel" => [
                "type" => "circle",
                "color" => "#FFFFFF"
            ],
            "imageSize" => 30,
            "aroundRadius" => 25,
            "font" => [
                "size" => 14,
                "weight" => "normal",
                "color" => "#303133"
            ],
            'pageStartBgColor' => '', // 底部背景颜色（开始）
            'pageEndBgColor' => '', // 底部背景颜色（结束）
            'pageGradientAngle' => 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）
            'componentBgUrl' => '',
            'componentBgAlpha' => 2,
            "componentStartBgColor" => "rgba(255, 255, 255, 1)",
            "componentEndBgColor" => "",
            "componentGradientAngle" => "to bottom",
            "topRounded" => 9,
            "bottomRounded" => 9,
            "elementBgColor" => "",
            "topElementRounded" => 0,
            "bottomElementRounded" => 0,
            "margin" => [
                "top" => 10,
                "bottom" => 10,
                "both" => 10
            ],
            "ignore" => [],
            "list" => [],
            // 轮播图设置
            'swiper' => [
                'indicatorColor' => 'rgba(0, 0, 0, 0.3)', // 未选中颜色
                "indicatorActiveColor" => '#FF0E0E',
                'indicatorStyle' => 'style-1',
                'indicatorAlign' => 'center'
            ]
        ];

        $wap_index_list = ( new ConfigService() )->getWapIndexList();

        if (!empty($wap_index_list)) {
            foreach ($wap_index_list as $k => $v) {

                $link_list = LinkDict::getLink([ 'addon' => $v[ 'key' ] ]);
                $link = [];
                foreach ($link_list as $ck => $cv) {
                    if ($cv[ 'addon_info' ][ 'key' ] == $v[ 'key' ]) {
                        foreach ($cv[ 'child_list' ] as $tk => $tv) {
                            if (isset($cv[ 'type' ]) && $cv[ 'type' ] == 'folder') {
                                if (!empty($tv[ 'child_list' ])) {
                                    foreach ($tv[ 'child_list' ] as $child_k => $child_v) {
                                        if ($child_v[ 'url' ] == $v[ 'url' ]) {
                                            $link = [
                                                "parent" => $ck,
                                                "name" => $child_v[ 'name' ],
                                                "title" => $child_v[ 'title' ],
                                                "url" => $child_v[ 'url' ]
                                            ];
                                            break;
                                        }
                                    }
                                }
                            } else if ($tv[ 'url' ] == $v[ 'url' ]) {
                                $link = [
                                    "parent" => $ck,
                                    "name" => $tv[ 'name' ],
                                    "title" => $tv[ 'title' ],
                                    "url" => $tv[ 'url' ]
                                ];
                                break;
                            }
                        }
                    }
                }
                $default_index_value[ 'list' ][] = [
                    "title" => $v[ 'title' ],
                    "link" => $link,
                    "imageUrl" => $v[ 'icon' ],
                    "label" => [
                        "control" => false,
                        "text" => "热门",
                        "textColor" => "#FFFFFF",
                        "bgColorStart" => "#F83287",
                        "bgColorEnd" => "#FE3423"
                    ],
                    "id" => unique_random(10 + $k),
                    "imgWidth" => 100,
                    "imgHeight" => 100
                ];
            }
        }

        $system_pages = [
            'DIY_INDEX' => [
                'default_index' => [ // 页面标识
                    "title" => "首页", // 页面名称
                    'cover' => '', // 页面封面图
                    'preview' => '', // 页面预览图
                    'desc' => '官方推出的系统首页', // 页面描述
                    'mode' => 'diy', // 页面模式：diy：自定义，fixed：固定
                    // 页面数据源
                    "data" => [
                        "global" => [
                            "title" => "首页",
                            'pageStartBgColor' => '#F8F8F8',
                            'pageEndBgColor' => '',
                            'pageGradientAngle' => 'to bottom',
                            'bgUrl' => '',
                            'bgHeightScale' => 0,
                            'imgWidth' => '',
                            'imgHeight' => '',
                            "bottomTabBar" => [
                                'control' => true,
                                'isShow' => true
                            ],
                            "template" => [
                                'textColor' => "#303133",
                                'pageStartBgColor' => '',
                                'pageEndBgColor' => '',
                                'pageGradientAngle' => 'to bottom',
                                'componentBgUrl' => '',
                                'componentBgAlpha' => 2,
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
                                ]
                            ],
                            'topStatusBar' => [
                                'isShow' => true,
                                'bgColor' => "#ffffff",
                                'rollBgColor' => "#ffffff",
                                'style' => 'style-1',
                                'styleName' => '风格1',
                                'textColor' => "#333333",
                                'rollTextColor' => "#333333",
                                'textAlign' => 'center',
                                'inputPlaceholder' => '请输入搜索关键词',
                                'imgUrl' => '',
                                'link' => [
                                    'name' => ""
                                ]
                            ],
                            'popWindow' => [
                                'imgUrl' => "",
                                'imgWidth' => '',
                                'imgHeight' => '',
                                'count' => 'once',
                                'show' => 0,
                                'link' => [
                                    'name' => ""
                                ],
                            ]
                        ],
                        "value" => [
                            $default_index_value
                        ]
                    ]
                ],
            ],
            'DIY_MEMBER_INDEX' => [
                'default_member_index_one' => [
                    "title" => "默认个人中心1", // 页面名称
                    'cover' => 'static/resource/images/diy/template/default_member_index_one_cover.png', // 页面封面图
                    'preview' => '', // 页面预览图
                    'desc' => '官方推出默认个人中心1', // 页面描述
                    'mode' => 'diy',
                    // 页面数据源
                    "data" => [
                        "global" => [
                            "title" => "个人中心",
                            'pageStartBgColor' => '#F8F8F8',
                            'pageEndBgColor' => '',
                            'pageGradientAngle' => 'to bottom',
                            'bgUrl' => '',
                            'bgHeightScale' => 0,
                            'imgWidth' => '',
                            'imgHeight' => '',
                            "bottomTabBar" => [
                                'control' => true,
                                'isShow' => true
                            ],
                            "template" => [
                                'textColor' => "#303133",
                                'pageStartBgColor' => '',
                                'pageEndBgColor' => '',
                                'pageGradientAngle' => 'to bottom',
                                'componentBgUrl' => '',
                                'componentBgAlpha' => 2,
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
                                    "both" => 10
                                ]
                            ],
                            "topStatusBar" => [
                                'isShow' => true,
                                'bgColor' => "#ffffff",
                                'rollBgColor' => "#ffffff",
                                'style' => 'style-1',
                                'styleName' => '风格1',
                                'textColor' => "#333333",
                                'rollTextColor' => "#333333",
                                'textAlign' => 'center',
                                'inputPlaceholder' => '请输入搜索关键词',
                                'imgUrl' => '',
                                'link' => [
                                    'name' => ""
                                ]
                            ],
                            'popWindow' => [
                                'imgUrl' => "",
                                'imgWidth' => '',
                                'imgHeight' => '',
                                'count' => 'once',
                                'show' => 0,
                                'link' => [
                                    'name' => ""
                                ],
                            ]
                        ],
                        "value" => [
                            [
                                "path" => "edit-member-info",
                                "id" => "67qv49qgxp00",
                                "componentName" => "MemberInfo",
                                "componentTitle" => "会员信息",
                                "uses" => 0,
                                "ignore" => [],
                                'pageStartBgColor' => '',
                                'pageEndBgColor' => '',
                                'pageGradientAngle' => 'to bottom',
                                'componentBgUrl' => '',
                                'componentBgAlpha' => 2,
                                "componentStartBgColor" => "",
                                "componentEndBgColor" => "",
                                "componentGradientAngle" => "to bottom",
                                "topRounded" => 9,
                                "bottomRounded" => 9,
                                "elementBgColor" => "",
                                "topElementRounded" => 0,
                                "bottomElementRounded" => 0,
                                "margin" => [
                                    "top" => 12,
                                    "bottom" => 6,
                                    "both" => 10
                                ],
                                "style" => "style-1",
                                "styleName" => "风格1",
                                "textColor" => "#FFFFFF",
                                "bgUrl" => "static/resource/images/diy/member_style1_bg.png",
                                "bgColorStart" => "",
                                "bgColorEnd" => ""
                            ],
                            [
                                "path" => "edit-horz-blank",
                                "uses" => 0,
                                "id" => "2da0xqyo8zms",
                                "componentName" => "HorzBlank",
                                "componentTitle" => "辅助空白",
                                "ignore" => [
                                    "pageBgColor",
                                    "componentBgUrl"
                                ],
                                "height" => 10,
                                "textColor" => "#303133",
                                "pageStartBgColor" => "",
                                "pageEndBgColor" => "",
                                "pageGradientAngle" => "to bottom",
                                "componentBgUrl" => "",
                                "componentBgAlpha" => 2,
                                "componentStartBgColor" => "rgba(255, 255, 255, 1)",
                                "componentEndBgColor" => "",
                                "componentGradientAngle" => "to bottom",
                                "topRounded" => 9,
                                "bottomRounded" => 0,
                                "elementBgColor" => "",
                                "topElementRounded" => 0,
                                "bottomElementRounded" => 0,
                                "margin" => [
                                    "top" => 6,
                                    "bottom" => 0,
                                    "both" => 10
                                ]
                            ],
                            [
                                "path" => "edit-text",
                                "uses" => 0,
                                "position" => "",
                                "id" => "1puhgfus8www",
                                "componentName" => "Text",
                                "componentTitle" => "标题",
                                "ignore" => [],
                                "style" => "style-2",
                                "styleName" => "风格2",
                                "text" => "我的服务",
                                "link" => [
                                    "name" => "",
                                ],
                                "textColor" => "#303133",
                                "fontSize" => 16,
                                "fontWeight" => "normal",
                                "textAlign" => "center",
                                "subTitle" => [
                                    "text" => "",
                                    "color" => "#999999",
                                    "fontSize" => 14,
                                    "control" => true,
                                    "fontWeight" => "normal"
                                ],
                                "more" => [
                                    "text" => "全部",
                                    "control" => true,
                                    "isShow" => true,
                                    "link" => [
                                        "name" => ""
                                    ],
                                    "color" => "#999999"
                                ],
                                'pageStartBgColor' => '',
                                'pageEndBgColor' => '',
                                'pageGradientAngle' => 'to bottom',
                                'componentBgUrl' => '',
                                'componentBgAlpha' => 2,
                                "componentStartBgColor" => "rgba(255, 255, 255, 1)",
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
                                    "both" => 10
                                ]
                            ],
                            [
                                "path" => "edit-graphic-nav",
                                "id" => "62b7d7hl4ok",
                                "componentName" => "GraphicNav",
                                "componentTitle" => "图文导航",
                                "uses" => 0,
                                "layout" => "horizontal",
                                "mode" => "graphic",
                                "showStyle" => "fixed",
                                "rowCount" => 4,
                                "pageCount" => 2,
                                "carousel" => [
                                    "type" => "circle",
                                    "color" => "#FFFFFF"
                                ],
                                "imageSize" => 25,
                                "aroundRadius" => 25,
                                "font" => [
                                    "size" => 12,
                                    "weight" => "bold",
                                    "color" => "#303133"
                                ],
                                'pageStartBgColor' => '',
                                'pageEndBgColor' => '',
                                'pageGradientAngle' => 'to bottom',
                                'componentBgUrl' => '',
                                'componentBgAlpha' => 2,
                                "componentStartBgColor" => "rgba(255, 255, 255, 1)",
                                "componentEndBgColor" => "",
                                "componentGradientAngle" => "to bottom",
                                "topRounded" => 0,
                                "bottomRounded" => 9,
                                "elementBgColor" => "",
                                "topElementRounded" => 0,
                                "bottomElementRounded" => 0,
                                "margin" => [
                                    "top" => 0,
                                    "bottom" => 6,
                                    "both" => 10
                                ],
                                "ignore" => [],
                                "list" => [
                                    [
                                        "title" => "个人资料",
                                        "link" => [
                                            "parent" => "MEMBER_LINK",
                                            "name" => "MEMBER_PERSONAL",
                                            "title" => "个人资料",
                                            "url" => "/app/pages/member/personal"
                                        ],
                                        "imageUrl" => "static/resource/images/diy/horz_m_personal.png",
                                        "label" => [
                                            "control" => false,
                                            "text" => "热门",
                                            "textColor" => "#FFFFFF",
                                            "bgColorStart" => "#F83287",
                                            "bgColorEnd" => "#FE3423"
                                        ],
                                        "id" => "xvlauaflc6o",
                                        "imgWidth" => 100,
                                        "imgHeight" => 100
                                    ],
                                    [
                                        "title" => "我的余额",
                                        "link" => [
                                            "parent" => "MEMBER_LINK",
                                            "name" => "MEMBER_BALANCE",
                                            "title" => "我的余额",
                                            "url" => "/app/pages/member/balance"
                                        ],
                                        "imageUrl" => "static/resource/images/diy/horz_m_balance.png",
                                        "label" => [
                                            "control" => false,
                                            "text" => "热门",
                                            "textColor" => "#FFFFFF",
                                            "bgColorStart" => "#F83287",
                                            "bgColorEnd" => "#FE3423"
                                        ],
                                        "id" => "63bjscck5n40",
                                        "imgWidth" => 100,
                                        "imgHeight" => 100
                                    ],
                                    [
                                        "title" => "我的积分",
                                        "link" => [
                                            "parent" => "MEMBER_LINK",
                                            "name" => "MEMBER_POINT",
                                            "title" => "我的积分",
                                            "url" => "/app/pages/member/point"
                                        ],
                                        "imageUrl" => "static/resource/images/diy/horz_m_point.png",
                                        "label" => [
                                            "control" => false,
                                            "text" => "热门",
                                            "textColor" => "#FFFFFF",
                                            "bgColorStart" => "#F83287",
                                            "bgColorEnd" => "#FE3423"
                                        ],
                                        "id" => "4qiczw54t8g0",
                                        "imgWidth" => 100,
                                        "imgHeight" => 100
                                    ],
                                    [
                                        "title" => "联系客服",
                                        "link" => [
                                            "name" => "MEMBER_CONTACT",
                                            "parent" => "MEMBER_LINK",
                                            "title" => "客服",
                                            "url" => "/app/pages/member/contact",
                                            "action" => ""
                                        ],
                                        "imageUrl" => "static/resource/images/diy/horz_m_service.png",
                                        "label" => [
                                            "control" => false,
                                            "text" => "热门",
                                            "textColor" => "#FFFFFF",
                                            "bgColorStart" => "#F83287",
                                            "bgColorEnd" => "#FE3423"
                                        ],
                                        "id" => "2eqwfkdphpgk",
                                        "imgWidth" => 100,
                                        "imgHeight" => 100
                                    ]
                                ],
                                // 轮播图设置
                                'swiper' => [
                                    'indicatorColor' => 'rgba(0, 0, 0, 0.3)', // 未选中颜色
                                    "indicatorActiveColor" => '#FF0E0E',
                                    'indicatorStyle' => 'style-1',
                                    'indicatorAlign' => 'center'
                                ]
                            ],
                            [
                                "path" => "edit-graphic-nav",
                                "uses" => 0,
                                "id" => "33yn28534fs0",
                                "componentName" => "GraphicNav",
                                "componentTitle" => "图文导航",
                                "ignore" => [],
                                "layout" => "vertical",
                                "mode" => "graphic",
                                "showStyle" => "fixed",
                                "rowCount" => 4,
                                "pageCount" => 2,
                                "carousel" => [
                                    "type" => "circle",
                                    "color" => "#FFFFFF"
                                ],
                                "imageSize" => 25,
                                "aroundRadius" => 0,
                                "font" => [
                                    "size" => 13,
                                    "weight" => "normal",
                                    "color" => "rgba(0, 0, 0, 1)"
                                ],
                                "list" => [
                                    [
                                        "title" => "个人资料",
                                        "link" => [
                                            "parent" => "MEMBER_LINK",
                                            "name" => "MEMBER_PERSONAL",
                                            "title" => "个人资料",
                                            "url" => "/app/pages/member/personal"
                                        ],
                                        "imageUrl" => "static/resource/images/diy/vert_m_personal.png",
                                        "label" => [
                                            "control" => false,
                                            "text" => "热门",
                                            "textColor" => "#FFFFFF",
                                            "bgColorStart" => "#F83287",
                                            "bgColorEnd" => "#FE3423"
                                        ],
                                        "id" => "4xc4kw9xlqu0",
                                        "imgWidth" => 88,
                                        "imgHeight" => 88
                                    ],
                                    [
                                        "title" => "我的余额",
                                        "link" => [
                                            "parent" => "MEMBER_LINK",
                                            "name" => "MEMBER_BALANCE",
                                            "title" => "我的余额",
                                            "url" => "/app/pages/member/balance"
                                        ],
                                        "imageUrl" => "static/resource/images/diy/vert_m_balance.png",
                                        "label" => [
                                            "control" => false,
                                            "text" => "热门",
                                            "textColor" => "#FFFFFF",
                                            "bgColorStart" => "#F83287",
                                            "bgColorEnd" => "#FE3423"
                                        ],
                                        "id" => "4555rq0cc1q0",
                                        "imgWidth" => 88,
                                        "imgHeight" => 88
                                    ],
                                    [
                                        "title" => "我的积分",
                                        "link" => [
                                            "parent" => "MEMBER_LINK",
                                            "name" => "MEMBER_POINT",
                                            "title" => "我的积分",
                                            "url" => "/app/pages/member/point"
                                        ],
                                        "imageUrl" => "static/resource/images/diy/vert_m_point.png",
                                        "label" => [
                                            "control" => false,
                                            "text" => "热门",
                                            "textColor" => "#FFFFFF",
                                            "bgColorStart" => "#F83287",
                                            "bgColorEnd" => "#FE3423"
                                        ],
                                        "id" => "1gq3uxox0fk0",
                                        "imgWidth" => 88,
                                        "imgHeight" => 88
                                    ],
                                    [
                                        "title" => "联系客服",
                                        "link" => [
                                            "name" => "MEMBER_CONTACT",
                                            "parent" => "MEMBER_LINK",
                                            "title" => "客服",
                                            "url" => "/app/pages/member/contact",
                                            "action" => ""
                                        ],
                                        "imageUrl" => "static/resource/images/diy/vert_m_service.png",
                                        "label" => [
                                            "control" => false,
                                            "text" => "热门",
                                            "textColor" => "#FFFFFF",
                                            "bgColorStart" => "#F83287",
                                            "bgColorEnd" => "#FE3423"
                                        ],
                                        "id" => "6gqbh1tvyr00",
                                        "imgWidth" => 88,
                                        "imgHeight" => 88
                                    ],
                                    [
                                        "id" => "6xhwid2el5c0",
                                        "title" => "开发者联盟",
                                        "imageUrl" => "static/resource/images/diy/vert_m_develop.png",
                                        "imgWidth" => 88,
                                        "imgHeight" => 88,
                                        "link" => [
                                            "name" => ""
                                        ],
                                        "label" => [
                                            "control" => false,
                                            "text" => "热门",
                                            "textColor" => "#FFFFFF",
                                            "bgColorStart" => "#F83287",
                                            "bgColorEnd" => "#FE3423"
                                        ]
                                    ]
                                ],
                                // 轮播图设置
                                'swiper' => [
                                    'indicatorColor' => 'rgba(0, 0, 0, 0.3)', // 未选中颜色
                                    "indicatorActiveColor" => '#FF0E0E',
                                    'indicatorStyle' => 'style-1',
                                    'indicatorAlign' => 'center'
                                ],
                                'pageStartBgColor' => '',
                                'pageEndBgColor' => '',
                                'pageGradientAngle' => 'to bottom',
                                'componentBgUrl' => '',
                                'componentBgAlpha' => 2,
                                "componentStartBgColor" => "rgba(255, 255, 255, 1)",
                                "componentEndBgColor" => "",
                                "componentGradientAngle" => "to bottom",
                                "topRounded" => 9,
                                "bottomRounded" => 9,
                                "elementBgColor" => "",
                                "topElementRounded" => 0,
                                "bottomElementRounded" => 0,
                                "margin" => [
                                    "top" => 6,
                                    "bottom" => 12,
                                    "both" => 10
                                ]
                            ]
                        ]
                    ]
                ],
                'default_member_index_two' => [
                    "title" => "默认个人中心2", // 页面名称
                    'cover' => 'static/resource/images/diy/template/default_member_index_two_cover.png', // 页面封面图
                    'preview' => '', // 页面预览图
                    'desc' => '官方推出默认个人中心2', // 页面描述
                    'mode' => 'diy',
                    // 页面数据源
                    "data" => [
                        "global" => [
                            "title" => "个人中心",
                            'pageStartBgColor' => '#F8F8F8',
                            'pageEndBgColor' => '',
                            'pageGradientAngle' => 'to bottom',
                            "bgUrl" => "static/resource/images/diy/member_style2_bg.png",
                            'bgHeightScale' => 0,
                            'imgWidth' => 750,
                            'imgHeight' => 403,
                            "bottomTabBar" => [
                                'control' => true,
                                'isShow' => true
                            ],
                            "template" => [
                                'textColor' => "#303133",
                                'pageStartBgColor' => '',
                                'pageEndBgColor' => '',
                                'pageGradientAngle' => 'to bottom',
                                'componentBgUrl' => '',
                                'componentBgAlpha' => 2,
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
                                    "both" => 10
                                ]
                            ],
                            'topStatusBar' => [
                                'isShow' => true,
                                'bgColor' => "#ffffff",
                                'isTransparent' => false,
                                'style' => 'style-1',
                                'styleName' => '风格1',
                                'textColor' => "#333333",
                                'textAlign' => 'center',
                                'inputPlaceholder' => '请输入搜索关键词',
                                'imgUrl' => '',
                                'link' => [
                                    'name' => ""
                                ]
                            ],
                            'popWindow' => [
                                'imgUrl' => "",
                                'imgWidth' => '',
                                'imgHeight' => '',
                                'count' => 'once',
                                'show' => 0,
                                'link' => [
                                    'name' => ""
                                ],
                            ]
                        ],
                        "value" => [
                            [
                                "path" => "edit-member-info",
                                "id" => "67qv49qgxp00",
                                "componentName" => "MemberInfo",
                                "componentTitle" => "会员信息",
                                "uses" => 0,
                                "ignore" => [],
                                'pageStartBgColor' => '',
                                'pageEndBgColor' => '',
                                'pageGradientAngle' => 'to bottom',
                                'componentBgUrl' => '',
                                'componentBgAlpha' => 2,
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
                                "textColor" => "#FFFFFF",
                                "bgUrl" => "",
                                "style" => "style-1",
                                "styleName" => "风格1",
                                "bgColorStart" => "",
                                "bgColorEnd" => ""
                            ],
                            [
                                "path" => "edit-horz-blank",
                                "uses" => 0,
                                "id" => "5fo173bx5840",
                                "componentName" => "HorzBlank",
                                "componentTitle" => "辅助空白",
                                "ignore" => [
                                    "pageBgColor",
                                    "componentBgUrl"
                                ],
                                "height" => 10,
                                "textColor" => "#303133",
                                "pageStartBgColor" => "",
                                "pageEndBgColor" => "",
                                "pageGradientAngle" => "to bottom",
                                "componentBgUrl" => "",
                                "componentBgAlpha" => 2,
                                "componentStartBgColor" => "rgba(255, 255, 255, 1)",
                                "componentEndBgColor" => "",
                                "componentGradientAngle" => "to bottom",
                                "topRounded" => 9,
                                "bottomRounded" => 0,
                                "elementBgColor" => "",
                                "topElementRounded" => 0,
                                "bottomElementRounded" => 0,
                                "margin" => [
                                    "top" => 0,
                                    "bottom" => 0,
                                    "both" => 10
                                ]
                            ],
                            [
                                "path" => "edit-text",
                                "uses" => 0,
                                "position" => "",
                                "id" => "629cgb1ygcw0",
                                "componentName" => "Text",
                                "componentTitle" => "标题",
                                "ignore" => [],
                                "style" => "style-1",
                                "styleName" => "风格1",
                                "text" => "我的服务",
                                "link" => [
                                    "name" => ""
                                ],
                                "textColor" => "#303133",
                                "fontSize" => 16,
                                "fontWeight" => "normal",
                                "textAlign" => "left",
                                "subTitle" => [
                                    "text" => "",
                                    "color" => "#999999",
                                    "fontSize" => 14,
                                    "control" => false,
                                    "fontWeight" => "normal"
                                ],
                                "more" => [
                                    "text" => "查看更多",
                                    "control" => false,
                                    "isShow" => false,
                                    "link" => [
                                        "name" => ""
                                    ],
                                    "color" => "#999999"
                                ],
                                'pageStartBgColor' => '',
                                'pageEndBgColor' => '',
                                'pageGradientAngle' => 'to bottom',
                                'componentBgUrl' => '',
                                'componentBgAlpha' => 2,
                                "componentStartBgColor" => "rgba(255, 255, 255, 1)",
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
                                    "both" => 10
                                ]
                            ],
                            [
                                "path" => "edit-graphic-nav",
                                "id" => "62b7d7hl4ok",
                                "componentName" => "GraphicNav",
                                "componentTitle" => "图文导航",
                                "uses" => 0,
                                "layout" => "horizontal",
                                "mode" => "graphic",
                                "showStyle" => "fixed",
                                "rowCount" => 4,
                                "pageCount" => 2,
                                "carousel" => [
                                    "type" => "circle",
                                    "color" => "#FFFFFF"
                                ],
                                "imageSize" => 25,
                                "aroundRadius" => 25,
                                "font" => [
                                    "size" => 12,
                                    "weight" => "bold",
                                    "color" => "#303133"
                                ],
                                'pageStartBgColor' => '',
                                'pageEndBgColor' => '',
                                'pageGradientAngle' => 'to bottom',
                                'componentBgUrl' => '',
                                'componentBgAlpha' => 2,
                                "componentStartBgColor" => "rgba(255, 255, 255, 1)",
                                "componentEndBgColor" => "",
                                "componentGradientAngle" => "to bottom",
                                "topRounded" => 0,
                                "bottomRounded" => 9,
                                "elementBgColor" => "",
                                "topElementRounded" => 0,
                                "bottomElementRounded" => 0,
                                "margin" => [
                                    "top" => 0,
                                    "bottom" => 6,
                                    "both" => 10
                                ],
                                "ignore" => [],
                                "list" => [
                                    [
                                        "title" => "个人资料",
                                        "link" => [
                                            "parent" => "MEMBER_LINK",
                                            "name" => "MEMBER_PERSONAL",
                                            "title" => "个人资料",
                                            "url" => "/app/pages/member/personal"
                                        ],
                                        "imageUrl" => "static/resource/images/diy/horz_m_personal.png",
                                        "label" => [
                                            "control" => false,
                                            "text" => "热门",
                                            "textColor" => "#FFFFFF",
                                            "bgColorStart" => "#F83287",
                                            "bgColorEnd" => "#FE3423"
                                        ],
                                        "id" => "xvlauaflc6o",
                                        "imgWidth" => 100,
                                        "imgHeight" => 100
                                    ],
                                    [
                                        "title" => "我的余额",
                                        "link" => [
                                            "parent" => "MEMBER_LINK",
                                            "name" => "MEMBER_BALANCE",
                                            "title" => "我的余额",
                                            "url" => "/app/pages/member/balance"
                                        ],
                                        "imageUrl" => "static/resource/images/diy/horz_m_balance.png",
                                        "label" => [
                                            "control" => false,
                                            "text" => "热门",
                                            "textColor" => "#FFFFFF",
                                            "bgColorStart" => "#F83287",
                                            "bgColorEnd" => "#FE3423"
                                        ],
                                        "id" => "63bjscck5n40",
                                        "imgWidth" => 100,
                                        "imgHeight" => 100
                                    ],
                                    [
                                        "title" => "我的积分",
                                        "link" => [
                                            "parent" => "MEMBER_LINK",
                                            "name" => "MEMBER_POINT",
                                            "title" => "我的积分",
                                            "url" => "/app/pages/member/point"
                                        ],
                                        "imageUrl" => "static/resource/images/diy/horz_m_point.png",
                                        "label" => [
                                            "control" => false,
                                            "text" => "热门",
                                            "textColor" => "#FFFFFF",
                                            "bgColorStart" => "#F83287",
                                            "bgColorEnd" => "#FE3423"
                                        ],
                                        "id" => "4qiczw54t8g0",
                                        "imgWidth" => 100,
                                        "imgHeight" => 100
                                    ],
                                    [
                                        "title" => "联系客服",
                                        "link" => [
                                            "name" => "MEMBER_CONTACT",
                                            "parent" => "MEMBER_LINK",
                                            "title" => "客服",
                                            "url" => "/app/pages/member/contact",
                                            "action" => ""
                                        ],
                                        "imageUrl" => "static/resource/images/diy/horz_m_service.png",
                                        "label" => [
                                            "control" => false,
                                            "text" => "热门",
                                            "textColor" => "#FFFFFF",
                                            "bgColorStart" => "#F83287",
                                            "bgColorEnd" => "#FE3423"
                                        ],
                                        "id" => "2eqwfkdphpgk",
                                        "imgWidth" => 100,
                                        "imgHeight" => 100
                                    ]
                                ],
                                // 轮播图设置
                                'swiper' => [
                                    'indicatorColor' => 'rgba(0, 0, 0, 0.3)', // 未选中颜色
                                    "indicatorActiveColor" => '#FF0E0E',
                                    'indicatorStyle' => 'style-1',
                                    'indicatorAlign' => 'center'
                                ]
                            ],
                            [
                                "path" => "edit-graphic-nav",
                                "uses" => 0,
                                "id" => "33yn28534fs0",
                                "componentName" => "GraphicNav",
                                "componentTitle" => "图文导航",
                                "ignore" => [],
                                "layout" => "vertical",
                                "mode" => "graphic",
                                "showStyle" => "fixed",
                                "rowCount" => 4,
                                "pageCount" => 2,
                                "carousel" => [
                                    "type" => "circle",
                                    "color" => "#FFFFFF"
                                ],
                                "imageSize" => 25,
                                "aroundRadius" => 0,
                                "font" => [
                                    "size" => 13,
                                    "weight" => "normal",
                                    "color" => "rgba(0, 0, 0, 1)"
                                ],
                                "list" => [
                                    [
                                        "title" => "个人资料",
                                        "link" => [
                                            "parent" => "MEMBER_LINK",
                                            "name" => "MEMBER_PERSONAL",
                                            "title" => "个人资料",
                                            "url" => "/app/pages/member/personal"
                                        ],
                                        "imageUrl" => "static/resource/images/diy/vert_m_personal.png",
                                        "label" => [
                                            "control" => false,
                                            "text" => "热门",
                                            "textColor" => "#FFFFFF",
                                            "bgColorStart" => "#F83287",
                                            "bgColorEnd" => "#FE3423"
                                        ],
                                        "id" => "4xc4kw9xlqu0",
                                        "imgWidth" => 88,
                                        "imgHeight" => 88
                                    ],
                                    [
                                        "title" => "我的余额",
                                        "link" => [
                                            "parent" => "MEMBER_LINK",
                                            "name" => "MEMBER_BALANCE",
                                            "title" => "我的余额",
                                            "url" => "/app/pages/member/balance"
                                        ],
                                        "imageUrl" => "static/resource/images/diy/vert_m_balance.png",
                                        "label" => [
                                            "control" => false,
                                            "text" => "热门",
                                            "textColor" => "#FFFFFF",
                                            "bgColorStart" => "#F83287",
                                            "bgColorEnd" => "#FE3423"
                                        ],
                                        "id" => "4555rq0cc1q0",
                                        "imgWidth" => 88,
                                        "imgHeight" => 88
                                    ],
                                    [
                                        "title" => "我的积分",
                                        "link" => [
                                            "parent" => "MEMBER_LINK",
                                            "name" => "MEMBER_POINT",
                                            "title" => "我的积分",
                                            "url" => "/app/pages/member/point"
                                        ],
                                        "imageUrl" => "static/resource/images/diy/vert_m_point.png",
                                        "label" => [
                                            "control" => false,
                                            "text" => "热门",
                                            "textColor" => "#FFFFFF",
                                            "bgColorStart" => "#F83287",
                                            "bgColorEnd" => "#FE3423"
                                        ],
                                        "id" => "1gq3uxox0fk0",
                                        "imgWidth" => 88,
                                        "imgHeight" => 88
                                    ],
                                    [
                                        "title" => "联系客服",
                                        "link" => [
                                            "name" => "MEMBER_CONTACT",
                                            "parent" => "MEMBER_LINK",
                                            "title" => "客服",
                                            "url" => "/app/pages/member/contact",
                                            "action" => ""
                                        ],
                                        "imageUrl" => "static/resource/images/diy/vert_m_service.png",
                                        "label" => [
                                            "control" => false,
                                            "text" => "热门",
                                            "textColor" => "#FFFFFF",
                                            "bgColorStart" => "#F83287",
                                            "bgColorEnd" => "#FE3423"
                                        ],
                                        "id" => "6gqbh1tvyr00",
                                        "imgWidth" => 88,
                                        "imgHeight" => 88
                                    ],
                                    [
                                        "id" => "777g7jxbtfc0",
                                        "title" => "开发者联盟",
                                        "imageUrl" => "static/resource/images/diy/vert_m_develop.png",
                                        "imgWidth" => 96,
                                        "imgHeight" => 96,
                                        "link" => [
                                            "name" => ""
                                        ],
                                        "label" => [
                                            "control" => false,
                                            "text" => "热门",
                                            "textColor" => "#FFFFFF",
                                            "bgColorStart" => "#F83287",
                                            "bgColorEnd" => "#FE3423"
                                        ]
                                    ]
                                ],
                                // 轮播图设置
                                'swiper' => [
                                    'indicatorColor' => 'rgba(0, 0, 0, 0.3)', // 未选中颜色
                                    "indicatorActiveColor" => '#FF0E0E',
                                    'indicatorStyle' => 'style-1',
                                    'indicatorAlign' => 'center'
                                ],
                                'pageStartBgColor' => '',
                                'pageEndBgColor' => '',
                                'pageGradientAngle' => 'to bottom',
                                'componentBgUrl' => '',
                                'componentBgAlpha' => 2,
                                "componentStartBgColor" => "rgba(255, 255, 255, 1)",
                                "componentEndBgColor" => "",
                                "componentGradientAngle" => "to bottom",
                                "topRounded" => 9,
                                "bottomRounded" => 9,
                                "elementBgColor" => "",
                                "topElementRounded" => 0,
                                "bottomElementRounded" => 0,
                                "margin" => [
                                    "top" => 6,
                                    "bottom" => 12,
                                    "both" => 10
                                ]
                            ]

                        ]
                    ]
                ]
            ]
        ];

        if (!empty($params[ 'addon' ])) {
            $pages = ( new DictLoader("UniappPages") )->load($params);
        } else {
            $pages = ( new DictLoader("UniappPages") )->load($system_pages);
        }

        if (!empty($params[ 'type' ])) {
            if (!empty($pages[ $params[ 'type' ] ])) {
                $temp = $pages[ $params[ 'type' ] ];
                if (isset($params[ 'mode' ]) && !empty($params[ 'mode' ])) {
                    foreach ($temp as $k => $v) {
                        if ($params[ 'mode' ] != $v[ 'mode' ]) {
                            unset($temp[ $k ]);
                        }
                    }
                }
                return $temp;
            } else {
                return [];
            }
        }

        return $pages;
    }

}

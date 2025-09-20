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

namespace app\dict\diy_form;

use core\dict\DictLoader;

/**
 * 页面模板
 */
class TemplateDict
{
    /**
     * 获取万能表单模版
     * @param array $params
     * @return array|null
     */
    public static function getTemplate($params = [])
    {
        $other_template_data = ( new DictLoader("DiyFormTemplate") )->load([]);
        $template = self::template();
        $data = array_merge($other_template_data, $template);
        if (!empty($params) && !empty($params[ 'type' ])) {
            if (!empty($params[ 'template_key' ])) {
                return $data[ $params[ 'type' ] ][ $params[ 'template_key' ] ] ?? [];
            }
            return $data[ $params[ 'type' ] ] ?? [];
        }

        return $data;
    }

    public static function template()
    {
        $data = [
            "DIY_FORM" => [
                'active_sign_up' => [ // 页面标识
                    "title" => "活动报名", // 页面名称
                    'cover' => '', // 页面封面图
                    'preview' => '', // 页面预览图
                    'desc' => '适用于活动宣传，收集报名信息，快速统计报名总人数', // 页面描述
                    'containField' => '共8个字段，包含8个字段：姓名、手机号、身份证号、邮箱、日期、报名职位、特长优点、入职时长', // 包含字段
                    // 页面数据源
                    "data" => [
                        "global" => [
                            "title" => "活动报名",
                            "completeLayout" => "style-1",
                            "completeAlign" => "left",
                            "borderControl" => true,
                            "pageStartBgColor" => "rgba(255, 255, 255, 1)",
                            "pageEndBgColor" => "",
                            "pageGradientAngle" => "to bottom",
                            "bgUrl" => "",
                            "bgHeightScale" => 100,
                            "imgWidth" => "",
                            "imgHeight" => "",
                            "topStatusBar" => [
                                "isShow" => true,
                                "bgColor" => "#ffffff",
                                "rollBgColor" => "#ffffff",
                                "style" => "style-1",
                                "styleName" => "风格1",
                                "textColor" => "#333333",
                                "rollTextColor" => "#333333",
                                "textAlign" => "center",
                                "inputPlaceholder" => "请输入搜索关键词",
                                "imgUrl" => "",
                                "link" => [
                                    "name" => ""
                                ]
                            ],
                            "bottomTabBar" => [
                                'control' => true,
                                'isShow' => true
                            ],
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
                            ]
                        ],
                        "value" => [
                            [
                                "path" => "edit-image-ads",
                                "uses" => 0,
                                "componentType" => "diy",
                                "id" => "36vpa1zz5mw0",
                                "componentName" => "ImageAds",
                                "componentTitle" => "图片广告",
                                "ignore" => [
                                    "componentBgUrl"
                                ],
                                "imageHeight" => 150,
                                "isSameScreen" => false,
                                "list" => [
                                    [
                                        "link" => [
                                            "name" => ""
                                        ],
                                        "imageUrl" => "static/resource/images/diy_form/diy_form_active_sign_up_banner.png",
                                        "imgWidth" => 750,
                                        "imgHeight" => 300,
                                        "id" => "4wk5whtbi0m0",
                                        "width" => 375,
                                        "height" => 150
                                    ]
                                ],
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
                                "isHidden" => false,
                                "pageStyle" => "padding-top:2rpx;padding-bottom:0rpx;padding-right:0rpx;padding-left:0rpx;"
                            ],
                            [
                                "path" => "edit-form-input",
                                "uses" => 0,
                                "position" => "",
                                "componentType" => "diy_form",
                                "id" => "46wkksoz5ew0",
                                "componentName" => "FormInput",
                                "componentTitle" => "单行文本",
                                "ignore" => [
                                    "componentBgUrl"
                                ],
                                "field" => [
                                    "name" => "姓名",
                                    "remark" => [
                                        "text" => "",
                                        "color" => "#999999",
                                        "fontSize" => 14
                                    ],
                                    "required" => true,
                                    "unique" => false,
                                    "autofill" => false,
                                    "privacyProtection" => false,
                                    'cache' => true,
                                    "default" => "",
                                    "value" => ""
                                ],
                                "placeholder" => "请输入姓名",
                                "fontSize" => 14,
                                "fontWeight" => "bold",
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
                                    "top" => 15,
                                    "bottom" => 10,
                                    "both" => 25
                                ],
                                "isHidden" => false,
                                "pageStyle" => "background-color:#FFFFFF;padding-top:30rpx;padding-bottom:20rpx;padding-right:34rpx;padding-left:34rpx;"
                            ],
                            [
                                "path" => "edit-form-mobile",
                                "uses" => 1,
                                "componentType" => "diy_form",
                                "id" => "6tsdwql8ds00",
                                "componentName" => "FormMobile",
                                "componentTitle" => "手机号",
                                "ignore" => [
                                    "componentBgUrl"
                                ],
                                "field" => [
                                    "name" => "手机号",
                                    "remark" => [
                                        "text" => "",
                                        "color" => "#999999",
                                        "fontSize" => 14
                                    ],
                                    "required" => false,
                                    "unique" => true,
                                    "autofill" => false,
                                    "privacyProtection" => true,
                                    'cache' => true,
                                    "default" => "",
                                    "value" => ""
                                ],
                                "placeholder" => "请输入手机号",
                                "fontSize" => 14,
                                "fontWeight" => "bold",
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
                                    "top" => 5,
                                    "bottom" => 10,
                                    "both" => 25
                                ],
                                "isHidden" => false,
                                "pageStyle" => "background-color:#FFFFFF;padding-top:10rpx;padding-bottom:20rpx;padding-right:34rpx;padding-left:34rpx;"
                            ],
                            [
                                "path" => "edit-form-identity",
                                "uses" => 1,
                                "componentType" => "diy_form",
                                "id" => "4hy63cm1lj80",
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
                                    "unique" => true,
                                    "autofill" => false,
                                    "privacyProtection" => false,
                                    'cache' => true,
                                    "default" => "",
                                    "value" => ""
                                ],
                                "placeholder" => "请输入身份证号",
                                "fontSize" => 14,
                                "fontWeight" => "bold",
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
                                    "top" => 5,
                                    "bottom" => 10,
                                    "both" => 25
                                ],
                                "isHidden" => false,
                                "pageStyle" => "background-color:#FFFFFF;padding-top:10rpx;padding-bottom:20rpx;padding-right:34rpx;padding-left:34rpx;"
                            ],
                            [
                                "path" => "edit-form-email",
                                "uses" => 0,
                                "componentType" => "diy_form",
                                "id" => "13f2w3r9h9vg",
                                "componentName" => "FormEmail",
                                "componentTitle" => "邮箱",
                                "ignore" => [
                                    "componentBgUrl"
                                ],
                                "field" => [
                                    "name" => "邮箱",
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
                                "placeholder" => "请输入邮箱",
                                "fontSize" => 14,
                                "fontWeight" => "bold",
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
                                    "top" => 5,
                                    "bottom" => 10,
                                    "both" => 25
                                ],
                                "isHidden" => false,
                                "pageStyle" => "background-color:#FFFFFF;padding-top:10rpx;padding-bottom:20rpx;padding-right:34rpx;padding-left:34rpx;"
                            ],
                            [
                                "path" => "edit-form-date",
                                "uses" => 0,
                                "componentType" => "diy_form",
                                "id" => "7dc7gd9hh400",
                                "componentName" => "FormDate",
                                "componentTitle" => "日期",
                                "ignore" => [
                                    "componentBgUrl"
                                ],
                                "field" => [
                                    "name" => "日期",
                                    "remark" => [
                                        "text" => "",
                                        "color" => "#999999",
                                        "fontSize" => 14
                                    ],
                                    "required" => true,
                                    "unique" => false,
                                    "autofill" => false,
                                    "privacyProtection" => false,
                                    'cache' => true,
                                    "default" => [
                                        "date" => "",
                                        "timestamp" => 0
                                    ],
                                    "value" => [
                                        "date" => "",
                                        "timestamp" => 0
                                    ]
                                ],
                                "placeholder" => "请选择日期",
                                "fontSize" => 14,
                                "fontWeight" => "bold",
                                "dateFormat" => "YYYY-MM-DD HH:mm",
                                "dateWay" => "current",
                                "defaultControl" => true,
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
                                    "top" => 5,
                                    "bottom" => 10,
                                    "both" => 25
                                ],
                                "isHidden" => false,
                                "pageStyle" => "background-color:#FFFFFF;padding-top:10rpx;padding-bottom:20rpx;padding-right:34rpx;padding-left:34rpx;"
                            ],
                            [
                                "path" => "edit-form-radio",
                                "uses" => 0,
                                "componentType" => "diy_form",
                                "id" => "3z2yq22p9xc0",
                                "componentName" => "FormRadio",
                                "componentTitle" => "单选项",
                                "ignore" => [
                                    "componentBgUrl"
                                ],
                                "field" => [
                                    "name" => "报名职位",
                                    "remark" => [
                                        "text" => "",
                                        "color" => "#999999",
                                        "fontSize" => 14
                                    ],
                                    "required" => true,
                                    "unique" => false,
                                    "autofill" => false,
                                    "privacyProtection" => false,
                                    'cache' => true,
                                    "default" => [],
                                    "value" => []
                                ],
                                "fontSize" => 14,
                                "fontWeight" => "bold",
                                "style" => "style-2",
                                "options" => [
                                    [
                                        "id" => "fzjmiaochnsd",
                                        "text" => "前台"
                                    ],
                                    [
                                        "id" => "mabstfflrdpj",
                                        "text" => "收银"
                                    ],
                                    [
                                        "id" => "1ogre1qndmrk",
                                        "text" => "后厨"
                                    ],
                                    [
                                        "id" => "1mv5qku9wihs",
                                        "text" => "财务"
                                    ],
                                    [
                                        "id" => "qfdjp035qsw",
                                        "text" => "经理"
                                    ]
                                ],
                                "logicalRule" => [
                                    [
                                        "triggerOptionId" => "",
                                        "execEvent" => [
                                            [
                                                "id" => "",
                                                "componentName" => "",
                                                "componentTitle" => ""
                                            ]
                                        ]
                                    ]
                                ],
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
                                    "top" => 5,
                                    "bottom" => 10,
                                    "both" => 25
                                ],
                                "isHidden" => false,
                                "pageStyle" => "background-color:#FFFFFF;padding-top:10rpx;padding-bottom:20rpx;padding-right:34rpx;padding-left:34rpx;"
                            ],
                            [
                                "path" => "edit-form-textarea",
                                "uses" => 0,
                                "componentType" => "diy_form",
                                "id" => "39m5zel59cw0",
                                "componentName" => "FormTextarea",
                                "componentTitle" => "多行文本",
                                "ignore" => [
                                    "componentBgUrl"
                                ],
                                "field" => [
                                    "name" => "特长优点",
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
                                "placeholder" => "请输入特长优点",
                                "fontSize" => 14,
                                "fontWeight" => "bold",
                                "rowCount" => 4,
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
                                    "top" => 5,
                                    "bottom" => 10,
                                    "both" => 25
                                ],
                                "isHidden" => false,
                                "pageStyle" => "background-color:#FFFFFF;padding-top:10rpx;padding-bottom:20rpx;padding-right:34rpx;padding-left:34rpx;"
                            ],
                            [
                                "path" => "edit-form-date-scope",
                                "uses" => 0,
                                "convert" => [],
                                "componentType" => "diy_form",
                                "id" => "mj9fl99x02o",
                                "componentName" => "FormDateScope",
                                "componentTitle" => "日期范围",
                                "ignore" => [
                                    "componentBgUrl"
                                ],
                                "field" => [
                                    "name" => "入职时长",
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
                                        "start" => [
                                            "date" => "",
                                            "timestamp" => 0
                                        ],
                                        "end" => [
                                            "date" => "",
                                            "timestamp" => 0
                                        ]
                                    ],
                                    "value" => [
                                        "start" => [
                                            "date" => "",
                                            "timestamp" => 0
                                        ],
                                        "end" => [
                                            "date" => "",
                                            "timestamp" => 0
                                        ]
                                    ]
                                ],
                                "fontSize" => 14,
                                "fontWeight" => "bold",
                                "dateFormat" => "YYYY/MM/DD",
                                "start" => [
                                    "placeholder" => "请选择起始日期",
                                    "dateWay" => "current",
                                    "defaultControl" => true
                                ],
                                "end" => [
                                    "placeholder" => "请选择结束日期",
                                    "dateWay" => "current",
                                    "defaultControl" => true
                                ],
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
                                    "top" => 5,
                                    "bottom" => 10,
                                    "both" => 25
                                ],
                                "isHidden" => false,
                                "pageStyle" => "background-color:#FFFFFF;padding-top:10rpx;padding-bottom:20rpx;padding-right:34rpx;padding-left:34rpx;"
                            ],
                            [
                                "path" => "edit-form-submit",
                                "uses" => 1,
                                "position" => "bottom_fixed",
                                "componentType" => "diy_form",
                                "id" => "38b02iygfzc0",
                                "componentName" => "FormSubmit",
                                "componentTitle" => "表单提交",
                                "ignore" => [
                                    "componentBgUrl"
                                ],
                                "btnPosition" => "follow_content",
                                "submitBtn" => [
                                    "text" => "提交",
                                    "color" => "#ffffff",
                                    "bgColor" => "#409EFF"
                                ],
                                "resetBtn" => [
                                    "control" => true,
                                    "text" => "重置",
                                    "color" => "",
                                    "bgColor" => ""
                                ],
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
                                "topElementRounded" => 50,
                                "bottomElementRounded" => 50,
                                "margin" => [
                                    "top" => 5,
                                    "bottom" => 5,
                                    "both" => 25
                                ],
                                "isHidden" => false,
                                "pageStyle" => "padding-top:10rpx;padding-bottom:10rpx;padding-right:20rpx;padding-left:20rpx;"
                            ]
                        ]
                    ]

                ],
            ],
            "DIY_FORM_MEMBER_INFO" => [
                'member_info_form_data' => [ // 页面标识
                    "title" => "个人资料", // 页面名称
                    'cover' => '', // 页面封面图
                    'preview' => '', // 页面预览图
                    'desc' => '适用于会员个人资料字段补充收集', // 页面描述
                    'containField' => '共7个字段，包含7个字段：姓名、身份证号、邮箱、职业、爱好、身份证人像面、身份证国徽面', // 包含字段
                    // 页面数据源
                    "data" => [
                        "global" => [
                            "title" => "个人资料",
                            "completeLayout" => "style-1",
                            "completeAlign" => "left",
                            "borderControl" => true,
                            "pageStartBgColor" => "rgba(255, 255, 255, 1)",
                            "pageEndBgColor" => "",
                            "pageGradientAngle" => "to bottom",
                            "bgUrl" => "",
                            "bgHeightScale" => 100,
                            "imgWidth" => "",
                            "imgHeight" => "",
                            "topStatusBar" => [
                                "isShow" => true,
                                "bgColor" => "#ffffff",
                                "rollBgColor" => "#ffffff",
                                "style" => "style-1",
                                "styleName" => "风格1",
                                "textColor" => "#333333",
                                "rollTextColor" => "#333333",
                                "textAlign" => "center",
                                "inputPlaceholder" => "请输入搜索关键词",
                                "imgUrl" => "",
                                "link" => [
                                    "name" => ""
                                ]
                            ],
                            "bottomTabBar" => [
                                'control' => true,
                                'isShow' => true
                            ],
                            "popWindow" => [
                                "imgUrl" => "",
                                "imgWidth" => "",
                                "imgHeight" => "",
                                "count" => -1,
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
                            ]
                        ],
                        "value" => [
                            [
                                "path" => "edit-form-input",
                                "uses" => 0,
                                "position" => "",
                                "componentType" => "diy_form",
                                "id" => "46wkksoz5ew0",
                                "componentName" => "FormInput",
                                "componentTitle" => "单行文本",
                                "ignore" => [
                                    "componentBgUrl"
                                ],
                                "field" => [
                                    "name" => "姓名",
                                    "remark" => [
                                        "text" => "",
                                        "color" => "#999999",
                                        "fontSize" => 14
                                    ],
                                    "required" => true,
                                    "unique" => false,
                                    "autofill" => false,
                                    "privacyProtection" => false,
                                    "cache" => true,
                                    "default" => "",
                                    "value" => ""
                                ],
                                "placeholder" => "请输入姓名",
                                "fontSize" => 14,
                                "fontWeight" => "bold",
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
                                    "top" => 15,
                                    "bottom" => 10,
                                    "both" => 25
                                ],
                                "isHidden" => false,
                                "pageStyle" => "background-color:#FFFFFF;padding-top:30rpx;padding-bottom:20rpx;padding-right:34rpx;padding-left:34rpx;"
                            ],
                            [
                                "path" => "edit-form-identity",
                                "uses" => 1,
                                "componentType" => "diy_form",
                                "id" => "4hy63cm1lj80",
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
                                    "unique" => true,
                                    "autofill" => false,
                                    "privacyProtection" => false,
                                    'cache' => true,
                                    "default" => "",
                                    "value" => ""
                                ],
                                "placeholder" => "请输入身份证号",
                                "fontSize" => 14,
                                "fontWeight" => "bold",
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
                                    "top" => 5,
                                    "bottom" => 10,
                                    "both" => 25
                                ],
                                "isHidden" => false,
                                "pageStyle" => "background-color:#FFFFFF;padding-top:10rpx;padding-bottom:20rpx;padding-right:34rpx;padding-left:34rpx;"
                            ],
                            [
                                "path" => "edit-form-email",
                                "uses" => 0,
                                "componentType" => "diy_form",
                                "id" => "13f2w3r9h9vg",
                                "componentName" => "FormEmail",
                                "componentTitle" => "邮箱",
                                "ignore" => [
                                    "componentBgUrl"
                                ],
                                "field" => [
                                    "name" => "邮箱",
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
                                "placeholder" => "请输入邮箱",
                                "fontSize" => 14,
                                "fontWeight" => "bold",
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
                                    "top" => 5,
                                    "bottom" => 10,
                                    "both" => 25
                                ],
                                "isHidden" => false,
                                "pageStyle" => "background-color:#FFFFFF;padding-top:10rpx;padding-bottom:20rpx;padding-right:34rpx;padding-left:34rpx;"
                            ],
                            [
                                "path" => "edit-form-checkbox",
                                "uses" => 0,
                                "convert" => [],
                                "componentType" => "diy_form",
                                "id" => "4jcvmb2asve0",
                                "componentName" => "FormCheckbox",
                                "componentTitle" => "多选项",
                                "ignore" => [
                                    "componentBgUrl"
                                ],
                                "field" => [
                                    "name" => "职业",
                                    "remark" => [
                                        "text" => "",
                                        "color" => "#999999",
                                        "fontSize" => 14
                                    ],
                                    "required" => true,
                                    "unique" => false,
                                    "autofill" => false,
                                    "privacyProtection" => false,
                                    'cache' => true,
                                    "default" => [],
                                    "value" => []
                                ],
                                "fontSize" => 14,
                                "fontWeight" => "bold",
                                "style" => "style-3",
                                "options" => [
                                    [
                                        "id" => "incszbphksdt",
                                        "text" => "选项1"
                                    ],
                                    [
                                        "id" => "dzcjgipwkffx",
                                        "text" => "选项2"
                                    ]
                                ],
                                "selectableNum" => [
                                    "min" => 0,
                                    "max" => 0
                                ],
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
                                    "top" => 5,
                                    "bottom" => 10,
                                    "both" => 25
                                ],
                                "isHidden" => false,
                            ],
                            [
                                "path" => "edit-form-textarea",
                                "uses" => 0,
                                "componentType" => "diy_form",
                                "id" => "64a0gh89tz40",
                                "componentName" => "FormTextarea",
                                "componentTitle" => "多行文本",
                                "ignore" => [
                                    "componentBgUrl"
                                ],
                                "field" => [
                                    "name" => "爱好",
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
                                "placeholder" => "请输入特长优点",
                                "fontSize" => 14,
                                "fontWeight" => "bold",
                                "rowCount" => 4,
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
                                    "top" => 5,
                                    "bottom" => 10,
                                    "both" => 25
                                ],
                                "isHidden" => false,
                                "pageStyle" => "background-color:#FFFFFF;padding-top:10rpx;padding-bottom:20rpx;padding-right:34rpx;padding-left:34rpx;"
                            ],
                            [
                                "path" => "edit-form-submit",
                                "uses" => 1,
                                "position" => "bottom_fixed",
                                "componentType" => "diy_form",
                                "id" => "38b02iygfzc0",
                                "componentName" => "FormSubmit",
                                "componentTitle" => "表单提交",
                                "ignore" => [
                                    "componentBgUrl"
                                ],
                                "btnPosition" => "follow_content",
                                "submitBtn" => [
                                    "text" => "提交",
                                    "color" => "#ffffff",
                                    "bgColor" => "#409EFF"
                                ],
                                "resetBtn" => [
                                    "control" => true,
                                    "text" => "重置",
                                    "color" => "",
                                    "bgColor" => ""
                                ],
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
                                "topElementRounded" => 50,
                                "bottomElementRounded" => 50,
                                "margin" => [
                                    "top" => 5,
                                    "bottom" => 5,
                                    "both" => 25
                                ],
                                "isHidden" => false,
                                "pageStyle" => "padding-top:10rpx;padding-bottom:10rpx;padding-right:20rpx;padding-left:20rpx;"
                            ]
                        ]
                    ]
                ],
            ],
            //  todo 靠后完善
//            'SIGN_REGISTRATION' => [
//                'active_sign_up' => [ // 页面标识
//                    "title" => "活动报名", // 页面名称
//                    'cover' => '', // 页面封面图
//                    'preview' => '', // 页面预览图
//                    'desc' => '适用于活动宣传，收集报名信息，快速统计报名总人数', // 页面描述
//                    'containField' => '共2个字段，包含2个字段：姓名、手机号', // 包含字段
//                    // 页面数据源
//                    "data" => [
//                        "global" => [],
//                        "value" => []
//                    ]
//                ],
//                'attendance_clock_in' => [ // 页面标识
//                    "title" => "考勤打卡", // 页面名称
//                    'cover' => '', // 页面封面图
//                    'preview' => '', // 页面预览图
//                    'desc' => '无需额外设备，员工直接使用微信扫码完成考勤打卡，管理员可随时查看考勤汇总数据，导出后进行工作考核', // 页面描述
//                    'containField' => '包含5个字段：打卡类型、姓名、手机号、定位、备注', // 包含字段
//                    // 页面数据源
//                    "data" => [
//                        "global" => [],
//                        "value" => []
//                    ]
//                ],
//                'meeting_sign_up' => [ // 页面标识
//                    "title" => "会议报名", // 页面名称
//                    'cover' => '', // 页面封面图
//                    'preview' => '', // 页面预览图
//                    'desc' => '填表人报名后获取核销凭证，活动现场出示，主办方可核销', // 页面描述
//                    'containField' => '包含4个字段：姓名、手机号、公司名称、职务', // 包含字段
//                    // 页面数据源
//                    "data" => [
//                        "global" => [],
//                        "value" => []
//                    ]
//                ],
//                'service_reservation' => [ // 页面标识
//                    "title" => "服务预约", // 页面名称
//                    'cover' => '', // 页面封面图
//                    'preview' => '', // 页面预览图
//                    'desc' => '扫码填写预约报名信息，管理者可及时查看预约情况', // 页面描述
//                    'containField' => '共5个字段，包含5个字段：姓名、联系方式、预约日期、预约时间、备注', // 包含字段
//                    // 页面数据源
//                    "data" => [
//                        "global" => [],
//                        "value" => []
//                    ]
//                ],
//                'person_registration' => [ // 页面标识
//                    "title" => "人员信息登记", // 页面名称
//                    'cover' => '', // 页面封面图
//                    'preview' => '', // 页面预览图
//                    'desc' => '适用于员工、学生、居民和访客等各类人员的信息收集场景', // 页面描述
//                    'containField' => '包含8个字段：姓名、手机号、性别、身份证号、出生日期、民族、籍贯、家庭地址', // 包含字段
//                    // 页面数据源
//                    "data" => [
//                        "global" => [],
//                        "value" => []
//                    ]
//                ],
//            ],
//            'LEAVE_MESSAGE_SUGGESTION' => [
//                'feedback_collection' => [ // 页面标识
//                    "title" => "反馈意见收集", // 页面名称
//                    'cover' => '', // 页面封面图
//                    'preview' => '', // 页面预览图
//                    'desc' => '反馈人扫码即可随时随地进行留言建议，管理人可以审核内容，在线回复', // 页面描述
//                    'containField' => '共5个字段，包含5个字段：您要反馈哪方面的问题？、详细描述、相关图片、反馈人、手机号', // 包含字段
//                    // 页面数据源
//                    "data" => [
//                        "global" => [],
//                        "value" => []
//                    ]
//                ],
//                'Satisfaction_level_questionnaire' => [ // 页面标识
//                    "title" => "满意度调查问卷", // 页面名称
//                    'cover' => '', // 页面封面图
//                    'preview' => '', // 页面预览图
//                    'desc' => '通过满意度调研表单制作一个满意度调研二维码，所有人扫码即可填写，代替原有纸质方式；所有的评价都将汇总在管理后台，可以统一导出进行统计，了解整体的服务质量', // 页面描述
//                    'containField' => '包含12个字段：1. 商场温度舒适度、2. 电梯及扶梯是否运行正常、3. 商场地面、墙面及天花板清洁、4. 卫生间地面、台盆及镜面清洁、5. 卫生间客用品是否缺失、6. 卫生间有无异味、7. 保洁员工仪容仪表及工作态度、8. 安保员工仪容仪表及工作态度、9. 车场停车费扫码付费便捷度、姓名、联系方式、图文描述', // 包含字段
//                    // 页面数据源
//                    "data" => [
//                        "global" => [],
//                        "value" => []
//                    ]
//                ],
//            ],
//            'WRITE_OFF_VOUCHER' => [
//                'gift_receive_reservation' => [ // 页面标识
//                    "title" => "礼品领取预约", // 页面名称
//                    'cover' => '', // 页面封面图
//                    'preview' => '', // 页面预览图
//                    'desc' => '客户线上填写信息并选择礼品，完成后获取二维码凭证。在现场，出示该凭证领取礼品，工作人员扫描凭证进行核销', // 页面描述
//                    'containField' => '包含3个字段：姓名、手机、礼品选择', // 包含字段
//                    // 页面数据源
//                    "data" => [
//                        "global" => [],
//                        "value" => []
//                    ]
//                ],
//            ],
        ];
        return $data;
    }

}

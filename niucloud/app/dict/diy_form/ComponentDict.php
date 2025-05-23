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
 * 表单组件
 * Class ComponentDict
 * @package app\dict\diy_form
 */
class ComponentDict
{

    public static function getComponent($params = [])
    {
        $system_components = [
            'DIY_FORM_COMPONENT' => [
                'title' => get_lang('dict_diy_form.component_type_form'),
                'support' => [], // 支持的插件
                'list' => [
                    'FormSubmit' => [
                        'title' => '表单提交',
                        'icon' => 'iconfont icona-biaodantijiaopc30',
                        'path' => 'edit-form-submit', // 编辑组件属性名称
                        'uses' => 1, // 最大添加数量
                        'support' => [], // 支持的表单类型
                        'sort' => 10001,
                        'position' => 'bottom_fixed', // 组件置顶标识，不能拖拽，可选值：fixed、top_fixed、right_fixed、bottom_fixed、left_fixed
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
                            "topElementRounded" => 50,// 元素上圆角
                            "bottomElementRounded" => 50, // 元素下圆角
                            "margin" => [
                                "top" => 8, // 上边距
                                "bottom" => 0, // 下边距
                                "both" => 10 // 左右边距
                            ],
                        ],
                        'value' => [
                            /*
                             * 按钮位置
                             * follow_content：跟随内容（当表单内容多时，需要滚动到页面最底部才会展示提交按钮，适合必填项比较多的情况使用）
                             * hover_screen_bottom：悬浮屏幕底部（提交按钮悬浮在屏幕底部，方便填表人快速提交）
                             */
                            'btnPosition' => 'follow_content',
                            // 提交按钮
                            'submitBtn' => [
                                'text' => '提交', // 按钮文本内容
                                'color' => '#ffffff', // 文字颜色
                                'bgColor' => '#409EFF', // 背景色
                            ],
                            // 重置按钮
                            'resetBtn' => [
                                'control' => true, // 是否展示开关
                                'text' => '重置', // 按钮文本内容
                                'color' => '', // 文字颜色
                                'bgColor' => '', // 背景色
                            ],
                        ]
                    ],
                    'FormInput' => [
                        'title' => '单行文本',
                        'icon' => 'iconfont icona-danhangwenben-1pc30',
                        'path' => 'edit-form-input', // 编辑组件属性名称
                        'uses' => 0, // 最大添加数量
                        'sort' => 10002,
                        'position' => '', // 组件置顶标识，不能拖拽，可选值：fixed、top_fixed、right_fixed、bottom_fixed、left_fixed
                        // 组件属性
                        'template' => [
                            "textColor" => "#303133", // 文字颜色
                            'pageStartBgColor' => '#FFFFFF', // 底部背景颜色（开始）
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
                                "top" => 8, // 上边距
                                "bottom" => 8, // 下边距
                                "both" => 10 // 左右边距
                            ],
                        ],
                        'value' => [
                            // 表单的公共属性
                            'field' => [
                                'name' => '单行文本', // 字段名称
                                // 字段说明，支持修改颜色、大小
                                'remark' => [
                                    'text' => '',
                                    'color' => '#999999',
                                    "fontSize" => 14,
                                ],
                                'required' => false, // 是否必填 true：是，false：否
                                'unique' => false, // 内容不可重复提交 true：是，false：否
                                'autofill' => false, // 自动填充上次填写的内容 true：开启，false：关闭
                                'privacyProtection' => false, // 隐私保护 true：开启，false：关闭，隐藏逻辑各组件自行处理
//                                'detailComponent' => '/src/app/views/diy_form/components/detail-form-render.vue', // 用于详情展示，后台会返回默认，特殊组件可以重写组件
                                'cache' => true, // 开启本地数据缓存 true：开启，false：关闭
                                'default' => '', // 默认值 存储数据类型不同，各组件自行处理
                                'value' => '', // 字段值 存储数据类型不同，各组件自行处理
                            ],
                            'placeholder' => '请输入', // 提示语
                            "fontSize" => 14,
                            "fontWeight" => "normal",
                        ]
                    ],
                    'FormTextarea' => [
                        'title' => '多行文本',
                        'icon' => 'iconfont icona-duohangwenben-1pc30',
                        'path' => 'edit-form-textarea', // 编辑组件属性名称
                        'uses' => 0, // 最大添加数量
                        'sort' => 10003,
                        // 组件属性
                        'template' => [
                            "textColor" => "#303133", // 文字颜色
                            'pageStartBgColor' => '#FFFFFF', // 底部背景颜色（开始）
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
                                "top" => 8, // 上边距
                                "bottom" => 8, // 下边距
                                "both" => 10 // 左右边距
                            ],
                        ],
                        'value' => [
                            // 表单的公共属性
                            'field' => [
                                'name' => '多行文本', // 字段名称
                                // 字段说明，支持修改颜色、大小
                                'remark' => [
                                    'text' => '',
                                    'color' => '#999999',
                                    "fontSize" => 14,
                                ],
                                'required' => false, // 是否必填 true：是，false：否
                                'unique' => false, // 内容不可重复提交 true：是，false：否
                                'autofill' => false, // 自动填充上次填写的内容 true：开启，false：关闭
                                'privacyProtection' => false, // 隐私保护 true：开启，false：关闭，隐藏逻辑各组件自行处理
                                'cache' => true, // 开启本地数据缓存 true：开启，false：关闭
                                'default' => '', // 默认值 存储数据类型不同，各组件自行处理
                                'value' => '', // 字段值 存储数据类型不同，各组件自行处理
                            ],
                            'placeholder' => '请输入', // 提示语
                            "fontSize" => 14,
                            "fontWeight" => "normal",
                            "rowCount" => 4, // 显示行数
                        ]
                    ],
                    'FormNumber' => [
                        'title' => '数字',
                        'icon' => 'iconfont icona-shuzipc30-1',
                        'path' => 'edit-form-number', // 编辑组件属性名称
                        'uses' => 0, // 最大添加数量
                        'sort' => 10004,
                        // 组件属性
                        'template' => [
                            "textColor" => "#303133", // 文字颜色
                            'pageStartBgColor' => '#FFFFFF', // 底部背景颜色（开始）
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
                                "top" => 8, // 上边距
                                "bottom" => 8, // 下边距
                                "both" => 10 // 左右边距
                            ],
                        ],
                        'value' => [
                            // 表单的公共属性
                            'field' => [
                                'name' => '数字', // 字段名称
                                // 字段说明，支持修改颜色、大小
                                'remark' => [
                                    'text' => '',
                                    'color' => '#999999',
                                    "fontSize" => 14,
                                ],
                                'required' => false, // 是否必填 true：是，false：否
                                'unique' => false, // 内容不可重复提交 true：是，false：否
                                'autofill' => false, // 自动填充上次填写的内容 true：开启，false：关闭
                                'privacyProtection' => false, // 隐私保护 true：开启，false：关闭，隐藏逻辑各组件自行处理
                                'cache' => true, // 开启本地数据缓存 true：开启，false：关闭
                                'default' => '', // 默认值 存储数据类型不同，各组件自行处理
                                'value' => '', // 字段值 存储数据类型不同，各组件自行处理
                            ],
                            'placeholder' => '请输入', // 提示语
                            "fontSize" => 14,
                            "fontWeight" => "normal",
                            'unit' => '', // 单位，最多8位数
                            'formatLimit' => [], // 格式限制，support_negative_number：支持输入负数，number_scope：限制填写范围，support_decimal：支持输入小数（多选）
                            'numberScope' => [
                                'min' => 0,
                                'max' => 0
                            ]
                        ]
                    ],
                    'FormRadio' => [
                        'title' => '单选项',
                        'icon' => 'iconfont icona-duihaopc30',
                        'path' => 'edit-form-radio', // 编辑组件属性名称
                        'uses' => 0, // 最大添加数量
                        'sort' => 10005,
                        // 组件属性
                        'template' => [
                            "textColor" => "#303133", // 文字颜色
                            'pageStartBgColor' => '#FFFFFF', // 底部背景颜色（开始）
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
                                "top" => 8, // 上边距
                                "bottom" => 8, // 下边距
                                "both" => 10 // 左右边距
                            ],
                        ],
                        'value' => [
                            // 表单的公共属性
                            'field' => [
                                'name' => '单选项', // 字段名称
                                // 字段说明，支持修改颜色、大小
                                'remark' => [
                                    'text' => '',
                                    'color' => '#999999',
                                    "fontSize" => 14,
                                ],
                                'required' => false, // 是否必填 true：是，false：否
                                'unique' => false, // 内容不可重复提交 true：是，false：否
                                'autofill' => false, // 自动填充上次填写的内容 true：开启，false：关闭
                                'privacyProtection' => false, // 隐私保护 true：开启，false：关闭，隐藏逻辑各组件自行处理
                                'cache' => true, // 开启本地数据缓存 true：开启，false：关闭
                                'default' => [], // 默认值 存储数据类型不同，各组件自行处理
                                'value' => [], // 字段值 存储数据类型不同，各组件自行处理
                            ],
                            "fontSize" => 14,
                            "fontWeight" => "normal",
                            'style' => 'style-1', // 展示样式 style-1：默认，list：列表，style-3：下拉选择
                            'options' => [
                                [
                                    'id' => unique_random(12), // 唯一值，用于排序
                                    'text' => '选项1'
                                ],
                                [
                                    'id' => unique_random(12), // 唯一值，用于排序
                                    'text' => '选项2'
                                ],
                            ],
                            // 逻辑规则
                            'logicalRule' => [
                                [
                                    'triggerOptionId' => '', // 触发单选项id
                                    // 执行事件，存储组件对象
                                    'execEvent' => [
                                        [
                                            'id' => '', // 组件唯一id，例如：5fu1fk9c6cs0
                                            'componentName' => '', // 组件标识，例如：FormInput
                                            'componentTitle' => '', // 组件名称，例如：单行文本
                                        ]
                                    ],
                                ]
                            ]
                        ],
                        // 渲染值
                        'render' => function($data) {
                            if (!empty($data)) {
                                $data = json_decode($data, true);
                                if (!empty($data)) {
                                    return $data[ 0 ][ 'text' ];
                                }
                            }
                            return '';
                        },
                        // 转换类型
                        'convert' => function($data) {
                            $data = json_decode($data, true);
                            return $data;
                        }
                    ],
                    'FormCheckbox' => [
                        'title' => '多选项',
                        'icon' => 'iconfont icona-duoxuanxiangpc301',
                        'path' => 'edit-form-checkbox', // 编辑组件属性名称
                        'uses' => 0, // 最大添加数量
                        'sort' => 10006,
                        // 组件属性
                        'template' => [
                            "textColor" => "#303133", // 文字颜色
                            'pageStartBgColor' => '#FFFFFF', // 底部背景颜色（开始）
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
                                "top" => 8, // 上边距
                                "bottom" => 8, // 下边距
                                "both" => 10 // 左右边距
                            ],
                        ],
                        'value' => [
                            'field' => [
                                'name' => '多选项', // 字段名称
                                // 字段说明，可以修改颜色、大小，【表单的公共属性，考虑在 template中定义】
                                'remark' => [
                                    'text' => '',
                                    'color' => '#999999',
                                    "fontSize" => 14,
                                ],
                                'required' => false, // 是否必填 true：是，false：否
                                'unique' => false, // 内容不可重复提交 true：是，false：否
                                'autofill' => false, // 自动填充上次填写的内容 true：开启，false：关闭
                                'privacyProtection' => false, // 隐私保护 true：开启，false：关闭，隐藏逻辑各组件自行处理
                                'cache' => true, // 开启本地数据缓存 true：开启，false：关闭
                                'default' => [], // 默认值 存储数据类型不同，各组件自行处理
                                'value' => [], // 字段值 存储数据类型不同，各组件自行处理
                            ],
                            "fontSize" => 14,
                            "fontWeight" => "normal",
                            'style' => 'style-1', // 展示样式 style-1：默认，style-2：列表，style-3：下拉选择
                            'options' => [
                                [
                                    'id' => unique_random(12), // 唯一值，用于排序
                                    'text' => '选项1'
                                ],
                                [
                                    'id' => unique_random(12), // 唯一值，用于排序
                                    'text' => '选项2'
                                ],
                            ],
                            // 可选数量
                            'selectableNum' => [
                                'min' => 0, // 最少选择 N 项
                                'max' => 0 // 最多选择 N 项
                            ]
                        ],
                        // 渲染值
                        'render' => function($data) {
                            if (!empty($data)) {
                                $data = json_decode($data, true);
                                $value = [];
                                foreach ($data as $k => $v) {
                                    $value[] = $v[ 'text' ];
                                }
                                return implode(',', $value);
                            }
                            return '';
                        },
                        // 转换类型
                        'convert' => function($data) {
                            $data = json_decode($data, true);
                            return $data;
                        }
                    ],
//                    'FormWechatName' => [
//                        'title' => '微信名',
//                        'icon' => 'iconfont iconbiaotipc',
//                        'path' => 'edit-form-wechat-name', // 编辑组件属性名称
//                        'uses' => 1, // 最大添加数量
//                        'sort' => 10007,
//                        // 组件属性
//                        'template' => [
//                            "textColor" => "#303133", // 文字颜色
//                            'pageStartBgColor' => '#FFFFFF', // 底部背景颜色（开始）
//                            'pageEndBgColor' => '', // 底部背景颜色（结束）
//                            'pageGradientAngle' => 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）
//                            'componentBgUrl' => '', // 组件背景图片
//                            'componentBgAlpha' => 2, // 组件背景图片的透明度，0~10
//                            "componentStartBgColor" => '', // 组件背景颜色（开始）
//                            "componentEndBgColor" => '', // 组件背景颜色（结束）
//                            "componentGradientAngle" => 'to bottom', // 渐变角度，上下（to bottom）、左右（to right）
//                            "topRounded" => 0, // 组件上圆角
//                            "bottomRounded" => 0, // 组件下圆角
//                            "elementBgColor" => '', // 元素背景颜色
//                            "topElementRounded" => 0,// 元素上圆角
//                            "bottomElementRounded" => 0, // 元素下圆角
//                            "margin" => [
//                                "top" => 10, // 上边距
//                                "bottom" => 10, // 下边距
//                                "both" => 10 // 左右边距
//                            ],
//                        ],
//                        'value' => [
//                            // 表单的公共属性
//                            'field' => [
//                                'name' => '微信名', // 字段名称
//                                // 字段说明，支持修改颜色、大小
//                                'remark' => [
//                                    'text' => '',
//                                    'color' => '#999999',
//                                    "fontSize" => 14,
//                                ],
//                                'required' => false, // 是否必填 true：是，false：否
//                                'unique' => false, // 内容不可重复提交 true：是，false：否
//                                'autofill' => false, // 自动填充上次填写的内容 true：开启，false：关闭
//                                'privacyProtection' => false, // 隐私保护 true：开启，false：关闭，隐藏逻辑各组件自行处理
//                                'cache' => true, // 开启本地数据缓存 true：开启，false：关闭
//                                'default' => '', // 默认值 存储数据类型不同，各组件自行处理
//                                'value' => '', // 字段值 存储数据类型不同，各组件自行处理
//                            ],
//                            'placeholder' => '请输入', // 提示语
//                            "fontSize" => 14,
//                            "fontWeight" => "normal",
//                        ]
//                    ],
                    'FormMobile' => [
                        'title' => '手机号',
                        'icon' => 'iconfont icona-shoujipc30',
                        'path' => 'edit-form-mobile', // 编辑组件属性名称
                        'uses' => 1, // 最大添加数量
                        'sort' => 10008,
                        // 组件属性
                        'template' => [
                            "textColor" => "#303133", // 文字颜色
                            'pageStartBgColor' => '#FFFFFF', // 底部背景颜色（开始）
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
                                "top" => 8, // 上边距
                                "bottom" => 8, // 下边距
                                "both" => 10 // 左右边距
                            ],
                        ],
                        'value' => [
                            // 表单的公共属性
                            'field' => [
                                'name' => '手机号', // 字段名称
                                // 字段说明，支持修改颜色、大小
                                'remark' => [
                                    'text' => '',
                                    'color' => '#999999',
                                    "fontSize" => 14,
                                ],
                                'required' => false, // 是否必填 true：是，false：否
                                'unique' => false, // 内容不可重复提交 true：是，false：否
                                'autofill' => false, // 自动填充上次填写的内容 true：开启，false：关闭
                                'privacyProtection' => false, // 隐私保护 true：开启，false：关闭，隐藏逻辑各组件自行处理
                                'cache' => true, // 开启本地数据缓存 true：开启，false：关闭
                                'default' => '', // 默认值 存储数据类型不同，各组件自行处理
                                'value' => '', // 字段值 存储数据类型不同，各组件自行处理
                            ],
                            'placeholder' => '请输入', // 提示语
                            "fontSize" => 14,
                            "fontWeight" => "normal",
                        ]
                    ],
                    'FormEmail' => [
                        'title' => '邮箱',
                        'icon' => 'iconfont icona-youxiangpc30',
                        'path' => 'edit-form-email', // 编辑组件属性名称
                        'uses' => 0, // 最大添加数量
                        'sort' => 10009,
                        // 组件属性
                        'template' => [
                            "textColor" => "#303133", // 文字颜色
                            'pageStartBgColor' => '#FFFFFF', // 底部背景颜色（开始）
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
                                "top" => 8, // 上边距
                                "bottom" => 8, // 下边距
                                "both" => 10 // 左右边距
                            ],
                        ],
                        'value' => [
                            // 表单的公共属性
                            'field' => [
                                'name' => '邮箱', // 字段名称
                                // 字段说明，支持修改颜色、大小
                                'remark' => [
                                    'text' => '',
                                    'color' => '#999999',
                                    "fontSize" => 14,
                                ],
                                'required' => false, // 是否必填 true：是，false：否
                                'unique' => false, // 内容不可重复提交 true：是，false：否
                                'autofill' => false, // 自动填充上次填写的内容 true：开启，false：关闭
                                'privacyProtection' => false, // 隐私保护 true：开启，false：关闭，隐藏逻辑各组件自行处理
                                'cache' => true, // 开启本地数据缓存 true：开启，false：关闭
                                'default' => '', // 默认值 存储数据类型不同，各组件自行处理
                                'value' => '', // 字段值 存储数据类型不同，各组件自行处理
                            ],
                            'placeholder' => '请输入', // 提示语
                            "fontSize" => 14,
                            "fontWeight" => "normal",
                        ]
                    ],
                    'FormIdentity' => [
                        'title' => '身份证号',
                        'icon' => 'iconfont icona-shenfenzhengpc30',
                        'path' => 'edit-form-identity', // 编辑组件属性名称
                        'uses' => 1, // 最大添加数量
                        'sort' => 10010,
                        // 组件属性
                        'template' => [
                            "textColor" => "#303133", // 文字颜色
                            'pageStartBgColor' => '#FFFFFF', // 底部背景颜色（开始）
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
                                "top" => 8, // 上边距
                                "bottom" => 8, // 下边距
                                "both" => 10 // 左右边距
                            ],
                        ],
                        'value' => [
                            // 表单的公共属性
                            'field' => [
                                'name' => '身份证号', // 字段名称
                                // 字段说明，支持修改颜色、大小
                                'remark' => [
                                    'text' => '',
                                    'color' => '#999999',
                                    "fontSize" => 14,
                                ],
                                'required' => false, // 是否必填 true：是，false：否
                                'unique' => false, // 内容不可重复提交 true：是，false：否
                                'autofill' => false, // 自动填充上次填写的内容 true：开启，false：关闭
                                'privacyProtection' => false, // 隐私保护 true：开启，false：关闭，隐藏逻辑各组件自行处理
                                'cache' => true, // 开启本地数据缓存 true：开启，false：关闭
                                'default' => '', // 默认值 存储数据类型不同，各组件自行处理
                                'value' => '', // 字段值 存储数据类型不同，各组件自行处理
                            ],
                            'placeholder' => '请输入', // 提示语
                            "fontSize" => 14,
                            "fontWeight" => "normal",
                        ]
                    ],
//                    'FormTable' => [
//                        'title' => '表格',
//                        'icon' => 'iconfont iconbiaotipc',
//                        'path' => 'edit-form-table', // 编辑组件属性名称
//                        'uses' => 0, // 最大添加数量
//                        'sort' => 10011,
//                        // 组件属性
//                        'template' => [
//                            "textColor" => "#303133", // 文字颜色
//                            'pageStartBgColor' => '#FFFFFF', // 底部背景颜色（开始）
//                            'pageEndBgColor' => '', // 底部背景颜色（结束）
//                            'pageGradientAngle' => 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）
//                            'componentBgUrl' => '', // 组件背景图片
//                            'componentBgAlpha' => 2, // 组件背景图片的透明度，0~10
//                            "componentStartBgColor" => '', // 组件背景颜色（开始）
//                            "componentEndBgColor" => '', // 组件背景颜色（结束）
//                            "componentGradientAngle" => 'to bottom', // 渐变角度，上下（to bottom）、左右（to right）
//                            "topRounded" => 0, // 组件上圆角
//                            "bottomRounded" => 0, // 组件下圆角
//                            "elementBgColor" => '', // 元素背景颜色
//                            "topElementRounded" => 0,// 元素上圆角
//                            "bottomElementRounded" => 0, // 元素下圆角
//                            "margin" => [
//                                "top" => 10, // 上边距
//                                "bottom" => 10, // 下边距
//                                "both" => 10 // 左右边距
//                            ],
//                        ],
//                        'value' => [
//                            // 表单的公共属性
//                            'field' => [
//                                'name' => '表格', // 字段名称
//                                // 字段说明，支持修改颜色、大小
//                                'remark' => [
//                                    'text' => '',
//                                    'color' => '#999999',
//                                    "fontSize" => 14,
//                                ],
//                                'required' => false, // 是否必填 true：是，false：否
//                                'unique' => false, // 内容不可重复提交 true：是，false：否
//                                'autofill' => false, // 自动填充上次填写的内容 true：开启，false：关闭
//                                'privacyProtection' => false, // 隐私保护 true：开启，false：关闭，隐藏逻辑各组件自行处理
//                                'cache' => true, // 开启本地数据缓存 true：开启，false：关闭
//                                'detailComponent' => '/src/app/views/diy_form/components/detail-form-table.vue', // 用于详情展示
//                                'default' => '', // 默认值 存储数据类型不同，各组件自行处理
//                                'value' => '', // 字段值 存储数据类型不同，各组件自行处理
//                            ],
//                            "fontSize" => 14,
//                            "fontWeight" => "normal",
//                            // 列设置
//                            'columnList' => [
//                                [
//                                    'id' => '', // 唯一值，用于排序
//                                    'type' => 'text', // 类型，text：文本，number：数字，radio：单选项，checkbox：多选项，todo，不同类型，结构也不一样，这个组件要在前端处理
//                                    'name' => '', // 字段名称
//                                    'value' => '选项1' // 字段值
//                                ],
//                            ],
//                            'autoIncrementControl' => false, // 是否开启自增（展示按钮），添加多个
//                            // 填写限制（开启自增才展示）
//                            'writeLimit' => [
//                                'default' => 2, // 默认展示 N 项
//                                'min' => 0, // 最少填写 N 项
//                                'max' => 0 // 最多填写 N 项
//                            ],
//                            'btnText' => '新增一组'
//                        ],
//                        // 渲染值
//                        'render' => function($data) {
//                            // todo 处理业务数据
//                            return '';
//                        },
//                        // 转换类型
//                        'convert' => function($data) {
//                            // todo 处理业务数据
//                            return $data;
//                        }
//                    ],
                    'FormDate' => [
                        'title' => '日期',
                        'icon' => 'iconfont icona-riqipc30',
                        'path' => 'edit-form-date', // 编辑组件属性名称
                        'uses' => 0, // 最大添加数量
                        'sort' => 10012,
                        // 组件属性
                        'template' => [
                            "textColor" => "#303133", // 文字颜色
                            'pageStartBgColor' => '#FFFFFF', // 底部背景颜色（开始）
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
                                "top" => 8, // 上边距
                                "bottom" => 8, // 下边距
                                "both" => 10 // 左右边距
                            ],
                        ],
                        'value' => [
                            // 表单的公共属性
                            'field' => [
                                'name' => '日期', // 字段名称
                                // 字段说明，支持修改颜色、大小
                                'remark' => [
                                    'text' => '',
                                    'color' => '#999999',
                                    "fontSize" => 14,
                                ],
                                'required' => false, // 是否必填 true：是，false：否
                                'unique' => false, // 内容不可重复提交 true：是，false：否
                                'autofill' => false, // 自动填充上次填写的内容 true：开启，false：关闭
                                'privacyProtection' => false, // 隐私保护 true：开启，false：关闭，隐藏逻辑各组件自行处理
                                'cache' => true, // 开启本地数据缓存 true：开启，false：关闭
                                'default' => [ // 默认值 存储数据类型不同，各组件自行处理
                                    'date' => '', // 例：2024-12-27
                                    'timestamp' => 0, // 例：1735290511
                                ],
                                'value' => [  // 字段值 存储数据类型不同，各组件自行处理
                                    'date' => '', // 例：2024-12-27
                                    'timestamp' => 0, // 例：1735290511
                                ]
                            ],
                            'placeholder' => '请输入', // 提示语
                            "fontSize" => 14,
                            "fontWeight" => "normal",
                            'dateFormat' => 'YYYY年M月D日', // 日期格式，YYYY年M月D日、YYYY-MM-DD、YYYY/MM/DD、YYYY-MM-DD HH:mm、HH:mm
                            'dateWay' => "current", // 默认值方式，current：当前日期，diy：指定日期 todo 在做的过程中可能会再调整字段名称
                            'defaultControl' => true,
                        ],
                        // 渲染值
                        'render' => function($data) {
                            if (!empty($data)) {
                                $data = json_decode($data, true);
                                return "{$data['date']}";
                            }
                            return '';
                        },
                        // 转换类型
                        'convert' => function($data) {
                            $data = json_decode($data, true);
                            return $data;
                        }
                    ],
                    'FormDateScope' => [
                        'title' => '日期范围',
                        'icon' => 'iconfont icona-riqifanweipc30',
                        'path' => 'edit-form-date-scope', // 编辑组件属性名称
                        'uses' => 0, // 最大添加数量
                        'sort' => 10013,
                        // 组件属性
                        'template' => [
                            "textColor" => "#303133", // 文字颜色
                            'pageStartBgColor' => '#FFFFFF', // 底部背景颜色（开始）
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
                                "top" => 8, // 上边距
                                "bottom" => 8, // 下边距
                                "both" => 10 // 左右边距
                            ],
                        ],
                        'value' => [
                            'field' => [
                                'name' => '日期范围', // 字段名称
                                // 字段说明，支持修改颜色、大小
                                'remark' => [
                                    'text' => '',
                                    'color' => '#999999',
                                    "fontSize" => 14,
                                ],
                                'required' => false, // 是否必填 true：是，false：否
                                'unique' => false, // 内容不可重复提交 true：是，false：否
                                'autofill' => false, // 自动填充上次填写的内容 true：开启，false：关闭
                                'privacyProtection' => false, // 隐私保护 true：开启，false：关闭，隐藏逻辑各组件自行处理
                                'cache' => true, // 开启本地数据缓存 true：开启，false：关闭
                                // 默认值 存储数据类型不同，各组件自行处理
                                'default' => [
                                    // todo 在做的过程中可能会再调整字段名称
                                    'start' => [
                                        'date' => '', // 例：2024-12-27
                                        'timestamp' => 0, // 例：1735290511
                                    ],
                                    'end' => [
                                        'date' => '', // 例：2025-01-03
                                        'timestamp' => 0, // 例：1735895311
                                    ],
                                ],
                                // 字段值 存储数据类型不同，各组件自行处理
                                'value' => [
                                    'start' => [
                                        'date' => '', // 例：2024-12-27
                                        'timestamp' => 0, // 例：1735290511
                                    ],
                                    'end' => [
                                        'date' => '', // 例：2025-01-03
                                        'timestamp' => 0, // 例：1735895311
                                    ],
                                ],
                            ],
                            "fontSize" => 14,
                            "fontWeight" => "normal",
                            'dateFormat' => 'YYYY年M月D日', // 日期格式，YYYY年M月D日、YYYY-MM-DD、YYYY/MM/DD、YYYY-MM-DD HH:mm
                            'start' => [
                                'placeholder' => '请选择起始日期',
                                'dateWay' => "current", // 默认值方式，current：当前日期，diy：指定日期 todo 在做的过程中可能会再调整字段名称
                                'defaultControl' => true,
                            ],
                            'end' => [
                                'placeholder' => '请选择结束日期',
                                'dateWay' => "current", // 默认值方式，current：当前日期，diy：指定日期 todo 在做的过程中可能会再调整字段名称
                                'defaultControl' => true,
                            ],
                        ],
                        // 渲染值
                        'render' => function($data) {
                            if (!empty($data)) {
                                $data = json_decode($data, true);
                                return "{$data['start']['date']}-{$data['end']['date']}";
                            }
                            return '';
                        },
                        // 转换类型
                        'convert' => function($data) {
                            $data = json_decode($data, true);
                            return $data;
                        }
                    ],
                    'FormTime' => [
                        'title' => '时间',
                        'icon' => 'iconfont icona-shijianpc30-1',
                        'path' => 'edit-form-time', // 编辑组件属性名称
                        'uses' => 0, // 最大添加数量
                        'sort' => 10014,
                        // 组件属性
                        'template' => [
                            "textColor" => "#303133", // 文字颜色
                            'pageStartBgColor' => '#FFFFFF', // 底部背景颜色（开始）
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
                                "top" => 8, // 上边距
                                "bottom" => 8, // 下边距
                                "both" => 10 // 左右边距
                            ],
                        ],
                        'value' => [
                            // 表单的公共属性
                            'field' => [
                                'name' => '时间', // 字段名称
                                // 字段说明，支持修改颜色、大小
                                'remark' => [
                                    'text' => '',
                                    'color' => '#999999',
                                    "fontSize" => 14,
                                ],
                                'required' => false, // 是否必填 true：是，false：否
                                'unique' => false, // 内容不可重复提交 true：是，false：否
                                'autofill' => false, // 自动填充上次填写的内容 true：开启，false：关闭
                                'privacyProtection' => false, // 隐私保护 true：开启，false：关闭，隐藏逻辑各组件自行处理
                                'cache' => true, // 开启本地数据缓存 true：开启，false：关闭
                                'default' => '', // 默认值 存储数据类型不同，各组件自行处理
                                'value' => '', // 字段值 存储数据类型不同，各组件自行处理
                            ],
                            'placeholder' => '请输入', // 提示语
                            "fontSize" => 14,
                            "fontWeight" => "normal",
                            'timeWay' => "current", // 默认值方式，current：当前时间，diy：指定时间 todo 在做的过程中可能会再调整字段名称
                            'defaultControl' => true,
                        ]
                    ],
                    'FormTimeScope' => [
                        'title' => '时间范围',
                        'icon' => 'iconfont icona-shijianfanweipc30',
                        'path' => 'edit-form-time-scope', // 编辑组件属性名称
                        'uses' => 0, // 最大添加数量
                        'sort' => 10015,
                        // 组件属性
                        'template' => [
                            "textColor" => "#303133", // 文字颜色
                            'pageStartBgColor' => '#FFFFFF', // 底部背景颜色（开始）
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
                                "top" => 8, // 上边距
                                "bottom" => 8, // 下边距
                                "both" => 10 // 左右边距
                            ],
                        ],
                        'value' => [
                            'field' => [
                                'name' => '时间范围', // 字段名称
                                // 字段说明，支持修改颜色、大小
                                'remark' => [
                                    'text' => '',
                                    'color' => '#999999',
                                    "fontSize" => 14,
                                ],
                                'required' => false, // 是否必填 true：是，false：否
                                'unique' => false, // 内容不可重复提交 true：是，false：否
                                'autofill' => false, // 自动填充上次填写的内容 true：开启，false：关闭
                                'privacyProtection' => false, // 隐私保护 true：开启，false：关闭，隐藏逻辑各组件自行处理
                                'cache' => true, // 开启本地数据缓存 true：开启，false：关闭
                                // 默认值 存储数据类型不同，各组件自行处理
                                'default' => [
                                    'start' => [
                                        'date' => '', // 例：09:00
                                        'timestamp' => 0, // 例：32400
                                    ],
                                    'end' => [
                                        'date' => '', // 例：18:30
                                        'timestamp' => 0, // 66600
                                    ]
                                ],
                                // 字段值 存储数据类型不同，各组件自行处理
                                'value' => [
                                    'start' => [
                                        'date' => '', // 例：09:00
                                        'timestamp' => 0, // 例：32400
                                    ],
                                    'end' => [
                                        'date' => '', // 例：18:30
                                        'timestamp' => 0, // 66600
                                    ]
                                ]
                            ],
                            "fontSize" => 14,
                            "fontWeight" => "normal",
                            'start' => [
                                'placeholder' => '请选择起始时间',
                                'timeWay' => "current", // 默认值方式，current：当前时间，diy：指定时间 todo 在做的过程中可能会再调整字段名称
                                'defaultControl' => true,
                            ],
                            'end' => [
                                'placeholder' => '请选择结束时间',
                                'timeWay' => "current", // 默认值方式，current：当前时间，diy：指定时间 todo 在做的过程中可能会再调整字段名称
                                'defaultControl' => true,
                            ],
                        ],
                        // 渲染值
                        'render' => function($data) {
                            if (!empty($data)) {
                                $data = json_decode($data, true);
                                return "{$data['start']['date']}-{$data['end']['date']}";
                            }
                            return '';
                        },
                        // 转换类型
                        'convert' => function($data) {
                            $data = json_decode($data, true);
                            return $data;
                        }
                    ],
//                    'FormLocation' => [
//                        'title' => '定位',
//                        'icon' => 'iconfont iconbiaotipc',
//                        'path' => 'edit-form-location', // 编辑组件属性名称
//                        'uses' => 1, // 最大添加数量
//                        'sort' => 10016,
//                        // 组件属性
//                        'template' => [
//                            "textColor" => "#303133", // 文字颜色
//                            'pageStartBgColor' => '#FFFFFF', // 底部背景颜色（开始）
//                            'pageEndBgColor' => '', // 底部背景颜色（结束）
//                            'pageGradientAngle' => 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）
//                            'componentBgUrl' => '', // 组件背景图片
//                            'componentBgAlpha' => 2, // 组件背景图片的透明度，0~10
//                            "componentStartBgColor" => '', // 组件背景颜色（开始）
//                            "componentEndBgColor" => '', // 组件背景颜色（结束）
//                            "componentGradientAngle" => 'to bottom', // 渐变角度，上下（to bottom）、左右（to right）
//                            "topRounded" => 0, // 组件上圆角
//                            "bottomRounded" => 0, // 组件下圆角
//                            "elementBgColor" => '', // 元素背景颜色
//                            "topElementRounded" => 0,// 元素上圆角
//                            "bottomElementRounded" => 0, // 元素下圆角
//                            "margin" => [
//                                "top" => 10, // 上边距
//                                "bottom" => 10, // 下边距
//                                "both" => 10 // 左右边距
//                            ],
//                        ],
//                        'value' => [
//                            // 表单的公共属性
//                            'field' => [
//                                'name' => '定位', // 字段名称
//                                // 字段说明，支持修改颜色、大小
//                                'remark' => [
//                                    'text' => '',
//                                    'color' => '#999999',
//                                    "fontSize" => 14,
//                                ],
//                                'required' => false, // 是否必填 true：是，false：否
//                                'unique' => false, // 内容不可重复提交 true：是，false：否
//                                'autofill' => false, // 自动填充上次填写的内容 true：开启，false：关闭
//                                'privacyProtection' => false, // 隐私保护 true：开启，false：关闭，隐藏逻辑各组件自行处理
//                                'cache' => true, // 开启本地数据缓存 true：开启，false：关闭
//                                'default' => '', // 默认值 存储数据类型不同，各组件自行处理
//                                'value' => '', // 字段值 存储数据类型不同，各组件自行处理
//                            ],
//                            'placeholder' => '请输入', // 提示语
//                            "fontSize" => 14,
//                            "fontWeight" => "normal",
//                            'mode' => 'authorized_wechat_location', // 获取方式，authorized_wechat_location：授权微信定位，open_choose_location：手动选择定位
//                        ]
//                    ],
//                    'FormAddress' => [
//                        'title' => '地址',
//                        'icon' => 'iconfont iconbiaotipc',
//                        'path' => 'edit-form-address', // 编辑组件属性名称
//                        'uses' => 0, // 最大添加数量
//                        'sort' => 10017,
//                        // 组件属性
//                        'template' => [
//                            "textColor" => "#303133", // 文字颜色
//                            'pageStartBgColor' => '#FFFFFF', // 底部背景颜色（开始）
//                            'pageEndBgColor' => '', // 底部背景颜色（结束）
//                            'pageGradientAngle' => 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）
//                            'componentBgUrl' => '', // 组件背景图片
//                            'componentBgAlpha' => 2, // 组件背景图片的透明度，0~10
//                            "componentStartBgColor" => '', // 组件背景颜色（开始）
//                            "componentEndBgColor" => '', // 组件背景颜色（结束）
//                            "componentGradientAngle" => 'to bottom', // 渐变角度，上下（to bottom）、左右（to right）
//                            "topRounded" => 0, // 组件上圆角
//                            "bottomRounded" => 0, // 组件下圆角
//                            "elementBgColor" => '', // 元素背景颜色
//                            "topElementRounded" => 0,// 元素上圆角
//                            "bottomElementRounded" => 0, // 元素下圆角
//                            "margin" => [
//                                "top" => 10, // 上边距
//                                "bottom" => 10, // 下边距
//                                "both" => 10 // 左右边距
//                            ],
//                        ],
//                        'value' => [
//                            // 表单的公共属性
//                            'field' => [
//                                'name' => '地址', // 字段名称
//                                // 字段说明，支持修改颜色、大小
//                                'remark' => [
//                                    'text' => '',
//                                    'color' => '#999999',
//                                    "fontSize" => 14,
//                                ],
//                                'required' => false, // 是否必填 true：是，false：否
//                                'unique' => false, // 内容不可重复提交 true：是，false：否
//                                'autofill' => false, // 自动填充上次填写的内容 true：开启，false：关闭
//                                'privacyProtection' => false, // 隐私保护 true：开启，false：关闭，隐藏逻辑各组件自行处理
//                                'default' => '', // 默认值 存储数据类型不同，各组件自行处理 todo 设置默认省/市/区/街道
//                                'value' => '', // 字段值 存储数据类型不同，各组件自行处理
//                            ],
//                            'placeholder' => '请输入', // 提示语
//                            "fontSize" => 14,
//                            "fontWeight" => "normal",
//                            'addressFormat' => 'province/city/district/address', // 地址格式
//                        ]
//                    ],
                    'FormImage' => [
                        'title' => '图片',
                        'icon' => 'iconfont icona-tupianpc30',
                        'path' => 'edit-form-image', // 编辑组件属性名称
                        'uses' => 0, // 最大添加数量
                        'sort' => 10018,
                        // 组件属性
                        'template' => [
                            "textColor" => "#303133", // 文字颜色
                            'pageStartBgColor' => '#FFFFFF', // 底部背景颜色（开始）
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
                                "top" => 8, // 上边距
                                "bottom" => 8, // 下边距
                                "both" => 10 // 左右边距
                            ],
                        ],
                        'value' => [
                            // 表单的公共属性
                            'field' => [
                                'name' => '图片', // 字段名称
                                // 字段说明，支持修改颜色、大小
                                'remark' => [
                                    'text' => '',
                                    'color' => '#999999',
                                    "fontSize" => 14,
                                ],
                                'required' => false, // 是否必填 true：是，false：否
                                'unique' => false, // 内容不可重复提交 true：是，false：否
                                'autofill' => false, // 自动填充上次填写的内容 true：开启，false：关闭
                                'privacyProtection' => false, // 隐私保护 true：开启，false：关闭，隐藏逻辑各组件自行处理
                                'cache' => true, // 开启本地数据缓存 true：开启，false：关闭
                                'detailComponent' => '/src/app/views/diy_form/components/detail-form-image.vue', // 用于详情展示
                                'default' => '', // 默认值 存储数据类型不同，各组件自行处理
                                'value' => [], // 字段值 存储数据类型不同，各组件自行处理
                            ],
                            "fontSize" => 14,
                            "fontWeight" => "normal",
                            /**
                             * 上传方式
                             * select_from_album：从相册选择
                             * take_pictures：拍照上传（可用于防作假）
                             * 请选择拍照方式：快速拍照模式（支持连拍，支持闪光灯拍照）、微信拍照模式（每次只能拍摄1张）
                             * is_album_upload 相册
                             * is_photo_upload  拍照
                             * is_chat_upload 微信
                             * open_continuous  开放的连续
                             */
                            // 'uploadMode' => [ 'take_pictures', 'select_from_album' ], // 上传方式
                            'uploadMode' => [ 'take_pictures', 'select_from_album' ], // 上传方式
                            'limit' => 9, // 限制上传数量
                        ],
                        // 渲染值
                        'render' => function($data) {
                            if (!empty($data)) {
                                $data = json_decode($data, true);
                                $value = [];
                                foreach ($data as $k => $v) {
                                    $value[] = str_starts_with($v, 'http') ? $v : request()->domain() . '/' . $v;
                                }
                                return implode(',', $value);
                            }
                            return '';
                        },
                        // 转换类型
                        'convert' => function($data) {
                            $data = json_decode($data, true);
                            return $data;
                        }
                    ],
//                    'FormVideo' => [
//                        'title' => '视频',
//                        'icon' => 'iconfont iconbiaotipc',
//                        'path' => 'edit-form-video', // 编辑组件属性名称
//                        'uses' => 0, // 最大添加数量
//                        'sort' => 10019,
//                        // 组件属性
//                        'template' => [
//                            "textColor" => "#303133", // 文字颜色
//                            'pageStartBgColor' => '#FFFFFF', // 底部背景颜色（开始）
//                            'pageEndBgColor' => '', // 底部背景颜色（结束）
//                            'pageGradientAngle' => 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）
//                            'componentBgUrl' => '', // 组件背景图片
//                            'componentBgAlpha' => 2, // 组件背景图片的透明度，0~10
//                            "componentStartBgColor" => '', // 组件背景颜色（开始）
//                            "componentEndBgColor" => '', // 组件背景颜色（结束）
//                            "componentGradientAngle" => 'to bottom', // 渐变角度，上下（to bottom）、左右（to right）
//                            "topRounded" => 0, // 组件上圆角
//                            "bottomRounded" => 0, // 组件下圆角
//                            "elementBgColor" => '', // 元素背景颜色
//                            "topElementRounded" => 0,// 元素上圆角
//                            "bottomElementRounded" => 0, // 元素下圆角
//                            "margin" => [
//                                "top" => 10, // 上边距
//                                "bottom" => 10, // 下边距
//                                "both" => 10 // 左右边距
//                            ],
//                        ],
//                        'value' => [
//                            // 表单的公共属性
//                            'field' => [
//                                'name' => '视频', // 字段名称
//                                // 字段说明，支持修改颜色、大小
//                                'remark' => [
//                                    'text' => '',
//                                    'color' => '#999999',
//                                    "fontSize" => 14,
//                                ],
//                                'required' => false, // 是否必填 true：是，false：否
//                                'unique' => false, // 内容不可重复提交 true：是，false：否
//                                'autofill' => false, // 自动填充上次填写的内容 true：开启，false：关闭
//                                'privacyProtection' => false, // 隐私保护 true：开启，false：关闭，隐藏逻辑各组件自行处理
//                                'cache' => true, // 开启本地数据缓存 true：开启，false：关闭
//                                'detailComponent' => '/src/app/views/diy_form/components/detail-form-video.vue', // 用于详情展示
//                                'default' => '', // 默认值 存储数据类型不同，各组件自行处理
//                                'value' => '', // 字段值 存储数据类型不同，各组件自行处理
//                            ],
//                            "fontSize" => 14,
//                            "fontWeight" => "normal",
//                            /**
//                             * 上传方式
//                             * shoot_and_album：拍摄和相册
//                             * shoot_only：只允许拍摄
//                             */
//                            'uploadMode' => 'shoot_and_album', // 上传方式
//                        ]
//                    ],
//                    'FormFile' => [
//                        'title' => '文件',
//                        'icon' => 'iconfont iconbiaotipc',
//                        'path' => 'edit-form-file', // 编辑组件属性名称
//                        'uses' => 0, // 最大添加数量
//                        'sort' => 10020,
//                        // 组件属性
//                        'template' => [
//                            "textColor" => "#303133", // 文字颜色
//                            'pageStartBgColor' => '#FFFFFF', // 底部背景颜色（开始）
//                            'pageEndBgColor' => '', // 底部背景颜色（结束）
//                            'pageGradientAngle' => 'to bottom', // 渐变角度，从上到下（to bottom）、从左到右（to right）
//                            'componentBgUrl' => '', // 组件背景图片
//                            'componentBgAlpha' => 2, // 组件背景图片的透明度，0~10
//                            "componentStartBgColor" => '', // 组件背景颜色（开始）
//                            "componentEndBgColor" => '', // 组件背景颜色（结束）
//                            "componentGradientAngle" => 'to bottom', // 渐变角度，上下（to bottom）、左右（to right）
//                            "topRounded" => 0, // 组件上圆角
//                            "bottomRounded" => 0, // 组件下圆角
//                            "elementBgColor" => '', // 元素背景颜色
//                            "topElementRounded" => 0,// 元素上圆角
//                            "bottomElementRounded" => 0, // 元素下圆角
//                            "margin" => [
//                                "top" => 10, // 上边距
//                                "bottom" => 10, // 下边距
//                                "both" => 10 // 左右边距
//                            ],
//                        ],
//                        'value' => [
//                            // 表单的公共属性
//                            'field' => [
//                                'name' => '文件', // 字段名称
//                                // 字段说明，支持修改颜色、大小
//                                'remark' => [
//                                    'text' => '',
//                                    'color' => '#999999',
//                                    "fontSize" => 14,
//                                ],
//                                'required' => false, // 是否必填 true：是，false：否
//                                'unique' => false, // 内容不可重复提交 true：是，false：否
//                                'autofill' => false, // 自动填充上次填写的内容 true：开启，false：关闭
//                                'privacyProtection' => false, // 隐私保护 true：开启，false：关闭，隐藏逻辑各组件自行处理
//                                'cache' => true, // 开启本地数据缓存 true：开启，false：关闭
//                                'detailComponent' => '/src/app/views/diy_form/components/detail-form-file.vue', // 用于详情展示
//                                'default' => '', // 默认值 存储数据类型不同，各组件自行处理
//                                'value' => '', // 字段值 存储数据类型不同，各组件自行处理
//                            ],
//                            "fontSize" => 14,
//                            "fontWeight" => "normal",
//                            'limitUploadSize' => 30720,  // 限制上传大小，30MB
//                        ]
//                    ],
                ],
            ],
        ];
        $list = ( new DictLoader("DiyFormComponent") )->load($system_components);
        if (!empty($params[ 'component_name' ])) {
            $component = [];
            foreach ($list as $k => $v) {
                $is_ok = false;
                foreach ($v[ 'list' ] as $ck => $cv) {
                    if ($ck == $params[ 'component_name' ]) {
                        $is_ok = true;
                        $component = $cv;
                        break;
                    }
                }
                if ($is_ok) {
                    break;
                }
            }
            return $component;
        } else {
            return $list;
        }
    }

}

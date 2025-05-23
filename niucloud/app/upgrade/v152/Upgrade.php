<?php

namespace app\upgrade\v152;

use app\model\addon\Addon;
use app\model\diy\Diy;
use app\model\diy\DiyTheme;
use app\model\diy_form\DiyForm;
use app\service\core\addon\CoreAddonService;

class Upgrade
{

    public function handle()
    {
        $this->handleDiyData();
        $this->handleDiyThemeData();
        $this->handleDiyFormData();
    }

    /**
     * 处理自定义数据
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function handleDiyData()
    {
        $diy_model = new Diy();
        $where = [
            [ 'value', '<>', '' ]
        ];
        $field = 'id,name,title,template,value';
        $list = $diy_model->where($where)->field($field)->select()->toArray();

        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $diy_data = json_decode($v[ 'value' ], true);

                foreach ($diy_data[ 'value' ] as $ck => $cv) {

                    // 浮动按钮 组件
                    if ($cv[ 'componentName' ] == 'FloatBtn') {

                        // 左右偏移量
                        if (!isset($diy_data[ 'value' ][ $ck ][ 'lateralOffset' ])) {
                            $diy_data[ 'value' ][ $ck ][ 'lateralOffset' ] = 15;
                        }

                    }

                    // 商品列表 组件、积分商品 组件、商品推荐、新人专享、分销商品
                    if (in_array($cv[ 'componentName' ], [ 'GoodsList', 'ShopExchangeGoods', 'ShopNewcomer', 'FenxiaoGoodsList' ])) {
                        // 图片圆角
                        if (!isset($diy_data[ 'value' ][ $ck ][ 'imgElementRounded' ])) {
                            $diy_data[ 'value' ][ $ck ][ 'imgElementRounded' ] = 10;
                        }
                    }


                    // 多商品组 组件
                    if ($cv[ 'componentName' ] == 'ManyGoodsList') {

                        // 图片圆角
                        if (!isset($diy_data[ 'value' ][ $ck ][ 'imgElementRounded' ])) {
                            $diy_data[ 'value' ][ $ck ][ 'imgElementRounded' ] = 0;
                        }
                    }

                    // 会员信息
                    if ($cv[ 'componentName' ] == 'ShopMemberInfo') {

                        // 账户颜色
                        if (!isset($diy_data[ 'value' ][ $ck ][ 'accountTextColor' ])) {
                            $diy_data[ 'value' ][ $ck ][ 'accountTextColor' ] = '#666666';
                        }
                    }

                }

                $diy_data = json_encode($diy_data);
                $diy_model->where([ [ 'id', '=', $v[ 'id' ] ] ])->update([ 'value' => $diy_data ]);
            }
        }

    }

    /**
     * 处理主题风格数据
     */
    private function handleDiyThemeData()
    {
        $diy_theme_model = new DiyTheme();
        // 删除原有所有主题风格颜色
        $diy_theme_model->where([ [ 'id', '>', 0 ] ])->delete();
        // 创建默认主题风格颜色
        $this->initDefaultDiyTheme();
    }

    /**
     * 处理表单数据
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function handleDiyFormData()
    {
        $diy_form_model = new DiyForm();
        $field = 'form_id,value';
        $list = $diy_form_model->where([ [ 'value', '<>', '' ] ])->field($field)->select()->toArray();

        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $diy_form_data = $v[ 'value' ];
                foreach ($diy_form_data[ 'value' ] as $ck => $cv) {
                    if ($cv[ 'componentType' ] == 'diy_form') {
                        if (in_array($cv[ 'componentName' ], [ 'FormDate', 'FormDateScope', 'FormTime', 'FormTimeScope' ])) {
                            $diy_form_data[ 'value' ][ $ck ][ 'cache' ] = false;
                        } else {
                            $diy_form_data[ 'value' ][ $ck ][ 'cache' ] = true;
                        }

                    }
                }

                $diy_form_model->where([ [ 'form_id', '=', $v[ 'form_id' ] ] ])->update([ 'value' => $diy_form_data ]);

            }

        }


    }

    private function getAppTheme()
    {
        return [
            // 系统主题色
            'theme_color' => [
                [
                    'title' => '商务蓝',
                    'name' => 'blue',
                    'theme' => [
                        '--page-bg-color' => "#F6F6F6",//页面背景色
                        '--primary-color' => "#007aff",//主色调
                        '--primary-color-light' => "#ecf5ff",//主色调浅色（淡）
                        '--primary-color-light2' => "#FFF4ED",//主色调深色（深）
                        '--primary-help-color2' => "#007aff",//辅色调
                        '--primary-color-dark' => "#999999",//灰色调
                        '--primary-color-disabled' => "#CCCCCC",//禁用色
                    ]
                ]
            ],
        ];
    }

    private function getCmsTheme()
    {
        return [
            // 应用主题色
            'theme_color' => [
                [
                    'title' => '商务蓝',
                    'name' => 'blue',
                    'theme' => [
                        '--page-bg-color' => "#F6F6F6",//页面背景色
                        '--price-text-color' => "#FF2525",//价格颜色
                        '--primary-color' => "#007aff",//主色调
                        '--primary-color-light' => "#ecf5ff",//主色调浅色（淡）
                        '--primary-color-light2' => "#FFF4ED",//主色调深色（深）
                        '--primary-help-color2' => "#007aff",//辅色调
                        '--primary-color-dark' => "#999999",//灰色调
                        '--primary-color-disabled' => "#CCCCCC",//禁用色
                    ]
                ]
            ],
        ];
    }

    private function getO2oTheme()
    {
        return [
            'theme_color' => [
                [
                    'title' => '活力橙',
                    'name' => 'orange',
                    'theme' => [
                        '--page-bg-color' => "#F6F6F6",//页面背景色
                        '--price-text-color' => "#FF2525",//价格颜色
                        '--primary-color' => "#FA6400",//主色调
                        '--primary-color-light' => "#FFF4ED",//主色调浅色（淡）
                        '--primary-color-light2' => "#FFF4ED",//主色调深色（深）
                        '--primary-help-color2' => "#fa6400",//辅色调
                        '--primary-color-dark' => "#999999",//灰色调
                        '--primary-color-disabled' => "#CCCCCC",//禁用色
                    ]
                ],
            ],
        ];
    }

    private function getShopTheme()
    {
        return [
            'theme_color' => [
                [
                    'title' => '热情红',
                    'name' => 'red',
                    'theme' => [
                        '--page-bg-color' => "#F6F6F6",//页面背景色
                        '--price-text-color' => "#FF4142",//价格颜色
                        '--primary-color' => "#FF4142",//主色调
                        '--primary-color-light' => "#FFEAEA",//主色调浅色（淡）
                        '--primary-color-light2' => "#FFF7F7",//主色调深色（深）
                        '--primary-help-color1' => "#FFB000",//辅色调1
                        '--primary-help-color2' => "#FB7939",//辅色调2
                        '--primary-help-color3' => "#F26F3E",//辅色调3
                        '--primary-help-color4' => "#FFB397",//辅色调4
                        '--primary-help-color5' => "#FFA029",//辅色调5
                        '--primary-color-dark' => "#999999",//灰色调
                        '--primary-color-disabled' => "#CCCCCC",//禁用色
                    ],
                ],
            ],
        ];
    }

    private function getFenxiaoTheme()
    {
        return [
            'theme_color' => [
                [
                    'title' => '热情红',
                    'name' => 'red',
                    'theme' => [
                        '--page-bg-color' => "#F6F6F6",//页面背景色
                        '--price-text-color' => "#FF4142",//价格颜色
                        '--primary-color' => "#FF4142",//主色调
                        '--primary-color-light' => "#FFEAEA",//主色调浅色（淡）
                        '--primary-color-light2' => "#FFF7F7",//主色调深色（深）
                        '--primary-help-color1' => "#FFB000",//辅色调1
                        '--primary-help-color2' => "#FB7939",//辅色调2
                        '--primary-help-color3' => "#F26F3E",//辅色调3
                        '--primary-help-color4' => "#FFB397",//辅色调4
                        '--primary-help-color5' => "#FFA029",//辅色调5
                        '--primary-color-dark' => "#999999",//灰色调
                        '--primary-color-disabled' => "#CCCCCC",//禁用色
                    ],
                ],
            ],
        ];
    }

    private function getGiftcardTheme()
    {
        return [
            'theme_color' => [
                [
                    'title' => '热情红',
                    'name' => 'red',
                    'theme' => [
                        '--page-bg-color' => "#F6F6F6",//页面背景色
                        '--price-text-color' => "#FF4142",//价格颜色
                        '--primary-color' => "#FF4142",//主色调
                        '--primary-color-light' => "#FFEAEA",//主色调浅色（淡）
                        '--primary-color-light2' => "#FFF7F7",//主色调深色（深）
                        '--primary-help-color1' => "#FFB000",//辅色调1
                        '--primary-help-color2' => "#FB7939",//辅色调2
                        '--primary-help-color3' => "#F26F3E",//辅色调3
                        '--primary-help-color4' => "#FFB397",//辅色调4
                        '--primary-help-color5' => "#FFA029",//辅色调5
                        '--primary-color-dark' => "#999999",//灰色调
                        '--primary-color-disabled' => "#CCCCCC",//禁用色
                    ],
                ],
            ],
        ];
    }

    private function getSowCommunityTheme()
    {
        return [
            'theme_color' => [
                [
                    'title' => '热情红',
                    'name' => 'red',
                    'theme' => [
                        '--page-bg-color' => "#F6F6F6",//页面背景色
                        '--price-text-color' => "#FF4142",//价格颜色
                        '--primary-color' => "#FF4142",//主色调
                        '--primary-color-light' => "#FFEAEA",//主色调浅色（淡）
                        '--primary-color-light2' => "#FFF7F7",//主色调深色（深）
                        '--primary-help-color2' => "#FFB000",//辅色调
                        '--primary-color-dark' => "#999999",//灰色调
                        '--primary-color-disabled' => "#CCCCCC",//禁用色
                    ],
                ],
            ],
        ];
    }

    private function getTourismTheme()
    {
        return [
            'theme_color' => [
                [
                    'title' => '活力橙',
                    'name' => 'orange',
                    'theme' => [
                        '--page-bg-color' => "#F6F6F6",//页面背景色
                        '--price-text-color' => "#F55246",//价格颜色
                        '--primary-color' => "#FA6400",//主色调
                        '--primary-color-light' => "#FFF4ED",//主色调浅色（淡）
                        '--primary-color-light2' => "#FFF4ED",//主色调深色（深）
                        '--primary-help-color2' => "#fa6400",//辅色调
                        '--primary-color-dark' => "#999999",//灰色调
                        '--primary-color-disabled' => "#CCCCCC",//禁用色
                    ],
                ],
            ],
        ];
    }

    private function getVipcardTheme()
    {
        return [
            'theme_color' => [
                [
                    'title' => '活力橙',
                    'name' => 'orange',
                    'theme' => [
                        '--page-bg-color' => "#F6F6F6",//页面背景色
                        '--price-text-color' => "#FF4142",//价格颜色
                        '--primary-color' => "#FA6400",//主色调
                        '--primary-color-light' => "#FFF4ED",//主色调浅色（淡）
                        '--primary-color-light2' => "#FFF4ED",//主色调深色（深）
                        '--primary-help-color2' => "#fa6400",//辅色调
                        '--primary-color-dark' => "#999999",//灰色调
                        '--primary-color-disabled' => "#CCCCCC",//禁用色
                    ],
                ],
            ],
        ];
    }


    /**
     * 初始化默认自定义主题配色
     * @return true
     */
    private function initDefaultDiyTheme()
    {
        $addon_list = ( new CoreAddonService() )->getInstallAddonList();
        $apps = [];
        foreach ($addon_list as $k => $v) {
            if ($v[ 'type' ] == 'app') {
                $apps[] = $v;
            }
        }
        $system_theme = $this->getAppTheme();
        foreach ($system_theme[ 'theme_color' ] as $k => $v) {
            $data[] = [
                'type' => 'app',
                'addon' => 'app',
                'title' => $v[ 'title' ],
                'theme' => $v[ 'theme' ],
                'default_theme' => $v[ 'theme' ],
                'theme_type' => 'default',
                'is_selected' => $k == 0 ? 1 : 0,
                'create_time' => time(),
            ];
        }
        foreach ($apps as $value) {
            $addon_theme = [];
            if ($value[ 'key' ] == 'cms') {
                $addon_theme = $this->getCmsTheme();
            } elseif ($value[ 'key' ] == 'o2o') {
                $addon_theme = $this->getO2oTheme();
            } elseif ($value[ 'key' ] == 'shop') {
                $addon_theme = $this->getShopTheme();
            } elseif ($value[ 'key' ] == 'shop_fenxiao') {
                $addon_theme = $this->getFenxiaoTheme();
            } elseif ($value[ 'key' ] == 'shop_giftcard') {
                $addon_theme = $this->getGiftcardTheme();
            } elseif ($value[ 'key' ] == 'sow_community') {
                $addon_theme = $this->getSowCommunityTheme();
            } elseif ($value[ 'key' ] == 'tourism') {
                $addon_theme = $this->getTourismTheme();
            } elseif ($value[ 'key' ] == 'vipcard') {
                $addon_theme = $this->getVipcardTheme();
            }

            if (!empty($addon_theme)) {
                foreach ($addon_theme[ 'theme_color' ] as $k => $v) {
                    $data[] = [
                        'type' => 'app',
                        'addon' => $value[ 'key' ],
                        'title' => $v[ 'title' ],
                        'theme' => $v[ 'theme' ],
                        'default_theme' => $v[ 'theme' ],
                        'theme_type' => 'default',
                        'is_selected' => $k == 0 ? 1 : 0,
                        'create_time' => time(),
                    ];
                }
                $addon_data = ( new addon() )->field('key')->where([ [ 'support_app', '=', $value[ 'key' ] ] ])->select()->toArray();
                if (!empty($addon_data)) {
                    foreach ($addon_data as $v) {
                        foreach ($addon_theme[ 'theme_color' ] as $theme_k => $theme_v) {
                            $data[] = [
                                'type' => 'addon',
                                'addon' => $v[ 'key' ],
                                'title' => $theme_v[ 'title' ],
                                'theme' => $theme_v[ 'theme' ],
                                'default_theme' => $theme_v[ 'theme' ],
                                'theme_type' => 'default',
                                'is_selected' => $theme_k == 0 ? 1 : 0,
                                'create_time' => time(),
                            ];
                        }
                    }
                }
            }
        }
        try {
            if (!empty($data)) {
                $diy_theme_model = new DiyTheme();
                foreach ($data as $k => &$v) {
                    $theme_count = $diy_theme_model->where([
                        [ 'title', "=", $v[ 'title' ] ],
                        [ 'addon', "=", $v[ 'addon' ] ]
                    ])->count();
                    // 如果已有该主题风格颜色则不再添加
                    if ($theme_count > 0) {
                        unset($data[ $k ]);
                    }
                }
                $diy_theme_model->insertAll($data);
            }
        } catch (\Exception $e) {
        }
        return true;
    }

}

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

namespace app\service\core\diy;

use app\model\addon\Addon;
use app\model\diy\Diy;
use app\model\diy\DiyTheme;
use app\service\core\addon\CoreAddonService;
use core\base\BaseCoreService;

/**
 * 自定义页面服务层
 * Class CoreDiyService
 * @package app\service\core\diy
 */
class CoreDiyService extends BaseCoreService
{
    /**
     * 删除自定义页面
     * @param $condition
     * @return mixed
     */
    public function del($condition)
    {
        return ( new Diy() )->where($condition)->delete();
    }

    /**
     * 初始化默认自定义主题配色
     * @return true
     */
    public function initDefaultDiyTheme()
    {
        $addon_list = ( new CoreAddonService() )->getInstallAddonList();
        $apps = [];
        foreach ($addon_list as $k => $v) {
            if ($v[ 'type' ] == 'app') {
                $apps[] = $v;
            }
        }
        $system_theme = array_values(array_filter(event('ThemeColor', [ 'key' => 'app' ])))[ 0 ] ?? [];
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
            $addon_theme = array_values(array_filter(event('ThemeColor', [ 'key' => $value[ 'key' ] ])))[ 0 ] ?? [];
            if (empty($addon_theme)) continue;

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
        if (!empty($data)) {
            $diy_theme_model->insertAll($data);
        }
        return true;
    }
}

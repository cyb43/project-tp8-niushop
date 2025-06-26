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

namespace app\service\api\diy;

use app\dict\diy\PagesDict;
use app\dict\diy\TemplateDict;
use app\model\diy\Diy;
use app\model\diy\DiyTheme;
use app\service\core\addon\CoreAddonService;
use core\base\BaseApiService;

/**
 * 自定义页面服务层
 * Class DiyService
 * @package app\service\api\diy
 */
class DiyService extends BaseApiService
{

    public function __construct()
    {
        parent::__construct();
        $this->model = new Diy();
    }

    /**
     * 获取自定义页面信息
     * @param array $params
     * @return array
     */
    public function getInfo(array $params = [])
    {
        $start_up_page = [];
        $page_template = [];

        if (!empty($params[ 'name' ])) {

            // 查询启动页
            $diy_config_service = new DiyConfigService();
            $start_up_page = $diy_config_service->getStartUpPageConfig($params[ 'name' ]);

            $page_template = TemplateDict::getTemplate([ 'key' => [ $params[ 'name' ] ] ]);
            if (!empty($page_template)) {
                $page_template = $page_template [ $params[ 'name' ] ];
            }
        }

        if (empty($params[ 'id' ]) && !empty($start_up_page) && !empty($page_template) && !empty($start_up_page[ 'page' ]) && $start_up_page[ 'page' ] != $page_template[ 'page' ]) {
            $info = $start_up_page;
            return $info;
        } else {
            $condition = [];
            if (!empty($params[ 'id' ])) {
                $condition[] = [ 'id', '=', $params[ 'id' ] ];
            } elseif (!empty($params[ 'name' ])) {
                $condition[] = [ 'name', '=', $params[ 'name' ] ];
                $condition[] = [ 'is_default', '=', 1 ];
            }

            $field = 'id,title,name,type,template, mode,value,is_default,share,visit_count';

            $info = $this->model->field($field)->where($condition)->findOrEmpty()->toArray();

            if (empty($info)) {
                // 查询默认页面数据
                if (!empty($params[ 'name' ])) {
                    $page_data = $this->getFirstPageData($params[ 'name' ]);
                    if (!empty($page_data)) {
                        $info = [
                            'title' => $page_data[ 'title' ],
                            'name' => $page_data[ 'type' ],
                            'type' => $page_data[ 'type' ],
                            'template' => $page_data[ 'template' ],
                            'mode' => $page_data[ 'mode' ],
                            'value' => json_encode($page_data[ 'data' ], JSON_UNESCAPED_UNICODE),
                            'is_default' => 1,
                            'share' => '',
                            'visit_count' => 0
                        ];
                    }
                }
            }
            return $info;
        }
    }

    /**
     * 获取默认页面数据
     * @param $type
     * @return array|mixed
     */
    public function getFirstPageData($type)
    {
        $pages = PagesDict::getPages([ 'type' => $type ]);
        if (!empty($pages)) {
            $template = array_key_first($pages);
            $page = array_shift($pages);
            $page[ 'template' ] = $template;
            $page[ 'type' ] = $type;
            return $page;
        }
        return [];
    }

    // todo 使用缩略图
    public function handleThumbImgs($data)
    {
        $data = json_decode($data, true);

        // todo $data['global']

        foreach ($data[ 'value' ] as $k => $v) {

            // 图片广告
            if ($v[ 'componentName' ] == 'ImageAds') {
                foreach ($v[ 'list' ] as $ck => $cv) {
                    if (!empty($cv[ 'imageUrlThumbMid' ])) {
                        $data[ 'value' ][ $k ][ 'list' ][ $ck ][ 'imageUrl' ] = $cv[ 'imageUrlThumbMid' ];
                    }
                }
            }

            // 图文导航
            if ($v[ 'componentName' ] == 'GraphicNav') {

                foreach ($v[ 'list' ] as $ck => $cv) {
                    if (!empty($cv[ 'imageUrlThumbMid' ])) {
                        $data[ 'value' ][ $k ][ 'list' ][ $ck ][ 'imageUrl' ] = $cv[ 'imageUrlThumbMid' ];
                    }
                }
            }

        }

        $data = json_encode($data);
        return $data;
    }

    /**
     * 获取自定义主题配色
     * @return array
     */
    public function getDiyTheme()
    {
        $addon_list = ( new CoreAddonService() )->getInstallAddonList();
        $apps = [];
        foreach ($addon_list as $k => $v) {
            if ($v[ 'type' ] == 'app') {
                $apps[] = $v;
            }
        }
        $theme_data = ( new DiyTheme() )->where([ [ 'is_selected', '=', 1 ] ])->column('id,title,theme,new_theme', 'addon');
        $system_theme = array_values(array_filter(event('ThemeColor', [ 'key' => 'app' ])))[ 0 ] ?? [];
        $app_theme[ 'app' ] = [
            'title' => $theme_data[ 'app' ][ 'title' ] ?? ( !empty($system_theme) ? $system_theme[ 'theme_color' ][ 0 ][ 'title' ] : '' ),
            'theme' => $theme_data[ 'app' ][ 'theme' ] ?? ( !empty($system_theme) ? $system_theme[ 'theme_color' ][ 0 ][ 'theme' ] : '' ),
            'new_theme' => $theme_data[ 'app' ][ 'new_theme' ] ?? '',
        ];
        $data = [];
        foreach ($addon_list as $key => $value) {
            if (isset($value[ 'support_app' ]) && empty($value[ 'support_app' ]) && $value[ 'type' ] == 'addon') {
                continue;
            }
            $addon_theme = array_values(array_filter(event('ThemeColor', [ 'key' => $value[ 'key' ] ])))[ 0 ] ?? [];
            if (!empty($addon_theme) && !empty($addon_theme[ 'theme_color' ])) {
                $data[ $value[ 'key' ] ][ 'title' ] = $theme_data[ $value[ 'key' ] ][ 'title' ] ?? $addon_theme[ 'theme_color' ][ 0 ][ 'title' ];
                $data[ $value[ 'key' ] ][ 'theme' ] = $theme_data[ $value[ 'key' ] ][ 'theme' ] ?? $addon_theme[ 'theme_color' ][ 0 ][ 'theme' ];
                $data[ $value[ 'key' ] ][ 'new_theme' ] = $theme_data[ $value[ 'key' ] ][ 'new_theme' ] ?? '';
            }
        }
        if (count($apps) > 1) {// 应用数量大于1时，展示系统主题色设置，只有一个应用时，不展示系统主题色设置
            $data = array_merge($app_theme, $data);
        }
        return $data;
    }

}

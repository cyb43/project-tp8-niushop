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

namespace app\service\admin\addon;


use app\dict\addon\AddonDict;
use app\model\addon\Addon;
use app\service\core\addon\CoreAddonCloudService;
use app\service\core\addon\CoreAddonDownloadService;
use app\service\core\addon\CoreAddonInstallService;
use app\service\core\addon\CoreAddonService;
use core\base\BaseAdminService;
use core\exception\CommonException;
use think\db\exception\DbException;


/**
 * 消息管理服务层
 */
class AddonService extends BaseAdminService
{
    public static $cache_tag_name = 'addon_cache';

    public function __construct()
    {
        parent::__construct();
        $this->model = new Addon();

    }

    public function getList()
    {
        return ( new CoreAddonService() )->getLocalAddonList();
    }

    /**
     * 获取当前站点消息
     * @return array
     */
    public function getLocalAddonList()
    {
        return ( new CoreAddonService() )->getLocalAddonList();
    }

    /**
     * 安装插件
     * @param string $addon
     */
    public function install(string $addon)
    {
        return ( new CoreAddonInstallService($addon) )->install();
    }

    /**
     * 云安装插件
     * @param string $addon
     */
    public function cloudInstall(string $addon)
    {
        return ( new CoreAddonInstallService($addon) )->install('cloud');
    }

    /**
     * 云安装日志
     * @param string $addon
     * @return null
     */
    public function cloudInstallLog(string $addon)
    {
        return ( new CoreAddonCloudService() )->getBuildLog($addon);
    }

    /**
     * 获取安装任务
     * @return mixed
     */
    public function getInstallTask()
    {
        return ( new CoreAddonInstallService('') )->getInstallTask();
    }

    /**
     * 安装插件检测安装环境
     * @param string $addon
     */
    public function installCheck(string $addon)
    {
        return ( new CoreAddonInstallService($addon) )->installCheck();
    }

    /**
     * 取消安装任务
     * @param string $addon
     */
    public function cancleInstall(string $addon)
    {
        return ( new CoreAddonInstallService($addon) )->cancleInstall();
    }

    /**
     * @param string $addon
     * @return void
     */
    public function uninstallCheck(string $addon)
    {
        return ( new CoreAddonInstallService($addon) )->uninstallCheck();
    }

    /**
     * 卸载插件
     * @param string $addon
     * @return true
     */
    public function uninstall(string $addon)
    {
        return CoreAddonInstallService::instance($addon)->uninstall();
    }

    /**
     * 获取插件列表
     * @param array $where
     * @return array
     */
    public function getPage(array $where = [])
    {
        return ( new CoreAddonService() )->getPage($where);
    }

    /**
     * 获取插件信息
     * @param int $id
     * @return array
     */
    public function getInfo(int $id)
    {
        return ( new CoreAddonService() )->getInfo($id);
    }

    /**
     * 设置插件状态
     * @param int $id
     * @param int $status
     */
    public function setStatus(int $id, int $status)
    {
        return ( new CoreAddonService() )->setStatus($id, $status);
    }

    /**
     * 下载应用
     * @param string $app_key
     * @return true
     */
    public function download(string $app_key, string $version)
    {
        if (empty($version)) throw new CommonException('ADDON_DOWNLOAD_VERSION_EMPTY');
        return ( new CoreAddonDownloadService() )->download($app_key, $version);
    }


    /**
     * 查询已安装应用
     * @return array
     */
    public function getInstallList()
    {
        return ( new CoreAddonService() )->getInstallAddonList();
    }

    /**
     * 应用key缓存
     * @param $keys
     * @return mixed|string
     * @throws DbException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAddonListByKeys($keys)
    {
        sort($keys);
        $cache_name = 'addon_list' . implode('_', $keys);
        return cache_remember(
            $cache_name,
            function () use ($keys) {
                $where = [
                    [ 'key', 'in', $keys ],
                    [ 'status', '=', AddonDict::ON ]
                ];
                return $this->model->where($where)->field('title, icon, key, desc, status, cover')->select()->toArray();

            },
            self::$cache_tag_name
        );
    }

    /**
     * 获取插件信息
     * @param int $id
     * @return array
     */
    public function getInfoByKey(string $key)
    {
        return $this->model->where([ [ 'key', '=', $key ] ])->field('title, icon, key, desc, status, cover')->findOrEmpty()->toArray();
    }

    /**
     * 查询应用列表，todo 完善
     * @return array
     */
    public function getShowAppTools()
    {
        $list = [
            'addon' => [
                'title' => '运营工具',
                'list' => []
            ],
            'tool' => [
                'title' => '系统工具',
                'list' => []
            ],
//            'promotion' => [
//                'title' => '营销活动',
//                'list' => []
//            ]
        ];

        $apps = event('ShowApp');

        $keys = [];
        foreach ($apps as $v) {
            foreach ($v as $ck => $cv) {
                if (!empty($cv)) {
                    foreach ($cv as $addon_k => $addon_v) {
                        if (in_array($addon_v[ 'key' ], $keys)) {
                            continue;
                        }
                        $list[ $ck ][ 'list' ][] = $addon_v;
                        $keys[] = $addon_v[ 'key' ];
                    }
                }

            }

        }

        $addons = $this->model->where([ [ 'status', '=', AddonDict::ON ] ])->append([ 'status_name' ])->column('title, icon, key, desc, status, type, support_app', 'key');
        if (!empty($addons)) {
            foreach ($addons as $k => $v) {
                if (!in_array($v[ 'key' ], $keys) && $v[ 'type' ] == AddonDict::ADDON && $v[ 'status' ] == AddonDict::ON) {
                    $list[ 'addon' ][ 'list' ][] = [
                        'title' => $v[ 'title' ],
                        'desc' => $v[ 'desc' ],
                        'icon' => $v[ 'icon' ],
                        'key' => $v[ 'key' ]
                    ];
                }
            }
        }

        return $list;
    }

    /**
     * 查询营销列表
     * @return array
     */
    public function getShowMarketingTools()
    {
        $list = [
            'marketing' => [
                'title' => '营销活动',
                'list' => []
            ]
        ];

        $apps = event('ShowMarketing');

        $keys = [];
        foreach ($apps as $v) {
            foreach ($v as $ck => $cv) {
                if (!empty($cv)) {
                    foreach ($cv as $addon_k => $addon_v) {
                        if (in_array($addon_v[ 'key' ], $keys)) {
                            continue;
                        }
                        $list[ $ck ][ 'list' ][] = $addon_v;
                        $keys[] = $addon_v[ 'key' ];
                    }
                }

            }
        }

        return $list;
    }
}

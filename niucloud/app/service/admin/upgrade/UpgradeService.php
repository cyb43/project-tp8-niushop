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

namespace app\service\admin\upgrade;

use app\dict\addon\AddonDict;
use app\dict\sys\BackupDict;
use app\dict\sys\UpgradeDict;
use app\model\addon\Addon;
use app\model\sys\SysBackupRecords;
use app\service\admin\install\InstallSystemService;
use app\service\admin\sys\ConfigService;
use app\service\core\addon\CoreAddonCloudService;
use app\service\core\addon\CoreAddonInstallService;
use app\service\core\addon\CoreAddonService;
use app\service\core\addon\CoreDependService;
use app\service\core\addon\WapTrait;
use app\service\core\channel\CoreH5Service;
use app\service\core\menu\CoreMenuService;
use app\service\core\niucloud\CoreCloudBuildService;
use app\service\core\niucloud\CoreModuleService;
use app\service\core\schedule\CoreScheduleInstallService;
use core\base\BaseAdminService;
use core\exception\CloudBuildException;
use core\exception\CommonException;
use core\util\DbBackup;
use core\util\niucloud\BaseNiucloudClient;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Log;

/**
 * 框架及插件升级
 * @package app\service\core\upgrade
 */
class UpgradeService extends BaseAdminService
{
    use WapTrait;
    use ExecuteSqlTrait;

    protected $upgrade_dir;

    protected $root_path;

    protected $cache_key = 'upgrade';

    protected $upgrade_task = null;

    protected $addon = '';

    private $steps = [
        'requestUpgrade' => [ 'step' => 'requestUpgrade', 'title' => '请求升级' ],
        'downloadFile' => [ 'step' => 'downloadFile', 'title' => '' ],
        'backupCode' => [ 'step' => 'backupCode', 'title' => '备份源码' ],
        'backupSql' => [ 'step' => 'backupSql', 'title' => '' ],
        'coverCode' => [ 'step' => 'coverCode', 'title' => '合并更新文件' ],
        'handleUniapp' => [ 'step' => 'handleUniapp', 'title' => '处理uniapp' ],
        'refreshMenu' => [ 'step' => 'refreshMenu', 'title' => '刷新菜单' ],
        'installSchedule' => [ 'step' => 'installSchedule', 'title' => '安装计划任务' ],
        'cloudBuild' => [ 'step' => 'cloudBuild', 'title' => '开始云编译' ],
        'gteCloudBuildLog' => [ 'step' => 'gteCloudBuildLog', 'title' => '' ],
        'upgradeComplete' => [ 'step' => 'upgradeComplete', 'title' => '升级完成' ]
    ];

    public function __construct()
    {
        parent::__construct();

        $this->root_path = dirname(root_path()) . DIRECTORY_SEPARATOR;
        $this->upgrade_dir = $this->root_path . 'upgrade' . DIRECTORY_SEPARATOR;
        $this->upgrade_task = Cache::get($this->cache_key);

        if (!empty($this->upgrade_task) && !isset($this->upgrade_task[ 'upgrade_apps' ])) {
            $this->upgrade_task[ 'upgrade_apps' ] = [ AddonDict::FRAMEWORK_KEY ];
        }
    }

    /**
     * 升级前环境检测
     * @param string $addon
     * @return array|array[]
     */
    public function upgradePreCheck(string $addon = '')
    {
        $niucloud_dir = $this->root_path . 'niucloud' . DIRECTORY_SEPARATOR;
        $upgrade_dir = $this->root_path . 'upgrade' . DIRECTORY_SEPARATOR;
        $admin_dir = $this->root_path . 'admin' . DIRECTORY_SEPARATOR;
        $web_dir = $this->root_path . 'web' . DIRECTORY_SEPARATOR;
        $wap_dir = $this->root_path . 'uni-app' . DIRECTORY_SEPARATOR;

        if (!is_dir($admin_dir)) throw new CommonException('ADMIN_DIR_NOT_EXIST');
        if (!is_dir($web_dir)) throw new CommonException('WEB_DIR_NOT_EXIST');
        if (!is_dir($wap_dir)) throw new CommonException('UNIAPP_DIR_NOT_EXIST');

        $data = [
            // 目录检测
            'dir' => [
                // 要求可读权限
                'is_readable' => [],
                // 要求可写权限
                'is_write' => []
            ]
        ];

        $data[ 'dir' ][ 'is_readable' ][] = [ 'dir' => str_replace(project_path(), '', $niucloud_dir), 'status' => is_readable($niucloud_dir) ];
        $data[ 'dir' ][ 'is_readable' ][] = [ 'dir' => str_replace(project_path(), '', $upgrade_dir), 'status' => is_readable($upgrade_dir) ];

        $data[ 'dir' ][ 'is_readable' ][] = [ 'dir' => str_replace(project_path(), '', $admin_dir), 'status' => is_readable($admin_dir) ];
        $data[ 'dir' ][ 'is_readable' ][] = [ 'dir' => str_replace(project_path(), '', $web_dir), 'status' => is_readable($web_dir) ];
        $data[ 'dir' ][ 'is_readable' ][] = [ 'dir' => str_replace(project_path(), '', $wap_dir), 'status' => is_readable($wap_dir) ];

        $data[ 'dir' ][ 'is_write' ][] = [ 'dir' => str_replace(project_path(), '', $niucloud_dir), 'status' => is_write($niucloud_dir) ];
        $data[ 'dir' ][ 'is_write' ][] = [ 'dir' => str_replace(project_path(), '', $upgrade_dir), 'status' => is_write($upgrade_dir) ];
        $data[ 'dir' ][ 'is_write' ][] = [ 'dir' => str_replace(project_path(), '', $admin_dir), 'status' => is_write($admin_dir) ];
        $data[ 'dir' ][ 'is_write' ][] = [ 'dir' => str_replace(project_path(), '', $web_dir), 'status' => is_write($web_dir) ];
        $data[ 'dir' ][ 'is_write' ][] = [ 'dir' => str_replace(project_path(), '', $wap_dir), 'status' => is_write($wap_dir) ];

        // 检测全部目录及文件是否可读可写，忽略指定目录

        // 忽略指定目录，admin
        $exclude_admin_dir = [ 'dist', 'node_modules', '.git' ];
        $check_res = checkDirPermissions(project_path() . 'admin', [], $exclude_admin_dir);

        // 忽略指定目录，uni-app
        $exclude_uniapp_dir = [ 'dist', 'node_modules', '.git' ];
        $check_res = array_merge2($check_res, checkDirPermissions(project_path() . 'uni-app', [], $exclude_uniapp_dir));

        // 忽略指定目录，web
        $exclude_web_dir = [ '.nuxt', '.output', 'dist', 'node_modules', '.git' ];
        $check_res = array_merge2($check_res, checkDirPermissions(project_path() . 'web', [], $exclude_web_dir));

        // 忽略指定目录，niucloud
        $exclude_niucloud_dir = [
            'public' . DIRECTORY_SEPARATOR . 'admin',
            'public' . DIRECTORY_SEPARATOR . 'wap',
            'public' . DIRECTORY_SEPARATOR . 'web',
            'public' . DIRECTORY_SEPARATOR . 'upload',
            'public' . DIRECTORY_SEPARATOR . 'file',
            'runtime',
            'vendor',
            '.user.ini',
            '.git'
        ];
        $check_res = array_merge2($check_res, checkDirPermissions(project_path() . 'niucloud', [], $exclude_niucloud_dir));

        if (!empty($check_res[ 'unreadable' ])) {
            foreach ($check_res[ 'unreadable' ] as $item) {
                $data[ 'dir' ][ 'is_readable' ][] = [ 'dir' => str_replace(project_path(), '', $item), 'status' => false ];
            }
        }
        if (!empty($check_res[ 'not_writable' ])) {
            foreach ($check_res[ 'not_writable' ] as $item) {
                $data[ 'dir' ][ 'is_write' ][] = [ 'dir' => str_replace(project_path(), '', $item), 'status' => false ];
            }
        }

        $check_res = array_merge(
            array_column($data[ 'dir' ][ 'is_readable' ], 'status'),
            array_column($data[ 'dir' ][ 'is_write' ], 'status')
        );

        // 是否通过校验
        $data[ 'is_pass' ] = !in_array(false, $check_res);

        return $data;
    }

    /**
     * 升级
     * @param $addon
     * @return array
     */
    public function upgrade(string $addon = '', $data = [])
    {
        if ($this->upgrade_task) throw new CommonException('UPGRADE_TASK_EXIST');

        $upgrade_content = $this->getUpgradeContent($addon);
        if (empty($upgrade_content['content'])) throw new CommonException("NOT_EXIST_UPGRADE_CONTENT");

        // 过滤掉没有更新版本的插件
        $upgrade_content['content'] = array_values(array_filter(array_map(function ($item){
            if (!empty($item['version_list'])) return $item;
        }, $upgrade_content['content'])));
        if (empty($upgrade_content['content'])) throw new CommonException("NOT_EXIST_UPGRADE_CONTENT");

        $upgrade_content['upgrade_apps'] = [];
        foreach ($upgrade_content['content'] as $item) {
            $upgrade_content['upgrade_apps'][] = $item['app']['app_key'];
        }

        $upgrade_title = '框架';

        $upgrade = [
            'product_key' => BaseNiucloudClient::PRODUCT,
            'framework_version' => config('version.version')
        ];

        $upgrade[ 'app_key' ] = $upgrade_content['content'][0]['app']['app_key'];
        $upgrade[ 'version' ] = $upgrade_content['content'][0]['version'];

//        if (!$addon) {
//            $upgrade[ 'app_key' ] = AddonDict::FRAMEWORK_KEY;
//            $upgrade[ 'version' ] = config('version.version');
//        } else {
//            $upgrade[ 'app_key' ] = $addon;
//            $upgrade[ 'version' ] = ( new Addon() )->where([ [ 'key', '=', $addon ] ])->value('version');
//            $upgrade_title = ( new Addon() )->where([ [ 'key', '=', $addon ] ])->value('title');
//
//            // 判断框架版本是否低于插件支持版本
//            $last_version = $upgrade_content[ 'version_list' ][ count($upgrade_content[ 'version_list' ]) - 1 ];
//            if (str_replace('.', '', config('version.version')) < str_replace('.', '', $last_version[ 'niucloud_version' ][ 'version_no' ])) {
//                throw new CommonException('BEFORE_UPGRADING_NEED_UPGRADE_FRAMEWORK');
//            }
//        }

        $response = ( new CoreAddonCloudService() )->upgradeAddon($upgrade);
        if (isset($response[ 'code' ]) && $response[ 'code' ] == 0) throw new CommonException($response[ 'msg' ]);

        try {
            $key = uniqid();
            $upgrade_dir = $this->upgrade_dir . $key . DIRECTORY_SEPARATOR;

            if (!is_dir($upgrade_dir)) {
                dir_mkdir($upgrade_dir);
            }

            // 是否需要备份
            $is_need_backup = $data['is_need_backup'] ?? true;
            if (!$is_need_backup) {
                unset($this->steps['backupCode']);
                unset($this->steps['backupSql']);
            }

            // 是否需要云编译
            $is_need_cloudbuild = $data['is_need_cloudbuild'] ?? true;
            if (!$is_need_cloudbuild) {
                unset($this->steps['cloudBuild']);
                unset($this->steps['gteCloudBuildLog']);
            }

            $upgrade_task = [
                'key' => $key,
                'upgrade' => $upgrade,
                'steps' => $this->steps,
                'step' => 'requestUpgrade',
                'executed' => [ 'requestUpgrade' ],
                'log' => [ $this->steps[ 'requestUpgrade' ][ 'title' ] ],
                'params' => [ 'app_key' => $upgrade[ 'app_key' ], 'token' => $response[ 'token' ] ],
                'upgrade_content' => $upgrade_content,
                'upgrade_apps' => $upgrade_content['upgrade_apps'],
                'is_need_backup' => $is_need_backup,
            ];

            foreach ($upgrade_content[ 'content' ] as $k => $v) {
                unset($upgrade_content[ 'content' ][ $k ][ 'version_list' ]);
            }

            // 记录升级日志
            ( new UpgradeRecordsService() )->add([
                'upgrade_key' => $upgrade_task[ 'key' ],
                'app_key' => $upgrade[ 'app_key' ],
                'name' => $upgrade_title,
                'content' => json_encode($upgrade_content),
                'prev_version' => "",
                'current_version' => "",
                'status' => UpgradeDict::STATUS_READY,
                'is_need_backup' => $is_need_backup
            ]);

            Cache::set($this->cache_key, $upgrade_task);
            return $upgrade_task;
        } catch (\Exception $e) {
            throw new CommonException($e->getMessage());
        }
    }

    /**
     * 执行升级
     * @return true
     */
    public function execute()
    {
        if (!$this->upgrade_task) return true;

        $steps = isset($this->upgrade_task[ 'steps' ]) ? array_keys($this->upgrade_task[ 'steps' ]) : array_keys($this->steps);
        if (isset($this->upgrade_task[ 'steps' ])) $this->steps = $this->upgrade_task[ 'steps' ];
        $index = array_search($this->upgrade_task[ 'step' ], $steps);
        $step = $steps[ $index + 1 ] ?? '';
        $params = $this->upgrade_task[ 'params' ] ?? [];

        if ($step) {
            try {
                $res = $this->$step(...$params);

                if (is_array($res)) {
                    $this->upgrade_task[ 'params' ] = $res;
                } else {
                    $this->upgrade_task[ 'step' ] = $step;
                    $this->upgrade_task[ 'params' ] = [];
                    $this->upgrade_task[ 'executed' ][] = $step;
                    if (!empty($this->steps[ $step ][ 'title' ])) {
                        $this->upgrade_task[ 'log' ][] = $this->steps[ $step ][ 'title' ];
                    }
                }
                if (!in_array($step, [ 'upgradeComplete', 'restoreComplete' ])) {
                    Cache::set($this->cache_key, $this->upgrade_task);
                } else {
                    $this->clearUpgradeTask(2);
                }
            } catch (CloudBuildException $e) {
                if (strpos($e->getMessage(), '队列') !== false) {
                    throw new CloudBuildException($e->getMessage());
                }
                $this->upgrade_task[ 'step' ] = $step;
                $this->upgrade_task[ 'error' ][] = '升级失败，失败原因：' . $e->getMessage() . $e->getFile() . $e->getLine();

                $fail_reason = [
                    'Message' => '失败原因：' . $e->getMessage(),
                    'File' => '文件：' . $e->getFile(),
                    'Line' => '代码行号：' . $e->getLine(),
                    'Trace' => $e->getTrace()
                ];
                $this->upgradeErrorHandle($fail_reason);
            } catch (\Exception $e) {
                $this->upgrade_task[ 'step' ] = $step;
                $this->upgrade_task[ 'error' ][] = '升级失败，失败原因：' . $e->getMessage() . $e->getFile() . $e->getLine();
                $fail_reason = [
                    'Message' => '失败原因：' . $e->getMessage(),
                    'File' => '文件：' . $e->getFile(),
                    'Line' => '代码行号：' . $e->getLine(),
                    'Trace' => $e->getTrace()
                ];
                $this->upgradeErrorHandle($fail_reason);
            }
            return true;
        } else {
            return true;
        }
    }

    /**
     * 下载升级文件
     * @param string $token
     * @param string $dir
     * @param int $index
     * @param $step
     * @return true|null
     */
    public function downloadFile(string $app_key, string $token, string $dir = '', int $index = -1, $step = 0, $length = 0)
    {
        if (!$dir) {
            $dir = $this->upgrade_dir . $this->upgrade_task[ 'key' ] . DIRECTORY_SEPARATOR . 'download' . DIRECTORY_SEPARATOR . $app_key . DIRECTORY_SEPARATOR;
            dir_mkdir($dir);
        }
        $res = ( new CoreAddonCloudService() )->downloadUpgradeFile($app_key, $token, $dir, $index, $step, $length);

        if ($res === true) {
            $index = array_search($app_key, $this->upgrade_task['upgrade_apps']);
            if ($app_key == AddonDict::FRAMEWORK_KEY) {
                $this->upgrade_task['log'][] = "下载更新文件";
            } else {
                $this->upgrade_task['log'][] = "下载". $this->upgrade_task['upgrade_content']['content'][$index]['app']['app_name'] ."更新文件";
            }
            $index++;
            if (isset($this->upgrade_task['upgrade_apps'][$index])) {
                $upgrade = [
                    'product_key' => BaseNiucloudClient::PRODUCT,
                    'framework_version' => config('version.version'),
                    'app_key' => $this->upgrade_task['upgrade_content']['content'][$index]['app']['app_key'],
                    'version' => $this->upgrade_task['upgrade_content']['content'][$index]['version']
                ];
                $response = ( new CoreAddonCloudService() )->upgradeAddon($upgrade);
                if (isset($response[ 'code' ]) && $response[ 'code' ] == 0) throw new CommonException($response[ 'msg' ]);

                return ['app_key' => $upgrade[ 'app_key' ], 'token' => $response[ 'token' ]];
            }
        }
        return $res;
    }

    /**
     * 备份源码
     * @return true
     */
    public function backupCode()
    {
        ( new BackupService() )->backupCode();
        return true;
    }

    /**
     * 备份数据库
     * @return true
     */
    public function backupSql($index = 0)
    {
        $backup_dir = $this->upgrade_dir . $this->upgrade_task[ 'key' ] . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR;
        // 创建目录
        dir_mkdir($backup_dir);

        $db = new DbBackup($backup_dir, 1024 * 1024 * 2, key: $this->upgrade_task[ 'key' ]);

        $prefix = config('database.connections.' . config('database.default'))[ 'prefix' ];

        // 不需要备份的表
        $not_need_backup = [
            "{$prefix}sys_schedule_log",
            "{$prefix}sys_user_log",
            "{$prefix}jobs",
            "{$prefix}jobs_failed",
            "{$prefix}sys_upgrade_records",
            "{$prefix}sys_backup_records"
        ];

        $result = $db->setExcludeTables($not_need_backup)->backupDatabaseSegment();
        if ($db->getBackupProgress() == 100) {
            $this->upgrade_task[ 'log' ][] = "数据库备份完成";
        } else {
            $this->upgrade_task[ 'log' ][] = $db->getBackupProgress() == 0 ? '数据库开始备份' : '数据库备份已备份'. $db->getBackupProgress() . '%';
        }
        if ($result === true) return true;
        return ['index' => $result];
    }

    /**
     * 覆盖更新升级的代码
     * @return array|true
     */
    public function coverCode($index = 0, $addon = "")
    {
        $this->upgrade_task[ 'is_cover' ] = 1;
        if (empty($addon)) $addon = $this->upgrade_task['upgrade_apps'][0];

        $app_index = array_search($addon, $this->upgrade_task['upgrade_apps']);

        $version_list = array_reverse($this->upgrade_task[ 'upgrade_content' ]['content'][$app_index][ 'version_list' ]);
        $code_dir = $this->upgrade_dir . $this->upgrade_task[ 'key' ] . DIRECTORY_SEPARATOR . 'download' . DIRECTORY_SEPARATOR . $addon . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR;

        $version_item = $version_list[ $index ];
        $version_no = $version_item[ 'version_no' ];

        $to_dir = $addon == AddonDict::FRAMEWORK_KEY ? rtrim($this->root_path, DIRECTORY_SEPARATOR) : $this->root_path . 'niucloud' . DIRECTORY_SEPARATOR . 'addon' . DIRECTORY_SEPARATOR . $addon;

        // 获取文件变更记录
        if (file_exists($code_dir . $version_no . '.txt')) {
            $change = array_filter(explode("\n", file_get_contents($code_dir . $version_no . '.txt')));
            foreach ($change as &$item) {
                list($operation, $md5, $file) = $item = explode(' ', $item);
                if ($operation == '-') {
                    @unlink($to_dir . $file);
                }
            }
            // 合并依赖
            $this->installDepend($code_dir . $version_no, array_column($change, 2));
        }

        // 覆盖文件
        if (is_dir($code_dir . $version_no)) {
            // 忽略环境变量文件
            $exclude_files = [ '.env.development', '.env.production', '.env', '.env.dev', '.env.product' ];
            dir_copy($code_dir . $version_no, $to_dir, exclude_files: $exclude_files);
            if ($addon != AddonDict::FRAMEWORK_KEY) {
                ( new CoreAddonInstallService($addon) )->installDir();
            }
        }

        $upgrade_file_dir = 'v' . str_replace('.', '', $version_no);
        if ($addon == AddonDict::FRAMEWORK_KEY) {
            $class_path = "\\app\\upgrade\\{$upgrade_file_dir}\\Upgrade";
            $sql_file = root_path() . 'app' . DIRECTORY_SEPARATOR . 'upgrade' . DIRECTORY_SEPARATOR . $upgrade_file_dir . DIRECTORY_SEPARATOR . 'upgrade.sql';
        } else {
            $class_path = "\\addon\\{$addon}\\app\\upgrade\\{$upgrade_file_dir}\\Upgrade";
            $sql_file = root_path() . 'addon' . DIRECTORY_SEPARATOR . $addon . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'upgrade' . DIRECTORY_SEPARATOR . $upgrade_file_dir . DIRECTORY_SEPARATOR . 'upgrade.sql';
        }

        // 执行升级sql
        if (file_exists($sql_file)) {
            $this->executeSql($sql_file);
        }

        // 执行升级方法
        if (class_exists($class_path)) {
            ( new $class_path() )->handle();
        }

        $index++;
        if ($index < count($version_list)) {
            return compact('index', 'addon');
        } else {
            $app_index++;
            if (isset($this->upgrade_task['upgrade_apps'][$app_index])) {
                return ['index' => 0, 'addon' => $this->upgrade_task['upgrade_apps'][$app_index]];
            }
            return true;
        }
    }

    /**
     * 合并依赖
     * @param string $version_no
     * @return void
     */
    public function installDepend(string $dir, array $change_files)
    {
        $addon = $this->upgrade_task[ 'upgrade' ][ 'app_key' ];
        $depend_service = new CoreDependService();

        if ($addon == AddonDict::FRAMEWORK_KEY) {
            $composer = '/niucloud/composer.json';
            $admin_package = '/admin/package.json';
            $web_package = '/web/package.json';
            $uniapp_package = '/uni-app/package.json';
        } else {
            $composer = "/niucloud/addon/{$addon}/package/composer.json";
            $admin_package = "/niucloud/addon/{$addon}/package/admin-package.json";
            $web_package = "/niucloud/addon/{$addon}/package/web-package.json";
            $uniapp_package = "/niucloud/addon/{$addon}/package/uni-app-package.json";
        }

        if (in_array($composer, $change_files)) {
            $original = $depend_service->getComposerContent();
            $new = $depend_service->jsonFileToArray($dir . $composer);
            foreach ($new as $name => $value) {
                $original[ $name ] = isset($original[ $name ]) && is_array($original[ $name ]) ? array_map('unserialize', array_unique(array_map('serialize', array_merge($original[ $name ], $new[ $name ])))) : $new[ $name ];
            }
            $depend_service->writeArrayToJsonFile($original, $dir . $composer);
        }
        if (in_array($admin_package, $change_files)) {
            $original = $depend_service->getNpmContent('admin');
            $new = $depend_service->jsonFileToArray($dir . $admin_package);

            foreach ($new as $name => $value) {
                $original[ $name ] = isset($original[ $name ]) && is_array($original[ $name ]) ? array_merge($original[ $name ], $new[ $name ]) : $new[ $name ];
            }
            $depend_service->writeArrayToJsonFile($original, $dir . $admin_package);
        }
        if (in_array($web_package, $change_files)) {
            $original = $depend_service->getNpmContent('web');
            $new = $depend_service->jsonFileToArray($dir . $web_package);

            foreach ($new as $name => $value) {
                $original[ $name ] = isset($original[ $name ]) && is_array($original[ $name ]) ? array_merge($original[ $name ], $new[ $name ]) : $new[ $name ];
            }
            $depend_service->writeArrayToJsonFile($original, $dir . $web_package);
        }
        if (in_array($uniapp_package, $change_files)) {
            $original = $depend_service->getNpmContent('uni-app');
            $new = $depend_service->jsonFileToArray($dir . $uniapp_package);

            foreach ($new as $name => $value) {
                $original[ $name ] = isset($original[ $name ]) && is_array($original[ $name ]) ? array_merge($original[ $name ], $new[ $name ]) : $new[ $name ];
            }
            $depend_service->writeArrayToJsonFile($original, $dir . $uniapp_package);
        }
    }

    /**
     * 处理手机端
     * @return true
     */
    public function handleUniapp()
    {
        $key = end($this->upgrade_task['upgrade_apps']);

        $code_dir = $this->upgrade_dir . $this->upgrade_task[ 'key' ] . DIRECTORY_SEPARATOR . 'download' . DIRECTORY_SEPARATOR . $key . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR;
        $exclude_files = [ '.env.development', '.env.production', 'manifest.json' ];
        dir_copy($code_dir . 'uni-app', $this->root_path . 'uni-app', exclude_files: $exclude_files);

        $addon_list = ( new CoreAddonService() )->getInstallAddonList();
        $depend_service = new CoreDependService();

        if (!empty($addon_list)) {

            foreach ($addon_list as $addon => $item) {
                $this->addon = $addon;

                // 编译 diy-group 自定义组件代码文件
                $this->compileDiyComponentsCode($this->root_path . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR, $addon);

                // 编译 pages.json 页面路由代码文件
                $this->installPageCode($this->root_path . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR);

                // 编译 加载插件标题语言包
                $this->compileLocale($this->root_path . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR, $addon);

                // 合并插件依赖
                $addon_uniapp_package = str_replace('/', DIRECTORY_SEPARATOR, project_path() . "niucloud/addon/{$addon}/package/uni-app-package.json");

                if (file_exists($addon_uniapp_package)) {
                    $original = $depend_service->getNpmContent('uni-app');
                    $new = $depend_service->jsonFileToArray($addon_uniapp_package);

                    foreach ($new as $name => $value) {
                        $original[ $name ] = isset($original[ $name ]) && is_array($original[ $name ]) ? array_merge($original[ $name ], $new[ $name ]) : $new[ $name ];
                    }

                    $uniapp_package = $this->root_path . 'uni-app' . DIRECTORY_SEPARATOR . 'package.json';
                    $depend_service->writeArrayToJsonFile($original, $uniapp_package);
                }
            }
        }

        $map = ( new ConfigService() )->getMap();
        ( new CoreH5Service() )->mapKeyChange($map[ 'key' ]);

        return true;
    }

    /**
     * 执行升级sql
     * @param string $sql_file
     * @return true
     */
    private function executeSql(string $sql_file)
    {
        $sql_content = file_get_contents($sql_file);

        if (!empty($sql_content)) {
            $prefix = config('database.connections.mysql.prefix');
            $sql_data = array_filter($this->getSqlQuery($sql_content));

            if (!empty($sql_data)) {
                foreach ($sql_data as $sql) {
                    $sql = $prefix ? $this->handleSqlPrefix($sql, $prefix) : $sql;
                    Db::query($sql);
                }
            }
        }
        return true;
    }

    /**
     * 刷新菜单
     * @return array|true
     */
    public function refreshMenu($addon = "")
    {
        if (empty($addon)) $addon = $this->upgrade_task['upgrade_apps'][0];
        $app_index = array_search($addon, $this->upgrade_task['upgrade_apps']);

        if ($addon == AddonDict::FRAMEWORK_KEY) {
            ( new InstallSystemService() )->installMenu();
        } else {
            ( new CoreMenuService() )->refreshAddonMenu($addon);
        }

        $app_index++;
        if (isset($this->upgrade_task['upgrade_apps'][$app_index])) {
            return ['addon' => $this->upgrade_task['upgrade_apps'][$app_index]];
        }
        return true;
    }

    /**
     * 安装计划任务
     * @return true
     */
    public function installSchedule($addon = "")
    {
        if (empty($addon)) $addon = $this->upgrade_task['upgrade_apps'][0];
        $app_index = array_search($addon, $this->upgrade_task['upgrade_apps']);

        if ($addon == AddonDict::FRAMEWORK_KEY) {
            ( new CoreScheduleInstallService() )->installSystemSchedule();
        } else {
            ( new CoreScheduleInstallService() )->installAddonSchedule($addon);
        }

        $app_index++;
        if (isset($this->upgrade_task['upgrade_apps'][$app_index])) {
            return ['addon' => $this->upgrade_task['upgrade_apps'][$app_index]];
        }
        return true;
    }

    /**
     * 云编译
     * @return void
     */
    public function cloudBuild()
    {
        ( new CoreCloudBuildService() )->cloudBuild();
    }

    /**
     * 获取云编译日志
     * @return array|true
     */
    public function gteCloudBuildLog()
    {
        $log = ( new CoreCloudBuildService() )->getBuildLog();
        if (empty($log)) return true;

        foreach ($log[ 'data' ][ 0 ] as $item) {
            if ($item[ 'code' ] == 0) {
                $this->upgrade_task[ 'step' ] = 'gteCloudBuildLog';
                $this->upgrade_task[ 'error' ][] = $item[ 'msg' ];
                Cache::set($this->cache_key, $this->upgrade_task);

                $fail_reason = [
                    'Message' => '失败原因 云编译错误：' . $item[ 'msg' ],
                    'File' => '',
                    'Line' => '',
                    'Trace' => ''
                ];

                ( new CoreCloudBuildService() )->clearTask();

                $this->upgradeErrorHandle($fail_reason);

                return true;
            }
            if (!in_array($item[ 'action' ], $this->upgrade_task[ 'log' ])) {
                $this->upgrade_task[ 'log' ][] = $item[ 'action' ];
                Cache::set($this->cache_key, $this->upgrade_task);
            }
        }

        sleep(2);
        return [];
    }

    /**
     * 更新完成
     * @return true
     */
    public function upgradeComplete()
    {
        foreach ($this->upgrade_task['upgrade_apps'] as $addon) {
            if ($addon != AddonDict::FRAMEWORK_KEY) {
                $core_addon_service = new CoreAddonService();
                $install_data = $core_addon_service->getAddonConfig($addon);
                $install_data[ 'icon' ] = 'addon/' . $addon . '/icon.png';
                $core_addon_service->set($install_data);
            }
        }
        // 执行完成，更新升级记录状态，备份记录状态
        ( new UpgradeRecordsService() )->complete($this->upgrade_task[ 'key' ]);
        $this->clearUpgradeTask(2);
        return true;
    }

    /**
     * 升级出错之后的处理
     * @return void
     */
    public function upgradeErrorHandle($fail_reason = [])
    {
        $steps = [];
        $steps[ $this->upgrade_task[ 'step' ] ] = [];

        if (isset($this->upgrade_task[ 'is_cover' ])) {
            $steps[ 'restoreCode' ] = [ 'step' => 'restoreCode', 'title' => '恢复源码备份' ];
            $steps[ 'restoreSql' ] = [ 'step' => 'restoreSql', 'title' => '恢复数据库备份' ];
        }
        $steps[ 'restoreComplete' ] = [ 'step' => 'restoreComplete', 'title' => '备份恢复完成' ];
        $this->upgrade_task[ 'steps' ] = $steps;
        $this->upgrade_task[ 'params' ] = [];
        Cache::set($this->cache_key, $this->upgrade_task);

        Log::write('升级出错之后的处理：'.json_encode($fail_reason));
    }

    /**
     * 恢复源码
     * @return true
     */
    public function restoreCode()
    {
        if (!isset($this->upgrade_task['is_need_backup']) || $this->upgrade_task['is_need_backup']) {
            $backup_dir = $this->upgrade_dir . $this->upgrade_task[ 'key' ] . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR;
        } else {
            $backup_dir = $this->upgrade_dir . $this->upgrade_task['upgrade_content']['last_backup'][ 'key' ] . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR;
        }
        try {
            if (is_dir($backup_dir)) {
                dir_copy($backup_dir, rtrim($this->root_path, DIRECTORY_SEPARATOR));
            }
            return true;
        } catch (\Exception $e) {
            $this->upgrade_task[ 'error' ][] = '源码备份恢复失败稍后请手动恢复，源码备份文件路径：' . $backup_dir . '，失败原因：' . $e->getMessage() . $e->getFile() . $e->getLine();
            Cache::set($this->cache_key, $this->upgrade_task);
            return true;
        }
    }

    /**
     * 恢复数据库
     * @return array|true
     */
    public function restoreSql($index = 0)
    {
        if (!isset($this->upgrade_task['is_need_backup']) || $this->upgrade_task['is_need_backup']) {
            $backup_dir = $this->upgrade_dir . $this->upgrade_task[ 'key' ] . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR;
        } else {
            $backup_dir = $this->upgrade_dir . $this->upgrade_task['upgrade_content']['last_backup'][ 'key' ] . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR;
        }
        try {
            if (is_dir($backup_dir)) {
                $db = new DbBackup($backup_dir, key: $this->upgrade_task[ 'key' ]);
                $result = $db->restoreDatabase();
                if ($result !== true) return ['index' => $result];

                $restore_progress = $db->getRestoreProgress();
                if ($restore_progress % 5 == 0) {
                    $this->upgrade_task[ 'log' ][] = $restore_progress == 0 ? '数据库开始恢复' : '数据库恢复中已恢复'. $restore_progress . '%';
                }
            }

            return true;
        } catch (\Exception $e) {
            $this->upgrade_task[ 'error' ][] = '数据库备份恢复失败稍后请手动恢复，数据库备份文件路径：' . $backup_dir . '，失败原因：' . $e->getMessage() . $e->getFile() . $e->getLine();
            Cache::set($this->cache_key, $this->upgrade_task);
            return true;
        }
    }

    public function restoreComplete()
    {
        ( new UpgradeRecordsService() )->failed($this->upgrade_task[ 'key' ], $this->upgrade_task['error']);
        $this->clearUpgradeTask(2);
        return true;
    }

    /**
     * 获取升级内容
     * @param string $addon
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUpgradeContent(string $addon = '')
    {
        $content = [
            'content' => [],
            'upgrade_apps' => []
        ];

        $upgrade = [
            'product_key' => BaseNiucloudClient::PRODUCT
        ];
        $apps = [];
        if (!$addon) {
            $upgrade[ 'app_key' ] = AddonDict::FRAMEWORK_KEY;
            $upgrade[ 'version' ] = config('version.version');
            array_push($apps, $upgrade);
        } else {
            $addons = array_filter(explode(',', $addon));
            foreach ($addons as $key) {
                $info = ( new Addon() )->where([ [ 'key', '=', $key ] ])->field('version,type')->find();
                $upgrade[ 'app_key' ] = $key;
                $upgrade[ 'version' ] = $info['version'];
                if ($info['type'] == 'app') {
                    array_unshift($apps, $upgrade);
                } else {
                    array_push($apps, $upgrade);
                }
            }
        }

        foreach ($apps as $item) {
            $upgrade_content = ( new CoreModuleService() )->getUpgradeContent($item)[ 'data' ] ?? [];
            if (!empty($upgrade_content)) {
                $content['content'][] = $upgrade_content;
                $content['upgrade_apps'][] = $upgrade_content['app']['app_key'];
            }
        }

        if (!empty($content['content'])) {
            $content['last_backup'] = (new SysBackupRecords())->where([ ['status', '=', BackupDict::STATUS_COMPLETE ] ])->order('complete_time desc')->find();
        }

        return $content;
    }

    /**
     * 获取正在进行的升级任务
     * @return mixed|null
     */
    public function getUpgradeTask()
    {
        return $this->upgrade_task;
    }

    /**
     * 清除升级任务
     * @return true
     */
    public function clearUpgradeTask(int $delayed = 0, int $is_active = 0)
    {
        // 主动取消升级
        if ($is_active && $this->upgrade_task && !empty($this->upgrade_task[ 'key' ])) {
            ( new UpgradeRecordsService() )->edit([ [ 'upgrade_key', '=', $this->upgrade_task[ 'key' ] ], ['status', '=', UpgradeDict::STATUS_READY ] ], ['status' => UpgradeDict::STATUS_CANCEL]);
        }

        if ($delayed) {
            Cache::set($this->cache_key, $this->upgrade_task, $delayed);
        } else {
            Cache::set($this->cache_key, null);
        }
        // 清除云编译的任务缓存
        ( new CoreCloudBuildService() )->clearTask();
        return true;
    }

    /**
     * 获取插件定义的package目录
     * @param string $addon
     * @return string
     */
    public function geAddonPackagePath(string $addon)
    {
        return root_path() . 'addon' . DIRECTORY_SEPARATOR . $addon . DIRECTORY_SEPARATOR . 'package' . DIRECTORY_SEPARATOR;
    }

    /**
     * 用户操作
     * @param $operate
     * @return void
     */
    public function operate($operate)
    {
        if (!$this->upgrade_task) return true;

        switch ($operate) {
            case 'local':
                $this->upgrade_task[ 'step' ] = 'gteCloudBuildLog';
                Cache::set($this->cache_key, $this->upgrade_task);
                break;
            case 'rollback':
                $fail_reason = [
                    'Message' => '失败原因：一键云编译队列任务过多',
                    'File' => '',
                    'Line' => '',
                    'Trace' => ''
                ];
                $this->upgradeErrorHandle($fail_reason);
                break;
        }
    }
}

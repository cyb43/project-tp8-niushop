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

namespace app\service\core\niucloud;

use app\dict\addon\AddonDict;
use app\model\addon\Addon;
use app\service\core\addon\CoreAddonBaseService;
use app\service\core\addon\CoreAddonDevelopDownloadService;
use app\service\core\addon\WapTrait;
use core\base\BaseCoreService;
use core\exception\CloudBuildException;
use core\exception\CommonException;
use core\util\niucloud\BaseNiucloudClient;
use core\util\niucloud\CloudService;
use think\facade\Cache;

/**
 * 应用管理服务层
 */
class CoreCloudBuildService extends BaseCoreService
{
    private $cache_key = 'cloud_build_task';

    private $build_task;

    protected $root_path;

    protected $auth_code;

    use WapTrait;

    public function __construct()
    {
        parent::__construct();
        $this->root_path = project_path();
        $this->build_task = Cache::get($this->cache_key);
        $this->auth_code = ( new CoreNiucloudConfigService() )->getNiucloudConfig()[ 'auth_code' ] ?? '';
    }

    /**
     * 编译前环境检测
     * @return array|array[]
     */
    public function buildPreCheck()
    {
        $niucloud_dir = $this->root_path . 'niucloud' . DIRECTORY_SEPARATOR;
        $admin_dir = $this->root_path . 'admin' . DIRECTORY_SEPARATOR;
        $web_dir = $this->root_path . 'web' . DIRECTORY_SEPARATOR;
        $wap_dir = $this->root_path . 'uni-app' . DIRECTORY_SEPARATOR;

        try {
            if (!is_dir($admin_dir)) throw new CommonException('ADMIN_DIR_NOT_EXIST');
            if (!is_dir($web_dir)) throw new CommonException('WEB_DIR_NOT_EXIST');
            if (!is_dir($wap_dir)) throw new CommonException('UNIAPP_DIR_NOT_EXIST');
        } catch (\Exception $e) {
            throw new CommonException($e->getMessage());
        }

        $data = [
            // 目录检测
            'dir' => [
                // 要求可读权限
                'is_readable' => [],
                // 要求可写权限
                'is_write' => []
            ]
        ];

        clearstatcache();

        // 校验niucloud/public niucloud/vendor 目录是否可读可写
        $data[ 'dir' ][ 'is_readable' ][] = [ 'dir' => str_replace(project_path(), '', public_path()), 'status' => is_readable(public_path()) ];
        $data[ 'dir' ][ 'is_readable' ][] = [ 'dir' => str_replace(project_path(), '', $niucloud_dir . 'vendor'), 'status' => is_readable($niucloud_dir . 'vendor') ];

        $data[ 'dir' ][ 'is_write' ][] = [ 'dir' => str_replace(project_path(), '', public_path()), 'status' => is_write(public_path()) ];
        $data[ 'dir' ][ 'is_write' ][] = [ 'dir' => str_replace(project_path(), '', $niucloud_dir . 'vendor'), 'status' => is_write($niucloud_dir . 'vendor') ];

        // 校验niucloud/public下 wap web admin 目录及文件是否可读可写
        $check_res = checkDirPermissions(public_path() . 'wap');
        $check_res = array_merge2($check_res, checkDirPermissions(public_path() . 'admin'));
        $check_res = array_merge2($check_res, checkDirPermissions(public_path() . 'web'));

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
     * 云编译
     * @param $addon
     * @return void
     */
    public function cloudBuild()
    {
        if (empty($this->auth_code)) {
            throw new CommonException('CLOUD_BUILD_AUTH_CODE_NOT_FOUND');
        }
        if ($this->build_task) throw new CommonException('CLOUD_BUILD_TASK_EXIST');

        $action_token = ( new CoreModuleService() )->getActionToken('cloudbuild', [ 'data' => [ 'product_key' => BaseNiucloudClient::PRODUCT ] ]);

        // 上传任务key
        $task_key = uniqid();
        // 此次上传任务临时目录
        $temp_dir = runtime_path() . 'backup' . DIRECTORY_SEPARATOR . 'cloud_build' . DIRECTORY_SEPARATOR . $task_key . DIRECTORY_SEPARATOR;
        $package_dir = $temp_dir . 'package' . DIRECTORY_SEPARATOR;
        dir_mkdir($package_dir);

        // 拷贝composer文件
        file_put_contents($package_dir . 'composer.json', file_get_contents(root_path() . 'composer.json'));
        // 拷贝手机端文件
        $wap_is_compile = ( new Addon() )->where([ [ 'compile', 'like', '%wap%' ] ])->field('id')->findOrEmpty();
        if ($wap_is_compile->isEmpty()) {
            dir_copy($this->root_path . 'uni-app', $package_dir . 'uni-app', exclude_dirs: [ 'node_modules', 'unpackage', 'dist' ]);
            $this->handleUniapp($package_dir . 'uni-app');
        }
        // 拷贝admin端文件
        $admin_is_compile = ( new Addon() )->where([ [ 'compile', 'like', '%admin%' ] ])->field('id')->findOrEmpty();
        if ($admin_is_compile->isEmpty()) {
            dir_copy($this->root_path . 'admin', $package_dir . 'admin', exclude_dirs: [ 'node_modules', 'dist', '.vscode', '.idea' ]);
        }
        // 拷贝web端文件
        $web_is_compile = ( new Addon() )->where([ [ 'compile', 'like', '%web%' ] ])->field('id')->findOrEmpty();
        if ($web_is_compile->isEmpty()) {
            dir_copy($this->root_path . 'web', $package_dir . 'web', exclude_dirs: [ 'node_modules', '.output', '.nuxt' ]);
        }

        $this->handleCustomPort($package_dir);

        $zip_file = $temp_dir . DIRECTORY_SEPARATOR . 'build.zip';
        ( new CoreAddonDevelopDownloadService('') )->compressToZip($package_dir, $zip_file);

        $query = [
            'authorize_code' => $this->auth_code,
            'timestamp' => time(),
            'token' => $action_token[ 'data' ][ 'token' ] ?? ''
        ];
        $response = ( new CloudService() )->httpPost('cloud/build?' . http_build_query($query), [
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => fopen($zip_file, 'r'),
                    'filename' => 'build.zip'
                ]
            ],
            'timeout' => 300.0
        ]);
        if (isset($response[ 'code' ]) && $response[ 'code' ] == 0) throw new CloudBuildException($response[ 'msg' ]);

        $this->build_task = [
            'task_key' => $task_key,
            'timestamp' => $query[ 'timestamp' ]
        ];
        Cache::set($this->cache_key, $this->build_task);

        return $this->build_task;
    }

    private function handleUniapp(string $dir)
    {
        $addon = ( new Addon() )->where([ [ 'status', '=', AddonDict::ON ] ])->value('key', '');
        $this->compileDiyComponentsCode($dir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR, $addon);
    }

    private function handleCustomPort(string $package_dir)
    {
        $addons = get_install_addons();

        foreach ($addons as $addon) {
            $custom_port = ( new CoreAddonBaseService() )->getAddonConfig($addon)[ 'port' ] ?? [];
            if (!empty($custom_port)) {
                $addon_path = root_path() . 'addon' . DIRECTORY_SEPARATOR . $addon . DIRECTORY_SEPARATOR;
                foreach ($custom_port as $port) {
                    if (is_dir($addon_path . $port[ 'name' ])) {
                        dir_copy($addon_path . $port[ 'name' ], $package_dir . $port[ 'name' ]);
                        $json_path = $package_dir . $port[ 'name' ] . DIRECTORY_SEPARATOR . 'info.json';
                        file_put_contents($json_path, json_encode($port));
                    }
                }
            }
        }
    }

    /**
     * 安装时云编译
     * @return void
     */
    public function installBuild()
    {
        if ($this->build_task) throw new CommonException('CLOUD_BUILD_TASK_EXIST');

        // 上传任务key
        $task_key = uniqid();
        // 此次上传任务临时目录
        $temp_dir = runtime_path() . 'backup' . DIRECTORY_SEPARATOR . 'cloud_build' . DIRECTORY_SEPARATOR . $task_key . DIRECTORY_SEPARATOR;
        $package_dir = $temp_dir . 'package' . DIRECTORY_SEPARATOR;
        dir_mkdir($package_dir);

        // 拷贝composer文件
        file_put_contents($package_dir . 'composer.json', file_get_contents(root_path() . 'composer.json'));
        // 拷贝手机端文件
        $wap_is_compile = ( new Addon() )->where([ [ 'compile', 'like', '%wap%' ] ])->field('id')->findOrEmpty();
        if ($wap_is_compile->isEmpty()) {
            dir_copy($this->root_path . 'uni-app', $package_dir . 'uni-app', exclude_dirs: [ 'node_modules', 'unpackage', 'dist' ]);
        }
        // 拷贝admin端文件
        $admin_is_compile = ( new Addon() )->where([ [ 'compile', 'like', '%admin%' ] ])->field('id')->findOrEmpty();
        if ($admin_is_compile->isEmpty()) {
            dir_copy($this->root_path . 'admin', $package_dir . 'admin', exclude_dirs: [ 'node_modules', 'dist', '.vscode', '.idea' ]);
        }
        // 拷贝web端文件
        $web_is_compile = ( new Addon() )->where([ [ 'compile', 'like', '%web%' ] ])->field('id')->findOrEmpty();
        if ($web_is_compile->isEmpty()) {
            dir_copy($this->root_path . 'web', $package_dir . 'web', exclude_dirs: [ 'node_modules', '.output', '.nuxt' ]);
        }

        $this->handleCustomPort($package_dir);

        $zip_file = $temp_dir . DIRECTORY_SEPARATOR . 'build.zip';
        ( new CoreAddonDevelopDownloadService('') )->compressToZip($package_dir, $zip_file);

        $query = [
            'authorize_code' => $this->auth_code,
            'timestamp' => time()
        ];
        $versions = array_merge([ 'niucloud-admin' => config('version.version') ], ( ( new Addon() )->column('version', 'key') ));
        ksort($versions);
        $query[ 'version' ] = md5(json_encode($versions) . BaseNiucloudClient::PRODUCT);

        $response = ( new CloudService() )->httpPost('cloud/install?' . http_build_query($query), [
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => fopen($zip_file, 'r'),
                    'filename' => 'build.zip'
                ]
            ],
            'timeout' => 300.0
        ]);
        if (isset($response[ 'code' ]) && $response[ 'code' ] == 0) throw new CommonException($response[ 'msg' ]);

        $this->build_task = [
            'task_key' => $task_key,
            'timestamp' => $query[ 'timestamp' ],
            'version' => $query[ 'version' ]
        ];
        Cache::set($this->cache_key, $this->build_task);

        return $this->build_task;
    }

    /**
     * 获取编译任务
     * @return mixed
     */
    public function getBuildTask()
    {
        return $this->build_task;
    }

    /**
     * 获取编译执行日志
     * @return void
     */
    public function getBuildLog()
    {
        if (!$this->build_task) return;

        $query = [
            'authorize_code' => $this->auth_code,
            'timestamp' => $this->build_task[ 'timestamp' ]
        ];
        $build_log = ( new CloudService() )->httpGet('cloud/get_build_logs?' . http_build_query($query));

        if (isset($build_log[ 'data' ]) && isset($build_log[ 'data' ][ 0 ]) && is_array($build_log[ 'data' ][ 0 ])) {
            $last = end($build_log[ 'data' ][ 0 ]);
            if ($last[ 'percent' ] == 100 && $last[ 'code' ] == 1) {
                $build_log[ 'data' ][ 0 ] = $this->buildSuccess($build_log[ 'data' ][ 0 ]);
            }
        }
        return $build_log;
    }

    /**
     * 编译完成
     * @param array $log
     * @return array
     */
    public function buildSuccess(array $log)
    {
        try {
            $query = [
                'authorize_code' => $this->auth_code,
                'timestamp' => $this->build_task[ 'timestamp' ]
            ];
            if (isset($this->build_task[ 'version' ])) $query[ 'version' ] = $this->build_task[ 'version' ];
            $chunk_size = 1 * 1024 * 1024;
            $temp_dir = runtime_path() . 'backup' . DIRECTORY_SEPARATOR . 'cloud_build' . DIRECTORY_SEPARATOR . $this->build_task[ 'task_key' ] . DIRECTORY_SEPARATOR;

            if (!isset($this->build_task[ 'index' ])) {
                $response = ( new CloudService() )->request('HEAD', 'cloud/build_download?' . http_build_query($query), [
                    'headers' => [ 'Range' => 'bytes=0-' ]
                ]);
                $length = $response->getHeader('Content-range');
                $length = (int) explode("/", $length[ 0 ])[ 1 ];
                $step = (int) ceil($length / $chunk_size);

                $this->build_task = array_merge($this->build_task, [ 'step' => $step, 'index' => 0, 'length' => $length ]);
                Cache::set($this->cache_key, $this->build_task);
            } else {
                $zip_file = $temp_dir . 'download.zip';
                $zip_resource = fopen($zip_file, 'a');

                if (( $this->build_task[ 'index' ] + 1 ) <= $this->build_task[ 'step' ]) {
                    $start = $this->build_task[ 'index' ] * $chunk_size;
                    $end = ( $this->build_task[ 'index' ] + 1 ) * $chunk_size;
                    $end = min($end, $this->build_task[ 'length' ]);

                    $response = ( new CloudService() )->request('GET', 'cloud/build_download?' . http_build_query($query), [
                        'headers' => [ 'Range' => "bytes={$start}-{$end}" ]
                    ]);
                    fwrite($zip_resource, $response->getBody());
                    fclose($zip_resource);

                    $this->build_task[ 'index' ] += 1;
                    Cache::set($this->cache_key, $this->build_task);

                    $log[] = [ 'code' => 1, 'action' => '编译包下载中,已下载' . round($this->build_task[ 'index' ] / $this->build_task[ 'step' ] * 100) . '%', 'percent' => '100' ];
                } else {
                    // 解压文件
                    $zip = new \ZipArchive();

                    if ($zip->open($zip_file) === true) {
                        dir_mkdir($temp_dir . 'download');
                        $zip->extractTo($temp_dir . 'download');
                        $zip->close();

//                        if (is_dir($temp_dir . 'download' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'admin')) {
//                            del_target_dir(public_path() .'admin', true);
//                        }
//                        if (is_dir($temp_dir . 'download' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'web')) {
//                            del_target_dir(public_path() .'web', true);
//                        }
//                        if (is_dir($temp_dir . 'download' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'wap')) {
//                            del_target_dir(public_path() .'wap', true);
//                        }

                        dir_copy($temp_dir . 'download', root_path());

                        $this->clearTask();
                    } else {
                        $log[] = [ 'code' => 0, 'msg' => '编译包解压失败', 'action' => '编译包解压', 'percent' => '100' ];
                    }
                }
            }
        } catch (\Exception $e) {
            $log[] = [ 'code' => 0, 'msg' => $e->getMessage(), 'action' => '', 'percent' => '100' ];
            $this->clearTask();
        }
        return $log;
    }

    /**
     * 清除任务
     * @return void
     */
    public function clearTask()
    {
        if (!$this->build_task) return;
        $temp_dir = runtime_path() . 'backup' . DIRECTORY_SEPARATOR . 'cloud_build' . DIRECTORY_SEPARATOR . $this->build_task[ 'task_key' ] . DIRECTORY_SEPARATOR;;
        @del_target_dir($temp_dir, true);
        Cache::set($this->cache_key, null);
    }
}

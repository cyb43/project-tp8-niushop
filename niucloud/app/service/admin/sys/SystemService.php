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

namespace app\service\admin\sys;

use app\job\sys\CheckJob;
use app\service\core\sys\CoreSysConfigService;
use core\base\BaseAdminService;
use core\exception\AdminException;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Log;
use Throwable;

/**
 * 系统信息服务层
 * Class SystemService
 * @package app\service\admin\sys
 */
class SystemService extends BaseAdminService
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取版权信息(网站整体，不按照站点设置)
     * @return array
     */
    public function getInfo()
    {
        return [
            'os' => PHP_OS,
            'environment' => $_SERVER[ 'SERVER_SOFTWARE' ],
            'php_v' => PHP_VERSION,
            'version' => config('version')
        ];
    }

    /**
     * 获取域名配置
     */
    public function getUrl()
    {
        return ( new CoreSysConfigService() )->getSceneDomain();
    }

    /**
     * 获取系统信息
     * @return array
     */
    public function getSystemInfo()
    {
        $server = [];
        $server[] = [ "name" => get_lang('dict_setting.server_system'), "server" => PHP_OS ];
        $server[] = [ "name" => get_lang('dict_setting.server_setting'), "server" => PHP_SAPI ];
        $server[] = [ "name" => get_lang('dict_setting.php_version'), "server" => PHP_VERSION ];

        //环境权限
        $system_variables = [];
        //pdo
        $pdo = extension_loaded('pdo') && extension_loaded('pdo_mysql');
        $system_variables[] = [ "name" => "pdo", "need" => get_lang('dict_setting.php_authority_ask'), "status" => $pdo ];
        //curl
        $curl = extension_loaded('curl') && function_exists('curl_init');
        $system_variables[] = [ "name" => "curl", "need" => get_lang('dict_setting.php_authority_ask'), "status" => $curl ];
        //openssl
        $openssl = extension_loaded('openssl');
        $system_variables[] = [ "name" => "openssl", "need" => get_lang('dict_setting.php_authority_ask'), "status" => $openssl ];
        //gd
        $gd = extension_loaded('gd');
        $system_variables[] = [ "name" => "GD库", "need" => get_lang('dict_setting.php_authority_ask'), "status" => $gd ];
        //fileinfo
        $fileinfo = extension_loaded('fileinfo');
        $system_variables[] = [ "name" => "fileinfo", "need" => get_lang('dict_setting.php_authority_ask'), "status" => $fileinfo ];
        //目录权限
        $root_path = str_replace("\\", DIRECTORY_SEPARATOR, dirname(__FILE__, 5));
        $root_path = str_replace("../", DIRECTORY_SEPARATOR, $root_path);


        $dirs_list = [
            [ "path" => $root_path . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR, "need" => get_lang('dict_setting.file_authority_ask'), "path_name" => "/runtime", "name" => "runtime" ],
            [ "path" => $root_path . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR, "need" => get_lang('dict_setting.file_authority_ask'), "path_name" => "/public/upload", "name" => "upload" ],
        ];
        //目录 可读 可写检测
        foreach ($dirs_list as $k => $v) {
            $is_readable = is_readable($v[ "path" ]);
            $is_write = is_write($v[ "path" ]);
            if ($is_readable && $is_write) {
                $dirs_list[ $k ][ "status" ] = true;
            } else {
                $dirs_list[ $k ][ "status" ] = false;
            }
        }
        $system_variables = array_merge($system_variables, $dirs_list);

        //获取环境版本
        $server_version = [];
        $row = (array) Db::query("select VERSION() as verson");
        $server_version[] = [ "name" => get_lang('dict_setting.php_version'), "demand" => get_lang('dict_setting.php_ask'), "server" => PHP_VERSION ];
        $server_version[] = [ "name" => get_lang('dict_setting.mysql_version'), "demand" => get_lang('dict_setting.mysql_ask'), "server" => $row[ 0 ][ 'verson' ] ];

        // 进程
        $process[] = [ "name" => "php think queue:listen", "need" => get_lang('dict_setting.php_authority_ask'), "status" => ( new SystemService() )->checkJob() ];

        return [ "server" => $server, "dirs_list" => $dirs_list, 'system_variables' => $system_variables, 'server_version' => $server_version, 'process' => $process ];
    }

    /**
     * 清除表缓存
     * @return string
     */
    public function schemaCache()
    {
        if (is_dir(dirname($_SERVER[ 'DOCUMENT_ROOT' ]) . '/runtime/schema')) {
            rmdirs(dirname($_SERVER[ 'DOCUMENT_ROOT' ]) . '/runtime/schema');
        }
        return 'CLEAR_MYSQL_CACHE_SUCCESS';
    }

    /**
     * 清理缓存
     */
    public function clearCache()
    {
        Cache::clear();
        return 'CACHE_CLEAR_SUCCESS';
    }

    /**
     *校验消息队列是否正常运行
     * @return bool
     */
    public function checkJob()
    {
        $secret = uniqid('', true);
        $file = root_path('runtime') . $secret . '.job';
        try {
            CheckJob::dispatch([ 'file' => $file ]);
        } catch (Throwable $e) {
            return false;
        }
//        $timeout = 0;
//        while($timeout < 5){
//            if (file_exists($file)) {
//                @unlink($file);
//                return true;
//            }
//            $timeout++;
//            sleep(1);
//        }
        sleep(5);
        if (file_exists($file)) {
            @unlink($file);
            return true;
        }
        return false;
    }

    /**
     * 校验计划任务是否正常运行
     * @return bool
     */
    public function checkSchedule()
    {
        $file = root_path('runtime') . '.schedule';
        if (file_exists($file)) {
            $time = file_get_contents($file);
            if (!empty($time) && abs($time - time()) < 90) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取二维码
     * @param $data
     * @return array
     */
    public function getQrcode($data)
    {
        $page = $data[ 'page' ];//'app/pages/index/diy_form';
        $qrcode_data = [];
        foreach ($data[ 'params' ] as $item) {
            $qrcode_data[] = [
                'key' => $item[ 'column_name' ],
                'value' => $item[ 'column_value' ]
            ];
        }
        $dir = 'upload/' . $data[ 'folder' ] . '_qrcode';

        try {
            // h5
            //$h5_path = qrcode('', $page, $data, $dir);

            // 微信小程序
            $weapp_path = qrcode('', $page, $qrcode_data, $dir, 'weapp');
            return [
                'h5_path' => '',
                'weapp_path' => $weapp_path
            ];
        } catch (AdminException $e) {
            Log::write('获取推广微信小程序二维码error' . $e->getMessage() . $e->getFile() . $e->getLine() . 'params:' . json_encode($data, 256));
            throw new AdminException($e->getMessage() . $e->getFile() . $e->getLine() . 'params:' . json_encode($data, 256));
        }
    }
}

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

namespace app\job\schedule;

use core\base\BaseJob;
use think\facade\Log;

/**
 * 队列异步调用定时任务
 */
class AutoClearPosterAndQrcode extends BaseJob
{

    public function doJob()
    {
        Log::write('AutoClearPosterAndQrcode 定时清除 二维码及海报数据开始' . date('Y-m-d H:i:s'));
        try {
            // 清理海报目录
            $dir = 'upload/poster';
            $dir = public_path($dir);
            Log::write('AutoClearPosterAndQrcode尝试清理海报目录: ' . $dir);
            $res = $this->clearDirectory($dir);
            Log::write('AutoClearPosterAndQrcode海报目录清理结果: ' . ($res ? '成功' : '失败'));

            // 清理二维码目录
            $qrcode_dir = 'upload/qrcode';
            $qrcode_dir = public_path($qrcode_dir);
            Log::write('AutoClearPosterAndQrcode尝试清理二维码目录: ' . $qrcode_dir);
            $res = $this->clearDirectory($qrcode_dir);
            Log::write('AutoClearPosterAndQrcode二维码目录清理结果: ' . ($res ? '成功' : '失败'));

            return true;
        } catch (\Exception $e) {
            Log::write('AutoClearPosterAndQrcode 定时清除异常: ' . $e->getMessage() . ' 位置: ' . $e->getFile() . ':' . $e->getLine() . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * 清空指定目录下的所有文件和子目录
     *
     * @param string $directory 目录路径
     * @param bool $preserveDirectory 是否保留根目录（默认保留）
     * @return bool 是否成功执行
     */
    function clearDirectory(string $directory, bool $preserveDirectory = true): bool
    {
        // 规范化目录路径，统一使用DIRECTORY_SEPARATOR
        $directory = rtrim(realpath($directory), DIRECTORY_SEPARATOR);

        Log::write('AutoClearPosterAndQrcode开始清理目录: ' . $directory);

        // 检查目录是否存在
        if (!is_dir($directory)) {
            Log::write('AutoClearPosterAndQrcode目录不存在或不是有效目录: ' . $directory);
            return false;
        }

        // 打开目录
        $handle = opendir($directory);
        if (!$handle) {
            Log::write('AutoClearPosterAndQrcode无法打开目录: ' . $directory);
            return false;
        }

        // 遍历目录内容
        while (($entry = readdir($handle)) !== false) {
            // 跳过当前目录和上级目录
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            // 使用DIRECTORY_SEPARATOR确保路径分隔符正确
            $path = $directory . DIRECTORY_SEPARATOR . $entry;

            // 递归处理子目录
            if (is_dir($path)) {
                // 递归清空子目录
                if (!$this->clearDirectory($path, false)) {
                    Log::write('AutoClearPosterAndQrcode递归清理子目录失败: ' . $path);
                    closedir($handle);
                    return false;
                }
                Log::write('AutoClearPosterAndQrcode已递归删除子目录: ' . $path);
                // 子目录已经在递归调用中被删除，不需要再次删除
            } else {
                // 删除文件
                if (!unlink($path)) {
                    Log::write('AutoClearPosterAndQrcode删除文件失败: ' . $path);
                    closedir($handle);
                    return false;
                }
            }
        }

        // 关闭目录句柄
        closedir($handle);

        // 是否删除根目录本身
        if (!$preserveDirectory) {
            Log::write('AutoClearPosterAndQrcode准备删除根目录: ' . $directory);
            if (!rmdir($directory)) {
                Log::write('AutoClearPosterAndQrcode删除根目录失败: ' . $directory);
                return false;
            }
            Log::write('AutoClearPosterAndQrcode成功删除根目录: ' . $directory);
        } else {
            Log::write('AutoClearPosterAndQrcode保留根目录: ' . $directory);
        }

        return true;
    }


}

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

use app\adminapi\middleware\AdminCheckRole;
use app\adminapi\middleware\AdminCheckToken;
use app\adminapi\middleware\AdminLog;
use think\facade\Route;


/**
 * 路由
 */
Route::group('', function () {

    // 获取正在进行的升级任务
    Route::get('upgrade/task', 'addon.Upgrade/getUpgradeTask');

    // 执行升级
    Route::post('upgrade/execute', 'addon.Upgrade/execute');

    // 清除升级任务
    Route::post('upgrade/clear', 'addon.Upgrade/clearUpgradeTask');

    // 升级环境检测
    Route::get('upgrade/check/[:addon]', 'addon.Upgrade/upgradePreCheck')->pattern(['addon' => '[\w|\,]+']);

    // 升级
    Route::post('upgrade/[:addon]', 'addon.Upgrade/upgrade')->pattern(['addon' => '[\w|\,]+']);

    // 获取升级内容
    Route::get('upgrade/[:addon]', 'addon.Upgrade/getUpgradeContent')->pattern(['addon' => '[\w|\,]+']);

    Route::post('upgrade/operate/:operate', 'addon.Upgrade/operate');

    // 升级记录分页列表
    Route::get('upgrade/records', 'addon.Upgrade/getRecords');

    // 删除升级记录
    Route::delete('upgrade/records', 'addon.Upgrade/delRecords');

    // 备份记录分页列表
    Route::get('backup/records', 'addon.Backup/getRecords');

    // 修改备注
    Route::put('backup/remark', 'addon.Backup/modifyRemark');

    // 恢复前检测文件是否存在，备份
    Route::post('backup/check_dir', 'addon.Backup/checkDirExist');

    // 检测目录权限
    Route::post('backup/check_permission', 'addon.Backup/checkPermission');

    // 恢复升级备份
    Route::post('backup/restore', 'addon.Backup/restoreBackup');

    // 删除升级记录
    Route::post('backup/delete', 'addon.Backup/deleteRecords');

    // 手动备份
    Route::post('backup/manual', 'addon.Backup/manualBackup');

    // 获取进行中的恢复
    Route::get('backup/restore_task', 'addon.Backup/getRestoreTask');

    // 获取进行中的备份
    Route::get('backup/task', 'addon.Backup/getBackupTask');
})->middleware([
    AdminCheckToken::class,
    AdminCheckRole::class,
    AdminLog::class
]);

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
 * 应用插件相关路由
 */
Route::group('niucloud', function() {
    //设置 授权信息
    Route::post('authinfo', 'niucloud.Module/setAuthorize');
    //获取 授权设置
    Route::get('admin/authinfo', 'niucloud.Module/getAuthorize');

    //获取授权信息
    Route::get('authinfo', 'niucloud.Module/authorize');


    //获取应用插件列表
    Route::get('module', 'addon.Addon/getLocalAddonList');
    //获取应用详情
    Route::get('module', 'addon.Addon/getLocalAddonList');
    //获取插件插件列表
    Route::get('module/version', 'addon.Addon/lists');
    //下载
    Route::get('module/download/:version_id', 'addon.Addon/info');
    //更新
    Route::put('addon/status/:version_id', 'addon.Addon/setStatus');
    // 获取框架最新版本
    Route::get('framework/newversion', 'niucloud.Module/getFrameworkLastVersion');
    // 获取框架版本更新记录
    Route::get('framework/version/list', 'niucloud.Module/getFrameworkVersionList');

    // 获取应用/插件的版本更新记录
    Route::get('app_version/list', 'niucloud.Module/getAppVersionList');

    // 云编译
    Route::post('build', 'niucloud.Cloud/build');
    // 云编译
    Route::post('build/install', 'niucloud.Cloud/installBuild');
    // 获取编译任务
    Route::get('build', 'niucloud.Cloud/getBuildTask');
    // 获取云编译
    Route::get('build/log', 'niucloud.Cloud/getBuildLog');
    // 清除编译任务
    Route::post('build/clear', 'niucloud.Cloud/clearBuildTask');
    // 编译前环境检测
    Route::get('build/check', 'niucloud.Cloud/buildPreCheck');
    // 申请体验
    Route::get('apply/experience', 'niucloud.Module/applyExperience');

    //连通测试
    Route::post('build/connect_test', 'niucloud.Cloud/connectTest');
    //保存本地服务器地址
    Route::post('build/set_local_url', 'niucloud.Cloud/setLocalCloudCompileConfig');
    //获取本地服务器地址
    Route::get('build/get_local_url', 'niucloud.Cloud/getLocalCloudCompileConfig');
})->middleware([
    AdminCheckToken::class,
    AdminCheckRole::class,
    AdminLog::class
]);

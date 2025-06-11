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



Route::group('user', function () {
    /***************************************************** 用户 ****************************************************/
    Route::get('', 'user.User/lists');
    //全部用户列表
    Route::get('user_all', 'user.User/getUserAll');
    //用户详情
    Route::get(':uid', 'user.User/info');
    //用户新增
    Route::post('', 'user.User/add');
    //用户锁定
    Route::put('lock/:uid', 'user.User/lock');
    //用户解锁
    Route::put('unlock/:uid', 'user.User/unlock');
    //编辑用户
    Route::put(':uid', 'user.User/edit');
    //修改用户属性
    Route::put(':uid/:field', 'user.User/modify');
    //删除用户
    Route::delete(':uid', 'user.User/del');

    /***************************************************** 操作日志 **************************************************/
    //操作日志列表
    Route::get('userlog', 'user.UserLog/lists');
    //操作日志详情
    Route::get('userlog/:id', 'user.UserLog/info');
    Route::delete('userlog/destroy', 'user.UserLog/destroy');

})->middleware([
    AdminCheckToken::class,
    AdminCheckRole::class,
    AdminLog::class
]);

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

use app\api\middleware\ApiCheckToken;
use app\api\middleware\ApiLog;
use think\facade\Route;


/**
 * 自定义页面
 */
Route::group('diy', function() {

    // 自定义页面信息
    Route::get('diy', 'diy.Diy/info');

    Route::get('tabbar', 'diy.Diy/tabbar');

    Route::get('tabbar/list', 'diy.Diy/tabbarList');

    Route::get('share', 'diy.Diy/share');

    // 万能表单详情
    Route::get('form', 'diy.DiyForm/info');

})->middleware(ApiLog::class)
    ->middleware(ApiCheckToken::class, false);

/**
 * 自定义表单
 */
Route::group('diy', function() {

    // 提交填表记录
    Route::post('form/record', 'diy.DiyForm/addRecord');

    // 修改填表记录
    Route::put('form/record', 'diy.DiyForm/editRecord');

    // 获取表单填写结果信息
    Route::get('form/result', 'diy.DiyForm/getResult');

    // 获取填表记录
    Route::get('form/record', 'diy.DiyForm/getRecord');

    // 获取个人资料表单填写记录
    Route::get('form/member_record', 'diy.DiyForm/getMemberInfoRecord');

})->middleware(ApiLog::class)
    ->middleware(ApiCheckToken::class, true);


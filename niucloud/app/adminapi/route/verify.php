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
Route::group('verify', function () {
    /*****************************************************  核销相关接口 ****************************************************/
    // 订单核销记录
    Route::get('verify/record', 'verify.Verify/lists');
    // 订单核销记录详情
    Route::get('verify/:verify_code', 'verify.Verify/detail');
    Route::get('detail/:verify_code', 'verify.Verify/getInfoByCode');

    Route::post('verify/:verify_code', 'verify.Verify/verify');


    /*****************************************************  核销员相关接口 ****************************************************/
    // 添加核销员
    Route::post('verifier', 'verify.Verifier/add');
    Route::post('verifier/:id', 'verify.Verifier/edit');
    Route::get('verifier/:id', 'verify.Verifier/detail');
    // 获取核销员列表
    Route::get('verifier', 'verify.Verifier/lists');
    // 获取核销员列表
    Route::get('verifier/select', 'verify.Verifier/select');
    // 获取核销类型
    Route::get('verifier/type', 'verify.Verifier/getVerifyType');
    // 删除核销员
    Route::delete('verifier/:id', 'verify.Verifier/del');

})->middleware([
    AdminCheckToken::class,
    AdminCheckRole::class,
    AdminLog::class
]);

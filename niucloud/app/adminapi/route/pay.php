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
 * 支付相关路由
 */
Route::group('pay', function () {

    /***************************************************** 支付渠道 *************************************************/
    //渠道列表
    Route::get('channel/lists', 'pay.PayChannel/lists');
    //渠道设置
    Route::post('channel/set/:channel/:type', 'pay.PayChannel/set');
    //通过渠道获取支付配置
    Route::get('channel/lists/:channel', 'pay.PayChannel/getListByChannel');
    //转账设置
    Route::post('channel/set/transfer', 'pay.PayChannel/setTransfer');
    //多渠道设置
    Route::post('channel/set/all', 'pay.PayChannel/setAll');
    // 支付审核
    Route::get('audit', 'pay.Pay/audit');
    // 审核通过
    Route::put('pass/:out_trade_no', 'pay.Pay/pass');
    // 审核拒绝
    Route::put('refuse/:out_trade_no', 'pay.Pay/refuse');
    // 支付单据详情
    Route::get('detail/:id', 'pay.Pay/detail');
    /***************************************************** 账单 *************************************************/
    //账单列表
    Route::get('account', 'pay.Account/lists');
    //账单详情
    Route::get('account/:id', 'pay.Account/info');
    //账单统计
    Route::get('account/stat', 'pay.Account/stat');
    //账单类型
    Route::get('account/type', 'pay.Account/accountType');
    /***************************************************** 退款 *************************************************/
    //退款列表
    Route::get('refund', 'pay.PayRefund/pages');
    //退款详情
    Route::get('refund/status', 'pay.PayRefund/getStatus');
    Route::get('refund/:refund_no', 'pay.PayRefund/detail');
    //退款方式
    Route::get('refund/type', 'pay.PayRefund/getRefundType');
    //退款转账
    Route::post('refund/transfer', 'pay.PayRefund/transfer');
    // 获取全部支付方式
    Route::get('type/all', 'pay.PayChannel/getPayTypeList');
    /***************************************************** 支付 *************************************************/
    //去支付
    Route::post('', 'pay.Pay/pay');
    //支付信息
    Route::get('info/:trade_type/:trade_id', 'pay.Pay/info');
    //支付方式列表
    Route::get('type/list', 'pay.Pay/payTypeList');
    //找朋友帮忙付支付信息
    Route::get('friendspay/info/:trade_type/:trade_id/:channel', 'pay.Pay/friendspayInfo');

    /***************************************************** 转账 *************************************************/
    //获取转账场景
    Route::get('transfer_scene', 'pay.Transfer/getWechatTransferScene');
    //设置场景id
    Route::post('transfer_scene/set_scene_id/:scene', 'pay.Transfer/setSceneId');
    //设置业务场景配置
    Route::post('transfer_scene/set_trade_scene/:type', 'pay.Transfer/setTradeScene');
})->middleware([
    AdminCheckToken::class,
    AdminCheckRole::class,
    AdminLog::class
]);

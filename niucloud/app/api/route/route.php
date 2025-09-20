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

use app\api\middleware\ApiChannel;
use app\api\middleware\ApiCheckToken;
use app\api\middleware\ApiLog;
use app\api\route\dispatch\BindDispatch;
use core\dict\DictLoader;
use think\facade\Route;
use app\service\core\niucloud\CoreNotifyService;

//公众号消息推送
Route::any('wechat/serve', 'wechat.Serve/serve')
    ->middleware(ApiChannel::class)
    ->middleware(ApiCheckToken::class)
    ->middleware(ApiLog::class);

// 微信小程序消息推送
Route::any('weapp/serve', 'weapp.Serve/serve')
    ->middleware(ApiChannel::class)
    ->middleware(ApiCheckToken::class)
    ->middleware(ApiLog::class);

Route::group(function() {
    Route::post('niucloud/notify', function() {
        return ( new CoreNotifyService() )->notify();
    });

});

/**
 * 路由
 */
Route::group(function () {
    //获取授权地址
    Route::get('wechat/codeurl', 'wechat.Wechat/getCodeUrl');
    //获取授权信息
    Route::get('wechat/user', 'wechat.Wechat/getWechatUser');
    //公众号通过授权信息登录
    Route::post('wechat/userlogin', 'wechat.Wechat/wechatLogin');
    //检查微信公众号是否配置
    Route::get('wechat/check', 'wechat.Wechat/checkWechatConfig');

    //公众号通过code登录
    Route::post('wechat/login', 'wechat.Wechat/login');
    //公众号通过code注册
    Route::post('wechat/register', 'wechat.Wechat/register');
    //公众号通过code同步授权
    Route::post('wechat/sync', 'wechat.Wechat/sync');
    //公众号扫码登录
    Route::post('wechat/scanlogin', 'wechat.Wechat/scanLogin');
    //小程序通过code登录
    Route::post('weapp/login', 'weapp.Weapp/login');
    //小程序通过code注册
    Route::post('weapp/register', 'weapp.Weapp/register');
    // 获取小程序订阅消息模板id
    Route::get('weapp/subscribemsg', 'weapp.Weapp/subscribeMessage');

    // 查询小程序是否已开通发货信息管理服务
    Route::get('weapp/getIsTradeManaged', 'weapp.Weapp/getIsTradeManaged');

    // 通过外部交易号获取消息跳转路径
    Route::get('weapp/getMsgJumpPath', 'weapp.Weapp/getMsgJumpPath');

    // app通过wx code登录
    Route::post('wxapp/login', 'channel.App/wechatLogin');

    //登录
    Route::get('login', 'login.Login/login');
    //第三方绑定
    Route::post('bind', BindDispatch::class);
    //密码重置
    Route::post('password/reset', 'login.Login/resetPassword');
    //账号密码注册
    Route::post('register', 'login.Register/account');
    //手机号注册
    Route::post('register/mobile', 'login.Register/mobile');
    //账号密码注册
    Route::get('captcha', 'login.Login/captcha');
    //手机号发送验证码
    Route::post('send/mobile/:type', 'login.Login/sendMobileCode');
    //手机号登录
    Route::post('login/mobile', 'login.Login/mobile');

    //校验扫码信息
    Route::get('checkscan', 'sys.scan/checkScan');
    /***************************************************** 会员相关设置**************************************************/
    //获取注册与登录设置
    Route::get('login/config', 'login.Config/getLoginConfig');
    // 协议
    Route::get('agreement/:key', 'agreement.Agreement/info');
    // 获取公众号jssdk config
    Route::get('wechat/jssdkconfig', 'wechat.Wechat/jssdkConfig');
    /***************************************************** 版权相关设置**************************************************/
    Route::get('copyright', 'sys.Config/getCopyright');
    // 站点信息
    Route::get('site', 'sys.Config/site');
    //场景域名
    Route::get('scene_domain', 'sys.Config/getSceneDomain');
    // 获取手机端首页列表
    Route::get('wap_index', 'sys.Config/getWapIndexList');

    // 获取地图设置
    Route::get('map', 'sys.Config/getMap');

    // 获取初始化数据信息
    Route::get('init', 'sys.Config/init');
    // 获取公众号用户是否绑定手机
    Route::get('member_mobile_exist', 'sys.Config/getMemberMobileExist');

    /***************************************************** 地区管理 ****************************************************/
    //通过pid获取列表
    Route::get('area/list_by_pid/:pid', 'sys.Area/listByPid');
    //通过层级获取列表
    Route::get('area/tree/:level', 'sys.Area/tree');
    // 获取省市县数据根据地址id
    Route::get('area/code/:code', 'sys.Area/areaByAreaCode');

    // 通过经纬度查询地址
    Route::get('area/address_by_latlng', 'sys.Area/getAddressByLatlng');

    /***************************************************** 海报管理 ****************************************************/
    //获取海报
    Route::get('poster', 'poster.Poster/poster');

    /***************************************************** 核销管理 ****************************************************/
    //根据业务获取核销码
    Route::get('verify', 'sys.Verify/getVerifyCode');
    //校验当前会员是否是核销员
    Route::get('check_verifier', 'sys.Verify/checkVerifier');
    //核销记录
    Route::get('verify_records', 'sys.Verify/records');
    //会员核销记录
    Route::get('member_verify_records', 'sys.Verify/memberRecordList');
    //核销详情
    Route::get('verify_detail/:code', 'sys.Verify/detail');
    //通过code码获取核销信息
    Route::get('get_verify_by_code', 'sys.Verify/getInfoByCode');
    //核销操作
    Route::post('verify/:code', 'sys.Verify/verify');

    /***************************************************** 会员管理 ****************************************************/
    /***************************************************** 任务管理 ****************************************************/
    // 获取成长值任务
    Route::get('task/growth', 'sys.Task/growth');
    // 获取积分任务
    Route::get('task/point', 'sys.Task/point');
})->middleware(ApiChannel::class)
    ->middleware(ApiCheckToken::class)
    ->middleware(ApiLog::class);

Route::group(function () {
    //公众号更新用户openid
    Route::put('wechat/update_openid', 'wechat.Wechat/updateOpenid');
    //公众号更新用户openid
    Route::put('wechat/update_openid_h5', 'wechat.Wechat/updateOpenidByH5');
    //小程序更新用户openid
    Route::put('weapp/update_openid', 'weapp.Weapp/updateOpenid');

})->middleware(ApiChannel::class)
    ->middleware(ApiCheckToken::class, true)
    ->middleware(ApiLog::class);
//加载插件路由
( new DictLoader("Route") )->load([ 'app_type' => 'api' ]);

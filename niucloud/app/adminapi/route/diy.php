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
 * 自定义页面控制器
 */
Route::group('diy', function() {

    /***************************************************** 自定义页面管理 ****************************************************/

    // 自定义页面分页列表
    Route::get('diy', 'diy.Diy/lists');

    // 自定义页面分页列表，轮播搜索组件用
    Route::get('carousel_search', 'diy.Diy/getPageByCarouselSearch');

    // 添加自定义页面
    Route::post('diy', 'diy.Diy/add');

    // 编辑自定义页面
    Route::put('diy/:id', 'diy.Diy/edit');

    // 自定义页面详情
    Route::get('diy/:id', 'diy.Diy/info');

    // 删除自定义页面
    Route::delete('diy/:id', 'diy.Diy/del');

    Route::get('list', 'diy.Diy/getList');

    // 页面装修列表
    Route::get('decorate', 'diy.Diy/getDecoratePage');

    // 切换模板
    Route::put('change', 'diy.Diy/changeTemplate');

    // 页面初始化数据
    Route::get('init', 'diy.Diy/getPageInit');

    // 获取自定义链接列表
    Route::get('link', 'diy.Diy/getLink');

    Route::get('page_link', 'diy.Diy/pageLink');

    // 设为使用
    Route::put('use/:id', 'diy.Diy/setUse');

    // 获取页面模板
    Route::get('template', 'diy.Diy/getTemplate');

    // 获取模板页面列表
    Route::get('template/pages', 'diy.Diy/getTemplatePages');

    // 自定义路由列表
    Route::get('route', 'diy.DiyRoute/lists');

    // 获取路由列表（存在的应用插件列表）
    Route::get('route/apps', 'diy.DiyRoute/getApps');

    // 获取自定义路由分享内容
    Route::get('route/info', 'diy.DiyRoute/getInfoByName');

    // 编辑自定义路由分享内容
    Route::put('route/share', 'diy.DiyRoute/modifyShare');

    // 编辑自定义页面分享内容
    Route::put('diy/share', 'diy.Diy/modifyShare');

    // 获取模板页面（存在的应用插件列表）
    Route::get('apps', 'diy.Diy/getApps');

    // 复制模版
    Route::post('copy', 'diy.Diy/copy');

    // 获取自定义主题配色
    Route::get('theme', 'diy.Diy/getDiyTheme');

    // 设置主题配色
    Route::post('theme', 'diy.Diy/setDiyTheme');

    // 获取默认主题配色
    Route::get('theme/color', 'diy.Diy/getDefaultThemeColor');

    // 添加自定义主题配色
    Route::post('theme/add', 'diy.Diy/addDiyTheme');

    // 编辑自定义主题配色
    Route::put('theme/edit/:id', 'diy.Diy/editDiyTheme');

    // 删除自定义主题配色
    Route::delete('theme/delete/:id', 'diy.Diy/delDiyTheme');

    /***************************************************** 配置相关 *****************************************************/

    // 底部导航列表
    Route::get('bottom', 'diy.Config/getBottomList');

    // 底部导航配置
    Route::get('bottom/config', 'diy.Config/getBottomConfig');

    // 设置底部导航
    Route::post('bottom', 'diy.Config/setBottomConfig');

    /*****************************************************  万能表单管理 ****************************************************/

    // 万能表单分页列表
    Route::get('form', 'diy.DiyForm/pages');

    // 万能表单列表
    Route::get('form/list', 'diy.DiyForm/lists');

    // 万能表单分页列表（用于弹框选择）
    Route::get('form/select', 'diy.DiyForm/select');

    // 万能表单类型
    Route::get('form/type', 'diy.DiyForm/getFormType');

    // 添加万能表单
    Route::post('form', 'diy.DiyForm/add');

    // 编辑万能表单
    Route::put('form/:id', 'diy.DiyForm/edit');

    // 万能表单详情
    Route::get('form/:id', 'diy.DiyForm/info');

    // 删除万能表单
    Route::put('form/delete', 'diy.DiyForm/del');

    // 获取万能表单微信小程序二维码
    Route::get('form/qrcode', 'diy.DiyForm/getQrcode');

    // 页面初始化数据
    Route::get('form/init', 'diy.DiyForm/getInit');

    // 获取页面模板
    Route::get('form/template', 'diy.DiyForm/getTemplate');

    // 获取万能表单填写配置
    Route::get('form/write/:form_id', 'diy.DiyForm/getWriteConfig');

    // 编辑万能表单填写配置
    Route::put('form/write', 'diy.DiyForm/editWriteConfig');

    // 获取万能表单填写配置
    Route::get('form/submit/:form_id', 'diy.DiyForm/getSubmitConfig');

    // 编辑万能表单填写配置
    Route::put('form/submit', 'diy.DiyForm/editSubmitConfig');

    // 编辑万能表单分享内容
    Route::put('form/share', 'diy.DiyForm/modifyShare');

    // 复制模版
    Route::post('form/copy', 'diy.DiyForm/copy');

    // 修改万能表单状态
    Route::put('form/status', 'diy.DiyForm/modifyStatus');

    //获取填写记录列表
    Route::get('form/records', 'diy.DiyForm/getRecordPages');

    //获取填写记录详情
    Route::get('form/records/:record_id', 'diy.DiyForm/getRecordInfo');

    //删除填写记录
    Route::put('form/records/delete', 'diy.DiyForm/delRecord');

    //获取万能表单字段记录
    Route::get('form/fields/list', 'diy.DiyForm/getFieldsList');

    //获取填表人统计列表
    Route::get('form/records/member/stat', 'diy.DiyForm/memberStatPages');

    //获取字段统计列表
    Route::get('form/records/field/stat', 'diy.DiyForm/fieldStatList');

})->middleware([
    AdminCheckToken::class,
    AdminCheckRole::class,
    AdminLog::class
]);

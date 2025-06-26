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
Route::group('member', function() {
    /***************************************************** 会员管理 ****************************************************/
    //会员列表
    Route::get('member', 'member.Member/lists');
    //会员详情
    Route::get('member/:id', 'member.Member/info');
    //会员添加
    Route::post('member', 'member.Member/add');
    //会员删除
    Route::delete('member/:member_id', 'member.Member/del');
    //会员编码
    Route::get('memberno', 'member.Member/getMemberNo');
    //会员添加
    Route::put('member/:member_id', 'member.Member/edit');//会员添加
    //会员导出
    Route::get('member/export', 'member.Member/export');

    Route::put('member/modify/:member_id/:field', 'member.Member/modify');
    Route::post('member/batch_modify', 'member.Member/batchModify');
    //会员注册方式
    Route::get('registertype', 'member.Member/getMemberRegisterType');
    //会员注册渠道
    Route::get('register/channel', 'member.Member/getMemberRegisterChannelType');
    //会员列表(不分页)
    Route::get('member/list', 'member.Member/getMemberList');
    //获取会员状态枚举
    Route::get('status/list', 'member.Member/getStatusList');
    //会员设置状态
    Route::put('setstatus/:status', 'member.Member/setStatus');
    // 获取会员权益字典
    Route::get('dict/benefits', 'member.Member/getMemberBenefitsDict');
    // 获取会员礼包字典
    Route::get('dict/gift', 'member.Member/getMemberGiftDict');
    // 获取成长值规则字典
    Route::get('dict/growth_rule', 'member.Member/getGrowthRuleDict');
    // 获取积分规则字典
    Route::get('dict/point_rule', 'member.Member/getPointRuleDict');
    /***************************************************** 会员标签 ****************************************************/
    //会员标签列表
    Route::get('label', 'member.MemberLabel/lists');
    //会员标签详情
    Route::get('label/:id', 'member.MemberLabel/info');
    //会员标签添加
    Route::post('label', 'member.MemberLabel/add');
    //会员标签编辑
    Route::put('label/:id', 'member.MemberLabel/edit');
    //会员标签删除
    Route::delete('label/:id', 'member.MemberLabel/del');
    //会员标签
    Route::get('label/all', 'member.MemberLabel/getAll');
    /***************************************************** 会员账户 ****************************************************/
    //会员账户类型变动方式
    Route::get('account/type', 'member.Account/accountType');
    //会员积分流水
    Route::get('account/point', 'member.Account/point');
    //会员余额流水
    Route::get('account/balance', 'member.Account/balance');
    //会员可提现余额流水
    Route::get('account/money', 'member.Account/money');
    //会员成长值流水
    Route::get('account/growth', 'member.Account/growth');
    //会员佣金流水
    Route::get('account/commission', 'member.Account/commission');
    //会员佣金统计
    Route::get('account/sum_commission', 'member.Account/sumCommission');
    //会员积分统计
    Route::get('account/sum_point', 'member.Account/sumPoint');
    //会员积分调整
    Route::post('account/point', 'member.Account/adjustPoint');
    //会员余额调整
    Route::post('account/balance', 'member.Account/adjustBalance');
    //会员账户类型变动方式
    Route::get('account/change_type/:account_type', 'member.Account/changeType');
    //会员账户类型变动方式
    Route::get('account/sum_balance', 'member.Account/sumBalance');
    /***************************************************** 会员相关设置**************************************************/
    //获取注册与登录设置
    Route::get('config/login', 'member.Config/getLoginConfig');
    //更新注册与登录设置
    Route::post('config/login', 'member.Config/setLoginConfig');
    //获取会员提现设置
    Route::get('config/cash_out', 'member.Config/getCashOutConfig');
    //更新提现设置
    Route::post('config/cash_out', 'member.Config/setCashOutConfig');
    //获取成长值规则设置
    Route::get('config/growth_rule', 'member.Config/getGrowthRuleConfig');
    //设置成长值规则
    Route::post('config/growth_rule', 'member.Config/setGrowthRuleConfig');
    //获取积分规则设置
    Route::get('config/point_rule', 'member.Config/getPointRuleConfig');
    //设置积分规则
    Route::post('config/point_rule', 'member.Config/setPointRuleConfig');
    //获取注册与登录设置
    Route::get('config/member', 'member.Config/getMemberConfig');
    //更新注册与登录设置
    Route::post('config/member', 'member.Config/setMemberConfig');
    /***************************************************** 会员体现**************************************************/
    //会员提现列表
    Route::get('cash_out', 'member.CashOut/lists');
    //会员提现详情
    Route::get('cash_out/:id', 'member.CashOut/info');
    //会员提现审核
    Route::put('cash_out/audit/:id/:action', 'member.CashOut/audit');
    //会员提现备注
    Route::put('cash_out/remark/:id', 'member.CashOut/remark');
    //取消会员提现
    Route::put('cash_out/cancel/:id', 'member.CashOut/cancel');
    //校验会员提现转账状态
    Route::put('cash_out/check/:id', 'member.CashOut/checkTransferStatus');
    //转账方式
    Route::get('cash_out/transfertype', 'member.CashOut/getTransferType');
    //转账方式
    Route::put('cash_out/transfer/:id', 'member.CashOut/transfer');
    //提现状态
    Route::get('cash_out/status', 'member.CashOut/getStatusList');
    //提现统计信息
    Route::get('cash_out/stat', 'member.CashOut/stat');
    /***************************************************** 会员等级 ****************************************************/
    //会员等级分页列表
    Route::get('level', 'member.MemberLevel/pages');
    //会员等级详情
    Route::get('level/:id', 'member.MemberLevel/info');
    //会员等级添加
    Route::post('level', 'member.MemberLevel/add');
    //会员等级编辑
    Route::put('level/:id', 'member.MemberLevel/edit');
    //会员等级删除
    Route::delete('level/:id', 'member.MemberLevel/del');
    //全部会员等级
    Route::get('level/all', 'member.MemberLevel/getAll');
    // 获取会员权益内容
    Route::get('benefits/content', 'member.Member/getMemberBenefitsContent');
    // 获取会员礼包内容
    Route::get('gifts/content', 'member.Member/getMemberGiftsContent');
    /***************************************************** 会员签到 ****************************************************/
    //签到设置
    Route::put('sign/config', 'member.MemberSign/setSign');
    //签到设置
    Route::get('sign/config', 'member.MemberSign/getSign');
    //签到记录
    Route::get('sign', 'member.MemberSign/lists');
    /***************************************************** 会员地址 ****************************************************/
    //会员收货地址列表
    Route::get('address', 'member.Address/lists');
    //会员收货地址详情
    Route::get('address/:id', 'member.Address/info');
    //添加会员收货地址
    Route::post('address', 'member.Address/add');
    //编辑会员收货地址
    Route::put('address/:id', 'member.Address/edit');
})->middleware([
    AdminCheckToken::class,
    AdminCheckRole::class,
    AdminLog::class
]);

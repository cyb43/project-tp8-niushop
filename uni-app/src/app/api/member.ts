import request from '@/utils/request'

export function getMemberInfo() {
    return request.get('member/member')
}

/**
 * 获取积分流水
 */
export function getPointList(data: AnyObject) {
    return request.get('member/account/point', data)
}

/**
 * 获取积分来源用途
 */
export function getPointType(account_type: string) {
    return request.get(`member/account/fromtype/${ account_type }`)
}

/**
 * 获取不可提现余额流水
 */
export function getBalanceList(data: AnyObject) {
    return request.get('member/account/balance', data)
}

/**
 * 获取余额流水,条件获取
 */
export function getBalanceListAll(data: AnyObject) {
    return request.get('member/account/balance_list', data)
}

/**
 * 获取可提现余额流水
 */
export function getMoneyList(data: AnyObject) {
    return request.get('member/account/money', data)
}

/**
 * 会员信息修改
 */
export function modifyMember(data: AnyObject) {
    return request.put(`member/modify/${ data.field }`, data, { showErrorMessage: true })
}

/**
 * 登录会员绑定手机号
 */
export function bindMobile(data: AnyObject) {
    if (uni.getStorageSync('pid')) {
        data.pid = uni.getStorageSync('pid');
    }
    return request.put('member/mobile', data, { showErrorMessage: true })
}

/**
 * 获取手机号
 */
export function getMobile(data: AnyObject) {
    if (uni.getStorageSync('pid')) {
        data.pid = uni.getStorageSync('pid');
    }
    return request.put('member/getmobile', data, { showErrorMessage: true })
}

/**
 * 提现转账方式
 */
export function cashOutTransferType() {
    return request.get('member/cash_out/transfertype')
}

/**
 * 提现配置
 */
export function cashOutConfig() {
    return request.get('member/cash_out/config')
}

/**
 * 申请余额提现
 */
export function cashOutApply(data: AnyObject) {
    return request.post('member/cash_out/apply', data, { showSuccessMessage: false, showErrorMessage: true })
}

/**
 * 获取提现账户信息
 */
export function getCashoutAccountInfo(data: AnyObject) {
    return request.get(`member/cashout_account/${ data.account_id }`, {})
}

/**
 * 获取首条提现账户信息
 */
export function getFirstCashOutAccountInfo(data: AnyObject) {
    return request.get('member/cashout_account/firstinfo', data)
}

/**
 * 获取提现账户列表
 */
export function getCashOutAccountList(data: AnyObject) {
    return request.get(`member/cashout_account`, data)
}

/**
 * 获取提现记录列表
 */
export function getCashOutList(data: AnyObject) {
    return request.get(`member/cash_out`, data)
}

/**
 * 获取提现记录详情
 */
export function getCashOutDetail(id: number) {
    return request.get(`member/cash_out/${ id }`)
}

/**
 * 获取提现转账
 */
export function getCashOutTransfer(data: AnyObject) {
    return request.post(`member/cash_out/transfer/${ data.id }`, data)
}

/**
 * 添加提现账户
 */
export function addCashoutAccount(data: AnyObject) {
    return request.post('member/cashout_account', data, { showSuccessMessage: true, showErrorMessage: true })
}

/**
 * 添加提现账户
 */
export function editCashoutAccount(data: AnyObject) {
    return request.put(`member/cashout_account/${ data.account_id }`, data, {
        showSuccessMessage: true,
        showErrorMessage: true
    })
}

/**
 * 删除提现账户
 */
export function deleteCashoutAccount(accountId: number) {
    return request.delete(`member/cashout_account/${ accountId }`, { showSuccessMessage: true, showErrorMessage: true })
}

/**
 * 会员取消提现
 * @param params
 */
export function memberCancel(params: Record<string, any>) {
    return request.put(`member/cash_out/cancel/${params.id}`, params, { showSuccessMessage: true,showErrorMessage: true })
}
/**
 * 佣金账户流水
 */
export function getMemberCommission(data: AnyObject) {
    return request.get(`member/account/commission`, data)
}

/**
 * 佣金列表
 */
export function getCommissionList(data: AnyObject) {
    return request.get(`member/account/commission`, data)
}

/**
 * 获取账号变动类型
 */
export function getAccountType(params: Record<string, any>) {
    return request.get(`member/account/fromtype/${ params.account_type }`)
}

/**
 * 获取会员收货地址列表
 * @param params
 * @returns
 */
export function getAddressList(params: Record<string, any>) {
    return request.get(`member/address`, params)
}

/**
 * 获取会员收货地址详情
 * @param id 会员收货地址id
 * @returns
 */
export function getAddressInfo(id: number) {
    return request.get(`member/address/${ id }`);
}

/**
 * 添加会员收货地址
 * @param params
 * @returns
 */
export function addAddress(params: Record<string, any>) {
    return request.post('member/address', params, { showErrorMessage: true, showSuccessMessage: true })
}

/**
 * 编辑会员收货地址
 * @param params
 * @returns
 */
export function editAddress(params: Record<string, any>) {
    return request.put(`member/address/${ params.id }`, params, { showErrorMessage: true, showSuccessMessage: true })
}

/**
 * 删除会员收货地址
 * @param id
 * @returns
 */
export function deleteAddress(id: number) {
    return request.delete(`member/address/${ id }`, { showErrorMessage: true, showSuccessMessage: true })
}

/**
 * 获取会员等级
 */
export function getMemberLevel() {
    return request.get(`member/level`);
}

/**
 * 获取成长值任务
 */
export function getTaskGrowth() {
    return request.get(`task/growth`);
}

/**
 * 获取签到日期
 */
export function getSignInfo(data: AnyObject) {
    return request.get(`member/sign/info/${ data.year }/${ data.month }`, {})
}

/**
 * 获取日签到奖励
 */
export function getDayPack(data: AnyObject) {
    return request.get(`member/sign/award/${ data.year }/${ data.month }/${ data.day }`)
}

/**
 * 获取签到设置
 */
export function getSignConfig() {
    return request.get(`member/sign/config`)
}

/**
 * 点击签到
 * @returns
 */
export function setSign() {
    return request.post('member/sign')
}

/**
 * 获取个人积分
 */
export function getMemberAccountPointcount() {
    return request.get(`member/account/pointcount`)
}

/**
 * 获取积分任务
 */
export function getTaskPoint() {
    return request.get(`task/point`)
}

/**
 * 充值配置
 */
export function rechargeConfig() {
    return request.get('recharge/config')
}

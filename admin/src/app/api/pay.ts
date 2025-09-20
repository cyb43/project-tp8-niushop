import request from '@/utils/request'

/***************************************************** 账单列表 **************************************************/

/**
 * 获取账单列表
 * @param params
 * @returns
 */
export function getAccountList(params: Record<string, any>) {
    return request.get(`pay/account`, {params})
}

/**
 * 获取账单详情
 * @param id
 */
export function getAccountInfo(id: number) {
    return request.get(`pay/account/${id}`)
}

/**
 * 获取账单统计
 * @returns
 */
export function getAccountStat() {
    return request.get(`pay/account/stat`)
}

/**
 * 获取账单类型
 * @returns
 */
export function getAccountType() {
    return request.get(`pay/account/type`)
}

/***************************************************** 退款信息 **************************************************/

/**
 * 退款列表
 * @param params
 * @returns
 */
export function getPayRefundPages(params: Record<string, any>) {
    return request.get(`pay/refund`, {params})
}

/**
 * 获取退款详情
 * @param refund_no
 */
export function getPayRefundInfo(refund_no: string) {
    return request.get(`pay/refund/${refund_no}`)
}

/**
 * 获取退款状态字典
 */
export function getRefundStatus() {
    return request.get(`pay/refund/status`)
}

/**
 * 退款方式
 */
export function getRefundType() {
    return request.get(`pay/refund/type`)
}

/**
 * 退款转账
 * @param params
 */
export function getRefundTransfer(params: Record<string, any>) {
    return request.post(`pay/refund/transfer`, params, {showSuccessMessage: true})
}

/**
 * 全部支付方式
 */
export function getAllPayType() {
    return request.get(`pay/type/all`)
}

/**
 * 支付列表
 */
export function getPayList() {
    return request.get(`pay/type/list`)
}

/**
 * 支付
 */
export function pay(params: Record<string, any>) {
    return request.post(`pay`, params)
}

/**
 * 帮付
 * @param tradeType
 * @param tradeId
 * @param channel
 */
export function getFriendsPay(tradeType : string, tradeId : number, channel: string) {
   return request.get(`pay/friendspay/info/${tradeType}/${tradeId}/${channel}`, { showErrorMessage: false })
}


/**
 *转账场景
 */
export function getTransferScene() {
    return request.get(`pay/transfer_scene`)
}

/**
 * 更改场景id
 */
export function setSceneId(params: Record<string, any>) {
    return request.post(`pay/transfer_scene/set_scene_id/${params.scene}`, params, { showSuccessMessage: true })
}

/**
 * 设置业务场景配置
 */
export function setTradeScene(params: Record<string, any>) {
    return request.post(`pay/transfer_scene/set_trade_scene/${params.type}`, params)
}
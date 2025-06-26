import request from '@/utils/request'

/***************************************************** 订单列表 ****************************************************/
/**
 * 获取订单设置
 */
export function getShopOrderConfig() {
    return request.get(`shop/order/config`)
}

/**
 * 获取订单状态列表
 */
export function getShopOrderStatus() {
    return request.get(`shop/order/status`)
}

/**
 * 获取订单列表
 */
export function getShopOrder(params: Record<string, any>) {
    return request.get(`shop/order`, params)
}

/**
 * 获取订单角标数据
 */
export function getShopOrderNum() {
    return request.get(`shop/order/num`)
}

/**
 * 获取订单详情
 */
export function getShopOrderDetail(order_id: any) {
    return request.get(`shop/order/${ order_id }`)
}

/**
 * 关闭订单
 */
export function orderClose(order_id: number) {
    return request.put(`shop/order/close/${ order_id }`)
}

/**
 * 订单完成
 */
export function orderFinish(order_id: number) {
    return request.put(`shop/order/finish/${ order_id }`)
}

/**
 * 订单创建计算
 */
export function orderCreateCalculate(params: Record<string, any>) {
    return request.get('shop/order_create/calculate', params)
}

/**
 * 订单创建
 */
export function orderCreate(params: Record<string, any>) {
    return request.post('shop/order_create/create', params)
}

/**
 * 查询订单可用优惠券
 */
export function orderCoupon(params: Record<string, any>) {
    return request.get('shop/order_create/coupon', params)
}

/**
 * 查询自提点
 */
export function getStoreList(params: Record<string, any>) {
    return request.get('shop/order_create/store', params)
}

/**
 * 查询物流信息
 */
export function getMaterialflowList(params: Record<string, any>) {
    return request.get('shop/order/logistics', params)
}

/**
 * 查询同城配送预约配置
 */
export function getLocal() {
    return request.get('shop/order_create/local')
}

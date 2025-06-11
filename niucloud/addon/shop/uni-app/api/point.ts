import request from '@/utils/request'

/**
 * 获取个人积分信息
 * @returns
 */
export function getExchangePoint() {
    return request.get(`shop/exchange/point`);
}

/**
 * 获取积分商城推荐列表
 * @returns
 */
export function getExchangeComponentsList(params: Record<string, any>) {
    return request.get(`shop/exchange/components`, params)
}

/**
 * 获取积分商城列表
 * @returns
 */
export function getExchangeGoodsList(params: Record<string, any>) {
    return request.get(`shop/exchange`, params)
}

/**
 * 获取积分商品详情
 */
export function getExchangeGoodsDetail(id: any) {
    return request.get(`shop/exchange/goods/${ id }`)
}

/**
 * 订单创建计算
 */
export function orderCreateCalculate(params: Record<string, any>) {
    return request.get('shop/exchange_order/calculate', params)
}

/**
 * 订单创建
 */
export function orderCreate(params: Record<string, any>) {
    return request.post('shop/exchange_order/create', params)
}
import request from '@/utils/request'

/**
 * 获取顺手买配置
 */
export function getShopImpulseBuyConfig() {
    return request.get(`shop_impulse_buy/config`)
}

/**
 * 获取顺手买商品列表
 */
export function getShopImpulseBuyList(params: Record<string, any>) {
    return request.get(`shop_impulse_buy/list`, params)
}

/**
 * 获取顺手买商品详情
 */
export function getShopImpulseBuyDetail(params: Record<string, any>) {
    return request.get(`shop_impulse_buy/detail/${params.impulse_buy_goods_id}`, params)
}
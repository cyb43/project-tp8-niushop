import request from '@/utils/request'

/**
 * 添加购物车
 */
export function addCart(data: AnyObject) {
    return request.post(`shop/cart`, data)
}

/**
 * 编辑购物车数量
 */
export function editCart(data: AnyObject) {
    return request.put(`shop/cart`, data)
}

/**
 * 删除购物车
 */
export function deleteCart(data: AnyObject) {
    return request.put(`shop/cart/delete`, data)
}

/**
 * 清空购物车
 */
export function clearCart() {
    return request.delete(`shop/cart/clear`)
}

/**
 * 获取购物车列表
 */
export function getCartList(params: Record<string, any>) {
    return request.get(`shop/cart`, params)
}

/**
 * 获取购物车商品列表
 */
export function getCartGoodsList(params: Record<string, any>) {
    return request.get(`shop/cart/goods`, params)
}

/**
 * 获取购物车数量
 */
export function getCartSum(params: Record<string, any>) {
    return request.get(`shop/cart/sum`, params)
}

/**
 * 购物车计算
 */
export function getCartCalculate(params: Record<string, any>) {
    return request.get(`shop/cart/calculate`, params)
}

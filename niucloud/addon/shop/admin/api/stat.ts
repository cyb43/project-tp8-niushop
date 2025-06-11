import request from '@/utils/request'

/**
 * 获取商品统计基本信息
 * @returns
 */
export function getGoodsStatisticsBasic(params: Record<string, any>) {
    return request.get('shop/goods/statistics/basic', { params })
}

/**
 * 获取商品排行图表统计信息
 * @returns
 */
export function getGoodsStatisticsTrend(params: Record<string, any>) {
    return request.get('shop/goods/statistics/trend', { params })
}

/**
 * 获取商品排行榜统计类型
 */
export function getGoodsStatisticsType() {
    return request.get(`shop/goods/statistics/type`)
}

/**
 * 获取商品排行信息
 * @returns
 */
export function getGoodsStatisticsRank(params: Record<string, any>) {
    return request.get('shop/goods/statistics/rank', { params })
}

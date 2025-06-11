import request from '@/utils/request'

/**
 * 营销中心
 * @param params
 * @returns
 */
export function getMarketingIndex(params: Record<string, any>) {
    return request.get(`shop/marketing`, { params })
}

/**
 * 获取商品分类列表
 * @param params
 * @returns
 */
export function getGoodsCategoryList(params: Record<string, any>) {
    return request.get(`shop/goods/coupon/init`, { params })
}

/**
 * 添加优惠券
 * @param params
 * @returns
 */
export function addCoupon(params: Record<string, any>) {
    return request.post(`shop/goods/coupon`, params, { showErrorMessage: true, showSuccessMessage: true })
}

/**
 * 获取优惠券状态列表
 * @returns
 */
export function getCouponStatusList() {
    return request.get(`shop/goods/coupon/status`)
}

/**
 * 获取优惠券列表
 * @param params
 * @returns
 */
export function getCouponList(params: Record<string, any>) {
    return request.get(`shop/goods/coupon`, { params })
}

/**
 * 获取优惠券列表
 * @param params
 * @returns
 */
export function getCouponSelectList(params: Record<string, any>) {
    return request.get(`shop/goods/coupon/select`, { params })
}

/**
 * 获取商品分类列表
 * @param params
 * @returns
 */
export function getSelectedCouponList(params: Record<string, any>) {
    return request.get(`shop/goods/coupon/selected`, { params })
}

/**
 * 获取优惠券领取记录
 * @param params
 * @returns
 */
export function getCouponRecords(params: Record<string, any>) {
    return request.get(`shop/goods/coupon/records`, { params });
}

/**
 * 获取优惠券详情
 * @param id
 * @returns
 */
export function getCouponInfo(id: number) {
    return request.get(`shop/goods/coupon/detail/${ id }`);
}

/**
 * 优惠券状态变更
 * @param params
 * @returns
 */
export function editCouponStatus(params: Record<string, any>) {
    return request.put(`shop/goods/coupon/setstatus/${ params.status }`, params, { showSuccessMessage: true })
}

/**
 * 编辑优惠券
 * @param params
 * @returns
 */
export function editCoupon(params: Record<string, any>) {
    return request.put(`shop/goods/coupon/edit/${ params.id }`, params, {
        showErrorMessage: true,
        showSuccessMessage: true
    })
}

/**
 * 删除优惠券
 * @param params
 * @returns
 */
export function deleteCoupon(params: Record<string, any>) {
    return request.post(`shop/goods/coupon/delete`, params, { showSuccessMessage: true })
}

/**
 * 关闭优惠券
 * @param params
 * @returns
 */
export function closeCoupon(params: Record<string, any>) {
    return request.put(`shop/goods/coupon/invalid`, params, { showSuccessMessage: true })
}

/**
 * 发送优惠券
 * @param params
 * @returns
 */
export function sendCoupon(params: Record<string, any>) {
    return request.post(`shop/goods/coupon/send/${ params.id }`, params, {
        showErrorMessage: true,
        showSuccessMessage: true
    })
}

/**
 * 获取优惠券发放记录
 * @param params
 * @returns
 */
export function getCouponSendRecords(params: Record<string, any>) {
    return request.get(`shop/goods/coupon/send/pages/${ params.id }`, { params });
}

/**
 * 优惠券init
 * @returns
 */
export function initCoupon() {
    return request.get(`shop/goods/coupon/send/init`)
}

/************ 限时折扣 ****************/
/**
 * 获取限时折扣列表
 * @param params
 * @returns
 */
export function getActiveDiscountPageList(params: Record<string, any>) {
    return request.get(`shop/active/discount`, { params })
}

/**
 * 获取限时折扣状态列表
 * @returns
 */
export function getActiveDiscountStatusList() {
    return request.get(`shop/active/status`)
}


/**
 * 获取指定活动的折扣信息。
 *
 * @param active_id 获取基本信息
 * @returns
 */
export function getBasicInformation(active_id: number) {
    return request.get(`shop/active/discount/info/${ active_id }`);
}

/**
 * 获取限时折扣详情 编辑
 * @param active_id
 * @returns
 */
export function getActiveDiscountInfo(active_id: number) {
    return request.get(`shop/active/discount/${ active_id }`);
}

/**
 * 添加限时折扣
 * @param params
 * @returns
 */
export function addActiveDiscount(params: Record<string, any>) {
    return request.post('shop/active/discount', params, { showErrorMessage: true, showSuccessMessage: true })
}

/**
 * 编辑限时折扣
 * @param params
 * @returns
 */
export function editActiveDiscount(params: Record<string, any>) {
    return request.put(`shop/active/discount/${ params.discount_id }`, params, {
        showErrorMessage: true,
        showSuccessMessage: true
    })
}

/**
 * 关闭限时折扣
 * @param active_id
 * @returns
 */
export function closeActiveDiscount(active_id: number) {
    return request.put(`shop/active/discount/close/${ active_id }`, {}, {
        showErrorMessage: true,
        showSuccessMessage: true
    })
}

/**
 * 删除限时折扣
 * @param active_id
 * @returns
 */
export function deleteActiveDiscount(active_id: number) {
    return request.delete(`shop/active/discount/${ active_id }`, { showSuccessMessage: true })
}

/**
 * 获取参与限时折扣商品列表
 * @param params
 * @returns
 */
export function getActiveDiscountGoodsPageList(params: Record<string, any>) {
    return request.get(`shop/active/discount/goods/${ params.active_id }`, { params })
}

/**
 * 获取参与限时折扣订单列表
 * @param params
 * @returns
 */
export function getActiveDiscountOrderPageList(params: Record<string, any>) {
    return request.get(`shop/active/discount/order/${ params.active_id }`, { params })
}

/**
 * 限时折扣商品校验
 * @param params
 * @returns
 */
export function goodscheck(params: Record<string, any>) {
    return request.post(`shop/active/discount/goods/check`, params, {})
}

/**
 * 获取参与限时折扣会员列表
 * @param params
 * @returns
 */
export function getActiveDiscountMemberPageList(params: Record<string, any>) {
    return request.get(`shop/active/discount/member/${ params.active_id }`, { params })
}

/**
 * 获取活动专题
 * @returns
 */
export function getActiveDiscountConfig() {
    return request.get(`shop/active/discount/config`);
}

/**
 * 编辑活动专题
 * @param params
 * @returns
 */
export function editActiveDiscountConfig(params: Record<string, any>) {
    return request.put(`shop/active/discount/config`, params, {
        showErrorMessage: true,
        showSuccessMessage: true
    })
}

/********** 积分商品 ***********/

/**
 * 获取积分商品列表
 * @param params
 * @returns
 */
export function getActiveExchangePageList(params: Record<string, any>) {
    return request.get(`shop/active/exchange`, { params })
}

/**
 * 获取积分商品分页列表（用于弹框选择）
 * @param params
 * @returns
 */
export function getActiveExchangeSelectPageList(params: Record<string, any>) {
    return request.get(`shop/active/exchange/select`, { params })
}

/**
 * 获取积分商品详情
 * @param id id
 * @returns
 */
export function getActiveExchangeInfo(id: number) {
    return request.get(`shop/active/exchange/${ id }`);
}

/**
 * 添加积分商品
 * @param params
 * @returns
 */
export function addActiveExchange(params: Record<string, any>) {
    return request.post('shop/active/exchange', params, { showErrorMessage: true, showSuccessMessage: true })
}

/**
 * 编辑积分商品
 * @param params
 * @returns
 */
export function editActiveExchange(params: Record<string, any>) {
    return request.put(`shop/active/exchange/${ params.id }`, params, {
        showErrorMessage: true,
        showSuccessMessage: true
    })
}

/**
 * 编辑积分商品状态
 * @param params
 * @returns
 */
export function editActiveExchangeStatus(params: Record<string, any>) {
    return request.put(`shop/active/exchange/status/${ params.id }`, params, {
        showErrorMessage: true,
        showSuccessMessage: true
    })
}

/**
 * 删除积分商品
 * @param id
 * @returns
 */
export function deleteActiveExchange(id: number) {
    return request.delete(`shop/active/exchange/${ id }`, { showSuccessMessage: true })
}

/**
 * 获取积分商品状态列表
 * @returns
 */
export function getActiveExchangeStatus() {
    return request.get(`shop/active/exchange/status`)
}

/************ 新人专享 ****************/

/**
 * 获取新人专享设置
 * @returns
 */
export function getActiveNewcomerConfig() {
    return request.get(`shop/active/newcomer/config`);
}

/**
 * 编辑新人专享设置
 * @param params
 * @returns
 */
export function editActiveNewcomerConfig(params: Record<string, any>) {
    return request.put(`shop/active/newcomer/config`, params, {
        showErrorMessage: true,
        showSuccessMessage: true
    })
}

/**
 *  获取新人专享列表【首页组件-分页】
 * @param params
 * @returns
 */
export function getNewcomerGoodsList(params: Record<string, any>) {
    return request.get('shop/active/newcomer/goods/select', { params })
}

/**
 *  新人专享 - 已选商品列表
 * @param params
 * @returns
 */
export function getNewcomerSelectGoodsList(params: Record<string, any>) {
    return request.get('shop/active/newcomer/goods/selectgoodssku', { params })
}

/************ 商品榜单 ****************/

/**
 * 设置排行榜配置
 * @param params
 * @returns
 */
export function setRankConfig(params: Record<string, any>) {
    return request.post('shop/good/rank/config', params, { showErrorMessage: true, showSuccessMessage: true })
}

/**
 * 获取排行榜配置
 * @returns
 */
export function getRankConfig() {
    return request.get(`shop/good/rank/config`)
}

/**
 * 获取排行榜列表
 * @param params
 * @returns
 */
export function getRankPageList(params: Record<string, any>) {
    return request.get(`shop/good/rank`, { params })
}

/**
 * 获取排行榜选项列表
 * @returns
 */
export function optionData() {
    return request.get(`shop/good/rank/dict`)
}

/**
 * 获取排行榜详情
 * @param rank_id
 * @returns
 */
export function getRankInfo(rank_id: number) {
    return request.get(`shop/good/rank/${ rank_id }`);
}

/**
 * 添加排行榜
 * @param params
 * @returns
 */
export function addGoodRank(params: Record<string, any>) {
    return request.post('shop/good/rank', params, { showErrorMessage: true, showSuccessMessage: true })
}

/**
 * 编辑排行榜
 * @param params
 * @returns
 */
export function editGoodRank(params: Record<string, any>) {
    return request.put(`shop/good/rank/${ params.id }`, params, {
        showErrorMessage: true,
        showSuccessMessage: true
    })
}

/**
 * 排行榜状态
 * @param params
 * @returns
 */
export function editRankStatus(params: Record<string, any>) {
    return request.put(`shop/goods/rank/status`, params, {
        showErrorMessage: true,
        showSuccessMessage: true
    })
}

/**
 * 删除排行榜
 * @param id
 * @returns
 */
export function deleteGoodRank(id: number) {
    return request.delete(`shop/good/rank/${ id }`, { showSuccessMessage: true })
}

/**
 * 批量删除排行榜
 * @param params
 * @returns
 */
export function batchDelete(params: Record<string, any>) {
    return request.put(`shop/good/rank/batchDelete`, params, { showSuccessMessage: true })
}

/**
 * 排行榜修改排序
 * @param params
 * @returns
 */
export function modifyGoodsRankSort(params: Record<string, any>) {
    return request.put(`shop/good/rank/sort`, params, { showSuccessMessage: true })
}

/**
 * 商品排行榜列表
 * @param params
 * @returns
 */
export function getSelectRankPageList(params: Record<string, any>) {
    return request.get(`shop/good/rank/select`, { params })
}

/************ 满减 ****************/

/**
 * 获取满减列表
 * @param params
 * @returns
 */
export function getManjianList(params: Record<string, any>) {
    return request.get(`shop/manjian`, { params })
}

/**
 * 获取满减状态
 * @returns
 */
export function getManjianStatusList() {
    return request.get(`shop/manjian/status`)
}

/**
 * 添加满减
 * @param params
 * @returns
 */
export function addManjian(params: Record<string, any>) {
    return request.post('shop/manjian', params)
}

/**
 * 编辑满减
 * @param params
 * @returns
 */
export function editManjian(params: Record<string, any>) {
    return request.put(`shop/manjian/${ params.id }`, params)
}

/**
 * 获取满减详情
 * @param params
 * @returns
 */
export function getManjianInfo(params: Record<string, any>) {
    return request.get(`shop/manjian/init`, { params });
}

/**
 * 检查商品
 * @param params
 * @returns
 */
export function goodsCheck(params: Record<string, any>) {
    return request.post('shop/manjian/goods/check', params)
}

/**
 * 获取满减详情会员列表
 * @param params
 * @returns
 */
export function getManjianMemberPageList(params: Record<string, any>) {
    return request.get(`shop/manjian/member/${ params.id }`, { params })
}

/**
 * 关闭满减
 * @param manjian_id
 * @returns
 */
export function closeManjian(manjian_id: number) {
    return request.put(`shop/manjian/close/${ manjian_id }`, {}, {
        showErrorMessage: true,
        showSuccessMessage: true
    })
}

/**
 * 获取商品选择列表,代客下单使用
 * @param params
 * @returns
 */
export function getGoodsSelectByReplaceBuy(params: Record<string, any>) {
    return request.get(`shop/goods/buy/goods/select`, { params })
}

/**
 * 获取选中商品选择列表,代客下单使用
 * @param params
 * @returns
 */
export function getGoodsSelectedByReplaceBuy(params: Record<string, any>) {
    return request.get(`shop/goods/buy/goods/selected`, { params })
}

/**
 * 获取商品规格信息
 * @param params
 * @returns
 */
export function getGoodsSkuInfo(params: Record<string, any>) {
    return request.get(`shop/goods/buy/sku/select`, { params })
}

/*
 * 删除满减
 * @param manjian_id
 * @returns
 */
export function deleteManjian(manjian_id: number) {
    return request.delete(`shop/manjian/${ manjian_id }`, { showSuccessMessage: true })
}

/**
 * 批量删除满减
 * @param params
 * @returns
 */
export function batchDeleteManjian(params: Record<string, any>) {
    return request.put(`shop/manjian/goods/batchDelete`, params, { showSuccessMessage: true })
}

/**
 * 批量关闭满减
 * @param params
 * @returns
 */
export function batchCloseMajian(params: Record<string, any>) {
    return request.put(`shop/manjian/goods/batchClose`, params, { showSuccessMessage: true })
}


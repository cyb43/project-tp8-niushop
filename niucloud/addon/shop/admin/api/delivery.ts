import request from '@/utils/request'

/*********************************  物流公司  ***************************************/
/**
 * 获取物流公司分页列表
 * @param params
 * @returns
 */
export function getCompanyPageList(params: Record<string, any>) {
    return request.get(`shop/delivery/company`, { params })
}

/**
 * 获取物流公司列表
 * @param params
 * @returns
 */
export function getCompanyList(params: Record<string, any>) {
    return request.get(`shop/delivery/company/list`, { params })
}

/**
 * 获取物流公司详情
 * @param company_id 物流公司company_id
 * @returns
 */
export function getCompanyInfo(company_id: number) {
    return request.get(`shop/delivery/company/${ company_id }`);
}

/**
 * 添加物流公司
 * @param params
 * @returns
 */
export function addCompany(params: Record<string, any>) {
    return request.post('shop/delivery/company', params, { showErrorMessage: true, showSuccessMessage: true })
}

/**
 * 编辑物流公司
 * @param params
 * @returns
 */
export function editCompany(params: Record<string, any>) {
    return request.put(`shop/delivery/company/${ params.company_id }`, params, {
        showErrorMessage: true,
        showSuccessMessage: true
    })
}

/**
 * 删除物流公司
 * @param company_id
 * @returns
 */
export function deleteCompany(company_id: number) {
    return request.delete(`shop/delivery/company/${ company_id }`, { showErrorMessage: true, showSuccessMessage: true })
}

/*********************************  运费模版  ***************************************/
/**
 * 获取运费模版分页列表
 * @param params
 * @returns
 */
export function getShippingTemplatePageList(params: Record<string, any>) {
    return request.get(`shop/shipping/template`, { params })
}

/**
 * 获取运费模版列表
 * @param params
 * @returns
 */
export function getShippingTemplateList(params: Record<string, any>) {
    return request.get(`shop/shipping/template/list`, { params })
}

/**
 * 获取物运费模版详情
 * @param template_id 运费模版template_id
 * @returns
 */
export function getShippingTemplateInfo(template_id: number) {
    return request.get(`shop/shipping/template/${ template_id }`);
}

/**
 * 添加运费模版
 * @param params
 * @returns
 */
export function addShippingTemplate(params: Record<string, any>) {
    return request.post('shop/shipping/template', params, { showErrorMessage: true, showSuccessMessage: true })
}

/**
 * 编辑运费模版
 * @param params
 * @returns
 */
export function editShippingTemplate(params: Record<string, any>) {
    return request.put(`shop/shipping/template/${ params.template_id }`, params, {
        showErrorMessage: true,
        showSuccessMessage: true
    })
}

/**
 * 删除运费模版
 * @param template_id
 * @returns
 */
export function deleteShippingTemplate(template_id: number) {
    return request.delete(`shop/shipping/template/${ template_id }`, {
        showErrorMessage: true,
        showSuccessMessage: true
    })
}

/*********************************  自提门店  ***************************************/
/**
 * 获取自提门店列表
 * @param params
 * @returns
 */
export function getStoreList(params: Record<string, any>) {
    return request.get(`shop/delivery/store`, { params })
}

/**
 * 获取自提门店详情
 * @param store_id 自提门店store_id
 * @returns
 */
export function getStoreInfo(store_id: number) {
    return request.get(`shop/delivery/store/${ store_id }`);
}

/**
 * 获取初始化页面信息
 * @returns
 */
export function getStoreInit() {
    return request.get(`shop/delivery/store/init`)
}

/**
 * 添加自提门店
 * @param params
 * @returns
 */
export function addStore(params: Record<string, any>) {
    return request.post('shop/delivery/store', params, { showErrorMessage: true, showSuccessMessage: true })
}

/**
 * 编辑自提门店
 * @param params
 * @returns
 */
export function editStore(params: Record<string, any>) {
    return request.put(`shop/delivery/store/${ params.store_id }`, params, {
        showErrorMessage: true,
        showSuccessMessage: true
    })
}

/**
 * 删除自提门店
 * @param store_id
 * @returns
 */
export function deleteStore(store_id: number) {
    return request.delete(`shop/delivery/store/${ store_id }`, { showErrorMessage: true, showSuccessMessage: true })
}

/*********************************  物流查询  ***************************************/
/**
 * 设置 物流查询配置
 * @param params
 * @returns
 */
export function setDeliverySearch(params: Record<string, any>) {
    return request.post('shop/delivery/search', params, { showErrorMessage: true, showSuccessMessage: true })
}

/**
 * 获取 物流查询配置
 * @returns
 */
export function getDeliverySearch() {
    return request.get('shop/delivery/search')
}

/**
 * 获取 物流管理配置
 * @returns
 */
export function getShopDeliveryList() {
    return request.get('shop/delivery/deliveryList')
}

/**
 * 物流管理设置
 * @param params
 * @returns
 */
export function setShopDeliveryConfig(params: Record<string, any>) {
    return request.put(`shop/delivery/setConfig`, params, { showSuccessMessage: true })
}

/**
 * 获取 配送员列表
 * @param params
 * @returns
 */
export function getShopDelivery(params: Record<string, any>) {
    return request.get('shop/delivery/staff', { params })
}

/**
 * 获取配送员信息
 * @param staff_id
 * @returns
 */
export function getShopDeliverInfo(staff_id: number) {
    return request.get(`shop/delivery/staff/${ staff_id }`);
}

/**
 * 添加配送员
 * @param params
 * @returns
 */
export function addShopDeliver(params: Record<string, any>) {
    return request.post('shop/delivery/staff', params, { showSuccessMessage: true })
}

/**
 * 编辑配送员
 * @param params
 * @returns
 */
export function editShopDeliver(params: Record<string, any>) {
    return request.put(`shop/delivery/staff/${ params.deliver_id }`, params, { showSuccessMessage: true })
}

/**
 * 删除配送员
 * @param staff_id
 * @returns
 */
export function deleteShopDeliver(staff_id: number) {
    return request.delete(`shop/delivery/staff/${ staff_id }`)
}

/**
 * 获取同城配送设置
 * @returns
 */
export function getLocal() {
    return request.get('shop/local');
}

/**
 * 设置同城配送
 * @param params
 * @returns
 */
export function setLocal(params: Record<string, any>) {
    return request.put('shop/local', params, { showSuccessMessage: true })
}

/**
 * 三方配送
 * @returns
 */
export function getThird() {
    return request.get('shop/third/init')
}

import request from '@/utils/request'

/**
 * 获取微信配置
 * @returns
 */
export function getWechatConfig() {
    return request.get('wechat/config')
}

/**
 * 微信配置所需的静态信息
 */
export function getWechatStatic() {
    return request.get('wechat/static');
}

/**
 * 编辑微信配置
 * @param params
 * @returns
 */
export function editWechatConfig(params: Record<string, any>) {
    return request.put('wechat/config', params, { showSuccessMessage: true })
}

/**
 * 获取微信菜单
 * @returns
 */
export function getWechatMenu() {
    return request.get('wechat/menu')
}

/**
 * 编辑微信菜单
 * @param params
 * @returns
 */
export function editWechatMenu(params: Record<string, any>) {
    return request.put('wechat/menu', params, { showSuccessMessage: true })
}

/**
 * 获取消息模板列表
 * @returns
 */
export function getTemplateList() {
    return request.get('wechat/template')
}

/**
 * 获取同步
 * @param params
 * @returns
 */
export function getBatchAcquisition(params: Record<string, any>) {
    return request.put('wechat/template/sync', params, { showSuccessMessage: true })
}

/**
 * 查询关键字回复列表
 */
export function getKeywordsReplyList(params: Record<string, any>) {
    return request.get('wechat/reply/keywords', { params })
}

/**
 * 修改关键字回复
 */
export function editKeywordsReply(params: Record<string, any>) {
    return request.put(`wechat/reply/keywords/${ params.id }`, params, { showSuccessMessage: true })
}

/**
 * 修改关键字回复
 */
export function addKeywordsReply(params: Record<string, any>) {
    return request.post('wechat/reply/keywords', params, { showSuccessMessage: true })
}

/**
 * 查询关键字回复信息
 * @param id
 */
export function getKeywordsReplyInfo(id: number) {
    return request.get(`wechat/reply/keywords/${ id }`)
}

/**
 * 修改关键字回复
 */
export function delKeywordsReply(id: number) {
    return request.delete(`wechat/reply/keywords/${ id }`, { showSuccessMessage: true })
}

/**
 * 获取默认回复
 */
export function getDefaultReply() {
    return request.get('wechat/reply/default')
}

/**
 * 设置默认回复
 * @param params
 */
export function setDefaultReply(params: Record<string, any>) {
    return request.put('wechat/reply/default', params, { showSuccessMessage: true })
}

/**
 * 获取关注回复
 */
export function getSubscribeReply() {
    return request.get('wechat/reply/subscribe')
}

/**
 * 设置关注回复
 */
export function setSubscribeReply(params: Record<string, any>) {
    return request.put('wechat/reply/subscribe', params, { showSuccessMessage: true })
}

/**
 * 查询素材列表
 */
export function getMediaList(params: Record<string, any>) {
    return request.get('wechat/media', { params })
}

/**
 * 同步素材库
 */
export function syncNews() {
    return request.get('wechat/sync/news')
}

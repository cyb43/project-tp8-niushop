import request from '@/utils/request'

//当前接口用户指系统整体用户管理，站内用户添加，编辑，详情，操作日志，请查看站点内部相关接口

/***************************************************** 用户 ****************************************************/

/**
 * 获取用户列表
 * @param params
 * @returns
 */
export function getUserList(params: Record<string, any>) {
    return request.get(`user`, { params })
}

/**
 * 获取用户详情
 * @param uid 用户uid
 * @returns
 */
export function getUserInfo(uid: number) {
    return request.get(`user/${ uid }`);
}

/**
 * 添加用户
 * @param params
 * @returns
 */
export function addUser(params: Record<string, any>) {
    return request.post('user', params, { showSuccessMessage: true })
}

/**
 * 获取所有用户列表
 * @param params
 * @returns
 */
export function getAllUserList(params: Record<string, any>) {
    return request.get(`user/user_all`, { params })
}

/**
 * 更新用户
 * @param params
 */
export function editUser(params: Record<string, any>) {
    return request.put(`user/${ params.uid }`, params, { showSuccessMessage: true })
}

/**
 * 锁定用户
 */
export function lockUser(uid: number) {
    return request.put(`user/lock/${ uid }`, {}, { showSuccessMessage: true })
}

/**
 * 解除用户锁定
 */
export function unlockUser(uid: number) {
    return request.put(`user/unlock/${ uid }`, {}, { showSuccessMessage: true })
}

/**
 * 删除用户
 *
 * @param uid
 */
export function deleteUser(uid: number) {
    return request.delete(`user/${ uid }`)
}

/***************************************************** 操作日志 **************************************************/

/**
 * 获取操作日志列表
 * @param params
 * @returns
 */
export function getLogList(params: Record<string, any>) {
    return request.get(`user/userlog`, { params })
}

/**
 * 获取操作日志详情
 * @param id
 */
export function getLogInfo(id: number) {
    return request.get(`user/userlog/${ id }`)
}

/**
 * 清空操作日志列表
 * @returns
 */
export function logDestroy() {
    return request.delete(`user/userlog/destroy`)
}

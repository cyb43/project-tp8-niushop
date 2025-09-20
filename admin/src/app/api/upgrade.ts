import request from '@/utils/request'


/**
 * 获取更新内容
 * @param addon
 */
export function getUpgradeContent(addon: string = '') {
    return request.get(addon ? `upgrade/${ addon }` : 'upgrade')
}

/**
 * 获取升级任务
 */
export function getUpgradeTask() {
    return request.get('upgrade/task')
}

/**
 * 升级
 * @param addon
 * @param params
 */
export function upgradeAddon(addon: string = '', params: Record<string, any> = {}) {
    return request.post(addon ? `upgrade/${ addon }` : 'upgrade', params)
}

/**
 * 执行升级
 */
export function executeUpgrade() {
    return request.post('upgrade/execute', {}, { showErrorMessage: false })
}

/**
 * 升级前检测
 */
export function preUpgradeCheck(addon: string = '') {
    return request.get(addon ? `upgrade/check/${ addon }` : 'upgrade/check')
}

/**
 * 清除
 */
export function clearUpgradeTask() {
    return request.post('upgrade/clear')
}

/**
 * 用户操作
 * @param operate
 */
export function upgradeUserOperate(operate: string) {
    return request.post(`upgrade/operate/${ operate }`)
}

/**
 * 获取升级记录分页列表
 * @param params
 * @returns
 */
export function getUpgradeRecords(params: Record<string, any>) {
    return request.get(`upgrade/records`, { params })
}

/**
 * 删除升级记录
 * @param params
 */
export function delUpgradeRecords(params: Record<string, any>) {
    return request.delete(`upgrade/records`, { params })
}

/**
 * 获取备份记录分页列表
 * @param params
 * @returns
 */
export function getBackupRecords(params: Record<string, any>) {
    return request.get(`backup/records`, { params })
}

/**
 * 修改备份备注
 * @param params
 */
export function modifyBackupRemark(params: Record<string, any>) {
    return request.put(`backup/remark`, params, { showSuccessMessage: true })
}

/**
 * 备份功能 检测目录权限
 */
export function checkDirExist(params: Record<string, any>) {
    return request.post('backup/check_dir', params)
}

/**
 * 备份功能 检测目录权限
 */
export function checkPermission(params: Record<string, any>) {
    return request.post('backup/check_permission', params)
}

/**
 * 备份功能 恢复升级备份
 */
export function restoreUpgradeBackup(params: Record<string, any>) {
    return request.post('backup/restore', params)
}

/**
 * 备份功能 删除升级记录
 */
export function deleteRecords(params: Record<string, any>) {
    return request.post('backup/delete', params, { showSuccessMessage: true })
}

/**
 * 手动备份
 */
export function manualBackup(params: Record<string, any>) {
    return request.post("backup/manual", params)
}

/**
 * 获取进行中的恢复
 * @param params
 */
export function performRecoveryTasks(params: Record<string, any>) {
    return request.get("backup/restore_task", params)
}

/**
 * 获取进行中的备份
 * @param params
 */
export function performBackupTasks(params: Record<string, any>) {
    return request.get("backup/task", params)
}

/**
 * 连通测试
 * @param params
 */
export function connectTest(params: Record<string, any>) {
    return request.post("niucloud/build/connect_test", params)
}
/**
 * 保存服务器地址
 * @param params
 */
export function setLocalUrl(params: Record<string, any>) {
    return request.post("niucloud/build/set_local_url", params)
}
/**
 * 获取服务器地址
 * @param params
 */
export function getLocalUrl(params: Record<string, any>) {
    return request.get("niucloud/build/get_local_url", params)
}

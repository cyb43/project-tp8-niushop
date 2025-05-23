import request from '@/utils/request'

/***************************************************** 万能表单 ****************************************************/

/**
 * 获取万能表单分页列表
 * @param params
 * @returns
 */
export function getDiyFormPageList(params: Record<string, any>) {
    return request.get(`diy/form`, { params })
}

/**
 * 获取万能表单列表
 * @param params
 * @returns
 */
export function getDiyFormList(params: Record<string, any>) {
    return request.get(`diy/form/list`, { params })
}

/**
 * 获取万能表单分页列表（用于弹框选择）
 * @param params
 * @returns
 */
export function getDiyFormSelectPageList(params: Record<string, any>) {
    return request.get(`diy/form/select`, { params })
}

/**
 * 获取万能表单详情
 * @param form_id 万能表单id
 * @returns
 */
export function getDiyFormInfo(form_id: number) {
    return request.get(`diy/form/${ form_id }`);
}

/**
 * 添加万能表单
 * @param params
 * @returns
 */
export function addDiyForm(params: Record<string, any>) {
    return request.post('diy/form', params, { showSuccessMessage: true })
}

/**
 * 编辑万能表单
 * @param params
 */
export function editDiyForm(params: Record<string, any>) {
    return request.put(`diy/form/${ params.form_id }`, params, { showSuccessMessage: true })
}

/**
 * 修改万能表单分享内容
 * @param params
 */
export function editDiyFormShare(params: Record<string, any>) {
    return request.put(`diy/form/share`, params, { showSuccessMessage: true })
}

/**
 * 删除万能表单
 * @param params
 * @returns
 */
export function deleteDiyForm(params: Record<string, any>) {
    return request.put(`diy/form/delete`, params, { showSuccessMessage: true })
}

/**
 * 获取万能表单初始化数据
 */
export function initPage(params: Record<string, any>) {
    return request.get(`diy/form/init`, { params })
}

/**
 * 获取万能表单微信小程序二维码
 * @param params
 * @returns
 */
export function getDiyFormQrcode(params: Record<string, any>) {
    return request.get(`diy/form/qrcode`, { params })
}

/**
 * 获取万能表单字段列表
 * @param params
 * @returns
 */
export function getDiyFormFieldsList(params: Record<string, any>) {
    return request.get(`diy/form/fields/list`, { params })
}

/**
 * 获取字段统计列表
 * @param params
 * @returns
 */
export function getDiyFormFieldStat(params: Record<string, any>) {
    return request.get(`diy/form/records/field/stat`, { params })
}

/**
 * 获取页面模板类型
 */
export function getDiyTemplate(params: Record<string, any>) {
    return request.get(`diy/template`, { params })
}

/**
 * 获取模板页面列表
 */
export function getDiyTemplatePages(params: Record<string, any>) {
    return request.get(`diy/form/template`, { params })
}

/**
 * 万能表单状态状态
 * @param params
 * @returns
 */
export function editFormStatus(params: Record<string, any>) {
    return request.put(`diy/form/status`, params, {
        showErrorMessage: true,
        showSuccessMessage: true
    })
}

/**
 * 获取模板页面（存在的应用插件列表）
 * @param params
 * @returns
 */
export function getApps(params: Record<string, any>) {
    return request.get(`diy/apps`)
}

/**
 * 复制模版页面
 * @param params
 */
export function copyDiy(params: Record<string, any>) {
    return request.post(`diy/form/copy`, params, { showSuccessMessage: true })
}

/**
 * 获取万能表单类型
 * @param params
 * @returns
 */
export function getFormType(params: Record<string, any>) {
    return request.get(`diy/form/type`)
}

/**
 * 获取万能表单填写配置
 * @param form_id
 * @returns
 */
export function getFormWriteConfig(form_id: any) {
    return request.get(`diy/form/write/${ form_id }`)
}

/**
 * 编辑万能表单填写配置
 * @param params
 */
export function editDiyFormWriteConfig(params: Record<string, any>) {
    return request.put(`diy/form/write`, params, { showSuccessMessage: true })
}

/**
 * 获取万能表单提交成功页配置
 * @param form_id
 * @returns
 */
export function getFormSubmitConfig(form_id: any) {
    return request.get(`diy/form/submit/${ form_id }`)
}

/**
 * 编辑万能表单提交成功页配置
 * @param params
 */
export function editDiyFormSubmitConfig(params: Record<string, any>) {
    return request.put(`diy/form/submit`, params, { showSuccessMessage: true })
}

/**
 * 获取万能表单数据列表
 * @param params
 * @returns
 */
export function getFormRecords(params: Record<string, any>) {
    return request.get(`diy/form/records`, { params })
}

/**
 * 获取万能表单数据详情
 * @param id
 * @returns
 */
export function getFormRecordsInfo(id: number) {
    return request.get(`diy/form/records/${ id }`);
}

/**
 * 删除万能表单数据
 * @param params
 * @returns
 */
export function deleteFormRecords(params: Record<string, any>) {
    return request.put(`diy/form/records/delete`, params, { showSuccessMessage: true })
}

/**
 * 获取万能表单填表人列表
 * @param params
 * @returns
 */
export function getFormRecordsMember(params: Record<string, any>) {
    return request.get(`diy/form/records/member/stat`, { params })
}

/**
 * 复制模版页面
 * @param params
 */
export function copyForm(params: Record<string, any>) {
    return request.post(`diy/form/copy`, params, { showSuccessMessage: true })
}

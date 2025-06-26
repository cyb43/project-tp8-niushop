import request from '@/utils/request'

/**
 * 获取自定义表单信息
 */
export function getDiyFormInfo(params: Record<string, any>) {
    return request.get(`diy/form`,params)
}

/**
 * 提交表单数据
 */
export function addFormRecord(params: Record<string, any>) {
    return request.post('diy/form/record', params)
}

/**
 * 提交表单数据 【编辑】
 */
export function editFormRecord(params: Record<string, any>) {
    return request.put('diy/form/record', params)
}

/**
 * 获取表单填写结果信息
 */
export function getFormResultInfo(params: Record<string, any>) {
    return request.get('diy/form/result', params)
}

/**
 * 获取表单填写记录
 */
export function getFormRecord(params: Record<string, any>) {
    return request.get(`diy/form/record`,params)
}

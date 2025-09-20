import request from '@/utils/request'

/**
 * 获取自定义海报分页列表
 * @param params
 * @returns
 */
export function getPosterPageList(params: Record<string, any>) {
    return request.get(`sys/poster`, { params })
}

/**
 * 获取自定义海报列表
 * @param params
 * @returns
 */
export function getPosterList(params: Record<string, any>) {
    return request.get(`sys/poster/list`, { params })
}

/**
 * 获取自定义海报详情
 * @param id 自定义海报id
 * @returns
 */
export function getPosterInfo(id: number) {
    return request.get(`sys/poster/${ id }`);
}

/**
 * 添加自定义海报
 * @param params
 * @returns
 */
export function addPoster(params: Record<string, any>) {
    return request.post('sys/poster', params, { showErrorMessage: true, showSuccessMessage: true })
}

/**
 * 编辑自定义海报
 * @param params
 * @returns
 */
export function editPoster(params: Record<string, any>) {
    return request.put(`sys/poster/${ params.id }`, params, {
        showErrorMessage: true,
        showSuccessMessage: true
    })
}

/**
 * 删除自定义海报
 * @param id
 * @returns
 */
export function deletePoster(id: number) {
    return request.delete(`sys/poster/${ id }`, { showErrorMessage: true, showSuccessMessage: true })
}

/**
 * 修改自定义海报状态
 * @param params
 */
export function modifyPosterStatus(params: Record<string, any>) {
    return request.put(`sys/poster/status`, params, { showSuccessMessage: true })
}

/**
 * 将自定义海报修改为默认海报
 * @param params
 */
export function modifyPosterDefault(params: Record<string, any>) {
    return request.put(`sys/poster/default`, params, { showSuccessMessage: true })
}

/**
 * 获取自定义海报类型
 * @param params
 * @returns
 */
export function getPosterType(params: Record<string, any>) {
    return request.get(`sys/poster/type`, { params })
}

/**
 * 获取自定义海报模板
 * @param params
 * @returns
 */
export function getPosterTemplate(params: Record<string, any>) {
    return request.get(`sys/poster/template`, { params })
}

/**
 * 获取自定义海报初始化数据
 */
export function initPoster(params: Record<string, any>) {
    return request.get(`sys/poster/init`, { params })
}

/**
 * 获取自定义海报预览
 */
export function getPreviewPoster(params: Record<string, any>) {
    return request.get(`sys/poster/preview`, { params })
}

/**
 * 下载
 * @param params
 * @returns
 */
export function getPosterGenerate(params: Record<string, any>) {
    return request.get(`sys/poster/generate`, { params, showErrorMessage: false })
}
// 判断是否安装imagemagick扩展
export function checkImagick() {
    return request.get(`sys/check_imagick`, { showErrorMessage: false })
}
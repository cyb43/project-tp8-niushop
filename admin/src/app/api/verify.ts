import request from '@/utils/request'


/***************************************************** 核销 ****************************************************/

/**
 * 获取核销记录
 * @param params
 * @returns
 */
export function getVerifyRecord(params: Record<string, any>) {
    return request.get(`verify/verify/record`, { params })
}

/**
 * 获取核销信息
 * @param verifyCode
 * @returns
 */
export function getVerifyDetail(verifyCode: string) {
    return request.get(`verify/verify/${ verifyCode }`)
}

/**
 * 获取核销详情
 * @param verifyCode
 * @returns
 */
export function getVerifyDetailInfo(verifyCode: string) {
    return request.get(`verify/detail/${ verifyCode }`)
}

/**
 * 核销
 * @param verifyCode
 * @returns
 */
export function verify(verifyCode: string, params: Record<string, any>) {
    return request.post(`verify/verify/${ verifyCode }`,params,{ showSuccessMessage: true})
}
/***************************************************** 核销员 ****************************************************/

/**
 * 获取核销员列表
 * @param params
 * @returns
 */
export function getVerifierList(params: Record<string, any>) {
    return request.get(`verify/verifier`, { params })
}

/**
 * 获取核销员列表
 * @returns
 */
export function getVerifierSelect() {
    return request.get(`verify/verifier/select`)
}


/**
 * 获取核销类型列表
 * @returns
 */
export function getVerifyTypeList() {
    return request.get(`verify/verifier/type`)
}

/**
 * 添加核销员
 * @param params
 * @returns
 */
export function addVerifier(params: Record<string, any>) {
    return request.post('verify/verifier', params, { showSuccessMessage: true })
}

/**
 * 删除核销员
 * @param id
 * @returns
 */
export function deleteVerifier(id: number) {
    return request.delete(`verify/verifier/${ id }`, { showSuccessMessage: true })
}

/**
 * 获取核销员信息
 * @returns
 */
export function getVerifyInfo(id: number) {
    return request.get(`verify/verifier/${ id }`)
}

/**
 * 修改核销员信息
 * @returns
 */
export function editVerifier(params: Record<string, any>) {
    return request.post(`verify/verifier/${ params.id }`, params,{ showSuccessMessage: true })
}

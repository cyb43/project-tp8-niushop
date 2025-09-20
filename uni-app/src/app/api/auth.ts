import request from '@/utils/request'

/**
 * 用户名登录
 */
export function usernameLogin(data: AnyObject) {
    if (uni.getStorageSync('pid')) {
        data.pid = uni.getStorageSync('pid');
    }
    return request.get('login', data, { showErrorMessage: true })
}

/**
 * 手机验证码登录
 */
export function mobileLogin(data: AnyObject) {
    if (uni.getStorageSync('pid')) {
        data.pid = uni.getStorageSync('pid');
    }
    return request.post('login/mobile', data, { showErrorMessage: true })
}

/**
 * 获取登录配置
 */
export function getConfig(params: Record<string, any>) {
    return request.get('login/config', params)
}

/**
 * 退出登录
 */
export function logout() {
    return request.put('auth/logout')
}

/**
 * 用户名注册
 */
export function usernameRegister(data: AnyObject) {
    let url = 'register'
    if (uni.getStorageSync('pid')) {
        data.pid = uni.getStorageSync('pid');
    }
    return request.post(url, data, { showErrorMessage: true })
}

/**
 * 手机号注册
 */
export function mobileRegister(data: AnyObject) {
    let url = 'register/mobile'
    if (uni.getStorageSync('pid')) {
        data.pid = uni.getStorageSync('pid');
    }
    return request.post(url, data, { showErrorMessage: true })
}

/**
 * 微信公众号授权信息
 */
export function wechatUser(data: AnyObject) {
    return request.get('wechat/user', data, { showErrorMessage: false })
}

/**
 * 微信公众号授权信息登录（openid）
 */
export function wechatUserLogin(data: AnyObject) {
    if (uni.getStorageSync('pid')) {
        data.pid = uni.getStorageSync('pid');
    }
    return request.post('wechat/userlogin', data, { showErrorMessage: true })
}

/**
 * 微信公众号授权登录
 */
export function wechatLogin(data: AnyObject) {
    if (uni.getStorageSync('pid')) {
        data.pid = uni.getStorageSync('pid');
    }
    return request.post('wechat/login', data, { showErrorMessage: false })
}

/**
 * 微信公众号号修改openid
 */
export function updateWechatOpenid(data: AnyObject) {
    return request.put('wechat/update_openid', data, { showErrorMessage: false })
}

/**
 * 微信公众号号修改openid
 */
export function updateWechatOpenidByH5(data: AnyObject) {
    return request.put('wechat/update_openid_h5', data, { showErrorMessage: false })
}

/**
 * 微信小程序授权登录
 */
export function weappLogin(data: AnyObject) {
    if (uni.getStorageSync('pid')) {
        data.pid = uni.getStorageSync('pid');
    }
    return request.post('weapp/login', data, { showErrorMessage: false })
}

/**
 * 微信小程序修改openid
 */
export function updateWeappOpenid(data: AnyObject) {
    return request.put('weapp/update_openid', data, { showErrorMessage: false })
}

/**
 * 绑定手机号
 */
export function bind(data: AnyObject) {
    let url = 'bind'
    if (uni.getStorageSync('pid')) {
        data.pid = uni.getStorageSync('pid');
    }
    return request.post(url, data, { showErrorMessage: true })
}

/**
 * 记录会员访问日志【目前只更新最后访问时间】
 */
export function memberLog(data: AnyObject) {
    return request.post('member/log', data, { showErrorMessage: false })
}
import request from '@/utils/request'

/**
 * 获取自定义页面信息
 */
export function getDiyInfo(params: Record<string, any>) {
    return request.get('diy/diy', params)
}

/**
 * 获取底部导航列表
 */
export function getTabbarList(params: Record<string, any>) {
    return request.get('diy/tabbar/list', params)
}

/**
 * 获取页面分享信息
 */
export function getShareInfo(params: Record<string, any>) {
    return request.get('diy/share', params)
}

/**
 * 获取个人资料表单
 */
export function getMemberFormRecord() {
    return request.get('diy/form/member_record')
}

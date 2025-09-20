import { defineStore } from 'pinia'
import { setToken, removeToken, redirect } from '@/utils/common'
import { getMemberInfo as getMemberInfoApi, bindMobile as bindMobileApi } from '@/app/api/member'
import { logout } from '@/app/api/auth'
import useConfigStore from '@/stores/config'
import { useLogin } from '@/hooks/useLogin'

interface Member {
    token: string | null
    info: any | null
    levelList: Array<any> | null
}

const useMemberStore = defineStore('member', {
    state: (): Member => {
        return {
            token: uni.getStorageSync(import.meta.env.VITE_REQUEST_STORAGE_TOKEN_KEY),
            info: null,
            levelList: null
        }
    },
    actions: {
        setToken(token: string, callback: any = null) {
            this.token = token
            setToken(token)
            this.getMemberInfo(callback)
        },
        getMemberInfo(callback: any = null) {
            if (!this.token) return
            getMemberInfoApi().then((res: any) => {
                this.info = res.data;
                uni.setStorageSync('wap_member_info', this.info)
                uni.setStorageSync('wap_member_id', res.data.member_id)
                if (this.info.mobile) {
                    uni.removeStorageSync('isBindMobile')
                    uni.setStorageSync('wap_member_mobile', this.info.mobile) // 存储会员手机号，防止重复请求微信获取手机号接口
                }

                // #ifdef MP-WEIXIN
                if (this.info && this.info.weapp_openid) {
                    // 如果会员已存在则小程序端快捷登录时不再弹出授权弹框
                    uni.setStorageSync('member_exist', 1)
                } else {
                    const login = useLogin()
                    login.getAuthCode({ updateFlag: true }) // 更新openid
                }
                // #endif

                // #ifdef H5
                if (this.info && this.info.wx_openid) {
                    // 如果会员已存在则小程序端快捷登录时不再弹出授权弹框
                    uni.setStorageSync('member_exist', 1)
                } else {
                    const login = useLogin()
                    login.updateWechatOpenidForH5(uni.getStorageSync('openid'));
                }
                // #endif

                if (callback) callback();
            }).catch(() => {
                this.logout()
                uni.removeStorageSync('wap_member_mobile');
                uni.removeStorageSync('wap_member_id');
                uni.removeStorageSync('wap_member_not_control_mobile');
            })
        },
        logout(isRedirect: boolean = false) {
            if (!this.token) return
            this.token = ''
            this.info = null
            // if (useConfigStore().login.is_auth_register) {
            uni.setStorageSync('autoLoginLock', true) // todo 普通账号退出登录,在进行三方账号登录不会自动登录
            // }
            let clearStorage = () => {
                removeToken()
                uni.removeStorageSync('wap_member_info');
                // uni.removeStorageSync('openid');
                uni.removeStorageSync('unionid');
                uni.removeStorageSync('isBindMobile');
                uni.removeStorageSync('nickname');
                uni.removeStorageSync('avatar');
                // 可能重复请求微信获取手机号接口
                uni.removeStorageSync('wap_member_mobile');
                uni.removeStorageSync('wap_member_id');
                uni.removeStorageSync('wap_member_not_control_mobile');
                isRedirect && redirect({ url: '/app/pages/index/index', mode: 'switchTab' })
            }
            logout().then(() => {
                clearStorage()
            }).catch(() => {
                clearStorage()
            })
        },
        // 一键绑定手机号
        bindMobile(e: any) {
            if (e.detail.errMsg == 'getPhoneNumber:ok') {
                uni.showLoading({ title: '' })
                bindMobileApi({
                    mobile_code: e.detail.code
                }).then((res: any) => {
                    uni.hideLoading()
                    this.getMemberInfo()
                }).catch(() => {
                    setTimeout(() => {
                        uni.hideLoading()
                    }, 2000);
                })

            }

            if (e.detail.errno == 104) {
                let msg = '用户未授权隐私权限';
                uni.showToast({ title: msg, icon: 'none' })
            }
            if (e.detail.errMsg == "getPhoneNumber:fail user deny") {
                let msg = '用户拒绝获取手机号码';
                uni.showToast({ title: msg, icon: 'none' })
            }

        }
    }
})

export default useMemberStore

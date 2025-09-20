import { defineStore } from 'pinia'
import { getToken, setToken, removeToken } from '@/utils/common'
import { login, logout, getAuthMenus } from '@/app/api/auth'
import storage from '@/utils/storage'
import router from '@/router'
import { formatRouters, findFirstValidRoute, findRules } from '@/router/routers'
import useTabbarStore from './tabbar'

interface User {
    token: string,
    userInfo: object,
    routers: any[],
    rules: any[],
    addonIndexRoute: Record<string, symbol>
}

const useUserStore = defineStore('user', {
    state: (): User => {
        return {
            token: getToken() || '',
            userInfo: storage.get('userinfo') || {},
            routers: [],
            rules: [],
            addonIndexRoute: {}
        }
    },
    actions: {
        login(form: object) {
            return new Promise((resolve, reject) => {
                login(form).then(async (res) => {
                    this.token = res.data.token
                    this.userInfo = res.data.userinfo
                    setToken(res.data.token)
                    storage.set({ key: 'userinfo', data: res.data.userinfo })
                    storage.set({ key: 'comparisonTokenStorage', data: res.data.token })
                    resolve(res)
                }).catch((error) => {
                    reject(error)
                })
            })
        },
        clearRouters() {
            this.routers = []
        },
        logout() {
            if (!this.token) return
            this.token = ''
            this.userInfo = {}
            removeToken()
            storage.remove(['userinfo','comparisonTokenStorage','defaultMarketingKeys'])
            this.routers = []
            this.rules = []
            logout()
            // 清除tabbar
            useTabbarStore().clearTab()
            router.push('/login')
        },
        getAuthMenusFn() {
            return new Promise((resolve, reject) => {
                getAuthMenus({}).then((res) => {
                    this.routers = formatRouters(res.data)
                    // 获取插件的首个菜单
                    this.routers.forEach((item, index) => {
                        if (item.meta.app !== '') {
                            if (item.children && item.children.length) {
                                this.addonIndexRoute[item.meta.addon] = findFirstValidRoute(item.children)
                            } else {
                                this.addonIndexRoute[item.meta.addon] = item.name
                            }
                        }
                    })
                    this.rules = findRules(res.data)
                    resolve(res)
                }).catch((error) => {
                    reject(error)
                })
            })
        },
        setUserInfo(data: any) {
            this.userInfo = data
            storage.set({ key: 'userinfo', data: data })
        }
    }
})

export default useUserStore

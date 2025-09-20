import { language } from '@/locale'
import { checkNeedLogin } from '@/utils/auth'
import { redirect, getToken,currRoute, setThemeColor } from '@/utils/common'
import { memberLog } from '@/app/api/auth'
import { useShare } from '@/hooks/useShare'

/**
 * 页面跳转拦截器
 */
export const redirectInterceptor = (route: { path: string, query: object }) => {
    route.path = `/${ route.path }`

    // 检测当前访问的是系统（app）还是插件
    setThemeColor(route.path)

    // #ifdef MP
    route.path.indexOf('addon') != -1 && language.loadAllLocaleMessages('addon', uni.getLocale())
    // #endif

    // 校验是否需要登录
    checkNeedLogin(route)

    loadShare()

    // 添加会员访问日志
    if (getToken()) memberLog({
        route: route.path,
        params: JSON.stringify(route.query),
        pre_route: getCurrentPages()[0]?.route
    })

}

/**
 * 应用初始化拦截器
 */
export const launchInterceptor = () => {
    const launch = uni.getLaunchOptionsSync()
    launch.path = `/${ launch.path }`

    // 检测当前访问的是系统（app）还是插件
    setThemeColor(launch.path);

    // 加载语言包
    language.loadAllLocaleMessages('app', uni.getLocale())

    // #ifndef MP
    language.loadAllLocaleMessages('addon', uni.getLocale())
    // #endif

    // 校验是否需要登录
    checkNeedLogin(launch)

    // 存储分享会员id
    if (launch.query && launch.query.mid) {
        uni.setStorageSync('pid', launch.query.mid)
    }

    loadShare()

    // 添加会员访问日志
    if (getToken()) memberLog({ route: launch.path, params: JSON.stringify(launch.query || {}), pre_route: '' })
}


// 加载分享
const loadShare = () => {
    const { setShare } = useShare()
    // 分享其它页面时，需要设置当前页面为白名单
    const shareWhiteList = [
        'addon/cms/pages/detail',
        'addon/seckill/pages/goods/detail',
        'addon/shop/pages/goods/detail',
        'addon/shop/pages/point/detail',
        'addon/shop_fenxiao/pages/promote_code',
        'addon/shop_fenxiao/pages/goods',
        'addon/shop_fenxiao/pages/zone',
        'addon/shop_giftcard/pages/detail',
        'addon/shop_giftcard/pages/give',
        'app/pages/index/diy',
        'app/pages/index/diy_form',
        'app/pages/friendspay/share',
        'app/pages/friendspay/money'
    ]
    if (currRoute()) {
        if (!shareWhiteList.includes(currRoute() || '')) setShare()
    }
}

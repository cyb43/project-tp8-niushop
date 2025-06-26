import i18n, { language } from "./i18n"
import useSystemStore from '@/stores/system'
import { getCurrentInstance } from 'vue'

const t = (message: string) => {
    let route = '/' + useSystemStore().currRoute
    // #ifdef H5
    route = getCurrentInstance()?.appContext.config.globalProperties.$route.path || ('/' + useSystemStore().currRoute)
    // #endif
    // #ifdef MP
    route = '/' + (getCurrentInstance()?.root.ctx.$scope.__route__ || useSystemStore().currRoute)
    // #endif
    const file = language.getFileKey(route)
    const key = `${ file.fileKey }.${ message }`
    if (i18n.global.t(message) != message) return i18n.global.t(message)
    return i18n.global.t(key) != key ? i18n.global.t(key) : ''
}

export { language, t }

export default {
    install(app: any) {
        //注册i18n
        app.use(i18n);
    }
};

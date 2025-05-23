import i18n, { language } from "./i18n"
import useSystemStore from '@/stores/system'
import { getCurrentInstance } from 'vue'

const t = (message: string) => {
    let route = '/' + useSystemStore().currRoute
    // #ifdef H5
    route = getCurrentInstance()?.appContext.config.globalProperties.$route.path || ('/' + useSystemStore().currRoute)
    // #endif
    // #ifdef MP
    let _route = getCurrentInstance()?.root.ctx.$scope.__route__;
    if(_route == 'app/pages/index/tabbar'){
        route = useSystemStore().currRoute
    }else{
        route = '/' + getCurrentInstance()?.root.ctx.$scope.__route__
    }
    // #endif
    // console.log('vgetCurrentInstance()?.root.ctx.$scope.__route__',getCurrentInstance()?.root.ctx.$scope.__route__,useSystemStore().currRoute)
    // console.log('route',route)
    const file = language.getFileKey(route)
    // console.log('file',file)
    const key = `${ file.fileKey }.${ message }`
    // console.log('key',key)
    // console.log('i18n.global.t(key)',i18n.global.t(key))
    // console.log('i18n.global.t(message)',i18n.global.t(message))
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

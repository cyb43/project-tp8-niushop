import type { App } from 'vue'
import * as ElementPlusIconsVue from '@element-plus/icons-vue'
import { useCssVar, useTitle } from '@vueuse/core'
import colorFunction from 'css-color-function'
import storage from './storage'

/**
 * 全局注册element-icon
 * @param app
 */
export function useElementIcon(app: App): void {
    for (const [key, component] of Object.entries(ElementPlusIconsVue)) {
        app.component(key, component)
    }
}

/**
 * 设置主题色
 */
export function setThemeColor(color: string, mode: string = 'light'): void {
    useCssVar('--el-color-primary', null).value = color

    const colors: any = {
        dark: {
            'light-3': 'shade(20%)',
            'light-5': 'shade(30%)',
            'light-7': 'shade(50%)',
            'light-8': 'shade(60%)',
            'light-9': 'shade(70%)',
            'dark-2': 'tint(20%)'
        },
        light: {
            'dark-2': 'shade(20%)',
            'light-3': 'tint(30%)',
            'light-5': 'tint(50%)',
            'light-7': 'tint(70%)',
            'light-8': 'tint(80%)',
            'light-9': 'tint(90%)'
        }
    }

    Object.keys(colors[mode]).forEach((key) => {
        useCssVar('--el-color-primary' + '-' + key, null).value = colorFunction.convert(`color(${ color } ${ colors[mode][key] })`)
    })
}

/**
 * 获取当前访问应用类型
 */
export function getAppType() {
    const path = location.pathname.split('/').filter((val) => {
        return val
    })

    if (!path.length) {
        return 'admin'
    } else {
        return path[0]
    }
}

/**
 * 设置网站 title
 * @param value
 */
export function setWindowTitle(value: string = ''): void {
    const title = useTitle()
    title.value = value ? value : import.meta.env.VITE_DETAULT_TITLE
}

/**
 * 获取token
 * @returns
 */
export function getToken(): null | string {
    return storage.get('token')
}

/**
 * 设置token
 * @param token
 * @returns
 */
export function setToken(token: string): void {
    storage.set({ key: 'token', data: token })
}

/**
 * 移除token
 * @returns
 */
export function removeToken(): void {
    storage.remove('token')
}

/**
 * 防抖函数
 * @param fn
 * @param delay
 * @returns
 */
export function debounce(fn: (args?: any) => any, delay: number = 300) {
    let timer: null | number = null
    return function (...args) {
        if (timer != null) {
            clearTimeout(timer)
            timer = null
        }
        timer = setTimeout(() => {
            fn.call(this, ...args)
        }, delay);
    }
}

/**
 * 判断是否是url
 * @param str
 * @returns
 */
export function isUrl(str: string): boolean {
    return str.indexOf('http://') != -1 || str.indexOf('https://') != -1
}

/**
 * 图片输出
 * @param path
 * @returns
 */
export function img(path: string): string {
    let imgDomain = import.meta.env.VITE_IMG_DOMAIN || location.origin

    if (typeof path == 'string' && path.startsWith('/')) path = path.replace(/^\//, '')
    if (typeof imgDomain == 'string' && imgDomain.endsWith('/')) imgDomain = imgDomain.slice(0, -1)

    return isUrl(path) ? path : `${imgDomain}/${path}`
}

/**
 * 输出asset img
 * @param path
 * @returns
 */
export function assetImg(path: string) {
    return new URL('@/', import.meta.url) + path
}

/**
 * 获取字符串字节长度
 * @param str
 * @returns
 */
export function strByteLength(str: string = ''): number {
    let len = 0;
    for (let i = 0; i < str.length; i++) {
        if (str.charCodeAt(i) > 127 || str.charCodeAt(i) == 94) {
            len += 2;
        } else {
            len++;
        }
    }
    return len;
}

/**
 * url 转 route
 * @param url
 */
export function urlToRouteRaw(url: string) {
    const query: any = {}
    const [path, param] = url.split('?')

    param && param.split('&').forEach((str: string) => {
        let [name, value] = str.split('=')
        query[name] = value
    })

    return { path, query }
}

const isArray = (value: any) => {
    if (typeof Array.isArray === 'function') {
        return Array.isArray(value)
    }
    return Object.prototype.toString.call(value) === '[object Array]'
}

/**
 * @description 深度克隆
 * @param {object} obj 需要深度克隆的对象
 * @returns {*} 克隆后的对象或者原值（不是对象）
 */
export function deepClone(obj: object) {
    // 对常见的“非”值，直接返回原来值
    if ([null, undefined, NaN, false].includes(obj)) return obj
    if (typeof obj !== 'object' && typeof obj !== 'function') {
        // 原始类型直接返回
        return obj
    }
    const o = isArray(obj) ? [] : {}
    for (const i in obj) {
        if (obj.hasOwnProperty(i)) {
            o[i] = typeof obj[i] === 'object' ? deepClone(obj[i]) : obj[i]
        }
    }
    return o
}

/**
 * 生成唯一字符
 * @param {Number} len
 * @param {Boolean} firstU
 * @param {Number} radix
 */
export function guid(len = 10, firstU = true, radix: any = null) {
    const chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.split('')
    const uuid = []
    radix = radix || chars.length

    if (len) {
        // 如果指定uuid长度,只是取随机的字符,0|x为位运算,能去掉x的小数位,返回整数位
        for (let i = 0; i < len; i++) uuid[i] = chars[0 | Math.random() * radix]
    } else {
        let r
        // rfc4122标准要求返回的uuid中,某些位为固定的字符
        uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-'
        uuid[14] = '4'

        for (let i = 0; i < 36; i++) {
            if (!uuid[i]) {
                r = 0 | Math.random() * 16
                uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r]
            }
        }
    }
    // 移除第一个字符,并用u替代,因为第一个字符为数值时,该guuid不能用作id或者class
    if (firstU) {
        uuid.shift()
        return `u${ uuid.join('') }`
    }
    return uuid.join('')
}

/**
 * 金额格式化
 */
export function moneyFormat(money: string): string {
    return isNaN(parseFloat(money)) ? money : parseFloat(money).toFixed(2)
}

/**
 * 时间戳转日期格式
 */
export function timeStampTurnTime(timeStamp: any, type = "") {
    if (timeStamp != undefined && timeStamp != "" && timeStamp > 0) {
        const date = new Date();
        date.setTime(timeStamp * 1000);
        const y: any = date.getFullYear();
        let m: any = date.getMonth() + 1;
        m = m < 10 ? ('0' + m) : m;
        let d: any = date.getDate();
        d = d < 10 ? ('0' + d) : d;
        let h: any = date.getHours();
        h = h < 10 ? ('0' + h) : h;
        let minute: any = date.getMinutes();
        let second: any = date.getSeconds();
        minute = minute < 10 ? ('0' + minute) : minute;
        second = second < 10 ? ('0' + second) : second;
        if (type) {
            if (type == 'yearMonthDay') {
                return y + '年' + m + '月' + d + '日';
            }
            return y + '-' + m + '-' + d;
        } else {
            return y + '-' + m + '-' + d + ' ' + h + ':' + minute + ':' + second;
        }

    } else {
        return "";
    }
}

/**
 * 获取当前日期时间
 */
export function getCurrentDataTime(timeStamp: any) {
    const addZero = (t) => {
        return t < 10 ? '0' + t : t;
    }
    const time = new Date(timeStamp);
    let Y = time.getFullYear(), // 年
        M = time.getMonth() + 1, // 月
        D = time.getDate(), // 日
        h = time.getHours(), // 时
        m = time.getMinutes(), // 分
        s = time.getSeconds(); // 秒
    if (M > 12) {
        M = M - 12;
    }
    return `${Y}-${addZero(M)}-${addZero(D)} ${addZero(h)}:${addZero(m)}:${addZero(s)}`
}

/**
 * 日期格式转时间戳
 * @param {Object} date
 */
export function timeTurnTimeStamp(date: string) {
    const f = date.split(' ', 2);
    const d = (f[0] ? f[0] : '').split('-', 3);
    const t = (f[1] ? f[1] : '').split(':', 3);
    return (new Date(
        parseInt(d[0], 10) || null,
        (parseInt(d[1], 10) || 1) - 1,
        parseInt(d[2], 10) || null,
        parseInt(t[0], 10) || null,
        parseInt(t[1], 10) || null,
        parseInt(t[2], 10) || null
    )).getTime() / 1000;
}

/**
 * 过滤小数点(保留两位)
 * @param event
 */
export function filterDigit(event: any) {
    event.target.value = event.target.value.replace(/[^\d\.]/g, '');
    event.target.value = event.target.value.replace(/^\./g, '');
    event.target.value = event.target.value.replace(/\.{2,}/g, '.');
    // 限制最多两位小数
    const decimalParts = event.target.value.split('.');
    if (decimalParts.length > 1 && decimalParts[1].length > 2) {
        // 如果有小数部分且超过两位，则截取前两位
        event.target.value = `${ decimalParts[0] }.${ decimalParts[1].slice(0, 2) }`;
    }
}

/**
 * 过滤整数
 * @param event
 */
export function filterNumber(event: any) {
    event.target.value = event.target.value.replace(/[^\d]/g, '');
}

/**
 * 过滤特殊字符
 * @param event
 */
export function filterSpecial(event: any) {
    event.target.value = event.target.value.replace(/[^\u4e00-\u9fa5a-zA-Z0-9]/g, '')
    event.target.value = event.target.value.replace(/[`~!@#$%^&*()_\-+=<>?:"{}|,.\/;'\\[\]·~！@#￥%……&*（）——\-+={}|《》？：“”【】、；‘’，。、]/g, '')
}

/**
 * 过滤空格
 * @param event
 */
export function filterBlank(event: any) {
    event.target.value = event.target.value.replace(/\s/g, '');
}
/**
 * 设置表格分页数据的本地存储
 * @param page
 * @param limit
 * @param where
 */
export function setTablePageStorage(page: any = 1, limit: any = 10, where: any = {}) {
    let data = storage.get('tablePageStorage');
    if (!data) {
        data = {};
    }

    const key = location.pathname + JSON.stringify(where);
    data[key] = {
        page,
        limit
    };

    const MAX_COUNT = 5; // 最多存储 5 个页面的分页缓存，超出则删除最开始的第一个页面
    if (Object.keys(data).length > MAX_COUNT) {
        delete data[Object.keys(data)[0]];
    }

    storage.set({ key: 'tablePageStorage', data });
}

/**
 * 获取表格分页数据的本地存储
 * @param where
 */
export function getTablePageStorage(where: any = {}) {
    let data = storage.get('tablePageStorage');
    const key = location.pathname + JSON.stringify(where);
    if (!data || !data[key]) {
        data = {
            page: 1,
            limit: 10
        };
    } else {
        data = data[key];
    }
    return data;
}

import { RouteRecordRaw, RouterView } from 'vue-router'
import Default from '@/layout/index.vue'
import Decorate from '@/layout/decorate/index.vue'

// 静态路由
export const STATIC_ROUTES: Array<RouteRecordRaw> = [
    {
        path: '/:pathMatch(.*)*',
        component: () => import('@/app/views/error/404.vue')
    }
]

// 免登录路由
export const NO_LOGIN_ROUTES: string[] = [
    '/404'
]

// 根路由
export const ROOT_ROUTER: RouteRecordRaw = {
    path: '/',
    component: Default,
    name: Symbol()
}

// 平台端根路由
export const ADMIN_ROUTE: RouteRecordRaw = {
    path: '/admin',
    name: Symbol('admin'),
    component: Default,
    children: [
        {
            path: '',
            name: Symbol('adminRoot'),
            component: Default
        },
        {
            path: 'login',
            component: () => import('@/app/views/login/index.vue')
        },
        {
            path: 'user/center',
            meta: {
                type: 1,
                title: '个人中心',
                show: false
            },
            component: () => import('@/app/views/index/edit_personal.vue')
        }
    ]
}

export const DECORATE_ROUTER: RouteRecordRaw = {
    path: '/decorate',
    component: Decorate,
    name: Symbol('decorate'),
    children: []
}

const modules = import.meta.glob('@/app/views/**/*.vue')
const addonModules = import.meta.glob('@/addon/**/views/**/*.vue')

interface Route {
    menu_name: string,
    menu_short_name: string,
    router_path: string,
    view_path: string
    menu_type: number,
    menu_key: string,
    icon?: {
        name: string,
        type: string
    },
    children?: [],
    auth?:[],
    is_show: boolean,
    app_type: string,
    addon: string,
    menu_attr ?:  String
}

/**
 * 创建路由
 * @param route
 * @param parentRoute
 */
const createRoute = function (route: Route, parentRoute: RouteRecordRaw | null = null): RouteRecordRaw {
    const record: RouteRecordRaw = {
        path: `/${ route.app_type }/${ route.router_path }`,
        name: route.menu_key,
        meta: {
            title: route.menu_name,
            short_title: route.menu_short_name,
            icon: route.icon,
            type: route.menu_type,
            show: route.is_show,
            app: route.app_type,
            view: route.view_path,
            addon: route.addon,
            attr: route.menu_attr,
            parent_route: parentRoute ? parentRoute.meta : parentRoute,
            sort: route.sort
        }
    }
    if (route.menu_type == 0) {
        record.component = RouterView
    } else {
        record.component = route.addon ? addonModules[`/src/addon/${ route.addon }/views/${ route.view_path }.vue`] : modules[`/src/app/views/${ route.view_path }.vue`]
    }
    return record
}

/**
 * 格式化路由
 * @param routes
 * @param parentRoute
 */
export function formatRouters(routes: Route[], parentRoute: RouteRecordRaw | null = null) {
    return routes.map((route) => {
        const routeRecord = createRoute(route, parentRoute)
        if (route.children != null && route.children && route.children.length) {
            routeRecord.children = formatRouters(route.children, routeRecord)
        }
        return routeRecord
    })
}

/**
 * 获取首条有效路由
 * @param routes
 * @returns
 */
export function findFirstValidRoute(routes: RouteRecordRaw[]): string | undefined {
    for (const route of routes) {
        if (route.meta?.type == 1 && route.meta?.show) {
            return route.name as string
        }
        if (route.children) {
            const name = findFirstValidRoute(route.children)
            if (name) {
                return name
            }
        }
    }
}

/**
 * 获取按钮权限
 * @param routes
 * @param rules
 */
export function findRules(routes: Route[], rules :string[] = []) : string[] {
    for (const route of routes) {
        if (route.auth && Array.isArray(route.auth)) {
            rules = rules.concat(route.auth)
        }
        if (route.children) {
            rules = findRules(route.children, rules)
        }
    }
    return rules
}


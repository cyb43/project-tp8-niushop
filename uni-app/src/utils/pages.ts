import pagesJson from '@/pages.json'

/**
 * 获取需要登录的页面
 */
export function getNeedLoginPages() {
    const pages: any = []
    // 获取主包中需要登录的页面
    pagesJson.pages.forEach(item => {
        if (item.needLogin) pages.push(`/${ item.path }`)
    })
    // 获取分包中需要登录的页面
    if (pagesJson.subPackages) {
        pagesJson.subPackages.forEach(subPackages => {
            if(subPackages.pages) {
                subPackages.pages.forEach(item => {
                    if (item.needLogin) pages.push(`/${ subPackages.root }/${ item.path }`)
                })
            }
        })
    }
    return pages
}

/**
 * 获取所有页面
 */
export function getAppPages() {
    const pages: any = []
    // 获取主包中需要登录的页面
    pagesJson.pages.forEach(item => {
        pages.push(`/${ item.path }`)
    })
    return pages
}

export function getSubPackagesPages() {
    const pages: any = []
    // 获取分包中需要登录的页面
    if (pagesJson.subPackages) {
        pagesJson.subPackages.forEach(subPackages => {
            if(subPackages.pages) {
                subPackages.pages.forEach(item => {
                    pages.push(`/${ subPackages.root }/${ item.path }`)
                })
            }
        })
    }
    return pages
}

/**
 * 获取tabbar
 */
export function getTabbarPages() {
    return pagesJson.tabBar.list.map(item => {
        return `/${ item.pagePath }`
    })
}

/**
 * 获取首页路径
 */
export function getFirstPage() {
    return '/' + pagesJson.pages[0].path
}

/**
 * 获取自定义navbar的页面
 */
export function getCustomNavigationPages() {
    const pages: any = {}
    // 获取主包中的
    pagesJson.pages.forEach(item => {
        if (item.style && item.style.navigationStyle && item.style.navigationStyle == 'custom') {
            pages[`/${ item.path }`] = item
        }
    })
    // 获取分包中的
    if (pagesJson.subPackages) {
        pagesJson.subPackages.forEach(subPackages => {
            if(subPackages.pages) {
                subPackages.pages.forEach(item => {
                    if (item.style && item.style.navigationStyle && item.style.navigationStyle == 'custom') {
                        pages[`/${ subPackages.root }/${ item.path }`] = item
                    }
                })
            }
        })
    }
    return pages
}

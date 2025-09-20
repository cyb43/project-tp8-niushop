<template>
    <el-container :class="['h-full px-[10px]',{'layout-header border-b border-color': !dark}]" >
        <el-row class="w-100 h-full w-full">
            <el-col :span="12">
                <div class="left-panel h-full flex items-center">
                    <!-- 左侧菜单折叠 -->
                    <div class="hidden-sm-and-up navbar-item flex items-center h-full cursor-pointer" @click="toggleMenuCollapse">
                        <icon name="element Expand" v-if="systemStore.menuIsCollapse" />
                        <icon name="element Fold" v-else />
                    </div>
                    <!-- 刷新当前页 -->
                    <div class="navbar-item flex items-center h-full cursor-pointer" @click="refreshRouter">
                        <icon name="element Refresh" />
                    </div>
                    <!-- 面包屑导航 -->
                    <div class="flex items-center h-full pl-[10px]">
                        <el-breadcrumb separator="/">
                             <!-- :to="route.path" class="inter" 这种修改方式导致部分跳转不对，需要重新调整菜单才可以 -->
                            <el-breadcrumb-item v-for="(route, index) in breadcrumb" :to="route.path" class="inter" :key="index">{{route.meta.title }}</el-breadcrumb-item>
                        </el-breadcrumb>
                    </div>
                </div>
            </el-col>
            <el-col :span="12">
                <div class="right-panel h-full flex items-center justify-end">
                    <div class="flex items-center flex-shrink-0 hidden-xs-only">
                        <el-popover placement="bottom" :width="330" trigger="click" v-model:visible="isMenuSearch" >
                            <template #reference>
                                <i class="iconfont icona-sousuoV6xx-36 cursor-pointer px-[8px] !text-[14px]"></i>
                            </template>
                            <template #default>
                                <div class="flex items-center">
                                    <el-select v-model="selectedRoute"  filterable class="!w-[250px] mr-[20px] menu-select" :teleported="false" clearable  @change="handleRouteSelect">
                                        <el-option v-for="item in flatRoutes" :key="item.name" :label="item.full_title" :value="item.name" >
                                        </el-option>
                                    </el-select>
                                    <el-button type="primary" link @click="isMenuSearch = false">{{t('取消')}}</el-button>
                                </div>
                            </template>
                        </el-popover>
                    </div>
                    <!-- 预览 只有站点时展示-->
                    <i class="iconfont iconicon_huojian1 cursor-pointer px-[8px]" :title="t('visitWap')" @click="toPreview"></i>
                    <!-- 切换语言 -->
<!--                    <div class="navbar-item flex items-center h-full cursor-pointer">-->
<!--                        <switch-lang />-->
<!--                    </div>-->
                    <!-- 切换全屏 -->
                    <!-- <div class="navbar-item flex items-center h-full cursor-pointer" @click="toggleFullscreen">
                        <icon name="iconfont icontuichuquanping" v-if="isFullscreen" />
                        <icon name="iconfont iconquanping" v-else />
                    </div> -->
                    <!-- 布局设置 -->
                    <div class="navbar-item flex items-center h-full cursor-pointer">
                        <layout-setting />
                    </div>
                    <!-- 用户信息 -->
                    <div class="navbar-item flex items-center h-full cursor-pointer">
                        <user-info />
                    </div>
                </div>
            </el-col>
        </el-row>
        <input type="hidden" v-model="comparisonToken">

        <el-dialog v-model="detectionLoginDialog" :title="t('layout.detectionLoginTip')" width="30%" :close-on-click-modal="false" :close-on-press-escape="false" :show-close="false">
            <span>{{ t('layout.detectionLoginContent') }}</span>
            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="detectionLoginFn">{{ t('layout.detectionLoginOperation') }}</el-button>
                </span>
            </template>
        </el-dialog>
    </el-container>
</template>

<script lang="ts" setup>
import { computed, ref, onMounted } from 'vue'
import layoutSetting from './layout-setting.vue'
import userInfo from './user-info.vue'
import switchLang from './switch-lang.vue'
import { useFullscreen } from '@vueuse/core'
import useSystemStore from '@/stores/modules/system'
import useUserStore from '@/stores/modules/user'
import useAppStore from '@/stores/modules/app'
import { useRoute,useRouter } from 'vue-router'
import { t } from '@/lang'
import storage from '@/utils/storage'

const { toggle: toggleFullscreen } = useFullscreen()
const systemStore = useSystemStore()
const appStore = useAppStore()
const route = useRoute()
const router = useRouter()
const screenWidth = ref(window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth)

const dark = computed(() => {
    return systemStore.dark
})

const userStore = useUserStore()
const isMenuSearch = ref(false)
const routers = userStore.routers
const getParentTitleChain=(meta:any) =>{
    let titles = []
    let current = meta?.parent_route

    while (current) {
        if (current.short_title) {
        titles.unshift(current.short_title)
        }
        current = current.parent_route
    }

    return titles.join(' - ')
}
const flattenRoutes = (routes:any, parent = null)=> {
    let flat = [];
    routes.forEach(route => {
        const { path, name, meta = {}, short_title, children } = route
        const isLeaf = meta.type ==1 && meta.show==1
        if(isLeaf){
            const title = meta.title || short_title || ''
            const parentTitleChain = getParentTitleChain(meta)
            const fullTitle = parentTitleChain ? `${parentTitleChain} - ${title}` : title
            const item = {
                path,
                name,
                title,
                parent_title: parentTitleChain,
                full_title: fullTitle
            };

            flat.push(item);
        }
        if (children && children.length > 0) {
            flat = flat.concat(flattenRoutes(children, route))
        }
    });

    return flat;
}
const flatRoutes = flattenRoutes(routers)
const selectedRoute = ref('')
const handleRouteSelect = (name:any) => {
    if (name) {
        router.push({ name })
        isMenuSearch.value = false
    }
}

// 检测登录 start
const detectionLoginDialog = ref(false)
const comparisonToken = ref('')
if (storage.get('comparisonTokenStorage')) {
    comparisonToken.value = storage.get('comparisonTokenStorage')
    // storage.remove(['comparisonTokenStorage']);
}
// 监听标签页面切换
document.addEventListener('visibilitychange', e => {
    if (document.visibilityState === 'visible' && (comparisonToken.value != storage.get('token'))) {
        detectionLoginDialog.value = true
    }
})

const detectionLoginFn = () => {
    detectionLoginDialog.value = false
    location.href = `${location.origin}/`
}
// 检测登录 end

onMounted(() => {
    // 监听窗体宽度变化
    window.onresize = () => {
        return (() => {
            screenWidth.value = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth
        })()
    }
})

// watch(screenWidth, () => {
//     if (screenWidth.value < 992) {
//         if (!systemStore.menuIsCollapse) systemStore.toggleMenuCollapse(true)
//     } else {
//         if (systemStore.menuIsCollapse) systemStore.toggleMenuCollapse(false)
//     }
// })

// 菜单栏展开折叠
const toggleMenuCollapse = () => {
    systemStore.$patch((state) => {
        if (screenWidth.value < 768) {
            state.menuDrawer = true
            state.menuIsCollapse = false
        } else {
            systemStore.toggleMenuCollapse(!systemStore.menuIsCollapse)
        }
    })
}

// 刷新路由
const refreshRouter = () => {
    if (!appStore.routeRefreshTag) return
    appStore.refreshRouterView()
}

// 面包屑导航
const breadcrumb = computed(() => {
    const matched = route.matched.filter(item => { return item.meta.title })
    if (matched[0] && matched[0].path == '/') matched.splice(0, 1)
    return matched
})

// 跳转去预览
const toPreview = () => {
    const url = router.resolve({
        path: '/preview/wap',
        query: {
            page:'/'
        }
    })
    window.open(url.href)
}
</script>

<style lang="scss" scoped>
.layout-header{
    position: relative;
    z-index: 5;
    box-shadow: 0 0 4px 0 rgba(0,145,255,0.1);
}
.navbar-item {
    padding: 0 8px;
    &:hover {
        background-color: var(--el-bg-color-page);
    }
}
.index-item {
	border: 1px solid;
	border-color: var(--el-color-primary);
    &:hover {
		color: #fff;
        background-color: var(--el-color-primary);
    }
}
:deep(.inter .el-breadcrumb__inner){
    font-weight: inherit !important;
    color: var(--el-text-color-regular) !important;
}
</style>

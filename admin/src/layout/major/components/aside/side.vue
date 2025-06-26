<template>
    <el-container class="w-100 h-screen">
        <el-main class="p-0 flex">
            <div class="w-[124px] px-[8px] bg-[#282c34] h-screen one-menu">
                <el-header class="logo-wrap">
                    <div class="logo flex items-center m-auto h-[64px]" v-if="!systemStore.menuIsCollapse">
<!--                        <img class="max-h-[40px] max-w-[40px] rounded-full" v-if="siteInfo.logo" :src="img(siteInfo.logo)" alt="">-->
<!--                        <img class="max-h-[40px] max-w-[40px] rounded-full" v-else src="@/app/assets/images/icon-addon.png" alt="">-->
                        <img class="max-h-[40px] max-w-[40px] rounded-full" src="@/app/assets/images/icon-addon.png" alt="">
                    </div>
                    <div class="logo flex items-center justify-center h-[64px]" v-else>
                        <i class="text-3xl iconfont iconyunkongjian"></i>
                    </div>
                </el-header>
                <el-scrollbar class="h-[calc( 100vh - 64px )]">
                    <el-menu :default-active="oneMenuActive" :router="true" class="aside-menu" :unique-opened="true" :collapse="systemStore.menuIsCollapse">
                        <template v-for="(item, index) in oneMenuData" :key="index">
                            <el-menu-item :index="item.original_name" @click="router.push({ name: item.name })" v-if="item.meta.show">
                                <div v-if="item.meta.icon" class="w-[16px] h-[16px] relative flex justify-center">
                                    <el-image class="w-[16px] h-[16px] rounded-[50%] overflow-hidden" :src="item.meta.icon" fit="fill" v-if="isUrl(item.meta.icon)"/>
                                    <icon :name="item.meta.icon" class="absolute top-[50%] -translate-y-[50%]" v-else />
                                </div>
                                <div v-else class="w-[16px] h-[16px]"></div>
                                <template #title>
                                    <div class="relative flex-1 w-0">
                                        <span class="ml-[10px] w-full truncate">{{ item.meta.short_title || item.meta.title }}</span>
                                    </div>
                                </template>
                            </el-menu-item>
                        </template>
                    </el-menu>
                    <div class="h-[48px]"></div>
                </el-scrollbar>
            </div>
            <el-scrollbar v-if="twoMenuData.length" class="two-menu w-[140px]">
                <div class="w-[140px] h-[64px] flex items-center justify-center text-[16px] border-b-[1px] border-solid border-[var(--el-border-color-lighter)]">
                    {{ route.matched[1].meta.title }}
                </div>

                <el-menu class="aside-menu" :default-active="route.name" :default-openeds="menuOption" :router="true" :collapse="systemStore.menuIsCollapse">
                    <menu-item v-for="(route, index) in twoMenuData" :routes="route" :key="index" />
                </el-menu>
                <div class="h-[48px]"></div>
            </el-scrollbar>
        </el-main>
    </el-container>
</template>

<script lang="ts" setup>
import { ref, watch, computed, onMounted, watchEffect } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import useSystemStore from '@/stores/modules/system'
import useUserStore from '@/stores/modules/user'
import { getShowApp,getShowMarketing } from '@/app/api/addon'
import menuItem from './menu-item.vue'
import { img, isUrl } from '@/utils/common'
import { findFirstValidRoute } from '@/router/routers'
import { cloneDeep } from 'lodash-es'

const systemStore = useSystemStore()
const userStore = useUserStore()
const route = useRoute()
const router = useRouter()
const routers = userStore.routers
const addonIndexRoute = userStore.addonIndexRoute

const oneMenuData = ref<Record<string, any>[]>([])
const twoMenuData = ref<Record<string, any>[]>([])
const addonRouters: Record<string, any> = {}

routers.forEach(item => {
    item.original_name = item.name
    if (item.meta.addon == '') {
        if (item.meta.attr == '') {
            if (item.children && item.children.length) {
                item.name = findFirstValidRoute(item.children)
            }
            oneMenuData.value.push(item)
        }
    } else if (item.meta.addon != '' && systemStore?.apps.length == 1 && systemStore?.apps[0].key == item.meta.addon) {
        if (item.children) {
            item.children.forEach((citem: Record<string, any>) => {
                citem.original_name = citem.name
                if (citem.children && citem.children.length) {
                    citem.name = findFirstValidRoute(citem.children)
                }
            })
            oneMenuData.value.unshift(...item.children)
        } else {
            oneMenuData.value.unshift(item)
        }
    } else {
        addonRouters[item.meta.addon] = item
    }
})

// 多应用时将应用插入菜单
if (systemStore?.apps.length > 1) {
    const routers:Record<string, any>[] = []
    systemStore?.apps.forEach((item: Record<string, any>) => {
        if (addonRouters[item.key]) {
            addonRouters[item.key].name = addonIndexRoute[item.key]
            routers.push(addonRouters[item.key])
        }
    })
    oneMenuData.value.unshift(...routers)
}

const appList = ref(null);
const marketingList = ref(null);
const oneMenuActive = ref(route.matched[1].name)

const getAppList = async () => {
    const res = await getShowApp();
    appList.value = res.data;
    // loading.value = false;
};
const getMarketingList = async () => {
    const res = await getShowMarketing();
    marketingList.value = res.data
}

onMounted(async () => {
    await getAppList() // 确保数据先加载
    await getMarketingList()
})

watchEffect(() => {
    // if (!appList.value || loading.value) return; // 确保数据加载完毕
    const addonKeys = appList.value?.addon?.list?.map(item => item.key) ?? [];
    const toolKeys = appList.value?.tool?.list?.map(item => item.key) ?? [];
    const allKeys = [...addonKeys, ...toolKeys];
    const marketingKeys = marketingList.value?.marketing?.list?.map(item => item.key) ?? [];
    const matchedName = route.matched[1]?.name;
    if (allKeys.includes(matchedName)) {
        oneMenuActive.value = "addon";
        twoMenuData.value = route.matched[1]?.children ?? [];
    } else if (marketingKeys.includes(matchedName)) {
        oneMenuActive.value = "active";
        twoMenuData.value = route.matched[1]?.children ?? [];
    } else if (route.meta.attr !== "") {
        oneMenuActive.value = route.matched[2]?.name;
        twoMenuData.value = route.matched[1]?.children ?? [];
    } else {
        // 多应用
        if (systemStore?.apps.length > 1) {
            twoMenuData.value = route.matched[1]?.children;
            oneMenuActive.value = route.matched[1]?.name;
        } else {
            // 单应用
            const oneMenu = route.matched[1];
            if (oneMenu.meta.addon === "") {
                oneMenuActive.value = route.matched[1]?.name;
                twoMenuData.value = route.matched[1]?.children ?? [];
            } else {
                if (oneMenu.meta.addon === systemStore?.apps[0]?.key) {
                    oneMenuActive.value = route.matched[2]?.name;
                    twoMenuData.value = route.matched[2]?.children ?? [];
                } else {
                    oneMenuActive.value = route.matched[1]?.name;
                    twoMenuData.value = route.matched[1]?.children ?? [];
                }
            }
        }
    }
})

// watch(route, () => {
//     if (route.meta.attr != '') {
//         if (route.matched[2]) oneMenuActive.value = route.matched[2].name
//         twoMenuData.value = route.matched[1].children ?? []
//     } else {
//         // 多应用
//         if (systemStore?.apps.length > 1) {
//             twoMenuData.value = route.matched[1].children
//             oneMenuActive.value = route.matched[1].name
//         } else {
//             // 单应用
//             const oneMenu = route.matched[1]
//             if (oneMenu.meta.addon == '') {
//                 oneMenuActive.value = route.matched[1].name
//                 twoMenuData.value = route.matched[1].children ?? []
//             } else {
//                 if (oneMenu.meta.addon == systemStore?.apps[0].key) {
//                     oneMenuActive.value = route.matched[2].name
//                     twoMenuData.value = route.matched[2].children ?? []
//                 } else {
//                     oneMenuActive.value = route.matched[1].name
//                     twoMenuData.value = route.matched[1].children ?? []
//                 }
//             }
//         }
//     }
// }, { immediate: true })

// 让二级菜单默认展开
const menuOption = ref([])
watch(twoMenuData.value, () => {
    menuOption.value = [];
    if(twoMenuData.value && Object.values(twoMenuData.value).length){
        let data = cloneDeep(twoMenuData.value);
        for(let key in data){
            menuOption.value.push(data[key].name);
        }
    }
}, { immediate: true })
</script>

<style lang="scss">
.one-menu{
    .aside-menu:not(.el-menu--collapse) {
        background-color: transparent;
        .el-menu-item{
            margin-bottom: 4px;
            height: 40px;
            padding-left: 12px !important;
            color: rgba(255,255,255,.7);
            font-size: 14px;
            border-radius: 2px;
            &:hover{
                background-color: var(--el-color-primary);
                color: #fff;
            }
            &.is-active{
                background-color: var(--el-color-primary) !important;
                color: #fff;
            }
            span{
                font-size: 14px;
                margin-left: 8px;
            }
        }
    }
    .el-menu{
        border: 0;
    }
    .el-scrollbar{
        height: calc(100vh - 65px);
    }
}
.two-menu{
    .aside-menu:not(.el-menu--collapse) {
        width: 140px;
        border: 0;
        padding-top: 16px;
        .el-menu-item{
            height: 36px;
            margin: 0 8px 4px;
            padding: 0 8px !important;
            border-radius: 2px;
            span{
                margin-left: 8px;
                font-size: 14px;
            }
            &.is-active{
                background-color: var(--el-color-primary-light-9) !important;
            }
            &:hover{
                background-color: #f7f7f7;
                color: var(--el-color-primary);
            }
        }
        .el-sub-menu{
            margin-bottom: 8px;
            .el-sub-menu__title{
                margin: 0 8px 4px;
                height: 36px;
                padding-left: 8px;
                border-radius: 2px;
                span{
                    height: 36px;
                    display: flex;
                    align-items: center;
                    font-size: 14px;
                }
                &:hover{
                    background-color: #f7f7f7;
                    color: var(--el-color-primary);
                }
                .el-icon.el-sub-menu__icon-arrow{
                    right: 5px;
                }
            }
            .el-menu-item{
                padding-left: 20px !important;
            }
        }
    }
}

.logo-wrap {
    padding: 0;
    display: flex;
    white-space: nowrap;
    align-items: center;

    .logo {
        height: 100%;
        box-sizing: border-box;
    }

    .logo-title {
        flex: 1;
        width: 0;
        text-overflow: ellipsis;
        overflow: hidden;
        font-size: var(--el-font-size-base);
    }
}
</style>

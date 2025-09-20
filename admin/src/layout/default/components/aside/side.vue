<template>
    <el-container class="w-[200px] h-screen layout-aside flex flex-col">
        <el-header class="logo-wrap flex items-center justify-center h-[64px]">
            <div class="logo flex items-center justify-center m-auto w-full h-[64px]" v-if="!systemStore.menuIsCollapse">
                <template v-if="webSite">
                    <img class="max-h-[40px] max-w-[70%]" v-if="webSite.logo" :src="img(webSite.logo)" alt="">
                    <img class="max-h-[40px] max-w-[70%]" src="@/app/assets/images/icon-addon-one.png" alt="" v-else>
                </template>
            </div>
            <div class="logo flex items-center justify-center h-[64px]" v-else>
                <i class="text-3xl iconfont iconyunkongjian"></i>
            </div>
        </el-header>
        <el-main class="menu-wrap">
            <el-scrollbar>
                <el-menu :default-active="route.name" :router="true" class="aside-menu h-full" :unique-opened="true" :collapse="systemStore.menuIsCollapse" >
                    <menu-item v-for="(route, index) in menuData" :routes="route" :key="index" />
                </el-menu>
                <div class="h-[48px]"></div>
            </el-scrollbar>
        </el-main>
    </el-container>
</template>

<script lang="ts" setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import useSystemStore from '@/stores/modules/system'
import useUserStore from '@/stores/modules/user'
import menuItem from './menu-item.vue'
import { img } from '@/utils/common'
import { findFirstValidRoute } from '@/router/routers'
import { getWebConfig } from "@/app/api/sys"
import { getShowMarketing } from '@/app/api/addon'
import storage from '@/utils/storage'

const systemStore = useSystemStore()
const userStore = useUserStore()
const route = useRoute()
const webSite = ref(null)
const routers = userStore.routers
const addonIndexRoute = userStore.addonIndexRoute
const menuData = ref<Record<string, any>[]>([])
const addonRouters: Record<string, any> = {}

const getMarketingList = async () => {
    const res = await getShowMarketing()
    const marketingList = res.data
    const marketingKeys = marketingList?.marketing?.list?.map(item => item.key) ?? []
    // menuData.value.forEach((item, index, arr) => {
    //     if (marketingKeys.includes(item.name)) {
    //         arr.splice(index, 1)
    //     }
    // })
    storage.set({ key: 'defaultMarketingKeys', data: marketingKeys })
}

onMounted(() => {
    getWebConfig().then(({ data }) => {
        webSite.value = data
    });
    getMarketingList()
})

routers.forEach(item => {
    item.original_name = item.name
    if (item.meta.addon == '') {
        if (item.meta.attr == '' && item.name != 'sign' && item.name != 'verify') {
            if (item.children && item.children.length) {
                item.name = findFirstValidRoute(item.children)
            }
            menuData.value.push(item)
        }
    } else if (item.meta.addon != '' && systemStore?.apps.length == 1 && systemStore?.apps[0].key == item.meta.addon && item.meta.show) {
        if (item.children) {
            item.children.forEach((citem: Record<string, any>) => {
                citem.original_name = citem.name
                if (citem.children && citem.children.length) {
                    citem.name = findFirstValidRoute(citem.children)
                }
            })
            menuData.value.unshift(...item.children)
        } else {
            menuData.value.unshift(item)
        }
    } else {
        addonRouters[item.meta.addon] = item
    }

    // 排序, 功能正确，改了排序后需要把菜单排序的默认值重新调整一下【多应用一级菜单，单应用二级菜单】
    // menuData.value.sort((a, b) => {
    //     if (a.meta.sort && b.meta.sort) {
    //         return b.meta.sort - a.meta.sort
    //     } else if (a.meta.sort) {
    //         return -1
    //     } else if (b.meta.sort) {
    //         return 1
    //     } else {
    //         return 0
    //     }
    // })
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
    menuData.value.unshift(...routers)

    // 排序, 功能正确，改了排序后需要把菜单排序的默认值重新调整一下【多应用一级菜单，单应用二级菜单】
    // menuData.value.sort((a, b) => {
    //     if (a.meta.sort && b.meta.sort) {
    //         return b.meta.sort - a.meta.sort
    //     } else if (a.meta.sort) {
    //         return -1
    //     } else if (b.meta.sort) {
    //         return 1
    //     } else {
    //         return 0
    //     }
    // })
}
</script>

<style lang="scss">
.menu-wrap {
    padding: 0!important;

    .el-menu {
        border-right: 0!important;

        .el-menu-item, .el-sub-menu__title {
            --el-menu-item-height: 40px;
        }

        .el-sub-menu .el-menu-item {
            --el-menu-sub-item-height: 40px;
        }
    }
}
</style>

<template>
    <view class="w-screen h-screen flex flex-col">
        <top-tabbar v-if="navbar" :data="navbar.data" :is-back="false"/>

        <view class="hidden">{{ tag }}</view>

        <template v-if="tabbar && Object.keys(tabbar).length">
            <u-tabbar :value="page" zIndex="9999" :fixed="true" :placeholder="true" :safeAreaInsetBottom="true"
                      :inactive-color="tabbar.value.textColor" :active-color="tabbar.value.textHoverColor">
                <block v-for="item in tabbar.value.list">
                    <u-tabbar-item class="py-[5rpx]" :style="{'background-color': tabbar.value.backgroundColor}" :text="item.text"
                                   :icon="img(page == item.link.url ? item.iconSelectPath : item.iconPath)" :name="item.link"
                                   v-if="tabbar.value.type == 1" @click="itemBtn(item.link.url)"></u-tabbar-item>
                    <u-tabbar-item class="py-[5rpx]" :style="{'background-color': tabbar.value.backgroundColor}"
                                   :icon="img(page == item.link.url ? item.iconSelectPath : item.iconPath)" :name="item.link"
                                   v-if="tabbar.value.type == 2" @click="itemBtn(item.link.url)"></u-tabbar-item>
                    <u-tabbar-item class="py-[5rpx]" :style="{'background-color': tabbar.value.backgroundColor}" :text="item.text" :name="item.link.url"
                                   v-if="tabbar.value.type == 3" @click="itemBtn(item.link.url)"></u-tabbar-item>
                </block>
            </u-tabbar>
            <view class="tab-bar-placeholder"></view>
        </template>
    </view>
</template>

<script setup lang="ts">
    import { ref, reactive, watch, computed } from 'vue'
    import { img, redirect, urlDeconstruction } from '@/utils/common'
    import { getCustomNavigationPages } from '@/utils/pages'
    import useConfigStore from '@/stores/config'
    import { cloneDeep } from 'lodash-es'
    import useSystemStore from '@/stores/system'
    import tabbarJson from '@/tabbar.json'
    import zh from '@/locale/zh-Hans.json'
    import en from '@/locale/en.json'
    import TopTabbar from "@/components/top-tabbar/top-tabbar.vue";

    import { language } from '@/locale'

    uni.hideTabBar()

    const customNavigationPages = getCustomNavigationPages()
    const tag = ref(0)
    const scrollTop = ref({})
    const scrollRecord = ref({})
    const systemStore = useSystemStore()
    const page = computed(() => {
        return systemStore.currTabbar.path
    })
    const tabbar:any = reactive({})
    const addon = computed(() => {
        if (systemStore.currTabbar.path) {
            let pathArr = systemStore.currTabbar.path.split('/')
            let index = !pathArr[0] ? 1 : 0;
            let app = pathArr[index] == 'addon' ? pathArr[(index+1)] : 'app';
            return app
        }
        return ''
    })
    const pagesRefs = ref({})
    const navbar = ref(null)
    const loadRecord = ref([])

    const setTabbar = ()=> {
        let list = cloneDeep(useConfigStore().tabbarList);
        if (list.length == 1) {
            Object.assign(tabbar, list[0]);
        } else {
            let isExist = false;
            for (let i = 0; i < list.length; i++) {
                if (list[i].key == addon.value) {
                    Object.assign(tabbar, list[i]);
                    isExist = true;
                    break;
                }
            }

            if (!isExist) {

                // 检测是否单应用
                let count = 0;
                let singleTabbar = {}
                try {
                    if (list) {
                        list.forEach((item: any) => {
                            if (item.info.type == 'app') {
                                count++;
                                singleTabbar = item;
                            }
                        })
                    }

                    if (count == 1) {
                        Object.assign(tabbar, singleTabbar);
                    }

                } catch (e) {

                }
            }

        }
    }

    const pagesLoad = () => {
        if (systemStore.currTabbar.path) {
            if (!loadRecord.value.includes(systemStore.currTabbar.path)) loadRecord.value.push(systemStore.currTabbar.path)

            if (pagesRefs.value[systemStore.currTabbar.path] && pagesRefs.value[systemStore.currTabbar.path].$) {
                // #ifdef MP
                systemStore.currTabbar.path.indexOf('addon') != -1 && language.loadAllLocaleMessages('addon', uni.getLocale())
                // #endif

                // 执行页面onshow onload事件
                pagesRefs.value[systemStore.currTabbar.path].$.root.onLoad.forEach(el => {
                    el(systemStore.currTabbar.query || {})
                })
                pagesRefs.value[systemStore.currTabbar.path].$.root.onShow.forEach(el => {
                    el()
                })
                // 设置页面滚动条高度
                scrollTop.value[systemStore.currTabbar.path] = scrollRecord.value[systemStore.currTabbar.path] || 0
                // 处理页面navbar
                // 页面没有自定义navbar
                if (!customNavigationPages[systemStore.currTabbar.path]) {
                    let key = systemStore.currTabbar.path.replace('/app/', '').replace('/addon/', '').replaceAll('/', '.')

                    switch (uni.getLocale()) {
                        case 'zh-Hans':
                            zh[key] && (key = zh[key])
                            break;
                        case 'en':
                            en[key] && (key = en[key])
                            break;
                    }

                    navbar.value = {
                        data: {
                            title: key,
                            topStatusBar: {
                                "bgColor": "#ffffff",
                                "rollBgColor": "#ffffff",
                                "style": "style-1",
                                "textColor": "#333333",
                                "rollTextColor": "#333333",
                                "textAlign": "center",
                                "imgUrl": "",
                                "link": {
                                    "name": ""
                                }
                            }
                        }
                    }
                } else {
                    navbar.value = null
                }
            } else {
                setTimeout(() => {
                    tag.value++
                    pagesLoad()
                }, 300)
            }
        }
    }

    watch(
        () => useConfigStore().tabbarList,
        (newValue, oldValue) => {
            if (newValue) {
                setTabbar()
            }
        }
        , { deep: true, immediate: true }
    )

    watch(() => addon, (newValue, oldValue) => {
            if (newValue) {
                setTabbar()
            }
        },
        { deep: true, immediate: true }
    )

    const setPagesRefs = (el, key) => {
        if (!pagesRefs.value[key]) {
            pagesRefs.value[key] = el
        }
    }

    const itemBtn = (url: string)=>{
        const route = urlDeconstruction(url)
        if (tabbarJson.includes(route.path)) {
            useSystemStore().$patch((state) => {
                state.currTabbar = route
            })
        } else {
            redirect({ url, mode: 'navigateTo' })
        }
    }

    watch(() => systemStore.currTabbar, (newValue, oldValue) => {
        pagesLoad()
    }, { deep: true, immediate: true })

    const scrollListen = (e: any, key: string) => {
        scrollRecord.value[key] = e.detail.scrollTop

        try {
            pagesRefs.value[systemStore.currTabbar.path].$.root.onPageScroll.forEach(el => {
                el(e.detail)
            })
        } catch (e) {
        }
    }
</script>

<style lang="scss" scoped>
    :deep(.u-tabbar) {
        flex: unset!important;
    }
</style>

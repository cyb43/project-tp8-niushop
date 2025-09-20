<template>
    <!--营销管理-->
    <div class="main-container" v-loading="loading">
        <el-card class="box-card !border-none" shadow="never">

            <template v-if="Object.keys(marketingList).length">

                <template v-for="(item, index) in marketingList" :key="index + 'b'">
                    <div class="flex justify-between items-center">
                        <span class="text-page-title">{{ item.title }}</span>
                    </div>

                    <div class="flex flex-wrap plug-list pb-10 plug-large">
                        <div class="cursor-pointer mt-[20px] mr-4 bg-[#f7f7f7]" v-for="(childItem,childIndex) in item.list" :key="childIndex" @click="toLink(childItem)">
                            <div class="w-[264px] flex py-[20px] px-[17px] app-item relative">
                                <el-image class="w-[40px] h-[40px] mr-[10px] rounded-[6px] overflow-hidden" :src="img(childItem.icon)" fit="contain">
                                    <template #error>
                                        <div class="image-slot">
                                            <img class="w-[40px] h-[40px]" src="@/app/assets/images/index/app_default.png" />
                                        </div>
                                    </template>
                                </el-image>
                                <div class="flex flex-col justify-between w-[180px]">
                                    <div class="text-[14px] flex items-center">
                                        <span class="app-text max-w-[170px]">{{ childItem.title }}</span>
<!--                                        <span class="iconfont iconxiaochengxu2 text-[#00b240] ml-[4px] !text-[14px]"></span>-->
                                    </div>
                                        <!-- <el-icon color="#666">
                                            <QuestionFilled />
                                        </el-icon> -->
                                    <p class="app-text text-[12px] text-[#999]">{{childItem.desc}}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </template>

            <div class="empty flex items-center justify-center" v-if="!loading && !Object.keys(marketingList).length">
                <el-empty :description="t('emptyAppData')" />
            </div>
        </el-card>
    </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { getShowMarketing } from '@/app/api/addon'
import { img } from '@/utils/common'
import useUserStore from '@/stores/modules/user'
import { useRouter } from 'vue-router'
import { t } from '@/lang'

const addonIndexRoute = useUserStore().addonIndexRoute
const router = useRouter()
const marketingList = ref<Record<string, any>[]>([])

const loading = ref(true)
const getMarketingList = async () => {
    const res = await getShowMarketing()
    marketingList.value = res.data
    loading.value = false
}
getMarketingList()

const toLink = (item: any) => {
    if (item.url) {
        // 判断如果携带is_target=true就通过新窗口打开
        if (item.url.indexOf('is_target=true') != -1) {
            const url = router.resolve(item.url)
            window.open(url.href)
        } else {
            router.push(item.url)
        }
    } else {
        addonIndexRoute[item.key] && router.push({ name: addonIndexRoute[item.key] })
    }
}
</script>

<style lang="scss" scoped>
    .app-text {
        overflow: hidden;
        /* 超出部分隐藏 */
        white-space: nowrap;
        /* 禁止文本换行 */
        text-overflow: ellipsis;
        /* 显示省略号 */
    }
    .app-item:hover{
        transition: 0.5s;
        box-shadow: 0 2px 8px 0 rgba(0,0,0,0.1);
    }
</style>

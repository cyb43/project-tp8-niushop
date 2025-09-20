<template>
    <div @click="openDialog">
        <slot></slot>
    </div>
    <el-dialog v-model="showDialog" :title="t('upload.select' + type)" width="60%" class="attachment-dialog" :destroy-on-close="true">
        <div class="flex border-t border-b main-wrap border-color w-full h-[40vh]">
            <!-- 素材 -->
            <div class="attachment-list-wrap flex flex-col p-[15px] flex-1 overflow-hidden">
                <el-row :gutter="15" class="h-[32px]">
                    <el-col :span="10">
                        <div class="flex" v-if="prop.type != 'news'">
                            <upload-media :type="prop.type" @success="getAttachmentList()">
                                <el-button type="primary">{{ t('upload.upload' + type) }}</el-button>
                            </upload-media>
                        </div>
                        <div class="flex" v-else>
                            <el-button type="primary" :loading="syncLoading" @click="syncWechatNews">
                                {{ syncLoading ? '同步中' : '同步微信图文' }}
                            </el-button>
                        </div>
                    </el-col>
                </el-row>
                <div class="flex-1 my-[15px] h-0" v-loading="attachment.loading">
                    <el-scrollbar>
                        <!-- 素材管理 -->
                        <div v-if="attachment.data.length">
                            <div class="flex flex-wrap" v-if="prop.type != 'news'">
                                <div class="attachment-item mr-[10px] mb-[10px] w-[120px]"
                                     v-for="(item, index) in attachment.data" :key="index" @click="selectedFile = item">
                                    <div
                                        class="attachment-wrap w-full rounded cursor-pointer overflow-hidden relative flex items-center justify-center h-[120px]">
                                        <el-image :src="img(item.value)" fit="contain" v-if="type == 'image'" :preview-src-list="item.image_list" />
                                        <video :src="img(item.value)" v-else-if="type == 'video'"></video>
                                        <div class="absolute z-[1] flex items-center justify-center w-full h-full inset-0 bg-black bg-opacity-60" v-show="selectedFile.id == item.id">
                                            <icon name="element Select" color="#fff" size="40px" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="relative" ref="waterfallContainerRef" v-else>
                                <div ref="waterfallItemRef"
                                     class="absolute attachment-item mr-[10px] mb-[10px] w-[280px] rounded-lg overflow-hidden border border-color"
                                     v-for="(item, index) in attachment.data"
                                     :style="{ left: listPosition[index] ? listPosition[index].left : '', top: listPosition[index] ? listPosition[index].top : '' }"
                                     :key="index" @click="selectedFile = item">
                                    <div class="relative">
                                        <div class="w-full h-[130px] relative">
                                            <el-image :src="item.value.news_item[0].thumb_url" class="w-full h-full" />
                                            <div class="absolute left-0 bottom-0 p-[10px] w-full truncate text-white leading-none" v-if="item.value.news_item.length > 1">
                                                {{ item.value.news_item[0].title }}
                                            </div>
                                        </div>
                                        <div v-if="item.value.news_item.length > 1">
                                            <template v-for="(newsItem, newsIndex) in item.value.news_item">
                                                <div class="px-[15px] py-[10px] flex" :class="{'border-b border-color' : newsIndex < item.value.news_item.length - 1 }" v-if="newsIndex > 0">
                                                    <div class="flex-1 w-0 truncate">{{ newsItem.title }}</div>
                                                    <div class="w-[50px] h-[50px] ml-[10px]">
                                                        <el-image :src="newsItem.thumb_url" class="w-full h-full" />
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="px-[15px] py-[10px]" v-else>{{ item.value.news_item[0].title }}</div>
                                        <div class="absolute z-[1] flex items-center justify-center w-full h-full inset-0 bg-black bg-opacity-60" v-show="selectedFile.id == item.id">
                                            <icon name="element Select" color="#fff" size="40px" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-center" v-else>
                            <el-empty v-if="!attachment.loading" :description="t('upload.mediaEmpty')" :image-size="100" />
                        </div>
                    </el-scrollbar>
                </div>
                <el-row :gutter="20">
                    <el-col span="24">
                        <div class="flex h-full justify-end items-center">
                            <el-pagination v-model:current-page="attachment.page" :small="true"
                                           v-model:page-size="attachment.limit" :page-sizes="[10, 20, 30, 40, 60]"
                                           layout="total, sizes, prev, pager, next, jumper" :total="attachment.total"
                                           @size-change="getAttachmentList()" @current-change="getAttachmentList" />
                        </div>
                    </el-col>
                </el-row>
            </div>
        </div>

        <template #footer>
            <span class="dialog-footer">
                <el-button @click="showDialog = false">{{ t('cancel') }}</el-button>
                <el-button type="primary" @click="confirm">{{ t('confirm') }}</el-button>
            </span>
        </template>
    </el-dialog>
</template>

<script lang="ts" setup>
import { reactive, ref, nextTick } from 'vue'
import { t } from '@/lang'
import UploadMedia from './upload-media.vue'
import { img, debounce } from '@/utils/common'
import { getMediaList, syncNews } from '@/app/api/wechat'

const prop = defineProps({
    type: {
        type: String,
        default: 'image'
    }
})

const showDialog = ref(false)

const openDialog = () => {
    prop.type == 'news' && waterfall()
    showDialog.value = true
}

const attachment: Record<string, any> = reactive({
    loading: true,
    page: 1,
    total: 0,
    limit: 10,
    data: []
})

/**
 * 查询素材
 */
const getAttachmentList = (page: number = 1) => {
    attachment.loading = true
    attachment.page = page
    getMediaList({
        page: attachment.page,
        limit: attachment.limit,
        type: prop.type
    }).then(res => {
        attachment.data = res.data.data
        attachment.total = res.data.total
        attachment.loading = false
        prop.type == 'news' && waterfall()
    }).catch(() => {
        attachment.loading = false
    })
}
getAttachmentList()

const emits = defineEmits(['success'])
const selectedFile: Record<string, any> = ref({})

const confirm = () => {
    emits('success', selectedFile.value)
}

const syncLoading = ref(false)
const syncWechatNews = () => {
    if (syncLoading.value) return
    syncLoading.value = true

    syncNews().then(() => {
        syncLoading.value = false
        getAttachmentList()
    }).catch(() => {
        syncLoading.value = false
    })
}

const meta = document.createElement('meta')
meta.content = 'same-origin'
meta.name = 'referrer'
document.getElementsByTagName('head')[0].appendChild(meta)

// 瀑布流计算
const waterfallContainerRef = ref(null)
const waterfallItemRef = ref([])
const listPosition = ref([])
const waterfall = debounce(() => {
    nextTick(() => {
        const containerWidth = waterfallContainerRef.value.clientWidth
        const column = parseInt(containerWidth / 292)
        const heights = []
        const positions = []

        waterfallItemRef.value.forEach((item, i) => {
            if (i < column) {
                const position = {}
                position.top = '0px'
                if (i % column == 0) {
                    position.left = item.clientWidth * i + 'px'
                } else {
                    position.left = item.clientWidth * i + (i % column * 10) + 'px'
                }
                positions[i] = position
                heights[i] = item.clientHeight + 10
            } else {
                const minHeight = Math.min(...heights) //  找到第一列的最小高度
                const minIndex = heights.findIndex(item => item === minHeight) // 找到最小高度的索引
                const position = {}
                position.top = minHeight + 10 + 'px'
                position.left = positions[minIndex].left
                positions[i] = position
                heights[minIndex] += item.clientHeight + 10
            }
        })
        listPosition.value = positions
    })
}, 800)

// 重新布局，以适应窗口变化
window.addEventListener('resize', () => waterfall())
</script>

<style lang="scss" scoped>
</style>

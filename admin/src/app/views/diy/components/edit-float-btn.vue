<template>
    <!-- 内容 -->
    <div class="content-wrap float-btn" v-show="diyStore.editTab == 'content'">

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('floatBtnButton') }}</h3>
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('floatBtnButton')">
                    <span>{{ selectTemplate.name }}</span>
                    <ul class="ml-[10px] flex items-center">
                        <template v-for="(item,i) in templateList" :key="i">
                            <li v-if="diyStore.editComponent.style==='style-1'||(diyStore.editComponent.style==='style-2'&&i>1)"
                                :class="['w-[50px] h-[32px] flex items-center justify-center border-solid  border-[1px] border-[#eee] cursor-pointer', {'border-r-transparent': templateList.length != (i+1)}, (item.className == diyStore.editComponent.bottomPosition) ?  '!border-[var(--el-color-primary)]' : '' ]"
                                @click="changeTemplateList(item)">
                                <span :class="['iconfont !text-[20px]', item.src]"></span>
                            </li>
                        </template>
                    </ul>
                </el-form-item>
                <el-form-item :label="t('floatBtnOffset')">
                    <el-slider v-model="diyStore.editComponent.offset" show-input size="small" class="ml-[10px] diy-nav-slider" :max="100" />
                </el-form-item>
                <el-form-item :label="t('lateralBtnOffset')">
                    <el-slider v-model="diyStore.editComponent.lateralOffset" show-input size="small" class="ml-[10px] diy-nav-slider" :max="15" :min="-10" />
                </el-form-item>
            </el-form>
        </div>
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('floatBtnImageSet') }}</h3>
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('floatBtnImageSize')">
                    <el-slider v-model="diyStore.editComponent.imageSize" show-input size="small" class="ml-[10px] diy-nav-slider" :min="30" :max="100" />
                </el-form-item>
                <el-form-item :label="t('floatBtnAroundRadius')">
                    <el-slider v-model="diyStore.editComponent.aroundRadius" show-input size="small" class="ml-[10px] diy-nav-slider" :max="50" />
                </el-form-item>

                <div class="text-[12px] text-[#999] mb-[15px] mt-[5px]">{{ t('floatBtnImageSuggest') }}</div>
                <div ref="imageBoxRef">
                    <div v-for="(item,index) in diyStore.editComponent.list" :key="item.id"
                         class="item-wrap p-[10px] pb-0 relative border border-dashed border-gray-300 mb-[16px]">
                        <el-form-item :label="t('image')">
                            <upload-image v-model="item.imageUrl" :limit="1" />
                        </el-form-item>

                        <div class="del absolute cursor-pointer z-[2] top-[-8px] right-[-8px]"
                             v-show="diyStore.editComponent.list.length > 1"
                             @click="diyStore.editComponent.list.splice(index,1)">
                            <icon name="element CircleCloseFilled" color="#bbb" size="20px" />
                        </div>

                        <el-form-item :label="t('link')">
                            <diy-link v-model="item.link" />
                        </el-form-item>
                    </div>
                </div>
            </el-form>

            <el-button v-show="diyStore.editComponent.list.length < 3" class="w-full" @click="addImageAd">{{ t('addImageAd') }}</el-button>
        </div>
    </div>

    <!-- 样式 -->
    <div class="style-wrap" v-show="diyStore.editTab == 'style'">
        <!-- 组件样式 -->
        <slot name="style"></slot>
    </div>

</template>

<script lang="ts" setup>
import { ref, reactive, onMounted, nextTick } from 'vue'
import { t } from '@/lang'
import Sortable from 'sortablejs'
import useDiyStore from '@/stores/modules/diy'
import { range } from 'lodash-es'

const diyStore = useDiyStore()
diyStore.editComponent.ignore = ['pageBgColor', 'marginTop', 'marginBottom', 'marginBoth', 'componentBgUrl'] // 忽略公共属性

// 组件验证
diyStore.editComponent.verify = (index: number) => {
    const res = { code: true, message: '' }
    diyStore.value[index].list.forEach((item: any) => {
        if (item.imageUrl === '') {
            res.code = false
            res.message = t('imageUrlTip')
            return res
        }
    })
    return res
}

const templateList = ref([
    {
        name: '左上',
        src: 'iconzuoshangpc',
        className: 'upperLeft'
    },
    {
        name: '右上',
        src: 'iconyoushangpc',
        className: 'upperRight'
    },
    {
        name: '左下',
        src: 'iconzuoxiapc',
        className: 'lowerLeft'
    },
    {
        name: '右下',
        src: 'iconyouxiapc',
        className: 'lowerRight'
    }
])

const selectTemplate = ref({})
templateList.value.forEach((item) => {
    if (item.className == diyStore.editComponent.bottomPosition) {
        selectTemplate.value = item
    }
})

const changeTemplateList = (data: any) => {
    selectTemplate.value = data
    diyStore.editComponent.bottomPosition = data.className
}

const addImageAd = () => {
    diyStore.editComponent.list.push({
        id: diyStore.generateRandom(),
        imageUrl: '',
        link: { name: '' }
    })
}

const imageBoxRef = ref()
diyStore.editComponent.list.forEach((item: any) => {
    if (!item.id) item.id = diyStore.generateRandom()
})

onMounted(() => {
    nextTick(() => {
        const imageSortable = Sortable.create(imageBoxRef.value, {
            group: 'item-wrap',
            animation: 200,
            onEnd: event => {
                const temp = diyStore.editComponent.list[event.oldIndex!]
                diyStore.editComponent.list.splice(event.oldIndex!, 1)
                diyStore.editComponent.list.splice(event.newIndex!, 0, temp)
                imageSortable.sort(
                    range(diyStore.editComponent.list.length).map(value => {
                        return value.toString()
                    })
                )
            }
        })
    })
})

defineExpose({})
</script>

<style lang="scss" scoped>

</style>

<template>
    <div class="flex flex-wrap">
        <template v-if="limit == 1">
            <div class="rounded cursor-pointer overflow-hidden relative border border-solid border-color image-wrap mr-[10px]" :class="{'rounded-full': type=='avatar'}" :style="style">
                <div class="w-full h-full relative" v-if="images.data.length">
                    <div class="w-full h-full flex items-center justify-center">
                        <el-image class="w-full h-full" :src="images.data[0].indexOf('data:image') != -1 ? images.data[0] : img(images.data[0])" :fit="imageFit"></el-image>
                    </div>
                    <div class="absolute z-[1] flex items-center justify-center w-full h-full inset-0 bg-black bg-opacity-60 operation">
                        <icon name="element ZoomIn" color="#fff" size="18px" class="mr-[10px]" @click="previewImage()" />
                        <icon name="element Delete" color="#fff" size="18px" @click.stop="removeImage" />
                    </div>
                </div>
                <upload-attachment :limit="limit" @confirm="confirmSelect" v-else>
                    <div class="w-full h-full flex items-center justify-center flex-col content-wrap">
                        <icon name="element Plus" size="20px" color="var(--el-text-color-secondary)" />
                        <div class="leading-none text-xs mt-[10px] text-secondary">{{ imageText || t('upload.root') }}</div>
                    </div>
                </upload-attachment>
            </div>
        </template>
        <template v-else>
            <div class="flex flex-wrap" ref="imgListRef">
                <div class="rounded cursor-pointer overflow-hidden relative border border-solid border-color image-wrap mr-[10px] mb-[10px]" :style="style" v-for="(item, index) in images.data" :key="item+index">
                    <div class="w-full h-full relative">
                        <div class="w-full h-full flex items-center justify-center">
                            <el-image :src="img(item)" fit="contain"></el-image>
                        </div>
                        <div class="absolute z-[1] flex items-center justify-center w-full h-full inset-0 bg-black bg-opacity-60 operation">
                            <icon name="element ZoomIn" color="#fff" size="18px" class="mr-[10px]" @click="previewImage(index)" />
                            <icon name="element Delete" color="#fff" size="18px" @click="removeImage(index)" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="rounded cursor-pointer overflow-hidden relative border border-dashed border-color" :style="style" v-if="images.data.length < limit">
                <upload-attachment :limit="limit" @confirm="confirmSelect">
                    <div class="w-full h-full flex items-center justify-center flex-col content-wrap">
                        <icon name="element Plus" size="20px" color="var(--el-text-color-secondary)" />
                        <div class="leading-none text-xs mt-[10px] text-secondary">{{ imageText || t('upload.root') }}</div>
                    </div>
                </upload-attachment>
            </div>
        </template>
    </div>

    <el-image-viewer :url-list="previewImageList" v-if="imageViewer.show" @close="imageViewer.show = false" :initial-index="imageViewer.index" :zoom-rate="1" />
</template>

<script lang="ts" setup>
import { computed, reactive, watch, onMounted, toRaw, nextTick, ref } from 'vue'
import { img } from '@/utils/common'
import Sortable from 'sortablejs'
import { t } from '@/lang'

const prop = defineProps({
    type: {
        type: String,
        default: 'image'
    },
    imageFit: {
        type: String,
        default: 'contain'
    },
    modelValue: {
        type: String,
        default: ''
    },
    width: {
        type: String,
        default: '100px'
    },
    height: {
        type: String,
        default: '100px'
    },
    imageText: {
        type: String
    },
    limit: {
        type: Number,
        default: 1
    }
})

const emit = defineEmits(['update:modelValue', 'change'])

const value = computed({
    get () {
        return prop.modelValue
    },
    set (value) {
        emit('update:modelValue', value)
    }
})

const images: Record<string, any> = reactive({
    data: []
})

let previewImageList: string[] = reactive([])

const setValue = () => {
    value.value = toRaw(images.data).toString()
    previewImageList = toRaw(images.data).map((url: string) => { return url.indexOf('data:image') != -1 ? url : img(url) })
}

watch(() => value.value, () => {
    if (value.value.indexOf('data:image') != -1) {
        images.data = [value.value]
    } else {
        images.data = [
            ...value.value.split(',').filter((item: string) => {
                return item
            })
        ]
    }
    setValue()
}, { immediate: true })

const style = computed(() => {
    return {
        width: prop.width,
        height: prop.height
    }
}) 

/**
 * 选择图片
 */
const confirmSelect = (data: Record<string, any>) => {
    if (prop.limit == 1) {
        images.data.splice(0, 1)
        data && images.data.push(data.url)
    } else {
        data.forEach((item: any) => {
            if (images.data.length < prop.limit) images.data.push(item.url)
        })
        nextTick(() => {
            rowDrop()
        })
    }
    setValue()

    nextTick(() => {
        emit('change', value.value)
    })
}

/**
 * 删除图片
 * @param index
 */
const removeImage = (index: number = 0) => {
    images.data.splice(index, 1)
    setValue()
}

/**
 * 查看图片
 */
const imageViewer = reactive({
    show: false,
    index: 0
})
const previewImage = (index: number = 0) => {
    imageViewer.show = true
    imageViewer.index = index
}

/**
 * 拖拽
 */
const imgListRef:any = ref(null)
onMounted(()=> {
    nextTick(() => {
        rowDrop()
    })
})

// 拖拽排序
const rowDrop = () => {
    if (prop.limit == 1) return;
    Sortable.create(imgListRef.value, {
        group: 'image-wrap',
        animation: 300,
        onEnd: event => {
            const temp = images.data[event.oldIndex!]
            images.data.splice(event.oldIndex!, 1)
            images.data.splice(event.newIndex!, 0, temp)
            setValue()
        }
    })
}

</script>

<style lang="scss" scoped>
.image-wrap {
    .operation {
        display: none;
    }

    &:hover {
        .operation {
            display: flex;
        }
    }
}
</style>

<template>
    <!-- 属性内容 -->
    <div class="content-wrap">
        <div class="edit-attr-item-wrap">
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('image')">
                    <upload-image v-model="posterStore.editComponent.value" :limit="1" />
                </el-form-item>
            </el-form>
        </div>

        <!-- 组件公共属性 -->
        <slot name="common"></slot>
    </div>
</template>

<script lang="ts" setup>
import { ref, watch } from 'vue'
import { t } from '@/lang'
import { img } from '@/utils/common'
import usePosterStore from '@/stores/modules/poster'

const posterStore = usePosterStore()

// 组件验证
posterStore.editComponent.verify = (index: number) => {
    const res = { code: true, message: '' }
    if (posterStore.value[index].value == '') {
        res.code = false
        res.message = t('imageUrlTip')
        return res
    }
    return res
}

watch(
    () => posterStore.editComponent.value,
    (newValue, oldValue) => {
        // 设置图片宽高
        const image = new Image()
        image.src = img(posterStore.editComponent.value)
        image.onload = async() => {
            posterStore.editComponent.imgWidth = image.width
            posterStore.editComponent.imgHeight = image.height
        }
    }
)

defineExpose({})

</script>

<style lang="scss" scoped></style>

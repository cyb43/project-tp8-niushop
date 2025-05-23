<template>
    <!-- 属性内容 -->
    <div class="content-wrap">

        <div class="edit-attr-item-wrap">
            <div class="mb-[10px] text-sm text-primary">{{ t('needLoginTips') }}</div>
        </div>

        <!-- 组件公共属性 -->
        <slot name="common"></slot>
        <el-form label-width="80px" class="px-[10px]">
            <el-form-item :label="t('imgShape')">
                <div class="flex items-center">
                    <div class="bg-[#DFDFDF] cursor-pointer w-[50px] h-[50px] border-solid border-[1px] border-transparent rounded-[50%]"
                        :class="{'border-[var(--el-color-primary)]': posterStore.editComponent.shape == 'circle'}"
                        @click="imgShapeChangeFn('circle')"></div>
                    <div class="bg-[#DFDFDF] cursor-pointer w-[50px] h-[50px] ml-[25px] border-solid border-[1px] border-transparent"
                        :class="{'border-[var(--el-color-primary)]': posterStore.editComponent.shape == 'normal'}"
                        @click="imgShapeChangeFn('normal')"></div>
                </div>
            </el-form-item>
        </el-form>
    </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { t } from '@/lang'
import usePosterStore from '@/stores/modules/poster'

const posterStore = usePosterStore()

// 图片形状改变
const imgShapeChangeFn = (data) => {
    posterStore.editComponent.shape = data;
}

defineExpose({})

</script>

<style lang="scss" scoped></style>

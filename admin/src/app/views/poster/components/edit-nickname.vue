<template>
    <!-- 属性内容 -->
    <div class="content-wrap">

        <div class="edit-attr-item-wrap">
            <div class="mb-[10px] text-sm text-primary">{{ t('needLoginTips') }}</div>
        </div>

        <!-- 组件公共属性 -->
        <slot name="common"></slot>

    </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { t } from '@/lang'
import usePosterStore from '@/stores/modules/poster'

const posterStore = usePosterStore()

defineExpose({})

</script>

<style lang="scss" scoped></style>

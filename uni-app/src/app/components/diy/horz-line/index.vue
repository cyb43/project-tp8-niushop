<template>
    <view class="horz-line-wrap">
        <view v-if="diyStore.mode == 'decorate'" class="h-[30rpx]"></view>
        <view :style="warpCss"></view>
        <view v-if="diyStore.mode == 'decorate'" class="h-[30rpx]"></view>
    </view>
</template>

<script setup lang="ts">
// 辅助线
import { computed } from 'vue';
import useDiyStore from '@/app/stores/diy';

const props = defineProps(['component', 'index']);

const diyStore = useDiyStore();

const diyComponent = computed(() => {
    if (diyStore.mode == 'decorate') {
        return diyStore.value[props.index];
    } else {
        return props.component;
    }
})

const warpCss = computed(() => {
    let style = '';
    style += 'border-top:' + (diyComponent.value.borderWidth * 2) + 'rpx ' + diyComponent.value.borderStyle + ' ' + diyComponent.value.borderColor + ';';
    return style;
})

</script>

<style></style>

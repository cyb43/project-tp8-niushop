<template>
    <view :style="warpCss">
        <view class="simple-graph-wrap overflow-hidden relative leading-0">
            <image v-if="diyComponent.imageUrl" :style="itemCss" :src="img(diyComponent.imageUrl)" mode="widthFix" :show-menu-by-longpress="true" class="w-full" />
            <image v-else :style="itemCss" :src="img('static/resource/images/diy/figure.png')" mode="widthFix" :show-menu-by-longpress="true" class="w-full" />

            <template v-if="diyStore.mode != 'decorate'">
                <!-- 热区功能 -->
                <view @click="diyStore.toRedirect(mapItem.link)" class="absolute" v-for="(mapItem, mapIndex) in diyComponent.heatMapData" :key="mapIndex" :style="{ width: mapItem.width + '%', height: mapItem.height + '%', left: mapItem.left + '%', top: mapItem.top + '%' }"></view>
            </template>
        </view>
    </view>
</template>

<script setup lang="ts">
// 热区
import { computed, watch, onMounted } from 'vue';
import useDiyStore from '@/app/stores/diy';
import { img } from '@/utils/common';

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
    if (diyComponent.value.componentStartBgColor) {
        if (diyComponent.value.componentStartBgColor && diyComponent.value.componentEndBgColor) style += `background:linear-gradient(${ diyComponent.value.componentGradientAngle },${ diyComponent.value.componentStartBgColor },${ diyComponent.value.componentEndBgColor });`;
        else style += 'background-color:' + diyComponent.value.componentStartBgColor + ';';
    }
    return style;
})

const itemCss = computed(() => {
    let style = '';
    style += 'height:' + diyComponent.value.imgHeight + ';';
    if (diyComponent.value.topRounded) style += 'border-top-left-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
    if (diyComponent.value.topRounded) style += 'border-top-right-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    return style;
})

onMounted(() => {
    refresh();
    // 装修模式下刷新
    watch(
        () => diyComponent.value,
        (newValue, oldValue) => {
            if (newValue && newValue.componentName == 'HotArea') {
                refresh();
            }
        }
    )
});

const refresh = () => {
    if (diyStore.mode == 'decorate') {
        // 装修模式下设置默认图
        if (diyComponent.value.imageUrl == '') {
            diyComponent.value.imgWidth = 690;
            diyComponent.value.imgHeight = 330;
        }
    }
}

</script>

<style lang="scss">
</style>

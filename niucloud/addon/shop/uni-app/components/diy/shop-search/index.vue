<template>
    <view :style="warpCss">
        <view :style="maskLayer"></view>
        <view class="diy-shop-search relative overflow-hidden flex items-center">
            <image :src="img('addon/shop/diy/search_01.png')" class="w-[40rpx] h-[40rpx]" mode="widthFix" @click="toLink('/addon/shop/pages/goods/category')" />
            <view class="flex-1 ml-[24rpx] rounded-[32rpx] flex items-center bg-[var(--temp-bg)] opacity-90 py-[10rpx] pl-[38rpx] pr-[32rpx] justify-between h-[60rpx] box-border" @click="toLink('/addon/shop/pages/goods/search')">
                <text class="text-[var(--text-color-light9)] text-[26rpx]">{{ diyComponent.text }}</text>
                <text class="nc-iconfont nc-icon-sousuo-duanV6xx1 text-[24rpx]"></text>
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
// 搜索
import { ref, computed, watch, onMounted, nextTick, getCurrentInstance } from 'vue';
import { img, redirect } from '@/utils/common';
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
    if (diyComponent.value.componentStartBgColor) {
        if (diyComponent.value.componentStartBgColor && diyComponent.value.componentEndBgColor) style += `background:linear-gradient(${ diyComponent.value.componentGradientAngle },${ diyComponent.value.componentStartBgColor },${ diyComponent.value.componentEndBgColor });`;
        else style += 'background-color:' + diyComponent.value.componentStartBgColor + ';';
    }

    if (diyComponent.value.componentBgUrl) {
        style += `background-image:url('${ img(diyComponent.value.componentBgUrl) }');`;
        style += 'background-size: cover;background-repeat: no-repeat;';
    }

    if (diyComponent.value.topRounded) style += 'border-top-left-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
    if (diyComponent.value.topRounded) style += 'border-top-right-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    return style;
})

// 背景图加遮罩层
const maskLayer = computed(() => {
    let style = '';
    if (diyComponent.value.componentBgUrl) {
        style += 'position:absolute;top:0;width:100%;';
        style += `background: rgba(0,0,0,${ diyComponent.value.componentBgAlpha / 10 });`;
        style += `height:${ height.value }px;`;

        if (diyComponent.value.topRounded) style += 'border-top-left-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
        if (diyComponent.value.topRounded) style += 'border-top-right-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
        if (diyComponent.value.bottomRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
        if (diyComponent.value.bottomRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    }

    return style;
});

onMounted(() => {
    refresh();
    // 装修模式下刷新
    if (diyStore.mode == 'decorate') {
        watch(
            () => diyComponent.value,
            (newValue, oldValue) => {
                if (newValue && newValue.componentName == 'ShopSearch') {
                    refresh();
                }
            }
        )
    }
});

const instance = getCurrentInstance();
const height = ref(0)

const refresh = () => {
    nextTick(() => {
        const query = uni.createSelectorQuery().in(instance);
        query.select('.diy-shop-search').boundingClientRect((data: any) => {
            height.value = data.height;
        }).exec();
    })
}

const toLink = (url: any) => {
    if (diyStore.mode == 'decorate') return false;
    redirect({ url })
}

</script>

<style lang="scss" scoped>
</style>

<template>
    <view :style="warpCss">
        <view class="diy-image-ads" :style="imageAdsTempStyle">
            <view v-if="diyComponent.list.length == 1" class="leading-0 overflow-hidden" :style="swiperWarpCss">
                <view @click="diyStore.toRedirect(diyComponent.list[0].link)">
                    <image v-if="diyComponent.list[0].imageUrl" :src="img(diyComponent.list[0].imageUrl)" :style="{height: imgHeight}" mode="heightFix" class="!w-full" :show-menu-by-longpress="true" />
                    <image v-else :src="img('static/resource/images/diy/figure.png')" :style="{height: imgHeight}" mode="heightFix" class="!w-full" :show-menu-by-longpress="true" />
                </view>
            </view>

            <swiper v-else class="swiper" :style="{ height: imgHeight }" autoplay="true" circular="true" @change="swiperChange">
                <swiper-item class="swiper-item" v-for="(item) in diyComponent.list" :key="item.id" :style="swiperWarpCss">
                    <view @click="diyStore.toRedirect(item.link)">
                        <view class="item" :style="{height: imgHeight}">
                            <image v-if="item.imageUrl" :src="img(item.imageUrl)" mode="scaleToFill" class="w-full h-full" :show-menu-by-longpress="true" />
                            <image v-else :src="img('static/resource/images/diy/figure.png')" mode="scaleToFill" class="w-full h-full" :show-menu-by-longpress="true" />
                        </view>
                    </view>
                </swiper-item>
            </swiper>
        </view>
    </view>
</template>

<script lang="ts" setup>
// 图片广告
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import useDiyStore from '@/app/stores/diy';
import { img } from '@/utils/common';
import useSystemStore from "@/stores/system";
import { nextTick } from 'process';

const props = defineProps(['component', 'index']);
const systemStore = useSystemStore()

const diyStore = useDiyStore();

const diyComponent = computed(() => {
    if (diyStore.mode == 'decorate') {
        return diyStore.value[props.index];
    } else {
        return props.component;
    }
})

// 兼容通屏样式
const imageAdsTempStyle = computed(() => {
    let style = "";
    if (diyComponent.value.isSameScreen && props.index == 0) {
        // #ifdef H5
        // h5,上移的像素，采取的是平均值
        if (systemStore.systemInfo.platform === 'ios') {
            style = 'margin-top: -55px;';
        } else {
            style = 'margin-top: -44.5px;';
        }
        // #endif
    }
    return style;
})

const warpCss = computed(() => {
    let style = '';
    style += 'position:relative;';
    if (diyComponent.value.componentStartBgColor) {
        if (diyComponent.value.componentStartBgColor && diyComponent.value.componentEndBgColor) style += `background:linear-gradient(${ diyComponent.value.componentGradientAngle },${ diyComponent.value.componentStartBgColor },${ diyComponent.value.componentEndBgColor });`;
        else style += 'background-color:' + diyComponent.value.componentStartBgColor + ';';
    }

    if (diyComponent.value.componentBgUrl) {
        style += `background-image:url('${ img(diyComponent.value.componentBgUrl) }');`;
        style += 'background-size: cover;background-repeat: no-repeat;';
    }
    return style;
})

const swiperWarpCss = computed(() => {
    let style = '';
    if (diyComponent.value.topRounded) style += 'border-top-left-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
    if (diyComponent.value.topRounded) style += 'border-top-right-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    return style;
})

const imgHeight = computed(() => {
    return (diyComponent.value.imageHeight * 2) + 'rpx';
})

const swiperIndex = ref(0);

const swiperChange = e => {
    swiperIndex.value = e.detail.current;
};

onMounted(() => {
    refresh();
    // 装修模式下刷新
    if (diyStore.mode == 'decorate') {
        watch(
            () => diyComponent.value,
            (newValue, oldValue) => {
                if (newValue && newValue.componentName == 'ImageAds') {
                    refresh();
                }
            }
        )
    }
});

onBeforeUnmount(()=>{
    diyStore.imageAdsSameScreen = false;
})

const refresh = () => {
    // 装修模式下设置默认图
    if (diyStore.mode == 'decorate') {
        diyComponent.value.list.forEach((item: any) => {
            if (item.imageUrl == '') {
                item.imgWidth = 690;
                item.imgHeight = 330;
            }
        });
    } else {
        // #ifdef MP
        if (diyComponent.value.isSameScreen && props.index == 0) {
            // 图文导航开启沉浸式且导航栏开启时，导航栏不占位
            diyStore.imageAdsSameScreen = true;
        }
        // #endif
    }
}
</script>

<style lang="scss" scoped>
</style>

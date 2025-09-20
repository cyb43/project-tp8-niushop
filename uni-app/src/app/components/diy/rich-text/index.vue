<template>
    <view :style="warpCss">
        <view :style="maskLayer"></view>
        <view class="diy-rich-text relative">
            <view v-if="diyComponent.html && diyComponent.html != '<p><br></p>'">
                <u-parse :content="diyComponent.html" :tagStyle="{img: 'vertical-align: top;'}"></u-parse>
            </view>
            <template v-else>
                <view>点此编辑『富文本』内容 ——&gt;</view>
                <view>
                    <text>你可以对文字进行</text>
                    <text>、</text>
                    <text class="font-bold">加粗</text>
                    <text>、</text>
                    <text class="italic">斜体</text>
                    <text>、</text>
                    <text class="underline">下划线</text>
                    <text>、</text>
                    <text class="line-through">删除线</text>
                    <text>、文字</text>
                    <text style="color: rgb(0, 176, 240);">颜色</text>
                    <text>、</text>
                    <text style="background-color: rgb(255, 192, 0); color: rgb(255, 255, 255);">背景色</text>
                    <text>、以及字号</text>
                    <text class="text-lg">大</text>
                    <text class="text-sm">小</text>
                    <text class="pl-[10rpx]">等简单排版操作。</text>
                </view>
                <view>也可在这里插入图片、并对图片加上超级链接，方便用户点击。</view>
            </template>
        </view>
    </view>
</template>

<script setup lang="ts">
// 富文本组件
import { ref, computed, watch, onMounted, nextTick, getCurrentInstance } from 'vue';
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
    style += 'position:relative;';
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
                if (newValue && newValue.componentName == 'RichText') {
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
        query.select('.diy-rich-text').boundingClientRect((data: any) => {
            height.value = data.height;
        }).exec();
    })
}
</script>

<style></style>

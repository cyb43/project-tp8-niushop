<template>
    <view :style="warpCss" class="flex justify-between overflow-hidden">
        <view class="p-[20rpx] box-border overflow-hidden" :style="moduleOneCss">
            <view class="flex items-center pb-[30rpx] pt-[6rpx]" v-if="diyComponent.moduleOne.head.textImg || diyComponent.moduleOne.head.subText">
                <image class="h-[28rpx]" v-if="diyComponent.moduleOne.head.textImg" :src="img(diyComponent.moduleOne.head.textImg)" mode="heightFix"/>
                <text class="w-[2rpx] mx-[10rpx] h-[22rpx]" v-if="diyComponent.moduleOne.head.textImg && diyComponent.moduleOne.head.subText" :style="{'backgroundColor': diyComponent.moduleOne.head.subTextColor}"></text>
                <text class="text-[22rpx] truncate max-w-[164rpx]" v-if="diyComponent.moduleOne.head.subText" :style="{color: diyComponent.moduleOne.head.subTextColor}">{{ diyComponent.moduleOne.head.subText }}</text>
            </view>
            <view class="flex items-center">
                <view v-for="(item,index) in diyComponent.moduleOne.list" :key="index" class="flex flex-col items-center" :class="{'mr-[10rpx]': index == 0}" @click="diyStore.toRedirect(item.link)">
                    <view class="bg-[#fff] flex items-center justify-center w-[148rpx] h-[148rpx] rounded-[12rpx] mb-[16rpx]">
                        <image v-if="item.imageUrl" class="w-[102rpx] h-[102rpx]" :src="img(item.imageUrl)" mode="aspectFill" />
                        <u-icon v-else name="photo" color="#999" size="50"></u-icon>
                    </view>
                    <view class="w-[132rpx] h-[44rpx] rounded-[30rpx] flex items-center justify-center text-[22rpx]" :style="moduleBtnCss(item)">{{ item.btnTitle.text }}</view>
                </view>
            </view>
        </view>

        <view class="p-[20rpx] box-border overflow-hidden" :style="moduleTwoCss">
            <view class="flex items-center pb-[30rpx] pt-[6rpx]" v-if="diyComponent.moduleTwo.head.textImg || diyComponent.moduleTwo.head.subText">
                <image class="h-[28rpx] w-[auto]" v-if="diyComponent.moduleTwo.head.textImg" :src="img(diyComponent.moduleTwo.head.textImg)" mode="heightFix" />
                <text class="w-[2rpx] mx-[10rpx] h-[22rpx]" v-if="diyComponent.moduleTwo.head.textImg && diyComponent.moduleTwo.head.subText" :style="{'backgroundColor': diyComponent.moduleTwo.head.subTextColor}"></text>
                <text class="text-[22rpx] truncate max-w-[164rpx]" v-if="diyComponent.moduleTwo.head.subText" :style="{color: diyComponent.moduleTwo.head.subTextColor}">{{ diyComponent.moduleTwo.head.subText }}</text>
            </view>
            <view class="flex items-center">
                <view v-for="(item,index) in diyComponent.moduleTwo.list" :key="index"
                      class="flex flex-col items-center" :class="{'mr-[10rpx]': index == 0}"
                      @click="diyStore.toRedirect(item.link)">
                    <view class="bg-[#fff] flex items-center justify-center w-[148rpx] h-[148rpx] rounded-[12rpx] mb-[16rpx]">
                        <image v-if="item.imageUrl" class="w-[102rpx] h-[102rpx]" :src="img(item.imageUrl)" mode="aspectFill"/>
                        <u-icon v-else name="photo" color="#999" size="50"></u-icon>
                    </view>
                    <view class="w-[132rpx] h-[44rpx] rounded-[30rpx] flex items-center justify-center text-[22rpx]" :style="moduleBtnCss(item)">{{ item.btnTitle.text }}</view>
                </view>
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
// 图片展播
import { ref, reactive, computed, watch, onMounted } from 'vue';
import { img } from '@/utils/common';
import useDiyStore from '@/app/stores/diy';

const props = defineProps(['component', 'index', 'global', 'scrollBool']);
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
    if (diyComponent.value.topRounded) style += 'border-top-left-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
    if (diyComponent.value.topRounded) style += 'border-top-right-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    return style;
})

const moduleOneCss = computed(() => {
    let style = '';
    if (diyComponent.value.moduleOne.listFrame) {
        if (diyComponent.value.moduleOne.listFrame.startColor && diyComponent.value.moduleOne.listFrame.endColor) style += `background:linear-gradient(${ diyComponent.value.moduleOne.listFrame.startColor },${ diyComponent.value.moduleOne.listFrame.endColor });`;
    }

    if (diyComponent.value.moduleRounded.topRounded) style += 'border-top-left-radius:' + diyComponent.value.moduleRounded.topRounded * 2 + 'rpx;';
    if (diyComponent.value.moduleRounded.topRounded) style += 'border-top-right-radius:' + diyComponent.value.moduleRounded.topRounded * 2 + 'rpx;';
    if (diyComponent.value.moduleRounded.bottomRounded) style += 'border-bottom-left-radius:' + diyComponent.value.moduleRounded.bottomRounded * 2 + 'rpx;';
    if (diyComponent.value.moduleRounded.bottomRounded) style += 'border-bottom-right-radius:' + diyComponent.value.moduleRounded.bottomRounded * 2 + 'rpx;';

    if (diyComponent.value.margin && diyComponent.value.margin.both) style += 'width: calc((100vw - ' + (diyComponent.value.margin.both * 4) + 'rpx - 20rpx) / 2);'
    else style += 'width: calc((100vw - 20rpx) / 2 );'
    return style;
})

const moduleTwoCss = computed(() => {
    let style = '';
    if (diyComponent.value.moduleTwo.listFrame) {
        if (diyComponent.value.moduleTwo.listFrame.startColor && diyComponent.value.moduleTwo.listFrame.endColor) style += `background:linear-gradient(${ diyComponent.value.moduleTwo.listFrame.startColor },${ diyComponent.value.moduleTwo.listFrame.endColor });`;
    }
    if (diyComponent.value.moduleRounded.topRounded) style += 'border-top-left-radius:' + diyComponent.value.moduleRounded.topRounded * 2 + 'rpx;';
    if (diyComponent.value.moduleRounded.topRounded) style += 'border-top-right-radius:' + diyComponent.value.moduleRounded.topRounded * 2 + 'rpx;';
    if (diyComponent.value.moduleRounded.bottomRounded) style += 'border-bottom-left-radius:' + diyComponent.value.moduleRounded.bottomRounded * 2 + 'rpx;';
    if (diyComponent.value.moduleRounded.bottomRounded) style += 'border-bottom-right-radius:' + diyComponent.value.moduleRounded.bottomRounded * 2 + 'rpx;';

    if (diyComponent.value.margin && diyComponent.value.margin.both) style += 'width: calc((100vw - ' + (diyComponent.value.margin.both * 4) + 'rpx - 20rpx) / 2);'
    else style += 'width: calc((100vw - 20rpx) / 2 );'
    return style;
})

const moduleBtnCss = (data: any) => {
    let style = '';
    if (data.btnTitle.color) {
        style += 'color:' + data.btnTitle.color + ';';
    }
    if (diyComponent.value.moduleTwo.listFrame.startColor && diyComponent.value.moduleTwo.listFrame.endColor) style += `background:linear-gradient(${ data.btnTitle.startColor },${ data.btnTitle.endColor });`;
    return style;
}

onMounted(() => {
    refresh();
    // 装修模式下刷新
    if (diyStore.mode == 'decorate') {
        watch(
            () => diyComponent.value,
            (newValue, oldValue) => {
                if (newValue && newValue.componentName == 'PictureShow') {
                    refresh();
                }
            }
        )
    }
});

const refresh = () => {

}
</script>

<style lang="scss" scoped></style>

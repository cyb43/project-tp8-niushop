<template>
    <view class="float-btn fixed z-1000" :class="[diyComponent.style,diyComponent.bottomPosition, diyStore.mode == 'decorate' ? 'float-btn-border' : '']" :style="floatBtnWrapCss">
        <view v-if="diyComponent.style==='style-1'" class="flex flex-col items-center p-[24rpx]" :style="warpCss">
            <view v-for="(item,index) in diyComponent.list" :key="index" @click="diyStore.toRedirect(item.link)" :class="{'flex items-center justify-center' : true, 'mb-[20rpx]': diyComponent.list.length != index+1 }" :style="floatBtnItemCss">
                <image v-if="item && item.imageUrl" :style="floatBtnItemCss" :src="img(item.imageUrl)" mode="aspectFit" />
                <image v-else :src="img('static/resource/images/diy/figure.png')" mode="aspectFit" :style="floatBtnItemCss" />
            </view>
        </view>
        <!-- <view v-if="diyComponent.style==='style-2'" class="relative w-[3rpx] h-[3rpx]">
          <view class="py-[14rpx] overflow-hidden absolute right-[25rpx] top-[1rpx] transform -translate-y-1/2" :style="styleTwoWarpCss">
            <swiper :style="{'width':diyComponent.imageSize * 2+24+'rpx','height':diyComponent.imageSize * 2+44+'rpx !important',}" circular>
              <swiper-item v-for="(item,index) in diyComponent.list" :key="index">
                <view @click="diyStore.toRedirect(item.link)" class="px-[12rpx] flex flex-col items-center justify-center">
                  <image v-if="item && item.imageUrl" :style="floatBtnItemCss" :src="img(item.imageUrl)" mode="aspectFit" />
                  <image v-else :src="img('static/resource/images/diy/figure.png')" mode="aspectFit" :style="floatBtnItemCss"/>
                  <view class="text-[24rpx] text-[303133] text-center mt-[20rpx]">{{ item.link.title }}</view>
                </view>
              </swiper-item>
            </swiper>
          </view>
          <view class="w-[60rpx] h-[60rpx] absolute right-[-64rpx] top-[1rpx] transform -translate-y-1/2 rounded-[30rpx] flex items-center" :style="styleTwoSphere">
            <text class="!text-[60rpx] iconfont iconxiaolian-1 text-[var(--primary-color)] font-400  transform rotate-90 translate-x-[-13rpx]"></text>
          </view>
        </view> -->
    </view>
</template>

<script setup lang="ts">
// 浮动按钮组件
import { computed, ref } from 'vue';
import { onPageScroll } from '@dcloudio/uni-app';
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
    if (diyComponent.value.topRounded) style += 'border-top-left-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
    if (diyComponent.value.topRounded) style += 'border-top-right-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';

    return style;
})

const floatBtnItemCss = computed(() => {
    let style = '';
    style += 'width:' + diyComponent.value.imageSize * 2 + 'rpx;';
    style += 'height:' + diyComponent.value.imageSize * 2 + 'rpx;';
    style += 'border-radius:' + diyComponent.value.aroundRadius * 2 + 'rpx;';
    return style;
})

const floatBtnWrapCss = computed(() => {
    let style = '';
    if (diyComponent.value.offset) {
        if (diyComponent.value.bottomPosition == 'lowerRight' || diyComponent.value.bottomPosition == 'lowerLeft') {
            style += 'translateY(' + ((-diyComponent.value.offset) * 2) + 'rpx)';
        } else if (diyComponent.value.bottomPosition == 'upperRight' || diyComponent.value.bottomPosition == 'upperLeft') {
            style += 'translateY(' + diyComponent.value.offset * 2 + 'rpx)';
        }
    }

    if (diyComponent.value.lateralOffset) {
        if (diyComponent.value.bottomPosition == 'upperLeft' || diyComponent.value.bottomPosition == 'lowerLeft') {
            style += ' translateX(' + diyComponent.value.lateralOffset * 2 + 'rpx)';
        } else if (diyComponent.value.bottomPosition == 'upperRight' || diyComponent.value.bottomPosition == 'lowerRight') {
            style += ' translateX(' + ((-diyComponent.value.lateralOffset) * 2) + 'rpx)';
        }
    }

    style = `transform: ${ style };`
    return style;
})

//样式二
const styleTwoRepeat = ref(true)
const styleTwoRepeatTime: any = ref(null)
const styleTwoWarpCss = computed(() => {
    let style = '';
    if (diyComponent.value.componentStartBgColor) {
        if (diyComponent.value.componentStartBgColor && diyComponent.value.componentEndBgColor) style += `background:linear-gradient(${ diyComponent.value.componentGradientAngle },${ diyComponent.value.componentStartBgColor },${ diyComponent.value.componentEndBgColor });`;
        else style += 'background-color:' + diyComponent.value.componentStartBgColor + ';';
    }
    if (diyComponent.value.topRounded) style += 'border-top-left-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
    if (diyComponent.value.topRounded) style += 'border-top-right-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    style += 'transition: right .25s;box-shadow:0px 32rpx 96rpx 32rpx rgba(0, 0, 0, .08), 0px 24rpx 64px rgba(0, 0, 0, .12), 0px 16rpx 32rpx -16rpx rgba(0, 0, 0, .16);'
    style += styleTwoRepeat.value ? 'transition-delay: 0.25s;' : ('right:-' + (diyComponent.value.imageSize * 2 + 24) + 'rpx !important;');
    return style;
})

const styleTwoSphere = computed(() => {
    let style = 'transition: right .25s;background: rgba(0, 0, 0, 0.5);';
    style += styleTwoRepeat.value ? '' : 'right:-32rpx !important;transition-delay: 0.25s;';
    return style
})

onPageScroll(() => {
    if (diyComponent.value.style === 'style-2') {
        if (styleTwoRepeatTime) clearTimeout(styleTwoRepeatTime.value)
        styleTwoRepeat.value = false
        styleTwoRepeatTime.value = setTimeout(() => {
            styleTwoRepeat.value = true
            clearTimeout(styleTwoRepeatTime.value)
        }, 200)
    }
})

</script>

<style lang="scss" scoped>
.float-btn {
    &.upperLeft {
        top: 100rpx;
        left: 0;

        &.style-2 {
            left: 0;
        }
    }

    &.upperRight {
        top: 100rpx;
        right: 0;

        &.style-2 {
            right: 0;
        }
    }

    &.lowerLeft {
        bottom: 160rpx;
        left: 0;
        padding-bottom: constant(safe-area-inset-bottom);
        /*兼容 IOS<11.2*/
        padding-bottom: env(safe-area-inset-bottom);
        /*兼容 IOS>11.2*/
        &.style-2 {
            left: 0;
        }

    }

    &.lowerRight {
        bottom: 160rpx;
        right: 0;
        padding-bottom: constant(safe-area-inset-bottom);
        /*兼容 IOS<11.2*/
        padding-bottom: env(safe-area-inset-bottom);
        /*兼容 IOS>11.2*/
        &.style-2 {
            right: 0;
        }
    }
}

.z-1000 {
    z-index: 1000;
}

.float-btn-border {
    border: 4rpx dashed var(--primary-color);
}
</style>

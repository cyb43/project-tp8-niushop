<template>
    <view :style="warpCss">
        <view :style="maskLayer"></view>
        <view class="diy-notice relative overflow-hidden">
            <view class="flex items-center pl-[28rpx] p-[22rpx]">
                <view v-if="diyComponent.noticeType == 'img'" class="min-w-[60rpx] flex items-center">
                    <template v-if="diyComponent.imgType == 'system'">
                        <image v-if="diyComponent.systemUrl == 'style_1'" :src="img(`static/resource/images/diy/notice/${diyComponent.systemUrl}.png`)" class="h-[40rpx] w-[auto] mr-[20rpx] flex-shrink-0" mode="heightFix" />
                        <image v-else-if="diyComponent.systemUrl == 'style_2'" :src="img(`static/resource/images/diy/notice/${diyComponent.systemUrl}.png`)" class="w-[200rpx] mr-[20rpx] h-[30rpx] flex-shrink-0" mode="heightFix" />
                    </template>
                    <image v-else-if="diyComponent.imgType == 'diy'" :src="img(diyComponent.imageUrl || '')" class="w-[200rpx] h-[30rpx] mr-[20rpx] flex-shrink-0" mode="heightFix" />
                </view>
                <view v-if="diyComponent.noticeType == 'text' && diyComponent.noticeTitle" class="max-w-[128rpx] px-[12rpx] text-[26rpx] h-[40rpx] leading-[40rpx] text-[var(--primary-color)] bg-[var(--primary-color-light)] truncate rounded-[8rpx] mr-[20rpx] flex-shrink-0">{{ diyComponent.noticeTitle }}</view>
                <view class="flex-1 flex overflow-hidden horizontal-body" :id="'horizontal-body-'+diyComponent.id" :class="{'items-center':diyComponent.scrollWay == 'upDown'}">
                    <!-- 横向滚动 -->
                    <view class="horizontal-wrap" :style="marqueeStyle" v-if="diyComponent.scrollWay == 'horizontal'">
                        <view class="marquee marquee-one" id="marquee-one">
                            <view class="item flex-shrink-0 !leading-[40rpx] h-[40rpx]" :class="{'ml-[80rpx]':index}"
                                  v-for="(item, index) in diyComponent.list" :key="index" @click="toRedirect(item)"
                                  :style="{ color: diyComponent.textColor, fontSize: diyComponent.fontSize * 2 + 'rpx',  fontWeight: diyComponent.fontWeight }">{{ item.text }}</view>
                        </view>
                        <view class="marquee" v-if="marqueeBodyWidth < (marqueeOneWidth-30)">
                            <view class="item flex-shrink-0 !leading-[40rpx] h-[40rpx]" :class="{'ml-[80rpx]':index}"
                                  v-for="(item, index) in diyComponent.list" :key="index" @click="toRedirect(item)"
                                  :style="{ color: diyComponent.textColor, fontSize: diyComponent.fontSize * 2 + 'rpx',  fontWeight: diyComponent.fontWeight }">{{ item.text }}</view>
                        </view>
                    </view>

                    <!-- 上下滚动 -->
                    <template v-if="diyComponent.scrollWay == 'upDown'">
                        <swiper :vertical="true" :duration="500" autoplay="true" circular="true" class="flex-1">
                            <swiper-item v-for="(item, index) in diyComponent.list" :key="index" @touchmove.prevent.stop>
                                <text @click="toRedirect(item)" class="beyond-hiding truncate"
                                      :style="{ color: diyComponent.textColor, fontSize: diyComponent.fontSize * 2 + 'rpx', fontWeight: diyComponent.fontWeight }">{{ item.text }}</text>
                            </swiper-item>
                        </swiper>
                        <text class="nc-iconfont nc-icon-youV6xx text-[26rpx] -ml-[8rpx] pl-[30rpx]" :style="{'color': '#999', 'fontWeight': diyComponent.fontWeight}"></text>
                    </template>
                </view>

            </view>
            <view @touchmove.prevent.stop>
                <u-popup :show="noticeShow" @close="noticeShow = false" mode="center" round="var(--rounded-big)" :safeAreaInsetBottom="false">
                    <view class="w-[570rpx] px-[32rpx] popup-common center">
                        <view class="title">公告</view>
                        <scroll-view :scroll-y="true" class="px-[30rpx] box-border h-[260rpx]">
                            <template v-for="(item) in noticeContent.split('\n')">
                                <view class="text-[28rpx] leading-[40rpx] mb-[20rpx]">{{ item }}</view>
                            </template>
                        </scroll-view>
                        <view class="btn-wrap !pt-[40rpx]">
                            <button class="primary-btn-bg w-[480rpx] h-[70rpx] text-[26rpx] leading-[70rpx] rounded-[35rpx] !text-[#fff] font-500" @click="noticeShow = false">我知道了</button>
                        </view>
                    </view>
                </u-popup>
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
// 公告组件
import { ref, computed, watch, onMounted, nextTick, getCurrentInstance } from 'vue';
import { img } from '@/utils/common';
import useDiyStore from '@/app/stores/diy';

const props = defineProps(['component', 'index']);
const diyStore = useDiyStore();
const noticeShow = ref(false);
const noticeContent = ref('');

const diyComponent = computed(() => {
    if (diyStore.mode == 'decorate') {
        return diyStore.value[props.index];
    } else {
        return props.component;
    }
})

const warpCss = computed(() => {
    var style = '';
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
    var style = '';
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

const marqueeBodyWidth = ref(0); // 容器宽度
const marqueeOneWidth = ref(0); // 内容宽度
const marqueeStyle = ref(''); // 横向滚动样式
const time = ref(0); // 滚动完成时间
const delayTime = ref(800); // 动画延迟时间

// 绑定横向滚动事件
const bindCrossSlipEvent = () => {
    if (diyComponent.value.scrollWay == 'horizontal') {
        setTimeout(() => {
            nextTick(() => {
                // #ifdef  MP-WEIXIN
                uni.createSelectorQuery().in(instance).select('#horizontal-body-' + diyComponent.value.id).boundingClientRect(res => {
                    marqueeBodyWidth.value = res.width;
                    const query = uni.createSelectorQuery().in(instance);
                    query.select('#horizontal-body-' + diyComponent.value.id + ' .marquee-one').boundingClientRect((data: any) => {
                        marqueeOneWidth.value = data.width
                        time.value = Math.ceil(marqueeOneWidth.value * 14);
                        if (marqueeBodyWidth.value > (marqueeOneWidth.value - 30)) {
                            marqueeStyle.value = `animation: none;`;
                        } else {
                            marqueeStyle.value = `
                                animation-duration: ${ time.value }ms;
                                animation-delay: ${ delayTime.value }ms;`;
                        }
                    }).exec();
                }).exec();
                // #endif
                // #ifdef  H5
                let documentObj = window.document.getElementById('horizontal-body-' + diyComponent.value.id);
                let marqueeOne = window.document.getElementById('marquee-one');
                if (documentObj && marqueeOne) {
                    marqueeBodyWidth.value = documentObj.offsetWidth;
                    marqueeOneWidth.value = marqueeOne.offsetWidth;
                    time.value = Math.ceil(marqueeOneWidth.value * 14);
                    if (marqueeBodyWidth.value > (marqueeOneWidth.value - 30)) {
                        marqueeStyle.value = `animation: none;`;
                    } else {
                        marqueeStyle.value = `animation-duration: ${ time.value }ms;animation-delay: ${ delayTime.value }ms;`;
                    }
                }
                // #endif
            });
        });
    }
}

onMounted(() => {
    bindCrossSlipEvent();
    refresh();
    // 装修模式下刷新
    if (diyStore.mode == 'decorate') {
        watch(
            () => diyComponent.value,
            (newValue, oldValue) => {
                if (newValue && newValue.componentName == 'Notice') {
                    bindCrossSlipEvent();
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
        query.select('.diy-notice').boundingClientRect((data: any) => {
            height.value = data.height;
        }).exec();
    })
}

const toRedirect = (data: {}) => {
    if (diyStore.mode == 'decorate') return false;
    if (diyComponent.value.showType == 'popup') {
        noticeShow.value = true;
        noticeContent.value = data.text;
    } else {
        diyStore.toRedirect(data.link);
    }
}
</script>

<style lang="scss" scoped>
.main-wrap {
    display: inline-block;
    width: calc(100% - 100rpx);
    position: relative;
}

swiper {
    height: 50rpx;
}

.beyond-hiding {
    display: inline-block;
    width: 100%;
    white-space: nowrap;
    line-height: 50rpx
}

.notice-popup {
    padding: 0 30rpx 40rpx;
    background-color: #fff;

    .head-wrap {
        font-size: 32rpx;
        line-height: 100rpx;
        height: 100rpx;
        display: block;
        text-align: center;
        position: relative;
        border-bottom: 2rpx solid #eeeeee;
        margin-bottom: 20rpx;

        .iconfont {
            position: absolute;
            float: right;
            right: 0;
            font-size: 32rpx;
        }
    }

    .content-wrap {
        max-height: 600rpx;
        overflow-y: auto;
    }

    button {
        margin-top: 40rpx;
    }
}

.horizontal-wrap {
    height: 40rpx;
    display: flex;
    align-items: center;
    transform: translateZ(0);
    animation: marquee 0s 0s linear infinite;
    transform-style: preserve-3d;
    backface-visibility: hidden;
}

.marquee {
    display: flex;
    align-items: center;
    height: 100%;
    white-space: nowrap;
    padding-right: 30px;

    // -webkit-perspective: 1000;
    //   -moz-perspective: 1000;
    //   -ms-perspective: 1000;
    //   perspective: 1000;
}

@keyframes marquee {
    0% {
        transform: translateX(0);
    }
    20% {
        transform: translateX(-10%);
    }
    40% {
        transform: translateX(-20%);
    }
    60% {
        transform: translateX(-30%);
    }
    80% {
        transform: translateX(-40%);
    }
    100% {
        transform: translateX(-50%);
    }
}
</style>

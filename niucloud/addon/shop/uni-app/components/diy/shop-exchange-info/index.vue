<template>
    <view :style="warpCss" class="shop-exchange-info-wrap">
        <view class="pl-[60rpx] pt-[71rpx] pb-[133rpx] min-h-[382rpx] box-border text-[#fff]">
            <!-- #ifdef MP-WEIXIN -->
            <view :style="navbarInnerStyle"></view>
            <!-- #endif -->
            <template v-if="token">
                <view class="text-[34rpx] leading-[48rpx]">我的积分</view>
                <view class="text-[80rpx] font-500 price-font leading-[112rpx]">{{ memberPoint.point || 0 }}</view>
                <view class="text-[24rpx] font-400 leading-[34rpx]">今日获得：{{ memberPoint.today_point || 0 }}</view>
            </template>
            <template v-else>
                <view class="pt-[42rpx] title">积分当钱花</view>
                <view class="text-[26rpx] leading-[36rpx] text-[#FEF2C0] mt-[10rpx]">做任务可领积分</view>
            </template>
        </view>
    </view>
</template>

<script lang="ts" setup>
import { computed, ref, watch, onMounted } from 'vue'
import { getExchangePoint } from '@/addon/shop/api/point';
import { img, getToken } from '@/utils/common';
import { t } from '@/locale'
import useDiyStore from '@/app/stores/diy'

const props = defineProps(['component', 'index', 'global']);

const diyStore = useDiyStore();
const loading = ref(true)
const diyComponent = computed(() => {
    if (diyStore.mode == 'decorate') {
        return diyStore.value[props.index];
    } else {
        return props.component;
    }
})

const warpCss = computed(() => {
    var style = '';
    if (diyComponent.value.bgUrl) {
        style += 'background-image:url(' + img(diyComponent.value.bgUrl) + ');';
        style += 'background-size: 100%;';
        style += 'backgroundPosition: bottom;';
        style += 'background-repeat: no-repeat;';
    }
    if (diyComponent.value.topRounded) style += 'border-top-left-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
    if (diyComponent.value.topRounded) style += 'border-top-right-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';

    return style;
})

onMounted(() => {
    refresh();
});

watch(
    () => diyComponent.value,
    (newValue, oldValue) => {
        refresh();
    }, { deep: true })

const memberPoint: Record<string, any> = ref({})

const token = ref(getToken())

const refresh = () => {
    // 装修模式
    if (diyStore.mode == 'decorate') {
        memberPoint.value = {
            point: 10500,
            today_point: 500,
        }
    } else {
        if (!token) {
            return;
        }
        getExchangePointFn()
    }
}

const getExchangePointFn = async() => {
    const res = await getExchangePoint()
    memberPoint.value = res.data
    loading.value = false
}

let menuButtonInfo: any = {};
// 如果是小程序，获取右上角胶囊的尺寸信息，避免导航栏右侧内容与胶囊重叠(支付宝小程序非本API，尚未兼容)
// #ifdef MP-WEIXIN || MP-BAIDU || MP-TOUTIAO || MP-QQ
menuButtonInfo = uni.getMenuButtonBoundingClientRect();
// #endif

// 导航栏内部盒子的样式
const navbarInnerStyle = computed(() => {
    let style = '';
    // 导航栏宽度，如果在小程序下，导航栏宽度为胶囊的左边到屏幕左边的距离
    // #ifdef MP
    if (props.global.topStatusBar.isShow == false) {
        style += 'height:' + menuButtonInfo.height + 'px;';
        style += 'padding-top:' + menuButtonInfo.top + 'px;';
    }
    // #endif
    return style;
})
</script>

<style lang="scss" scoped>
.title {
    width: 240rpx;
    height: 58rpx;
    font-family: FZLanTingHei-EB-GBK;
    font-weight: 700;
    font-size: 48rpx;
    line-height: 56rpx;
    text-align: left;
    color: transparent;
    background: linear-gradient(90.00002732075491deg, #FFFDF0 5%, #FEF1B9 88%);
    -webkit-background-clip: text; /* 确保背景色可以应用到文字上 */
    background-clip: text;
}
</style>

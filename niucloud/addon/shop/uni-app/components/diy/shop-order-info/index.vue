<template>
    <view :style="warpCss">
        <view class="diy-text relative">
            <view class="px-[var(--pad-sidebar-m)] pt-[var(--pad-top-m)] pb-[40rpx] flex items-center justify-between">
                <view @click="diyStore.toRedirect(diyComponent.link)">
                    <view class="max-w-[200rpx] truncate leading-[1] text-[30rpx]" :style="{ fontSize: diyComponent.fontSize * 2 + 'rpx', color: diyComponent.textColor, fontWeight: (diyComponent.fontWeight == 'normal' ? 500 : diyComponent.fontWeight) }">{{ diyComponent.text }}</view>
                </view>
                <view class="flex items-center">
                    <view @click="redirect({ url: '/addon/shop/pages/order/list'})" class="flex items-center">
                        <text class="max-w-[200rpx] truncate text-[24rpx]" :style="{ color: diyComponent.more.color }">{{ diyComponent.more.text }}</text>
                        <text class="nc-iconfont nc-icon-youV6xx text-[24rpx]" :style="{ color: diyComponent.more.color }"></text>
                    </view>
                </view>
            </view>
        </view>
        <view class="pb-[var(--pad-top-m)] px-[var(--pad-sidebar-m)] flex items-center justify-between text-center">
            <view class="flex flex-col items-center w-[20%] flex-shrink-0" @click="toList(1)">
                <view class="relative w-[44rpx] h-[44rpx]">
                    <image class="w-[44rpx] h-[44rpx]" :src="img('addon/shop/diy/member/order1.png')" />
                    <view v-if="orderInfo.wait_pay" :class="['absolute left-[35rpx] top-[-10rpx] rounded-[28rpx] h-[28rpx] min-w-[28rpx] text-center leading-[30rpx] bg-[#FF4646] text-[#fff] text-[20rpx] font-500 box-border', orderInfo.wait_pay > 9 ? 'px-[10rpx]' : '']">{{ orderInfo.wait_pay > 99 ? "99+" : orderInfo.wait_pay }}</view>
                </view>
                <view class="mt-[20rpx] leading-[1]" :style="{ fontSize: diyComponent.item.fontSize * 2 + 'rpx', color: diyComponent.item.color, fontWeight: diyComponent.item.fontWeight }">待付款</view>
            </view>
            <view class="flex flex-col items-center w-[20%] flex-shrink-0" @click="toList(2)">
                <view class="relative w-[44rpx] h-[44rpx]">
                    <image class="w-[44rpx] h-[44rpx]" :src="img('addon/shop/diy/member/order2.png')" />
                    <view v-if="orderInfo.wait_shipping"
                          :class="['absolute left-[35rpx] top-[-10rpx] rounded-[28rpx] h-[28rpx] min-w-[28rpx] text-center leading-[30rpx] bg-[#FF4646] text-[#fff] text-[20rpx] font-500 box-border', orderInfo.wait_shipping > 9 ? 'px-[10rpx]' : '']">
                        {{ orderInfo.wait_shipping > 99 ? "99+" : orderInfo.wait_shipping }}
                    </view>
                </view>
                <view class="mt-[20rpx] leading-[1]" :style="{ fontSize: diyComponent.item.fontSize * 2 + 'rpx', color: diyComponent.item.color, fontWeight: diyComponent.item.fontWeight }">待发货</view>
            </view>
            <view class="flex flex-col items-center w-[20%] flex-shrink-0" @click="toList(3)">
                <view class="relative w-[44rpx] h-[44rpx]">
                    <image class="w-[44rpx] h-[44rpx]" :src="img('addon/shop/diy/member/order3.png')" />
                    <view v-if="orderInfo.wait_take" :class="['absolute left-[35rpx] top-[-10rpx] rounded-[28rpx] h-[28rpx] min-w-[28rpx] text-center leading-[30rpx] bg-[#FF4646] text-[#fff] text-[20rpx] font-500 box-border', orderInfo.wait_take > 9 ? 'px-[10rpx]' : '']">{{ orderInfo.wait_take > 99 ? "99+" : orderInfo.wait_take }}</view>
                </view>
                <view class="mt-[20rpx] leading-[1]" :style="{ fontSize: diyComponent.item.fontSize * 2 + 'rpx', color: diyComponent.item.color, fontWeight: diyComponent.item.fontWeight }">待收货</view>
            </view>
            <view class="flex flex-col items-center w-[20%] flex-shrink-0" @click="toList(5)">
                <view class="relative w-[44rpx] h-[44rpx]">
                    <image class="w-[44rpx] h-[44rpx]" :src="img('addon/shop/diy/member/order4.png')" />
                    <view v-if="orderInfo.evaluate"
                          :class="['absolute left-[35rpx] top-[-10rpx] rounded-[28rpx] h-[28rpx] min-w-[28rpx] text-center leading-[30rpx] bg-[#FF4646] text-[#fff] text-[20rpx] font-500 box-border', orderInfo.evaluate > 9 ? 'px-[10rpx]' : '']">
                        {{ orderInfo.evaluate > 99 ? "99+" : orderInfo.evaluate }}
                    </view>
                </view>
                <view class="mt-[20rpx] leading-[1]" :style="{ fontSize: diyComponent.item.fontSize * 2 + 'rpx', color: diyComponent.item.color, fontWeight: diyComponent.item.fontWeight }">待评价</view>
            </view>
            <view class="flex flex-col items-center w-[20%] flex-shrink-0" @click="redirect({ url: '/addon/shop/pages/refund/list'})">
                <view class="relative w-[44rpx] h-[44rpx]">
                    <image class="w-[44rpx] h-[44rpx]" :src="img('addon/shop/diy/member/order5.png')" />
                    <view v-if="orderInfo.refund" :class="['absolute left-[35rpx] top-[-10rpx] rounded-[28rpx] h-[28rpx] min-w-[28rpx] text-center leading-[30rpx] bg-[#FF4646] text-[#fff] text-[20rpx] font-500 box-border', orderInfo.refund > 9 ? 'px-[10rpx]' : '']">{{ orderInfo.refund > 99 ? "99+" : orderInfo.refund }}</view>
                </view>
                <view class="mt-[20rpx] leading-[1]" :style="{ fontSize: diyComponent.item.fontSize * 2 + 'rpx', color: diyComponent.item.color, fontWeight: diyComponent.item.fontWeight }">售后/退款</view>
            </view>
        </view>
    </view>
</template>

<script lang="ts" setup>
import { ref, computed, watch, onMounted } from 'vue';
import useDiyStore from '@/app/stores/diy';
import { img, redirect } from '@/utils/common';
import { getShopOrderNum } from '@/addon/shop/api/order';

const props = defineProps(['component', 'index']);
const diyStore = useDiyStore();
const diyComponent = computed(() => {
    if (diyStore.mode == 'decorate') {
        return diyStore.value[props.index];
    } else {
        return props.component;
    }
})

onMounted(() => {
    refresh();
});

watch(
    () => diyComponent.value,
    (newValue, oldValue) => {
        refresh();
    }, { deep: true })

const refresh = () => {
    // 装修模式
    if (diyStore.mode == 'decorate') {
        orderInfo.value = {}
    } else {
        getShopOrderNumFn()
    }
}

const orderInfo = ref({})
const warpCss = computed(() => {
    let style = '';
    style += 'position:relative;';
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

const getShopOrderNumFn = () => {
    getShopOrderNum().then((res: any) => {
        orderInfo.value = res.data
    })
}
const toList = (status: any) => {
    redirect({ url: '/addon/shop/pages/order/list', param: { status } })
}
</script>

<style>
</style>

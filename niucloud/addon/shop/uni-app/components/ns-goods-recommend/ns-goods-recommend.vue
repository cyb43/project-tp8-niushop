<template>
    <view class="goods-recommend">
        <view class="mt-[60rpx] flex flex-col items-center sidebar-margin">
            <view class="flex items-center mb-[30rpx]" v-if="goodsList && Object.keys(goodsList).length">
                <image class="w-[38rpx] h-[22rpx]" :src="img('addon/shop_fenxiao/level/title_left.png')" mode="aspectFill" />
                <text class="text-[30rpx] mx-[18rpx] font-500 text-[#EF000C]">猜你喜欢</text>
                <image class="w-[38rpx] h-[22rpx]" :src="img('addon/shop_fenxiao/level/title_right.png')" mode="aspectFill" />
            </view>
            <diy-goods-list @loadingFn="getGoodsListFn" :component="goodsData" />
        </view>
		<view class="h-[50rpx]"></view>
    </view>
</template>

<script setup lang="ts">
import { img } from '@/utils/common';
import { ref } from 'vue'
import diyGoodsList from '@/addon/shop/components/diy/goods-list/index.vue';

// 获取商品数据
const goodsList = ref()
const getGoodsListFn = (data: any) => {
    goodsList.value = data || {}
}

// 商品列表组件
const goodsData = ref({
    style: 'style-2',
    num: 10,
    source: 'all',
    topElementRounded: 12,
    bottomElementRounded: 12,
    margin: {
        both: 10,
        bottom: 0,
        top: 0
    },
    priceStyle: {
        mainColor: "#FF4142",
        control: true
    },
    goodsNameStyle: {
        color: "#303133",
        control: true,
        fontWeight: "normal"
    },
    saleStyle: {
        color: "#999",
        control: true
    },
    labelStyle: {
        isShow: true,
        control: true
    },
    btnStyle: {
        fontWeight: false,
        padding: 0,
        aroundRadius: 25,
        textColor: "#fff",
        startBgColor: '#FF4142',
        endBgColor: '#FF4142',
        style: 'nc-icon-gouwuche1',
        control: true
    }
});
</script>

<style lang="scss" scoped>

</style>

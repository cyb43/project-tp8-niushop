<template>
    <view class="bg-[var(--page-bg-color)] min-h-screen" :style="themeColor()">
        <mescroll-body ref="mescrollRef" @init="mescrollInit" :down="{ use: false }" @up="getShopOrderFn">
            <view class="px-[var(--sidebar-m)] py-[var(--top-m)]" v-if="info.length">
                <template v-for="(item, index)  in info" :key="index">
                    <view class="card-template mb-[var(--top-m)]">
                        <view class="bg-[var(--temp-bg)] p-[20rpx] rounded-[var(--rounded-mid)] flex" @click="redirect({ url: '/addon/shop/pages/goods/detail', param: { goods_id: item.goods_id } })">
                            <u--image radius="var(--goods-rounded-mid)" width="150rpx" height="150rpx" :src="img(item.order_goods.goods_image_thumb_mid ? item.order_goods.goods_image_thumb_mid : '')" model="aspectFill">
                                <template #error>
                                    <u-icon name="photo" color="#999" size="50"></u-icon>
                                </template>
                            </u--image>
                            <view class="flex-1 flex flex-wrap ml-[20rpx] my-[4rpx]">
                                <view>
                                    <view class="text-[26rpx] leading-[40rpx] max-w-[450rpx] truncate">{{ item.order_goods.goods_name }}</view>
                                    <view class="max-w-[450rpx] mt-[14rpx] truncate text-[22rpx] text-[var(--text-color-light9)] leading-[28rpx]" v-if="item.order_goods.sku_name">{{ item.order_goods.sku_name }}</view>
                                </view>
                                <view class="mt-auto w-full flex justify-between items-center">
                                    <view class="flex items-baseline  price-font">
                                        <text class="text-[24rpx] font-500">￥</text>
                                        <text class="text-[40rpx] font-500">{{ parseFloat(item.order_goods.price).toFixed(2).split('.')[0] }}</text>
                                        <text class="text-[24rpx] font-500">.{{ parseFloat(item.order_goods.price).toFixed(2).split('.')[1] }}</text>
                                    </view>
                                    <text class="font-400 text-[28rpx] text-[#333]">x{{ item.order_goods.num }}</text>
                                </view>
                            </view>
                        </view>
                        <view class="flex items-center mt-[30rpx]">
                            <u-rate :count="5" v-model="item.scores" active-color="var(--primary-color)" :size="'36rpx'" gutter="1" readonly></u-rate>
                            <text class="ml-[16rpx] text-[28rpx] pt-[2rpx] text-[var(--primary-color)]">{{ item.scores === 1 ? '差评' : item.scores === 2 || item.scores === 3 ? '中评' : '好评' }}</text>
                        </view>
                        <view class="px-[2rpx] text-[28rpx] text-[#333] mt-[16rpx] mb-[20rpx] break-all leading-[1.5]">{{ item.content }}</view>
                        <template v-if="item.image_mid.length === 1">
                            <u--image class="rounded-[var(--goods-rounded-big)] overflow-hidden mt-[10rpx]" width="420rpx" height="420rpx" :src="img(item.image_mid[0])" model="aspectFill" @click="imgListPreview(item.images[0])">
                                <template #error>
                                    <u-icon name="photo" color="#999" size="50"></u-icon>
                                </template>
                            </u--image>
                        </template>
                        <template v-if="item.image_mid.length === 2">
                            <view class="flex justify-between mt-[10rpx]">
                                <u--image class="rounded-[var(--goods-rounded-big)] overflow-hidden" width="322rpx" height="322rpx" :src="img(item.image_mid[0])" model="aspectFill" @click="imgListPreview(item.images[0])">
                                    <template #error>
                                        <u-icon name="photo" color="#999" size="50"></u-icon>
                                    </template>
                                </u--image>
                                <u--image class="rounded-[var(--goods-rounded-big)] overflow-hidden" width="322rpx" height="322rpx" :src="img(item.image_mid[1])" model="aspectFill" @click="imgListPreview(item.images[1])">
                                    <template #error>
                                        <u-icon name="photo" color="#999" size="50"></u-icon>
                                    </template>
                                </u--image>
                            </view>
                        </template>
                        <template v-if="item.image_mid.length === 3">
                            <view class="flex justify-between mt-[10rpx]">
                                <u--image class="rounded-[var(--goods-rounded-big)] overflow-hidden" width="430rpx" height="430rpx" :src="img(item.image_mid[0])" model="aspectFill" @click="imgListPreview(item.images[0])">
                                    <template #error>
                                        <u-icon name="photo" color="#999" size="50"></u-icon>
                                    </template>
                                </u--image>
                                <view>
                                    <u--image class="rounded-[var(--goods-rounded-big)] overflow-hidden mb-[20rpx]" width="205rpx" height="205rpx" :src="img(item.image_mid[1])" model="aspectFill" @click="imgListPreview(item.images[1])">
                                        <template #error>
                                            <u-icon name="photo" color="#999" size="50"></u-icon>
                                        </template>
                                    </u--image>
                                    <u--image class="rounded-[var(--goods-rounded-big)] overflow-hidden" width="205rpx" height="205rpx" :src="img(item.image_mid[2])" model="aspectFill" @click="imgListPreview(item.images[2])">
                                        <template #error>
                                            <u-icon name="photo" color="#999" size="50"></u-icon>
                                        </template>
                                    </u--image>
                                </view>
                            </view>
                        </template>
                        <template v-if="item.image_mid.length === 4">
                            <view class="flex flex-wrap mt-[10rpx]">
                                <u--image class="rounded-[var(--goods-rounded-big)] overflow-hidden mr-[15rpx] mb-[15rpx]"
                                    width="215rpx" height="215rpx" :src="img(item.image_mid[0])" model="aspectFill"
                                    @click="imgListPreview(item.images[0])">
                                    <template #error>
                                        <u-icon name="photo" color="#999" size="50"></u-icon>
                                    </template>
                                </u--image>
                                <u--image
                                    class="rounded-[var(--goods-rounded-big)] overflow-hidden mr-[15rpx] mb-[15rpx]"
                                    width="215rpx" height="215rpx" :src="img(item.image_mid[1])" model="aspectFill"
                                    @click="imgListPreview(item.images[1])">
                                    <template #error>
                                        <u-icon name="photo" color="#999" size="50"></u-icon>
                                    </template>
                                </u--image>
                                <u--image class="rounded-[var(--goods-rounded-big)] overflow-hidden mr-[15rpx]"
                                          width="215rpx" height="215rpx" :src="img(item.image_mid[2])"
                                          model="aspectFill" @click="imgListPreview(item.images[2])">
                                    <template #error>
                                        <u-icon name="photo" color="#999" size="50"></u-icon>
                                    </template>
                                </u--image>
                                <u--image class="rounded-[var(--goods-rounded-big)] overflow-hidden mr-[15rpx]"
                                          width="215rpx" height="215rpx" :src="img(item.image_mid[3])"
                                          model="aspectFill" @click="imgListPreview(item.images[3])">
                                    <template #error>
                                        <u-icon name="photo" color="#999" size="50"></u-icon>
                                    </template>
                                </u--image>
                            </view>
                        </template>
                        <template v-if="item.image_mid.length > 4">
                            <view class="flex flex-wrap mt-[10rpx]">
                                <u--image v-for="(imageItem, imageIndex) in item.images"
                                          :class="['rounded-[var(--goods-rounded-big)] overflow-hidden mb-[10rpx]', (imageIndex + 1) % 3 === 0 ? '' : 'mr-[10rpx]']"
                                          width="211rpx" height="211rpx" :src="img(imageItem)" model="aspectFill"
                                          @click="imgListPreview(imageItem)">
                                    <template #error>
                                        <u-icon name="photo" color="#999" size="50"></u-icon>
                                    </template>
                                </u--image>
                            </view>
                        </template>
                        <view v-if="item.explain_first !=''" class="text-[26rpx] !text-[var(--text-color-light6)] mt-[20rpx] pt-[20rpx] border-0 border-t-[2rpx] border-solid border-[#F1F2F5] w-[100%] overflow-clip leading-[34rpx] break-all">
                            <text class="text-[var(--primary-color)]">商家回复：</text>
                            <text>{{ item.explain_first }}</text>
                        </view>
                    </view>
                </template>
            </view>
            <mescroll-empty v-if="!info.length && !loading" :option="{tip : '暂无评价'}"></mescroll-empty>
            <loading-page :loading="loading"></loading-page>
        </mescroll-body>
    </view>
</template>
<script lang="ts" setup>
import { ref } from 'vue';
import { getOrderEvaluate } from '@/addon/shop/api/evaluate';
import { onLoad, onUnload, onPageScroll, onReachBottom } from '@dcloudio/uni-app';
import { img, redirect, goback } from '@/utils/common';
import useMescroll from '@/components/mescroll/hooks/useMescroll.js';
import MescrollEmpty from '@/components/mescroll/mescroll-empty/mescroll-empty.vue';

const { mescrollInit, downCallback, getMescroll } = useMescroll(onPageScroll, onReachBottom);
const info = ref<Array<any>>([])
const loading = ref(true)
onLoad((option: any) => {
    if (option.order_id) {
        getOrderEvaluateFn(option.order_id)
    } else {
        let parameter = {
            url: '/addon/shop/pages/evaluate/list',
            title: '缺少订单id'
        };
        goback(parameter)
    }
})
const getOrderEvaluateFn = (id: any) => {
    loading.value = true
    getOrderEvaluate(id).then((res: any) => {
        info.value = res.data
        loading.value = false
    }).catch(() => {
        loading.value = false
    })
}

//预览图片
const imgListPreview = (item: any) => {
    if (item === '') return false
    const urlList = [];
    urlList.push(img(item))  //push中的参数为 :src="item.img_url" 中的图片地址
    uni.previewImage({
        indicator: "number",
        loop: true,
        urls: urlList
    })
}

// 关闭预览图片
onUnload(() => {
    // #ifdef  H5 || APP
    try {
        uni.closePreviewImage()
    } catch (e) {

    }
    // #endif
})
</script>

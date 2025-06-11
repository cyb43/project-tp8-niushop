<template>
    <u-popup :show="show" @close="show = false" mode="bottom" :round="10">
        <view @touchmove.prevent.stop class="popup-common bg-[var(--page-bg-color)]" v-if="goodsDetail">
            <view class="absolute top-[30rpx] right-[20rpx] z-10 p-[10rpx] bg-[rgba(255,255,255,.4)] rounded-full border-[2rpx] border-solid border-transparent box-border nc-iconfont nc-icon-guanbiV6xx2 font-bold text-[#303133] text-[36rpx]"
            :class="{'border-[#d8d8d8]': detailHeadBgChange}" @click="show = false"></view>
            <scroll-view scroll-y="true" class="h-[80vh]" @scroll="scrollFn">
                <view class="w-full transition-transform duration-300 ease-linear transform translate-x-0">
                    <view class="swiper-box">
                        <u-swiper :list="goodsDetail.goods.goods_image"
                                  :indicator="goodsDetail.goods.goods_image.length"
                                  :indicatorStyle="{'bottom': '70rpx'}" :autoplay="true"
                                  height="100vw" radius="0"></u-swiper>
                    </view>
                </view>
                <view class="bg-[var(--page-bg-color)] rounded-tr-[40rpx] rounded-tl-[40rpx] overflow-hidden -mt-[34rpx] relative">
                    <view class="flex flex-col px-[30rpx] pt-[30rpx]">
                        <view class="text-[var(--price-text-color)] flex items-baseline mb-[12rpx]">
                            <view class="inline-block goods-price-time">
                                <text class="price-font text-[32rpx]">￥</text>
                                <text class="price-font text-[48rpx]">{{ parseFloat(goodsDetail.impulse_buy_price).toFixed(2).split('.')[0] }}</text>
                                <text class="price-font text-[32rpx] mr-[10rpx]">.{{ parseFloat(goodsDetail.impulse_buy_price).toFixed(2).split('.')[1] }}</text>
                            </view>
                            <!-- <text class="text-[26rpx] text-[var(--text-color-light9)] line-through price-font" v-if="goodsDetail.market_price && parseFloat(goodsDetail.market_price)">
                            ￥{{ goodsDetail.market_price }}
                            </text> -->
                        </view>
                        <view class="text-[#333] font-medium text-[30rpx] multi-hidden leading-[40rpx]">
                            {{ goodsDetail.goods.goods_name }}
                        </view>
                        <view class="flex justify-between items-start mt-[24rpx]">
                            <view class="text-[24rpx] leading-[34rpx] text-[var(--text-color-light6)]" v-if="goodsDetail.sku && goodsDetail.sku.sku_name">
                                <text class="whitespace-nowrap mr-[4rpx]">已选:</text>
                                <text>{{ goodsDetail.sku.sku_name }}</text>
                            </view>
                            <view class="text-[24rpx] leading-[34rpx] text-[var(--text-color-light6)]">
                                <text class="whitespace-nowrap mr-[4rpx]">库存:</text>
                                <text>{{ goodsDetail.sku.stock }}</text>
                                <text>{{ goodsDetail.goods.unit }}</text>
                            </view>
                        </view>
                    </view>
                    <view class="mt-[40rpx] sidebar-margin card-template px-[var(--pad-sidebar-m)]">
                        <view class="title !py-[0] !text-left !text-[30rpx]">商品详情</view>
                        <view class="u-content">
                            <u-parse :content="goodsDetail.goods.goods_desc" :tagStyle="{img: 'vertical-align: top;',p:'overflow: hidden;word-break:break-word;' }"></u-parse>
                        </view>
                    </view>
                </view>
            </scroll-view>

            <view class="btn-wrap">
                <button class="primary-btn-bg btn" :class="{'opacity-80': propData && propData.num}" @click="goodsDetailSave">确定</button>
            </view>
        </view>
    </u-popup>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { getShopImpulseBuyDetail } from '@/addon/shop/api/impulse_buy'
import { t } from '@/locale'
import { redirect, img, deepClone } from '@/utils/common'

const show = ref(false)
const loading = ref(false)
const goodsDetail: any = ref()
const propData = ref()
const open = (data: any) => {
    goodsDetail.value = ''
    show.value = true;
    propData.value = data
    getDetailFn({impulse_buy_goods_id: data.impulse_buy_goods_id})
}
const getDetailFn = (parse:any)=>{
    getShopImpulseBuyDetail(parse).then(({ data }) => {
        goodsDetail.value = deepClone(data)
        goodsDetail.value.goods.goods_image = goodsDetail.value.goods.goods_image_thumb_big;
        goodsDetail.value.goods.goods_image.forEach((item: any, index: any) => {
            goodsDetail.value.goods.goods_image[index] = img(item);
        })
        loading.value = false
    })
}

//预览图片
const imgListPreview = (item: any, index: any) => {
    if (Array.isArray(item)) {
        if (!item.length) return false
        var urlList = item;
        uni.previewImage({
            indicator: "number",
            current: index,
            loop: true,
            urls: urlList
        })
    } else {
        if (item === '') return false
        const urlList = []
        urlList.push(img(item))  //push中的参数为 :src="item.img_url" 中的图片地址
        uni.previewImage({
            indicator: "number",
            loop: true,
            urls: urlList
        })
    }

}

const goodsDetailSave = () => {
    if(propData.value && propData.value.num){

    }
    show.value = false
}

const detailHeadBgChange = ref(false) // 详情头部背景渐变
const scrollFn = (e: any) => {
    let scrollTop = e.detail.scrollTop;
    if (scrollTop > 375) {
        detailHeadBgChange.value = true;
    } else {
        detailHeadBgChange.value = false;
    }
}

defineExpose({
    open
})
</script>

<style lang="scss" scoped>
</style>

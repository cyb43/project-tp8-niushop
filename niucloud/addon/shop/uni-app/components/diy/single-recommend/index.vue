<template>
    <view :style="warpCss" class="overflow-hidden" v-if="goodsList && goodsList[0]">
        <view class="flex justify-between items-center mb-[20rpx]" v-if="diyComponent.textImg || diyComponent.subTitle.text">
            <view class="h-[34rpx] flex items-center" v-if="diyComponent.textImg" @click="diyStore.toRedirect(diyComponent.textLink)">
                <image class="h-[100%] w-[auto]" :src="img(diyComponent.textImg)" mode="heightFix" />
            </view>
            <view class="flex items-center ml-[auto]" v-if="diyComponent.subTitle.text" @click="diyStore.toRedirect(diyComponent.subTitle.link)" :style="{'color': diyComponent.subTitle.textColor}">
                <text class="text-[24rpx]">{{ diyComponent.subTitle.text }}</text>
                <text class="text-[22rpx] iconfont iconxiangyoujiantou"></text>
            </view>
        </view>

        <view class="flex justify-between">
            <!-- 轮播图 -->
            <view class="relative w-[340rpx] overflow-hidden" :style="carouselCss">
                <view v-if="diyComponent.list.length == 1" class="leading-0 overflow-hidden">
                    <view @click="diyStore.toRedirect(diyComponent.list[0].link)">
                        <image v-if="diyComponent.list[0].imageUrl" :src="img(diyComponent.list[0].imageUrl)" mode="heightFix" class="h-[504rpx] !w-full" :show-menu-by-longpress="true" />
                        <image v-else :src="img('static/resource/images/diy/figure.png')" mode="heightFix" class="h-[504rpx] !w-full" :show-menu-by-longpress="true" />
                    </view>
                </view>
                <template v-else>
                    <swiper class="swiper ns-indicator-dots-three h-[504rpx]" autoplay="true" circular="true"
                            :indicator-dots="isShowDots" @change="swiperChange"
                            :indicator-color="diyComponent.indicatorColor"
                            :indicator-active-color="diyComponent.indicatorActiveColor">
                        <swiper-item class="swiper-item" v-for="(item) in diyComponent.list" :key="item.id">
                            <view @click="diyStore.toRedirect(item.link)">
                                <view class="item h-[504rpx]">
                                    <image v-if="item.imageUrl" :src="img(item.imageUrl)" mode="scaleToFill" class="w-full h-full" :show-menu-by-longpress="true" />
                                    <image v-else :src="img('static/resource/images/diy/figure.png')" mode="scaleToFill" class="w-full h-full" :show-menu-by-longpress="true" />
                                </view>
                            </view>
                        </swiper-item>
                    </swiper>
                    <!-- #ifdef MP-WEIXIN -->
                    <view v-if="diyComponent.list.length > 1" class="swiper-dot-box straightLineStyle2">
                        <view v-for="(numItem, numIndex) in diyComponent.list" :key="numIndex" :class="['swiper-dot', { active: numIndex == swiperIndex }]" :style="[numIndex == swiperIndex ? { backgroundColor: diyComponent.indicatorActiveColor } : { backgroundColor: diyComponent.indicatorColor }]"></view>
                    </view>
                    <!-- #endif -->
                </template>
            </view>

            <view class="w-[340rpx] h-[504rpx] flex flex-col bg-[#fff] box-border overflow-hidden" :style="goodsTempCss" @click="toLink(goodsList[0])">
                <view :style="goodsImgCss" class="w-[346rpx] h-[350rpx] overflow-hidden">
                    <u--image width="346rpx" height="350rpx" :src="img(goodsList[0].goods_cover_thumb_mid || '')" model="aspectFill">
                        <template #error>
                            <image class="w-[346rpx] h-[350rpx]" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill" />
                        </template>
                    </u--image>
                </view>
                <view class="px-[16rpx] flex-1 pt-[16rpx] pb-[20rpx] flex flex-col justify-between">
                    <view class="text-[#303133] leading-[40rpx] text-[28rpx] truncate" :style="{ color : diyComponent.goodsNameStyle.color, fontWeight : diyComponent.goodsNameStyle.fontWeight }">{{ goodsList[0].goods_name }}</view>
                    <view class="flex justify-between flex-wrap items-baseline mt-[28rpx]">
                        <view class="flex items-center">
                            <view class="text-[var(--price-text-color)] price-font truncate max-w-[200rpx]" :style="{ color : diyComponent.priceStyle.mainColor }">
                                <text class="text-[24rpx] font-400">￥</text>
                                <text class="text-[40rpx] font-500">{{ parseFloat(diyGoods.goodsPrice(goodsList[0])).toFixed(2).split('.')[0] }}</text>
                                <text class="text-[24rpx] font-500">.{{ parseFloat(diyGoods.goodsPrice(goodsList[0])).toFixed(2).split('.')[1] }}</text>
                            </view>
							<image v-if="diyGoods.priceType(goodsList[0]) == 'member_price'" class="max-w-[50rpx] h-[28rpx] ml-[6rpx]" :src="img('addon/shop/VIP.png')" mode="heightFix" />
							<image v-else-if="diyGoods.priceType(goodsList[0]) == 'newcomer_price'"  class="max-w-[60rpx] h-[28rpx] ml-[6rpx]" :src="img('addon/shop/newcomer.png')" mode="heightFix" />
							<image v-else-if="diyGoods.priceType(goodsList[0]) == 'discount_price'" class="max-w-[80rpx] h-[28rpx] ml-[6rpx]" :src="img('addon/shop/discount.png')" mode="heightFix" />	 
                        </view>
                        <view class="w-[44rpx] h-[44rpx] bg-[red] flex items-center justify-center rounded-[50%]" :style="{ backgroundColor : diyComponent.saleStyle.color }">
                            <text class="iconfont iconjia  font-500 text-[32rpx] text-[#fff]"></text>
                        </view>
                    </view>
                </view>
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
// 精选推荐
import { ref, reactive, computed, watch, onMounted } from 'vue';
import { redirect, img } from '@/utils/common';
import useDiyStore from '@/app/stores/diy';
import { getGoodsComponents } from '@/addon/shop/api/goods';
import { useGoods } from '@/addon/shop/hooks/useGoods'

const diyGoods = useGoods();
const props = defineProps(['component', 'index', 'value']);
const diyStore = useDiyStore();

const goodsList = ref<Array<any>>([]);

const diyComponent = computed(() => {
    if (props.value) {
        return props.value;
    } else if (diyStore.mode == 'decorate') {
        return diyStore.value[props.index];
    } else {
        return props.component;
    }
})

// 轮播指示器
let isShowDots = ref(true)
// #ifdef H5
isShowDots.value = true;
// #endif

// #ifdef MP-WEIXIN
isShowDots.value = false;
// #endif

const warpCss = computed(() => {
    var style = '';
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

const goodsTempCss = computed(() => {
    var style = '';
    if (diyComponent.value.elementBgColor) style += 'background-color:' + diyComponent.value.elementBgColor + ';';
    if (diyComponent.value.topElementRounded) style += 'border-top-left-radius:' + diyComponent.value.topElementRounded * 2 + 'rpx;';
    if (diyComponent.value.topElementRounded) style += 'border-top-right-radius:' + diyComponent.value.topElementRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomElementRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomElementRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomElementRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomElementRounded * 2 + 'rpx;';

    if (diyComponent.value.margin && diyComponent.value.margin.both) style += 'width: calc((100vw - ' + (diyComponent.value.margin.both * 4) + 'rpx - 20rpx) / 2);'
    else style += 'width: calc((100vw - 20rpx) / 2 );'
    return style;
})

const goodsImgCss = computed(() => {
    var style = '';
    if (diyComponent.value.topElementRounded) style += 'border-radius:' + diyComponent.value.topElementRounded * 2 + 'rpx;';
    return style;
})

const carouselCss = computed(() => {
    var style = '';
    if (diyComponent.value.topCarouselRounded) style += 'border-top-left-radius:' + diyComponent.value.topCarouselRounded * 2 + 'rpx;';
    if (diyComponent.value.topCarouselRounded) style += 'border-top-right-radius:' + diyComponent.value.topCarouselRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomCarouselRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomCarouselRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomCarouselRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomCarouselRounded * 2 + 'rpx;';

    if (diyComponent.value.margin && diyComponent.value.margin.both) style += 'width: calc((100vw - ' + (diyComponent.value.margin.both * 4) + 'rpx - 20rpx) / 2);'
    else style += 'width: calc((100vw - 20rpx) / 2 );'
    return style;
})

const getGoodsListFn = () => {
    let data = {
        num: 1,
        goods_ids: diyComponent.value.source == 'custom' ? diyComponent.value.goods_ids : ''
    }
    getGoodsComponents(data).then((res) => {
        goodsList.value = res.data;
    });
}

onMounted(() => {
    refresh();
    // 装修模式下刷新
    if (diyStore.mode != 'decorate') {
        watch(
            () => diyComponent.value,
            (newValue, oldValue) => {
                refresh();
            },
            { deep: true }
        )
    }
});

const refresh = () => {
    // 装修模式下设置默认图
    if (diyStore.mode == 'decorate') {
        let obj = {
            goods_cover_thumb_mid: "",
            goods_name: "商品名称",
            sale_num: "100",
            unit: "件",
            goodsSku: { show_price: 100 }
        };
        goodsList.value.push(obj);
    } else {
        getGoodsListFn();
    }
}

const toLink = (data: any) => {
    redirect({ url: '/addon/shop/pages/goods/detail', param: { goods_id: data.goods_id } })
}

const swiperIndex = ref(0);
const swiperChange = e => {
    swiperIndex.value = e.detail.current;
};
</script>

<style lang="scss" scoped>
.swiper.ns-indicator-dots-three :deep(.uni-swiper-dots-horizontal) {
    bottom: 12rpx;
}

.swiper.ns-indicator-dots-three :deep(.uni-swiper-dot) {
    width: 8rpx;
    height: 8rpx;
    border-radius: 8rpx;
    margin-right: 14rpx;
}

.swiper.ns-indicator-dots-three :deep(.uni-swiper-dot):last-of-type {
    margin-right: 0;
}

.swiper.ns-indicator-dots-three :deep(.uni-swiper-dot-active) {
    width: 30rpx;
}

.swiper-dot-box {
    position: absolute;
    bottom: 4rpx;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 80rpx 8rpx;
    box-sizing: border-box;

    .swiper-dot {
        background-color: #b2b2b2;
        width: 10rpx;
        border-radius: 50%;
        height: 10rpx;
        margin: 8rpx;
    }

    &.straightLineStyle2 {
        .swiper-dot {
            width: 8rpx;
            height: 8rpx;
            border-radius: 8rpx;
            margin: 0;
            margin-right: 14rpx;

            &.last-of-type {
                margin-right: 0;
            }

            &.active {
                width: 30rpx;
            }
        }
    }
}
</style>

<template>
    <view class="bg-[var(--page-bg-color)] min-h-[100vh]" :style="themeColor()">
        <view class="fixed left-0 right-0 top-0 product-warp bg-[#fff]">

            <view class="py-[14rpx] flex items-center justify-between px-[20rpx]">
                <view class="flex-1 search-input">
                    <text @click.stop="searchTypeFn('all')" class="nc-iconfont nc-icon-sousuo-duanV6xx1 btn"></text>
                    <input class="input" maxlength="50" type="text" v-model="goods_name" placeholder="请搜索您想要的商品"
                           placeholderClass="text-[var(--text-color-light9)] text-[24rpx]" confirm-type="search"
                           @confirm="searchTypeFn('all')" />
                    <text v-if="goods_name" class="nc-iconfont nc-icon-cuohaoV6xx1 clear" @click="goods_name=''"></text>
                </view>
            </view>
            <view class="h-[88rpx] px-[30rpx]">
                <view class=" flex items-center justify-between text-[26rpx] text-[var(--text-color-light6)] h-[88rpx]">
                    <text :class="{ '!text-[var(--primary-color)] font-500': searchType == 'total_order_num' }" @click="searchTypeFn('total_order_num')">综合排序</text>
                    <view class="flex items-center" @click="searchTypeFn('total_exchange_num')">
                        <text class="mr-[4rpx]" :class="{ '!text-[var(--primary-color)] font-500': searchType == 'total_exchange_num' }">销量</text>
                        <text v-if="sale_num == 'asc'"
                              class="text-[16rpx] text-[var(--text-color-light6)] nc-iconfont nc-icon-a-xiangshangV6xx1"
                              :class="{ '!text-[var(--primary-color)]': searchType == 'total_exchange_num' }"></text>
                        <text v-else
                              class="text-[16rpx] text-[var(--text-color-light6)] nc-iconfont nc-icon-a-xiangxiaV6xx1"
                              :class="{ '!text-[var(--primary-color)]': searchType == 'total_exchange_num' }"></text>
                    </view>
                    <view class="flex items-center" @click="searchTypeFn('price')">
                        <text class="mr-[4rpx]" :class="{'!text-[var(--primary-color)] font-500': searchType == 'price' }">价格</text>
                        <text v-if="price == 'asc'"
                              class="text-[16rpx] text-[var(--text-color-light6)] nc-iconfont nc-icon-a-xiangshangV6xx1"
                              :class="{'!text-[var(--primary-color)]': searchType == 'price' }"></text>
                        <text v-else
                              class="text-[16rpx] text-[var(--text-color-light6)] nc-iconfont nc-icon-a-xiangxiaV6xx1"
                              :class="{'!text-[var(--primary-color)]': searchType == 'price' }"></text>
                    </view>
                </view>
            </view>
        </view>

        <mescroll-body ref="mescrollRef" top="176rpx" bottom="60px" @init="mescrollInit" :down="{ use: false }" @up="getAllAppListFn">
            <view v-if="goodsList.length" class="sidebar-margin flex justify-between flex-wrap">
                <template v-for="(item, index) in goodsList">
                    <view class="goods-item-style-two flex flex-col bg-[#fff] box-border rounded-[var(--rounded-mid)] overflow-hidden mt-[var(--top-m)]" @click="toDetail(item.id)">
<!--                        <easy-image class="w-[100%] h-[350rpx]" image-class="rounded-tl-[var(&#45;&#45;rounded-mid)] rounded-tr-[var(&#45;&#45;rounded-mid)]"-->
<!--                                    :image-src="item.goods_cover_thumb_small" />-->
                        <image v-if="item.goods_cover_thumb_mid"
                               class="w-[100%] h-[350rpx] rounded-tl-[var(--rounded-mid)] rounded-tr-[var(--rounded-mid)]"
                               :src="img(item.goods_cover_thumb_mid)" :mode="'aspectFill'"
                               @error="item.goods_cover_thumb_mid='static/resource/images/diy/shop_default.jpg'"/>
                        <image v-else
                               class="w-[100%] h-[350rpx] rounded-tl-[var(--rounded-mid)] rounded-tr-[var(--rounded-mid)]"
                               :src="img('static/resource/images/diy/shop_default.jpg')" :mode="'aspectFill'"/>
                        <view class="px-[16rpx] flex-1 pt-[10rpx] pb-[20rpx] flex flex-col justify-between">
                         <!--   <view class="text-[#333] leading-[40rpx] text-[28rpx] multi-hidden">{{ item.names }}</view> -->
						 <view class="text-[28rpx] text-[#333] leading-[40rpx] multi-hidden mb-[10rpx]">
						     <view class="brand-tag" v-if="item.goods_brand" :style="diyGoods.baseTagStyle(item.goods_brand)">{{ item.goods_brand.brand_name }}</view>
						     {{ item.names }}
						 </view>
						 <view class="text-[24rpx] text-[#999] leading-[30rpx] using-hidden mb-[8rrpx]">
						     {{ item.sub_title }}
						 </view>
						 <view v-if="item.goods_label && item.goods_label.length" class="flex flex-wrap">
						     <template v-for="(tagItem, tagIndex) in item.goods_label">
						         <image class="img-tag" v-if="tagItem.style_type == 'icon' && tagItem.icon" :src="img(tagItem.icon)" mode="heightFix" @error="diyGoods.error(tagItem,'icon')"/>
						         <view class="base-tag" v-else-if="tagItem.style_type == 'diy' || !tagItem.icon" :style="diyGoods.baseTagStyle(tagItem)">
						             {{ tagItem.label_name }}
						         </view>
						     </template>
						 </view>
                            <view class="text-[24rpx] font-400 leading-[34rpx] mt-[10rpx] text-[var(--text-color-light9)]">已兑{{ item.total_exchange_num }}人</view>
                            <view class="flex justify-between flex-wrap items-center mt-[16rpx]">
                                <view class="flex flex-col">
                                    <view class="text-[var(--price-text-color)] price-font ml-[2rpx] flex items-center">
                                        <text class="text-[32rpx]">{{ item.point }}</text>
                                        <text class="text-[26rpx] ml-[4rpx]">积分</text>
                                    </view>
                                    <view v-if="item.price&&item.price>0" class="flex items-center price-font mt-[6rpx]">
                                        <text class="text-[var(--price-text-color)] font-400 text-[32rpx]">+{{ parseFloat(item.price).toFixed(2) }}</text>
                                        <text class="text-[var(--price-text-color)] font-400 ml-[4rpx] text-[20rpx]">元</text>
                                    </view>
                                </view>
                                <view class="w-[120rpx] h-[54rpx] text-[22rpx] flex-center !text-[#fff] m-0 rounded-full primary-btn-bg remove-border text-center" shape="circle">去兑换</view>
                            </view>
                        </view>
                    </view>
                </template>
            </view>
            <mescroll-empty v-if="!goodsList.length && loading" :option="{tip : '暂无商品'}"></mescroll-empty>
        </mescroll-body>

        <tabbar />
    </view>
</template>

<script setup lang="ts">
import { reactive, ref, onMounted } from 'vue'
import { t } from '@/locale'
import { redirect, img } from '@/utils/common';
import { getExchangeGoodsList } from '@/addon/shop/api/point';
import MescrollBody from '@/components/mescroll/mescroll-body/mescroll-body.vue';
import MescrollEmpty from '@/components/mescroll/mescroll-empty/mescroll-empty.vue';
import useMescroll from '@/components/mescroll/hooks/useMescroll.js';
import { onShow, onPageScroll, onReachBottom } from '@dcloudio/uni-app';
import { useGoods } from '@/addon/shop/hooks/useGoods'
const diyGoods = useGoods();
const { mescrollInit, downCallback, getMescroll } = useMescroll(onPageScroll, onReachBottom);
const goodsList = ref<Array<any>>([]);
const coupon_id = ref<number | string>('');
const currGoodsCategory = ref<number | string>('');
const mescrollRef = ref(null);
const loading = ref<boolean>(false);
const goods_name = ref("");
const price = ref("");
const sale_num = ref("");
const searchType = ref('total_order_num');

interface mescrollStructure {
    num: number,
    size: number,
    endSuccess: Function,

    [propName: string]: any
}

const getAllAppListFn = (mescroll: mescrollStructure) => {
    loading.value = false;
    let data: object = {
        goods_category: currGoodsCategory.value,
        page: mescroll.num,
        limit: mescroll.size,
        names: goods_name.value,
        coupon_id: coupon_id.value,
        order: searchType.value === 'total_order_num' ? '' : searchType.value,
        sort: searchType.value == 'price' ? price.value : sale_num.value
    };
    getExchangeGoodsList(data).then((res: any) => {
        let newArr = (res.data.data as Array<Object>);
        //设置列表数据
        if (Number(mescroll.num) === 1) {
            goodsList.value = []; //如果是第一页需手动制空列表
        }
        goodsList.value = goodsList.value.concat(newArr);
        mescroll.endSuccess(newArr.length);
        loading.value = true;
    }).catch(() => {
        loading.value = true;
        mescroll.endErr(); // 请求失败, 结束加载
    })
}

// 搜索
const searchTypeFn = (type: string) => {
    searchType.value = type;
    if (type == 'total_order_num') {
        sale_num.value = '';
        price.value = '';
    }
    if (type == 'price') {
        sale_num.value = '';
        if (price.value) {
            price.value = price.value == 'asc' ? 'desc' : 'asc';
        } else {
            price.value = 'asc';
        }
    }
    if (type == 'total_exchange_num') {
        price.value = '';
        if (sale_num.value) {
            sale_num.value = sale_num.value == 'asc' ? 'desc' : 'asc';
        } else {
            sale_num.value = 'asc';
        }
    }
    goodsList.value = [];
    getMescroll().resetUpScroll();
}
const toDetail = (id: string | number) => {
    redirect({ url: '/addon/shop/pages/point/detail', param: { id: id }, mode: 'navigateTo' })
}
onMounted(() => {
    setTimeout(() => {
        getMescroll().optUp.textNoMore = t("end");
    }, 500)
});
</script>

<style lang="scss" scoped>
@import '@/addon/shop/styles/common.scss';
.bg-color {
    background: linear-gradient(180deg, #EF000C 16%, rgba(239, 0, 12, 0) 92%);
}

.nav-item.active {
    color: $u-primary;
}

.scroll-view-wrap {
    word-break: keep-all;
}

.text-color {
    color: var(--primary-color);
}

.label-select {
    color: var(--primary-color);
    border-color: var(--primary-color);
    background-color: var(--primary-color-light);
}

:deep(.u-popup .u-transition) {
    top: 156rpx !important;
}

.product-warp {
    z-index: 99999;
}

:deep(.tab-bar-placeholder) {
    display: none !important;
}

:deep(.u-tabbar__placeholder) {
    display: none !important;
}

:deep(.u-input__content__clear) {
    width: 28rpx;
    height: 28rpx;
    font-size: 28rpx;
    background-color: var(--text-color-light9);
}

.goods-item-style-two {
    width: calc(50% - 10rpx);
}
</style>

<template>
    <view class="discount bg-[var(--page-bg-color)] min-h-[100vh]" :style="themeColor()">
        <view class="fixed top-0 left-0 w-full z-10 text-[0]">
            <!-- #ifdef MP-WEIXIN -->
            <view class="absolute top-0 left-0 right-0 z-999">
                <top-tabbar :data="param" class="top-header" />
            </view>
            <!-- #endif -->
            <u-swiper v-if="bannerList.length" :list="imgList" :indicator="bannerList.length" :indicatorStyle="{'bottom': '60rpx',}" :autoplay="true" :height="headStyle" @click="toRedirect"></u-swiper>
            <image v-if="!bannerListLoading&&!bannerList.length" :src="img('addon/shop/discount/discount_banner.png')" mode="scaleToFill" class="w-full" :style="{height: headStyle}" :show-menu-by-longpress="true" />
            <view class="relative w-full h-[110rpx] mt-[-40rpx] z-5" v-if="discountList.length">
                <view class="bg-[var(--primary-color)] w-[750rpx] rounded-tl-[24rpx] rounded-tr-[24rpx] h-[96rpx] absolute left-0 bottom-[1rpx]"></view>
                <scroll-view :scroll-x="true" class="h-[110rpx] absolute left-0 bottom-0 z-5">
                    <view class="flex items-end h-[100%]" :style="{'width':187.5*discountList.length+'rpx'}">
                        <view class="w-[187.5rpx] h-[100rpx] relative flex-shrink-0" v-for="(item,index) in discountList" @click="navClick(item)">
                            <view class="w-full absolute left-0 top-0 z-10 text-[#fff] text-center pt-[14rpx]">
                                <view class="text-[28rpx] leading-[39rpx] font-500 px-[10rpx] h-[39rpx] overflow-hidden" :class="{'!text-[#333]':active==item.discount_id}">{{ item.remark }}</view>
                                <view class="flex justify-center w-full">
                                    <text class="text-[22rpx] h-[36rpx] flex-center mt-[5rpx]" :class="{'active flex items-center justify-center':active==item.discount_id}">{{ item.status == 'not_active' ? '预告' : item.status_name }}</text>
                                </view>
                            </view>
                            <template v-if="active==item.discount_id">
                                <image v-if="discountList.length<4" class="absolute bottom-0 z-5 h-[110rpx] z-5"
                                       :class="{'left-0 w-[230rpx]':index==0,'left-[-41.25rpx] w-[270rpx]':index!=0}"
                                       :src="img(index==0?'addon/shop/discount/nav-left.png':'addon/shop/discount/nav-center.png')" />
                                <image v-if="discountList.length>=4" class="absolute bottom-0 z-5 h-[110rpx] z-5"
                                       :class="{'left-0 w-[230rpx]':index==0,'left-[-41.25rpx] w-[270rpx]':index!=0&&index!=discountList.length-1 ,'right-0 w-[230rpx]':index==discountList.length-1}"
                                       :src="img(index==0?'addon/shop/discount/nav-left.png':index==discountList.length-1?'addon/shop/discount/nav-right.png':'addon/shop/discount/nav-center.png')" />
                            </template>
                        </view>
                    </view>
                </scroll-view>
            </view>

        </view>

        <mescroll-body v-if="discountList.length" ref="mescrollRef" :top="mescrollTop" @init="mescrollInit" :down="{ use: false }" @up="getActiveDiscountGoodsListFn">
            <view class="sidebar-margin py-[var(--top-m)] bg-[#F4F6F8]">
         
                <template v-for="(item,index) in list" :key="index">
                 
                    <view class="bg-[#fff] p-[20rpx] flex rounded-[var(--rounded-big)]" :class="{'mb-[var(--top-m)]':index<list.length-1}" @click="toLink(item)">
                        <view class="w-[240rpx] h-[240rpx] rounded-[var(--goods-rounded-big)] overflow-hidden">
                            <u--image width="240rpx" height="240rpx" :radius="'var(--goods-rounded-big)'" :src="img(item.goods_cover ? item.goods_cover_thumb_mid : '')" model="aspectFill">
                                <template #error>
                                    <image class="rounded-[var(--goods-rounded-big)] overflow-hidden w-[240rpx] h-[240rpx]" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill" />
                                </template>
                            </u--image>
                        </view>

                        <view class="ml-[20rpx] flex-1 flex flex-col justify-between">
							<view>
								<view class="text-[28rpx] leading-[1.4] multi-hidden">{{ item.goods_name }}</view>
								<view class="text-[24rpx] text-[#999] leading-[30rpx] using-hidden mt-[16rpx]">
									{{ item.sub_title }}
								</view>
							</view>
                            <view v-if="item.goods_label_name && item.goods_label_name.length" class="flex flex-wrap mb-[auto]">
                                <template v-for="(tagItem, tagIndex) in item.goods_label_name">
                                    <image class="img-tag" v-if="tagItem.style_type == 'icon' && tagItem.icon" :src="img(tagItem.icon)" mode="heightFix" @error="diyGoods.error(tagItem,'icon')" />
                                    <view class="base-tag" v-else-if="tagItem.style_type == 'diy' || !tagItem.icon" :style="diyGoods.baseTagStyle(tagItem)">{{ tagItem.label_name }}</view>
                                </template>
                            </view>

                            <view
                                class="relative overflow-hidden w-full h-[88rpx] flex justify-between mt-[20rpx] rounded-[100rpx]"
                                :class="{'bg-[var(--primary-color-light)]': item.discount_status=='active','bg-[#FFF6F1]': item.discount_status!='active' }">
                                <view class="mr-[20rpx] pl-[30rpx] flex-1 flex flex-col justify-center">
                                    <view class="flex items-end">
                                        <view class="text-[var(--price-text-color)] flex items-baseline" :class="{'!text-[var(--primary-color)]':item.discount_status!='active'}">
                                            <text class="text-[26rpx] leading-[26rpx] font-500 mr-[4rpx] price-font">￥</text>
                                            <text class="text-[44rpx] leading-[40rpx] font-500 price-font">{{ parseFloat(item.goodsSku.discount_price).toFixed(2).split('.')[0] }}.</text>
                                            <text class="text-[26rpx] leading-[28rpx] font-500 price-font">{{ parseFloat(item.goodsSku.discount_price).toFixed(2).split('.')[1] }}</text>
                                        </view>
                                        <view v-if="item.goodsSku.discount_rate<10"
                                              class="mb-[4rpx] text-[var(--price-text-color)] px-[4rpx] border-[1rpx] border-[var(--primary-color)] border-solid  text-[18rpx] ml-[4rpx] rounded-[4rpx] leading-[24rpx]"
                                              :class="{'!border-[var(--primary-color)]':item.discount_status!='active'}">{{ item.goodsSku.discount_rate }}折</view>
                                    </view>
                                    <view class="flex items-center mt-[4rpx]">
                                        <view class="w-[20rpx] h-[20rpx] mr-[4rpx] rounded-[20rpx] text-[#fff] bg-[var(--primary-color)] flex items-center justify-center"
                                            :class="{'!bg-[var(--primary-color)]':item.discount_status!='active'}">
                                            <text class="text-[10rpx] nc-icon-biaoqianV6mm1 nc-iconfont"></text>
                                        </view>
                                        <view class="text-[18rpx] font-400 text-[var(--price-text-color)] leading-[24rpx]"
                                            :class="{'!text-[var(--primary-color)]':item.discount_status!='active'}">已省{{ item.goodsSku.reduce_money }}元</view>
                                    </view>
                                </view>
                                <view class="discount-btn text-[var(--primary-color)] iconfont iconUnion" v-if="item.discount_status!='active'">
                                    <text class="desc">{{ item.discount_status_name }}</text>
                                </view>
                                <view class="discount-btn text-[var(--primary-color)] iconfont iconUnion" v-else>
                                    <text class="icon iconfont iconqiang"></text>
                                    <text class="arrow iconxiayibu iconfont"></text>
                                </view>
                            </view>
                        </view>
                    </view>
                </template>
                <mescroll-empty v-if="!list.length && !loading" :option="{tip : '暂无商品，请看看其他商品吧！'}"></mescroll-empty>
            </view>
        </mescroll-body>

        <view v-if="!discountList.length && !loading" class="h-[calc(100vh-550rpx)]" :style="{'padding-top':mescrollTop}">
            <mescroll-empty :option="{tip : '暂无商品，请看看其他商品吧！','btnText': '去逛逛'}" @emptyclick="redirect({ url: '/addon/shop/pages/goods/list' })"></mescroll-empty>
        </view>
        <loading-page :loading="bannerListLoading"></loading-page>
    </view>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useLogin } from '@/hooks/useLogin'
import { img, redirect, getToken, currRoute, diyRedirect, pxToRpx } from '@/utils/common';
import { getActiveDiscountConfig, getActiveDiscountList, getActiveDiscountGoodsList } from '@/addon/shop/api/discount'
import MescrollBody from '@/components/mescroll/mescroll-body/mescroll-body.vue'
import MescrollEmpty from '@/components/mescroll/mescroll-empty/mescroll-empty.vue'
import useMescroll from '@/components/mescroll/hooks/useMescroll.js'
import { onPageScroll, onReachBottom } from '@dcloudio/uni-app'
import { topTabar } from '@/utils/topTabbar'
import { useGoods } from '@/addon/shop/hooks/useGoods'
import useSystemStore from "@/stores/system";

const systemStore = useSystemStore()
const diyGoods = useGoods();
const headStyle = computed(() => {
    let style = Object.keys(systemStore.menuButtonInfo).length ? (pxToRpx(Number(systemStore.menuButtonInfo.height)) + pxToRpx(systemStore.menuButtonInfo.top) + pxToRpx(8) + 368) + 'rpx' : '490rpx'
    return style
})

const { mescrollInit, downCallback, getMescroll } = useMescroll(onPageScroll, onReachBottom)
const bannerList: any = ref<Array<Object>>([])
const discountList = ref<Array<Object>>([])
const active = ref<number>(0)
const active_name = ref<String>('')
const list = ref<Array<Object>>([]);
const loading = ref<boolean>(true);
const bannerListLoading = ref<boolean>(true);

/********* 自定义头部 - start ***********/
const topTabarObj = topTabar()
let param = topTabarObj.setTopTabbarParam({ title: '限时折扣' })
/********* 自定义头部 - end ***********/

const imgList: any = ref([])

const getActiveDiscountConfigFn = () => {
    bannerListLoading.value = true
    getActiveDiscountConfig().then((res: any) => {
        bannerList.value = res.data
        imgList.value = bannerList.value.map((el: any) => img(el.imageUrl))
        bannerListLoading.value = false
    }).catch(() => {
        bannerListLoading.value = false
    })
}

getActiveDiscountConfigFn()
const getActiveDiscountListFn = () => {
    getActiveDiscountList({}).then((res: any) => {
        discountList.value = res.data
        calculateHeight();
        if (discountList.value && discountList.value.length) {
            navClick(res.data[0]);
        } else {
            loading.value = false
        }
    })
}

getActiveDiscountListFn()
const navClick = (item: any) => {
    active.value = item.discount_id
    active_name.value = item.status_name
    getMescroll()?.resetUpScroll();
    uni.pageScrollTo({
        scrollTop: 0, //距离页面顶部的距离
        duration: 1
    });
}
const getActiveDiscountGoodsListFn = (mescroll: any) => {
    if (discountList.value.length == 0) return;

    loading.value = true;
    let data: object = {
        page: mescroll.num,
        limit: mescroll.size,
        discount_id: active.value
    };

    getActiveDiscountGoodsList(data).then((res: any) => {
        let newArr = (res.data.data as Array<Object>).map((el: any) => {
            return el
        })
        //设置列表数据
        if (mescroll.num == 1) {
            list.value = []; //如果是第一页需手动制空列表
        }
        list.value = list.value.concat(newArr);
        mescroll.endSuccess(newArr.length);
        loading.value = false;
    }).catch(() => {
        loading.value = false;
        mescroll.endErr(); // 请求失败, 结束加载
    })
}
const toRedirect = (index: any) => {
    let data = bannerList.value[index].toLink
    if (Object.keys(data).length) {
        if (!data.name) return;
        if (currRoute() == 'app/pages/member/index' && !getToken()) {
            useLogin().setLoginBack({ url: data.url })
            return;
        }
        diyRedirect(data);
    }
}

const toLink = (item: any) => {
    if (item.discount_status != 'active') {
        uni.showToast({ title: `活动${ item.discount_status_name }`, icon: 'none' })
        return;
    }
    redirect({ url: '/addon/shop/pages/goods/detail', param: { sku_id: item.goodsSku.sku_id, type: 'discount' } })
}

const mescrollTop = ref('')
const calculateHeight = () => {
    mescrollTop.value = Object.keys(systemStore.menuButtonInfo).length ? (pxToRpx(Number(systemStore.menuButtonInfo.height)) + pxToRpx(systemStore.menuButtonInfo.top) + pxToRpx(8) + 368) + (discountList.value.length ? 70 : 0) + 'rpx' : (discountList.value.length ? '560rpx' : '490rpx')
}
</script>

<style lang="scss" scoped>
@import '@/addon/shop/styles/common.scss';

.swiper.ns-indicator-dots :deep(.uni-swiper-dots-horizontal) {
    bottom: 40rpx
}

.swiper.ns-indicator-dots :deep(.uni-swiper-dot) {
    background-color: #fff;
    opacity: 0.5;
}

.swiper.ns-indicator-dots :deep(.uni-swiper-dot-active) {
    background-color: #fff;
    opacity: 1;
}

.active {
    padding: 0 14rpx;
    line-height: 34rpx;
    border-radius: 29rpx;
    background: linear-gradient(94deg, var(--primary-color) 0%, var(--primary-color) 99%), #EF000C;
}

.active-button {
    background-size: cover;
    background-repeat: no-repeat;
}

:deep(.u-swiper-indicator__wrapper--line__bar) {
    height: 5rpx !important;
}

:deep(.u-swiper-indicator__wrapper--line) {
    height: 5rpx !important;
}

.discount-btn {
    font-size: 88rpx;
    margin-left: auto;
    position: absolute;
    right: -2rpx;
    display: flex;
    line-height: 1;

    .icon {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        left: 40rpx;
        color: #fff;
        font-size: 44rpx;
    }

    .arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        color: #fff;
        right: 18rpx;
        font-size: 16rpx;
    }

    .desc {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        left: 34rpx;
        color: #fff;
        font-size: 26rpx;
    }
}
</style>

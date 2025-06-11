<template>
    <view class="bg-[var(--page-bg-color)] min-h-[100vh] overflow-hidden" :style="themeColor()">
        <view class="coupon-header fixed  left-0 right-0 top-0 z-10080">
            <!-- #ifdef MP-WEIXIN -->
            <view :style="{height: headStyle, backgroundImage: 'url(' + img('addon/shop/coupon/coupon_uniapp.png') + ')',backgroundSize: '100%', backgroundPosition: 'bottom', backgroundRepeat: 'no-repeat'}">
                <top-tabbar :data="param" class="top-header" />
            </view>
            <!-- #endif -->
            <!-- #ifdef H5 -->
            <view class="h-[364rpx]" :style="{ backgroundImage: 'url(' + img('addon/shop/coupon/coupon_uniapp.png') + ')',backgroundSize: 'cover', backgroundPosition: 'bottom',backgroundRepeat: 'no-repeat'}"></view>
            <!-- #endif -->
            <view class="-mt-[-36rpx] px-[var(--sidebar-m)] py-[24rpx] flex items-center justify-between leading-[40rpx] text-[28rpx] bg-[var(--page-bg-color)] rounded-t-[26rpx] relative z-99999 !pl-[30rpx]" :class="{'!bg-[#fff]': typePopup}">
                <text :class="{ 'text-primary font-500': searchType == 'all' }" @click="searchTypeFn('all')">默认排序</text>
                <view class="flex items-center" :class="{ 'text-primary font-500': searchType == 'create_time' }" @click="searchTypeFn('create_time')">
                    <text class="mr-[4rpx]">最新</text>
                    <text v-if="create_time == 'asc'" class="text-[18rpx] text-[var(--text-color-light6)] nc-iconfont nc-icon-a-xiangshangV6xx1" :class="{ '!text-primary': searchType == 'create_time' }"></text>
                    <text v-else class="text-[18rpx] text-[var(--text-color-light6)]  nc-iconfont nc-icon-a-xiangxiaV6xx1" :class="{ '!text-primary': searchType == 'create_time' }"></text>
                </view>
                <view class="flex items-center" :class="{ 'text-primary font-500': searchType == 'price' }" @click="searchTypeFn('price')">
                    <text class="mr-[4rpx]">价格</text>
                    <text v-if="price == 'asc'" class="text-[18rpx]  text-[var(--text-color-light6)] nc-iconfont nc-icon-a-xiangshangV6xx1" :class="{ '!text-primary': searchType == 'price' }"></text>
                    <text v-else class="text-[18rpx] text-[var(--text-color-light6)] nc-iconfont nc-icon-a-xiangxiaV6xx1" :class="{ '!text-primary': searchType == 'price' }"></text>
                </view>
                <view class="flex items-center" :class="{'text-primary font-500': searchType == 'type' }" @click="searchTypeFn('type')">
                    <view class="w-[2rpx] h-[28rpx] bg-gradient-to-b from-[#333] to-[#fff] mr-[20rpx] flex-shrink-0"></view>
                    <text class="mr-[10rpx]">筛选</text>
                    <text class="nc-iconfont color-[var(--text-color-light6)] nc-icon-shaixuanV6xx text-[28rpx]"></text>
                </view>
            </view>
        </view>
        <mescroll-body ref="mescrollRef" @init="mescrollInit" :down="{ use: false }" height="auto" @up="getShopCouponListFn" :top="mescrollTop">
            <view v-if="list.length" class="pb-[var(--top-m)] sidebar-margin">
                <template v-for="(item, index) in list">
                    <view v-if="item.btnType === 'collected'"
                          class="flex items-center relative w-[100%] rounded-[var(--rounded-big)] overflow-hidden bg-[#fff] py-[20rpx] background-size"
                          :class="{'mt-[var(--top-m)]':index}"
                          :style="{ backgroundImage: 'url(' + img('addon/shop/coupon/coupn_loot.png') + ')'}"
                          @click="toDetail(item.id)">
                        <view
                            class="box-border flex-1 border-0 border-r-[1px] border-[#FFDCDC] border-dashed flex items-center">
                            <view class="w-[164rpx] box-border flex justify-center ">
                                <view class="flex items-baseline text-[var(--price-text-color)]">
                                    <text class="text-[28rpx] leading-[34rpx] text-center font-400 price-font mr-[4rpx]">￥</text>
                                    <text class="text-[54rpx] font-500 text-left leading-[70rpx] max-w-[136rpx] price-font">{{ item.coupon_price }}</text>
                                </view>
                            </view>
                            <view class="flex-1 box-border ml-[10rpx]">
                                <view class="text-[26rpx] leading-[42rpx] text-left font-500">
                                    <text v-if="item.min_condition_money === '0.00'">无门槛</text>
                                    <text v-else>满{{ item.coupon_min_price }}元可用</text>
                                </view>
                                <view class="mt-[10rpx] text-left flex items-center">
                                    <text class="w-[80rpx] text-center bg-[var(--primary-color-light)] whitespace-nowrap text-[var(--primary-color)] text-[18rpx] h-[30rpx] leading-[30rpx] rounded-[16rpx] mr-[10rpx] flex-shrink-0">{{ item.type_name }}</text>
                                    <text class="text-[24rpx] truncate max-w-[190rpx] leading-[30rpx] text-[var(--text-color-light6)]">{{ item.title }}</text>
                                </view>
                                <view
                                    class="w-[100%] mt-[10rpx] text-[20rpx] leading-[30rpx] text-[var(--text-color-light6)]">
                                    <text v-if="item.valid_type == 1">领取之日起{{ item.length || '' }}天内有效</text>
                                    <text v-else> 有效期至{{ item.valid_end_time ? item.valid_end_time.slice(0, 10) : '' }}</text>
                                </view>
                            </view>
                        </view>
                        <view class="pr-[20rpx] pl-[34rpx]">
                            <button class="flex-center" :style="{width:'150rpx',height:'60rpx',color:'#fff', fontSize:'24rpx', padding:'0',backgroundColor:'var(--primary-help-color4)', border:'none' ,opacity :'1',borderRadius:'30rpx'}" disabled>已领完</button>
                        </view>
                        <view class="absolute top-0 right-[190rpx]  h-[10rpx] w-[20rpx] rounded-br-[20rpx] rounded-bl-[20rpx] bg-[var(--page-bg-color)] "></view>
                        <view class="absolute bottom-0 right-[190rpx] h-[10rpx] w-[20rpx] rounded-tr-[20rpx] rounded-tl-[20rpx] bg-[var(--page-bg-color)]"></view>
                    </view>
                    <view v-else class="flex items-center relative w-[100%] rounded-[var(--rounded-big)] overflow-hidden bg-[#fff] py-[20rpx] background-size" :class="{'mt-[var(--top-m)]':index}" @click="toDetail(item.id)">
                        <view class="relative box-border flex-1 border-0 border-r-[1px] border-[#FFDCDC] border-dashed flex items-center pl-[10rpx]">
                            <view class="w-[164rpx] box-border flex justify-center">
                                <view class="flex items-baseline text-[var(--price-text-color)]">
                                    <text class="text-[28rpx] leading-[34rpx] text-center font-400 price-font  mr-[4rpx]">￥</text>
                                    <text class="text-[54rpx] font-500 text-left leading-[70rpx] max-w-[136rpx] price-font">{{ item.coupon_price }}</text>
                                </view>
                            </view>
                            <view class="flex-1 box-border ml-[10rpx]">
                                <view class="text-[26rpx] leading-[42rpx] text-left font-500">
                                    <text v-if="item.min_condition_money === '0.00'">无门槛</text>
                                    <text v-else>满{{ item.coupon_min_price }}元可用</text>
                                </view>
                                <view class="mt-[10rpx] text-left flex items-center">
                                    <text class="w-[80rpx] bg-[var(--primary-color-light)] whitespace-nowrap text-[var(--primary-color)] text-[18rpx] h-[30rpx] leading-[30rpx] text-center rounded-[16rpx] mr-[10rpx] flex-shrink-0">{{ item.type_name }}</text>
                                    <text class="text-[24rpx] truncate max-w-[190rpx] leading-[30rpx] text-[var(--text-color-light9)]">{{ item.title }}</text>
                                </view>
                                <view class="w-[100%] mt-[6rpx] text-[20rpx] leading-[30rpx] text-[var(--text-color-light9)]">
                                    <text v-if="item.valid_type == 1">领取之日起<text>{{ item.length || '' }}</text>天内有效</text>
                                    <text v-else> 有效期至<text>{{ item.valid_end_time ? item.valid_end_time.slice(0, 10) : '' }}</text></text>
                                </view>
                            </view>
                        </view>
                        <view v-if="item.btnType === 'collecting'" @click.stop="collecting(item.id, index)" class="pr-[20rpx] pl-[34rpx]">
                            <button class="flex-center" :style="{width:'150rpx',height:'60rpx',color:'#fff', fontSize:'24rpx', padding:'0', backgroundColor:'var(--primary-color)',border:'none',borderRadius:'30rpx'}">立即领取</button>
                        </view>
                        <view v-if="item.btnType === 'using'" @click.stop="toLink(item.id)" class="pr-[20rpx] pl-[34rpx]">
                            <button class="flex-center" :style="{width:'150rpx',height:'60rpx',color:'var(--primary-color)', fontSize:'24rpx', padding:'0',backgroundColor:'transparent',border:'2rpx solid var(--primary-color)',borderRadius:'30rpx'}">去使用</button>
                        </view>
                        <view class="absolute top-0 right-[190rpx]  h-[10rpx] w-[20rpx] rounded-br-[20rpx] rounded-bl-[20rpx] bg-[var(--page-bg-color)] "></view>
                        <view class="absolute bottom-0 right-[190rpx] h-[10rpx] w-[20rpx] rounded-tr-[20rpx] rounded-tl-[20rpx] bg-[var(--page-bg-color)]"></view>
                    </view>
                </template>
                <!-- <view :style="{'height': nullPageHeight}" class="noData bg-[#fff] rounded-[var(--rounded-big)] flex items-center justify-center"> -->
                <!-- </view> -->
            </view>
            <!-- <view> -->
            <mescroll-empty v-if="!list.length && !loading" :option="{tip : '暂无优惠券',btnText:'去逛逛'}" @emptyclick="redirect({ url: '/addon/shop/pages/goods/list' })"></mescroll-empty>
            <!-- </view> -->
        </mescroll-body>

        <u-popup :show="typePopup" mode="top" @close="typePopup = false" :customStyle="{top:typePopupTopVal}" :safeAreaInsetBottom="false">
            <view @touchmove.prevent.stop>
                <scroll-view :scroll-x="true" scroll-with-animation :scroll-into-view="'id' + (subActive ? subActive - 1 : 0)" class="px-[var(--sidebar-m)]  box-border bg-white rounded-b-[26rpx]">
                    <view class="items-center flex py-[20rpx]  border-0 border-t-[2rpx] border-solid border-[#F0F2F8]">
                        <text
                            class="flex-shrink-0 w-[120rpx] h-[50rpx] text-[24rpx] leading-[50rpx] text-center text-[#333] bg-[var(--temp-bg)] rounded-[30rpx] border-box mr-[20rpx] border-[2rpx] border-solid border-[#F8F9FD]"
                            :class="{'!text-primary !border-primary !bg-[var(--primary-color-light)] font-500':item.value == curType}"
                            v-for="(item,index) in typeList" :key="index" :id="'id' + index"
                            @click="typeClick(index,item.value)">{{ item.label }}</text>
                    </view>
                </scroll-view>
            </view>
        </u-popup>
    </view>
</template>

<script setup lang="ts">
import { ref, computed, nextTick, getCurrentInstance, watch } from 'vue'
import { img, redirect, pxToRpx, getToken } from '@/utils/common'
import { topTabar } from '@/utils/topTabbar'
import { getShopCouponList, getCoupon, getMyCouponType } from '@/addon/shop/api/coupon'
import MescrollBody from '@/components/mescroll/mescroll-body/mescroll-body.vue'
import MescrollEmpty from '@/components/mescroll/mescroll-empty/mescroll-empty.vue'
import useMescroll from '@/components/mescroll/hooks/useMescroll.js'
import { onLoad, onPageScroll, onReachBottom, onShow } from '@dcloudio/uni-app'
import useMemberStore from '@/stores/member'
import { useLogin } from '@/hooks/useLogin'
import { t } from '@/locale'

const { mescrollInit, downCallback, getMescroll } = useMescroll(onPageScroll, onReachBottom)
// 获取系统状态栏的高度
let menuButtonInfo: any = {};
// 如果是小程序，获取右上角胶囊的尺寸信息，避免导航栏右侧内容与胶囊重叠(支付宝小程序非本API，尚未兼容)
// #ifdef MP-WEIXIN || MP-BAIDU || MP-TOUTIAO || MP-QQ
menuButtonInfo = uni.getMenuButtonBoundingClientRect();
// #endif
/********* 自定义头部 - start ***********/
const topTabarObj = topTabar()
let param = topTabarObj.setTopTabbarParam({ title: '优惠券列表' })
/********* 自定义头部 - end ***********/

// 头部图片的高度
const headStyle = computed(() => {
    let style = (pxToRpx(Number(menuButtonInfo.height)) + pxToRpx(menuButtonInfo.top) + pxToRpx(8) + 364) + 'rpx'
    return style
})

const mescrollTop = computed(() => {
    let style = Object.keys(menuButtonInfo).length ? (pxToRpx(Number(menuButtonInfo.height)) + pxToRpx(menuButtonInfo.top) + pxToRpx(8) + 416) + 'rpx' : '416rpx'
    return style
})

const instance = getCurrentInstance();
let typePopupTopVal = ref()
const typePopupTopFn = () => {
    nextTick(() => {
        setTimeout(() => {
            const query = uni.createSelectorQuery().in(instance);
            query.select('.coupon-header').boundingClientRect((data: any) => {
                typePopupTopVal.value = data.height + 'px';
            }).exec();
        })
    })
}
typePopupTopFn();

// // 获取空页面高度
// const nullPageHeight = computed(() => {
// 	let hei = '';
// 	// #ifndef  H5
// 	// 屏幕高度 - 图片高度 - 上下padding + 内容框上移高度 - 苹果手机安全距离
// 	hei = `calc(100vh - ${mescrollTop.value} - 40rpx)`
// 	// #endif
// 	// #ifdef H5
// 	// 屏幕高度 - 图片高度 - 上下padding + 内容框上移高度 - 苹果手机安全距离
// 	hei = `calc(100vh - 416rpx)`
// 	// #endif
// 	return hei
// })

const list: any = ref<Array<Object>>([]);
const loading = ref<boolean>(true);
const memberStore = useMemberStore()
const userInfo = computed(() => memberStore.info)

watch(() => userInfo.value, (newValue, oldValue) => {
    if (newValue) {
        if (getMescroll()) getMescroll().resetUpScroll();
    }
}, { immediate: true, deep: true })

const getShopCouponListFn = (mescroll: any) => {
    loading.value = true;
    let data: object = {
        page: mescroll.num,
        limit: mescroll.size,
        order: searchType.value === 'all' ? '' : searchType.value,
        sort: searchType.value == 'price' ? price.value : create_time.value,
        type: curType.value || ''
    };

    getShopCouponList(data).then((res: any) => {
        let newArr = (res.data.data as Array<Object>).map((el: any) => {
            if (el.receive_type == 2) { //receive_type 后台发放
                el.btnType = 'collected'//已领完
            } else {
                if (!getToken()) {
                    if (el.sum_count != -1 && el.receive_count === el.sum_count) {
                        el.btnType = 'collected'//已领完
                    } else {
                        el.btnType = 'collecting'//领用
                    }
                } else {
                    if (el.sum_count != -1 && el.receive_count === el.sum_count) {
                        el.btnType = 'collected'//已领完
                    } else if (el.is_receive && el.limit_count === el.member_receive_count) {
                        el.btnType = 'using'//去使用
                    } else {
                        el.btnType = 'collecting'//领用
                    }
                }
            }
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

const collecting = (coupon_id: any, index: number) => {
    if (!userInfo.value) {
        useLogin().setLoginBack({ url: '/addon/shop/pages/coupon/list' })
        return false
    }
    getCoupon({ coupon_id, number: 1, type: 'receive' }).then((res: any) => {
        if (res.code > 0) {
            list.value[index].member_receive_count += 1
            list.value[index].receive_count += 1
            if (list.value[index].member_receive_count == list.value[index].limit_count
                || (list.value[index].sum_count != -1 && list.value[index].receive_count === list.value[index].sum_count)
            ) {
                list.value[index].btnType = 'using'
            }
        }

    })
}

const toDetail = (coupon_id: any) => {
    redirect({ url: '/addon/shop/pages/coupon/detail', param: { coupon_id } })
}
const toLink = (coupon_id: any) => {
    redirect({ url: '/addon/shop/pages/goods/list', param: { coupon_id } })
}

// 优惠劵
const price = ref('');
const create_time = ref('');
const searchType = ref('all');
// 类型
const subActive = ref<number>(0)
const curType = ref('')
const typeList = ref<Array<Object>>([])
const typePopup = ref<boolean>(false)
const getMyCouponTypeFn = () => {
    getMyCouponType().then((res: any) => {
        const obj = { label: '全部', value: '' };
        typeList.value.push(obj)
        typeList.value = typeList.value.concat(res.data)
    })
}
onLoad(() => {
    getMyCouponTypeFn()
})

const typeClick = (index: number, data: any) => {
    subActive.value = index
    curType.value = data
    list.value = []
    getMescroll().resetUpScroll()
    typePopup.value = false
}
// 筛选
const searchTypeFn = (type: any) => {
    searchType.value = type;
    if (type == 'all') {
        create_time.value = '';
        price.value = '';
    }
    if (type == 'price') {
        create_time.value = '';
        if (price.value) {
            price.value = price.value == 'asc' ? 'desc' : 'asc';
        } else {
            price.value = 'asc';
        }
    }
    if (type == 'create_time') {
        price.value = '';
        if (create_time.value) {
            create_time.value = create_time.value == 'asc' ? 'desc' : 'asc';
        } else {
            create_time.value = 'asc';
        }
    }
    if (type == 'type') {
        create_time.value = 'asc';
        price.value = 'asc';
        typePopup.value = true;

    } else {
        typePopup.value = false;
        list.value = [];

        getMescroll().resetUpScroll();
    }
}

</script>

<style lang="scss" scoped>
button {
    box-sizing: border-box;

    &::after {
        display: none;
    }
}

.background-size {
    background-repeat: no-repeat;
    background-position: right top;
    background-size: 27%;
}

:deep(.u-popup .u-popup__content) {
    border-bottom-left-radius: 26rpx;
    border-bottom-right-radius: 26rpx;
}

:deep(.mescroll-empty) {
    margin-top: 0 !important;
}
</style>

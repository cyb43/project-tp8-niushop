<template>
    <x-skeleton :type="skeleton.type" :loading="skeleton.loading" :config="skeleton.config" v-if="couponList && Object.keys(couponList).length > 0">
        <view :style="warpCss" class="overflow-hidden">

            <view v-if="diyComponent.style == 'style-1'" class="coupon-wrap style-1 relative">
                <scroll-view scroll-x="true" class="coupon-list" :style="{'background-image':'url(' + img('addon/shop/diy/goods_coupon/style1_bg2.png') + ')','background-size':'100%','background-repeat':'no-repeat'}">
                    <view class="coupon-class">
                        <template v-if="couponList.length > 1">
                            <view v-for="(item,index) in couponList" :key="index"
                                  class="rounded-[16rpx] box-border pt-[14rpx] inline-flex flex-col items-center relative w-[150rpx] h-[130rpx]"
                                  :class="{'mr-[20rpx]': index != couponList.length-1}"
                                  :style="{'background-image':'url(' + img('addon/shop/diy/goods_coupon/coupon_item_bg.png') + ')','background-size':'100%','background-repeat':'no-repeat'}"
                                  @click="couponItemLink(item)">
                                <view
                                    class="truncate w-full flex items-baseline justify-center price-font text-[var(--price-text-color)]">
                                    <text class="text-[26rpx] font-500">￥</text>
                                    <text class="text-[36rpx] truncate font-500">{{ parseFloat(item.price) }}</text>
                                </view>
                                <view class="text-[#303133] text-[20rpx] mt-[12rpx]">{{ item.min_condition_money == '0.00' ? '无门槛' : ('满' + parseFloat(item.min_condition_money) + '元可用') }}</view>
                                <view class="mt-[auto] rounded-b-[12rpx] text-[#f2333c] text-[20rpx] w-[100%] h-[36rpx] flex items-center justify-center bg-[#fff5f2]">{{ item.type_name }}</view>
                            </view>
                        </template>
                        <template v-else>
                            <view v-for="(item,index) in couponList" :key="index"
                                  class="rounded-[16rpx] box-border pt-[14rpx] pl-[44rpx] pr-[44rpx] inline-flex items-center justify-between relative w-[100%] h-[130rpx]"
                                  :style="{'background-image':'url(' + img('addon/shop/diy/goods_coupon/style1_bg4.png') + ')','background-size':'100%','background-repeat':'no-repeat'}"
                                  @click="couponItemLink(item)">
                                <view class="flex price-font text-[var(--price-text-color)] items-baseline">
                                    <text class="text-[36rpx] mt-[16rpx] mr-[4rpx]">￥</text>
                                    <text class="text-[85rpx] font-500 max-w-[170rpx] truncate">{{ parseFloat(item.price) }}</text>
                                </view>
                                <view class="border-0 border-dashed border-r-[2rpx] border-[#FF323C] absolute left-[40%] top-[46rpx] bottom-0 w-[2rpx]"></view>
                                <view class="w-[270rpx]">
                                    <view class="flex items-center mt-[auto]">
                                        <text class="rounded-[4rpx] bg-[#fff3f0] text-[#f2333c] border-[2rpx] border-solid border-[#f2333c] text-[22rpx] px-[6rpx] pb-[4rpx] pt-[6rpx] flex items-center justify-center whitespace-nowrap">{{ item.type_name }}</text>
                                        <text class="ml-[4rpx] text-[#f2333c] max-w-[184rpx] truncate">{{ item.title }}</text>
                                    </view>
                                    <view class="text-[#f2333c] text-[30rpx] font-500 mt-[10rpx] w-[270rpx] truncate">{{ item.min_condition_money == '0.00' ? '无门槛' : ('消费满' + parseFloat(item.min_condition_money) + '元可用') }}</view>
                                </view>
                            </view>
                        </template>
                    </view>
                </scroll-view>
                <view
                    class="w-[100%] h-[130rpx] pt-[24rpx] px-[26rpx] box-border flex items-center justify-between absolute left-0 right-0 bottom-0"
                    :style="{'background-image':'url(' + img('addon/shop/diy/goods_coupon/style1_bg.png') + ')','background-size':'100% 130rpx','background-repeat':'no-repeat'}">
                    <view class="flex flex-col">
                        <text class="text-[30rpx] text-[#fff] font-400">{{ diyComponent.couponTitle }}</text>
                        <text class="text-[20rpx] text-[rgba(255,255,255,.8)] mt-[10rpx]">{{ diyComponent.couponSubTitle }}</text>
                    </view>
                    <text v-if="diyComponent.btnText" @click="toLink('/addon/shop/pages/coupon/list')"
                          class="bg-[#fff] flex items-center justify-center text-[#FF4142] text-[22rpx] min-w-[100rpx] px-[24rpx] box-border h-[50rpx] coupon-buy-btn">
                        {{ diyComponent.btnText }}
                    </text>
                </view>
            </view>

            <view v-else-if="diyComponent.style == 'style-2'" class="coupon-wrap style-2 relative">
                <scroll-view scroll-x="true" class="coupon-list">
                    <view v-for="(item,index) in couponList" :key="index"
                          class="box-border pt-[14rpx] inline-flex flex-col items-center relative w-[140rpx] h-[130rpx] rounded-[10rpx]"
                          :class="{'mr-[20rpx]': index != couponList.length-1, 'mr-[290rpx]': index == couponList.length-1}"
                          :style="{'background-image':'url(' + img('addon/shop/diy/goods_coupon/coupon_item_bg.png') + ')','background-size':'100%','background-repeat':'no-repeat'}"
                          @click="couponItemLink(item)">
                        <view class="flex items-baseline justify-center w-full truncate price-font text-[var(--price-text-color)]">
                            <text class="text-[24rpx]">￥</text>
                            <text class="text-[38rpx] font-bold truncate">{{ parseFloat(item.price) }}</text>
                        </view>
                        <view class="text-[#303133] text-[20rpx] truncate max-w-[120rpx] mt-[12rpx]">{{ item.min_condition_money == '0.00' ? '无门槛' : ('满' + parseFloat(item.min_condition_money) + '元可用') }}</view>
                        <view class="mt-[auto] rounded-b-[12rpx] text-[#f2333c] text-[20rpx] w-[100%] h-[36rpx] flex items-center justify-center bg-[#fff5f2]">{{ item.type_name }}</view>
                    </view>
                </scroll-view>
                <view
                    class="w-[290rpx] h-[170rpx] py-[20rpx] pl-[30rpx] box-border flex flex-col items-center justify-between absolute right-0 bottom-0"
                    :style="{'background-image':'url(' + img('addon/shop/diy/goods_coupon/style2_bg.png') + ')','background-size':'290rpx 170rpx','background-repeat':'no-repeat'}">
                    <text class="text-[30rpx] text-[#fff] font-500">{{ diyComponent.couponTitle }}</text>
                    <text class="text-[20rpx] text-[rgba(255,255,255,.8)] mt-[14rpx]">{{ diyComponent.couponSubTitle }}</text>
                    <text v-if="diyComponent.btnText" @click="toLink('/addon/shop/pages/coupon/list')" class="bg-[#fff] text-[#FF4142] text-[22rpx] min-w-[100rpx] px-[24rpx] box-border h-[50rpx] leading-[50rpx] text-center coupon-buy-btn mt-auto">{{ diyComponent.btnText }}</text>
                </view>
            </view>

            <view v-else-if="diyComponent.style == 'style-3'" class="coupon-wrap style-3 relative"
                  :style="{'background-image':'url(' + img('addon/shop/diy/goods_coupon/style3_bg.jpg') + ')','background-size':'100% 204rpx','background-repeat':'no-repeat'}">
                <view class="desc flex flex-col">
                    <text class="text-[30rpx] text-[#fff] font-500">{{ diyComponent.couponTitle }}</text>
                    <text class="text-[22rpx] text-[rgba(255,255,255,.8)] mt-[10rpx]">{{ diyComponent.couponSubTitle }}</text>
                    <text v-if="diyComponent.btnText" @click="toLink('/addon/shop/pages/coupon/list')"
                          class="bg-[#fff] text-[#FF4142] text-[24rpx] w-[140rpx] box-border h-[50rpx] leading-[50rpx] text-center coupon-buy-btn mt-auto">
                        {{ diyComponent.btnText }}
                    </text>
                </view>
                <scroll-view scroll-x="true" class="coupon-list" v-if="couponList.length > 1">
                    <view v-for="(item,index) in couponList" :key="index"
                          class="bg-[#fff] box-border p-[8rpx] pb-[12rpx] inline-flex flex-col items-center relative rounded-[20rpx] ml-[12rpx]"
                          @click="couponItemLink(item)">
                        <view class="coupon-item-content">
                            <view class="text-[20rpx] text-[#fff]">{{ item.type_name }}</view>
                            <view class="mt-[auto] flex items-baseline justify-center w-full truncate price-font text-[#fff]">
                                <text class="text-[24rpx]">￥</text>
                                <text class="text-[44rpx] font-bold truncate">{{ parseFloat(item.price) }}</text>
                            </view>
                        </view>
                        <view class="text-[#303133] text-[22rpx] truncate max-w-[120rpx] mt-[12rpx]">{{ item.min_condition_money == '0.00' ? '无门槛' : ('满' + parseFloat(item.min_condition_money) + '元可用') }}</view>
                    </view>
                </scroll-view>
                <view v-if="couponList.length == 1"
                      class="bg-[#fff] box-border p-[10rpx] relative rounded-[20rpx] single-coupon flex-1"
                      @click="couponItemLink(couponList[0])">
                    <view class="flex items-center coupon-item-content">
                        <view class="coupon-left flex items-center justify-center text-[#fff] w-[156rpx] mr-[30rpx]">
                            <text class="text-[24rpx]">￥</text>
                            <text class="text-[44rpx] font-[500]">{{ parseFloat(couponList[0].price) }}</text>
                        </view>
                        <view class="flex flex-col">
                            <view class="text-[#fff] text-[28rpx] mb-[14rpx]">{{ couponList[0].min_condition_money == '0.00' ? '无门槛' : ('满' + parseFloat(couponList[0].min_condition_money) + '元可用') }}</view>
                            <view class="flex items-center">
                                <text class="bg-[#fff] mr-[10rpx] text-[red] text-[20rpx] px-[10rpx] py-[8rpx] rounded-[20rpx]">{{ couponList[0].type_name }}</text>
                                <text class="text-[#fff] text-[24rpx]">店铺优惠券</text>
                            </view>
                        </view>
                    </view>
                </view>
            </view>
            <view v-else-if="diyComponent.style == 'style-4'" class="coupon-wrap style-4 relative" :style="couponStyle4Css">
                <view class="desc flex items-center pt-[6rpx] pb-[26rpx]" @click="toLink('/addon/shop/pages/coupon/list')">
                    <text class="text-[32rpx] text-[#fff] font-500" :style="{color: diyComponent.titleColor}">{{ diyComponent.couponTitle }}</text>
                    <text class="text-[22rpx] text-[rgba(255,255,255,.8)] ml-[10rpx]" :style="{color: diyComponent.subTitleColor}">{{ diyComponent.couponSubTitle }}</text>
                </view>
                <scroll-view scroll-x="true" class="coupon-list">
                    <view v-for="(item,index) in couponList" :key="index"
                          class="px-[10rpx] h-[120rpx] inline-flex items-center relative mr-[12rpx] coupon-item box-border min-w-[310rpx]"
                          :style="{'background-color': diyComponent.couponItem.bgColor, 'border-radius': ((diyComponent.couponItem.aroundRadius*2)+'rpx')}"
                          @click="couponItemLink(item)">
                        <view
                            class="flex min-w-[110rpx] max-w-[120rpx] items-baseline justify-center truncate price-font mr-[10rpx]"
                            :style="{'color': diyComponent.couponItem.moneyColor}">
                            <text class="text-[26rpx]">￥</text>
                            <text class="text-[46rpx] font-bold truncate">{{ parseFloat(item.price) }}</text>
                        </view>
                        <view class="flex flex-col">
                            <view class="text-[28rpx] font-500" :style="{'color': diyComponent.couponItem.textColor}">{{ item.type_name }}</view>
                            <view class="text-[#666] text-[24rpx] truncate max-w-[180rpx] mt-[12rpx]" :style="{'color': diyComponent.couponItem.subTextColor}">{{ item.min_condition_money == '0.00' ? '无门槛' : ('满' + parseFloat(item.min_condition_money) + '元可用') }}</view>
                        </view>
                    </view>
                </scroll-view>
            </view>
        </view>
    </x-skeleton>
</template>

<script setup lang="ts">
// 优惠券组件
import { ref, reactive, computed, onMounted } from 'vue';
import { img, redirect } from '@/utils/common';
import useDiyStore from '@/app/stores/diy';
import { useLogin } from '@/hooks/useLogin';
import useMemberStore from '@/stores/member'
import { getShopCouponComponents, getCoupon } from '@/addon/shop/api/coupon';

const props = defineProps(['component', 'index']);
const diyStore = useDiyStore();
const memberStore = useMemberStore()
const userInfo = computed(() => memberStore.info)
const skeleton = reactive({
    type: 'banner',
    loading: false,
    config: {
        gridRows: 1,
        gridRowsGap: '0rpx',
        headHeight: '170rpx'
    }
})

const diyComponent = computed(() => {
    if (diyStore.mode == 'decorate') {
        return diyStore.value[props.index];
    } else {
        return props.component;
    }
})

const warpCss = computed(() => {
    var style = '';
    if (diyComponent.value.topRounded) style += 'border-top-left-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
    if (diyComponent.value.topRounded) style += 'border-top-right-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    return style;
})

const couponStyle4Css = computed(() => {
    var style = '';
    if (diyComponent.value.componentStartBgColor && diyComponent.value.componentEndBgColor) style += `background:linear-gradient(${ diyComponent.value.componentGradientAngle },${ diyComponent.value.componentStartBgColor },${ diyComponent.value.componentEndBgColor });`;
    else style += 'background-color:' + (diyComponent.value.componentStartBgColor || diyComponent.value.componentEndBgColor) + ';';
    return style;
})

const toLink = (url: any) => {
    if (diyStore.mode == 'decorate') return;
    redirect({ url })
}

const couponList: any = ref([])
const getShopCouponListFn = () => {
    let data: object = {
        num: diyComponent.value.source == 'all' ? diyComponent.value.num : '',
        coupon_ids: diyComponent.value.source == 'custom' ? diyComponent.value.couponIds : '',
    };
    getShopCouponComponents(data).then((res: any) => {
        couponList.value = res.data
        skeleton.loading = false;

        // 数据为空时隐藏整个组件
        // if(couponList.value.length == 0) {
        //     diyComponent.value.pageStyle = '';
        // }
    })
}

const couponItemLink = (data: any) => {
    // redirect({ url: '/addon/shop/pages/coupon/detail', param: { coupon_id: data.id } })
    collecting(data.id)
}

onMounted(() => {
    refresh();
});

const refresh = () => {

    // 装修模式下设置默认图
    if (diyStore.mode == 'decorate') {
        let obj = {
            title: '满减券',
            type_name: '通用券',
            price: 100,
            min_condition_money: 0,
        };
        for (let i = 0; i < 4; i++) {
            couponList.value.push(obj);
        }
    } else {
        getShopCouponListFn();
    }
}

const collecting = (coupon_id: any) => {
    if (diyStore.mode == 'decorate') return;
    if (!userInfo.value) {
        useLogin().setLoginBack({ url: '/addon/shop/pages/coupon/list' })
        return false
    }
    getCoupon({ coupon_id, number: 1 }).then(res => {
        // detail.value.btnType = 'using'
    })
}
</script>

<style lang="scss" scoped>
.coupon-wrap {
    &.style-1 {
        .coupon-list {
            position: relative;
            height: 270rpx;
            width: 100%;
            white-space: nowrap;
            padding: 30rpx 40rpx 0;
            box-sizing: border-box;

            &::before {
                content: "";
                position: absolute;
                top: 0;
                left: 20rpx;
                right: 20rpx;
                bottom: 0;
                background: linear-gradient(#FEF9EC, #FCD9A5);
                border-radius: 24rpx;
            }
        }

        .coupon-buy-btn {
            border-radius: 50rpx;
        }
    }

    &.style-2 {
        .coupon-list {
            position: relative;
            height: 170rpx;
            width: 100%;
            white-space: nowrap;
            padding: 20rpx 0 20rpx 20rpx;
            box-sizing: border-box;
            overflow: hidden;
            background: linear-gradient(#EE3928, #EF3F30);
        }

        .coupon-buy-btn {
            border-radius: 50rpx;
        }
    }

    &.style-3 {
        display: flex;
        align-items: center;
        padding: 20rpx;

        .desc {
            padding-top: 12rpx;
            height: 164rpx;
            margin-right: 10rpx;
            box-sizing: border-box;
        }

        .coupon-list {
            flex: 1;
            position: relative;
            white-space: nowrap;
            box-sizing: border-box;
            overflow: hidden;

            .coupon-item-content {
                position: relative;
                background: linear-gradient(160deg, #FD5F2F 0%, #F6370F 100%);
                width: 146rpx;
                height: 108rpx;
                border-radius: 12rpx;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 12rpx 0 14rpx;
                box-sizing: border-box;

                &::after {
                    content: "";
                    position: absolute;
                    border: 12rpx solid #fff;
                    border-right-color: transparent;
                    border-top-color: transparent;
                    border-radius: 50%;
                    top: 50%;
                    transform: rotate(45deg) translateY(-50%);
                    right: -5rpx;
                }

                &::before {
                    content: "";
                    position: absolute;
                    border: 12rpx solid #fff;
                    border-left-color: transparent;
                    border-bottom-color: transparent;
                    border-radius: 50%;
                    top: 50%;
                    transform: rotate(45deg) translateY(-50%);
                    left: -22rpx;
                }
            }
        }

        .single-coupon {
            .coupon-item-content {
                position: relative;
                background: linear-gradient(160deg, #FD5F2F 0%, #F6370F 100%);
                height: 144rpx;
                border-radius: 12rpx;
                padding: 12rpx 0 14rpx;
                box-sizing: border-box;

                &::after {
                    content: "";
                    position: absolute;
                    border: 14rpx solid #fff;
                    border-right-color: transparent;
                    border-top-color: transparent;
                    border-radius: 50%;
                    left: 139rpx;
                    transform: rotate(-45deg);
                    top: -18rpx;
                }

                &::before {
                    content: "";
                    position: absolute;
                    border: 14rpx solid #fff;
                    border-left-color: transparent;
                    border-bottom-color: transparent;
                    border-radius: 50%;
                    left: 139rpx;
                    transform: rotate(-45deg);
                    bottom: -16rpx;
                }

                .coupon-left {
                    position: relative;

                    &::after {
                        content: '';
                        position: absolute;
                        top: 50%;
                        right: 0;
                        transform: translateY(-50%);
                        border-right: 2rpx dashed #fff;
                        width: 2rpx;
                        height: 88rpx;
                    }
                }
            }
        }

        .coupon-buy-btn {
            border-radius: 50rpx;
        }
    }

    &.style-4 {
        padding: 20rpx 24rpx;

        .coupon-list {
            flex: 1;
            position: relative;
            white-space: nowrap;
            box-sizing: border-box;
            overflow: hidden;

            .coupon-item {
                width: calc(50% - 6rpx);
            }
        }
    }
}
</style>

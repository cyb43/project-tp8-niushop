<template>
    <view class="bg-[var(--page-bg-color)] min-h-screen overflow-hidden order-list" :style="themeColor()">
        <view class="fixed left-0 top-0 right-0 z-10" v-if="statusLoading">
            <scroll-view :scroll-x="true" class="tab-style-2">
                <view class="tab-content">
                    <view class="tab-items" :class="{ 'class-select': orderState === item.status.toString() }" @click="orderStateFn(item.status)" v-for="(item, index) in orderStateList">{{ item.name }}
                    </view>
                </view>
            </scroll-view>
        </view>
        <mescroll-body ref="mescrollRef" top="88rpx" @init="mescrollInit" :down="{ use: false }" @up="getShopOrderFn">
            <view class="sidebar-margin pt-[var(--top-m)]" v-if="list.length">
                <template v-for="(item, index) in list" :key="index">
                    <view class="mb-[var(--top-m)] card-template">
                        <view @click="toLink(item)">
                            <view class="flex justify-between items-center">
                                <view class="text-[#303133] text-[26rpx] font-400 leading-[30rpx]">
                                    <text>{{ t('orderNo') }}:</text>
                                    <text class="ml-[10rpx]">{{ item.order_no }}</text>
                                    <text class="text-[303133] text-[26rpx] font-400 nc-iconfont nc-icon-fuzhiV6xx1 ml-[11rpx]" @click.stop="copy(item.order_no)"></text>
                                </view>
                                <view class="text-[#303133] text-[26rpx] leading-[34rpx]" :class="{'text-primary': item.status  == 1,'!text-[var(--text-color-light9)]' :item.status  == 5 || item.status  == -1}">{{ item.status_name.name }}</view>
                            </view>
                            <view class="flex box-border mt-[20rpx]" v-for="(subitem, index) in item.order_goods" :key="index">
                                <image v-if="subitem.goods_image_thumb_small"
                                       class="w-[150rpx] h-[150rpx] rounded-[var(--rounded-mid)]"
                                       :src="img(subitem.goods_image_thumb_small)" :mode="'aspectFill'"
                                       @error="subitem.goods_image_thumb_small='static/resource/images/diy/shop_default.jpg'"/>
                                <image v-else class="w-[150rpx h-[150rpx] rounded-[var(--rounded-mid)]" :src="img('static/resource/images/diy/shop_default.jpg')" :mode="'aspectFill'"/>
                                <view class="ml-[20rpx] flex flex-1 flex-col box-border">
                                    <view class="max-w-[490rpx] text-[28rpx] leading-[40rpx] font-400 truncate text-[#303133]">{{ subitem.goods_name }}</view>
                                    <view class="flex justify-between items-baseline text-[#303133] mt-[14rpx]">
                                        <view>
                                            <view v-if="subitem.sku_name" class="text-[24rpx] text-[var(--text-color-light6)] font-400 truncate leading-[34rpx] max-w-[369rpx] mb-[10rpx]">{{ subitem.sku_name }}</view>
                                            <view class="text-[24rpx] font-400 leading-[34rpx] text-[var(--text-color-light6)]" v-if="item.delivery_type != 'virtual'">{{ t('deliveryType') }} ： {{ item.delivery_type_name }}</view>
                                            <view v-else class="text-[24rpx] font-400 leading-[34rpx] text-[var(--text-color-light6)]">{{ t('createTime') }} ：{{ item.create_time }}</view>
                                        </view>
                                        <text class="text-right text-[26rpx] font-400 w-[90rpx] leading-[36rpx]">x{{ subitem.num }}</text>
                                    </view>

                                </view>
                            </view>
                        </view>
                        <view class="flex justify-end items-center mt-[20rpx]">
                            <view class="text-[22rpx] text-[var(--text-color-light9)] leading-[30rpx] mr-[6rpx]" v-if="parseFloat(item.delivery_money)">{{ t('service') }}</view>
                            <view class="flex items-center">
                                <view class="text-[22rpx] font-400 leading-[30rpx] text-[#303133]">{{ t('actualPayment') }}：</view>
                                <view class="text-[var(--price-text-color)] price-font">
                                    <text class="text-[36rpx] mr-[2rpx]">{{ item.point }}</text>
                                    <text class="text-[30rpx]">{{ t('point') }}</text>
                                    <template v-if="parseFloat(item.order_money)">
                                        <text class="text-[30rpx] mx-[4rpx]">+</text>
                                        <text class="text-[36rpx] price-font">{{ parseFloat(item.order_money) }}</text>
                                        <text class="text-[30rpx] price-font ml-[2rpx]">{{ t('money') }}</text>
                                    </template>
                                </view>
                            </view>
                        </view>
                        <view class="flex justify-end text-[28rpx] mt-[20rpx] items-center" v-if="(item.status == 1) || (item.status == 3) || (item.status == 5 && evaluateConfig.is_evaluate == 1)">
                            <view class="text-[24rpx] font-500 leading-[52rpx] h-[56rpx] min-w-[150rpx] text-center border-[2rpx] border-solid border-[var(--text-color-light9)] rounded-full text-[var(--text-color-light6)] box-bordertext-[24rpx] font-500 leading-[52rpx] h-[56rpx] min-w-[150rpx] text-center border-[2rpx] border-solid border-[var(--text-color-light9)] rounded-full text-[var(--text-color-light6)] box-border" @click.stop="orderBtnFn(item, 'close')" v-if="item.status == 1">{{ t('orderClose') }}</view>
                            <view class="text-[24rpx] font-500 leading-[52rpx] h-[56rpx] min-w-[150rpx] text-center border-[2rpx] border-solid text-[#fff] border-primary bg-primary rounded-full ml-[20rpx] box-border" @click.stop="orderBtnFn(item, 'pay')" v-if="item.status == 1">{{ t('topay') }}</view>
                            <view class="text-[24rpx] font-500 leading-[52rpx] h-[56rpx] min-w-[150rpx] text-center border-[2rpx] border-solid text-[#fff] border-primary bg-primary rounded-full ml-[20rpx] box-border" @click.stop="orderBtnFn(item, 'finish')" v-if="item.status == 3">{{ t('orderFinish') }}</view>
                            <view class="text-[24rpx] font-500 leading-[52rpx] h-[56rpx] min-w-[150rpx] text-center border-[2rpx] border-solid border-[var(--text-color-light9)] rounded-full ml-[20rpx]  text-[var(--text-color-light6)] box-border"
                                v-if="item.status == 5 && evaluateConfig.is_evaluate == 1"
                                @click.stop="orderBtnFn(item, 'evaluate')">{{ item.is_evaluate == 1 ? t('selectedEvaluate') : t('evaluate') }}</view>
                        </view>
                    </view>
                </template>
            </view>
            <mescroll-empty v-if="!list.length && loading" :option="{tip : '暂无订单'}"></mescroll-empty>
        </mescroll-body>
        <pay ref="payRef" @close="payClose"></pay>
    </view>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { t } from '@/locale'
import { img, redirect, copy } from '@/utils/common';
import { getShopOrderStatus, getShopOrder, orderClose, orderFinish } from '@/addon/shop/api/order';
import { getEvaluateConfig } from '@/addon/shop/api/shop';
import MescrollBody from '@/components/mescroll/mescroll-body/mescroll-body.vue';
import MescrollEmpty from '@/components/mescroll/mescroll-empty/mescroll-empty.vue';
import useMescroll from '@/components/mescroll/hooks/useMescroll.js';
import { onLoad, onPageScroll, onReachBottom } from '@dcloudio/uni-app';
import useConfigStore from "@/stores/config";

const { mescrollInit, downCallback, getMescroll } = useMescroll(onPageScroll, onReachBottom);
const list = ref<Array<Object>>([]);
const loading = ref<boolean>(false);
const statusLoading = ref<boolean>(false);
const orderState = ref('')
const orderStateList: any = ref([]);
const evaluateConfig = ref("")

const mch_id = ref('')
const isTradeManaged = ref(false)

onLoad((option: any) => {
    orderState.value = option.status || "";
    evaluateEvent()
    getShopOrderStatusFn();
});

const evaluateEvent = () => {
    getEvaluateConfig().then((data: any) => {
        evaluateConfig.value = data.data
    })
}

const getShopOrderFn = (mescroll: any) => {
    loading.value = false;
    let data: object = {
        page: mescroll.num,
        limit: mescroll.size,
        status: orderState.value,
        activity_type: 'exchange'
    };

    getShopOrder(data).then((res: any) => {
        let newArr = (res.data.data as Array<Object>);
        //设置列表数据
        if (mescroll.num == 1) {
            list.value = []; //如果是第一页需手动制空列表
        }
        list.value = list.value.concat(newArr);
        mescroll.endSuccess(newArr.length);

        mch_id.value = res.data.mch_id;
        isTradeManaged.value = res.data.is_trade_managed;
        loading.value = true;
    }).catch(() => {
        loading.value = true;
        mescroll.endErr(); // 请求失败, 结束加载
    })
}

const getShopOrderStatusFn = () => {
    statusLoading.value = false;
    orderStateList.value = [];
    let obj = { name: '全部', status: '' };
    orderStateList.value.push(obj);

    getShopOrderStatus().then((res: any) => {
        Object.values(res.data).forEach((item, index) => {
            orderStateList.value.push(item);
        });
        statusLoading.value = true;
    }).catch(() => {
        statusLoading.value = true;
    })
}

const orderStateFn = (status: any) => {
    orderState.value = status.toString();
    list.value = [];
    getMescroll().resetUpScroll();
};

const toLink = (data: any) => {
    redirect({ url: '/addon/shop/pages/order/detail', param: { order_id: data.order_id } })
}

// 支付
const payRef = ref(null)
const orderBtnFn = (data: any, type = '') => {
    if (type == 'pay')
        payRef.value?.open(data.order_type, data.order_id, `/addon/shop/pages/order/detail?order_id=${ data.order_id }`);
    else if (type == 'close') {
        close(data);
    } else if (type == 'finish') {
        finish(data);
    } else if (type == 'evaluate') {
        if (!data.is_evaluate) {
            redirect({ url: '/addon/shop/pages/evaluate/order_evaluate', param: { order_id: data.order_id } })
        } else {
            redirect({ url: '/addon/shop/pages/evaluate/order_evaluate_view', param: { order_id: data.order_id } })
        }
    }
}

//关闭订单
const close = (item: any) => {
    uni.showModal({
        title: '提示',
        content: '您确定要关闭该订单吗？',
        confirmColor: useConfigStore().themeColor['--primary-color'],
        success: res => {
            if (res.confirm) {
                orderClose(item.order_id).then((data) => {
                    getMescroll().resetUpScroll();
                })
            }
        }
    })
}

//订单完成
const finish = (item: any) => {
    // 如果不在微信小程序中
    // #ifndef MP-WEIXIN
    uni.showModal({
        title: '提示',
        content: '您确定物品已收到吗？',
        confirmColor: useConfigStore().themeColor['--primary-color'],
        success: res => {
            if (res.confirm) {
                orderFinish(item.order_id).then((data) => {
                    getMescroll().resetUpScroll();
                })
            }
        }
    })
    // #endif

    // #ifdef MP-WEIXIN
    // 检测微信小程序是否已开通发货信息管理服务
    if (item.pay && item.pay.type == 'wechatpay' && isTradeManaged.value && wx.openBusinessView) {
        wx.openBusinessView({
            businessType: 'weappOrderConfirm',
            extraData: {
                merchant_id: mch_id.value,
                merchant_trade_no: item.out_trade_no
            },
            success: (res: any) => {
                orderFinish(item.order_id).then((data) => {
                    getMescroll().resetUpScroll();
                })
            },
            fail: (res: any) => {
                console.log('小程序确认收货组件打开失败 fail', res);
            }
        })
    } else {
        uni.showModal({
            title: '提示',
            content: '您确定物品已收到吗？',
            confirmColor: useConfigStore().themeColor['--primary-color'],
            success: res => {
                if (res.confirm) {
                    orderFinish(item.order_id).then((data) => {
                        getMescroll().resetUpScroll();
                    })
                }
            }
        })
    }
    // #endif
}
</script>
<style>
.order-list .mescroll-body {
    padding-bottom: constant(safe-area-inset-bottom) !important;
    padding-bottom: env(safe-area-inset-bottom) !important;
}
</style>
<style lang="scss" scoped>
.text-item {
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.text-color {
    color: var(--primary-color);
}

.bg-color {
    background-color: var(--primary-color);
}

.class-select {
    position: relative;
    font-weight: 500;
    color: var(--primary-color);

    &::before {
        content: "";
        position: absolute;
        bottom: 0;
        height: 4rpx;
        border-radius: 4rpx;
        background-color: var(--primary-color);
        width: 40rpx;
        left: 50%;
        transform: translateX(-50%);
    }
}
</style>

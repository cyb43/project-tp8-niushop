<template>
    <view class="bg-[var(--page-bg-color)] min-h-screen overflow-hidden order-list" :style="themeColor()">
        <view class="fixed left-0 top-0 right-0 z-10" v-if="statusLoading">
            <scroll-view :scroll-x="true" class="tab-style-2">
                <view class="tab-content">
                    <view class="tab-items" :class="{ 'class-select': orderState === item.status.toString() }" @click="orderStateFn(item.status)" v-for="(item, index) in orderStateList">{{ item.name }}</view>
                </view>
            </scroll-view>
        </view>

        <mescroll-body ref="mescrollRef" top="88rpx" @init="mescrollInit" :down="{ use: false }" @up="getShopOrderFn">
            <view class="sidebar-margin pt-[var(--top-m)]" v-if="list.length">
                <template v-for="(item, index) in list" :key="index">
                    <view class="mb-[var(--top-m)] card-template">
                        <view @click.stop="toLink(item)">
                            <view class="flex justify-between items-center">
                                <view class="text-[#303133] text-[26rpx] font-400 leading-[36rpx]">
                                    <text>{{ t('orderNo') }}:</text>
                                    <text class="ml-[10rpx]">{{ item.order_no }}</text>
                                    <text class="text-[#303133] text-[26rpx] font-400 nc-iconfont nc-icon-fuzhiV6xx1 ml-[11rpx]" @click.stop="copy(item.order_no)"></text>
                                </view>
								<view v-if="item.status  == -1" class="text-[#303133] text-[26rpx] max-w-[85px] leading-[34rpx] truncate" :class="{'text-primary': item.status  == 1,'!text-[var(--text-color-light9)]' :item.status  == 5 || item.status  == -1}">{{ item.close_type_name }}</view>
                                <view v-else class="text-[#303133] text-[26rpx] leading-[34rpx]" :class="{'text-primary': item.status  == 1,'!text-[var(--text-color-light9)]' :item.status  == 5 || item.status  == -1}">{{ item.status_name.name }}</view>
                            </view>
                            <template v-for="(subItem, index) in item.order_goods" :key="index">
                                <view class="flex box-border mt-[20rpx]">
                                    <u--image width="150rpx" height="150rpx" :radius="'var(--goods-rounded-big)'" :src="img(subItem.goods_image_thumb_small ? subItem.goods_image_thumb_small : '')" mode="aspectFill">
                                        <template #error>
                                            <image class="w-[150rpx] h-[150rpx] rounded-[var(--goods-rounded-big)] overflow-hidden" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill" />
                                        </template>
                                    </u--image>
                                    <view class="ml-[20rpx] flex flex-1 flex-col box-border">
                                        <view class="flex justify-between items-baseline">
                                            <view class="max-w-[322rpx] text-[28rpx] leading-[40rpx] font-400 truncate text-[#303133]">{{ subItem.goods_name }}</view>
                                            <template v-if="item.activity_type == 'exchange'">
                                                <view class="text-right ml-[10rpx] leading-[42rpx]" v-if="parseFloat(subItem.price)">
                                                    <text class="text-[22rpx] font-400 price-font">￥</text>
                                                    <text class="text-[36rpx] font-500 price-font">{{ parseFloat(subItem.price).toFixed(2).split('.')[0] }}</text>
                                                    <text class="text-[22rpx] font-500 price-font">.{{ parseFloat(subItem.price).toFixed(2).split('.')[1] }}</text>
                                                </view>
                                            </template>
                                            <template v-else-if="subItem.extend && subItem.extend.is_impulse_buy">
                                                <view class="text-right ml-[10rpx] leading-[42rpx]" v-if="parseFloat(subItem.goods_money)">
                                                    <text class="text-[22rpx] font-400 price-font">￥</text>
                                                    <text class="text-[36rpx] font-500 price-font">{{ parseFloat(subItem.goods_money).toFixed(2).split('.')[0] }}</text>
                                                    <text class="text-[22rpx] font-500 price-font">.{{ parseFloat(subItem.goods_money).toFixed(2).split('.')[1] }}</text>
                                                </view>
                                            </template>
                                            <template v-else>
                                                <view class="text-right leading-[42rpx] ml-[10rpx]">
                                                    <text class="text-[22rpx] price-font">￥</text>
                                                    <text class="text-[36rpx] font-500 price-font">{{ parseFloat(subItem.price).toFixed(2).split('.')[0] }}</text>
                                                    <text class="text-[22rpx] font-500 price-font">.{{ parseFloat(subItem.price).toFixed(2).split('.')[1] }}</text>
                                                </view>
                                            </template>
                                        </view>
                                        <view class="flex justify-between items-baseline text-[#303133] mt-[14rpx]">
                                            <view>
                                                <view class="text-[24rpx] text-[var(--text-color-light6)] font-400 truncate leading-[34rpx] max-w-[369rpx] mb-[10rpx]" v-if="subItem.sku_name">{{ subItem.sku_name }}</view>
                                                <view class="text-[24rpx] font-400 leading-[34rpx] text-[var(--text-color-light6)]" v-if="item.delivery_type != 'virtual'">{{ t('deliveryType') }} ： {{ item.delivery_type_name }}</view>
                                                <view class="text-[24rpx] font-400 leading-[34rpx] text-[var(--text-color-light6)]" v-else>{{ t('createTime') }} ：{{ item.create_time }}</view>
                                            </view>
                                            <text class="text-right text-[26rpx] font-400 w-[90rpx] leading-[36rpx]">x{{ subItem.num }}</text>
                                        </view>
                                    </view>
                                </view>
                                <view class="flex items-center box-border mt-[8rpx]" v-if="subItem.extend && subItem.extend.is_newcomer && subItem.num > 1">
                                    <image class="h-[24rpx] w-[56rpx]" :src="img('addon/shop/newcomer.png')" mode="heightFix" />
                                    <view class="text-[24rpx] text-[#FFB000] leading-[34rpx] ml-[8rpx]">第1{{ subItem.unit }}，￥{{ parseFloat(subItem.extend.newcomer_price).toFixed(2) }}/{{ subItem.unit }}；第{{ subItem.num > 2 ? '2~' + subItem.num : '2' }}{{ subItem.unit }}，￥{{ parseFloat(subItem.price).toFixed(2) }}/{{ subItem.unit }}</view>
                                </view>
                            </template>
                        </view>
                        <view class="flex justify-end items-center mt-[20rpx]">
                            <view class="flex items-baseline">
                                <view class="text-[22rpx] text-[var(--text-color-light9)] leading-[30rpx] mr-[6rpx]" v-if="parseFloat(item.delivery_money)">{{ t('service') }}</view>
                                <view class="text-[22rpx] font-400 leading-[30rpx] text-[#303133]">{{ t('actualPayment') }}：</view>
                                <view class="leading-[1] text-[var(--price-text-color)]">
                                    <template v-if="item.activity_type == 'exchange'">
                                        <text class="text-[36rpx] mr-[2rpx] leading-[40rpx] price-font font-500">{{ item.point }}</text>
                                        <text class="text-[20rpx] leading-[28rpx] font-500">{{ t('point') }}</text>
                                        <template v-if="parseFloat(item.order_money)">
                                            <text class="text-[20rpx] mx-[4rpx] font-500 leading-[28rpx]">+</text>
                                            <text class="text-[36rpx] font-500 leading-[40rpx] price-font">{{ parseFloat(item.order_money).toFixed(2) }}</text>
                                            <text class="text-[20rpx] font-500 leading-[28rpx] ml-[2rpx]">{{ t('money') }}</text>
                                        </template>
                                    </template>
                                    <template v-else>
                                        <text class="text-[22rpx] leading-[26rpx] price-font">￥</text>
                                        <text class="text-[36rpx] font-500 leading-[40rpx] price-font">{{ parseFloat(item.order_money).toFixed(2).split('.')[0] }}</text>
                                        <text class="text-[22rpx] font-500 leading-[28rpx] price-font">.{{ parseFloat(item.order_money).toFixed(2).split('.')[1] }}</text>
                                    </template>
                                </view>
                            </view>
                        </view>
                        <view class="flex justify-end text-[28rpx] mt-[20rpx] items-center" v-if="(item.status == 1) || (item.status == 3) || (item.status == 5 && evaluateConfig.evaluate_is_show && evaluateConfig.is_evaluate == 1)">
                            <view class="text-[24rpx] font-500 leading-[52rpx] h-[56rpx] min-w-[150rpx] text-center border-[2rpx] border-solid border-[var(--text-color-light9)] rounded-full text-[var(--text-color-light6)] box-border" v-if="item.status == 1" @click.stop="orderBtnFn(item, 'close')">{{ t('orderClose') }}</view>
                            <view class="text-[24rpx] font-500 flex-center h-[56rpx] min-w-[150rpx] text-center border-[0] text-[#fff] primary-btn-bg rounded-full ml-[20rpx] box-border" v-if="item.status == 1" @click.stop="orderBtnFn(item, 'pay')">{{ t('topay') }}</view>
                            <view class="text-[24rpx] font-500 flex-center h-[56rpx] min-w-[150rpx] text-center border-[0] text-[#fff] primary-btn-bg rounded-full ml-[20rpx] box-border" v-if="item.status == 3" @click.stop="orderBtnFn(item, 'finish')">{{ t('orderFinish') }}</view>
                            <view class="text-[24rpx] font-500 leading-[52rpx] h-[56rpx] min-w-[150rpx] text-center border-[2rpx] border-solid border-[var(--text-color-light9)] rounded-full ml-[20rpx]  text-[var(--text-color-light6)] box-border"
                                v-if="item.status == 5 && evaluateConfig.evaluate_is_show && evaluateConfig.is_evaluate == 1"
                                @click.stop="orderBtnFn(item, 'evaluate')">{{ item.is_evaluate == 1 ? t('selectedEvaluate') : t('evaluate') }}</view>
                        </view>
                    </view>
                </template>
            </view>
            <mescroll-empty v-if="!list.length && loading" :option="{tip : '暂无订单'}"></mescroll-empty>
        </mescroll-body>
        <pay ref="payRef" @close="payClose"></pay>
        <!-- #ifdef MP-WEIXIN -->
        <!-- 小程序隐私协议 -->
        <wx-privacy-popup ref="wxPrivacyPopupRef"></wx-privacy-popup>
        <!-- #endif -->
    </view>
</template>

<script setup lang="ts">
import { ref, nextTick } from 'vue';
import { t } from '@/locale'
import { img, redirect, copy } from '@/utils/common'
import { getShopOrderStatus, getShopOrder, orderClose, orderFinish } from '@/addon/shop/api/order';
import { getEvaluateConfig } from '@/addon/shop/api/shop';
import MescrollBody from '@/components/mescroll/mescroll-body/mescroll-body.vue';
import MescrollEmpty from '@/components/mescroll/mescroll-empty/mescroll-empty.vue';
import useMescroll from '@/components/mescroll/hooks/useMescroll.js';
import { onLoad, onPageScroll, onReachBottom, onShow } from '@dcloudio/uni-app';
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

const wxPrivacyPopupRef: any = ref(null)

onLoad((option: any) => {
    orderState.value = option.status || "";
    evaluateEvent()
    getShopOrderStatusFn();
    // #ifdef MP
    nextTick(() => {
        if (wxPrivacyPopupRef.value) wxPrivacyPopupRef.value.proactive();
    })
    // #endif
});

onShow(() => {
    nextTick(() => {
        // 评价完成之后，会返回到订单列表，需要请求最新数据
        if (getMescroll()) {
            getMescroll().resetUpScroll();
        }
    })
})

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
        status: orderState.value
    };

    getShopOrder(data).then((res: any) => {
        let newArr = (res.data.data as Array<Object>);
        //设置列表数据
        if (mescroll.num == 1) {
            list.value = []; //如果是第一页需手动制空列表
        }

        newArr.forEach((item: any) => {
            item.is_show_evaluate = true;
            let evaluateCount = 0;
            for (let i = 0; i < item.order_goods.length; i++) {
                if (item.order_goods[i].status != 1 || item.order_goods[i].is_enable_refund == 1) {
                    evaluateCount++;
                }
            }
            if (evaluateCount == item.order_goods.length) {
                item.is_show_evaluate = false;
            }
        })

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

.order-list :deep(.u-count-down__text) {
    font-size: 24rpx !important;
    color: #EF000C !important;
}
</style>
<style lang="scss" scoped>
.text-color {
    color: var(--primary-color);
}

.bg-color {
    background-color: var(--primary-color);
}
</style>

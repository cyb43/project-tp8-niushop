<template>
    <u-popup :show="show" :round="10" @close="handleClose" :closeable="true" bgColor="#fff" zIndex="10081" :closeOnClickOverlay="false">
        <view class="flex flex-col h-[65vh] popup-common" v-if="payInfo" @touchmove.prevent.stop>
            <view class="head">
                <view class="title">{{ t('pay.payTitle') }}</view>
                <view class="flex items-end justify-center w-full text-xl font-bold py-[20rpx] price-font">
                    <view class="text-base mr-[4rpx]">{{ t('currency') }}</view>
                    {{ moneyFormat(payInfo.money) }}
                </view>
            </view>
            <scroll-view scroll-y="true" class="flex-1 pt-[20rpx]">
                <view class="flex text-[28rpx] px-[36rpx] py-[20rpx] mb-[10rpx]">
                    <view class="text-[var(--text-color-light6)]">{{ t('pay.orderInfo') }}</view>
                    <view class="text-right flex-1 pl-[30rpx] truncate">{{ payInfo.body }}</view>
                </view>
                <view class="mx-[var(--popup-sidebar-m)] px-[30rpx] bg-white rounded-[20rpx] bg-[var(--temp-bg)]">
                    <template v-if="payInfo.pay_type_list.length">
                        <view class="pay-item py-[30rpx] flex items-center border-0 border-b border-solid border-[#eee]" v-for="(item, index) in payInfo.pay_type_list" :key="index" @click="type = item.key">
                            <u-image :src="img(item.icon)" width="50rpx" height="50rpx"></u-image>
                            <view class="flex-1 px-[20rpx] text-[28rpx] font-500">{{ item.name }}</view>
                            <u-icon name="checkbox-mark" color="var(--primary-color)" v-if="item.key == type"></u-icon>
                        </view>
                    </template>
                    <view class="py-[30rpx] text-center text-[24rpx] text-gray-subtitle" v-else>{{ t('pay.notHavePayType') }}</view>
                </view>
            </scroll-view>
            <view class="btn-wrap">
                <button class="primary-btn-bg btn" hover-class="none" :loading="loading" @click="confirmPay">{{ t('pay.confirmPay') }}</button>
            </view>
        </view>
    </u-popup>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { t } from '@/locale'
import { getPayInfo, pay } from '@/app/api/pay'
import { img, redirect, isWeixinBrowser, moneyFormat } from '@/utils/common'
import wechat from '@/utils/wechat'

// #ifdef H5
if (isWeixinBrowser()) wechat.init();
// #endif

const show = ref(false)
const loading = ref(false)

const payInfo = ref<AnyObject | null>(null)
const type = ref('')

/**
 * 确认支付
 */
const confirmPay = () => {
    if (uni.$u.test.isEmpty(type.value)) {
        uni.showToast({ title: t('pay.notHavePayType'), icon: 'none' })
        return
    }
    if (type.value == 'friendspay') {
        redirect({
            url: '/app/pages/friendspay/share',
            param: {
                id: payInfo.value?.trade_id,
                type: payInfo.value?.trade_type,
            },
            mode: 'redirectTo'
        })
        return false
    }
    if (loading.value) return
    loading.value = true

    pay({
        trade_type: payInfo.value?.trade_type,
        trade_id: payInfo.value?.trade_id,
        type: type.value,
        openid: uni.getStorageSync('openid') || ''
    }).then((res: any) => {
        switch (type.value) {
            case 'wechatpay':
                // #ifndef H5
                uni.requestPayment({
                    provider: 'wxpay',
                    ...res.data,
                    success: (res: any) => {
                        toPayResult()
                    },
                    fail: (res: any) => {
                        loading.value = false
                    }
                })
                // #endif
                // #ifdef H5
                if (isWeixinBrowser()) {
                    res.data.timestamp = res.data.timeStamp
                    delete res.data.timeStamp
                    wechat.pay({
                        ...res.data,
                        success: () => {
                            toPayResult()
                        },
                        cancel: () => {
                            loading.value = false
                        }
                    })
                } else {
                    uni.setStorageSync('paymenting', {
                        trade_type: payInfo.value?.trade_type,
                        trade_id: payInfo.value?.trade_id
                    })
                    location.href = res.data.h5_url
                }
                // #endif
                break;
            case 'alipay':
                // #ifdef H5
                if (isWeixinBrowser()) {
                    redirect({
                        url: '/app/pages/pay/browser',
                        param: {
                            trade_type: payInfo.value?.trade_type,
                            trade_id: payInfo.value?.trade_id,
                            alipay: encodeURIComponent(res.data.url)
                        },
                        mode: 'redirectTo'
                    })
                } else {
                    uni.setStorageSync('paymenting', {
                        trade_type: payInfo.value?.trade_type,
                        trade_id: payInfo.value?.trade_id
                    })
                    location.href = res.data.url
                }
                // #endif
                break;
            default:
                if (res.data.url) {
                    redirect({
                        url: res.data.url,
                        param: res.data.param || {},
                        mode: 'redirectTo'
                    })
                    return
                }
                toPayResult()
                break;
        }
    }).catch(() => {
        loading.value = false
    })
}

/**
 * 检测是否是支付后返回
 */
uni.$on('checkIsReturnAfterPayment', () => {
    const data = uni.getStorageSync('paymenting')
    if (uni.getStorageSync('paymenting')) {
        redirect({
            url: '/app/pages/pay/result',
            param: {
                trade_type: data.trade_type,
                trade_id: data.trade_id
            },
            mode: 'redirectTo',
            success() {
                uni.removeStorageSync('paymenting')
            }
        })
    }
})
const repeat = ref(false)
const open = (tradeType: string, tradeId: number, payReturn: string = '', scene: string = '') => {

    if (repeat.value) return
    repeat.value = true

    // 设置支付后跳转页面
    uni.setStorageSync('payReturn', encodeURIComponent(payReturn))

    // 帮人付款时过滤帮付选项
    const obj: any = {}
    if (scene) {
        obj.scene = scene
    }
    getPayInfo(tradeType, tradeId, obj).then((res: any) => {

        let { data } = res
        payInfo.value = data

        if (uni.$u.test.isEmpty(data)) {
            uni.showToast({ title: t('pay.notObtainedInfo'), icon: 'none' })
            return
        }
        if (data.money == 0) {
            toPayResult()
            return
        }
        type.value = data.pay_type_list[0] ? data.pay_type_list[0].key : ''
        show.value = true
        repeat.value = false

    }).catch(() => {
        repeat.value = false
    })
}

const emits = defineEmits(['close', 'confirm'])
const toPayResult = () => {
    emits('confirm')
    redirect({
        url: '/app/pages/pay/result',
        param: { trade_type: payInfo.value?.trade_type, trade_id: payInfo.value?.trade_id },
        mode: 'redirectTo'
    })
}


const handleClose = () => {
    uni.removeStorageSync('paymenting')
    show.value = false
    emits('close')
}

defineExpose({
    open
})
</script>

<style lang="scss" scoped>
.pay-item:last-child {
    border: none;
}
</style>

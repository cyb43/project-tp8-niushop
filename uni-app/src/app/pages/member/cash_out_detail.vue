<template>
    <view class="min-h-[100vh] bg-[var(--page-bg-color)] overflow-hidden" :style="themeColor()">
        <template v-if="!loading">
            <view class="sidebar-margin card-template mt-[20rpx] !pt-[60rpx] !pb-[40rpx]">
                <!-- <view class="flex flex-col justify-center items-center mb-[44rpx]" v-if="cashOutInfo.status == 4 && cashOutInfo.transfer_type == 'wechatpay'">
                  <image class="h-[70rpx] w-[70rpx] mb-[24rpx]" :src="img('static/resource/images/member/apply_withdrawal/transfer.png')" mode="widthFix" />
                  <view class="text-[28rpx] text-[#333]">处理中，预计2小时内到账</view>
                </view> -->
                <view class="flex items-center flex-col mb-[80rpx]">
                    <text class="text-[60rpx] font-bold price-font mb-[16rpx]">{{ cashOutInfo.apply_money }}</text>
                    <text class="text-[28rpx] text-[#333]" :class="{'text-primary': cashOutInfo.status == 1, 'text-[#999]': cashOutInfo.status == 4 }">{{ cashOutInfo.status_name }}</text>
                </view>
                <!-- 状态1.待审核2.待转账 3.已转账 4.转账中 -1拒绝' -->
                <view>
                    <view class="flex justify-between text-[28rpx] mt-[34rpx] leading-[32rpx]">
                        <text class="text-[#333] w-[200rpx]">{{ t('cashOutNo') }}</text>
                        <text class="text-[#333]">{{ cashOutInfo.cash_out_no }}</text>
                    </view>
                    <view class="flex justify-between text-[28rpx] mt-[34rpx] leading-[32rpx]"
                          v-if="Number(cashOutInfo.service_money)">
                        <text class="text-[#333] w-[200rpx]">{{ t('serviceMoney') }}</text>
                        <text class="text-[#333]">￥{{ cashOutInfo.service_money }}</text>
                    </view>
                    <view class="flex justify-between text-[28rpx] mt-[34rpx] leading-[32rpx]">
                        <text class="text-[#333] w-[200rpx]">{{ t('createTime') }}</text>
                        <text class="text-[#333]">{{ cashOutInfo.create_time }}</text>
                    </view>
                    <view class="flex justify-between text-[28rpx] mt-[34rpx] leading-[32rpx]" v-if="cashOutInfo.status && cashOutInfo.audit_time">
                        <text class="text-[#333] w-[200rpx]">{{ t('auditTime') }}</text>
                        <text class="text-[#333]">{{ cashOutInfo.audit_time }}</text>
                    </view>
                    <view class="flex justify-between text-[28rpx] mt-[34rpx] leading-[32rpx]" v-if="cashOutInfo.status == -1 && cashOutInfo.refuse_reason">
                        <text class="text-[#333] w-[200rpx]  flex-shrink-0">{{ t('refuseReason') }}</text>
                        <text class="text-[#333] ml-[20rpx] text-right">{{ cashOutInfo.refuse_reason }}</text>
                    </view>
                    <view class="flex justify-between text-[28rpx] mt-[34rpx] leading-[32rpx]" v-if="cashOutInfo.status == 3">
                        <text class="text-[#333] w-[200rpx]">{{ t('transferTypeName') }}</text>
                        <text class="text-[#333] truncate">{{ cashOutInfo.transfer_type_name }}</text>
                    </view>
                </view>
            </view>
            <view class="sidebar-margin card-template mt-[20rpx]" v-if="cashOutInfo.transfer_type == 'wechatpay' && (cashOutInfo.status == 2 || cashOutInfo.status == 3 || cashOutInfo.status == 4)">
                <u-steps :current="current" direction="column" activeColor="var(--primary-color)" activeIcon="checkmark-circle-fill" inactiveIcon="checkmark-circle-fill">
                    <u-steps-item title="提现申请" :desc="cashOutInfo.create_time"></u-steps-item>
                    <u-steps-item title="处理中" :desc="cashOutInfo.audit_time ? cashOutInfo.audit_time : ''"></u-steps-item>
                    <u-steps-item title="提现成功" :desc="cashOutInfo.transfer_time ? cashOutInfo.transfer_time : ''"></u-steps-item>
                </u-steps>
            </view>
            <view class="sidebar-margin card-template mt-[20rpx]">
                <view class="title">{{ t('proceedsInfo') }}</view>
                <view class="flex justify-between text-[28rpx] mt-[34rpx] leading-[32rpx]">
                    <text class="text-[#333] w-[200rpx]">{{ t('transferAccount') }}</text>
                    <text class="text-[#333] truncate">{{ cashOutInfo.transfer_type == 'wechatpay' ? '微信零钱' : cashOutInfo.transfer_account }}</text>
                </view>
                <template v-if="cashOutInfo.transfer_type == 'wechatpay'">
                    <view class="flex justify-between text-[28rpx] mt-[34rpx] leading-[32rpx]">
                        <text class="text-[#333] w-[200rpx]">{{ t('transferNickname') }}</text>
                        <text class="text-[#333]">{{ cashOutInfo.nickname }}</text>
                    </view>
                    <view class="flex justify-between text-[28rpx] mt-[34rpx] leading-[32rpx]">
                        <text class="text-[#333] w-[200rpx]">{{ t('transferImg') }}</text>
                        <u-avatar :src="img(cashOutInfo.headimg)" size="30" leftIcon="none" :default-url="img('static/resource/images/default_headimg.png')" />
                    </view>
                </template>
                <template v-if="cashOutInfo.transfer_type !== 'wechatpay'">
                    <view class="flex justify-between text-[28rpx] mt-[34rpx] leading-[32rpx]">
                        <text class="text-[#333] w-[200rpx]">{{ cashOutInfo.transfer_type == 'bank' ? t('bankRealname') : t('realname') }}</text>
                        <text class="text-[#333]">{{ cashOutInfo.transfer_realname }}</text>
                    </view>
                    <view class="flex justify-between text-[28rpx] mt-[34rpx] leading-[32rpx]" v-if="cashOutInfo.transfer_bank">
                        <text class="text-[#333] w-[200rpx]">{{ t('transferBank') }}</text>
                        <text class="text-[#333] truncate">{{ cashOutInfo.transfer_bank }}</text>
                    </view>
                    <view class="flex justify-between text-[28rpx] mt-[34rpx] leading-[32rpx]" v-if="cashOutInfo.transfer_type == 'wechat_code' || cashOutInfo.transfer_type == 'alipay'">
                        <text class="text-[#333] w-[200rpx]">{{ t('transferCode') }}</text>
                        <view class="flex items-center" @click="previewImg()">
                            <image :src="img(cashOutInfo.transfer_payment_code)" class="w-[140rpx] h-[140rpx] rounded-[var(--rounded-small)]" />
                        </view>
                    </view>
                </template>
            </view>
            <view class="sidebar-margin card-template my-[20rpx]" v-if="cashOutInfo.status == 3">
                <view class="title">{{ t('transferInfo') }}</view>
                <view class="flex justify-between text-[28rpx] leading-[32rpx]" v-if="cashOutInfo.transfer_time">
                    <text class="text-[#333] w-[200rpx]">{{ t('transferTime') }}</text>
                    <text class="text-[#333] truncate">{{ cashOutInfo.transfer_time }}</text>
                </view>
                <view class="flex justify-between text-[28rpx] mt-[34rpx] leading-[32rpx]" v-if="cashOutInfo.transfer_no">
                    <text class="text-[#333] w-[200rpx]">{{ t('transferNo') }}</text>
                    <text class="text-[#333] truncate">{{ cashOutInfo.transfer.transfer_no }}</text>
                </view>
                <view class="flex justify-between text-[28rpx] mt-[34rpx] leading-[32rpx]" v-if="cashOutInfo.transfer && cashOutInfo.transfer.transfer_remark">
                    <text class="text-[#333] w-[200rpx] flex-shrink-0">{{ t('transferRemark') }}</text>
                    <text class="text-[#333] ml-[20rpx]">{{ cashOutInfo.transfer.transfer_remark }}</text>
                </view>
                <view class="flex justify-between text-[28rpx] mt-[34rpx] leading-[32rpx]" v-if="cashOutInfo.transfer && cashOutInfo.transfer.transfer_voucher">
                    <text class="text-[#333] w-[200rpx]">{{ t('transferVoucher') }}</text>
                    <view class="flex items-center" @click="handleImg()">
                        <image :src="img(cashOutInfo.transfer.transfer_voucher)" class="w-[140rpx] h-[140rpx] rounded-[var(--rounded-small)]" />
                    </view>
                </view>
            </view>
            <view class="tab-bar-placeholder" v-if="cashOutInfo.status == 1 || cashOutInfo.status == 2 || cashOutInfo.status == 4"></view>
            <view class="fixed bottom-[0] tab-bar left-0 right-0 px-[var(--sidebar-m)] bg-[#fff] flex items-center justify-end min-h-[100rpx]"
                v-if="cashOutInfo.status == 1 || cashOutInfo.status == 2 || cashOutInfo.status == 4">
                <button class="min-w-[180rpx] box-border text-[26rpx] h-[70rpx] flex-center border-[2rpx] border-solid border-[#999] rounded-full !text-[var(--text-color-light6)] mr-0"
                    @click="cashOutCancel" v-if="cashOutInfo.status == 1 || cashOutInfo.status == 2 || cashOutInfo.status == 4">{{ t('cashOutCancel') }}</button>
                <button class="min-w-[180rpx] h-[70rpx] !text-[#fff] leading-[70rpx] primary-btn-bg rounded-[50rpx] text-[26rpx] ml-[20rpx] mr-0"
                    :loading="operateLoading" @click="cashOut" v-if="cashOutInfo.transfer_type == 'wechatpay' && cashOutInfo.status == 2">{{ t('cashOutNow') }}</button>
                <button class="min-w-[180rpx] h-[70rpx] !text-[#fff] leading-[70rpx] primary-btn-bg rounded-[50rpx] text-[26rpx] ml-[20rpx] mr-0"
                    :loading="operateLoading" @click="cashTransfer"
                    v-else-if="cashOutInfo.transfer_type == 'wechatpay' && cashOutInfo.status == 4">{{ t('cashOutNow') }}</button>
            </view>
        </template>
        <loading-page :loading="loading"></loading-page>
    </view>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { onLoad, onUnload } from '@dcloudio/uni-app'
import { t } from '@/locale'
import { redirect, img, goback, isWeixinBrowser } from '@/utils/common';
import { getCashOutDetail, getCashOutTransfer, memberCancel } from '@/app/api/member';
import wechat from '@/utils/wechat'
import useConfigStore from "@/stores/config";

// #ifdef H5
if (isWeixinBrowser()) wechat.init();
// #endif

const cashOutInfo = ref<any>({});
const current = ref(0);
const loading = ref<boolean>(true);
const operateLoading = ref(false)
let timer: any = null
let openId = uni.getStorageSync('openid') || ''
onLoad((option: any) => {
    let id = option.id || "";
    if (id) {
        getCashOutAccountListFn(id)
    } else {
        let parameter = {
            url: '/app/pages/member/cash_out',
            title: '提现详情不存在',
            mode: 'reLaunch'
        };
        goback(parameter);
    }
})

const getCashOutAccountListFn = (id: any) => {
    loading.value = true;

    getCashOutDetail(id).then((res: any) => {
        cashOutInfo.value = res.data;
        if (cashOutInfo.value.transfer_type == 'wechatpay') {
            if (cashOutInfo.value.status == 1) {
                current.value = 0;

            } else if (cashOutInfo.value.status == 2 || cashOutInfo.value.status == 4) {
                current.value = 1;
            } else if (cashOutInfo.value.status == 3) {
                current.value = 2;
            }
        }
        loading.value = false;
    }).catch(() => {
        loading.value = false;
    })
}

const cashOut = () => {

    if (!openId) {
        uni.showToast({
            title: '请到微信公众号或小程序中进行提现',
            icon: 'none'
        })
        return false
    }

    if (operateLoading.value) return false
    operateLoading.value = true

    let obj = {
        id: cashOutInfo.value.id,
        open_id: openId
    }

    getCashOutTransfer(obj).then((res: any) => {
        let obj = {
            mchId: res.data.extra.mch_id,
            appId: res.data.extra.appid,
            package: res.data.package_info
        }
        wechat.transfer(obj, (res: any) => {
            operateLoading.value = false;
            timer = setTimeout(() => {
                getCashOutAccountListFn(cashOutInfo.value.id)
                clearTimeout(timer)
            }, 6000)
        })
    }).catch((err:any)=>{
		operateLoading.value = false;
	})

}
const cashTransfer = () => {
    if (!openId) {
        uni.showToast({
            title: '请到微信公众号或小程序中进行提现',
            icon: 'none'
        })
        return false
    }
    if (operateLoading.value) return false
    operateLoading.value = true
    let obj = {
        mchId: cashOutInfo.value.transfer.extra.mch_id,
        appId: cashOutInfo.value.transfer.extra.appid,
        package: cashOutInfo.value.transfer.package_info
    }
    wechat.transfer(obj, (res: any) => {
        operateLoading.value = false;
        timer = setTimeout(() => {
            getCashOutAccountListFn(cashOutInfo.value.id)
            clearTimeout(timer)
        }, 6000)
    })
}

const cashOutCancel = () => {
    uni.showModal({
        title: '提示',
        content: '您确定取消提现吗？',
        confirmColor: useConfigStore().themeColor['--primary-color'],
        success: res => {
            if (res.confirm) {
                memberCancel({ id: cashOutInfo.value.id }).then(() => {
                    timer = setTimeout(() => {
                        getCashOutAccountListFn(cashOutInfo.value.id)
                        clearTimeout(timer)
                    })
                })
            }
        }
    })

}

const previewImg = () => {
    uni.previewImage({
        urls: [img(cashOutInfo.value.transfer_payment_code)],
        indicator: "number",
        loop: true
    })
}
const handleImg = () => {
    uni.previewImage({
        urls: [img(cashOutInfo.value.transfer.transfer_voucher)],
        indicator: "number",
        loop: true
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

<style lang="scss" scoped>
:deep(.u-text__value--main) {
    font-size: 24rpx !important;
    color: #606266;
}

:deep(.u-text__value) {
    font-size: 24rpx !important;
    color: #606266;
}

.tab-bar-placeholder {
    padding-bottom: calc(constant(safe-area-inset-bottom) + 100rpx);
    padding-bottom: calc(env(safe-area-inset-bottom) + 100rpx);
}

.tab-bar {
    padding-bottom: constant(safe-area-inset-bottom);
    padding-bottom: env(safe-area-inset-bottom);
}
</style>

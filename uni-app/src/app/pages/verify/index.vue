<template>
    <view :style="themeColor()">
        <view class="w-[100vw] min-h-[100vh] bg-[#f8f8f8]" v-if="!loading && isVerify">
            <view class="w-full bg-[#fff] verify-box h-[760rpx]">
                <view class="text-[var(--primary-color)] fixed top-[40rpx] right-[30rpx] flex items-center" @click="redirect({url:'/app/pages/verify/record'})">
                    <text class="nc-iconfont nc-icon-lishijiluV6xx !text-[28rpx] -mb-[2rpx]"></text>
                    <text class="text-[26rpx] ml-[8rpx]">核销记录</text>
                </view>
                <view v-show="operationType == 'sweepCode'" class="flex flex-col items-center justify-center">
                    <view class="sweep-code flex items-center justify-center" @click="scanCode">
                        <image class="w-[354rpx] h-[354rpx]" :src="img('static/resource/images/verify/saoma.png')" />
                    </view>
                    <view class="mt-[40rpx] text-[30rpx]">点击扫描二维码</view>
                    <view class="mt-[20rpx] text-[var(--text-color-light9)] text-[26rpx] font-400 pb-[142rpx]">扫描二维码进行核销</view>
                </view>

                <view v-show="operationType == 'manualInput'">
                    <view class="flex pt-[126rpx] items-center justify-center">
                        <view class="flex justify-center items-center flex-col pr-[30rpx] min-w-[130rpx]">
                            <image class="w-[100rpx] h-[100rpx]" :src="img('static/resource/images/verify/shuruhexiaoma.png')" />
                            <view class="text-[26rpx] h-[36rpx] leading-[36rpx] mt-[14rpx]">验证核销码</view>
                        </view>
                        <image class="w-[74rpx] h-[12rpx] mb-[50rpx]" :src="img('static/resource/images/verify/youjiantou.png')" />
                        <view class="flex justify-center items-center flex-col pl-[30rpx] min-w-[130rpx]">
                            <image class="w-[100rpx] h-[100rpx]" :src="img('static/resource/images/verify/hexiao1.png')" />
                            <view class="text-[26rpx] h-[36rpx] leading-[36rpx] mt-[14rpx]">核销</view>
                        </view>
                    </view>
                    <view class="mt-[50rpx]">
                        <view class="h-[90rpx] border-[2rpx] border-solid border-[#eee] rounded-[16rpx] box-border p-[20rpx] mx-[60rpx] flex items-center">
                            <text class="nc-iconfont nc-icon-saotiaoxingmaV6xx text-[44rpx] text-[#EF000C]"></text>
                            <input type="text" placeholder="请输入核销码"
                                   class="h-[90rpx] border-none text-start ml-[30rpx] text-[28rpx] flex-1"
                                   placeholder-class="_placeholder" v-model="verify_code" :focus="isFocus" ref="input" />
                        </view>
                        <view class="h-[80rpx] primary-btn-bg min-w-[630rpx] text-[#fff] flex-center !text-[26rpx] save-btn rounded-[100rpx] h-[80rpx] font-500 mx-[60rpx] mt-[146rpx] relative z-1" @click="confirm">核销</view>
                    </view>
                </view>
            </view>

            <view class="w-[630rpx] h-[100rpx] bg-[#fff] mx-[auto] mt-[220rpx] rounded-[90rpx] flex relative action-type-wrap">
                <view class="relative w-[51%] pr-[50rpx] box-border rounded-[50rpx] z-0 flex flex-col items-center justify-center"
                    @click="changeOperationType('sweepCode')" :class="{'xuanZhong1': operationType == 'sweepCode'}">
                    <text class="nc-iconfont nc-icon-saoyisaoV6xx !text-[40rpx]"></text>
                    <view class="text-[24rpx] leading-[1] mt-[10rpx]">扫码核销</view>
                </view>
                <view class="flex flex-col items-center flex-col w-[120rpx] h-[120rpx] bg-[#FF7354] rounded-[50%] absolute top-[-10rpx] left-[255rpx] heXiao text-white z-10 shrink-0">
                    <view class="nc-iconfont nc-icon-saotiaoxingmaV6xx ns-gradient-otherpages-member-balance-balance-rechange !text-[44rpx] mt-[19rpx]"></view>
                    <view class="text-[24rpx] mt-[8rpx] leading-[34rpx] h-[34rpx]">核销台</view>
                </view>
                <view class="relative w-[51%] pl-[50rpx] box-border rounded-[50rpx] z-0 flex flex-col items-center justify-center"
                    :class="{'xuanZhong': operationType == 'manualInput'}" @click="changeOperationType('manualInput')">
                    <text class="iconfont iconVector-77 !text-[40rpx]"></text>
                    <view class="ml-[20rpx] text-[24rpx] leading-[1] mt-[10rpx]" @click="focus">手动输入</view>
                </view>
            </view>

            <!-- #ifdef MP-WEIXIN -->
            <!-- 小程序隐私协议 -->
            <privacy-popup ref="privacyPopup"></privacy-popup>
            <!-- #endif -->
        </view>
        <view class="w-[100vw] min-h-[100vh] bg-[#f8f8f8] overflow-hidden" v-if="!loading && !isVerify">
            <view class="empty-page">
                <image class="img" :src="img('static/resource/images/system/empty.png')" mode="aspectFit" />
                <view class="desc">非核销员无此权限</view>
            </view>
        </view>
        <loading-page :loading="loading"></loading-page>
    </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { img, redirect, isWeixinBrowser, getToken } from '@/utils/common';
import { onShow } from '@dcloudio/uni-app'
import { getVerifierInfo, getCheckVerifier } from '@/app/api/verify'
import { t } from '@/locale'
import wechat from '@/utils/wechat'

const operationType = ref('manualInput'); //类型
// #ifdef H5
operationType.value = 'manualInput';
// #endif
// #ifndef H5
operationType.value = 'sweepCode';
// #endif

const isFocus = ref(false)
const verify_code = ref('');
const loading = ref(true)
const isVerify = ref(false)
onShow(() => {
    if (getToken()) checkIsVerifier();
})

// 检测是否是核销员
const checkIsVerifier = () => {
    getCheckVerifier().then((res: any) => {
        if (!res.data) {
            isVerify.value = false
            loading.value = false;
            // uni.showToast({
            // 	title: '非核销员无此权限',
            // 	icon: 'none'
            // });
            // setTimeout(() => {
            // 	if(getCurrentPages().length > 1){
            //         uni.navigateBack({
            //             delta: 1
            //         });
            //     }else{
            //         redirect({
            //             url: '/app/pages/member/index',
            //             mode: 'reLaunch'
            //         });
            //     }
            // }, 1000);
        } else {
            isVerify.value = true
            loading.value = false;
        }
    })
}

const scanCode = () => {
    // #ifdef MP
    uni.scanCode({
        onlyFromCamera: true,
        success: res => {
            if (res.errMsg == 'scanCode:ok') {
                let code = res.result;
                redirect({ url: '/app/pages/verify/verify', param: { code } })
            } else {
                uni.showToast({
                    title: res.errorMsg,
                    icon: 'none'
                });
            }
        }
    });
    // #endif

    // #ifdef H5
    if (isWeixinBrowser()) {
        wechat.init();
        wechat.scanQRCode(res => {
            if (res.resultStr) {
                let code = res.resultStr;
                redirect({ url: '/app/pages/verify/verify', param: { code } })
            }
        });
    }
    // #endif
}

let isLoading = false;
const confirm = () => {
    const reg = /[\S]+/;
    if (!reg.test(verify_code.value)) {
        uni.showToast({
            title: '请输入核销码',
            icon: 'none'
        });
        return false;
    }

    if (isLoading) return false;
    isLoading = true;

    getVerifierInfo({ code: verify_code.value }).then((res: any) => {
        isLoading = false;
        redirect({ url: '/app/pages/verify/verify', param: { code: verify_code.value } })
    }).catch(() => {
        isLoading = false;
    })
}

const focus = () => {
    isFocus.value = !isFocus.value;
}

const changeOperationType = (type: string) => {
    // #ifdef H5
    if (type == 'sweepCode' && !isWeixinBrowser()) {
        uni.showToast({
            title: 'H5端不支持扫码核销',
            icon: 'none'
        });
        return;
    }
    // #endif
    operationType.value = type;
}
</script>
<style lang="scss" scoped>
.action-type-wrap {
    box-shadow: 0 6px 6px 0 rgba(0, 0, 0, 0.03), 0 4px 2px 0 rgba(0, 0, 0, 0.04);
}

.heXiao {
    background: linear-gradient(180deg, #FF7354 0%, #FF020F 100%), #EF000C;
}

.xuanZhong {
    background: linear-gradient(270deg, #FFD1D1 0%, rgba(255, 209, 209, 0.1) 100%), #FFFFFF;
    color: #EF000C;
}

.xuanZhong1 {
    background: linear-gradient(90deg, #FFD1D1 0%, rgba(255, 209, 209, 0.1) 100%), #FFFFFF;
    color: #EF000C;
}

.sweep-code {
    width: 354rpx;
    height: 354rpx;
    box-shadow: 0 8px 8px 0 rgba(0, 0, 0, 0.03), 0 6px 3px 0 rgba(0, 0, 0, 0.02);
    border-radius: 50%;
    margin-top: 146rpx;
}

.verify-box {
    border-bottom-left-radius: 400rpx 60rpx;
    border-bottom-right-radius: 400rpx 60rpx;
}

._placeholder {
    color: var(--text-color-light9);
    font-size: 28rpx;
}
</style>

<template>
    <view :style="themeColor()" class="bg-[var(--page-bg-color)] min-h-[100vh] overflow-hidden">
        <template v-if="!loading && verifyInfo && verifyInfo.value">
            <view class="p-[30rpx] bg-[#fff]">
                <view class="text-[var(--primary-color)] fixed top-[40rpx] right-[30rpx] flex items-center" @click="redirect({url:'/app/pages/verify/record'})">
                    <text class="nc-iconfont nc-icon-lishijiluV6xx !text-[28rpx] -mb-[2rpx]"></text>
                    <text class="text-[26rpx] ml-[8rpx]">核销记录</text>
                </view>
                <view class="flex items-center mt-[60rpx] justify-center">
                    <view class="flex justify-center items-center flex-col pr-[30rpx]">
                        <image class="w-[100rpx] h-[100rpx]" :src="img('static/resource/images/verify/yanzhenghexiaoma.png')" />
                        <view class="text-[26rpx] mt-[14rpx] h-[36rpx] leading-[36rpx] flex-shrink-0">验证核销码</view>
                    </view>
                    <image class="w-[74rpx] h-[12rpx] mb-[50rpx]" :src="img('static/resource/images/verify/youjiantou.png')" />
                    <view class="flex justify-center items-center flex-col pl-[30rpx]">
                        <image class="w-[100rpx] h-[100rpx]" :src="img('static/resource/images/verify/hexiao.png')" />
                        <view class="text-[26rpx] mt-[14rpx] h-[36rpx] leading-[36rpx] flex-shrink-0">确定核销</view>
                    </view>
                </view>
            </view>
            <view class="card-template mt-[var(--top-m)] sidebar-margin">
                <view class="flex" :class="{'mb-[var(--top-m)]': (verifyInfo.value.list.length - 1 != index)}" v-for="(item,index) in verifyInfo.value.list" :key="index">
                    <image class="w-[150rpx] h-[150rpx] rounded-[var(--goods-rounded-big)]" mode="aspectFill" v-if="item.cover" :src="img(item.cover)"/>
                    <image class="w-[150rpx] h-[150rpx] rounded-[var(--goods-rounded-big)]" mode="aspectFill" v-else :src="img('addon/tourism/tourism/member/hotel.png')"/>
                    <view class="flex flex-col flex-1 ml-[20rpx] py-[4rpx]">
						<view class="leading-[1]">
							<view class="leading-[40rpx] truncate max-w-[490rpx] text-[28rpx]">{{ item.name }}</view>
							<view class="mt-[14rpx] truncate text-[24rpx] text-[var(--text-color-light9)] leading-[28rpx] max-w-[490rpx]" v-if="item.sub_name">{{ item.sub_name }}</view>
						</view>
						<view class="flex items-center mt-[20rpx]">
							 <view class="text-[var(--text-color-light6)] text-[28rpx] ">x{{item.verify_num }}</view>
							<view class="leading-[1] ml-3 text-[var(--price-text-color)] text-[28rpx]">
								{{item.un_use_msg}}
							</view>
						</view>
                       
                    </view>
                </view>
            </view>

            <view class="card-template mt-[var(--top-m)] sidebar-margin">
                <view class="title">核销信息</view>
                <view class="card-template-item justify-between">
                    <text class="text-[28rpx] text-[#333]">核销类型</text>
                    <view class="text-[28rpx] text-[#333]">{{ verifyInfo.type_name }}</view>
                </view>
                <view class="card-template-item justify-between" v-for="(item,index) in verifyInfo.value.content.fixed">
                    <text class="text-[28rpx] text-[#333]">{{ item.title }}</text>
                    <view class="text-[28rpx] text-[#333]">{{ item.value }}</view>
                </view>
            </view>

            <view v-for="(item,index) in verifyInfo.value.content.diy" :key="index"
                  class="card-template mt-[var(--top-m)] sidebar-margin">
                <view class="title">{{ item.title }}</view>
                <view class="card-template-item justify-between" v-for="(subItem,subIndex) in item.list" :key="subIndex">
                    <text class="text-[28rpx] text-[#333]">{{ subItem.title }}</text>
                    <text class="text-[28rpx] text-[#333]">{{ subItem.value }}</text>
                </view>
            </view>
            <view class="common-tab-bar w-[100%]"></view>
            <view class="verify-tab-bar fixed flex-center !text-[26rpx] rounded-[50rpx] h-[80rpx] left-[20rpx] right-[20rpx] text-[#fff] font-500" :class="verifyInfo.is_can_use ? 'primary-btn-bg' : 'bg-[#ccc]'" @click="verifyFn">确定</view>

        </template>
        <loading-page :loading="loading"></loading-page>
    </view>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { onLoad, onShow } from '@dcloudio/uni-app'
import { img, redirect, getToken } from '@/utils/common';
import { getVerifierInfo, getCheckVerifier, verify } from '@/app/api/verify'
import { t } from '@/locale'

const loading = ref(true)
const code = ref('');
onLoad((option: any) => {
    if (option.code) code.value = option.code;
    // 小程序扫码进入
    if (option.scene) {
        let sceneParams: any = decodeURIComponent(option.scene).split('&');
        if (sceneParams.length) {
            sceneParams.forEach((item: any) => {
                if (item.indexOf('code') != -1) code.value = item.split('-')[1];
            });
        }
    }
})

onShow(() => {
    if (getToken()) {
        checkIsVerifier();
        getVerifierInfoFn();
    }
})

// 检测是否是核销员
const checkIsVerifier = () => {
    getCheckVerifier().then((res: any) => {
        if (!res.data) {
            uni.showToast({
                title: '非核销员无此权限',
                icon: 'none'
            });
            setTimeout(() => {
                if (getCurrentPages().length > 1) {
                    uni.navigateBack({
                        delta: 1
                    });
                } else {
                    redirect({
                        url: '/app/pages/member/index',
                        mode: 'reLaunch'
                    });
                }
            }, 1000);
        } else {
            loading.value = false;
        }
    })
}

const verifyInfo = ref({})
const getVerifierInfoFn = () => {
    loading.value = true;
    getVerifierInfo({ code: code.value }).then((res: any) => {
        verifyInfo.value = res.data;
        loading.value = false;
    }).catch(() => {
        setTimeout(() => {
            loading.value = false;
            redirect({ url: '/app/pages/verify/index', param: {}, mode: 'redirectTo' })
        }, 1000);
    })
}
let isLoading = false;
const verifyFn = () => {
	if (!verifyInfo.value.is_can_use) return;
    if (isLoading) return false;
    isLoading = true;

    verify(code.value).then((res: any) => {
        uni.showToast({
            title: '核销成功',
            icon: 'none'
        });
        setTimeout(() => {
            isLoading = false;
            redirect({ url: '/app/pages/verify/index', param: {}, mode: 'redirectTo' })
        }, 1000);
    }).catch(() => {
        isLoading = false;
    })
}
</script>

<style lang="scss" scoped>
.verify-tab-bar{
    bottom: calc(constant(safe-area-inset-bottom) + 30rpx);
    bottom: calc(env(safe-area-inset-bottom) + 30rpx);
}
</style>

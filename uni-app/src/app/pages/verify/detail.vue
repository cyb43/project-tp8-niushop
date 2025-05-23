<template>
    <view :style="themeColor()" class="bg-[var(--page-bg-color)] min-h-[100vh] overflow-hidden">
        <template v-if="!loading">
            <view class="pt-[20rpx] sidebar-margin">
                <view class="flex flex-col card-template">
                    <view class="flex" :class="{'mb-[20rpx]': verifyInfo.value.list.length-1 != index}" v-for="(item,index) in verifyInfo.value.list" :key="index">
                        <image class="w-[150rpx] h-[150rpx] rounded-[var(--goods-rounded-big)]" mode="aspectFill" v-if="item.cover" :src="img(item.cover)"/>
                        <image class="w-[150rpx] h-[150rpx] rounded-[var(--goods-rounded-big)]" mode="aspectFill" v-else :src="img('addon/tourism/tourism/member/hotel.png')"/>
                        <view class="flex flex-col flex-1 ml-[20rpx] py-[4rpx]">
                            <view class="leading-[40rpx] truncate max-w-[490rpx] text-[28rpx]">{{ item.name }}</view>
                            <view class="mt-[14rpx] truncate text-[24rpx] text-[var(--text-color-light9)] leading-[28rpx] max-w-[490rpx] " v-if="item.sub_name">{{ item.sub_name }}</view>
                            <view class="text-[var(--text-color-light6)] text-[26rpx] mt-[20rpx]">x{{item.verify_num}}</view>
                        </view>
                    </view>
                </view>

                <view class="flex flex-col card-template mt-[20rpx] text-[28rpx]">
                    <view class="title">核销信息</view>
                    <view class="card-template-item justify-between">
                        <text class="text-[28rpx] text-[#333]">核销类型</text>
                        <view class="text-[28rpx] text-[#333]">{{ verifyInfo.type_name }}</view>
                    </view>
                    <view class="card-template-item justify-between">
                        <text class="text-[28rpx] text-[#333]">核销状态</text>
                        <view class="text-[28rpx] text-[#333]">已核销</view>
                    </view>
                    <view class="card-template-item justify-between">
                        <text class="text-[28rpx] text-[#333]">核销时间</text>
                        <view class="text-[#333333] text-[28rpx]">{{ verifyInfo.create_time }}</view>
                    </view>
                    <view class="card-template-item justify-between">
                        <text class="text-[28rpx] text-[#333]">核销人员</text>
                        <view class="text-[#333333] text-[28rpx]">{{ verifyInfo.member ? verifyInfo.member.nickname : '--' }}</view>
                    </view>
                    <view class="card-template-item justify-between" v-for="(item,index) in verifyInfo.value.content.fixed">
                        <text class="text-[28rpx] text-[#333]">{{ item.title }}</text>
                        <view class="text-[28rpx] text-[#333]">{{ item.value }}</view>
                    </view>
                </view>

                <view v-for="(item,index) in verifyInfo.value.content.diy" :key="index" class="card-template top-mar">
                    <view class="title">{{ item.title }}</view>
                    <view class="card-template-item justify-between" v-for="(subItem,subIndex) in item.list" :key="subIndex" :class="{'mt-30rpx' : subIndex == '0'}">
                        <text class="text-[28rpx] text-[#333]">{{ subItem.title }}</text>
                        <view class="text-[28rpx] text-[#333]">{{ subItem.value }}</view>
                    </view>
                </view>
            </view>

        </template>
        <loading-page :loading="loading"></loading-page>
    </view>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { onLoad, onShow } from '@dcloudio/uni-app'
import { img, redirect, getToken } from '@/utils/common';
import { getVerifyDetail } from '@/app/api/verify'
import { t } from '@/locale'

const loading = ref(true)
const code = ref('');
onLoad((option: any) => {
    if (option.code) code.value = option.code;
})

onShow(() => {
    if (getToken()) {
        getVerifyDetailFn();
    }
})

const verifyInfo = ref({})
const getVerifyDetailFn = () => {
    loading.value = true;
    getVerifyDetail(code.value).then((res: any) => {
        verifyInfo.value = res.data;
        loading.value = false;
    })
}
</script>

<template>
    <view @touchmove.prevent.stop>
        <u-popup :show="messagePopup" @close="closeFn" mode="center" round="var(--rounded-big)">
            <view class="w-[570rpx] px-[32rpx] popup-common center" v-if="Object.keys(configInfo).length">
                <view class="title">{{ configInfo.pay_explain_title }}</view>
                <scroll-view :scroll-y="true" class="px-[30rpx] box-border max-h-[260rpx]" v-if="configInfo.pay_explain_content">
                    <template v-for="(item) in configInfo.pay_explain_content.split('\n')">
                        <view class="text-[28rpx] leading-[40rpx] mb-[20rpx]">{{ item }}</view>
                    </template>
                </scroll-view>
                <view class="btn-wrap !pt-[40rpx]">
                    <button class="primary-btn-bg w-[480rpx] h-[70rpx] text-[26rpx] leading-[70rpx] rounded-[35rpx] !text-[#fff] font-500"  @click="closeFn">我知道了</button>
                </view>
            </view>
        </u-popup>
    </view>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const messagePopup = ref(false)
const configInfo = ref<any>({})
const closeFn = () =>{
	messagePopup.value = false
}
const open = (data: any) => {
    configInfo.value = data
    messagePopup.value = true;
}

defineExpose({
    open
})
</script>

<style lang="scss" scoped>

</style>

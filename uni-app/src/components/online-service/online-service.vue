<template>
    <view @touchmove.prevent.stop>
        <u-popup class="popup-type" mode="center" :show="servicesDataShow" @close="servicesDataShow = false" bgColor="transparent" overlayOpacity="0.8">
            <view @touchmove.prevent.stop class="flex flex-colitems-center">
                <view class="bg-[#ffff] rounded-[34rpx]">
                    <view class="text-center text-[30rpx] mt-[40rpx] mb-[40rpx]">联系在线客服</view>
                    <scroll-view scroll-y="true" class="w-[570rpx] pb-[60rpx] max-h-[580rpx] overflow-y-auto">
                        <view class="flex flex-col items-center justify-center px-[30rpx]" v-if="data.customer_phone">
                            <text class="text-[45rpx]">{{ data.customer_phone }}</text>
                            <view class="rounded-[30rpx] mt-[30rpx] bg-[#EF000C] text-[#fff] h-[60rpx] leading-[60rpx] w-[240rpx] text-center text-[24rpx]" @click="makePhoneCallFn(data.customer_phone)">一键拨打</view>
                        </view>
                        <view v-if="data.customer_phone" class="w-full h-[1rpx] bg-dashed-style my-[40rpx]"></view>
                        <view class="px-[24rpx] flex flex-col items-center justify-center" v-if="data.customer_qrcode">
                            <view class="border border-[#eee] border-solid rounded-[10rpx] border-[1rpx] w-[240rpx] h-[240rpx] flex items-center justify-center">
                                <image class="w-[226rpx] h-[226rpx]" :src="img(data.customer_qrcode)" mode="widthFix" />
                            </view>
                            <view class="text-[26rpx] text-[#333] mt-[20rpx]">扫描二维码添加客服</view>
                        </view>
                        <view class="px-[50rpx] mt-[16rpx] text-[22rpx] text-[#999] leading-[1.5]" style="text-indent: 2em;">{{ data.customer_guided_words }}</view>
                    </scroll-view>
                </view>
                <!-- <view @click="servicesDataShow = false" class="mt-[50rpx] nc-iconfont nc-icon-cuohaoV6xx1 !text-[50rpx] text-[#fff]"></view> -->
            </view>
        </u-popup>
    </view>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { redirect, img } from '@/utils/common'

const props = defineProps({
    data: {
        type: Object,
        default: {}
    }
})

const servicesDataShow = ref<boolean>(false)
const data = computed(() => {
    return props.data;
})

const open = () => {
    servicesDataShow.value = true;
}

const makePhoneCallFn = (data: any)=>{
    uni.makePhoneCall({
        phoneNumber: data,
        success: (res) => {
        },
        fail: (res) => {
        }
    });
}

defineExpose({
    open
})
</script>

<style scoped lang="scss">
.bg-dashed-style {
  background: linear-gradient(to right, #eee 60%, transparent 40%);
  background-size: 24rpx 100%; /* 控制线段长度（20rpx）和间隔 */
  background-repeat: repeat-x;
}
</style>

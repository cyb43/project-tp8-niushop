<template>
    <view @touchmove.prevent.stop>
        <u-popup :show="showpop" mode="bottom" :round="10" @close="close" :closeable="true" :safeAreaInsetBottom="true" @touchmove.prevent.stop>
            <view class="h-[70vh] px-[24rpx] bg-page pb-[20rpx]" @touchmove.prevent.stop v-if="Object.keys(showList).length">
                <view class="font-500 text-center text-[32rpx] leading-[104rpx] box-border h-[104rpx]">{{ t('detailedInformation') }}</view>
                <scroll-view :scroll-x="true" scroll-with-animation :scroll-into-view="'id' + (subActive>3 ? subActive - 2 : 0)">
                    <view class="flex py-[22rpx] whitespace-nowrap" v-if="packageList.length > 1">
                        <view :id="'id' + index" class="text-[26rpx] leading-[36rpx] mr-[30rpx] text-[#626779]"
                              :class="{'!text-primary class-select': item.id ==  current}"
                              v-for="(item,index) in packageList" :key="index" @click="handleClick(item,index)">
                            {{ item.name }}
                        </view>
                    </view>
                </scroll-view>
                <view class="text-[28rpx] mt-[20rpx] ">
                    <view class="flex justify-between mb-[20rpx]">
                        <template v-if="showList.sub_delivery_type == 'none_express'">
                            <text class="mr-[20rpx]">无需物流</text>
                        </template>
                        <template v-else>
                            <text class="mr-[20rpx]">{{ showList.company.company_name }}</text>
                            <view>
                                <text class="mr-[14rpx]">{{ showList.express_number }}</text>
                                <text @click="copy(showList.express_number)">{{ t('copy') }}</text>
                            </view>
                        </template>
                    </view>
                </view>
                <view class="parcel" style="height: 53vh;" v-if="showList.sub_delivery_type == 'express'">
                    <view class="h-[56vh] flex flex-col items-center justify-center"
                          v-if="!Object.keys(showList.traces).length || showList.traces.success == false">
                        <text class="nc-iconfont nc-icon-daishouhuoV6xx text-[180rpx] text-[#bfbfbf]"></text>
                        <view class="text-[28rpx] text-[#bfbfbf] leading-8">暂无物流信息～～</view>
                    </view>
                    <scroll-view v-else :scroll-y="true" style="height:53vh;padding: 20rpx;box-sizing: border-box;"
                                 class="bg-white rounded-md">
                        <u-steps :current="0" dot direction="column" activeColor="var(--primary-color)">
                            <template v-for="(item,index) in showList.traces.list" :key="index + 'id'">
                                <u-steps-item :title="item.remark" :desc="item.datetime"></u-steps-item>
                            </template>
                        </u-steps>
                    </scroll-view>
                </view>
                <view style="height: 53vh;" v-else-if="showList.sub_delivery_type == 'none_express'">
                    <view class="h-[56vh] flex-col flex items-center justify-center">
                        <text class="nc-iconfont nc-icon-daishouhuoV6xx text-[180rpx] text-[#bfbfbf]"></text>
                        <view class="text-[28rpx] text-[#bfbfbf] leading-8">无需物流～～</view>
                    </view>
                </view>
            </view>
        </u-popup>
    </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { t } from '@/locale'
import { img, redirect, copy } from '@/utils/common';
import { getMaterialflowList } from '@/addon/shop/api/order';

const showpop = ref(false)
const packageList = ref([])
const showList = ref<any>({})
const loadList = async(params: any) => {
    let data: any = await getMaterialflowList(params)
    showList.value = data.data
}
const current = ref(0)
const subActive = ref(0)

const open = (params: any) => {
    current.value = params.id
    loadList(params)
    showpop.value = true
}

const close = () => {
    showpop.value = false
}
const handleClick = (item: any, index: number) => {
    current.value = item.id
    subActive.value = index
    let params = {
        id: item.id,
        mobile: item.mobile
    }
    loadList(params)
}
defineExpose({
    packageList,
    open
})
</script>

<style lang="scss" scoped>
.parcel :deep(.u-text__value) {
    font-size: 24rpx !important;
    line-height: 42rpx !important;
}

.parcel :deep(.u-steps-item__content) {
    margin-left: 20rpx !important;
}

.class-select {
    position: relative;
    font-weight: 500;

    &::before {
        content: "";
        position: absolute;
        bottom: -6rpx;
        height: 4rpx;
        background-color: $u-primary;
        width: 26rpx;
        left: 50%;
        transform: translateX(-50%);
    }
}
</style>


<template>
    <u-popup :show="show" @close="show = false" mode="bottom" :round="10" :closeable="true">
        <view @touchmove.prevent.stop class="popup-common">
            <view class="title">请选择优惠券</view>
            <view v-if="!type" class="-mt-[20rpx]">
                <u-tabs :list="tabs" @click="switchTab" :current="current"
                        itemStyle="width:50%;height:88rpx;box-sizing: border-box; font-size: 28rpx;"
                        :activeStyle="{ color: 'var(--primary-color)' }" lineColor="var(--primary-color)"></u-tabs>
            </view>
            <scroll-view scroll-y="true" class="h-[50vh] pt-[10rpx]">
                <view class="px-[var(--popup-sidebar-m)] pb-[30rpx] pt-0 text-sm" v-show="current == 0">
                    <view class="mt-[var(--top-m)] px-[var(--pad-sidebar-m)] py-[var(--pad-top-m)] border-1 border-[#eee] border-solid rounded-[20rpx]"
                        :class="{'!border-[var(--primary-color)] bg-[var(--primary-color-light2)]': coupon && coupon.id == item.id}"
                        v-for="item in couponList" @click="selectCoupon(item)">
                        <view class="flex border-0 !border-b border-[#eee] border-dashed pb-[20rpx]" :class="{ '!border-[var(--primary-color)]': coupon && coupon.id == item.id }">
                            <view class="flex-1 w-0">
                                <view class="text-[30rpx] mb-[20rpx] font-500">{{ item.title }}</view>
                                <view class="text-[24rpx] text-[var(--text-color-light6)]" v-if="item.min_condition_money > 0">满{{ item.min_condition_money }}可用</view>
                                <view class="text-[24rpx] text-[var(--text-color-light6)]" v-else>无门槛券</view>
                            </view>
                            <view class="text-[36rpx] price-font">
                                <text class="text-xs mr-[2rpx]">￥</text>
                                {{ item.price }}
                            </view>
                        </view>
                        <view class="pt-[20rpx] text-[24rpx] text-[var(--text-color-light6)]">{{ item.create_time }} ~ {{ item.expire_time }}有效</view>
                    </view>
                </view>
                <view class="px-[var(--popup-sidebar-m)] pb-[30rpx] pt-0 text-sm" v-show="current == 1">
                    <view
                        class="mt-[var(--top-m)] px-[var(--pad-sidebar-m)] py-[var(--pad-top-m)] border-1 !border-[#eee] border-solid rounded-[var(--rounded-mid)] bg-[var(--temp-bg)]"
                        v-for="item in disabledCouponList">
                        <view class="flex border-0 !border-b !border-[#ddd] border-dashed pb-[20rpx]">
                            <view class="flex-1 w-0">
                                <view class="text-[30rpx] mb-[20rpx] font-500">{{ item.title }}</view>
                                <view class="text-[24rpx] text-[var(--text-color-light9)]" v-if="item.min_condition_money > 0">满{{ item.min_condition_money }}可用</view>
                                <view class="text-[24rpx] text-[var(--text-color-light9)]" v-else>无门槛券</view>
                            </view>
                            <view class="text-[36rpx] price-font">
                                <text class="text-xs mr-[2rpx]">￥</text>
                                {{ item.price }}
                            </view>
                        </view>
                        <view class="pt-[20rpx] text-[24rpx]  text-[var(--text-color-light9)]">{{ item.create_time }} ~ {{ item.expire_time }}期间有效</view>
                        <view class="text-[24rpx] pt-[10rpx] flex  text-[var(--text-color-light9)]">不可用原因：{{ item.error }}</view>
                    </view>
                </view>
            </scroll-view>
            <view class="btn-wrap">
                <button class="primary-btn-bg btn" @click="confirm">确认</button>
            </view>
        </view>
    </u-popup>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { orderCoupon } from '@/addon/shop/api/order'

const prop = defineProps({
    orderKey: {
        type: String,
        default: ''
    }
})

const current = ref(0)
const couponList = ref<object[]>([])
const disabledCouponList = ref<object[]>([])
const show = ref(false)
const coupon = ref<null | object>(null)
const emits = defineEmits(['confirm'])

watch(() => prop.orderKey, () => {
    if (prop.orderKey && !couponList.value.length) {
        orderCoupon({ order_key: prop.orderKey }).then(({ data }) => {
            const list = [], disabled = []

            if (data.length) {
                data.forEach(item => {
                    item.is_normal ? list.push(item) : disabled.push(item)
                })
            }

            disabledCouponList.value = disabled
            couponList.value = list

            if (list.length) {
                coupon.value = list[0]
                emits('confirm', coupon.value)
            }
        }).catch()
    }
}, { immediate: true })

const tabs = computed(() => {
    return [
        { name: `可用优惠券(${ couponList.value.length })`, key: 'normal' },
        { name: `不可用优惠券(${ disabledCouponList.value.length })`, key: 'disabled' }
    ]
})

const switchTab = (event: any) => {
    current.value = event.index
}

const open = (coupon_id: any) => {
    show.value = true
    if (coupon_id) {
        for (let i = 0; i < couponList.value.length; i++) {
            if (couponList.value[i].id == coupon_id) {
                coupon.value = couponList.value[i];
                break;
            }
        }
    }
}

const selectCoupon = (data: object) => {
    if (coupon.value) {
        coupon.value = coupon.value.id != data.id ? data : null
    } else {
        coupon.value = data
    }
}

const confirm = () => {
    emits('confirm', coupon.value)
    show.value = false
}

defineExpose({
    open,
    couponList
})
</script>

<style lang="scss" scoped></style>

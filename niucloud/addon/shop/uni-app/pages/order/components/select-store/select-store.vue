<template>
    <u-popup :show="show" @close="show = false" mode="bottom" :round="10">
        <view class="popup-common">
            <view class="title">请选择自提点</view>
            <scroll-view scroll-y="true" class="h-[50vh]">
                <view class="p-[var(--popup-sidebar-m)] pt-0 text-sm">
                    <view
                        class="mt-[var(--top-m)] border-1 border-[#eee] border-solid rounded-[var(--rounded-mid)] px-[var(--pad-sidebar-m)] py-[var(--pad-top-m)] mb-[var(--top-m)]"
                        :class="{'!border-primary bg-[var(--primary-color-light2)]': store && store.store_id == item.store_id}"
                        v-for="item in storeList" @click="selectStore(item)">
                        <view class="flex">
                            <view class="flex-1 w-0">
                                <text class="text-[30rpx] text-[#333]">{{ item.store_name }}</text>
                                <text class="text-[26rpx] ml-[12rpx] text-[var(--text-color-light6)]">{{ item.store_mobile }}</text>
                            </view>
                            <view v-if="item.distance">
                                <text class="text-red text-[26rpx] font-normal">{{ distanceFormat(item.distance) }}</text>
                            </view>
                        </view>
                        <view class="mt-[20rpx] text-[26rpx] leading-[1.4] flex">
                            <text class="flex-shrink-0">门店地址：</text>
                            <text>{{ item.full_address }}</text>
                        </view>
                        <view class="mt-[16rpx] text-[26rpx]">营业时间：{{ item.trade_time }}</view>
                    </view>
                </view>
                <view class="h-[50vh] flex items-center flex-col justify-center" v-if="loading">
                    <u-loading-icon :vertical="true"></u-loading-icon>
                </view>
                <view class="h-[95%] flex items-center flex-col justify-center" v-if="!loading && !storeList.length">
                    <u-empty text="没有可选择的自提点" width="214" :icon="img('static/resource/images/empty.png')" />
                </view>
            </scroll-view>
            <view class="btn-wrap">
                <button class="primary-btn-bg btn" @click="confirm">确认</button>
            </view>
        </view>
    </u-popup>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { getStoreList } from '@/addon/shop/api/order'
import { img } from '@/utils/common'

const show = ref(false)
const loaded = ref(false)
const loading = ref(true)
const storeList = ref<object[]>([])
const store = ref<null | object>(null)
const latlng = reactive({
    lat: 0,
    lng: 0
})

const getStoreListFn = (callback: any) => {
    if (!loaded.value) {
        loaded.value = true

        if (uni.getStorageSync('location_address')) {
            let location_address = uni.getStorageSync('location_address');
            latlng.lat = location_address.latitude;
            latlng.lng = location_address.longitude;
        } else {
            uni.getLocation({
                type: 'gcj02',
                success: (res) => {
                    latlng.lat = res.latitude
                    latlng.lng = res.longitude
                },
                fail: (res) => {
                    if (res.errno) {
                        if (res.errno == 104) {
                            let msg = '用户未授权隐私权限，获取位置失败';
                            uni.showToast({ title: msg, icon: 'none' })
                        } else if (res.errno == 112) {
                            let msg = '隐私协议中未声明，获取位置失败';
                            uni.showToast({ title: msg, icon: 'none' })
                        }
                    }
                    if (res.errMsg) {
                        if (res.errMsg.indexOf('getLocation:fail') != -1 || res.errMsg.indexOf('deny') != -1 || res.errMsg.indexOf('denied') != -1) {
                            let msg = '用户未授权获取位置权限，将无法提供距离最近的门店';
                            uni.showToast({ title: msg, icon: 'none' })
                        } else {
                            uni.showToast({ title: res.errMsg, icon: 'none' })
                        }
                    }
                }
            });
        }

        setTimeout(() => {
            getStoreList({ latlng }).then(({ data }) => {
                storeList.value = data
                if (data.length) {
                    selectStore(data[0]) // 默认选择第一个
                }
                if (typeof callback == 'function') {
                    callback(data);
                }
                loading.value = false
            }).catch(() => {
                loading.value = false
            })
        }, 1500)
    }
}

const open = () => {
    show.value = true
}

const selectStore = (data: object) => {
    if (store.value) {
        store.value = store.value.store_id != data.store_id ? data : null
    } else {
        store.value = data
    }
}

const emits = defineEmits(['confirm'])

const confirm = () => {
    emits('confirm', store.value)
    show.value = false
}

const distanceFormat = (distance: any) => {
    distance = parseFloat(distance)
    if (distance < 1000) {
        return `${ distance }m`;
    } else {
        const km = (distance / 1000).toFixed(2)
        return `${ km }km`;
    }
}

defineExpose({
    open,
    getData: getStoreListFn
})
</script>

<style lang="scss" scoped></style>

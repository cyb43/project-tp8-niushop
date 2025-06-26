<template>
    <view class="address bg-[var(--page-bg-color)] min-h-[100vh]" v-if="!loading" :style="themeColor()">
        <scroll-view scroll-y="true">
            <view class="sidebar-margin pt-[var(--top-m)]" v-if="addressList.length">
                <view class="mb-[var(--top-m)] rounded-[var(--rounded-big)] overflow-hidden"
                      v-for="(item, index) in addressList">
                    <view class="flex flex-col card-template">
                        <view class="flex-1 line-feed mr-[20rpx]" @click="selectAddress(item)">
                            <view class="flex items-center">
                                <view class="text-[#333] text-[30rpx] leading-[34rpx] font-500">{{ item.name }}</view>
                                <text class="text-[#333] text-[30rpx] ml-[10rpx]">{{ mobileHide(item.mobile) }}</text>
                            </view>
                            <view class="mt-[16rpx] text-[26rpx] line-feed text-[var(--text-color-light9)] leading-[1.4]">{{ item.full_address }}</view>
                        </view>
                        <view class="flex justify-between pt-[26rpx]">
                            <view class="flex items-center text-[26rpx] leading-none" @click.stop="setDefault(index)">
                                <text class="iconfont !text-[26rpx] mr-[10rpx]" :class="{ 'iconduigou text-primary': item.is_default, 'iconcheckbox_nol': !item.is_default }"></text>
                                设为默认
                            </view>
                            <view class="flex">
                                <view class="text-[26rpx]" @click.stop="editAddressFn(item.id)">
                                    <text class="nc-iconfont nc-icon-xiugaiV6xx shrink-0 text-[26rpx] mr-[4rpx]"></text>
                                    编辑
                                </view>
                                <view @click.stop="deleteAddressFn(index)" class="ml-[40rpx] text-[26rpx]">
                                    <text class="nc-iconfont nc-icon-shanchu-yuangaizhiV6xx shrink-0 text-[26rpx] mr-[4rpx]"></text>
                                    删除
                                </view>
                            </view>
                        </view>
                    </view>
                </view>
            </view>
            <mescroll-empty v-if="!addressList.length" :option="{tip : '暂无收货地址'}"></mescroll-empty>
            <view class="w-full footer">
                <view class="py-[var(--top-m)] px-[var(--sidebar-m)] footer w-full fixed bottom-0 left-0 right-0 box-border">
                    <button hover-class="none"
                            class="primary-btn-bg text-[#fff] h-[80rpx] leading-[80rpx] rounded-[100rpx] text-[26rpx] font-500"
                            @click="addAddress">{{ t('createAddress') }}</button>
                </view>
            </view>
        </scroll-view>
		<u-popup ref="popupRef"  :show="show" mode="center" @close="show = false" :round="30" zIndex="99999" :safeAreaInsetBottom="false">
		    <view class="bg-[#fff] flex flex-col justify-between w-[610rpx] h-[306rpx] rounded-[30rpx]  box-border px-[40rpx] pt-[70rpx] pb-[40rpx] relative overflow-hidden">
		        <view class="flex-center mb-[80rpx] text-[#333]">确定要删除该地址吗？</view>
		       <view class="flex-between-center">
		            <view class="w-[250rpx] h-[66rpx] rounded-full bg-[#eee] flex-center text-[26rpx] font-500 text-[#333]" @click="show = false">取消</view>
		            <view class="w-[250rpx] h-[66rpx] rounded-full primary-btn-bg flex-center text-[26rpx] font-500 text-[#fff]" @click="saveDelete">确定</view>
		        </view>
		    </view>
		</u-popup>
    </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { onLoad, onShow } from '@dcloudio/uni-app'
import { redirect, mobileHide } from '@/utils/common'
import { getAddressList, deleteAddress, editAddress } from '@/app/api/member'
import { t } from '@/locale'
import MescrollEmpty from '@/components/mescroll/mescroll-empty/mescroll-empty.vue';

const loading = ref(true)
const current = ref(0)
const addressList = ref<object[]>([])
const type = ref('')
const source = ref('')

onLoad((data: any) => {
    type.value = data.type || ''
    source.value = data.source || ''
    if (data.type) current.value = data.type == 'address' ? 0 : 1
    // 清空缓存，防止受待支付界面影响
    if (uni.getStorageSync('selectAddressCallback')) {
        uni.removeStorage({ key: 'selectAddressCallback' })
    }
})

const getAddressListFn = () => {
    getAddressList({}).then(({ data }) => {
        const address: any = [], locationAddress: any = []
        data.forEach((item: any) => {
            item.type == 'address' ? address.push(item) : locationAddress.push(item)
        })
        if (!source.value) { //地址管理使用
            addressList.value = data;
        } else { // 区分同城配送地址和快递地址
            addressList.value = current.value == 0 ? address : locationAddress;
        }
        loading.value = false
    }).catch(() => {
        loading.value = false
    })
}

onShow(() => {
    getAddressListFn()
})

const addAddress = () => {
    const url = `/app/pages/member/address_edit`
    redirect({ url, param: { source: source.value } })
}

const editAddressFn = (id: number) => {
    const url = `/app/pages/member/address_edit`
    redirect({ url, param: { id, source: source.value } })
}

const selectAddress = (data: any) => {
    const selectAddress = uni.getStorageSync('selectAddressCallback')
    if (selectAddress) {
        selectAddress.address_id = data.id

        uni.setStorage({
            key: 'selectAddressCallback',
            data: selectAddress,
            success() {
                redirect({ url: selectAddress.back, mode: 'redirectTo' })
            }
        })
    }
}
const show = ref(false)
const deleteIndex = ref(0)

const deleteAddressFn = (index: any) => {
    deleteIndex.value = index
	show.value = true
    // deleteAddress(data.id).then(() => {
    //     addressList.value.splice(index, 1)
    // }).catch()
}
const saveDelete = ()=>{
	 const data: any = addressList.value[deleteIndex.value ]
	deleteAddress(data.id).then(() => {
	    addressList.value.splice(deleteIndex.value, 1)
		show.value = false
	}).catch()
}
const setDefault = (index: any) => {
    const data: any = addressList.value[index]
    if (data.is_default) return

    data.is_default = 1;
    editAddress(data).then(() => {
        addressList.value.forEach((item, itemIndex) => {
            item.is_default && (item.is_default = 0)
            itemIndex == index && (item.is_default = 1)
        })
    }).catch()
}

</script>

<style lang="scss" scoped>
:deep(.u-tabs__wrapper__nav__line) {
    bottom: 0;
    background: var(--primary-color) !important;
}

.line-feed {
    word-wrap: break-word;
    word-break: break-all;
}

:deep(.u-swipe-action-item__right__button__wrapper__text.u-line-1) {
    font-size: 24rpx !important;

}

:deep(.u-swipe-action-item__right__button__wrapper) {
    padding: 0 10rpx !important;
}

.footer {
    height: calc(80rpx + var(--top-m) + var(--top-m) + constant(safe-area-inset-bottom)) !important;
    height: calc(80rpx + var(--top-m) + var(--top-m) + env(safe-area-inset-bottom)) !important;
}
</style>

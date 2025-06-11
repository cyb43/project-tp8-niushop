<template>
    <view class="bg-[var(--page-bg-color)] min-h-[100vh] overflow-hidden address-edit" :style="themeColor()">
        <u-form labelPosition="left" :model="formData" errorType='toast' :rules="rules" ref="formRef">
            <view class="sidebar-margin card-template mt-[var(--top-m)] py-[20rpx]">
                <view>
                    <u-form-item :label="t('name')" prop="name" labelWidth="200rpx">
                        <u-input fontSize="28rpx" v-model.trim="formData.name" border="none" clearable maxlength="25" placeholderStyle="color: #888" :placeholder="t('namePlaceholder')" />
                    </u-form-item>
                </view>
                <view class="mt-[16rpx]">
                    <u-form-item :label="t('mobile')" prop="mobile" labelWidth="200rpx">
                        <u-input fontSize="28rpx" v-model.trim="formData.mobile" maxlength="11" border="none" clearable :placeholder="t('mobilePlaceholder')" placeholderStyle="color: #888" />
                    </u-form-item>
                </view>
                <view class="mt-[16rpx]">
                    <u-form-item :label="t('selectArea')" prop="area" labelWidth="200rpx">
                        <view class="flex w-full items-center h-[52rpx]" v-if="addressType == 'address' && isSelectMap != 1" @click="selectArea">
                            <view v-if="!formData.area" class="text-[#888] text-[28rpx] flex-1">{{ t('selectAreaPlaceholder') }}</view>
                            <view v-else class="text-[28rpx] flex-1 leading-[1.4]">{{ formData.area }}</view>
                            <view @click.stop="chooseLocation" class="flex items-center">
                                <text class="nc-iconfont nc-icon-dizhiguanliV6xx mr-[4rpx] text-[32rpx] text-[var(--primary-color)]"></text>
                                <text class="text-[24rpx] whitespace-nowrap text-[var(--primary-color)]">定位</text>
                            </view>
                        </view>
                        <view v-else class="flex justify-between items-center flex-1 h-[52rpx]" @click="chooseLocation">
                            <view class="text-[28rpx] text-[#303133] leading-[1.4]" v-if="formData.area || formData.address_name">{{ formData.area || formData.address_name }}</view>
                            <view class="text-[#888] text-[28rpx]" v-else>{{ t('selectAddressPlaceholder') }}</view>
                            <view class="flex items-center">
                                <text class="nc-iconfont nc-icon-dizhiguanliV6xx text-[32rpx] mr-[4rpx] text-[var(--primary-color)]"></text>
                                <text class="text-[24rpx] whitespace-nowrap text-[var(--primary-color)]">定位</text>
                            </view>
                        </view>
                    </u-form-item>
                </view>
                <view class="mt-[16rpx]">
                    <u-form-item :label="t('address')" prop="address" labelWidth="200rpx">
                        <u-input fontSize="28rpx" v-model="formData.address" border="none" clearable maxlength="120" :placeholder="t('addressPlaceholder')" placeholderStyle="color: #888" />
                    </u-form-item>
                </view>
            </view>
            <view class="sidebar-margin card-template mt-[var(--top-m)] py-[10rpx]">
                <u-form-item :label="t('defaultAddress')" prop="name" :border-bottom="false" labelWidth="200rpx">
                    <u-switch v-model="formData.is_default" size="20" :activeValue="1" :inactiveValue="0" activeColor="var(--primary-color)" inactiveColor="var(--temp-bg)" />
                </u-form-item>
            </view>
        </u-form>
        <view class="w-full footer">
            <view
                class="py-[var(--top-m)] px-[var(--sidebar-m)] footer w-full fixed bottom-0 left-0 right-0 box-border">
                <button hover-class="none"
                        class="primary-btn-bg !text-[#fff] h-[80rpx] leading-[80rpx] rounded-[100rpx] text-[26rpx] font-500"
                        @click="save" :disabled="btnDisabled" :loading="operateLoading"
                        :class="{'opacity-50': btnDisabled}">{{ t('save') }}
                </button>
            </view>
        </view>
        <area-select ref="areaRef" @complete="areaSelectComplete" :area-id="formData.district_id || formData.city_id" />
        <!-- #ifdef MP-WEIXIN -->
        <!-- 小程序隐私协议 -->
        <wx-privacy-popup ref="wxPrivacyPopupRef"></wx-privacy-popup>
        <!-- #endif -->
    </view>

</template>

<script setup lang="ts">
import { ref, computed, nextTick } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import { redirect } from '@/utils/common'
import { t } from '@/locale'
import { addAddress, editAddress, getAddressInfo } from '@/app/api/member'
import manifestJson from '@/manifest.json'
import { getAddressByLatlng } from '@/app/api/system'

const formData: any = ref({
    id: 0,
    name: '',
    mobile: '',
    province_id: 0,
    city_id: 0,
    district_id: 0,
    lat: '',
    lng: '',
    address: '',
    address_name: '',
    full_address: '',
    is_default: 0,
    area: ''
})
const areaRef = ref()
const formRef: any = ref(null)
const source = ref('')
const btnDisabled = ref(false)
const isSelectAddress = ref(false)
const addressType = ref('address');
const isSelectMap = ref(2) // 值为1，该地址需要有经纬度，反之不需要

const wxPrivacyPopupRef: any = ref(null)

onLoad((data: any) => {
    isSelectMap.value = data.isSelectMap || '';
    const selectAddress = uni.getStorageSync('selectAddressCallback')
    if (data.id) {
        getAddressInfo(data.id).then((res: any) => {
            res.data && Object.assign(formData.value, res.data)
            // 兼容待支付页面编辑地址
            if (selectAddress) {
                addressType.value = selectAddress.delivery == 'express' ? 'address' : 'locationAddress';
            }
        })

    } else if (data.name) {
        if (uni.getStorageSync('addressInfo')) {
            Object.assign(formData.value, uni.getStorageSync('addressInfo'))
        }
        formData.value.address = data.name;
        getAddress(data.latng);
        var tempArr = getQueryVariable('latng').split(',');
        formData.value.lat = tempArr[0];
        formData.value.lng = tempArr[1];
    }
    source.value = data.source || ''
    if (selectAddress) {
        addressType.value = selectAddress.delivery == 'express' ? 'address' : 'locationAddress';
    }
    // #ifdef MP
    nextTick(() => {
        if (wxPrivacyPopupRef.value) wxPrivacyPopupRef.value.proactive();
    })
    // #endif
})

const rules = computed(() => {
    return {
        'name': {
            type: 'string',
            required: true,
            message: t('namePlaceholder'),
            trigger: ['blur', 'change'],
        },
        'mobile': [
            {
                type: 'string',
                required: true,
                message: t('mobilePlaceholder'),
                trigger: ['blur', 'change'],
            },
            {
                validator(rule: any, value: any, callback: any) {
                    let mobile = /^1[3-9]\d{9}$/;
                    if (!mobile.test(value)) {
                        callback(new Error(t('mobileError')))
                    } else {
                        callback()
                    }
                }
            }
        ],
        'area': {
            validator() {
                let bool = true;
                if (uni.$u.test.isEmpty(formData.value.area) && uni.$u.test.isEmpty(formData.value.address_name)) {
                    bool = false;
                }
                return bool
            },
            message: t('selectAreaPlaceholder')
        },
        'address': {
            type: 'string',
            required: true,
            message: t('addressPlaceholder'),
            trigger: ['blur', 'change']
        }
    }
})

const selectArea = () => {
    isSelectAddress.value = true
    areaRef.value.open()
}

const areaSelectComplete = (event: any) => {
    if (isSelectAddress.value && (formData.value.province_id == event.province?.id || formData.value.city_id != event.city?.id || formData.value.district_id != event.district?.id)) {
        formData.value.lat = '';
        formData.value.lng = '';
    }
    formData.value.province_id = event.province?.id || 0
    formData.value.city_id = event.city?.id || 0
    formData.value.district_id = event.district?.id || 0
    formData.value.area = `${ event.province?.name || '' }${ event.city?.name || '' }${ event.district?.name || '' }`
    isSelectAddress.value = false;
}

const operateLoading = ref(false)
const save = () => {
    const save = formData.value.id ? editAddress : addAddress
    formRef.value.validate().then(() => {
        if (operateLoading.value) return
        operateLoading.value = true

        btnDisabled.value = true

        formData.value.full_address = formData.value.area + formData.value.address

        if (isSelectMap.value == 1 && !formData.value.lat && !formData.value.lng) {
            uni.showToast({
                title: '缺少经纬度，请在地图上重新选点',
                icon: 'none'
            });
            operateLoading.value = false;
            btnDisabled.value = false
            return false;
        }

        save(formData.value).then((res: any) => {
            operateLoading.value = false
            setTimeout(() => {
                btnDisabled.value = false
                if (source.value == 'shop_order_payment') {
                    const selectAddress = uni.getStorageSync('selectAddressCallback')
                    if (selectAddress) {
                        selectAddress.address_id = res.data.id || formData.value.id
                        uni.setStorage({
                            key: 'selectAddressCallback',
                            data: selectAddress,
                            success() {
                                redirect({ url: selectAddress.back, mode: 'redirectTo' })
                            }
                        })
                    }
                } else {
                    redirect({
                        url: '/app/pages/member/address',
                        mode: 'redirectTo',
                        param: { source: source.value }
                    })
                }
            }, 1000)
        }).catch(() => {
            operateLoading.value = false
            btnDisabled.value = false
        })
    })
}

// 选择地址
const chooseLocation = () => {
    // #ifdef MP
    uni.chooseLocation({
        success: (res) => {
            res.latitude && (formData.value.lat = res.latitude)
            res.longitude && (formData.value.lng = res.longitude)
            res.address && (formData.value.area = res.address)
            res.name && (formData.value.address_name = res.address)
            res.name && (formData.value.address = res.name)
            if (res.latitude && res.longitude) {
                let latng = res.latitude + ',' + res.longitude;
                getAddress(latng);
            }
        },
        fail: (res) => {
            // 在隐私协议中没有声明chooseLocation:fail api作用域
            if (res.errMsg && res.errno) {
                if (res.errno == 104) {
                    let msg = '用户未授权隐私权限，选择位置失败';
                    uni.showToast({ title: msg, icon: 'none' })
                } else if (res.errno == 112) {
                    let msg = '隐私协议中未声明，打开地图选择位置失败';
                    uni.showToast({ title: msg, icon: 'none' })
                } else {
                    uni.showToast({ title: res.errMsg, icon: 'none' })
                }
            }
        }
    });
    // #endif

    // #ifdef H5
    var urlencode = formData.value;
    uni.setStorageSync('addressInfo', urlencode);
    let backurl = location.origin + location.pathname + '?source=' + source.value;
    if (isSelectMap.value) {
        backurl = backurl + '&isSelectMap=' + isSelectMap.value
    }
    window.location.href = 'https://apis.map.qq.com/tools/locpicker?search=1&type=0&backurl=' + encodeURIComponent(backurl) + '&key=' + manifestJson.h5.sdkConfigs.maps.qqmap.key + '&referer=myapp';
    // #endif
}

//获取详细地址
const getAddress = (latlng: any) => {
    getAddressByLatlng({ latlng }).then((res: any) => {
        if (res.data) {
            formData.value.full_address = '';
            formData.value.full_address += res.data.province != undefined ? res.data.province : '';
            formData.value.full_address += res.data.city != undefined ? '' + res.data.city : '';
            formData.value.full_address += res.data.district != undefined ? '' + res.data.district : '';

            formData.value.address_name = formData.value.full_address.replace(/-/g, '');
            formData.value.area = (res.data.province + res.data.city + res.data.district) || res.data.full_address;

            formData.value.province_id = res.data.province_id != undefined ? res.data.province_id : 0;
            formData.value.city_id = res.data.city_id != undefined ? res.data.city_id : 0;
            formData.value.district_id = res.data.district_id != undefined ? res.data.district_id : 0;
        } else {
            uni.showToast({ title: res.msg, icon: 'none' })
        }
    })

}

const getQueryVariable = (variable: any) => {
    var query = window.location.search.substring(1);
    var vars = query.split('&');
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split('=');
        if (pair[0] == variable) {
            return pair[1];
        }
    }
    return false;
}
</script>

<style lang="scss" scoped>
.address-edit :deep(.u-form-item__body__left__content__label) {
    font-size: 28rpx !important;
}

.address-edit :deep(.u-form-item__body__right) {
    display: flex;
}

.footer {
    height: calc(100rpx + var(--top-m) + var(--top-m) + constant(safe-area-inset-bottom)) !important;
    height: calc(100rpx + var(--top-m) + var(--top-m) + env(safe-area-inset-bottom)) !important;
}
</style>

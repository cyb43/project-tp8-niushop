<template>
    <view class="w-screen h-screen flex flex-col" :style="themeColor()">
        <!-- #ifdef MP-WEIXIN -->
        <view :style="{'height':headerHeight}">
            <top-tabbar :data="param" :scrollBool="topTabarObj.getScrollBool()" class="top-header" />
        </view>
        <!-- #endif -->
        <view class="mx-[60rpx]">
            <view class="pt-[140rpx] text-[50rpx] text-[#333]">{{ t('bindMobile') }}</view>
            <view class="text-[26rpx] leading-[39rpx] text-[var(--text-color-light6)] mt-[24rpx] mb-[90rpx]">{{ t('bindMobileTip') }}</view>
            <u-form labelPosition="left" :model="formData" errorType='toast' :rules="rules" ref="formRef">
                <view
                    class="h-[90rpx] flex w-full items-center px-[30rpx] rounded-[40rpx] box-border bg-[#F6F6F6]">
                    <u-form-item label="" prop="mobile" :border-bottom="false">
                        <u-input v-model="formData.mobile" type="number" maxlength="11" border="none" :placeholder="t('mobilePlaceholder')" class="!bg-transparent" :disabled="real_name_input" fontSize="26rpx" placeholderClass="!text-[var(--text-color-light9)] text-[26rpx]" />
                    </u-form-item>
                </view>
                <view
                    class="h-[90rpx] flex w-full items-center px-[30rpx] rounded-[40rpx] box-border bg-[#F6F6F6] mt-[40rpx]">
                    <u-form-item label="" prop="mobile_code" :border-bottom="false">
                        <u-input v-model="formData.mobile_code" type="number" maxlength="4" border="none" :placeholder="t('codePlaceholder')" class="!bg-transparent" :disabled="real_name_input" fontSize="26rpx" placeholderClass="!text-[var(--text-color-light9)]">
                            <template #suffix>
                                <sms-code :mobile="formData.mobile" type="bind_mobile" v-model="formData.mobile_key"></sms-code>
                            </template>
                        </u-input>
                    </u-form-item>
                </view>
            </u-form>
            <view class="mt-[100rpx]">
                <view v-if="config.agreement_show" class="flex items-center mb-[20rpx] py-[10rpx]" @click.stop="agreeChange">
                    <u-checkbox-group @change="agreeChange"> 
                        <u-checkbox activeColor="var(--primary-color)" :checked="isAgree" shape="circle" size="24rpx" :customStyle="{ 'marginTop': '4rpx' }" />
                    </u-checkbox-group>
                    <view class="text-[24rpx] text-[var(--text-color-light6)] flex items-center flex-wrap">
                        <text>{{ t('agreeTips') }}</text>
                        <text @click.stop="redirect({ url: '/app/pages/auth/agreement?key=privacy' })" class="text-primary">《{{ t('privacyAgreement') }}》</text>
                        <text>{{ t('and') }}</text>
                        <text @click.stop="redirect({ url: '/app/pages/auth/agreement?key=service' })" class="text-primary">《{{ t('userAgreement') }}》</text>
                    </view>
                </view>
                <button class="w-full h-[80rpx] !bg-[var(--primary-color)] text-[26rpx] rounded-[40rpx] leading-[80rpx] font-500 !text-[#fff]" @click="handleBind">{{ t('bind') }}</button>
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
import { ref, reactive, computed, nextTick } from 'vue'
import { t } from '@/locale'
import { bind } from '@/app/api/auth'
import { bindMobile } from '@/app/api/member'
import useMemberStore from '@/stores/member'
import useConfigStore from '@/stores/config'
import { useLogin } from '@/hooks/useLogin'
import { redirect, pxToRpx } from '@/utils/common'
import { onLoad } from '@dcloudio/uni-app'
import { topTabar } from '@/utils/topTabbar'
import useSystemStore from "@/stores/system";

const systemStore = useSystemStore()
/********* 自定义头部 - start ***********/
const topTabarObj = topTabar()
let param = topTabarObj.setTopTabbarParam({ title: '', topStatusBar: { bgColor: '#fff', textColor: '#333' } })
/********* 自定义头部 - end ***********/
const headerHeight = computed(() => {
    return Object.keys(systemStore.menuButtonInfo).length ? pxToRpx(Number(systemStore.menuButtonInfo.height)) + pxToRpx(systemStore.menuButtonInfo.top) + pxToRpx(8) + 'rpx' : 'auto'
})

const memberStore = useMemberStore()
const info = computed(() => memberStore.info)

const config = computed(() => {
    return useConfigStore().login
})

const loading = ref(false)
const isAgree = ref(false)

const formData: any = reactive({
    mobile: '',
    mobile_code: '',
    mobile_key: '',
    register_type: ''
})

const real_name_input = ref(true);

onLoad((data) => {
    // 防止浏览器自动填充
    setTimeout(() => {
        real_name_input.value = false;
    }, 800)

    data.register_type && (Object.assign(formData, { register_type: data.register_type }))
    uni.getStorageSync('openid') && (Object.assign(formData, { openid: uni.getStorageSync('openid') }))
    uni.getStorageSync('pid') && (Object.assign(formData, { pid: uni.getStorageSync('pid') }))
    uni.getStorageSync('unionid') && (Object.assign(formData, { unionid: uni.getStorageSync('unionid') }))
    uni.getStorageSync('nickname') && (Object.assign(formData, { nickname: uni.getStorageSync('nickname') }))
    uni.getStorageSync('avatar') && (Object.assign(formData, { avatar: uni.getStorageSync('avatar') }))
});

const rules = {
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
                    callback(new Error('请输入正确的手机号'))
                } else {
                    callback()
                }
            },
            message: t('mobileError'),
            trigger: ['change', 'blur'],
        }
    ],
    'mobile_code': {
        type: 'string',
        required: true,
        message: t('codePlaceholder'),
        trigger: ['blur', 'change']
    }
}

const agreeChange = () => {
    isAgree.value = !isAgree.value
}

const agreeTips = () => {
    uni.showToast({ title: t('isAgreeTips'), icon: 'none' })
}

const formRef: any = ref(null)

const handleBind = () => {
    formRef.value.validate().then(() => {
        if (loading.value) return
        loading.value = true

        const request = info.value ? bindMobile : bind

        request(formData).then((res: any) => {
            if (info.value) {
                memberStore.getMemberInfo()
                redirect({ url: '/app/pages/member/personal', mode: 'redirectTo' })
            } else {
                memberStore.setToken(res.data.token)
                useLogin().handleLoginBack()
            }
        }).catch(() => {
            loading.value = false
        })
    })
}
</script>

<style lang="scss" scoped>
:deep(.u-input) {
    background-color: transparent !important;
}

:deep(.u-form-item) {
    flex: 1;

    .u-line {
        display: none;
    }
}
</style>

<template>
    <u-popup :show="show" @close="show = false" mode="center" :round="10" :closeable="true" :safeAreaInsetBottom="false" zIndex="10086">
        <view @touchmove.prevent.stop class="max-w-[600rpx] w-[600rpx] box-border">
            <view class="text-center py-[var(--pad-top-m)] text-[32rpx] font-500 leading-[46rpx]">{{ t('bindMobile') }}</view>
            <view class="px-[var(--pad-sidebar-m)] pb-[var(--pad-top-m)]">
                <u-form labelPosition="left" :model="formData" errorType='toast' :rules="rules" ref="formRef">
                    <u-form-item label="" prop="mobile" :borderBottom="true">
                        <input v-model="formData.mobile" type="number" maxlength="11"
                               :placeholder="t('mobilePlaceholder')"
                               class="w-full h-[50rpx] leading-[50rpx] !bg-transparent !px-[20rpx] text-[26rpx] text-[#333]"
                               :disabled="real_name_input" placeholder-class="bind-mobile" />
                    </u-form-item>
                    <view class="mt-[20rpx]">
                        <u-form-item label="" prop="mobile_code" :borderBottom="true">
                            <input v-model="formData.mobile_code" type="number" maxlength="6"
                                   :placeholder="t('codePlaceholder')"
                                   class="box-border w-full h-[50rpx] leading-[50rpx] !bg-transparent !px-[20rpx] text-[26rpx] text-[#333]"
                                   :disabled="real_name_input" placeholder-class="bind-mobile" />
                            <template #right>
                                <sms-code v-if="config.agreement_show" :mobile="formData.mobile" type="login" v-model="formData.mobile_key" :isAgree="isAgree"></sms-code>
                                <sms-code v-else :mobile="formData.mobile" type="login" v-model="formData.mobile_key"></sms-code>
                            </template>
                        </u-form-item>
                    </view>
                    <view v-if="config.agreement_show" class="flex items-center mt-[30rpx] pl-[10rpx] py-[10rpx]" @click.stop="agreeChange">
                        <u-checkbox-group @change="agreeChange">
                            <u-checkbox activeColor="var(--primary-color)" :checked="isAgree" shape="circle" size="28rpx" />
                        </u-checkbox-group>
                        <view class="text-[24rpx] text-[var(--text-color-light6)] flex items-center flex-wrap">
                            <text>{{ t('agreeTips') }}</text>
                            <text @click.stop="redirect({ url: '/app/pages/auth/agreement?key=privacy' })" class="text-primary">《{{ t('privacyAgreement') }}》</text>
                            <text>{{ t('and') }}</text>
                            <text @click.stop="redirect({ url: '/app/pages/auth/agreement?key=service' })" class="text-primary">《{{ t('userAgreement') }}》</text>
                        </view>
                    </view>
                    <view class="mt-[100rpx]">
                        <button class="primary-btn-bg text-[26rpx] !text-[#fff] !h-[80rpx] leading-[80rpx] rounded-full font-500" :class="{'opacity-50': loading}" :disabled="loading" @click="handleBind">{{ t('bind') }}</button>
                    </view>
                </u-form>
            </view>
        </view>
    </u-popup>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { t } from '@/locale'
import { bindMobile } from '@/app/api/member'
import { mobileLogin } from '@/app/api/auth'
import useMemberStore from '@/stores/member'
import useConfigStore from '@/stores/config'
import { redirect } from '@/utils/common'
import { useLogin } from '@/hooks/useLogin'

const show = ref(false)
const memberStore = useMemberStore()
const info = computed(() => memberStore.info)

const config = computed(() => {
    return useConfigStore().login
})

const loading = ref(false)
const isAgree = ref(false)

const formData = reactive({
    mobile: '',
    mobile_code: '',
    mobile_key: ''
})

const real_name_input = ref(true);
onMounted(() => {
    // 防止浏览器自动填充
    setTimeout(() => {
        real_name_input.value = false;
    }, 800)

    uni.getStorageSync('pid') && (Object.assign(formData, { pid: uni.getStorageSync('pid') }))
    uni.getStorageSync('openid') && (Object.assign(formData, { openid: uni.getStorageSync('openid') }))
    uni.getStorageSync('unionid') && (Object.assign(formData, { unionid: uni.getStorageSync('unionid') }))
    uni.getStorageSync('nickname') && (Object.assign(formData, { nickname: uni.getStorageSync('nickname') }))
    uni.getStorageSync('avatar') && (Object.assign(formData, { headimg: uni.getStorageSync('avatar') }))
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
                if (!uni.$u.test.mobile(value)) {
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

const formRef: any = ref(null)

const handleBind = () => {
    formRef.value.validate().then(() => {
        if (!isAgree.value && config.value.agreement_show) {
            uni.showToast({ title: t('isAgreeTips'), icon: 'none' })
            return
        }

        if (loading.value) return
        loading.value = true

        const request = info.value ? bindMobile : mobileLogin;
        if(uni.getStorageSync('avatar') && uni.getStorageSync('nickname') && uni.getStorageSync('openid') && uni.getStorageSync('unionid')){
            formData.avatar = uni.getStorageSync('avatar')
            formData.nickname = uni.getStorageSync('nickname')
            formData.openid = uni.getStorageSync('openid')
            formData.unionid = uni.getStorageSync('unionid')
        }
        request(formData).then((res: any) => {
            if (info.value) {
                memberStore.getMemberInfo()
                if (info.value.mobile) {
                    uni.removeStorageSync('isBindMobile');
                }
            } else {
                memberStore.setToken(res.data.token)
                useLogin().handleLoginBack()
            }
            show.value = false
        }).catch(() => {
            loading.value = false
        })
    })
}

const open = () => {
    show.value = true
}

defineExpose({
    open
})
</script>

<style lang="scss" scoped>
:deep(.bind-mobile) {
    color: var(--text-color-light9);
    font-size: 26rpx;
}

:deep(.u-checkbox) {
    margin: 0 !important;
}
</style>

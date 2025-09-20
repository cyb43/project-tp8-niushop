<template>
    <view class="w-screen h-screen flex flex-col" :style="themeColor()">
        <!-- #ifdef MP-WEIXIN -->
        <view :style="{'height':headerHeight}">
            <top-tabbar :data="param" :scrollBool="topTabarObj.getScrollBool()" class="top-header" />
        </view>
        <!-- #endif -->
        <view class="mx-[60rpx]">
            <view class="pt-[140rpx] text-[44rpx] font-500 text-[#333]">{{ t('findPassword') }}</view>
            <view class="text-[26rpx] leading-[39rpx] text-[var(--text-color-light6)] mt-[24rpx] mb-[90rpx]">{{ t('findPasswordTip') }}</view>
            <u-form labelPosition="left" :model="formData" errorType='toast' :rules="rules" ref="formRef">
                <view class="h-[88rpx] flex w-full items-center px-[30rpx] rounded-[40rpx] box-border bg-[#F6F6F6]">
                    <u-form-item label="" prop="mobile" :border-bottom="false">
                        <u-input v-model="formData.mobile" type="number" maxlength="11" border="none"
                                 :placeholder="t('mobilePlaceholder')" class="!bg-transparent"
                                 :disabled="real_name_input" fontSize="26rpx"
                                 placeholderClass="!text-[var(--text-color-light9)] text-[26rpx]" />
                    </u-form-item>
                </view>
                <view class="h-[88rpx] flex w-full items-center px-[30rpx] rounded-[40rpx] box-border bg-[#F6F6F6] mt-[40rpx]">
                    <u-form-item label="" prop="code" :border-bottom="false">
                        <u-input v-model="formData.mobile_code" type="number" maxlength="4" border="none"
                                 :placeholder="t('codePlaceholder')" class="!bg-transparent" :disabled="real_name_input"
                                 fontSize="26rpx" placeholderClass="!text-[var(--text-color-light9)] text-[26rpx]">
                            <template #suffix>
                                <sms-code :mobile="formData.mobile" type="find_pass" v-model="formData.mobile_key"></sms-code>
                            </template>
                        </u-input>
                    </u-form-item>
                </view>
                <view class="h-[88rpx] flex w-full items-center px-[30rpx] rounded-[40rpx] box-border bg-[#F6F6F6] mt-[40rpx]">
                    <u-form-item label="" prop="password" :border-bottom="false">
                        <u-input v-model="formData.password" border="none" :password="isPassword" maxlength="40"
                                 :placeholder="t('passwordPlaceholder')" class="!bg-transparent"
                                 :disabled="real_name_input" fontSize="26rpx"
                                 placeholderClass="!text-[var(--text-color-light9)] text-[26rpx]">
                            <template #suffix>
                                <view @click="changePassword" v-if="formData.password">
                                    <u-icon :name="isPassword?'eye-off':'eye-fill'" color="#b9b9b9" size="20"></u-icon>
                                </view>
                            </template>
                        </u-input>
                    </u-form-item>
                </view>
                <view class="h-[88rpx] flex w-full items-center px-[30rpx] rounded-[40rpx] box-border bg-[#F6F6F6] mt-[40rpx]">
                    <u-form-item label="" prop="confirm_password" :border-bottom="false">
                        <u-input v-model="formData.confirm_password" border="none" :password="isConfirmPassword"
                                 maxlength="40" :placeholder="t('confirmPasswordPlaceholder')" class="!bg-transparent"
                                 :disabled="real_name_input" fontSize="26rpx"
                                 placeholderClass="!text-[var(--text-color-light9)] text-[26rpx]">
                            <template #suffix>
                                <view @click="changeConfirmPassword" v-if="formData.confirm_password">
                                    <u-icon :name="isConfirmPassword?'eye-off':'eye-fill'" color="#b9b9b9" size="20"></u-icon>
                                </view>
                            </template>
                        </u-input>
                    </u-form-item>
                </view>
            </u-form>
            <view class="mt-[160rpx]">
                <button class="w-full h-[80rpx] !bg-[var(--primary-color)] text-[26rpx] rounded-[40rpx] leading-[80rpx] font-500 !text-[#fff]" @click="handleConfirm">{{ t('confirm') }}</button>
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { t } from '@/locale'
import { resetPassword } from '@/app/api/system'
import { redirect, pxToRpx } from '@/utils/common'
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
const formData = reactive({
    mobile: '',
    mobile_code: '',
    mobile_key: '',
    password: '',
    confirm_password: ''
})

const real_name_input = ref(true);
onMounted(() => {
    // 防止浏览器自动填充
    setTimeout(() => {
        real_name_input.value = false;
    }, 800)
});

const loading = ref(false)
const formRef: any = ref(null)
const isPassword = ref(true)
const isConfirmPassword = ref(true)
const changePassword = () => {
    isPassword.value = !isPassword.value
}

const changeConfirmPassword = () => {
    isConfirmPassword.value = !isConfirmPassword.value
}

const rules = {
    'password': {
        type: 'string',
        required: true,
        message: t('passwordPlaceholder'),
        trigger: ['blur', 'change']
    },
    'confirm_password': [
        {
            type: 'string',
            required: true,
            message: t('confirmPasswordPlaceholder'),
            trigger: ['blur', 'change']
        },
        {
            validator(rule: any, value: any) {
                return value == formData.password
            },
            message: t('confirmPasswordError'),
            trigger: ['change', 'blur'],
        }
    ],
    'mobile': [
        {
            type: 'string',
            required: true,
            message: t('mobilePlaceholder'),
            trigger: ['blur', 'change'],
        },
        {
            validator(rule: any, value: any) {
                return uni.$u.test.mobile(value)
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

const handleConfirm = () => {
    formRef.value.validate().then(() => {
        if (loading.value) return
        loading.value = true

        resetPassword(formData).then((res: any) => {
            redirect({ url: '/app/pages/auth/login', mode: 'redirectTo' })
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

:deep(.u-checkbox) {
    margin: 0 !important;
}

:deep(.u-form-item) {
    flex: 1;

    .u-line {
        display: none;
    }
}
</style>

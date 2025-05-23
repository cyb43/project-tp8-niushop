<template>
    <view class="w-screen h-screen flex flex-col" :style="themeColor()" v-if="type">
        <!-- #ifdef MP-WEIXIN -->
        <view :style="{'height':headerHeight}">
            <top-tabbar :data="param" :scrollBool="topTabarObj.getScrollBool()" class="top-header" />
        </view>
        <!-- #endif -->
        <view class="mx-[60rpx]">
            <view class="pt-[140rpx] text-[44rpx] font-500 text-[#333]">{{ type == 'username' ? t('accountLogin') : t('mobileLogin') }}</view>
            <view class="text-[26rpx] leading-[39rpx] text-[var(--text-color-light6)] mt-[16rpx] mb-[80rpx]">{{ type == 'username' ? t('accountLoginTip') : t('mobileLoginTip') }}</view>
            <u-form labelPosition="left" :model="formData" errorType='toast' :rules="rules" ref="formRef">
                <template v-if="type == 'username'">
                    <view class="h-[88rpx] flex w-full items-center px-[30rpx] rounded-[var(--goods-rounded-mid)] box-border bg-[#F6F6F6]">
                        <u-form-item label="" prop="username" :border-bottom="false">
                            <u-input v-model="formData.username" border="none" maxlength="40"
                                     :placeholder="t('usernamePlaceholder')" autocomplete="off" class="!bg-transparent"
                                     :disabled="real_name_input" fontSize="26rpx"
                                     placeholderClass="!text-[var(--text-color-light9)] text-[26rpx]" />
                        </u-form-item>
                    </view>
                    <view class="h-[88rpx] flex w-full items-center px-[30rpx] rounded-[var(--goods-rounded-mid)] box-border bg-[#F6F6F6] mt-[40rpx]">
                        <u-form-item label="" prop="password" :border-bottom="false">
                            <u-input v-model="formData.password" border="none" :password="isPassword" maxlength="40"
                                     :placeholder="t('passwordPlaceholder')" autocomplete="new-password"
                                     class="!bg-transparent" :disabled="real_name_input" fontSize="26rpx"
                                     placeholderClass="!text-[var(--text-color-light9)] text-[26rpx]">
                                <template #suffix>
                                    <view @click="changePassword" v-if="formData.password">
                                        <u-icon :name="isPassword?'eye-off':'eye-fill'" color="#b9b9b9" size="20"></u-icon>
                                    </view>
                                </template>
                            </u-input>
                        </u-form-item>
                    </view>
                </template>
                <template v-if="type == 'mobile'">
                    <view class="h-[88rpx] flex w-full items-center px-[30rpx] rounded-[var(--goods-rounded-mid)] box-border bg-[#F6F6F6]">
                        <u-form-item label="" prop="mobile" :border-bottom="false">
                            <u-input v-model="formData.mobile" type="number" maxlength="11" border="none"
                                     :placeholder="t('mobilePlaceholder')" autocomplete="off" class="!bg-transparent"
                                     :disabled="real_name_input" fontSize="26rpx"
                                     placeholderClass="!text-[var(--text-color-light9)] text-[26rpx]" />
                        </u-form-item>
                    </view>
                    <view class="h-[88rpx] flex w-full items-center px-[30rpx] rounded-[var(--goods-rounded-mid)] box-border bg-[#F6F6F6] mt-[40rpx] text-[26rpx]">
                        <u-form-item label="" prop="mobile_code" :border-bottom="false">
                            <u-input v-model="formData.mobile_code" type="number" maxlength="4" border="none"
                                     class="!bg-transparent" fontSize="26rpx" :disabled="real_name_input"
                                     :placeholder="t('codePlaceholder')"
                                     placeholderClass="!text-[var(--text-color-light9)] text-[26rpx]">
                                <template #suffix>
                                    <sms-code v-if="configStore.login.agreement_show" :mobile="formData.mobile" type="login" v-model="formData.mobile_key" :isAgree="isAgree"></sms-code>
                                    <sms-code v-else :mobile="formData.mobile" type="login" v-model="formData.mobile_key"></sms-code>
                                </template>
                            </u-input>
                        </u-form-item>
                    </view>
                </template>
            </u-form>
            <view v-if="type == 'username'" class="text-right text-[24rpx] text-[var(--text-color-light9)] leading-[34rpx] mt-[20rpx]" @click="redirect({ url: '/app/pages/auth/resetpwd' })">{{ t('resetpwd') }}</view>
            <view :class="{'mt-[160rpx]':type != 'username','mt-[106rpx]':type == 'username'}">
                <view v-if="configStore.login.agreement_show" class="flex items-center mb-[20rpx] py-[14rpx]" @click.stop="agreeChange">
                    <u-checkbox-group @change="agreeChange">
                        <u-checkbox activeColor="var(--primary-color)" :checked="isAgree" shape="circle" size="30rpx" />
                    </u-checkbox-group>
                    <view class="text-[24rpx] text-[var(--text-color-light6)] flex items-center flex-wrap leading-[30rpx]">
                        <text>{{ t('agreeTips') }}</text>
                        <text @click.stop="redirect({ url: '/app/pages/auth/agreement?key=privacy' })" class="text-primary">《{{ t('privacyAgreement') }}》</text>
                        <text>{{ t('and') }}</text>
                        <text @click.stop="redirect({ url: '/app/pages/auth/agreement?key=service' })" class="text-primary">《{{ t('userAgreement') }}》</text>
                    </view>
                </view>
                <button class="w-full h-[80rpx] !bg-[var(--primary-color)] text-[26rpx] rounded-[40rpx] leading-[80rpx] font-500 !text-[#fff] !mx-[0]" :loadingText="t('logining')" @click="handleLogin">{{ t('login') }}</button>
                <view class="flex items-center justify-between mt-[30rpx]">
                    <view class="text-[26rpx] text-[var(--text-color-light6)] leading-[34rpx]" @click="setType" v-if="(type == 'username' && configStore.login.is_mobile) || (type == 'mobile' && configStore.login.is_username )">{{ type == 'username' ? t('mobileLogin') : t('accountLogin') }}</view>
                    <view class="text-[26rpx] text-[#333] leading-[34rpx]" @click="redirect({ url: '/app/pages/auth/register',param:{type} })">
                        <text>{{ t('noAccount') }},</text>
                        <text class="text-primary">{{ t('toRegister') }}</text>
                    </view>
                </view>
            </view>
        </view>
        <uni-popup ref="popupRef" type="dialog">
            <view class="bg-[#fff] flex flex-col justify-between w-[600rpx] min-h-[280rpx] rounded-[var(--rounded-big)] box-border px-[35rpx] pt-[35rpx] pb-[8rpx] relative">
                <view class="flex justify-center">
                    <text class="text-[33rpx] font-700"> 用户协议及隐私保护</text>
                </view>
                <view class="flex items-center mb-[20rpx] mt-[20rpx] py-[20rpx]" @click.stop="agreeChange">
                    <view class="text-[26rpx] text-[var(--text-color-light6)] flex items-center flex-wrap">
                        <text>{{ t('agreeTips') }}</text>
                        <text @click.stop="redirect({ url: '/app/pages/auth/agreement?key=privacy' })" class="text-primary">《{{ t('privacyAgreement') }}》</text>
                        <text>{{ t('and') }}</text>
                        <text @click.stop="redirect({ url: '/app/pages/auth/agreement?key=service' })" class="text-primary">《{{ t('userAgreement') }}》</text>
                    </view>
                </view>
                <view>
                    <view class="w-[100%] flex justify-center bg-[var(--primary-color)] h-[70rpx] leading-[70rpx] text-[#fff] text-[26rpx] border-[0] font-500 rounded-[50rpx]" @click="dialogConfirm">同意并登录</view>
                    <view class="w-[100%] flex justify-center h-[70rpx] leading-[70rpx] text-[#999] text-[24rpx] border-[0] font-500 rounded-[50rpx]" @click="dialogClose">不同意</view>
                </view>
            </view>
        </uni-popup>
        <view class="footer w-full" v-if="isShowQuickLogin">
            <view class="text-[26rpx] leading-[36rpx] text-[#333] text-center mb-[30rpx] font-400">{{ t('oneClicklogin') }}</view>
            <view class="flex justify-center">
                <button class="h-[80rpx] w-[80rpx] text-[46rpx] !text-[#1AAB37] text-center !p-0 !bg-transparent leading-[79rpx] border-[2rpx] rounded-[50%] border-solid border-[#ddd] nc-iconfont nc-icon-weixinV6mm overflow-hidden" @click="toLink"></button>
            </view>
        </view>
    </view>
</template>
<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { usernameLogin, mobileLogin } from '@/app/api/auth'
import useMemberStore from '@/stores/member'
import useConfigStore from '@/stores/config'
import { useLogin } from '@/hooks/useLogin'
import { t } from '@/locale'
import { redirect, getToken, pxToRpx, isWeixinBrowser } from '@/utils/common'
import { onLoad } from '@dcloudio/uni-app';
import { topTabar } from '@/utils/topTabbar'

let menuButtonInfo: any = {};
// 如果是小程序，获取右上角胶囊的尺寸信息，避免导航栏右侧内容与胶囊重叠(支付宝小程序非本API，尚未兼容)
// #ifdef MP-WEIXIN || MP-BAIDU || MP-TOUTIAO || MP-QQ
menuButtonInfo = uni.getMenuButtonBoundingClientRect();
// #endif
/********* 自定义头部 - start ***********/
const topTabarObj = topTabar()
const param = topTabarObj.setTopTabbarParam({ title: '', topStatusBar: { bgColor: '#fff', textColor: '#333' } })
/********* 自定义头部 - end ***********/
const headerHeight = computed(() => {
    return Object.keys(menuButtonInfo).length ? pxToRpx(Number(menuButtonInfo.height)) + pxToRpx(menuButtonInfo.top) + pxToRpx(8) + 'rpx' : 'auto'
})
const real_name_input = ref(true);
const memberStore = useMemberStore()
const configStore = useConfigStore()
const type = ref('')
const isAgree = ref(false)
const isShowQuickLogin = ref(false) // 是否显示快捷登录
const popupRef = ref()
const isPassword = ref(true)

const changePassword = () => {
    isPassword.value = !isPassword.value
}

const dialogClose = () => {
    popupRef.value.close();
}

const dialogConfirm = () => {
    isAgree.value = true
    popupRef.value.close();
    handleLogin()
}

onLoad(async(option: any) => {
    await configStore.getLoginConfig()
    if (!getToken() && !configStore.login.is_username && !configStore.login.is_mobile) {
        uni.showToast({ title: '商家未开启普通账号登录', icon: 'none' })
        setTimeout(() => {
            redirect({ url: '/app/pages/index/index', mode: 'reLaunch' })
        }, 100)
    }
    if (option.type) {
        if (option.type == 'mobile') {
            if (configStore.login.is_mobile) {
                type.value = option.type
                uni.getStorageSync('pid') && (Object.assign(formData, { pid: uni.getStorageSync('pid') }))
            }
        } else if (option.type == 'username' && configStore.login.is_username) {
            type.value = option.type
        }
    } else {
        if (configStore.login.is_username) {
            type.value = 'username'
        } else if (configStore.login.is_mobile) {
            type.value = 'mobile'
        }

    }

    // 如果只开启了账号密码登录，那么就不需要跳转到登录中间页了

    // #ifdef MP-WEIXIN
    if (!configStore.login.is_auth_register) {
        isShowQuickLogin.value = false;
    } else {
        isShowQuickLogin.value = true;
    }
    // #endif

    // #ifdef H5
    if (isWeixinBrowser()) {
        // 微信浏览器
        if (!configStore.login.is_auth_register) {
            isShowQuickLogin.value = false;
        } else {
            isShowQuickLogin.value = true;
        }
    } else {
        // 普通浏览器
        isShowQuickLogin.value = false;
    }
    // #endif
})

const formData = reactive({
    username: '',
    password: '',
    mobile: '',
    mobile_code: '',
    mobile_key: ''
})

onMounted(() => {
    // 防止浏览器自动填充
    setTimeout(() => {
        real_name_input.value = false;
    }, 800)
});

const agreeChange = () => {
    isAgree.value = !isAgree.value
}

const setType = () => {
    type.value = type.value == 'username' ? 'mobile' : 'username'
}

const loading = ref(false)

const rules = computed(() => {
    return {
        'username': {
            type: 'string',
            required: type.value == 'username',
            message: t('usernamePlaceholder'),
            trigger: ['blur', 'change'],
        },
        'password': {
            type: 'string',
            required: type.value == 'username',
            message: t('passwordPlaceholder'),
            trigger: ['blur', 'change']
        },
        'mobile': [
            {
                type: 'string',
                required: type.value == 'mobile',
                message: t('mobilePlaceholder'),
                trigger: ['blur', 'change'],
            },
            {
                validator(rule: any, value: any) {
                    if (type.value != 'mobile') return true
                    else return uni.$u.test.mobile(value)
                },
                message: t('mobileError'),
                trigger: ['change', 'blur'],
            }
        ],
        'mobile_code': {
            type: 'string',
            required: type.value == 'mobile',
            message: t('codePlaceholder'),
            trigger: ['blur', 'change']
        }
    }
})

const formRef: any = ref(null)

const handleLogin = () => {
    formRef.value.validate().then(() => {
        if (configStore.login.agreement_show && !isAgree.value) {
            popupRef.value.open();
            // uni.showToast({ title: t('isAgreeTips'), icon: 'none' });
            return false;
        }

        if (loading.value) return
        loading.value = true

        const login = type.value == 'username' ? usernameLogin : mobileLogin

        login(formData).then((res: any) => {
            memberStore.setToken(res.data.token)
            // todo 已注册的会员不受影响
            // if (configStore.login.is_bind_mobile && !res.data.mobile) {
            //     uni.setStorageSync('isBindMobile', true)
            // }
            useLogin().handleLoginBack()
        }).catch(() => {
            loading.value = false
        })
    })
}

const toLink = () => {
    const pages = getCurrentPages(); // 获取页面栈
    if (pages.length > 1) {
        const currentPage = pages[pages.length - 2].route;
        if (currentPage == 'app/pages/auth/index') {
            // 返回上一页
            uni.navigateBack({
                delta: 1 // 默认值是1，表示返回的页面层数
            });
        } else {
            redirect({ url: '/app/pages/auth/index', mode: 'redirectTo' })
        }
    } else {
        redirect({ url: '/app/pages/auth/index', mode: 'redirectTo' })
    }

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

.footer {
    margin-top: 200rpx;
    padding-bottom: calc(151rpx + constant(safe-area-inset-bottom));
    padding-bottom: calc(151rpx + env(safe-area-inset-bottom));
}
</style>

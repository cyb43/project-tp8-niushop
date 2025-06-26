<template>
    <div>
         <span v-if="active" class="iconfont icon-mianxing_denglu_erweimadenglu !text-[#333] !text-[50px] absolute top-0 right-0 cursor-pointer" @click="handleChange"></span>
        <span v-else class="iconfont icon-zhanghaodenglu !text-[#333] !text-[50px] absolute top-0 right-0 cursor-pointer" @click="handleChange"></span>
        <div  v-if="active" class="bg-white w-full py-[60px] px-[30px] !rounded-[var(--rounded-big)]">
            <div class="flex items-end justify-center mb-[30px]">
                <div class="text-[18px] cursor-pointer text-[#999] leading-[24px] oppoSans-R" :class="{ '!text-[#333] font-600': type == item.type,'mr-[70px]': (index+1) != loginType.length }" v-for="(item,index) in loginType" @click="type = item.type">{{item.title }}</div>
            </div>
            <el-form :model="formData" ref="formRef" :rules="formRules" :validate-on-rule-change="false">
                <div v-show="type == 'username'">
                    <el-form-item prop="username">
                        <div class="flex-1 h-[50px] border-[1px] border-solid border-[#ccc] rounded-[8px] flex items-center">
                            <el-input v-model="formData.username" :placeholder="t('usernamePlaceholder')" clearable :inline-message="true" :readonly="real_name_input" @click="real_name_input = false" @blur="real_name_input = true">
                                <template #prefix>
                                    <span class="iconfont icon-woV6xx1 !mr-[14px]"></span>
                                </template>
                            </el-input>
                        </div>
                    </el-form-item>
                    <el-form-item prop="password">
                        <div class="flex-1 h-[50px] border-[1px] border-solid border-[#ccc] rounded-[8px] flex items-center">
                            <el-input v-model="formData.password" :placeholder="t('passwordPlaceholder')" type="password" clearable show-password >
                                <template #prefix>
                                    <span class="iconfont icon-mima !mr-[14px]"></span>
                                </template>
                            </el-input>
                        </div>
                    </el-form-item>
                </div>
                <div v-show="type == 'mobile'">
                    <el-form-item prop="mobile">
                        <div class="flex-1 h-[50px] border-[1px] border-solid border-[#ccc] rounded-[8px] flex items-center">
                            <el-input v-model="formData.mobile" :placeholder="t('mobilePlaceholder')" clearable>
                                <template #prefix>
                                    <span class="iconfont icon-shoujiV6xx !mr-[14px]"></span>
                                </template>
                            </el-input>
                        </div>
                    </el-form-item>
                    <el-form-item prop="mobile_code">
                        <div class="flex-1 h-[50px] border-[1px] border-solid border-[#ccc] rounded-[8px] flex items-center">
                            <el-input v-model="formData.mobile_code" :placeholder="t('codePlaceholder')">
                                <template #prefix>
                                    <span class="iconfont icon-a-zhibao5 !mr-[14px]"></span>
                                </template>
                                <template #suffix>
                                    <sms-code :mobile="formData.mobile" type="login" v-model="formData.mobile_key" @click="sendSmsCode" ref="smsCodeRef"></sms-code>
                                </template>
                            </el-input>
                        </div>
                    </el-form-item>
                </div>
                <div class="flex justify-between">
                    <el-button type="primary" link @click="typeChange" class="!text-[12px]">{{ t('noAccount') }}，{{ t('toRegister') }}</el-button>
                </div>
                <div class="mt-[20px]">
                    <el-button type="primary" class="w-full !h-[50px] !rounded-[8px] oppoSans-M" size="large" @click="handleLogin" :loading="loading">{{ loading ? t('logining') : t('login') }}</el-button>
                </div>
                <div class="text-[12px] leading-[24px] flex items-center w-full mt-[20px]" v-if="configStore.login.agreement_show">
                    <span class="iconfont text-primary mr-[5px]" :class="isAgree ? 'icon-xuanze1' : 'icon-checkbox_nol'" @click="isAgree = !isAgree"></span>
                    {{ t('agreeTips') }}
                    <NuxtLink :to="service" target="_blank">
                        <span class="text-primary mx-[4px]">{{ t('userAgreement') }}</span>
                    </NuxtLink>
                    {{ t('and') }}
                    <NuxtLink :to="privacy" target="_blank">
                        <span class="text-primary mx-[4px]">{{ t('privacyAgreement') }}</span>
                    </NuxtLink>
                </div>
            </el-form>
        </div>
        <div v-else class="flex flex-col items-center py-[60px]  px-[30px]">
            <div class="text-[18px] cursor-pointer text-[#999] leading-[24px] oppoSans-R !text-[#333] font-600">微信扫码登录</div>
            <div class="qrcode p-[20px] mt-[30px] border leading-none box-content rounded-[var(--rounded-small)]">
                <div class="relative">
                    <el-image v-if="weixinCode.url" :src="weixinCode.url" class="w-[200px] h-[200px]"/>
                    <div v-else class="w-[202px] h-[202px]"></div>
                    <div class="flex flex-col justify-center items-center absolute inset-0 bg-gray-50" v-if="weixinCode.pastDue">
                        <span class="text-xs text-gray-600">{{ weixinCode.pastDueContent }}</span>
                        <span @click="scanLoginFn()" class="text-xs cursor-pointer text-color mt-2">点击刷新</span>
                    </div>
                </div>
                <div class="mt-[22px] flex items-center justify-center">
                    <span class="iconfont icon-weixin1 text-[#00c22c]"></span>
                    <span class="text-[14px] text-[#999] ml-[4px]">微信扫一扫</span>
                </div>
            </div>
        </div>
    </div>
</template>
<script lang="ts" setup>
import { ref,reactive,computed,onUnmounted } from 'vue'
import { FormInstance } from 'element-plus'
import { usernameLogin, mobileLogin, scanlogin, checkscan } from '@/app/api/auth'
import useMemberStore from '@/stores/member'
import useConfigStore from '@/stores/config'
import QRCode from "qrcode";

const memberStore = useMemberStore()
const configStore = useConfigStore()

// 跳转
const service = ref('')
const privacy = ref('')
if(location.pathname.indexOf('web') != -1){
    service.value = '/web/auth/agreement?key=service'
    privacy.value = '/web/auth/agreement?key=privacy'
}else{
    service.value = '/auth/agreement?key=service'
    privacy.value = '/auth/agreement?key=privacy'
}

//当前为二维码还是账户登录
let active = ref(true)
let timer:any = null
const handleChange = () => {
    active.value = !active.value
    if(!active.value){
        scanLoginFn();
    }else{
        clearTimeout(timer)
    }
}
onUnmounted(() => {
    clearTimeout(timer)
});
// 校验二维码
const checkScanFn = (key) => {
    let parameter = { key };

    checkscan(parameter).then((res) => {
        let data = res.data;
        switch (data.status) {
            case 'wait':
                timer = setTimeout(() => {
                    checkScanFn(weixinCode.value.key);
                }, 1000);
                break;
            case 'success':
                if (!data.login_data.token) {
                    useCookie('openId').value = data.login_data.openid
                    navigateTo(`/auth/bind`)
                    memberStore.logClose()
                } else {
                    memberStore.setToken(data.login_data.token)
                    memberStore.logClose()
                }
                break;
            case 'fail':
                weixinCode.value.pastDueContent = data.fail_reason
                weixinCode.value.pastDue = true;
                break;
        }
    }).catch((res) => {
        weixinCode.value.pastDue = true;
        weixinCode.value.pastDueContent = res.msg;
    })
}

// 扫码登录,微信二维码
const weixinCode = ref({
    url: '',
    key: '',
    pastDue: false,
    pastDueContent: '二维码生成失败'
})

const scanLoginFn = async () => {
    let data = await (await scanlogin()).data;
    weixinCode.value.key = data.key
    if(data.url) {
        QRCode.toDataURL(data.url, { errorCorrectionLevel: 'L', margin: 0, width: 100 }).then(url => {
            weixinCode.value.url = url
        });
        weixinCode.value.pastDue = false;
        setTimeout(() => {
            checkScanFn(weixinCode.value.key);
        }, 1000);
    }
}

configStore.getLoginConfig()

const loginType = computed(() => {
    const value = []
    configStore.login.is_username && (value.push({ type: 'username', title: t('usernameLogin') }))
    configStore.login.is_mobile && (value.push({ type: 'mobile', title: t('mobileLogin') }))
    type.value = value[0] ? value[0].type : ''
    return value
})

const loading = ref(false)
const type = ref('')
const formData = reactive({
    username: '',
    password: '',
    mobile: '',
    mobile_code: '',
    mobile_key: ''
})
const formRef = ref<FormInstance>()
const formRules = computed(() => {
    return {
        'username': {
            required: type.value == 'username',
            message: t('usernamePlaceholder'),
            trigger: ['blur', 'change'],
        },
        'password': {
            required: type.value == 'username',
            message: t('passwordPlaceholder'),
            trigger: ['blur', 'change']
        },
        'mobile': [
            {
                required: type.value == 'mobile',
                message: t('mobilePlaceholder'),
                trigger: ['blur', 'change'],
            },
            {
                validator(rule: any, value: string, callback: any) {
                    if (type.value != 'mobile') return true
                    else return test.mobile(value)
                },
                message: t('mobileError'),
                trigger: ['blur'],
            }
        ],
        'mobile_code': {
            required: type.value == 'mobile',
            message: t('codePlaceholder'),
            trigger: ['change']
        }
    }
})
const isAgree = ref(false)
const handleLogin = async () => {
    await formRef.value?.validate(async (valid, fields) => {
        if (valid) {
            if (configStore.login.agreement_show && !isAgree.value) {
                ElMessage.error(t('isAgreeTips'))
                return false;
            }
            if (loading.value) return
            loading.value = true

            const login = type.value == 'username' ? usernameLogin : mobileLogin

            login(formData).then(async (res) => {
                await memberStore.setToken(res.data.token)
                memberStore.logClose()
            }).catch(() => {
                loading.value = false
            })
        }
    })
}

const smsCodeRef = ref<AnyObject | null>(null)
const sendSmsCode = async () => {
    await formRef.value?.validateField('mobile', async (valid, fields) => {
        if (valid) {
            smsCodeRef.value?.send()
        }
    })
}
//去注册
const emit = defineEmits(['typeChange'])
const typeChange = ()=>{
    emit('typeChange','register')
}

const real_name_input = ref(true)
const password_input = ref(true)
</script>

<style lang="scss" scoped>

</style>

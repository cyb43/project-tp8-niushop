<template>
    <!--短信设置-->
    <div class="main-container" v-loading = "loading">
        <div v-if="!loading">
            <div v-if="isLogin && !isRecharge">
                <el-card class="box-card !border-none mb-[15px]" shadow="never">
                    <el-page-header :content="pageName" :icon="ArrowLeft" @back="back()" />
                </el-card>
                <el-form label-width="100px" ref="formRef" class="page-form">
                    <el-card class="box-card !border-none relative" shadow="never">
                        <el-alert type="warning" :closable="false" class="!mb-[30px]">
                            <template #default>
                            <p class="mb-[5px]">牛云短信操作指引</p>
                            <p class="mb-[5px]">* 开启准备：若要开启牛云短信功能，需登录对应账号，并配置可用的短信签名。</p>
                            <p class="mb-[5px]">* 审核说明：短信签名设置与模板消息开启均需经过审核。审核时间为周一至周日的 9:30 - 22:00（法定节假日审核时间顺延）。工作日内，审核预计耗时 2 小时；非工作日，预计耗时 4 小时。</p>
                            <p class="mb-[5px]">* 签名报备要求：签名可使用公司全称或简称（简称需为公司全称的一部分，不能增减或跳字，且签名需具备唯一性）。若使用非公司简称作为签名，需提供 app 在 ICP 备案截图或商标证明 ，且签名必须与资质名称完全一致。</p>
                            <p class="mb-[5px]">* 模版报备要点：报备模板时，请确认模板中变量对应的类型。若模板某变量内容超出长度限制，系统将自动截取，以确保短信正常发出。</p>
                            <p class="mb-[5px]">* 短信发送条件：短信成功发送需满足两个条件，一是签名审核通过且在运营商处实名认证成功；二是模板审核通过。</p>
                            <p class="mb-[5px]">* 其他事项：短信数量不足时，请及时进行充值。如有任何疑问，可联系客服，客服电话：400 - 886 - 7993（服务时间为 9:00 - 18:00 ）。</p>
                            </template>
                        </el-alert>
                        <h3 class="panel-title">{{ t('短信信息') }}</h3>
                        <el-row class="row-bg px-[30px] mb-[20px]">
                            <el-col :span="8">
                                <el-form-item :label="t('用户名')">
                                    <div class="input-width">
                                        <span>{{ formData.username }}</span>
                                        <el-button type="primary" link @click="isLogin = false" class="ml-[10px]">{{ t('切换账户') }}</el-button>
                                    </div>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item :label="t('公司名称')">
                                    <div class="input-width">{{ formData.company }}</div>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item :label="t('账户状态')">
                                    <div class="input-width">{{ formData.status_name }}</div>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item :label="t('手机号')">
                                    <div class="input-width">
                                        <span>{{ formData.mobiles ? formData.mobiles : '暂无'}}</span>
                                        <el-button type="primary" link @click="changeMobile()" class="ml-[10px]">{{ t('更换手机号') }}</el-button>
                                    </div>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item :label="t('签名')">
                                    <div class="input-width">
                                        <span>{{ formData.signature ? formData.signature : '暂无'}}</span>
                                        <el-button type="primary" link @click="openDialog" class="ml-[10px]">{{ t('更换签名') }}</el-button>
                                    </div>
                                </el-form-item>
                            </el-col>
                        </el-row>

                        <h3 class="panel-title">{{ t('短信权限') }}</h3>
                        <el-row class="row-bg px-[30px] mb-[20px]">
                            <el-col :span="24">
                                <el-form-item :label="t('是否开启')">
                                    <el-switch v-model="is_enable" :active-value="1" :inactive-value="0"  :before-change="beforeChangeIsEnable" />
                                </el-form-item>
                                <div class="mb-[10px] text-[12px] ml-[30px] text-[#999] leading-[20px]">是否开启牛云短信模版</div>
                            </el-col>
                        </el-row>
                        <h3 class="panel-title">{{ t('短信条数') }}</h3>
                        <el-row class="row-bg px-[30px] mb-[20px]">
                            <el-col :span="24">
                                <el-form-item :label="t('短信')">
                                    <div class="flex justify-between items-center">
                                        <span class="text-primary text-[20px] mx-[5px]">{{formData.sms_count}}</span>条
                                        <el-button  @click="isRecharge =true" class="ml-[30px]">{{ t('短信充值') }}</el-button>
                                    </div>
                                </el-form-item>
                            </el-col>
                        </el-row>
                        <el-tabs v-model="activeName" class="demo-tabs">
                            <el-tab-pane label="短信模版" name="template">
                                <smsTemplate :username="username" :signature="formData.signature"></smsTemplate>
                            </el-tab-pane>
                            <el-tab-pane label="充值记录" name="recharge">
                                <smsRechargeRecord :username="username"></smsRechargeRecord>
                            </el-tab-pane>
                            <el-tab-pane label="发送记录" name="send">
                                <smsSend :username="username"></smsSend>
                            </el-tab-pane>
                        </el-tabs>
                    </el-card>
                </el-form>
            </div>
            <smsNiuLogin v-show="!isLogin" :info="formData" :isLogin="isLoginStatus" @complete="getAccountIsLoginFn"></smsNiuLogin>
            <smsRecharge v-show="isRecharge" @back="isRecharge = false" @complete="backRecharge" :username="username" :isRecharge="isRecharge"></smsRecharge>
            <smsSignature ref="signatureDialogRef" :username="username" @select="handleSelectTemplate" ></smsSignature>
            <el-dialog v-model="visibleMobile" :title="t('更换手机号')" width="600px" destroy-on-close >
                <el-form label-width="120px" :model="changeFormData" ref="changeFormRef" :rules="changeFormRules"  class="page-form ml-[20px]">
                    <el-form-item label="手机号" prop="mobile" v-if="formData.mobiles">
                        <el-input placeholder="请输入手机号" disabled class="input-width" maxlength="11"  show-word-limit v-model="formData.mobiles" clearable />
                    </el-form-item>
                    <el-form-item label="新手机号" prop="new_mobile" v-if="formData.mobiles">
                        <el-input placeholder="请输入新手机号" class="input-width" maxlength="11" show-word-limit v-model="changeFormData.new_mobile" clearable />
                    </el-form-item>
                    <el-form-item label="手机号" prop="new_mobile" v-if="!formData.mobiles">
                        <el-input placeholder="请输入手机号" class="input-width" maxlength="11" show-word-limit v-model="changeFormData.new_mobile" clearable />
                    </el-form-item>
                    <el-form-item label="验证码" prop="captcha_code" v-if="formData.mobiles">
                        <div class="flex items-center">
                            <el-input placeholder="请输入验证码" class="input-width" maxlength="4"  show-word-limit v-model="changeFormData.captcha_code" clearable />
                            <img :src="changeFormData.captcha_img" alt="验证码" class="w-[100px] h-[32px] cursor-pointer ml-[10px]" @click="getSmsCaptchaFn" />
                        </div>
                    </el-form-item>
                    <el-form-item label="动态码" prop="code" v-if="formData.mobiles">
                        <div class="flex items-center">
                            <el-input placeholder="请输入动态码" class="input-width" maxlength="4"  show-word-limit v-model="changeFormData.code" clearable />
                            <el-button  class="ml-[10px]"  @click="getSmsSendFn" :disabled="countdown > 0" :loading="sending">
                                {{ countdown > 0 ? `${countdown}秒后重新获取` : '获取动态码' }}
                            </el-button>
                        </div>
                    </el-form-item>

                </el-form>
                <template #footer>
                    <el-button @click="visibleMobile = false">{{ t("cancel") }}</el-button>
                    <el-button type="primary" @click="onSave()">{{ t("confirm") }}</el-button>
                </template>
            </el-dialog>
        </div>
        <div v-else>
            <el-card class="box-card !border-none mb-[15px] min-h-[100vh]"  shadow="never"></el-card>
        </div>
    </div>

</template>

<script lang="ts" setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { t } from '@/lang'
import { ArrowLeft } from '@element-plus/icons-vue'
import { getAccountIsLogin, getAccountInfo, editAccount, editSms, getSmsCaptcha, getSmsSend, enableNiusms } from '@/app/api/notice'
import { useRoute, useRouter } from 'vue-router'
import smsNiuLogin from '@/app/views/setting/components/sms_niu_login.vue'
import smsTemplate from '@/app/views/setting/components/sms_template.vue'
import smsRechargeRecord from '@/app/views/setting/components/sms_recharge_record.vue'
import smsRecharge from '@/app/views/setting/components/sms_recharge.vue'
import smsSend from '@/app/views/setting/components/sms_send.vue'
import smsSignature from '@/app/views/setting/components/sms_signature.vue'

const router = useRouter()
const route = useRoute()
const pageName = route.meta.title
const isLogin = ref(true)
const formData = reactive({
    mobiles: '',
    sms_count: '',
    username: '',
    company: '',
    signature: '',
    status_name: ''
})
const isInit = ref(false) // 标记是否初始化完成
const activeName = ref('template')
const loading = ref(true)
const isRecharge = ref(false)
const isLoginStatus = ref(true)
const is_enable = ref(0)
const username = ref('')
onMounted(() => {
    getAccountIsLoginFn()
})
const backRecharge = () => {
    isRecharge.value = false
    getAccountIsLoginFn()
}
const getAccountIsLoginFn = () => {
    loading.value = true
    getAccountIsLogin().then(res => {
        isLoginStatus.value = res.data.is_login
        isLogin.value = res.data.is_login
        username.value = res.data.username
        is_enable.value = res.data.is_enable
        if (res.data.is_login) {
            getAccountInfo(username.value).then(res => {
                Object.assign(formData, res.data)
                loading.value = false
                isInit.value = true
            })
        } else {
            loading.value = false
            isInit.value = true
        }
    }).catch(err => {
        loading.value = false
        isInit.value = true
    })
}

const signatureDialogRef = ref(null)

const openDialog = () => {
    signatureDialogRef.value?.open()
}

const beforeChangeIsEnable = (val) => {
    if (!isInit.value) return false

    const enable = is_enable.value == 1 ? 0 : 1
    return new Promise((resolve, reject) => {
        enableNiusms({ is_enable: enable }).then(() => {
            resolve(true)
        }).catch(() => {
            reject(false)
        })
    })
}

const handleSelectTemplate = (val) => {
    loading.value = true
    editAccount(username.value, { signature: val.sign }).then(res => {
        getAccountIsLoginFn()
    })
}

// 更换手机号
const changeFormRef = ref()
const changeFormData = ref({
    new_mobile: '',
    captcha_key: '',
    captcha_code: '',
    captcha_img: '',
    code: '',
    key: ''
})

const changeMobile = async () => {
    changeFormData.value.new_mobile = ''
    changeFormData.value.captcha_key = ''
    changeFormData.value.captcha_code = ''
    changeFormData.value.captcha_img = ''
    changeFormData.value.code = ''
    changeFormData.value.key = ''

    try {
        await getSmsCaptchaFn()
        visibleMobile.value = true
    } catch (e) {
    }
}

const getSmsCaptchaFn = () => {
    return getSmsCaptcha().then(res => {
        changeFormData.value.captcha_key = res.data.captcha_key
        changeFormData.value.captcha_img = res.data.img
    }).catch(err => {
    })
}

const visibleMobile = ref(false)
const changeFormRules = computed(() => {
    return {
        new_mobile: [
            { required: true, message: '请输入手机号', trigger: 'blur' },
            {
                pattern: /^1[3-9]\d{9}$/,
                message: '请输入正确的手机号',
                trigger: ['blur', 'change']
            }
        ],
        captcha_code: [
            { required: true, message: '请输入验证码', trigger: 'blur' }
        ],
        code: [
            { required: true, message: '请输入动态码', trigger: 'blur' }
        ]
    }
})

const sending = ref(false) // 发送中状态
const countdown = ref(0) // 倒计时秒数

const getSmsSendFn = () => {
    if (countdown.value > 0 || sending.value) return // 正在倒计时或发送中，直接返回
    changeFormRef.value.validateField(['captcha_code'], (valid) => {
        if (!valid) return
        sending.value = true // 标记为发送中
        const params = {
            mobile: formData.mobiles,
            captcha_key: changeFormData.value.captcha_key,
            captcha_code: changeFormData.value.captcha_code
        }
        getSmsSend(params).then((res) => {
            changeFormData.value.key = res.data.key
            startCountdown(60) // 启动60秒倒计时
        }).catch((err) => {
            getSmsCaptchaFn()
            sending.value = false
        }).finally(() => {
            sending.value = false // 无论成功失败都重置发送状态
        })
    })
}

// 启动倒计时
const startCountdown = (seconds) => {
    countdown.value = seconds
    const timer = setInterval(() => {
        countdown.value--
        if (countdown.value <= 0) {
            clearInterval(timer)
        }
    }, 1000)
}

const onSave = async () => {
    await changeFormRef.value?.validate(async (valid) => {
        if (valid) {
            let params = {}
            if (formData.mobiles) {
                params = {
                    mobile: formData.mobiles,
                    new_mobile: changeFormData.value.new_mobile,
                    key: changeFormData.value.key,
                    code: changeFormData.value.code
                }
            } else {
                params = {
                    new_mobile: changeFormData.value.new_mobile
                }
            }

            editAccount(username.value, params).then(res => {
                visibleMobile.value = false
                getAccountIsLoginFn()
            })
        }
    })
}

const back = () => {
    router.push('/setting/sms/setting')
}
</script>

<style lang="scss" scoped></style>

<template>
    <el-card class="box-card !border-none"  shadow="never" v-loading="loading">
        <div v-if="type=='login'" >
            <div class="bg-[var(--el-color-primary-light-9)] p-2 text-[14px] rounded-[6px]">
                <span>还未注册牛云短信?</span>
                <span @click="toRegister" class="cursor-pointer text-primary">去注册</span>
            </div>
            <el-form :model="formData" label-width="150px" ref="formRef" :rules="formRules" class="page-form mt-[20px]">
                <el-form-item label="用户名" prop="username">
                    <el-input placeholder="请输入用户名" class="input-width" v-model="formData.username" clearable maxlength="50" autocomplete="off" />
                </el-form-item>
                <el-form-item label="密码" prop="password">
                    <el-input placeholder="请输入密码" type="password" show-password  show-word-limit maxlength="16" class="input-width" autocomplete="new-password" v-model="formData.password" clearable />
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="login">登录</el-button>
                    <el-button @click="editPass()">忘记密码</el-button>
                    <el-button @click="back()" v-if="props.isLogin">返回</el-button>
                </el-form-item>
            </el-form>
        </div>
        <div v-if="type=='register'" >
            <div class="bg-[var(--el-color-primary-light-9)] p-2 text-[14px] rounded-[6px]">
                <span>已有账号，</span>
                <span @click="type='login'" class="cursor-pointer text-primary">去登录</span>
            </div>
            <el-form :model="registerFormData" label-width="150px" ref="registerFormRef" :rules="registerFormRules" class="page-form mt-[20px]">
                <h3 class="panel-title !text-[14px]">{{ t('基本信息') }}</h3>
                <el-form-item label="用户名" prop="username">
                    <el-input placeholder="请输入用户名" class="input-width" autocomplete="off" maxlength="50"  show-word-limit v-model="registerFormData.username" clearable />
                </el-form-item>
                <div class="mb-[10px] text-[12px] ml-[150px] text-[#999] leading-[20px]">子账户用户名，仅支持6~50位英文+数字组合</div>
                <el-form-item label="公司名称" prop="company">
                    <el-input placeholder="请输入公司名称" class="input-width" maxlength="50"  show-word-limit v-model="registerFormData.company" clearable />
                </el-form-item>
                <el-form-item label="手机号" prop="mobile">
                    <el-input placeholder="请输入手机号" class="input-width" maxlength="11" show-word-limit v-model="registerFormData.mobile" clearable />
                </el-form-item>
                <el-form-item label="验证码" prop="captcha_code">
                    <div class="flex items-center">
                        <el-input placeholder="请输入验证码" class="input-width"  maxlength="4" show-word-limit v-model="registerFormData.captcha_code" clearable />
                        <img :src="registerFormData.captcha_img" alt="验证码" class="w-[100px] h-[32px] cursor-pointer ml-[10px]" @click="getSmsCaptchaFn" />
                    </div>
                </el-form-item>
                <el-form-item label="动态码" prop="code">
                    <div class="flex items-center">
                        <el-input placeholder="请输入动态码" class="input-width" maxlength="4" show-word-limit v-model="registerFormData.code" clearable />
                        <el-button  class="ml-[10px]"  @click="getSmsSendFn" :disabled="countdown > 0" :loading="sending">
                            {{ countdown > 0 ? `${countdown}秒后重新获取` : '获取动态码' }}
                        </el-button>
                    </div>
                </el-form-item>
                <el-form-item label="初始密码" prop="password">
                    <el-input placeholder="请输入初始密码" type="password" autocomplete="new-password" maxlength="16" show-password class="input-width" v-model="registerFormData.password" clearable />
                </el-form-item>
                <div class="mb-[10px] text-[12px] ml-[150px] text-[#999] leading-[20px]">密码由数字、大小写字母组成，密码长度8-16位</div>
                <el-form-item :label="t('默认签名')" prop="signature">
                    <el-input v-model="registerFormData.signature" placeholder="请输入默认签名" class="input-width" maxlength="20"  show-word-limit clearable />
                </el-form-item>
                <div class="ml-[150px] text-[12px] text-[#999] leading-[20px]">必须由【】包裹，例如：【test】</div>
                <div class="my-[5px] ml-[150px] text-[12px] text-[#999] leading-[20px]">字数要求在2-20个字符，不能使用空格和特殊符号“ - + = * & % # @ ~等;</div>
                <el-form-item label="备注" prop="remark">
                    <el-input placeholder="请输入备注" class="input-width" type="textarea" maxlength="50"  show-word-limit v-model="registerFormData.remark" clearable />
                </el-form-item>
                <h3 class="panel-title !text-[14px">{{ t('实名信息') }}</h3>
                <el-form-item :label="t('短信示例内容')" prop="contentExample">
                    <el-input v-model="registerFormData.contentExample" placeholder="请输入短信示例内容" clearable  maxlength="50"  show-word-limit class="input-width" />
                </el-form-item>
                <el-form-item :label="t('企业名称')" prop="companyName">
                    <el-input v-model="registerFormData.companyName" placeholder="请输入企业名称" clearable  maxlength="20"  show-word-limit class="input-width" />
                </el-form-item>
                <el-form-item :label="t('社会统一信用代码')" prop="creditCode">
                    <el-input v-model="registerFormData.creditCode" placeholder="请输入社会统一信用代码" clearable  maxlength="20"  show-word-limit class="input-width" />
                </el-form-item>
                <el-form-item :label="t('法人姓名')" prop="legalPerson">
                    <el-input v-model="registerFormData.legalPerson" placeholder="请输入法人姓名" clearable  maxlength="20"  show-word-limit class="input-width" />
                </el-form-item>
                <el-form-item :label="t('经办人姓名')" prop="principalName">
                    <el-input v-model="registerFormData.principalName" placeholder="请输入经办人姓名" clearable  maxlength="20"  show-word-limit class="input-width" />
                </el-form-item>
                <el-form-item :label="t('经办人手机号')" prop="principalMobile">
                    <el-input v-model="registerFormData.principalMobile" placeholder="请输入经办人手机号" clearable  maxlength="20"  show-word-limit class="input-width" />
                </el-form-item>
                <el-form-item :label="t('经办人身份证')" prop="principalIdCard">
                    <el-input v-model="registerFormData.principalIdCard" placeholder="请输入经办人身份证" clearable  maxlength="18"  show-word-limit class="input-width" />
                </el-form-item>
                <el-form-item :label="t('签名来源')">
                    <el-radio-group v-model="registerFormData.signSource" >
                        <el-radio v-for="item in signConfig.signSourceList" :key="item.type" :label="item.type" >{{item.name}}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item :label="t('签名类型')">
                    <el-radio-group v-model="registerFormData.signType">
                        <el-radio v-for="item in signConfig.signTypeList" :key="item.type" :label="item.type" >{{item.name}}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item :label="t('上传图片')" prop="imgUrl">
                    <upload-image v-model="registerFormData.imgUrl" :limit="1" />
                </el-form-item>
                <div class="ml-[150px] text-[12px] text-[#999] leading-[20px]">当签名来源为商标、APP、小程序、事业单位简称或企业名称简称时，需必填此字段</div>
                <div class="my-[5px] ml-[150px] text-[12px] text-[#999] leading-[20px]">当签名来源为事业单位全称或企业名称全称时，选填此字段。</div>
            </el-form>
            <div class="fixed-footer-wrap">
                <div class="fixed-footer">
                    <el-button type="primary" @click="register">注册</el-button>
                </div>
            </div>
        </div>
        <div v-if="type=='password'">
            <div class="bg-[var(--el-color-primary-light-9)] p-2 text-[14px] rounded-[6px]">
                <span class="text-primary">忘记密码，快去修改</span>
            </div>
            <el-form :model="changeFormData" label-width="150px" ref="changeFormRef" :rules="changeFormRules" class="page-form mt-[20px]">
                <el-form-item label="用户名" prop="username">
                    <el-input placeholder="请输入用户名" class="input-width" maxlength="50" show-word-limit v-model="changeFormData.username" clearable />
                </el-form-item>
                <el-form-item label="手机号" prop="mobile">
                    <el-input placeholder="请输入手机号" class="input-width" maxlength="11" show-word-limit v-model="changeFormData.mobile" clearable />
                </el-form-item>
                <el-form-item label="验证码" prop="captcha_code">
                    <div class="flex items-center">
                        <el-input placeholder="请输入验证码" class="input-width" maxlength="4" show-word-limit v-model="changeFormData.captcha_code" clearable />
                        <img :src="changeFormData.captcha_img" alt="验证码" class="w-[100px] h-[32px] cursor-pointer ml-[10px]" @click="getSmsCaptchaFn" />
                    </div>
                </el-form-item>
                <el-form-item label="动态码" prop="code">
                    <div class="flex items-center">
                        <el-input placeholder="请输入动态码" class="input-width" maxlength="4" show-word-limit v-model="changeFormData.code" clearable />
                        <el-button  class="ml-[10px]"  @click="getSmsSendFn" :disabled="countdown > 0" :loading="sending">
                            {{ countdown > 0 ? `${countdown}秒后重新获取` : '获取动态码' }}
                        </el-button>
                    </div>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="reset()">重置密码</el-button>
                    <el-button @click="type='login'">返回</el-button>
                </el-form-item>

            </el-form>
        </div>
    </el-card>
</template>

<script lang="ts" setup>
import { ref, computed, reactive } from 'vue'
import { loginAccount, getSmsCaptcha, getSmsSend, resetPassword, registerAccount, getSmsSignConfig } from '@/app/api/notice'
import { t } from '@/lang'

const props = defineProps({
    info: {
        type: Object,
        default: () => ({})
    },
    isLogin: {
        type: Boolean,
        default: false
    }
})

const loading = ref(false)
const formRef = ref()
const type = ref('login')
const emit = defineEmits(['complete'])
const formData = ref({
    username: '',
    password: ''
})

const isBack = computed(() => {
    return !!props.info && Object.keys(props.info).length > 0
})

const formRules = computed(() => {
    return {
        username: [
            { required: true, message: '请输入用户名', trigger: 'blur' }
        ],
        password: [
            { required: true, message: '请输入密码', trigger: 'blur' }
        ]
    }
})

const login = async () => {
    await formRef.value?.validate(async (valid) => {
        if (valid) {
            loginAccount(formData.value).then((res) => {
                emit('complete')
            })
        }
    })
}
const back = () => {
    emit('complete')
}

// 注册
const signConfig = reactive({
    signTypeList: [],
    signSourceList: []
})
const getSmsSignConfigFn = () => {
    getSmsSignConfig().then(res => {
        signConfig.signTypeList = res.data.sign_type_list
        signConfig.signSourceList = res.data.sign_source_list
        registerFormData.value.signSource = res.data.sign_source_list[0].type
        registerFormData.value.signType = res.data.sign_type_list[0].type
    })
}

getSmsSignConfigFn()
const registerFormData = ref({
    code: '',
    key: '',
    remark: '',
    username: '',
    password: '',
    company: '',
    mobile: '',
    captcha_key: '',
    captcha_code: '',
    captcha_img: '',
    imgUrl: '',
    contentExample: '',
    signType: '',
    signSource: '',
    principalIdCard: '',
    principalName: '',
    principalMobile: '',
    legalPerson: '',
    creditCode: '',
    companyName: '',
    signature: ''
})
const captchaType = ref('login')
const toRegister = async () => {
    captchaType.value = 'register'
    loading.value = true
    const success = await getSmsCaptchaFn()
    if (success) {
        registerFormData.value.username = ''
        registerFormData.value.password = ''
        type.value = 'register'
    }
    loading.value = false
}

const registerFormRef = ref()

const registerFormRules = computed(() => {
    return {
        username: [
            { required: true, message: '请输入用户名', trigger: 'blur' },
            {
                pattern: /^[A-Za-z0-9]{6,50}$/,
                message: '用户名格式不正确',
                trigger: 'blur'
            }
        ],
        password: [
            { required: true, message: '请输入密码', trigger: 'blur' },
            {
                pattern: /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[A-Za-z\d]{8,16}$/,
                message: '密码格式不正确',
                trigger: 'blur'
            }
        ],
        mobile: [
            { required: true, message: '请输入手机号', trigger: 'blur' }
        ],
        captcha_code: [
            { required: true, message: '请输入验证码', trigger: 'blur' }
        ],
        code: [
            { required: true, message: '请输入动态码', trigger: 'blur' }
        ],
        company: [
            { required: true, message: '请输入公司名称', trigger: 'blur' }
        ],
        signature: [
            { required: true, message: '请输入短信签名', trigger: 'blur' },
            {
                validator: (rule, value, callback) => {
                    const singleBracketValid = /^【[^【】]*】$/.test(value)
                    if (!singleBracketValid) {
                        return callback(new Error('短信签名必须被【】包裹'))
                    }

                    const content = value.slice(1, -1)

                    const lengthValid = content.length >= 2 && content.length <= 20
                    if (!lengthValid) {
                        return callback(new Error('短信签名内容需在 2-20 个字符之间'))
                    }

                    const invalidChars = /[\s\-+=*&%#@~;]/
                    if (invalidChars.test(content)) {
                        return callback(new Error('短信签名不能包含空格或特殊字符 - + = * & % # @ ~ ;'))
                    }

                    callback()
                },
                trigger: 'blur'
            }
        ],
        principalMobile: [
            { required: true, message: '请输入经办人手机号', trigger: 'blur' },
            { validator: phoneVerify, trigger: 'blur' }
        ],
        companyName: [
            { required: true, message: '请输入企业名称', trigger: 'blur' }
        ],
        contentExample: [
            { required: true, message: '请输入短信示例内容', trigger: 'blur' }
        ],
        creditCode: [
            { required: true, message: '请输入社会统一信用代码', trigger: 'blur' }
        ],
        legalPerson: [
            { required: true, message: '请输入法人姓名', trigger: 'blur' }
        ],
        principalName: [
            { required: true, message: '请输入经办人姓名', trigger: 'blur' }
        ],
        principalIdCard: [
            { required: true, message: '请输入经办人身份证', trigger: 'blur' },
            { validator: idCardVerify, trigger: 'blur' }
        ],
        imgUrl: [
            {
                validator: (rule, value, callback) => {
                    const needImage = [3, 4, 5].includes(registerFormData.value.signSource) || registerFormData.value.signType == 1
                    if (needImage) {
                        if (!value || value.length === 0) {
                            callback(new Error('请上传图片'))
                        } else {
                            callback()
                        }
                    } else {
                        callback() // 不需要校验
                    }
                },
                trigger: 'blur'
            }
        ]
    }
})
const idCardVerify = (rule: any, value: any, callback: any) => {
    if (value && !/^[1-9]\d{5}(19|20)\d{2}((0\d)|(1[0-2]))(([0-2]\d)|3[0-1])\d{3}([0-9Xx])$/.test(value)) {
        callback(new Error(t('请输入正确的身份证号码')))
    } else {
        callback()
    }
}

const phoneVerify = (rule: any, value: any, callback: any) => {
    if (value && !/^1[3-9]\d{9}$/.test(value)) {
        callback(new Error(t('请输入正确的手机号码')))
    } else {
        callback()
    }
}
const register = async () => {
    await registerFormRef.value?.validate(async (valid) => {
        if (valid) {
            const { captcha_key, captcha_code, captcha_img, ...params } = registerFormData.value
            registerAccount(params).then((res) => {
                type.value = 'login'
            }).catch((err) => {
                getSmsCaptchaFn()
            })
        }
    })
}

// 重置密码
const changeFormRef = ref()
const changeFormData = ref({
    mobile: '',
    captcha_key: '',
    captcha_code: '',
    captcha_img: '',
    code: '',
    key: '',
    username: props.info.username || '',
})

const getSmsCaptchaFn = async () => {
    try {
        const res = await getSmsCaptcha()
        if (captchaType.value === 'register') {
            registerFormData.value.captcha_key = res.data.captcha_key
            registerFormData.value.captcha_img = res.data.img
        } else if (captchaType.value === 'password') {
            changeFormData.value.captcha_key = res.data.captcha_key
            changeFormData.value.captcha_img = res.data.img
        }
        return true // 表示成功
    } catch (error) {
        console.error('获取验证码失败', error)
        return false // 表示失败
    }
}

const sending = ref(false) // 发送中状态
const countdown = ref(0) // 倒计时秒数

const getSmsSendFn = () => {
    if (countdown.value > 0 || sending.value) return // 正在倒计时或发送中，直接返回
    if (type.value === 'register') {
        registerFormRef.value.validateField(['mobile', 'captcha_code'], (valid) => {
            if (!valid) return
            sending.value = true // 标记为发送中
            const params = {
                mobile: registerFormData.value.mobile,
                captcha_key: registerFormData.value.captcha_key,
                captcha_code: registerFormData.value.captcha_code
            }
            getSmsSend(params).then((res) => {
                startCountdown(60) // 启动60秒倒计时
                registerFormData.value.key = res.data.key
            }).catch((err) => {
                getSmsCaptchaFn()
                sending.value = false
            }).finally(() => {
                sending.value = false // 无论成功失败都重置发送状态
            })
        })
    } else if (type.value === 'password') {
        changeFormRef.value.validateField(['mobile', 'captcha_code'], (valid) => {
            if (!valid) return
            sending.value = true // 标记为发送中

            const params = {
                mobile: changeFormData.value.mobile,
                captcha_key: changeFormData.value.captcha_key,
                captcha_code: changeFormData.value.captcha_code
            }
            getSmsSend(params).then((res) => {
                startCountdown(60) // 启动60秒倒计时
                changeFormData.value.key = res.data.key
            }).catch((err) => {
                getSmsCaptchaFn()
                sending.value = false
            }).finally(() => {
                sending.value = false // 无论成功失败都重置发送状态
            })
        })
    }
}

// 启动倒计时
const startCountdown = (seconds) => {
    countdown.value = seconds
    const timer = setInterval(() => {
        countdown.value--
        if (countdown.value <= 0) {
            clearInterval(timer)
            sending.value = false // 发送状态重置
        }
    }, 1000)
}

const changeFormRules = computed(() => {
    return {
        mobile: [
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
        ],
        username: [
            { required: true, message: '请输入用户名', trigger: 'blur' }
        ]
    }
})
const editPass = async () => {
    loading.value = true
    captchaType.value = 'password'
    const success = await getSmsCaptchaFn()
    if (success) {
        type.value = 'password'
    }
    loading.value = false
}

const reset = async () => {
    await changeFormRef.value?.validate(async (valid) => {
        if (valid) {
            const params = {
                key: changeFormData.value.key,
                code: changeFormData.value.code,
                mobile: changeFormData.value.mobile
            }
            resetPassword(changeFormData.value.username, { ...params }).then((res) => {
                const newPassword = res.data.password
                ElMessageBox.confirm(`新密码为：${newPassword}`, '请保存好新密码', {
                    confirmButtonText: '确定',
                    showCancelButton: false
                }).then(() => {
                    type.value = 'login'
                    emit('complete')
                }).catch(() => {
                    type.value = 'login'
                    emit('complete')
                })
            })
        }
    })
}

</script>

<style lang="scss" scoped>
</style>

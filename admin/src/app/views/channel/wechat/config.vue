<template>
    <!--公众号配置-->
    <div class="main-container">

        <el-card class="card !border-none" shadow="never">
            <el-page-header :content="pageName" :icon="ArrowLeft" @back="back()" />
        </el-card>

        <el-form class="page-form mt-[15px]" :model="formData" label-width="150px" ref="formRef" :rules="formRules" v-loading="loading">
            <el-card class="box-card !border-none" shadow="never">
                <h3 class="panel-title !text-sm">{{ t('wechatInfo') }}</h3>

                <el-form-item :label="t('wechatName')" prop="wechat_name">
                    <el-input v-model.trim="formData.wechat_name" :placeholder="t('wechatNamePlaceholder')" class="input-width" clearable :readonly="formData.is_authorization"/>
                </el-form-item>

                <el-form-item :label="t('wechatOriginal')" prop="wechat_original">
                    <el-input v-model.trim="formData.wechat_original" :placeholder="t('wechatOriginalPlaceholder')" class="input-width" clearable :readonly="formData.is_authorization"/>
                </el-form-item>

                <el-form-item :label="t('wechatQrcode')" prop="qr_code">
                    <upload-image v-model="formData.qr_code" />
                    <div class="form-tip">{{ t('wechatQrcodeTips') }}</div>
                </el-form-item>
            </el-card>

            <el-card class="box-card !border-none mt-[15px]" shadow="never">
                <h3 class="panel-title !text-sm">{{ t('wechatDevelopInfo') }}</h3>

                <el-form-item :label="t('wechatAppid')" prop="app_id">
                    <el-input v-model.trim="formData.app_id" :placeholder="t('appidPlaceholder')" class="input-width" clearable :readonly="formData.is_authorization"/>
                    <div class="form-tip">{{ t('wechatAppidTips') }}</div>
                </el-form-item>

                <el-form-item :label="t('wechatAppsecret')" prop="app_secret" v-if="!formData.is_authorization">
                    <el-input v-model.trim="formData.app_secret" :placeholder="t('appSecretPlaceholder')" class="input-width" clearable />
                    <div class="form-tip">{{ t('wechatAppsecretTips') }}</div>
                </el-form-item>
            </el-card>

            <el-card class="box-card !border-none mt-[15px]" shadow="never">
                <h3 class="panel-title !text-sm">{{ t('theServerSetting') }}</h3>

                <el-form-item label="URL">
                    <el-input :model-value="wechatStatic.serve_url" placeholder="Please input" class="input-width" :readonly="true">
                        <template #append>
                            <div class="cursor-pointer" @click="copyEvent(wechatStatic.serve_url)">{{ t('copy') }}</div>
                        </template>
                    </el-input>
                </el-form-item>

                <el-form-item label="Token" prop="token">
                    <el-input v-model.trim="formData.token" :placeholder="t('tokenPlaceholder')" class="input-width" maxlength="32" show-word-limit clearable />
                    <div class="form-tip">{{ t('tokenTips') }}</div>
                </el-form-item>

                <el-form-item label="EncodingAESKey" prop="encoding_aes_key">
                    <el-input v-model.trim="formData.encoding_aes_key" :placeholder="t('encodingAesKeyPlaceholder')" class="input-width" maxlength="43" show-word-limit clearable />
                    <div class="form-tip">{{ t('encodingAESKeyTips') }}</div>
                </el-form-item>

                <el-form-item :label="t('encryptionType')" prop="encryption_type">
                    <el-radio-group v-model="formData.encryption_type">
                        <el-radio label="not_encrypt">{{ t('cleartextMode') }}</el-radio>
                        <el-radio label="compatible">{{ t('compatibleMode') }}</el-radio>
                        <el-radio label="safe">{{ t('safeMode') }}</el-radio>
                    </el-radio-group>
                    <div class="form-tip">{{ t('cleartextModeTips') }}</div>
                    <div class="form-tip">{{ t('compatibleModeTips') }}</div>
                    <div class="form-tip">{{ t('safeModeTips') }}</div>
                </el-form-item>
            </el-card>

            <el-card class="box-card !border-none mt-[15px]" shadow="never">
                <div class="flex">
                    <h3 class="panel-title !text-sm">{{ t('functionSetting') }}</h3>
                </div>

                <el-form-item label="">
                    <div class="form-tip">{{ t('functionSettingTips') }}</div>
                </el-form-item>

                <el-form-item :label="t('businessDomain')">
                    <el-input :model-value="wechatStatic.business_domain" placeholder="Please input" class="input-width" :readonly="true">
                        <template #append>
                            <div class="cursor-pointer" @click="copyEvent(wechatStatic.business_domain)">{{ t('copy') }}</div>
                        </template>
                    </el-input>
                </el-form-item>

                <el-form-item :label="t('jsSecureDomain')">
                    <el-input :model-value="wechatStatic.js_secure_domain" placeholder="Please input" class="input-width" :readonly="true">
                        <template #append>
                            <div class="cursor-pointer" @click="copyEvent(wechatStatic.business_domain)">{{ t('copy') }}</div>
                        </template>
                    </el-input>
                </el-form-item>

                <el-form-item :label="t('webAuthDomain')">
                    <el-input :model-value="wechatStatic.web_auth_domain" placeholder="Please input" class="input-width" :readonly="true">
                        <template #append>
                            <div class="cursor-pointer" @click="copyEvent(wechatStatic.business_domain)">{{ t('copy') }}</div>
                        </template>
                    </el-input>
                </el-form-item>
            </el-card>
        </el-form>

        <div class="fixed-footer-wrap">
            <div class="fixed-footer">
                <el-button type="primary" :loading="loading" @click="save(formRef)">{{ t('save') }}</el-button>
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { reactive, ref, watch, computed } from 'vue'
import { t } from '@/lang'
import { getWechatConfig, getWechatStatic, editWechatConfig } from '@/app/api/wechat'
import { useClipboard } from '@vueuse/core'
import { ElMessage, FormInstance } from 'element-plus'
import { ArrowLeft } from '@element-plus/icons-vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title
const back = () => {
    router.push('/channel/wechat')
}
const loading = ref(true)

const formData = reactive<Record<string, any>>({
    wechat_name: '',
    wechat_original: '',
    app_id: '',
    app_secret: '',
    qr_code: '',
    token: '',
    encoding_aes_key: '',
    encryption_type: 'not_encrypt',
    is_authorization: 0
})

const formRef = ref<FormInstance>()

// 表单验证规则
const formRules = computed(() => {
    return {
        wechat_name: [
            { required: true, message: t('wechatNamePlaceholder'), trigger: 'blur' }
        ],
        wechat_original: [
            { required: true, message: t('wechatOriginalPlaceholder'), trigger: 'blur' }
        ],
        app_id: [
            { required: true, message: t('appidPlaceholder'), trigger: 'blur' }
        ],
        app_secret: [
            { required: !formData.is_authorization, message: t('appSecretPlaceholder'), trigger: 'blur' }
        ],
        token: [
            { required: true, message: t('tokenPlaceholder'), trigger: 'blur' }
        ],
        encoding_aes_key: [
            { required: true, message: t('encodingAesKeyPlaceholder'), trigger: 'blur' }
        ]
    }
})

/**
 * 获取微信配置
 */
getWechatConfig().then(res => {
    Object.assign(formData, res.data)
    loading.value = false
})

const wechatStatic = reactive<Record<string, string>>({
    business_domain: '',
    js_secure_domain: '',
    serve_url: '',
    web_auth_domain: ''
})

getWechatStatic().then(res => {
    Object.assign(wechatStatic, res.data)
    loading.value = false
})

/**
 * 复制
 */
const { copy, isSupported, copied } = useClipboard()
const copyEvent = (text: string) => {
    if (!isSupported.value) {
        ElMessage({
            message: t('notSupportCopy'),
            type: 'warning'
        })
        return
    }
    copy(text)
}

watch(copied, () => {
    if (copied.value) {
        ElMessage({
            message: t('copySuccess'),
            type: 'success'
        })
    }
})

/**
 * 保存
 */
const save = async (formEl: FormInstance | undefined) => {
    if (loading.value || !formEl) return

    await formEl.validate(async (valid) => {
        if (valid) {
            loading.value = true
            editWechatConfig(formData).then(() => {
                loading.value = false
            }).catch(() => {
                loading.value = false
            })
        }
    })
}

</script>

<style lang="scss" scoped></style>

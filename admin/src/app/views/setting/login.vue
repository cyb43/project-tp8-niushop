<template>
    <div class="main-container">

        <el-form class="page-form" :model="formData" :rules="formRules"  label-width="150px" ref="ruleFormRef" v-loading="loading" @submit.prevent>
            <el-card class="box-card !border-none" shadow="never">
                <h3 class="panel-title !text-sm">{{ t('commonSetting') }}</h3>

                <el-form-item :label="t('logonMode')" prop="type">
                    <el-checkbox v-model="formData.is_username" :true-value="1" :false-value="0" :label="t('isUsername')" />
                    <div class="form-tip mb-[10px]">{{ t('isUsernameTip') }}</div>
                    <el-checkbox v-model="formData.is_mobile" :true-value="1" :false-value="0" :label="t('isMobile')" />
                    <div class="form-tip">{{ t('isMobileTip') }}</div>
                </el-form-item>

                <el-form-item :label="t('isBindMobile')" prop="formData.is_bind_mobile">
                    <el-switch v-model="formData.is_bind_mobile" :active-value="1" :inactive-value="0" />
                    <div class="form-tip">{{ t('isBindMobileTip') }}</div>
                </el-form-item>

                <el-form-item :label="t('agreement')" prop="formData.agreement_show">
                    <el-switch v-model="formData.agreement_show" :active-value="1" :inactive-value="0" />
                    <div class="form-tip">{{ t('agreementTips') }}</div>
                </el-form-item>
            </el-card>

            <el-card class="box-card mt-[15px] !border-none" shadow="never">
                <h3 class="panel-title !text-sm">{{ t('tripartiteSetting') }}</h3>

                <el-form-item :label="t('isAuthRegister')" prop="formData.is_auth_register">
                    <el-switch v-model="formData.is_auth_register" :active-value="1" :inactive-value="0" />
                    <div class="form-tip">{{ t('isAuthRegisterTip') }}</div>
                </el-form-item>

                <el-form-item :label="t('isForceAccessUserInfo')" prop="formData.is_force_access_user_info" v-show="formData.is_auth_register == 1">
                    <el-switch v-model="formData.is_force_access_user_info" :active-value="1" :inactive-value="0" />
                    <div class="form-tip">{{ t('isForceAccessUserInfoTip') }}</div>
                </el-form-item>
            </el-card>

            <el-card class="box-card mt-[15px] !border-none" shadow="never">
                <h3 class="panel-title !text-sm">{{ t('loginPageSet') }}</h3>
                <el-form-item :label="t('bgUrl')">
                    <div>
                        <upload-image v-model="formData.bg_url" />
                        <p class="text-[12px] text-[#a9a9a9]">{{ t('bgUrlPlaceholder') }}</p>
                    </div>
                </el-form-item>
                <el-form-item :label="t('desc')">
                    <el-input v-model.trim="formData.desc" :placeholder="t('descPlaceholder')" class="input-width" clearable maxlength="20" show-word-limit />
                </el-form-item>

            </el-card>
        </el-form>

        <div class="fixed-footer-wrap">
            <div class="fixed-footer">
                <el-button type="primary" @click="onSave(ruleFormRef)">{{ t('save') }}</el-button>
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { reactive, ref, computed } from 'vue'
import { t } from '@/lang'
import { getLoginConfig, setLoginConfig } from '@/app/api/member'
import { FormInstance } from 'element-plus'
import { cloneDeep } from 'lodash-es'

const loading = ref(true)
const ruleFormRef = ref<FormInstance>()
const formData:any = reactive({
    is_username: 0,
    is_mobile: 0,
    is_auth_register: 0,
    is_force_access_user_info: 0,
    is_bind_mobile: 0,
    agreement_show: 0,
    bg_url: '',
    logo: '',
    desc: ''
})

const formRules = computed(() => {
    return {
        // type: [
        //     {
        //         required: true,
        //         trigger: 'change',
        //         validator: (rule: any, value: any, callback: any) => {
        //             if (!formData.is_mobile && !formData.is_username) {
        //                 callback(new Error(t('mobileOrUsernameNoEmpty')))
        //             } else {
        //                 callback()
        //             }
        //         }
        //     }
        // ]
    }
})

const setFormData = async () => {
    const data = await (await getLoginConfig()).data
    Object.keys(formData).forEach((key: string) => {
        if (data[key] != undefined) formData[key] = data[key]
    })
    loading.value = false
}
setFormData()

const onSave = async (formEl: FormInstance | undefined) => {
    if (loading.value || !formEl) return
    await formEl.validate((valid) => {
        if (valid) {
            const save = cloneDeep(formData)

            setLoginConfig(save).then(() => {
                loading.value = false
            }).catch(() => {
                loading.value = false
            })
        }
    })
}
</script>

<style lang="scss" scoped></style>

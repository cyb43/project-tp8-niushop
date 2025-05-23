<template>
    <el-dialog v-model="showDialog" :title="formData.config.pay_type_name ? formData.config.pay_type_name : t('updateFriendsPay')" width="550px" :destroy-on-close="true">
        <el-form :model="formData" label-width="110px" ref="formRef" :rules="formRules" class="page-form" v-loading="loading">
            <el-form-item :label="t('friendsPaySwitch')">
                <el-switch v-model="formData.config.pay_explain_switch" :active-value="1" :inactive-value="0"/>
            </el-form-item>
            <template v-if="formData.config.pay_explain_switch == 1">
                <el-form-item :label="t('friendsPayTitle')" prop="config.pay_explain_title">
                    <el-input v-model.trim="formData.config.pay_explain_title" :placeholder="t('friendsPayTitlePlaceholder')" class="input-width" maxlength="10" show-word-limit clearable />
                </el-form-item>
                <el-form-item :label="t('desContent')" prop="config.pay_explain_content">
                    <el-input v-model.trim="formData.config.pay_explain_content" :placeholder="t('desContentPlaceholder')" class="input-width" type="textarea" rows="4" maxlength="120" show-word-limit  clearable />
                </el-form-item>
            </template>
            <el-form-item :label="t('friendsPayGoodsSwitch')">
                <div>
                    <el-switch v-model="formData.config.pay_info_switch" :active-value="1" :inactive-value="0"/>
                    <div class="text-[12px] text-[#999] leading-[20px]">{{ t('friendsPayGoodsSwitchTips') }}</div>
                </div>
            </el-form-item>
            <el-form-item :label="t('friendsPayName')" prop="config.pay_type_name">
                <el-input v-model.trim="formData.config.pay_type_name" :placeholder="t('friendsPayNamePlaceholder')" class="input-width" maxlength="10" show-word-limit clearable />
            </el-form-item>
            <el-form-item :label="t('helpName')" prop="config.pay_page_name">
                <el-input v-model.trim="formData.config.pay_page_name" :placeholder="t('helpNamePlaceholder')" class="input-width" maxlength="10" show-word-limit clearable />
            </el-form-item>
            <el-form-item :label="t('helpBtn')" prop="config.pay_button_name">
                <el-input v-model.trim="formData.config.pay_button_name" :placeholder="t('helpBtnPlaceholder')" class="input-width" maxlength="10" show-word-limit clearable />
            </el-form-item>
            <el-form-item :label="t('remark')" prop="config.pay_leave_message">
                <el-input v-model.trim="formData.config.pay_leave_message" :placeholder="t('remarkPlaceholder')" class="input-width" type="textarea" rows="4" maxlength="20" show-word-limit clearable />
            </el-form-item>
            <el-form-item :label="t('payWechatImage')" prop="config.pay_wechat_share_image" v-if="initData.redio_key == 'wechat_friendspay'">
                <upload-image v-model="formData.config.pay_wechat_share_image" :limit="1" />
            </el-form-item>
            <el-form-item :label="t('payWeappImage')" prop="config.pay_weapp_share_image" v-if="initData.redio_key == 'weapp_friendspay'">
                <upload-image v-model="formData.config.pay_weapp_share_image" :limit="1" />
            </el-form-item>
        </el-form>
        <template #footer>
            <span class="dialog-footer">
                <el-button @click="cancel()">{{ t('cancel') }}</el-button>
                <el-button type="primary" :loading="loading" @click="confirm(formRef)">{{t('confirm')}}</el-button>
            </span>
        </template>
    </el-dialog>
</template>
<script lang="ts" setup>
import { ref, reactive, computed } from 'vue'
import { t } from '@/lang'
import type { FormInstance } from 'element-plus'
import Test from '@/utils/test'
import { cloneDeep } from 'lodash-es'

const showDialog = ref(false)
const loading = ref(true)
const initData = ref<any>(null)
/**
 * 表单数据
 */
const initialFormData = {
    type: 'friendspay',
    app_id: '',
    config: {
        pay_explain_switch: 0,
        pay_info_switch: 1,
        pay_explain_title: '',
        pay_explain_content: '',
        pay_type_name: '',
        pay_page_name: '',
        pay_button_name: '',
        pay_leave_message: '',
        pay_wechat_share_image: '',
        pay_weapp_share_image: ''
    },
    channel: '',
    status: 0,
    is_default: 0
}
const formData: Record<string, any> = reactive({ ...initialFormData })

const formRef = ref<FormInstance>()

// 表单验证规则
const formRules = computed(() => {
    return {
        'config.pay_explain_title': [
            { required: true, message: t('friendsPayTitlePlaceholder'), trigger: 'blur' },
            {
                validator: (rule: any, value: string, callback: any) => {
                    if (formData.config.pay_explain_switch == 1 && value === '') {
                        callback(new Error(t('friendsPayTitlePlaceholder')))
                    }

                    callback()
                },
                trigger: 'blur'
            }
        ],
        'config.pay_explain_content': [
            { required: true, message: t('desContentPlaceholder'), trigger: 'blur' },
            {
                validator: (rule: any, value: string, callback: any) => {
                    if (formData.config.pay_explain_switch == 1 && value === '') {
                        callback(new Error(t('desContentPlaceholder')))
                    }

                    callback()
                },
                trigger: 'blur'
            }
        ],
        'config.pay_type_name': [
            { required: true, message: t('friendsPayNamePlaceholder'), trigger: 'blur' }
        ],
        'config.pay_page_name': [
            { required: true, message: t('helpNamePlaceholder'), trigger: 'blur' }
        ],
        'config.pay_button_name': [
            { required: true, message: t('helpBtnPlaceholder'), trigger: 'blur' }
        ],
        'config.pay_leave_message': [
            { required: true, message: t('remarkPlaceholder'), trigger: 'blur' }
        ]
    }
})

const emit = defineEmits(['complete'])
/**
 * 确认
 * @param formEl
 */
const confirm = async (formEl: FormInstance | undefined) => {
    if (loading.value || !formEl) return
    await formEl.validate(async (valid) => {
        if (valid) {
            emit('complete', formData)
            showDialog.value = false
        }
    })
}

const cancel = () => {
    Object.assign(formData, initialFormData)
    if (initData.value) {
        Object.keys(formData).forEach((key: string) => {
            if (initData.value[key] != undefined) formData[key] = initData.value[key]
        })
        formData.channel = initData.value.redio_key.split('_')[0]
        formData.status = Number(formData.status)
    }
    emit('complete', formData)
    showDialog.value = false
}

const setFormData = async (data: any = null) => {
    initData.value = cloneDeep(data)
    loading.value = true
    Object.assign(formData, initialFormData)
    if (data) {
        Object.keys(formData).forEach((key: string) => {
            if (data[key] != undefined) formData[key] = data[key]
        })
        formData.channel = data.redio_key.split('_')[0]
        formData.status = Number(formData.status)
    }
    loading.value = false
}

const enableVerify = () => {
    let verify = true
    if ((formData.config.pay_explain_switch == 1 && Test.empty(formData.config.pay_explain_title)) || (formData.config.pay_explain_switch == 1 && Test.empty(formData.config.pay_explain_content)) || Test.empty(formData.config.pay_type_name) || Test.empty(formData.config.pay_page_name) || Test.empty(formData.config.pay_button_name) || Test.empty(formData.config.pay_leave_message)) verify = false
    return verify
}

defineExpose({
    showDialog,
    setFormData,
    enableVerify
})
</script>
<style lang="scss" scoped></style>

<template>
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">

            <el-form class="page-form" :model="formData" label-width="150px" ref="ruleFormRef" :rules="formRules" v-loading="loading">
                <h3 class="panel-title !text-sm">{{ t('memberNoRule') }}</h3>

                <el-form-item :label="t('prefix')" prop="prefix">
                    <el-input v-model.trim="formData.prefix" :placeholder="t('prefixPlaceholder')" class="input-width" clearable maxlength="20" @change="getMemberNo(ruleFormRef)"/>
                </el-form-item>
                <el-form-item :label="t('length')" prop="length">
                    <el-input v-model.trim="formData.length" :placeholder="t('lengthPlaceholder')" class="input-width" clearable @keyup="filterNumber($event)" @change="getMemberNo(ruleFormRef)" @blur="formData.length = $event.target.value"/>
                    <div class="form-tip">{{ t('lengthTips') }}</div>
                </el-form-item>
                <el-form-item >
                    <div class="text-lg">{{ memberNo }}</div>
                </el-form-item>

                <h3 class="panel-title">{{ t('diyForm') }}</h3>
                <el-form-item :label="t('personalForm')">
                    <el-select v-model="formData.form_id" :placeholder="t('diyFormPlaceholder')" clearable>
                        <el-option v-for="item in diyFormOptions" :key="item.form_id" :label="item.page_title" :value="item.form_id" />
                    </el-select>
                    <div class="ml-[10px]">
                        <span class="cursor-pointer text-primary mr-[10px]" @click="refreshDiyForm(true)">{{ t('refresh') }}</span>
                        <span class="cursor-pointer text-primary" @click="toDiyFormEvent">{{ t('addDiyForm') }}</span>
                    </div>
                </el-form-item>
            </el-form>
        </el-card>

        <div class="fixed-footer-wrap">
            <div class="fixed-footer">
                <el-button type="primary" @click="onSave(ruleFormRef)">{{ t('save') }}</el-button>
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import { getMemberConfig, setMemberConfig } from '@/app/api/member'
import { FormInstance, FormRules } from 'element-plus'
import { filterNumber } from '@/utils/common'
import { useRouter } from 'vue-router'
import { getDiyFormList } from '@/app/api/diy_form'

const router = useRouter()
const loading = ref(true)
const ruleFormRef = ref<FormInstance>({})
const memberNo = ref('')

// 验证会员编号
const prefixVerify = (rule: any, value: any, callback: any) => {
    if (value && !/^[a-zA-Z]*$/g.test(value)) {
        callback(new Error(t('prefixHint')))
    } else {
        callback()
    }
}

// 表单验证规则
const formRules = reactive<FormRules>({
    prefix: [
        { validator: prefixVerify, trigger: 'blur' }
    ],
    length: [
        { required: true, message: t('lengthPlaceholder'), trigger: ['blur', 'change'] },
        {
            validator: (rule: any, value: any, callback: any) => {
                if (parseInt(value) > 30 || parseInt(value) - formData.prefix.length < 4) {
                    callback(new Error(t('lengthHint')))
                } else {
                    callback()
                }
            },
            trigger: 'blur'
        }
    ]
})

const formData = reactive<Record<string, number | string>>({
    prefix: '',
    length: 10,
    form_id: ''
})

const setFormData = async () => {
    const data = await (await getMemberConfig()).data
    Object.keys(formData).forEach((key: string) => {
        if (data[key] != undefined) formData[key] = data[key]
    })
    getMemberNo(ruleFormRef.value)
    loading.value = false
}
setFormData()

const getMemberNo = async (formEl: FormInstance | undefined) => {
    await formEl.validate((valid) => {
        if (valid) {
            let no = ''
            const length = formData.length - formData.prefix.length - 1
            for (let i = 1; i <= length; i++) no += '0'
            memberNo.value = formData.prefix + no + '1'
        }
    })
}

/** ***************** 万能表单-start *************************/
// 万能表单列表下拉框
const diyFormOptions = reactive([])
// 跳转到万能表单列表，添加表单
const toDiyFormEvent = () => {
    const url = router.resolve({
        path: '/diy_form/list'
    })
    window.open(url.href)
}

// 刷新万能表单
const refreshDiyForm = (bool = false) => {
    getDiyFormList({
        type: 'DIY_FORM_MEMBER_INFO',
        status: 1
    }).then((res) => {
        const data = res.data
        if (data) {
            diyFormOptions.splice(0, diyFormOptions.length, ...data)
            if (bool) {
                ElMessage({
                    message: t('refreshSuccess'),
                    type: 'success'
                })
            }
        }
    })
}

refreshDiyForm()
/** *****************万能表单-end *************************/

const onSave = async (formEl: FormInstance | undefined) => {
    if (loading.value || !formEl) return
    await formEl.validate((valid) => {
        if (valid) {
            setMemberConfig(formData).then(() => {
                loading.value = false
                getMemberNo(ruleFormRef.value)
            }).catch(() => {
                loading.value = false
            })
        }
    })
}
</script>

<style lang="scss" scoped></style>

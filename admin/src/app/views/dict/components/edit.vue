<template>
    <el-dialog v-model="showDialog" :title="formData.id ? t('updateDict') : t('addDict')" width="480" class="diy-dialog-wrap" :destroy-on-close="true">
        <el-form :model="formData" label-width="120px" ref="formRef" :rules="formRules" class="page-form" v-loading="loading">
            <el-form-item :label="t('name')" prop="name">
                <el-input v-model.trim="formData.name" clearable maxlength="40" show-word-limit :placeholder="t('namePlaceholder')" class="input-width" />
            </el-form-item>
            <el-form-item :label="t('key')" prop="key">
                <el-input v-model.trim="formData.key" clearable  maxlength="40" show-word-limit :placeholder="t('keyPlaceholder')" class="input-width" />
                <p class="form-tip">{{ t('keyFormatTips') }}</p>
            </el-form-item>
            <el-form-item :label="t('memo')">
                <el-input v-model.trim="formData.memo" type="textarea" clearable :placeholder="t('memoPlaceholder')" class="input-width" />
            </el-form-item>
        </el-form>

        <template #footer>
            <span class="dialog-footer">
                <el-button @click="showDialog = false">{{ t('cancel') }}</el-button>
                <el-button type="primary" :loading="loading" @click="confirm(formRef)">{{ t('confirm') }}</el-button>
            </span>
        </template>
    </el-dialog>
</template>

<script lang="ts" setup>
import { ref, reactive, computed } from 'vue'
import { t } from '@/lang'
import type { FormInstance } from 'element-plus'
import { addDict, editDict, getDictInfo } from '@/app/api/dict'

const showDialog = ref(false)
const loading = ref(false)

/**
 * 表单数据
 */
const initialFormData = {
    id: '',
    name: '',
    key: '',
    memo: ''
}
const formData: Record<string, any> = reactive({ ...initialFormData })

const formRef = ref<FormInstance>()

// 表单验证规则
const formRules = computed(() => {
    return {
        name: [
            { required: true, message: t('namePlaceholder'), trigger: 'blur' }
        ],
        key: [
            { required: true, message: t('keyPlaceholder'), trigger: 'blur' },
            {
                validator: (rule: any, value: any, callback: any) => {
                    if (/^[a-zA-Z_]+$/.test(value)) {
                        callback()
                    } else {
                        callback(new Error(t('keyFormatTips')))
                    }
                },
                trigger: 'blur'
            }
        ],
        data: [
            { required: true, message: t('dataPlaceholder'), trigger: 'blur' }
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
    const save = formData.id ? editDict : addDict

    await formEl.validate(async (valid) => {
        if (valid) {
            loading.value = true

            const data = formData

            save(data).then(res => {
                loading.value = false
                showDialog.value = false
                emit('complete')
            }).catch(() => {
                loading.value = false
            })
        }
    })
}

const setFormData = async (row: any = null) => {
    Object.assign(formData, initialFormData)
    loading.value = true
    if (row) {
        const data = await (await getDictInfo(row.id)).data
        if (data) {
            Object.keys(formData).forEach((key: string) => {
                if (data[key] != undefined) formData[key] = data[key]
            })
        }
    }
    loading.value = false
}

defineExpose({
    showDialog,
    setFormData
})
</script>

<style lang="scss" scoped></style>
<style lang="scss">
.diy-dialog-wrap .el-form-item__label{
    height: auto  !important;
}
</style>

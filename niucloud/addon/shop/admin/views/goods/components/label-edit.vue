<template>
    <el-dialog v-model="showDialog" :title="title" width="500px" class="diy-dialog-wrap" :destroy-on-close="true">
        <el-form :model="formData" label-width="120px" ref="formRef" :rules="formRules" class="page-form" v-loading="loading">
            <el-form-item :label="t('labelName')" prop="label_name">
                <el-input v-model.trim="formData.label_name" clearable :placeholder="t('labelNamePlaceholder')" class="input-width" maxlength="10" />
            </el-form-item>
            <el-form-item :label="t('groupName')" prop="group_id">
                <el-select v-model="formData.group_id" :placeholder="t('groupNamePlaceholder')" clearable class="input-width">
                    <el-option v-for="item in prop.groupList" :key="item.group_id" :label="item.group_name" :value="item.group_id" />
                </el-select>
            </el-form-item>
            <el-form-item :label="t('styleType')">
                <el-radio-group v-model="formData.style_type">
                    <el-radio label="diy">{{ t('styleByDiy') }}</el-radio>
                    <el-radio label="icon">{{ t('styleByIcon') }}</el-radio>
                </el-radio-group>
            </el-form-item>
            <el-form-item :label="t('textColor')" v-show="formData.style_type == 'diy'">
                <el-color-picker v-model="formData.color_json.text_color" show-alpha :predefine="predefineColors" />
                <div class="form-tip">{{ t('colorTips') }}</div>
            </el-form-item>
            <el-form-item :label="t('bgColor')" v-show="formData.style_type == 'diy'">
                <el-color-picker v-model="formData.color_json.bg_color" show-alpha :predefine="predefineColors" />
                <div class="form-tip">{{ t('colorTips') }}</div>
            </el-form-item>
            <el-form-item :label="t('borderColor')" v-show="formData.style_type == 'diy'">
                <el-color-picker v-model="formData.color_json.border_color" show-alpha :predefine="predefineColors" />
                <div class="form-tip">{{ t('colorTips') }}</div>
            </el-form-item>

            <el-form-item :label="t('icon')" v-show="formData.style_type == 'icon'">
                <upload-image v-model="formData.icon" />
                <div class="form-tip">{{ t('iconTips') }}</div>
            </el-form-item>

            <el-form-item :label="t('status')">
                <el-switch v-model="formData.status" :active-value="1" :inactive-value="0" />
            </el-form-item>

            <el-form-item :label="t('memo')" >
                <el-input v-model.trim="formData.memo" type="textarea" clearable :placeholder="t('memoPlaceholder')" class="input-width" maxlength="50"/>
            </el-form-item>
            <el-form-item :label="t('sort')" >
                <el-input v-model.trim="formData.sort" clearable :placeholder="t('sortPlaceholder')" class="input-width" @keyup="filterNumber($event)" maxlength="8" show-word-limit @blur="formData.sort = $event.target.value"/>
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
import { filterNumber } from '@/utils/common'
import { addLabel, editLabel, getLabelInfo } from '@/addon/shop/api/goods'

const prop = defineProps({
    groupList: {
        type: Object,
        default: []
    },
})

const showDialog = ref(false)
const loading = ref(false)
const title = ref('')

/**
 * 表单数据
 */
const initialFormData = {
    label_id: '',
    label_name: '',
    group_id: '',
    style_type: 'diy',
    color_json: {
        text_color: 'rgba(255, 255, 255, 1)',
        bg_color: 'rgba(255, 65, 66, 1)',
        border_color: ''
    },
    icon: '',
    memo: '',
    sort: '',
    status: 1
}

const predefineColors = ref([
    '#F4391c',
    '#ff4500',
    '#ff8c00',
    '#FFD009',
    '#ffd700',
    '#19C650',
    '#90ee90',
    '#00ced1',
    '#1e90ff',
    '#c71585',
    '#FF407E',
    '#CFAF70',
    '#A253FF',
    'rgba(255, 69, 0, 0.68)',
    'rgb(255, 120, 0)',
    'hsl(181, 100%, 37%)',
    'hsla(209, 100%, 56%, 0.73)',
    '#c7158577'
])

const formData: Record<string, any> = reactive({ ...initialFormData })

const formRef = ref<FormInstance>()

// 表单验证规则
const formRules = computed(() => {
    return {
        label_name: [
            { required: true, message: t('labelNamePlaceholder'), trigger: 'blur' }
        ],
        group_id: [
            { required: true, message: t('groupNamePlaceholder'), trigger: 'blur' }
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
    const save = formData.label_id ? editLabel : addLabel

    await formEl.validate(async (valid) => {
        if (valid) {
            loading.value = true

            const data = formData

            save(data).then(res => {
                loading.value = false
                showDialog.value = false
                emit('complete')
            }).catch(err => {
                loading.value = false
            })
        }
    })
}

// 获取字典数据
const setFormData = async (row: any = null) => {
    Object.assign(formData, initialFormData)
    loading.value = true
    if (row) {
        const data = await (await getLabelInfo(row.label_id)).data
        title.value = t('updateLabel')
        if (data) {
            Object.keys(formData).forEach((key: string) => {
                if (data[key] != undefined) formData[key] = data[key]
            })
        }
    } else {
        title.value = t('addLabel')
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

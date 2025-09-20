<template>
    <el-dialog v-model="dialogThemeVisible" title="新增颜色" width="550px" align-center>
        <el-form :model="formData" label-width="120px" ref="formRef" :rules="formRules">
            <el-form-item label="名字" prop="title">
                <el-input v-model="formData.title" class="!w-[250px]" maxlength="7" placeholder="请输入颜色名称" />
            </el-form-item>

            <el-form-item label="颜色key值" prop="label">
                <el-input v-model="formData.label" class="!w-[250px]" maxlength="20" :disabled="type=='edit'" placeholder="请输入颜色key值" />
            </el-form-item>

            <el-form-item label="颜色value值" prop="value">
                <el-color-picker v-model="formData.value" show-alpha :predefine="diyStore.predefineColors"/>
            </el-form-item>

            <el-form-item label="颜色提示">
                <el-input v-model="formData.tip" class="!w-[250px]" placeholder="请输入颜色提示" />
            </el-form-item>
        </el-form>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="dialogThemeVisible = false">取消</el-button>
                <el-button type="primary" @click="confirmFn(formRef)">保存</el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { t } from '@/lang'
import type { FormInstance } from 'element-plus'
import { cloneDeep } from 'lodash-es'
import useDiyStore from '@/stores/modules/diy'
const diyStore = useDiyStore()

const dialogThemeVisible = ref(false)
const confirmRepeat = ref(false)
const emit = defineEmits(['confirm'])
/**
* 表单数据
*/
const initialData = {
    title: '',
    label: '',
    value: '',
    tip: ''
}
let keyArr = [] // 存储现有颜色的key
const type = ref('') // 区分编辑和添加
const formData: Record<string, any> = reactive({ ...initialData })

const open = (option:any) => {
    keyArr = option.key
    type.value = ''
    // 恢复默认值
    for (const key in formData) {
        formData[key] = ''
    }
    if (option.data && Object.keys(option.data).length) {
        type.value = 'edit'
        Object.keys(formData).forEach((item, index) => {
            formData[item] = option.data[item] ? option.data[item] : ''
        })
    }
    dialogThemeVisible.value = true
}

const formRef = ref<FormInstance>()

// 表单验证规则
const formRules = computed(() => {
    return {
        title: [
            { required: true, message: '请输入颜色名称', trigger: 'blur' }
        ],
        value: [
            {
                required: true,
                validator: (rule: any, value: any, callback: any) => {
                    if (!value) {
                        callback('请输入颜色value值')
                    } else {
                        callback()
                    }
                },
                trigger: ['blur', 'change']
            }
        ],
        label: [
            { required: true, message: '请输入颜色key值', trigger: 'blur' },
            {
                validator: (rule: any, value: any, callback: any) => {
                    const regex = /^[a-zA-Z0-9-]+$/
                    if (keyArr.indexOf(value) != -1) {
                        callback('新增颜色key值与已存在颜色key值命名重复，请修改命名')
                    } if (!regex.test(value)) {
                        callback('颜色key值只能输入字母、数字和连字符')
                    } else {
                        callback()
                    }
                },
                trigger: 'blur'
            }
        ]
    }
})

const confirmFn = async (formEl: FormInstance | undefined) => {
    if (confirmRepeat.value) return
    await formRef.value?.validate(async (valid) => {
        if (confirmRepeat.value) return
        confirmRepeat.value = true
        if (valid) {
            confirmRepeat.value = false
            emit('confirm', cloneDeep(formData))
            dialogThemeVisible.value = false
        }
    })
}

defineExpose({
    dialogThemeVisible,
    open
})
</script>

<style lang="scss" scoped>

</style>

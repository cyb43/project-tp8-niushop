<template>
    <el-dialog v-model="dialogThemeVisible" title="编辑色调" width="850px" align-center destroy-on-close="true">
        <el-form :model="openData" label-width="150px" :rules="formRules">
            <el-form-item label="色调名称" prop="title">
                <el-input v-model="openData.title" placeholder="请输入色调名称" maxlength="15" class="!w-[250px]" :disabled="openData.id != ''" @keydown.enter.native.prevent />
            </el-form-item>
        </el-form>

        <el-form :model="formData" label-width="150px" class="h-[640px] overflow-auto" ref="formRef" @submit.prevent>

            <el-form-item :label="item.title" v-for="(item, index) in formData" :key="index" :prop="`${index}.value`"
                :rules="[{ required: true, message: `请选择${item.title}色调`, trigger: 'blur' }]">
                <el-color-picker v-model="item.value" show-alpha :predefine="diyStore.predefineColors" @change="colorPickerChange($event, item)" />
                <div class="form-tip">{{ item.tip }}</div>
            </el-form-item>

            <el-form-item :label="item.title" v-for="(item, index) in openData.new_theme" :key="index">
                <div class="flex items-center">
                    <el-color-picker v-model="item.value" show-alpha :predefine="diyStore.predefineColors" />
                    <span class="text-primary cursor-pointer text-[14px] ml-[20px]" @click="editThemeFn(item)">编辑</span>
                    <span class="text-primary cursor-pointer text-[14px] ml-[8px]" @click="deleteThemeFn(item)">删除</span>
                </div>
                <div class="form-tip">{{ item.tip }}</div>
            </el-form-item>

            <el-form-item>
                <div class="flex items-center text-primary cursor-pointer text-[14px]" @click="addThemeFn">
                    <span class="mr-[3px]">+</span>
                    <span>新增颜色</span>
                </div>
                <div class="form-tip">新增颜色key值不能与当前的存在的key值重复</div>
            </el-form-item>
        </el-form>
        <add-theme-component ref="addThemeRef" @confirm="addThemeConfirm" />
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="dialogThemeVisible = false">取消</el-button>
                <el-button type="primary" plain @click="resetConfirmFn()">重置</el-button>
                <el-button type="primary" @click="confirmFn(formRef)">保存</el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { t } from '@/lang'
import { ElMessage } from 'element-plus'
import { cloneDeep } from 'lodash-es'
import addThemeComponent from './add-theme.vue'
import useDiyStore from '@/stores/modules/diy'
import { addTheme, editTheme } from '@/app/api/diy'
import type { FormInstance } from 'element-plus'

const diyStore = useDiyStore()

const dialogThemeVisible = ref(false)
const addThemeRef = ref()
const openData: Record<string, any> = reactive({ // 用于接收弹窗打开时的参数
    title: '',
    id: '',
    theme: {},
    default_theme: {},
    new_theme: [],
    key: '',
    theme_field: [] // 展示数据源
})

const emit = defineEmits(['confirm'])

const formRef = ref<FormInstance>()

// 表单验证规则
const formRules = computed(() => {
    return {
        title: [
            { required: true, message: '请输入色调名称', trigger: 'blur' }
        ]
    }
})

const formData = ref()

const open = (res: any) => { // 参数： title=>色调名称，key=>区分系统还是应用的标识，default_theme=>色调颜色的默认值，用于重置，theme=>当前色调颜色值
    Object.keys(openData).forEach((key: string) => {
        openData[key] = res[key] != undefined ? cloneDeep(res[key]) : ''
    })

    // 恢复默认值
    formData.value = [...cloneDeep(openData.theme_field)]

    // 渲染值
    formData.value.forEach((item, index) => {
        item.value = res.theme[item.label] ? res.theme[item.label] : item.value
    })
    dialogThemeVisible.value = true
}

// 新增颜色
const addThemeFn = () => {
    // 传入keyArr, 避免添加重复key
    const keyArr: string[] = []
    formData.value.forEach((item, index) => {
        keyArr.push(item.label)
    })
    const obj = {
        key: keyArr
    }

    addThemeRef.value.open(obj)
}

// 编辑颜色
const editThemeFn = (res: any) => {
    // 传入keyArr, 避免添加重复key
    const keyArr: string[] = []
    formData.value.forEach((item, index) => {
        keyArr.push(item.label)
    })

    const obj = {
        key: keyArr,
        data: res
    }
    addThemeRef.value.open(obj)
}
// 删除颜色
const deleteThemeFn = (res: any) => {
    let indent = -1
    for (let i = 0; i < openData.new_theme.length; i++) {
        if (openData.new_theme[i].label == res.label) {
            indent = i
        }
    }
    if (indent > -1) {
        openData.new_theme.splice(indent, 1)
    }
}

// 添加颜色组件回调
const addThemeConfirm = (res: any) => {
    for (let i = 0; i < openData.new_theme.length; i++) {
        if (openData.new_theme[i].label == res.label) {
            openData.new_theme[i] = res
            return
        }
    }
    openData.new_theme.push(res)
}

// 重置当前配色
const resetConfirmFn = () => {
    if (openData.default_theme && Object.keys(openData.default_theme).length) {
        formData.value.forEach((item, index) => {
            item.value = openData.default_theme[item.label]
        })
    } else {
        formData.value = cloneDeep(openData.theme_field)
        // 新增时，点击充值按钮，清空title
        if (!openData.id) {
            openData.title = ''
        }
    }

    openData.new_theme = []

    ElMessage({
        message: '重置成功',
        type: 'success'
    })
}

let confirmRepeat = false
const confirmFn = async (formEl: FormInstance | undefined) => {
    if (!formEl) return
    await formEl.validate(async (valid) => {
        if (valid) {
            const params = {
                title: '',
                id: '',
                theme: {},
                default_theme: {},
                new_theme: [],
                addon: ''
            }

            params.title = openData.title

            formData.value.forEach((item, index) => {
                params.theme[item.label] = item.value
            })
            openData.new_theme.forEach((item, index) => {
                params.theme[item.label] = item.value
            })

            params.new_theme = openData.new_theme || []
            let api = null
            if (openData.id) {
                api = editTheme
                params.id = openData.id
            } else {
                api = addTheme
            }
            params.addon = openData.key

            // 新增时，默认主题为当前主题
            if (openData.id == '') {
                const defaultTheme = {}
                openData.theme_field.forEach((item, index) => {
                    defaultTheme[item.label] = item.value
                })
                params.default_theme = cloneDeep(defaultTheme)
            } else {
                params.default_theme = cloneDeep(openData.default_theme)
            }
            if (confirmRepeat) return false
            confirmRepeat = true

            api(params).then((res: any) => {
                confirmRepeat = false
                dialogThemeVisible.value = false
                emit('confirm', params)
            }).catch(() => {
                confirmRepeat = false
            })
        }
    })
}

const applyOpacity = (color, opacity) => {
    // 解析十六进制或 RGBA 格式
    const hexRegex = /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/
    const rgbaRegex = /^rgba?\((\d+),\s*(\d+),\s*(\d+)(,\s*\d*\.?\d+)?\)$/

    if (hexRegex.test(color)) {
        // 处理十六进制颜色（如 #ffffff）
        const hex = color.replace('#', '')
        const r = parseInt(hex.substring(0, 2), 16)
        const g = parseInt(hex.substring(2, 4), 16)
        const b = parseInt(hex.substring(4, 6), 16)
        return `rgba(${r},${g},${b},${opacity})`
    } else if (rgbaRegex.test(color)) {
        // 处理 RGBA 颜色（如 rgba(255,255,255,0.5)）
        return color.replace(/[\d\.]+\)$/, `${opacity})`)
    }
    return color
}

const colorPickerChange = (e: any, data: any) => {
    if (data.label == '--primary-color') {
        formData.value.forEach((item, index) => {
            if (item.label == '--primary-color-light') {
                item.value = applyOpacity(data.value, 0.1)
            }
            if (item.label == '--primary-color-light2') {
                item.value = applyOpacity(data.value, 0.8)
            }
        })
    }
}
defineExpose({
    dialogThemeVisible,
    open
})
</script>

<style lang="scss" scoped></style>

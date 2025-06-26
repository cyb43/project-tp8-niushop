<template>
    <el-form ref="formRef" :model="formData" :rules="formRules">
        <el-form-item label="" prop="num">
            <el-checkbox v-model="formData.is_use" :true-label="1" :false-label="0" label="" size="large" />
            <span class="ml-[10px] el-form-item__label">送</span>
            <div class="w-[70px]">
                <el-input v-model.trim="formData.num" clearable :disabled="formData.is_use == 0" />
            </div>
            <span class="ml-[15px] el-form-item__label">积分</span>
        </el-form-item>
    </el-form>
</template>

<script lang="ts" setup>
import { computed, reactive, ref, watch } from 'vue'
import { FormRules } from 'element-plus'

const props = defineProps({
    modelValue: {
        type: Object,
        default: () => {
            return {}
        }
    }
})
const emits = defineEmits(['update:modelValue'])

const formData = ref({
    is_use: 0,
    num: ''
})

const formRef = ref(null)
// 正则表达式
const regExp = {
    required: /[\S]+/,
    number: /^\d{0,10}$/,
    digit: /^\d{0,10}(.?\d{0,2})$/,
    special: /^\d{0,10}(.?\d{0,3})$/
}
const formRules = reactive<FormRules>({
    num: [
        {
            validator: (rule: any, value: any, callback: any) => {
                if (formData.value.is_use) {
                    if (value.length == 0) {
                        callback('请输入积分数量')
                    } else if (isNaN(value) || !regExp.number.test(value)) {
                        callback('积分数量格式错误')
                    } else if (value <=0) {
                        callback('积分数量不能小于等于0')
                    } else{
                        callback();
                    }
                } else {
                    callback()
                }
            },
            trigger: 'blur'
        }
    ]
})

const value = computed({
    get () {
        return props.modelValue
    },
    set (value) {
        emits('update:modelValue', value)
    }
})

watch(() => value.value, (nval, oval) => {
    if ((!oval || !Object.keys(oval).length) && Object.keys(nval).length) {
        formData.value = value.value
    }
}, { immediate: true })

watch(() => formData.value, () => {
    value.value = formData.value
}, { deep: true })

const verify = async () => {
    let verify = true
    await formRef.value?.validate((valid) => {
        verify = valid
    })
    return verify
}

defineExpose({
    verify
})
</script>

<style lang="scss" scoped>
</style>

<template>
    <el-form ref="formRef" :model="formData" :rules="formRules">
        <el-form-item label="" prop="discount" class="!mb-[10px]">
            <div>
                <div class="flex items-center">
                    <el-checkbox v-model="formData.is_use" :true-label="1" :false-label="0" label="" size="large" />
                    <span class="ml-[10px] el-form-item__label">消费折扣</span>
                    <div class="w-[120px]" v-show="formData.is_use">
                        <el-input v-model.trim="formData.discount" clearable >
                            <template #append>折</template>
                        </el-input>
                    </div>
                </div>
                <div class="text-sm text-gray-400 mb-[5px]">会员购买产品默认折扣，需要商品设置参与会员折扣有效</div>
            </div>
        </el-form-item>
    </el-form>
</template>

<script lang="ts" setup>
import { computed, reactive, ref, watch } from 'vue'
import { FormRules } from 'element-plus'
import Test from '@/utils/test'

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
    discount: ''
})
const formRef = ref(null)

const formRules = reactive<FormRules>({
    discount: [
        {
            validator: (rule: any, value: any, callback: any) => {
                if (formData.value.is_use) {
                    if (Test.empty(formData.value.discount)) {
                        callback('请输入折扣')
                    }
                    if (!Test.decimal(formData.value.discount, 1)) {
                        callback('折扣格式错误')
                    }
                    if (parseFloat(formData.value.discount) < 0 || parseFloat(formData.value.discount) > 9.9) {
                        callback('折扣只能输入0~9.9之间的值')
                    }
                    if (formData.value.discount < 0) {
                        callback('折扣不能小于0')
                    }
                    callback()
                } else {
                    callback()
                }
            }
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

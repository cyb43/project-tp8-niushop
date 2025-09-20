<template>
    <el-form :model="formData" :rules="formRules" class="page-form" ref="formRef">
        <el-form-item :label="t('continueSign')" prop="continue_sign">
            <el-input class="input-width" v-model.trim="formData.continue_sign" @keyup="filterNumber($event)" :maxlength="3" clearable />
            <span class="ml-[10px]">{{ t('day') }}</span>
        </el-form-item>
        <el-form-item :label="t('continueSignAward')">
            <div class="flex-1">
                <div v-for="(item,index) in gifts" :key="index" class="mb-[15px]">
                    <component :is="item.component" v-model="formData[item.key]" ref="giftRefs" v-if="item.component" />
                </div>
            </div>
        </el-form-item>
        <el-form-item :label="t('receiveLimit')" prop="receive_num">
            <div>
                <el-radio class="mb-[15px]" v-model="formData.receive_limit" :label="1" @change="radioChange($event, 1)">{{ t('noLimit') }}</el-radio>
                <div class="flex">
                    <el-radio class="!mr-[15px]" v-model="formData.receive_limit" :label="2" @change="radioChange($event, 2)">{{ t('everyOneLimit') }}</el-radio>
                    <el-input class="input-width" v-model.trim="formData.receive_num" :maxlength="5" clearable /><span class="ml-[10px]">{{ t('time') }}</span>
                </div>
            </div>
        </el-form-item>
    </el-form>
</template>

<script lang="ts" setup>
import { t } from '@/lang'
import { ref, reactive, defineAsyncComponent, computed, watch } from 'vue'
import { FormRules } from 'element-plus'
import { getGiftDict } from '@/app/api/member'
import { guid, filterNumber } from '@/utils/common'
import Test from '@/utils/test'

const gifts = ref({})
const props = defineProps({
    modelValue: {
        type: Object,
        default: () => {
            return {}
        }
    },
    sign_period: {
        type: Number,
        default: 0
    }
})
const emits = defineEmits(['update:modelValue'])
const formData = ref({
    continue_sign: 0,
    continue_tag: guid(),
    receive_limit: 1,
    receive_num: 0
})

const value = computed({
    get () {
        return props.modelValue
    },
    set (value) {
        emits('update:modelValue', value)
    }
})
const giftRefs = ref([])

watch(() => value.value, (nval, oval) => {
    if ((!oval || !Object.keys(oval).length) && Object.keys(nval).length) {
        formData.value = value.value
    }
}, { immediate: true })

watch(() => formData.value, () => {
    value.value = formData.value
}, { deep: true })

const modules: any = import.meta.glob('@/**/*.vue')
getGiftDict().then(({ data }) => {
    Object.keys(data).forEach((key: string) => {
        data[key].component && (data[key].component = defineAsyncComponent(modules[data[key].component]))
    })
    gifts.value = data
})

const formRef = ref(null)
// 正则表达式
const regExp = {
    required: /[\S]+/,
    number: /^\d{0,10}$/,
    digit: /^\d{0,10}(.?\d{0,2})$/,
    special: /^\d{0,10}(.?\d{0,3})$/
}
// 表单验证规则
const formRules = reactive<FormRules>({
    continue_sign: [
        { required: true, message: t('continueSignPlaceholder'), trigger: 'blur' },
        {
            validator: (rule: any, value: any, callback: any) => {
                if (isNaN(value) || !regExp.number.test(value)) {
                    callback(t('continueSignFormatError'))
                } else if (value < 2 || value > 365) {
                    callback(t('continueSignBerweenDays'))
                } else if (Number(value) > Number(props.sign_period)) {
                    callback(t('continueSignMustLessThanSignPeriod')) // 添加这个校验
                } else {
                    callback()
                }
            },
            trigger: 'blur'
        }
    ],
    receive_num: [
        { required: true, message: t('receiveNumPlaceholder'), trigger: 'blur' },
        {
            validator: (rule: any, value: any, callback: Function) => {
                if (formData.value.receive_limit == 2) {
                    if (Test.empty(formData.value.receive_num)) {
                        callback(t('receiveNumPlaceholder'))
                    }
                    if (isNaN(formData.value.receive_num) || !regExp.number.test(formData.value.receive_num)) {
                        callback(t('receiveNumFormatError'))
                    }
                    if (formData.value.receive_num <= 0) {
                        callback(t('receiveNumMustGreaterThanZeroTip'))
                    }
                    callback()
                } else {
                    callback()
                }
            }
        }
    ]
})

const verify = async () => {
    let verify = true
    await formRef.value?.validate((valid) => {
        verify = valid
    })

    if (!verify) return verify

    for (let i = 0; i < giftRefs.value.length; i++) {
        const item = giftRefs.value[i]
        !await item.verify() && (verify = false)
    }
    return verify
}

const radioChange = (val, key) => {
    formData[key] = val
}

defineExpose({
    verify
})
</script>

<style lang="scss" scoped>
.input-width {
    width: 100px;
}
</style>

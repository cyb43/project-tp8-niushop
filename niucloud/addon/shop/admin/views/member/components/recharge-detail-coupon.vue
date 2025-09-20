<template>
    <el-form ref="formRef" label-width="120px" :model="formData" label-position="left">
        <el-form-item class="mt-[15px]" :label="t('coupon')" >
            <div class="coupon_list">
                <el-table :data="formData.value " size="large" max-height="600" >
                    <el-table-column prop="title" :label="t('name')" min-width="120">
                        <template #default="{ row }">
                            <div>{{ row.title }}</div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="type_name" :label="t('type')" min-width="120">
                        <template #default="{ row }">
                            <div>{{ row.type_name }}</div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="price" :label="t('couponPrice')" min-width="120">
                        <template #default="{ row }">
                            <div>￥{{ row.price }}</div>
                        </template>
                    </el-table-column>
                    <el-table-column  :label="t('useThreshold')" min-width="130" >
                        <template #default="{ row }">
                            <span v-if="row.min_condition_money == '0.00'">无门槛</span>
                            <span v-else >满{{ row.min_condition_money }}元可用</span>
                        </template>
                    </el-table-column>
                    <el-table-column  :label="t('termOfValidity')" min-width="210">
                        <template #default="{ row }">
                            <span v-if="row.valid_type == 1">  领取之日起{{ row.length || '' }} 天内有效</span>
                            <span v-else> 使用截止时间至{{ row.valid_end_time || ''}} </span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="num" :label="t('num')" min-width="120">
                        <template #default="{ row }">
                            <div>{{ row.num }}</div>
                        </template>
                    </el-table-column>
                </el-table>
            </div>

        </el-form-item>
    </el-form>
</template>

<script lang="ts" setup>
import { computed, ref, watch } from 'vue'
import { t } from '@/lang'

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
    coupon_id: [],
    value: []
})

const formRef = ref(null)

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
</script>

<style lang="scss" scoped>
.coupon_list :deep(.cell) {
    // min-height: 35px !important;
    overflow: initial !important;
}
.coupon_list :deep(.el-form-item__error ) {
    padding-top: 6px;
    left: 2px;
}
</style>

<template>
    <el-form ref="formRef" :model="formData" :rules="formRules">
        <el-form-item label="" class="!mb-0">
            <div class="flex items-center">
                <el-checkbox v-model="formData.is_use" label="" :true-label="1" :false-label="0" size="large" class="!mr-0"/>
                <span class="ml-[10px] el-form-item__label">送优惠券</span>
            </div>
        </el-form-item>
        <el-form-item label="" prop="coupon_id" v-show="formData.is_use">
            <div class="flex-1 max-w-[1000px]">
                <div>
                    <coupon-select-popup v-model="formData.coupon_id" ref="selectCouponRef">
                        <el-button type="primary" link>选择优惠券</el-button>
                    </coupon-select-popup>
                </div>
                <div v-if="couponList.length" class="mt-[10px] w-[100%]">
                    <el-table :data="couponList" size="default" ref="couponListTableRef" max-height="400">
                        <el-table-column prop="title" label="名称" min-width="130" />
                        <el-table-column prop="type_name" label="类型" min-width="130" />
                        <el-table-column prop="price" label="面值" min-width="130" >
                            <template #default="{ row }">
                                <span >¥{{row.price}}</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="使用门槛" min-width="130" >
                            <template #default="{ row }">
                                <span v-if="row.min_condition_money == '0.00'">无门槛</span>
                                <span v-else >满{{ row.min_condition_money }}元可用</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="有效期" min-width="210">
                            <template #default="{ row }">
                                <template v-if="row.receive_type == 1">
                                    <span v-if="row.valid_type == 1">  领取之日起{{ row.length || '' }} 天内有效</span>
                                    <span v-else> 使用截止时间至{{ row.valid_end_time || ''}} </span>
                                </template>
                                <span v-else>--</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="赠券数" min-width="130">
                            <template #default="{ row }">
                                <el-input-number
                                    v-model="formData.coupon_list['id_' + row.id]"
                                    :min="1"
                                    :precision="0"
                                    :max="row.limit_count"
                                    controls-position="right"
                                    class="!w-[100px]"
                                />
                            </template>
                        </el-table-column>
                        <el-table-column :label="t('operation')" align="right" fixed="right">
                            <template #default="{ row }">
                                <el-button type="primary" link @click="deleteCoupon(row)">{{ t('delete') }}</el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
            </div>
        </el-form-item>
    </el-form>
</template>

<script lang="ts" setup>
import { computed, reactive, ref, watch } from 'vue'
import couponSelectPopup from '@/addon/shop/views/goods/components/coupon-select-popup.vue'
import { getSelectedCouponList } from '@/addon/shop/api/marketing'
import { t } from '@/lang'
import { FormRules } from 'element-plus'

const props = defineProps({
    modelValue: {
        type: Object,
        default: () => {
            return {}
        }
    }
})
const selectCouponRef = ref(null)
const emits = defineEmits(['update:modelValue'])
const formData = ref({
    is_use: 0,
    coupon_id: [],
    coupon_list: {}
})
const formRef = ref(null)

const formRules = reactive<FormRules>({
    coupon_id: [
        {
            validator: (rule: any, value: any, callback: any) => {
                if (formData.value.is_use) {
                    if (!formData.value.coupon_id.length) {
                        callback('请选择优惠券')
                    }
                    formData.value.coupon_id.forEach(id => {
                        if (!formData.value.coupon_list['id_' + id]) {
                            callback('请输入赠券数量')
                        }
                    })
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
const couponList = ref([])

watch(() => value.value, (nval, oval) => {
    if ((!oval || !Object.keys(oval).length) && Object.keys(nval).length) {
        formData.value = value.value
    }
}, { immediate: true })

watch(() => formData.value, () => {
    value.value = formData.value
}, { deep: true })

watch(() => formData.value.coupon_id, () => {
    if (formData.value.coupon_id.length) {
        getSelectedCouponList({ coupon_id: formData.value.coupon_id.toString() }).then(({ data }) => {
            couponList.value = data
        })
    } else {
        formData.value.coupon_list = {}
        couponList.value = []
    }
}, { deep: true, immediate: true })

const verify = async () => {
    let verify = true
    await formRef.value?.validate((valid) => {
        verify = valid
    })
    return verify
}

const showCouponDialog = () => {
    selectCouponRef.value.showDialog()
}

const deleteCoupon = (row: any) => {
    delete formData.value.coupon_list['id_' + row.id]
    formData.value.coupon_id.splice(formData.value.coupon_id.indexOf(row.id), 1)
}

defineExpose({
    verify
})
</script>

<style lang="scss" scoped>
</style>

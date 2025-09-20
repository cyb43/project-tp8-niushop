<template>
    <el-dialog v-model="showDialog" :title="popTitle" width="500px" :destroy-on-close="true">
        <el-form :model="formData" label-width="150px" ref="formRef" class="page-form" v-loading="loading">
            <el-form-item :label="t('exportOrderType')">
                <el-radio-group v-model="formData.type">
                    <el-radio label="shop_order">{{ t('shopOrder') }}</el-radio>
                    <el-radio label="shop_order_goods">{{ t('shopOrderGoods') }}</el-radio>
                </el-radio-group>
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
import { ref, reactive } from 'vue'
import { t } from '@/lang'
import type { FormInstance } from 'element-plus'

const showDialog = ref(false)
const loading = ref(false)
const popTitle: string = ''

/**
 * 表单数据
 */
const initialFormData = {
    type: 'shop_order'
}
const formData: Record<string, any> = reactive({ ...initialFormData })

const formRef = ref<FormInstance>()

const emit = defineEmits(['complete'])

/**
 * 确认
 * @param formEl
 */
const confirm = async (formEl: FormInstance | undefined) => {
    if (loading.value || !formEl) return
    await formEl.validate(async (valid) => {
        if (valid) {
            emit('complete', formData.type)
            showDialog.value = false
        }
    })
}

defineExpose({
    showDialog
})
</script>

<style lang="scss" scoped></style>

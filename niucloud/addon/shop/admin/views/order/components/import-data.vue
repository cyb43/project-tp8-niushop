<template>
    <el-dialog v-model="importDialog" :title="t('importData')" width="420px">
        <div>
            <el-form :model="importData" ref="importDataFormRef" :rules="formRules" label-width="80px" label-position="left" >
                <el-form-item :label="t('templateType')" prop="type">
                    <el-radio-group v-model="importData.type">
                        <el-radio label="order">{{t('fullOrderDelivery')}}</el-radio>
                        <el-radio label="order_goods">{{t('openOrderDelivery')}}</el-radio>
                    </el-radio-group>
                    <div @click="examineTemplate" class="form-tip mt-[5px] cursor-pointer !text-[var(--el-color-primary)]" v-show="importData.type == 'order'">{{t('orderTemplate')}}</div>
                    <div @click="examineTemplate" class="form-tip mt-[5px] !text-[var(--el-color-primary)] cursor-pointer" v-show="importData.type == 'order_goods'">{{ t('orderGoodsTemplate') }}</div>
                </el-form-item>

                <el-form-item :label="t('uploadFile')" prop="path">
                    <upload-file v-model="importData.path" api="sys/document/excel" />
                </el-form-item>
            </el-form>
        </div>
        <template #footer>
            <span class="dialog-footer">
                <el-button @click="importDialog = false">{{ t('cancel') }}</el-button>
                <el-button type="primary" :loading="refundDialogLoading" @click="refundDialogConfirm(importDataFormRef)">{{ t('confirm') }}</el-button>
            </span>
        </template>
    </el-dialog>
</template>

<script lang="ts" setup>
import { ref, computed } from 'vue'
import { t } from '@/lang'
import { FormInstance } from 'element-plus'
import { addBatchOrderDelivery } from '@/addon/shop/api/order'

const importDialog = ref(false)

/**
 * 表单数据
 */
const importData = ref({
    type: 'order',
    path: ''
})

// 重置数据
const initDataFn = () => {
    importData.value.type = 'order'
    importData.value.path = ''
    refundDialogLoading.value = false
}

// 打开弹窗
const open = () => {
    initDataFn()
    importDialog.value = true
}
const refundDialogLoading = ref(false)

const emit = defineEmits(['complete'])
const refundDialogConfirm = async (formEl: FormInstance | undefined) => {
    if (refundDialogLoading.value || !formEl) return
    await formEl.validate(async (valid) => {
        if (valid) {
            refundDialogLoading.value = true
            const data = { data: importData.value }
            addBatchOrderDelivery(data).then((res) => {
                emit('complete')
                importDialog.value = false
            }).catch(() => {
                refundDialogLoading.value = false
            })
        }
    })
}
const importDataFormRef = ref<FormInstance>()

const examineTemplate = () => {
    let url = `${import.meta.env.VITE_IMG_DOMAIN || location.origin}/addon/shop/batch/batch_delivery_order.xls`
    if (importData.value.type == 'order_goods') {
        url = `${import.meta.env.VITE_IMG_DOMAIN || location.origin}/addon/shop/batch/batch_delivery_order_goods.xls`
    }
    window.open(url)
}

// 表单验证规则
const formRules = computed(() => {
    return {
        path: [{ required: true, message: t('uploadFilePlaceholder'), trigger: 'blur' }]
    }
})
defineExpose({
    open
})
</script>

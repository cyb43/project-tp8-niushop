<template>
    <el-drawer v-model="showDialog" title="退款详情" direction="rtl" :before-close="handleClose" size="1300px">
        <div class="main-container" v-loading="loading">
            <div class="relative" v-if="formData">
                <div class="flex mb-[20px] justify-between text-[15px]">
                    <span>{{ t('refundMoney') }}：<span>￥{{ formData.money }}</span></span>
                    <span>{{ t('refundNo') }}：<span>{{ formData.refund_no }}</span></span>
                </div>

                <el-table :data="refundList" size="large">
                    <el-table-column prop="out_trade_no" :label="t('outTradeNo')" min-width="200" />
                    <el-table-column prop="create_time" :label="t('createTime')" min-width="160" />
                    <el-table-column prop="refund_type_name" :label="t('refundTypeName')" min-width="120" />
                    <el-table-column :label="t('refundMoney')" min-width="120">
                        <template #default="{ row }">
                            <span>￥{{ row.money }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="status_name" :label="t('statusName')" min-width="120" />
                    <el-table-column :label="t('operation')" fixed="right" align="right" min-width="120">
                        <template #default="{ row }">
                            <el-button type="primary" v-if="row.status == 'wait'" link @click="transferEvent(row)">{{ t('transfer') }}</el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </div>

            <el-dialog v-model="transferDialog" :title="title" width="500px" class="diy-dialog-wrap" :destroy-on-close="true">
                <el-form :model="transferFormData" label-width="120px" ref="formRef" :rules="formRules" class="page-form" v-loading="loading">
                    <el-form-item :label="t('transferType')">
                        <el-radio-group v-model="transferFormData.refund_type">
                            <el-radio :label="item.value" v-for="(item, index) in refundTypeData" :key="index">{{ item.name }}</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item :label="t('refundMoney')">
                        <span>{{ transferFormData.refund_money }}</span>
                    </el-form-item>
                    <el-form-item :label="t('voucher')" v-if="transferFormData.refund_type == 'offline'">
                        <upload-image v-model="transferFormData.voucher" />
                    </el-form-item>
                </el-form>

                <template #footer>
                    <span class="dialog-footer">
                        <el-button @click="transferDialog = false">{{ t('cancel') }}</el-button>
                        <el-button type="primary" :loading="loading" @click="confirm(formRef)">{{ t('confirm') }}</el-button>
                    </span>
                </template>
            </el-dialog>
        </div>
    </el-drawer>
</template>

<script lang="ts" setup>
import { reactive, ref, computed,defineEmits } from 'vue'
import { t } from '@/lang'
import { getPayRefundInfo, getRefundType, getRefundTransfer } from '@/app/api/pay'
import { FormInstance } from 'element-plus'
import { useRouter, useRoute } from 'vue-router'

const route = useRoute()
const router = useRouter()
let popTitle: string = '退款详情';

const showDialog = ref(false)
const loading = ref(false)
let refundNo = '';

const refundList = ref([])
const formData: Record<string, any> = ref(null)

const handleClose = (done: () => void) => {
    showDialog.value = false;
}

/**
 * 表单数据
 */
const formRef = ref<FormInstance>()

const setFormData = async (row: any = null) => {
    refundNo = row.no;
    getRefundListInfo(refundNo)
}

const getRefundListInfo = async (no) => {
    loading.value = true
    formData.value = null
    refundList.value = [];
    await getPayRefundInfo(no).then(({ data }) => {
        formData.value = data
        refundList.value.push(data)
    })
    loading.value = false
}

const refundTypeData = ref([])
getRefundType().then((data) => {
    Object.keys(data.data).forEach((key: string) => {
        refundTypeData.value.push({ value: key, name: data.data[key] })
    })
})

const transferDialog = ref(false)
const transferEvent = (data:any) => {
    transferDialog.value = true
    transferFormData.refund_no = data.refund_no
    transferFormData.refund_money = data.money
    transferFormData.voucher = ''
}

const initialFormData = {
    refund_no: '',
    refund_type: 'back',
    voucher: '',
    refund_money: 0.00
}
const transferFormData: Record<string, any> = reactive({ ...initialFormData })

// 表单验证规则
const formRules = computed(() => {
    return {
        label_name: [
            { required: true, message: t('labelNamePlaceholder'), trigger: 'blur' }
        ]
    }
})
const emit = defineEmits(['loadPayRefundList'])

const confirm = async (formEl: FormInstance | undefined) => {
    if (loading.value || !formEl) return

    await formEl.validate(async (valid) => {
        if (valid) {
            loading.value = true
            const data = transferFormData
            getRefundTransfer(data).then(res => {
                loading.value = false
                transferDialog.value = false
                refundList.value = []
                getRefundListInfo(refundNo)
                emit('loadPayRefundList')
            }).catch(() => {
                transferDialog.value = false
                loading.value = false
            })
        }
    })
}
defineExpose({
    showDialog,
    setFormData
})
</script>
<style lang="scss" scoped></style>

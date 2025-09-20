<template>
    <el-dialog v-model="showDialog" :title="t('detail')" width="800px" :destroy-on-close="true">
        <el-scrollbar height="400px" v-loading="loading" class="invoice-detail-wrap">
            <el-descriptions :column="2">
                <el-descriptions-item :label="t('headerName')" label-align="right">{{ logData.header_name || '--' }}</el-descriptions-item>
                <el-descriptions-item :label="t('headTypeName')" label-align="right">{{ logData.header_type_name || '--' }}</el-descriptions-item>
                <el-descriptions-item :label="t('name')" label-align="right">{{ logData.name || '--' }}</el-descriptions-item>
                <el-descriptions-item :label="t('invoiceNumber')" label-align="right">{{ logData.invoice_number || '--' }}</el-descriptions-item>
                <el-descriptions-item :label="t('typeName')" label-align="right">{{ logData.type_name || '--' }}</el-descriptions-item>
                <el-descriptions-item :label="t('taxNumber')" label-align="right">{{ logData.tax_number || '--'}}</el-descriptions-item>
                <el-descriptions-item :label="t('money')" label-align="right">{{ logData.money || '--' }}</el-descriptions-item>
                <el-descriptions-item :label="t('invoiceTime')" label-align="right">{{ logData.invoice_time === 0 ? '--' : logData.invoice_time }}</el-descriptions-item>
                <el-descriptions-item :label="t('invoiceVoucher')" label-align="right">
                    <span>
                        <img class="w-[50px] max-h-[50px] inline-block" v-if="logData.invoice_voucher" :src="img(logData.invoice_voucher)" alt="" @click="previewImage" >
                    </span>
                </el-descriptions-item>
                <el-descriptions-item :label="t('bankTame')" label-align="right">{{ logData.bank_name || '--' }}</el-descriptions-item>
                <el-descriptions-item :label="t('bankCardNumber')" label-align="right">{{ logData.bank_card_number || '--' }}</el-descriptions-item>
                <el-descriptions-item :label="t('address')" label-align="right">{{ logData.address || '--' }}</el-descriptions-item>
                <el-descriptions-item :label="t('telephone')" label-align="right">{{ logData.telephone || '--' }}</el-descriptions-item>
                <!-- <el-descriptions-item :label="t('email')" label-align="right">{{ logData.email || '--' }}</el-descriptions-item> -->
                <!-- <el-descriptions-item :label="t('mobile')" label-align="right">{{ logData.mobile || '--' }}</el-descriptions-item> -->
                <el-descriptions-item :label="t('createTime')" label-align="right">{{ logData.create_time || '--' }}</el-descriptions-item>
                <el-descriptions-item :label="t('remark')" label-align="right">{{ logData.remark || '--' }}</el-descriptions-item>
            </el-descriptions>
        </el-scrollbar>

        <template #footer>
            <span class="dialog-footer">
                <el-button @click="showDialog = false">{{ t('cancel') }}</el-button>
            </span>
        </template>
    </el-dialog>
    <el-image-viewer :url-list="previewImageList" v-if="imageViewer.show" @close="imageViewer.show = false" :initial-index="imageViewer.index" :zoom-rate="1" />
</template>

<script lang="ts" setup>
import { ref, reactive } from 'vue'
import { t } from '@/lang'
import { getInvoiceDetail } from '@/addon/shop/api/order'
import { img } from '@/utils/common'

const showDialog = ref(false)
const loading = ref(false)
interface LogDataType {
    header_name?: string
    header_type_name?: string
    name?: string
    invoice_number?: string
    type_name?: string
    tax_number?: string
    money?: string
    invoice_time?: any
    invoice_voucher?: string
    bank_name?: string
    bank_card_number?: string
    address?: string
    telephone?: string
    email?: string
    mobile?: string
    create_time?: string
    remark?: string
}
const logData = ref<LogDataType>({})
const getInvoiceDetailList = async () => {
    logData.value = await (await getInvoiceDetail(id)).data
    loading.value = false
    previewImageList.splice(0, previewImageList.length)
    if (logData.value.invoice_voucher) {
        previewImageList.push(img(logData.value.invoice_voucher))
    }
}

let id = 0
const setFormData = async (row: any = null) => {
    loading.value = true
    if (row) {
        id = row.id
        getInvoiceDetailList()
    }
}

const previewImageList: string[] = reactive([])

/**
 * 查看图片
 */
const imageViewer = reactive({
    show: false,
    index: 0
})
const previewImage = () => {
    imageViewer.show = true
}

defineExpose({
    showDialog,
    setFormData
})
</script>

<style lang="scss">
.invoice-detail-wrap{
    .el-descriptions .el-descriptions__body .el-descriptions__table{
        tr{
            display: flex;
        }
        .el-descriptions__cell{
            display: flex;
            flex: 1;
            .el-descriptions__label{
                white-space: nowrap;
            }
            .el-descriptions__content{
                width: 290px;
                line-height: 1.4;
            }
        }
    }
}
</style>

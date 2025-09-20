<template>
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">
            <div class="flex justify-between items-center">
                <span class="text-page-title">{{ pageName }}</span>
            </div>
            <el-card class="box-card !border-none my-[10px] table-search-wrap" shadow="never">
                <el-form :inline="true" :model="invoiceManagementTableData.searchParam" ref="searchFormRef">
                    <el-form-item :label="t('headerName')" prop="header_name">
                        <el-input v-model.trim="invoiceManagementTableData.searchParam.header_name" :placeholder="t('headerNamePlaceholder')" clearable />
                    </el-form-item>
                    <el-form-item :label="t('createTime')" prop="create_time">
                        <el-date-picker v-model="invoiceManagementTableData.searchParam.create_time" type="datetimerange"
                            value-format="YYYY-MM-DD HH:mm:ss" :start-placeholder="t('startDate')"
                            :end-placeholder="t('endDate')" />
                    </el-form-item>
                    <el-form-item :label="t('invoiceTime')" prop="invoice_time">
                        <el-date-picker v-model="invoiceManagementTableData.searchParam.invoice_time" type="datetimerange"
                            value-format="YYYY-MM-DD HH:mm:ss" :start-placeholder="t('startDate')"
                            :end-placeholder="t('endDate')" />
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="loadInvoiceList()">{{ t('search') }}</el-button>
                        <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
                        <el-button type="primary" @click="exportEvent">{{ t('export') }}</el-button>
                    </el-form-item>
                </el-form>
            </el-card>
            <el-tabs v-model="activeName" class="demo-tabs" @tab-change="handleClick">
                <el-tab-pane :label="t('all')" name=""></el-tab-pane>
                <el-tab-pane :label="t('已开票')" name="1"></el-tab-pane>
                <el-tab-pane :label="t('未开票')" name="0"></el-tab-pane>
            </el-tabs>
            <!-- 表格数据 -->
            <div>
                <el-table :data="invoiceManagementTableData.data" size="large" v-loading="invoiceManagementTableData.loading">
                    <template #empty>
                        <span>{{ !invoiceManagementTableData.loading ? t('emptyData') : '' }}</span>
                    </template>
                    <el-table-column prop="header_name" :label="t('headerName')" min-width="100"  />
                    <el-table-column prop="header_type_name" :label="t('headerTypeName')" min-width="120" />
                    <el-table-column prop="tax_number" :label="t('taxNumber')" min-width="180" />
                    <el-table-column prop="name" :label="t('name')" min-width="120" />
                    <el-table-column prop="money" :label="t('money')" min-width="120" align="right" />
                    <el-table-column :label="t('createTime')" min-width="180">
                        <template #default="{ row }">
                            {{ row.create_time || '' }}
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('invoiceTime')" min-width="180">
                        <template #default="{ row }">
                            {{ row.invoice_time || '' }}
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('isInvoice')" min-width="120">
                        <template #default="{ row }">
                            {{ row.is_invoice === 1 ? t('hasInvoice') : t('noInvoice') }}
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('operation')" fixed="right" align="center" width="130">
                        <template #default="{ row }">
                            <el-button type="primary" link @click="detailEvent(row)">{{ t('detail') }}</el-button>
                            <el-button type="primary" link @click="invoiceEvent(row)" v-if="row.is_invoice === 0">{{ t('invoice') }}</el-button>
                            <el-button type="primary" link @click="checkOrder(row)">{{ t('viewOrder') }}</el-button>
                        </template>
                    </el-table-column>

                </el-table>
                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="invoiceManagementTableData.page"
                        v-model:page-size="invoiceManagementTableData.limit"
                        layout="total, sizes, prev, pager, next, jumper" :total="invoiceManagementTableData.total"
                        @size-change="loadInvoiceList()" @current-change="loadInvoiceList" />
                </div>
                <invoice-detail ref="invoiceDetailDialog" @complete="loadInvoiceList()" />
                <invoice-dialog ref="invoiceListDialog" @complete="loadInvoiceList()" />
                <export-sure ref="exportSureDialog" :show="flag" type="shop_invoice" :searchParam="invoiceManagementTableData.searchParam" @close="handleClose" />
            </div>
        </el-card>

    </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import { getInvoiceList } from '@/addon/shop/api/order'
import { FormInstance } from 'element-plus'
import InvoiceDetail from '@/addon/shop/views/order/components/invoice-detail.vue'
import InvoiceDialog from '@/addon/shop/views/order/components/invoice-dialog.vue'
import { useRouter, useRoute } from 'vue-router'
import { setTablePageStorage, getTablePageStorage } from '@/utils/common'

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title

const invoiceManagementTableData = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        is_invoice: '',
        create_time: '',
        invoice_time: '',
        header_name: ''
    }
})

const searchFormRef = ref<FormInstance>()
const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return

    formEl.resetFields()
    loadInvoiceList()
}

const activeName = ref('')

const handleClick = (event: any) => {
    invoiceManagementTableData.searchParam.is_invoice = event
    loadInvoiceList()
}
/**
 * 获取发票列表数据
 */
const loadInvoiceList = (page: number = 1) => {
    invoiceManagementTableData.loading = true
    invoiceManagementTableData.page = page
    getInvoiceList({
        page: invoiceManagementTableData.page,
        limit: invoiceManagementTableData.limit,
        ...invoiceManagementTableData.searchParam
    }).then(res => {
        invoiceManagementTableData.loading = false
        invoiceManagementTableData.data = res.data.data
        invoiceManagementTableData.total = res.data.total
        setTablePageStorage(invoiceManagementTableData.page, invoiceManagementTableData.limit, invoiceManagementTableData.searchParam)
    }).catch(() => {
        invoiceManagementTableData.loading = false
    })
}

loadInvoiceList(getTablePageStorage(invoiceManagementTableData.searchParam).page)

const invoiceDetailDialog: Record<string, any> | null = ref(null)
/**
 * 查看发票详情
 * @param data
 */
const detailEvent = (data: any) => {
    invoiceDetailDialog.value.setFormData(data)
    invoiceDetailDialog.value.showDialog = true
}
/**
 * 开具发票
 * @param data
 */
const invoiceListDialog: Record<string, any> | null = ref(null)
const invoiceEvent = (data: any) => {
    invoiceListDialog.value.setInvoiceData(data)
    invoiceListDialog.value.invoiceDialog = true
}

// 订单详情
const checkOrder = (data: any) => {
    if (data.trade_type === 'shop') {
        const routeUrl = router.resolve({
            path: '/shop/order/detail',
            query: { order_id: data.trade_id }
        })
        window.open(routeUrl.href, '_blank')
    }
}

/**
 * 发票记录导出
 */
const exportSureDialog = ref(null)
const flag = ref(false)
const handleClose = (val) => {
    flag.value = val
}
const exportEvent = (data: any) => {
    flag.value = true
}

</script>

<style lang="scss" scoped></style>

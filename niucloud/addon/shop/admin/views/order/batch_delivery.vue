<template>
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">
            <div class="flex justify-between items-center">
                <span class="text-page-title">{{ pageName }}</span>
            </div>
            <el-card class="box-card !border-none my-[10px] table-search-wrap" shadow="never">
                <el-form :inline="true" :model="importFileTableData.searchParam" ref="searchFormRef">
                    <el-form-item :label="t('createTime')" prop="create_time">
                        <el-date-picker v-model="importFileTableData.searchParam.create_time" type="datetimerange" value-format="YYYY-MM-DD HH:mm:ss" :start-placeholder="t('startTime')" :end-placeholder="t('endTime')" />
                    </el-form-item>
                    <el-form-item :label="t('operationType')" prop='type'>
                        <el-select v-model="importFileTableData.searchParam.type" clearable class="input-item">
                            <el-option v-for="(v,k) in deliveryType" :key="k" :label="v" :value="k"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item :label="t('state')" prop='status'>
                        <el-select v-model="importFileTableData.searchParam.status" clearable class="input-item">
                            <el-option v-for="(v,k) in deliveryState" :key="k" :label="v" :value="k"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item :label="t('operator')" prop='pay_type'>
                        <el-select v-model="importFileTableData.searchParam.main_id" clearable class="input-width" filterable>
                            <el-option v-for="item in allUserList" :key="item.uid" :label="item.username" :value="item.uid">
                                <div class="flex items-center">
                                    <el-avatar :src="img(item.head_img)" size="small" class="mr-[10px]" v-if="item.head_img" />
                                    <img src="@/app/assets/images/member_head.png" alt="" class="mr-[10px] w-[24px]" v-else>
                                    {{ item.username }}
                                </div>
                            </el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="loadInvoiceList()">{{ t('search') }}</el-button>
                        <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
                        <el-button type="primary" @click="importEvent">{{ t('importData') }}</el-button>
                    </el-form-item>
                </el-form>
            </el-card>
            <!-- 表格数据 -->
            <div>
                <el-table :data="importFileTableData.data" size="large" v-loading="importFileTableData.loading">
                    <template #empty>
                        <span>{{ !importFileTableData.loading ? t('emptyData') : '' }}</span>
                    </template>
                    <el-table-column prop="type_name" :label="t('operationType')" min-width="100" />
                    <el-table-column :label="t('operator')" min-width="100">
                        <template #default="{ row }">
                            <div class="flex items-center">
                                <span>{{ row?.user?.username }}</span>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="total_num" :label="t('totalNum')" min-width="120" />
                    <el-table-column prop="success_num" :label="t('succeedNum')" min-width="120" />
                    <el-table-column prop="fail_num" :label="t('failuresNum')" min-width="120" />
                    <el-table-column prop="status_name" :label="t('state')" min-width="100" />
                    <el-table-column :label="t('operationTime')" min-width="150">
                        <template #default="{ row }">
                            {{ row.create_time || '' }}
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('operation')" fixed="right" align="right" min-width="180">
                        <template #default="{ row }">
                            <el-button type="primary" link @click="examineDownloadFn(row.output)" v-if="row.status == 2 && row.output">{{ t('downloadRecord') }}</el-button>
                            <el-button type="primary" link @click="examineDownloadFn(row.fail_output)" v-if="row.status == 2 && row.fail_output">{{ t('causeFailure') }}</el-button>
                            <el-button type="primary" link @click="checkCauseFn(row)" v-if="row.status == 3">{{ t('checkCause') }}</el-button>
                        </template>
                    </el-table-column>
                </el-table>
                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="importFileTableData.page"
                                   v-model:page-size="importFileTableData.limit"
                                   layout="total, sizes, prev, pager, next, jumper" :total="importFileTableData.total"
                                   @size-change="loadInvoiceList()" @current-change="loadInvoiceList" />
                </div>
                <import-data ref="importDataDialog" @complete="loadInvoiceList()" />

                <!-- 失败原因弹窗 -->
                <el-dialog v-model="causeFailureDialog" :title="t('checkCause1')" width="420px">
                    <div class="break-all -mt-[20px]">{{ causeFailureContent }}</div>
                    <template #footer>
                        <span class="dialog-footer">
                            <el-button @click="causeFailureDialog = false">{{ t('confirm') }}</el-button>
                        </span>
                    </template>
                </el-dialog>
            </div>
        </el-card>

    </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import {
    getOrderBatchDeliveryList,
    getOrderBatchDeliveryType,
    getOrderBatchDeliveryState
} from '@/addon/shop/api/order'
import { FormInstance } from 'element-plus'
import { getAllUserList } from '@/app/api/user'
import importData from '@/addon/shop/views/order/components/import-data.vue'
import { useRoute } from 'vue-router'
import { img } from '@/utils/common'

const route = useRoute()
const pageName = route.meta.title

const deliveryType = ref([])
const deliveryState = ref([])
const allUserList = ref<any[]>([])
const setFormData = async() => {
    deliveryType.value = await (await getOrderBatchDeliveryType()).data
    deliveryState.value = await (await getOrderBatchDeliveryState()).data
    allUserList.value = await (await getAllUserList({})).data
}
setFormData()

const importFileTableData = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        create_time: '',
        main_id: '',
        type: '',
        status: ''
    }
})

const searchFormRef = ref<FormInstance>()
const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    importFileTableData.searchParam.main_id = ''
    loadInvoiceList()
}

/**
 * 获取批发列表数据
 */
const loadInvoiceList = (page: number = 1) => {
    importFileTableData.loading = true
    importFileTableData.page = page
    getOrderBatchDeliveryList({
        page: importFileTableData.page,
        limit: importFileTableData.limit,
        ...importFileTableData.searchParam
    }).then(res => {
        importFileTableData.loading = false
        importFileTableData.data = res.data.data
        importFileTableData.total = res.data.total
    }).catch(() => {
        importFileTableData.loading = false
    })
}
loadInvoiceList()

// 下载
const examineDownloadFn = (path: any) => {
    const url = `${ import.meta.env.VITE_IMG_DOMAIN || location.origin }/${ path }`
    window.open(url)
}

/**
 * 批量发货
 * @param data
 */
const importDataDialog: Record<string, any> | null = ref(null)
const importEvent = (data: any) => {
    importDataDialog.value.open()
}

const causeFailureDialog = ref(false)
const causeFailureContent = ref('') // 失败原因内容
// 查看失败原因
const checkCauseFn = (data: any) => {
    causeFailureContent.value = data.fail_remark
    causeFailureDialog.value = true
}
</script>

<style lang="scss" scoped></style>

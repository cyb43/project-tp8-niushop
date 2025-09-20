<template>
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">

            <div class="flex justify-between items-center mb-[5px]">
                <span class="text-lg">{{pageName}}</span>
                <el-button type="primary" @click="addEvent">{{ t('addPrinter') }}</el-button>
            </div>

            <el-tabs class="demo-tabs" model-value="/printer/list" @tab-change="handleClick">
                <el-tab-pane :label="t('tabPrinterManager')" name="/printer/list" />
                <el-tab-pane :label="t('tabPrinterTemplate')" name="/printer/template/list" />
            </el-tabs>

            <el-card class="box-card !border-none my-[10px] table-search-wrap" shadow="never">
                <el-form :inline="true" :model="printerTable.searchParam" ref="searchFormRef">
                    <el-form-item :label="t('printerName')" prop="printer_name">
                        <el-input v-model.trim="printerTable.searchParam.printer_name" :placeholder="t('printerNamePlaceholder')" maxlength="20" />
                    </el-form-item>

                    <el-form-item>
                        <el-button type="primary" @click="loadPrinterList()">{{ t('search') }}</el-button>
                        <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
                    </el-form-item>
                </el-form>
            </el-card>

            <div class="mt-[10px]">
                <el-table :data="printerTable.data" size="large" v-loading="printerTable.loading">
                    <template #empty>
                        <span>{{ !printerTable.loading ? t('emptyData') : '' }}</span>
                    </template>

                    <el-table-column prop="printer_name" :label="t('printerName')" min-width="220" :show-overflow-tooltip="true"/>

                    <el-table-column prop="brand_name" :label="t('brand')" min-width="120" :show-overflow-tooltip="true"/>

                    <el-table-column prop="print_width" :label="t('printWidth')" min-width="120" :show-overflow-tooltip="true"/>

                    <el-table-column prop="status" :label="t('status')" min-width="80" :show-overflow-tooltip="true" >
                        <template #default="{ row }">
                            <el-tag type="success" v-if="row.status == 1" @click="modifyPrinterStatusEvent(row.printer_id, 0)" class="cursor-pointer">{{ t('statusOn') }}</el-tag>
                            <el-tag type="info" v-else @click="modifyPrinterStatusEvent(row.printer_id, 1)" class="cursor-pointer">{{ t('statusOff') }}</el-tag>
                        </template>
                    </el-table-column>

                    <el-table-column :label="t('operation')" fixed="right" min-width="120">
                       <template #default="{ row }">
                           <el-button type="primary" link @click="testPrintEvent(row.printer_id)">{{ t('testPrint') }}</el-button>
                           <el-button type="primary" link @click="refreshTokenEvent(row.printer_id)">{{ t('refreshToken') }}</el-button>
                           <el-button type="primary" link @click="editEvent(row)">{{ t('edit') }}</el-button>
                           <el-button type="primary" link @click="deleteEvent(row.printer_id)">{{ t('delete') }}</el-button>
                       </template>
                    </el-table-column>

                </el-table>
                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="printerTable.page" v-model:page-size="printerTable.limit"
                        layout="total, sizes, prev, pager, next, jumper" :total="printerTable.total"
                        @size-change="loadPrinterList()" @current-change="loadPrinterList" />
                </div>
            </div>

        </el-card>
    </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import { getPrinterPageList, modifyPrinterStatus, deletePrinter, refreshPrinterToken, testPrint } from '@/app/api/printer'
import { ElMessageBox, FormInstance } from 'element-plus'
import { useRoute, useRouter } from 'vue-router'
import { setTablePageStorage, getTablePageStorage } from '@/utils/common'

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title
const repeat = ref(false)

const handleClick = (path: string) => {
    router.push({ path })
}

const printerTable = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        printer_name: ''
    }
})

const searchFormRef = ref<FormInstance>()

/**
 * 获取小票打印机列表
 */
const loadPrinterList = (page: number = 1) => {
    printerTable.loading = true
    printerTable.page = page

    getPrinterPageList({
        page: printerTable.page,
        limit: printerTable.limit,
        ...printerTable.searchParam
    }).then(res => {
        printerTable.loading = false
        printerTable.data = res.data.data
        printerTable.total = res.data.total
        setTablePageStorage(printerTable.page, printerTable.limit, printerTable.searchParam)
    }).catch(() => {
        printerTable.loading = false
    })
}

loadPrinterList(getTablePageStorage(printerTable.searchParam).page)

const isRepeat = ref(false)

// 修改小票打印机状态
const modifyPrinterStatusEvent = (printer_id: any, status: any) => {
    if (isRepeat.value) return
    isRepeat.value = true

    modifyPrinterStatus({
        printer_id,
        status
    }).then((res) => {
        loadPrinterList()
        isRepeat.value = false
    })
}

/**
 * 添加小票打印机
 */
const addEvent = () => {
    router.push('/printer/add')
}

/**
 * 编辑小票打印机
 * @param data
 */
const editEvent = (data: any) => {
    router.push('/printer/edit?printer_id=' + data.printer_id)
}

/**
 * 删除小票打印机
 */
const deleteEvent = (id: number) => {
    ElMessageBox.confirm(t('printerDeleteTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        if (repeat.value) return
        repeat.value = true
        deletePrinter(id).then(() => {
            loadPrinterList()
            repeat.value = false
        }).catch(() => {
            repeat.value = false
        })
    })
}

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    loadPrinterList()
}

/**
 * 测试打印
 * @param printer_id
 */
const testPrintEvent = (printer_id: any) => {
    ElMessageBox.confirm(t('testPrintTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        if (repeat.value) return
        repeat.value = true
        testPrint(printer_id).then((res: any) => {
            repeat.value = false
        }).catch(() => {
            repeat.value = false
        })
    })
}

/**
 * 授权（重新获取token）
 * @param printer_id
 */
const refreshTokenEvent = (printer_id: any) => {
    ElMessageBox.confirm(t('refreshTokenTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        if (repeat.value) return
        repeat.value = true
        refreshPrinterToken(printer_id).then((res: any) => {
            loadPrinterList()
            repeat.value = false
        }).catch(() => {
            repeat.value = false
        })
    })
}
</script>

<style lang="scss" scoped>
</style>

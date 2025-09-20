<template>
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">

            <div class="flex justify-between items-center">
                <span class="text-page-title">{{ pageName }}</span>
            </div>

            <el-card class="box-card !border-none my-[10px] table-search-wrap" shadow="never">
                <el-form :inline="true" :model="exportTableData.searchParam" ref="searchFormRef">
                    <el-form-item :label="t('exportKey')" prop="export_key">
                        <el-select v-model="exportTableData.searchParam.export_key" clearable :placeholder="t('exportKeyPlaceholder')" class="input-width">
                            <el-option :label="t('selectPlaceholder')" value="" />
                            <el-option :label="item" :value="key" v-for="(item, key) in exportKeyList" :key="key" />
                        </el-select>
                    </el-form-item>
                    <el-form-item :label="t('exportStatus')" prop="export_status">
                        <el-select v-model="exportTableData.searchParam.export_status" clearable :placeholder="t('exportStatusPlaceholder')" class="input-width">
                            <el-option :label="t('selectPlaceholder')" value="" />
                            <el-option :label="item" :value="key" v-for="(item, key) in exportStatusList" :key="key" />
                        </el-select>
                    </el-form-item>
                    <el-form-item :label="t('createTime')" prop="create_time">
                        <el-date-picker v-model="exportTableData.searchParam.create_time" type="datetimerange"
                            value-format="YYYY-MM-DD HH:mm:ss" :start-placeholder="t('startDate')"
                            :end-placeholder="t('endDate')" />
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="loadExportList()">{{ t('search') }}</el-button>
                        <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
                    </el-form-item>
                </el-form>
            </el-card>

            <div class="mt-[10px]">
                <el-table :data="exportTableData.data" size="large" v-loading="exportTableData.loading">
                    <template #empty>
                        <span>{{ !exportTableData.loading ? t('emptyData') : '' }}</span>
                    </template>
                    <el-table-column prop="id" :label="t('id')" min-width="120" />
                    <el-table-column prop="export_key_name" :label="t('exportKey')" min-width="120" />
                    <el-table-column prop="export_num" :label="t('exportNum')" min-width="120" />
                    <el-table-column prop="file_path" :label="t('filePath')" min-width="180" :show-overflow-tooltip="true" />
                    <el-table-column prop="file_size" :label="t('fileSize')" min-width="110">
                        <template #default="{ row }">
                            {{ row.file_size / 1000 }}k
                        </template>
                    </el-table-column>
                    <el-table-column prop="export_status" :label="t('exportStatus')" min-width="120" align="center">
                        <template #default="{ row }">
                            <el-tag type="warning" v-if="row.export_status == 1" class="cursor-pointer">{{ row.export_status_name }}</el-tag>
                            <el-tag type="success" v-else-if="row.export_status == 2" class="cursor-pointer">{{ row.export_status_name }}</el-tag>
                            <el-tag type="error" v-else class="cursor-pointer">{{ row.export_status_name }}</el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('createTime')" min-width="150" align="center">
                        <template #default="{ row }">
                            {{ row.create_time || '' }}
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('operation')" align="right" fixed="right" width="100">
                        <template #default="{ row }">
                            <div class="flex justify-end">
                                <el-button type="primary" link @click="downloadEvent(row)" v-if="row.export_status == 2">{{ t('download') }}</el-button>
                                <el-button type="primary" link @click="deleteEvent(row.id)">{{ t('delete') }}</el-button>
                            </div>
                        </template>
                    </el-table-column>
                </el-table>
                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="exportTableData.page" v-model:page-size="exportTableData.limit"
                        layout="total, sizes, prev, pager, next, jumper" :total="exportTableData.total"
                        @size-change="loadExportList()" @current-change="loadExportList" />
                </div>
            </div>
        </el-card>
    </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import { img } from '@/utils/common'
import { getExportStatusList, getExportKeyList, getExportList, deleteExport } from '@/app/api/sys'
import { ElMessageBox, FormInstance } from 'element-plus'
import { useRoute } from 'vue-router'

const route = useRoute()
const pageName = route.meta.title
const exportTableData = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        export_key: '',
        export_status: '',
        create_time: []
    }
})

const searchFormRef = ref<FormInstance>()

// 获取导出状态列表
const exportStatusList = ref([])
const setExportStatusList = async () => {
    exportStatusList.value = await (await getExportStatusList({})).data
}
setExportStatusList()

// 获取导出关键字列表
const exportKeyList = ref([])
const setExportKeyList = async () => {
    exportKeyList.value = await (await getExportKeyList({})).data
}
setExportKeyList()

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    loadExportList()
}

// 获取导出列表
const loadExportList = (page: number = 1) => {
    exportTableData.loading = true
    exportTableData.page = page

    getExportList({
        page: exportTableData.page,
        limit: exportTableData.limit,
        ...exportTableData.searchParam
    }).then(res => {
        exportTableData.loading = false
        exportTableData.data = res.data.data
        exportTableData.total = res.data.total
    }).catch(() => {
        exportTableData.loading = false
    })
}
loadExportList()

/**
 * 下载导出报表
 */
const downloadEvent = (data: any) => {
    const url = img(data.file_path);
    const suffix = url.substring(url.lastIndexOf("."), url.length);
    const a = document.createElement('a')
    a.setAttribute('download', url)
    a.setAttribute('target', '_blank')
    a.setAttribute('href', url)
    a.click()
}
/**
 * 删除导出报表
 */
const deleteEvent = (id: number) => {
    ElMessageBox.confirm(t('exportDeleteTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        deleteExport(id).then(() => {
            loadExportList()
        })
    })
}
</script>

<style lang="scss" scoped></style>

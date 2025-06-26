
<template>
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">

            <div class="flex justify-between items-center">
                <span class="text-page-title">{{ pageName }}</span>
            </div>

            <el-card class="box-card !border-none my-[10px] table-search-wrap" shadow="never">
                <el-form :inline="true" :model="tableData.searchParam" ref="searchFormRef">
                    <el-form-item :label="t('upgradeName')" prop="name">
                        <el-input v-model.trim="tableData.searchParam.name" :placeholder="t('upgradeNamePlaceholder')" />
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="loadList()">{{ t('search') }}</el-button>
                        <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
                    </el-form-item>
                </el-form>
            </el-card>

            <el-table :data="tableData.data" size="large" v-loading="tableData.loading" ref="tableRef" @selection-change="handleSelectionChange">

                <template #empty>
                    <span>{{ !tableData.loading ? t('emptyData') : '' }}</span>
                </template>

                <el-table-column type="selection" width="55" />
                <el-table-column prop="id" :label="t('id')" width="140" />
                <el-table-column prop="name" :label="t('upgradeName')" >
                    <template #default="{ row }">
                        <div v-if="!row.content || typeof row.content == 'string'">
                            【{{ row.name }}】从{{ row.prev_version }}升级到{{ row.current_version }}
                        </div>
                        <div v-else>
                            <div v-for="item in row.content.content">【{{ item.app.app_name }}】从{{ item.version }}升级到{{ item.upgrade_version }}</div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="create_time" :label="t('completeTime')" width="220px" />
                <el-table-column prop="status_name" :label="t('status')" width="120px"/>
                <el-table-column :label="t('operation')" align="right" width="160px">
                    <template #default="{ row }">
                        <el-button type="primary" link v-if="row.status == 'fail'" @click="handleFailReason(row)">{{ t('failReason') }}</el-button>
                        <el-button type="primary" link @click="deleteEvent(row.id)">{{ t('delete') }}</el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div class="mt-[10px] flex items-center">
                <el-button @click="batchDelete" size="small">{{ t('batchDelete') }}</el-button>
            </div>
            <div class="mt-[16px] flex justify-end">
                <el-pagination v-model:current-page="tableData.page"
                    v-model:page-size="tableData.limit" layout="total, sizes, prev, pager, next, jumper"
                    :total="tableData.total" @size-change="loadList()"
                    @current-change="loadList" />
            </div>
        </el-card>

    </div>

    <el-dialog v-model="failReasonDialogVisible" :title="t('failReason')" width="60%">
        <el-scrollbar class="h-[60vh] w-full whitespace-pre-wrap p-[20px]">
            <div v-html="failReason"></div>
        </el-scrollbar>
    </el-dialog>
</template>

<script lang="ts" setup>
import { ref, reactive } from 'vue'
import { t } from '@/lang'
import {ElMessage, ElMessageBox, FormInstance} from 'element-plus'
import { useRoute } from 'vue-router'
import { getUpgradeRecords, delUpgradeRecords } from '@/app/api/upgrade'

const route = useRoute()
const pageName = route.meta.title
const searchFormRef = ref<FormInstance>()
const tableRef = ref()
const tableData: any = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        name: ''
    }
})

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    loadList()
}

/**
 * 获取列表
 */
const loadList = (page: number = 1) => {
    tableData.loading = true
    tableData.page = page
    getUpgradeRecords({
        page: tableData.page,
        limit: tableData.limit,
        ...tableData.searchParam
    }).then(res => {
        tableData.loading = false
        tableData.data = res.data.data
        tableData.total = res.data.total
    }).catch(() => {
        tableData.loading = false
    })
}

loadList()

const failReason = ref('')
const failReasonDialogVisible = ref(false)
const handleFailReason = (data: any) => {
    failReason.value = data.fail_reason
    failReasonDialogVisible.value = true
}

let ids = []

const handleSelectionChange = (e: any) => {
    ids = e.map(item => item.id)
}

const batchDelete = () => {
    if (!ids.length) {
        ElMessage({ message: '请先勾选要删除的记录', type: 'error', duration: 5000 })
        return
    }
    deleteEvent(ids)
}

const deleteEvent = (ids: any) => {
    ElMessageBox.confirm(t('deleteTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        delUpgradeRecords({
            ids
        }).then(() => {
            loadList()
        }).catch(() => {
        })
    })
}
</script>

<style lang="scss" scoped>
</style>

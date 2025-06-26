<template>
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">

            <div class="flex justify-between items-center mb-[5px]">
                <span class="text-lg">{{pageName}}</span>
                <el-button type="primary" @click="addEvent">{{ t('addElectronicSheet') }}</el-button>
            </div>

            <el-tabs model-value="/shop/delivery/electronic_sheet" @tab-change="handleClick">
                <el-tab-pane :label="t('tabESTemplate')" name="/shop/delivery/electronic_sheet" />
                <el-tab-pane :label="t('tabESConfig')" name="/shop/delivery/electronic_sheet/config" />
            </el-tabs>

            <el-card class="box-card !border-none my-[10px] table-search-wrap" shadow="never">
                <el-form :inline="true" :model="tableData.searchParam" ref="searchFormRef">
                    <el-form-item :label="t('templateName')" prop="template_name">
                        <el-input v-model.trim="tableData.searchParam.template_name" :placeholder="t('templateNamePlaceholder')" maxlength="30" />
                    </el-form-item>

                    <el-form-item :label="t('expressCompany')" prop="express_company_id">
                        <el-select v-model="tableData.searchParam.express_company_id" :placeholder="t('expressCompanyPlaceholder')" clearable>
                            <el-option v-for="item in companyList" :key="item.company_id" :label="item.company_name" :value="item.company_id" />
                        </el-select>
                    </el-form-item>

                    <el-form-item>
                        <el-button type="primary" @click="loadList()">{{ t('search') }}</el-button>
                        <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
                    </el-form-item>
                </el-form>
            </el-card>

            <div class="mt-[10px]">
                <el-table :data="tableData.data" size="large" v-loading="tableData.loading">
                    <template #empty>
                        <span>{{ !tableData.loading ? t('emptyData') : '' }}</span>
                    </template>
                    <el-table-column :label="t('templateName')" min-width="200" :show-overflow-tooltip="true">
                        <template #default="{ row }">
                            <el-tag size="small" v-if="row.is_default">{{ t('isDefault') }}</el-tag>
                            <span class="ml-[8px]">{{row.template_name}}</span>
                       </template>
                    </el-table-column>

                    <el-table-column prop="express_company_id" :label="t('expressCompany')" min-width="120" :show-overflow-tooltip="true">
                        <template #default="{ row }">
                            <div>{{ row.company.company_name }}</div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="pay_type_name" :label="t('payType')" min-width="80" :show-overflow-tooltip="true"/>

                    <el-table-column prop="status" :label="t('status')" min-width="80" :show-overflow-tooltip="true" >
                        <template #default="{ row }">
                            <div v-if="row.status == 1">{{ t('statusOn') }}</div>
                            <div v-if="row.status == 0">{{ t('statusOff') }}</div>
                        </template>
                    </el-table-column>

                    <el-table-column :label="t('operation')" fixed="right" min-width="80" align="right">
                       <template #default="{ row }">
                           <el-button type="primary" link v-if="!row.is_default" @click="setDefaultEvent(row.id)">{{ t('setDefault') }}</el-button>
                           <el-button type="primary" link @click="editEvent(row)">{{ t('edit') }}</el-button>
                           <el-button type="primary" link v-if="!row.is_default" @click="deleteEvent(row.id)">{{ t('delete') }}</el-button>
                       </template>
                    </el-table-column>

                </el-table>
                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="tableData.page" v-model:page-size="tableData.limit"
                        layout="total, sizes, prev, pager, next, jumper" :total="tableData.total"
                        @size-change="loadList()" @current-change="loadList" />
                </div>
            </div>

        </el-card>
    </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import { getElectronicSheetPageList, deleteElectronicSheet, setDefaultElectronicSheet } from '@/addon/shop/api/electronic_sheet'
import { ElMessageBox,FormInstance } from 'element-plus'
import { useRoute,useRouter } from 'vue-router'
import { getCompanyList } from '@/addon/shop/api/delivery'
import { setTablePageStorage,getTablePageStorage } from "@/utils/common";

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title;

const tableData = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        template_name: "",
        express_company_id: "",
    }
})

const searchFormRef = ref<FormInstance>()

const handleClick = (path: string) => {
    router.push({ path })
}

/**
 * 获取电子面单列表
 */
const loadList = (page: number = 1) => {
    tableData.loading = true
    tableData.page = page

    getElectronicSheetPageList({
        page: tableData.page,
        limit: tableData.limit,
         ...tableData.searchParam
    }).then(res => {
        tableData.loading = false
        tableData.data = res.data.data
        tableData.total = res.data.total
        setTablePageStorage(tableData.page, tableData.limit, tableData.searchParam)
    }).catch(() => {
        tableData.loading = false
    })
}

loadList(getTablePageStorage(tableData.searchParam).page)

const companyList = ref([])

getCompanyList({
    electronic_sheet_switch: 1
}).then((res:any)=> {
    companyList.value = res.data;
})

/**
 * 添加电子面单
 */
const addEvent = () => {
    router.push('/shop/delivery/electronic_sheet_add')
}

/**
 * 编辑电子面单
 * @param data
 */
const editEvent = (data: any) => {
    router.push('/shop/delivery/electronic_sheet_edit?id=' + data.id)
}

/**
 * 删除电子面单
 */
const deleteEvent = (id: number) => {
    ElMessageBox.confirm(t('electronicSheetDeleteTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning',
        }
    ).then(() => {
        deleteElectronicSheet(id).then(() => {
            loadList()
        })
    })
}
/**
 * 设置默认电子面单模版
 */
const setDefaultEvent = (id: number) => {
    ElMessageBox.confirm(t('electronicSheetSetDefaultTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning',
        }
    ).then(() => {
        setDefaultElectronicSheet({id}).then(() => {
            loadList()
        })
    })
}

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    loadList()
}
</script>

<style lang="scss" scoped>
</style>

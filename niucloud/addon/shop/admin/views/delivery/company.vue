<template>
    <div class="main-container">

        <el-card class="box-card !border-none" shadow="never">
            <el-page-header :content="pageName" :icon="ArrowLeft" @back="router.push('/shop/order/delivery')" />
        </el-card>

        <el-card class="box-card mt-[15px] !border-none" shadow="never">
            <el-card class="box-card !border-none my-[10px] table-search-wrap" shadow="never">
                <div class="flex justify-between items-center">
                    <el-form :inline="true" :model="companyTable.searchParam" ref="searchFormRef">
                        <el-form-item :label="t('companyName')" prop="company_name">
                            <el-input v-model.trim="companyTable.searchParam.company_name" :placeholder="t('companyNamePlaceholder')" />
                        </el-form-item>
                        <el-form-item>
                            <el-button type="primary" @click="loadCompanyList()">{{ t('search') }}</el-button>
                            <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
                        </el-form-item>
                    </el-form>
                    <el-button type="primary" @click="addEvent">{{ t('addCompany') }}</el-button>
                </div>
            </el-card>

            <div class="mt-[10px]">
                <el-table :data="companyTable.data" size="large" v-loading="companyTable.loading">
                    <template #empty>
                        <span>{{ !companyTable.loading ? t('emptyData') : '' }}</span>
                    </template>
                    <el-table-column prop="company_name" :label="t('companyName')" min-width="120" />
                    <el-table-column prop="logo" :label="t('logo')" min-width="120">
                        <template #default="{ row }">
                            <div class="w-[50px] h-[50px] flex items-center justify-center">
                                <img v-if="row.logo" class="max-w-[100%] max-h-[100%]" :src="img(row.logo)" />
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="url" :label="t('url')" min-width="120" />
                    <el-table-column prop="kd100_express_no" :label="t('expressNoKd100')" min-width="120" />
                    <el-table-column prop="express_no" :label="t('expressNo')" min-width="120" />
                    <el-table-column prop="express_no_electronic_sheet" :label="t('expressNoElectronicSheet')" min-width="120" />
                    <el-table-column :label="t('electronicSheetSwitchName')" min-width="120">
                        <template #default="{ row }">
                            <span>{{ row.electronic_sheet_switch == 1 ? '支持' : '不支持' }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('operation')" fixed="right" align="right" min-width="120">
                        <template #default="{ row }">
                            <el-button type="primary" link @click="editEvent(row)">{{ t('edit') }}</el-button>
                            <el-button type="primary" link @click="deleteEvent(row.company_id)">{{ t('delete') }}
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>
                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="companyTable.page" v-model:page-size="companyTable.limit"
                                   layout="total, sizes, prev, pager, next, jumper" :total="companyTable.total"
                                   @size-change="loadCompanyList()" @current-change="loadCompanyList" />
                </div>
            </div>
        </el-card>
    </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import { getCompanyPageList, deleteCompany } from '@/addon/shop/api/delivery'
import { img, setTablePageStorage, getTablePageStorage } from '@/utils/common'
import { ElMessageBox, FormInstance } from 'element-plus'
import { ArrowLeft } from '@element-plus/icons-vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title

const companyTable = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        company_name: '',
        logo: '',
        url: '',
        create_time: '',
        modify_time: ''
    }
})

const searchFormRef = ref<FormInstance>()

/**
 * 获取物流公司列表
 */
const loadCompanyList = (page: number = 1) => {
    companyTable.loading = true
    companyTable.page = page

    getCompanyPageList({
        page: companyTable.page,
        limit: companyTable.limit,
        ...companyTable.searchParam
    }).then(res => {
        companyTable.loading = false
        companyTable.data = res.data.data
        companyTable.total = res.data.total
        setTablePageStorage(companyTable.page, companyTable.limit, companyTable.searchParam);
    }).catch(() => {
        companyTable.loading = false
    })
}

loadCompanyList(getTablePageStorage(companyTable.searchParam).page);

/**
 * 添加物流公司
 */
const addEvent = () => {
    router.push('/shop/order/delivery/company_add')
}

/**
 * 编辑物流公司
 * @param data
 */
const editEvent = (data: any) => {
    router.push('/shop/order/delivery/company_edit?company_id=' + data.company_id)
}

/**
 * 删除物流公司
 */
const deleteEvent = (id: number) => {
    ElMessageBox.confirm(t('companyDeleteTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        deleteCompany(id).then(() => {
            loadCompanyList()
        }).catch(() => {
        })
    })
}

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    loadCompanyList()
}
</script>

<style lang="scss" scoped></style>

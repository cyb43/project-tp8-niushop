<template>
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">
            <el-page-header :content="pageName" :icon="ArrowLeft" @back="$router.back()" />
        </el-card>

        <el-card class="box-card mt-[15px] !border-none" shadow="never">
            <el-card class="box-card !border-none my-[10px] pt-0 table-search-wrap" shadow="never">
                <div class="flex justify-between items-center">
                    <el-form :inline="true" :model="templateTable.searchParam" ref="searchFormRef">
                        <el-form-item :label="t('templateName')" prop="template_name">
                            <el-input v-model.trim="templateTable.searchParam.template_name" :placeholder="t('templateNamePlaceholder')" />
                        </el-form-item>
                        <el-form-item>
                            <el-button type="primary" @click="loadTemplateList()">{{ t('search') }}</el-button>
                            <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
                        </el-form-item>
                    </el-form>
                    <el-button type="primary" @click="addEvent">
                        {{ t('addTemplate') }}
                    </el-button>
                </div>
            </el-card>

            <div class="mt-[10px]">
                <el-table :data="templateTable.data" size="large" v-loading="templateTable.loading">
                    <template #empty>
                        <span>{{ !templateTable.loading ? t('emptyData') : '' }}</span>
                    </template>
                    <el-table-column prop="template_name" :label="t('templateName')" min-width="120" />
                    <el-table-column prop="fee_type_name" :label="t('feeTypeName')" min-width="120" />
                    <el-table-column :label="t('freeShipping')" min-width="120" align="center">
                        <template #default="{ row }">
                            {{ row.is_free_shipping ? t('open') : t('close') }}
                        </template>
                    </el-table-column>
                    <el-table-column prop="create_time" :label="t('createTime')" min-width="120" />
                    <el-table-column :label="t('operation')" fixed="right" min-width="120" align="right">
                        <template #default="{ row }">
                            <el-button type="primary" link @click="editEvent(row)">{{ t('edit') }}</el-button>
                            <el-button type="primary" link @click="deleteEvent(row.template_id)">{{ t('delete') }}</el-button>
                        </template>
                    </el-table-column>
                </el-table>
                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="templateTable.page" v-model:page-size="templateTable.limit"
                        layout="total, sizes, prev, pager, next, jumper" :total="templateTable.total"
                        @size-change="loadTemplateList()" @current-change="loadTemplateList" />
                </div>
            </div>
        </el-card>
    </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import { getShippingTemplatePageList, deleteShippingTemplate } from '@/addon/shop/api/delivery'
import { ElMessageBox, FormInstance } from 'element-plus'
import { ArrowLeft } from '@element-plus/icons-vue'
import { useRoute, useRouter } from 'vue-router'
import { setTablePageStorage,getTablePageStorage } from "@/utils/common";

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title

const templateTable = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        template_name: ''
    }
})

const searchFormRef = ref<FormInstance>()

/**
 * 获取运费模板列表
 */
const loadTemplateList = (page: number = 1) => {
    templateTable.loading = true
    templateTable.page = page

    getShippingTemplatePageList({
        page: templateTable.page,
        limit: templateTable.limit,
        ...templateTable.searchParam
    }).then(res => {
        templateTable.loading = false
        templateTable.data = res.data.data
        templateTable.total = res.data.total
        setTablePageStorage(templateTable.page, templateTable.limit, templateTable.searchParam);
    }).catch(() => {
        templateTable.loading = false
    })
}
loadTemplateList(getTablePageStorage(templateTable.searchParam).page);

/**
 * 添加运费模板
 */
const addEvent = () => {
    router.push({ path: '/shop/order/shipping/template_edit' })
}

/**
 * 编辑运费模板
 * @param data
 */
const editEvent = (data: any) => {
    router.push({ path: '/shop/order/shipping/template_edit', query: { id: data.template_id } })
}

/**
 * 删除运费模板
 */
const deleteEvent = (id: number) => {
    ElMessageBox.confirm(t('templateDeleteTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        deleteShippingTemplate(id).then(() => {
            loadTemplateList()
        }).catch(() => {
        })
    })
}

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    loadTemplateList()
}
</script>

<style lang="scss" scoped>
</style>

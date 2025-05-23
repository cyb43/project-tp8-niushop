<template>
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">

            <div class="flex justify-between items-center mb-[5px]">
                <span class="text-lg">{{pageName}}</span>
                <el-button type="primary" @click="addEvent">
                    {{ t('addPrinterTemplate') }}
                </el-button>
            </div>

            <el-tabs class="demo-tabs" model-value="/printer/template/list" @tab-change="handleClick">
                <el-tab-pane :label="t('tabPrinterManager')" name="/printer/list" />
                <el-tab-pane :label="t('tabPrinterTemplate')" name="/printer/template/list" />
            </el-tabs>

            <el-card class="box-card !border-none my-[10px] table-search-wrap" shadow="never">
                <el-form :inline="true" :model="printerTemplateTable.searchParam" ref="searchFormRef">
                    <el-form-item :label="t('templateName')" prop="template_name">
                        <el-input v-model.trim="printerTemplateTable.searchParam.template_name" :placeholder="t('templateNamePlaceholder')" />
                    </el-form-item>
                    <el-form-item :label="t('templateType')" prop="template_type">
                        <el-select v-model="printerTemplateTable.searchParam.template_type" :placeholder="t('templateTypePlaceholder')" clearable>
                            <el-option v-for="(item,key) in printerType" :key="itemkey" :label="item.title" :value="item.key" />
                        </el-select>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="loadPrinterTemplateList()">{{ t('search') }}</el-button>
                        <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
                    </el-form-item>
                </el-form>
            </el-card>

            <div class="mt-[10px]">
                <el-table :data="printerTemplateTable.data" size="large" v-loading="printerTemplateTable.loading">
                    <template #empty>
                        <span>{{ !printerTemplateTable.loading ? t('emptyData') : '' }}</span>
                    </template>

                    <el-table-column prop="template_name" :label="t('templateName')" min-width="220" :show-overflow-tooltip="true"/>
                    
                    <el-table-column prop="template_type_name" :label="t('templateType')" min-width="180" :show-overflow-tooltip="true"/>
                    
                    <el-table-column prop="create_time" :label="t('createTime')" min-width="120" />

                    <el-table-column :label="t('operation')" fixed="right" min-width="120">
                       <template #default="{ row }">
                           <el-button type="primary" link @click="editEvent(row)">{{ t('edit') }}</el-button>
                           <el-button type="primary" link @click="deleteEvent(row.template_id)">{{ t('delete') }}</el-button>
                       </template>
                    </el-table-column>

                </el-table>
                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="printerTemplateTable.page" v-model:page-size="printerTemplateTable.limit"
                        layout="total, sizes, prev, pager, next, jumper" :total="printerTemplateTable.total"
                        @size-change="loadPrinterTemplateList()" @current-change="loadPrinterTemplateList" />
                </div>
            </div>

        </el-card>
    </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import { getPrinterTemplatePageList, deletePrinterTemplate,getPrinterType } from '@/app/api/printer'
import { ElMessageBox,FormInstance } from 'element-plus'
import { useRoute,useRouter } from 'vue-router'
import { setTablePageStorage,getTablePageStorage } from "@/utils/common";

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title;

const handleClick = (path: string) => {
    router.push({ path })
}

const printerTemplateTable = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        template_name: '',
        template_type: '',
    }
})

const searchFormRef = ref<FormInstance>()

const printerType = ref([])
getPrinterType({}).then((res: any) => {
    if (res.data) {
        printerType.value = res.data;
    }
})

/**
 * 获取小票打印模板列表
 */
const loadPrinterTemplateList = (page: number = 1) => {
    printerTemplateTable.loading = true
    printerTemplateTable.page = page

    getPrinterTemplatePageList({
        page: printerTemplateTable.page,
        limit: printerTemplateTable.limit,
         ...printerTemplateTable.searchParam
    }).then(res => {
        printerTemplateTable.loading = false
        printerTemplateTable.data = res.data.data
        printerTemplateTable.total = res.data.total
        setTablePageStorage(printerTemplateTable.page, printerTemplateTable.limit, printerTemplateTable.searchParam);
    }).catch(() => {
        printerTemplateTable.loading = false
    })
}
loadPrinterTemplateList(getTablePageStorage(printerTemplateTable.searchParam).page)

/**
 * 添加小票打印模板
 */
const addEvent = () => {
    router.push('/printer/template/add')
}

/**
 * 编辑小票打印模板
 * @param data
 */
const editEvent = (data: any) => {
    router.push('/printer/template/edit?template_id=' + data.template_id)
}

/**
 * 删除小票打印模板
 */
const deleteEvent = (id: number) => {
    ElMessageBox.confirm(t('printerTemplateDeleteTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning',
        }
    ).then(() => {
        deletePrinterTemplate(id).then(() => {
            loadPrinterTemplateList()
        })
    })
}

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    loadPrinterTemplateList()
}
</script>

<style lang="scss" scoped>
</style>

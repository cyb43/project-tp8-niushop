<template>
    <div>
        <el-form :inline="true" :model="tableData.searchParam" ref="searchFormRef">
            <el-form-item :label="t('formSelectContentTitle')" prop="title" class="form-item-wrap">
                <el-input v-model.trim="tableData.searchParam.title" :placeholder="t('formSelectContentTitlePlaceholder')" />
            </el-form-item>
            <el-form-item :label="t('formSelectContentTypeName')" prop="type" class="form-item-wrap">
                <el-select v-model="tableData.searchParam.type" :placeholder="t('formSelectContentTypeNamePlaceholder')">
                    <el-option :label="t('formSelectContentTypeAll')" value="" />
                    <el-option v-for="(item, key) in formType" :label="item.title" :value="key" :key="key" />
                </el-select>
            </el-form-item>
            <el-form-item class="form-item-wrap">
                <el-button type="primary" @click="loadList()">{{ t('search') }}</el-button>
                <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
            </el-form-item>
        </el-form>
        <el-table :data="tableData.data" size="large" ref="tableRef" v-loading="tableData.loading">
            <template #empty>
                <span>{{ !tableData.loading ? t('emptyData') : '' }}</span>
            </template>
            <el-table-column min-width="7%">
                <template #default="{ row }">
                    <el-checkbox v-model="row.checked" @change="handleCheckChange($event,row)" />
                </template>
            </el-table-column>
            <el-table-column prop="page_title" :label="t('formSelectContentTitle')" min-width="65%" />
            <el-table-column prop="type_name" :label="t('formSelectContentTypeName')" min-width="25%" />
        </el-table>
        <div class="mt-[16px] flex justify-end">
            <el-pagination v-model:current-page="tableData.page" v-model:page-size="tableData.limit"
                           layout="total, sizes, prev, pager, next, jumper" :total="tableData.total"
                           @size-change="loadList()" @current-change="loadList" />
        </div>
    </div>
</template>

<script lang="ts" setup>
import { reactive, ref, nextTick } from 'vue'
import { t } from '@/lang'
import { getFormType, getDiyFormSelectPageList } from '@/app/api/diy_form'
import { FormInstance, ElMessage } from 'element-plus'

const prop = defineProps({
    formId: {
        type: [Number, String],
        default: 0
    }
})

const formType: any = reactive({}) // 表单类型

const searchFormRef = ref<FormInstance>()

const tableRef = ref()

const tableData: any = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        title: '',
        type: '',
        verify_form_ids: []
    }
})

// 已选万能表单
const selectData: any = reactive({
    form_id: prop.formId
})

// 获取自定义表单列表
const loadList = (page: number = 1) => {
    tableData.loading = true
    tableData.page = page

    if (selectData.form_id) {
        tableData.searchParam.verify_form_ids = [selectData.form_id]
    }

    getDiyFormSelectPageList({
        page: tableData.page,
        limit: tableData.limit,
        ...tableData.searchParam
    }).then(res => {
        tableData.loading = false
        tableData.data = res.data.data

        tableData.data.forEach((item: any) => {
            item.checked = item.form_id == selectData.form_id
        })
        tableData.total = res.data.total
        setGoodsSelected()
    }).catch(() => {
        tableData.loading = false
    })
}

// 获取万能表单类型
const loadFormType = (addon = '') => {
    getFormType({}).then(res => {
        for (const key in formType) {
            delete formType[key]
        }

        for (const key in res.data) {
            formType[key] = res.data[key]
        }
    })
}

loadFormType()
loadList()

const handleCheckChange = (isSelect: any, row: any) => {
    if (isSelect) {
        selectData.form_id = row.form_id
    } else {
        selectData.form_id = 0 // 未选中，移除当前
    }
    setGoodsSelected()
}

// 表格设置选中状态
const setGoodsSelected = () => {
    nextTick(() => {
        for (let i = 0; i < tableData.data.length; i++) {
            tableData.data[i].checked = false
            if (selectData.form_id == tableData.data[i].form_id) {
                tableData.data[i].checked = true
                Object.assign(selectData, tableData.data[i])
            }
        }
    })
}

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    loadList()
}

const getData = () => {
    if (selectData.form_id == 0) {
        ElMessage({
            type: 'warning',
            message: `${t('formSelectContentTips')}`
        })
        return
    }
    return {
        name: 'DIY_FORM',
        title: selectData.page_title,
        url: `/app/pages/index/diy_form?form_id=${selectData.form_id}`,
        action: '',
        formId: selectData.form_id
    }
}

defineExpose({
    getData
})
</script>

<style lang="scss" scoped>
.form-item-wrap {
    margin-right: 10px !important;
    margin-bottom: 10px !important;

    &.last-child {
        margin-right: 0 !important;
    }
}
</style>

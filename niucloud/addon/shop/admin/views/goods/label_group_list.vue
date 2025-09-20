<template>
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">

            <div class="flex justify-between items-center">
                <span class="text-page-title">{{pageName}}</span>
                <el-button type="primary" @click="addEvent">
                    {{ t('addLabelGroup') }}
                </el-button>
            </div>

            <el-tabs model-value="/shop/goods/label/group" @tab-change="handleClick">
                <el-tab-pane :label="t('tabGoodsLabel')" name="/shop/goods/label" />
                <el-tab-pane :label="t('tabGoodsLabelGroup')" name="/shop/goods/label/group" />
            </el-tabs>

            <el-card class="box-card !border-none my-[10px] table-search-wrap" shadow="never">
                <el-form :inline="true" :model="labelTable.searchParam" ref="searchFormRef">
                    <el-form-item :label="t('groupName')" prop="group_name">
                        <el-input v-model.trim="labelTable.searchParam.group_name" :placeholder="t('groupNamePlaceholder')"  />
                    </el-form-item>

                    <el-form-item>
                        <el-button type="primary" @click="loadLabelGroupList()">{{ t('search') }}</el-button>
                        <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
                    </el-form-item>
                </el-form>
            </el-card>

            <div class="mt-[10px]">
                <el-table :data="labelTable.data" size="large" v-loading="labelTable.loading" @sort-change="sortChange">
                    <template #empty>
                        <span>{{ !labelTable.loading ? t('emptyData') : '' }}</span>
                    </template>
                    <el-table-column prop="group_name" :label="t('groupName')" min-width="120" />
                    <el-table-column prop="sort" :label="t('sort')" min-width="120" sortable="custom">
                        <template #default="{ row }">
                            <el-input v-model.trim="row.sort" class="!w-[100px]" maxlength="8" @blur="sortInputListener(row.sort, row)" />
                        </template>
                    </el-table-column>

                    <el-table-column :label="t('operation')" fixed="right" align="right" min-width="120">
                       <template #default="{ row }">
                           <el-button type="primary" link @click="editEvent(row)">{{ t('edit') }}</el-button>
                           <el-button type="primary" link @click="deleteEvent(row.group_id)">{{ t('delete') }}</el-button>
                       </template>
                    </el-table-column>

                </el-table>
                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="labelTable.page" v-model:page-size="labelTable.limit"
                        layout="total, sizes, prev, pager, next, jumper" :total="labelTable.total"
                        @size-change="loadLabelGroupList()" @current-change="loadLabelGroupList" />
                </div>
            </div>

            <label-group-edit ref="editLabelGroupDialog" @complete="loadLabelGroupList" />
        </el-card>
    </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import { getLabelGroupPageList, deleteLabelGroup, modifyLabelGroupSort } from '@/addon/shop/api/goods'
import { ElMessageBox, FormInstance, ElMessage } from 'element-plus'
import LabelGroupEdit from '@/addon/shop/views/goods/components/label-group-edit.vue'
import { useRoute, useRouter } from 'vue-router'
import { debounce, setTablePageStorage, getTablePageStorage } from '@/utils/common'

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title

const handleClick = (path: string) => {
    router.push({ path })
}

const labelTable = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        group_name: '',
        order: '',
        sort: ''
    }
})

const searchFormRef = ref<FormInstance>()

// 监听排序
const sortChange = (event: any) => {
    let sort = ''
    if (event.order == 'ascending') {
        sort = 'asc'
    } else if (event.order == 'descending') {
        sort = 'desc'
    }
    if (sort) {
        labelTable.searchParam.order = event.prop
        labelTable.searchParam.sort = sort
    }
    loadLabelGroupList()
}

/**
 * 获取商品标签列表
 */
const loadLabelGroupList = (page: number = 1) => {
    labelTable.loading = true
    labelTable.page = page

    getLabelGroupPageList({
        page: labelTable.page,
        limit: labelTable.limit,
        ...labelTable.searchParam
    }).then(res => {
        labelTable.loading = false
        labelTable.data = res.data.data
        labelTable.total = res.data.total
        setTablePageStorage(labelTable.page, labelTable.limit, labelTable.searchParam)
    }).catch(() => {
        labelTable.loading = false
    })
}
loadLabelGroupList(getTablePageStorage(labelTable.searchParam).page)

const editLabelGroupDialog: Record<string, any> | null = ref(null)

/**
 * 添加商品标签
 */
const addEvent = () => {
    editLabelGroupDialog.value.setFormData()
    editLabelGroupDialog.value.showDialog = true
}

/**
 * 编辑商品标签
 * @param data
 */
const editEvent = (data: any) => {
    editLabelGroupDialog.value.setFormData(data)
    editLabelGroupDialog.value.showDialog = true
}

/**
 * 删除商品标签
 */
const deleteEvent = (id: number) => {
    ElMessageBox.confirm(t('labelGroupDeleteTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        deleteLabelGroup(id).then(() => {
            loadLabelGroupList()
        }).catch(() => {
        })
    })
}

// 修改排序号
const sortInputListener = debounce((sort, row) => {
    if (isNaN(sort) || !/^\d{0,8}$/.test(sort)) {
        ElMessage({
            type: 'warning',
            message: `${t('sortTips')}`
        })
        return
    }
    if (sort > 99999999) {
        row.sort = 99999999
    }
    modifyLabelGroupSort({
        group_id: row.group_id,
        sort
    }).then((res) => {
    })
})

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    loadLabelGroupList()
}
</script>

<style lang="scss" scoped>
</style>

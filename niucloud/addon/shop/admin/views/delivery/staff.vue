<template>
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">
            <el-page-header :content="pageName" :icon="ArrowLeft" @back="$router.back()" />
        </el-card>

        <el-card class="box-card mt-[15px] !border-none" shadow="never">
            <el-card class="box-card !border-none my-[10px] table-search-wrap" shadow="never">
                <div class="flex justify-between items-center">
                    <el-form :inline="true" :model="tableData.searchParam" ref="searchFormRef">
                        <el-form-item :label="t('deliverName')" prop="deliver_name">
                            <el-input v-model.trim="tableData.searchParam.deliver_name" :placeholder="t('deliverNamePlaceholder')"  />
                        </el-form-item>
                        <el-form-item :label="t('deliverMobile')" prop="deliver_mobile">
                            <el-input v-model.trim="tableData.searchParam.deliver_mobile" :placeholder="t('deliverMobilePlaceholder')" />
                        </el-form-item>
                        <el-form-item>
                            <el-button type="primary" @click="getShopDeliveryFn()">{{ t('search') }}</el-button>
                            <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
                        </el-form-item>
                    </el-form>
                    <el-button type="primary" @click="addEvent">
                        {{ t('addDeliveryPersonnel') }}
                    </el-button>
                </div>
            </el-card>

            <div class="mt-[10px]">
                <el-table :data="tableData.data" ref="tableRef" size="large" v-loading="tableData.loading">
                    <template #empty>
                        <span>{{ !tableData.loading ? t('emptyData') : '' }}</span>
                    </template>
                    <el-table-column prop="deliver_name" :label="t('deliverName')" />
                    <el-table-column prop="deliver_mobile" :label="t('deliverMobile')" />
                    <el-table-column :label="t('operation')" fixed="right" align="right" width="120">
                        <template #default="{ row }">
                            <el-button type="primary" link @click="editEvent(row)">{{ t('edit') }}</el-button>
                            <el-button type="primary" link @click="deleteEvent(row.deliver_id)">{{ t('delete') }}</el-button>
                        </template>
                    </el-table-column>
                </el-table>
                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="tableData.page" v-model:page-size="tableData.limit"
                        layout="total, sizes, prev, pager, next, jumper" :total="tableData.total"
                        @size-change="getShopDeliveryFn()" @current-change="getShopDeliveryFn" />
                </div>
            </div>
        </el-card>
        <delivery-personnel-edit ref="editCategoryDialog" @complete="getShopDeliveryFn" />
    </div>
</template>
<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import deliveryPersonnelEdit from '@/addon/shop/views/delivery/components/delivery-personnel-edit.vue'
import { getShopDelivery, deleteShopDeliver } from '@/addon/shop/api/delivery'
import { useRoute, useRouter } from 'vue-router'
import { ElMessageBox, FormInstance } from 'element-plus'
import { ArrowLeft } from '@element-plus/icons-vue'
import { setTablePageStorage,getTablePageStorage } from "@/utils/common";

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title
const tableData = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: false,
    data: [],
    searchParam: {
        deliver_name: '',
        deliver_mobile: ''
    }
})
const searchFormRef = ref<FormInstance>()
/**
 * 获取配送员列表
 */
const getShopDeliveryFn = (page: number = 1) => {
    tableData.loading = true
    tableData.page = page

    getShopDelivery({
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
getShopDeliveryFn(getTablePageStorage(tableData.searchParam).page)
const editCategoryDialog: Record<string, any> | null = ref(null)

/**
 * 添加配送员
 */
const addEvent = () => {
    editCategoryDialog.value.setFormData()
    editCategoryDialog.value.showDialog = true
}

/**
 * 编辑配送员
 * @param data
 */
const editEvent = (data: any) => {
    editCategoryDialog.value.setFormData(data)
    editCategoryDialog.value.showDialog = true
}
/**
 * 删除配送员
 */
const deleteEvent = (id: number) => {
    ElMessageBox.confirm(t('deliverDeleteTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        deleteShopDeliver(id).then(() => {
            getShopDeliveryFn()
        }).catch(() => {
        })
    })
}
const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    getShopDeliveryFn()
}
</script>
<style lang="scss" scoped></style>

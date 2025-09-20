<template>
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">

            <div class="flex justify-between items-center">
                <span class="text-page-title">{{ pageName }}</span>
                <el-button type="primary" @click="handleChange">
                    {{ t('addDiscount') }}
                </el-button>
            </div>

            <!-- 搜索 -->
            <el-card class="box-card !border-none my-[10px] table-search-wrap" shadow="never">
                <el-form :inline="true" :model="tableData.searchParam" ref="searchFormRef">
                    <el-form-item :label="t('name')" prop="name">
                        <el-input v-model.trim="tableData.searchParam.name" :placeholder="t('namePlaceholder')" />
                    </el-form-item>
                    <!-- <el-form-item :label="t('status')" prop='status'>
                        <el-select v-model="tableData.searchParam.status" clearable :placeholder="t('statusPlaceholder')" class="input-item">
                            <el-option v-for="(item, key) in activeStatusOption" :key="key" :label="item" :value="key"></el-option>
                        </el-select>
                    </el-form-item> -->
                    <el-form-item>
                        <el-button type="primary" @click="loadDiscountList()">{{ t('search') }}</el-button>
                        <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
                    </el-form-item>
                </el-form>
            </el-card>

            <!-- 列表 -->
            <div>
                <el-tabs v-model="tableData.searchParam.status" class="goods-tabs" @tab-click="tabHandleClick">
                    <el-tab-pane :label="t('全部')" name=""></el-tab-pane>
                    <el-tab-pane v-for="(label, value) in activeStatusOption" :key="value" :label="label" :name="value"></el-tab-pane>
                </el-tabs>
                <div class="mb-[10px] flex items-center">
                    <el-checkbox v-model="toggleCheckbox" size="large" class="px-[14px]" @change="toggleChange" :indeterminate="isIndeterminate" />
                    <el-button @click="batchDeleteEvent" size="small" v-if="tableData.searchParam.status != 'active'">{{t("batchDelete")}}</el-button>
                    <el-button @click="batchcloseEvent" size="small" v-if="tableData.searchParam.status =='active' || tableData.searchParam.status == '' || tableData.searchParam.status == null">{{t("batchClose")}}</el-button>
                </div>
                <el-table :data="tableData.data" size="large" v-loading="tableData.loading" ref="discountListTableRef" @selection-change="handleSelectionChange">
                    <template #empty>
                        <span>{{ !tableData.loading ? t('emptyData') : '' }}</span>
                    </template>
                    <el-table-column type="selection" width="55" />
                    <el-table-column prop="name" :label="t('name')" min-width="130" />
                    <el-table-column prop="remark" :label="t('title')" min-width="130" />
                    <el-table-column prop="status_name" :label="t('status')" min-width="130" />
                    <el-table-column prop="order_money" :label="t('paymentAmount')" min-width="130" />
                    <el-table-column prop="order_num" :label="t('orderCount')" min-width="130" />
                    <el-table-column prop="member_num" :label="t('memberCount')" min-width="130" />
                    <el-table-column :label="t('discountTime')"  min-width="150">
                        <template #default="{ row }">
                            <div>
                                <p>开始：{{ row.start_time }}</p>
                                <p>结束：{{ row.end_time }}</p>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('operation')" fixed="right" align="right" min-width="160">
                       <template #default="{ row }">
                            <el-button v-if="row.status=='not_active'||row.status=='active'" type="primary" link @click="editEvent(row.discount_id)">{{ t('edit') }}</el-button>
                            <el-button type="primary" link @click="detailEvent(row.discount_id)">{{ t('detail') }}</el-button>
                            <el-button v-if="row.status=='active'" type="primary" link @click="closeEvent(row.discount_id)">{{ t('close') }}</el-button>
                            <el-button v-if="row.status!='active'" type="primary" link @click="deleteEvent(row.discount_id)">{{ t('delete') }}</el-button>
                        </template>
                    </el-table-column>
                </el-table>

                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="tableData.page" v-model:page-size="tableData.limit"
                        layout="total, sizes, prev, pager, next, jumper" :total="tableData.total"
                        @size-change="loadDiscountList()" @current-change="loadDiscountList" />
                </div>
            </div>
        </el-card>
        <discount-detail ref="discountDetailDialog"></discount-detail>
    </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessageBox, FormInstance } from 'element-plus'
import { getActiveDiscountPageList, getActiveDiscountStatusList, closeActiveDiscount, deleteActiveDiscount, batchCloseActiveDiscount, batchDeleteActiveDiscount } from '@/addon/shop/api/marketing'
import { t } from '@/lang'
import discountDetail from '@/addon/shop/views/marketing/discount/components/discount-detail.vue'
import { setTablePageStorage, getTablePageStorage } from '@/utils/common'

const router = useRouter()
const route = useRoute()
const pageName = route.meta.title

// 表单内容
const tableData = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: false,
    data: [],
    searchParam: {
        name: '',
        status: '',
        active_id: route.query.active_id || ''
    }
})
const searchFormRef = ref<FormInstance>()

const tabHandleClick = (tab: any, event: Event) => {
    tableData.searchParam.status = tab.props.name
    loadDiscountList()
}
// 批量复选框
const toggleCheckbox = ref()

// 复选框中间状态
const isIndeterminate = ref(false)

// 监听批量复选框事件
const toggleChange = (value: any) => {
    isIndeterminate.value = false
    discountListTableRef.value.toggleAllSelection()
}

const discountListTableRef = ref()

// 选中数据
const multipleSelection: any = ref([])

// 监听表格单行选中
const handleSelectionChange = (val: []) => {
    multipleSelection.value = val

    toggleCheckbox.value = false
    if (multipleSelection.value.length > 0 && multipleSelection.value.length < tableData.data.length) {
        isIndeterminate.value = true
    } else {
        isIndeterminate.value = false
    }

    if (multipleSelection.value.length == tableData.data.length && tableData.data.length && multipleSelection.value.length) {
        toggleCheckbox.value = true
    }
}

const loadDiscountList = (page: number = 1) => {
    tableData.loading = true
    tableData.page = page

    getActiveDiscountPageList({
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
loadDiscountList(getTablePageStorage(tableData.searchParam).page)
const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    loadDiscountList()
}
// 获取状态列表
const activeStatusOption = ref({})
const getActiveDiscountStatusListFn = () => {
    getActiveDiscountStatusList().then(res => {
        activeStatusOption.value = res.data
    })
}
getActiveDiscountStatusListFn()
// 添加折扣券
const handleChange = () => {
    router.push('/shop/marketing/discount/add')
}
// 详情
const discountDetailDialog: Record<string, any> | null = ref(null)
const detailEvent = (id:number) => {
    const data = { id }
    discountDetailDialog.value.setFormData(data)
    discountDetailDialog.value.showDialog = true
}
// 编辑折扣券
const editEvent = (id:number) => {
    router.push({ path: '/shop/marketing/discount/edit', query: { id } })
}
// 关闭
const closeEvent = (id:number) => {
    ElMessageBox.confirm(t('closeTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        closeActiveDiscount(id).then(() => {
            loadDiscountList()
        }).catch(() => {
        })
    })
}
// 删除
const deleteEvent = (id:number) => {
    ElMessageBox.confirm(t('deleteTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        deleteActiveDiscount(id).then(() => {
            loadDiscountList()
        }).catch(() => {
        })
    })
}

// 批量删除
const batchDeleteEvent = () => {
    if (multipleSelection.value.length == 0) {
        ElMessage({
            type: 'warning',
            message: `${t('batchEmptySelectedGoodsTips')}`
        })
        return
    }

    ElMessageBox.confirm(t('batchDeleteTips'), t('warning'), {
        confirmButtonText: t('confirm'),
        cancelButtonText: t('cancel'),
        type: 'warning'
    }).then(() => {
        const discount_ids: any = []
        multipleSelection.value.forEach((item: any) => {
            discount_ids.push(item.discount_id)
        })

        batchDeleteActiveDiscount({
            ids: discount_ids
        }).then(() => {
            loadDiscountList()
        }).catch(() => {
        })
    })
}

// 批量关闭
const batchcloseEvent = () => {
    if (multipleSelection.value.length == 0) {
        ElMessage({
            type: 'warning',
            message: `${t('batchEmptySelectedGoodsTips')}`
        })
        return
    }

    ElMessageBox.confirm(t('batchCloseTips'), t('warning'), {
        confirmButtonText: t('confirm'),
        cancelButtonText: t('cancel'),
        type: 'warning'
    }).then(() => {
        const discount_ids: any = []
        multipleSelection.value.forEach((item: any) => {
            discount_ids.push(item.discount_id)
        })

        batchCloseActiveDiscount({
            ids: discount_ids
        }).then(() => {
            loadDiscountList()
        }).catch(() => {
        })
    })
}
</script>

<style lang="scss" scoped></style>

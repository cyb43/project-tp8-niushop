<template>
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">

            <div class="flex justify-between items-center">
                <span class="text-page-title">{{ pageName }}</span>
                <el-button type="primary" @click="handleChange">
                    {{ t('addGoods') }}
                </el-button>
            </div>

            <!-- 搜索 -->
            <el-card class="box-card !border-none my-[10px] table-search-wrap" shadow="never">
                <el-form :inline="true" :model="tableData.searchParam" ref="searchFormRef">
                    <el-form-item :label="t('goodsName')" prop="names">
                        <el-input v-model.trim="tableData.searchParam.names" :placeholder="t('goodsNamePlaceholder')" />
                    </el-form-item>
                    <!-- <el-form-item :label="t('status')" prop='status'>
                        <el-select v-model="tableData.searchParam.status" clearable :placeholder="t('statusPlaceholder')" class="input-item">
                            <el-option v-for="(item, key) in statusOption" :key="key" :label="item" :value="key"></el-option>
                        </el-select>
                    </el-form-item> -->
                    <el-form-item :label="t('createTime')" prop="create_time">
                        <el-date-picker v-model="tableData.searchParam.create_time" type="datetimerange" value-format="YYYY-MM-DD HH:mm:ss" :start-placeholder="t('startDate')" :end-placeholder="t('endDate')" />
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="loadExchangeGoodsList()">{{ t('search') }}</el-button>
                        <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
                    </el-form-item>
                </el-form>
            </el-card>

            <!-- 列表 -->
            <div class="mt-[10px]">
                <el-tabs v-model="tableData.searchParam.status" class="goods-tabs" @tab-click="tabHandleClick">
                    <el-tab-pane :label="t('全部')" name=""></el-tab-pane>
                    <el-tab-pane v-for="(label, value) in statusOption" :key="value" :label="label" :name="value"></el-tab-pane>
                </el-tabs>
                <div class="mb-[10px] flex items-center">
                    <el-checkbox v-model="toggleCheckbox" size="large" class="px-[14px]" @change="toggleChange" :indeterminate="isIndeterminate" />
                    <el-button @click="batchDeleteEvent" size="small" v-if="tableData.searchParam.status == 0|| tableData.searchParam.status == '' || tableData.searchParam.status == null">{{t("batchDelete")}}</el-button>
                    <el-button @click="batchUpEvent" size="small" v-if="tableData.searchParam.status == 0|| tableData.searchParam.status == '' || tableData.searchParam.status == null">{{t("batchUp")}}</el-button>
                    <el-button @click="batchDownEvent" size="small" v-if="tableData.searchParam.status == 1 || tableData.searchParam.status == '' || tableData.searchParam.status == null">{{t("batchDown")}}</el-button>
                </div>
                <el-table :data="tableData.data" size="large" v-loading="tableData.loading" ref="discountListTableRef" @selection-change="handleSelectionChange">
                    <template #empty>
                        <span>{{ !tableData.loading ? t('emptyData') : '' }}</span>
                    </template>
                    <el-table-column type="selection" width="55" />
                    <el-table-column :label="t('goods')" min-width="130">
                        <template #default="{ row }">
                            <div class="flex items-center cursor-pointer">
                                <div class="min-w-[60px] h-[60px] flex items-center justify-center">
                                    <el-image v-if="row.goods_cover_thumb_small" class="w-[60px] h-[60px]" :src="img(row.goods_cover_thumb_small)" fit="contain">
                                        <template #error>
                                            <div class="image-slot">
                                                <img class="w-[60px] h-[60px]" src="@/addon/shop/assets/goods_default.png" />
                                            </div>
                                        </template>
                                    </el-image>
                                    <img v-else class="w-[70px] h-[60px]" src="@/addon/shop/assets/goods_default.png" fit="contain" />
                                </div>
                                <div class="ml-2">
                                    <span :title="row.names" class="multi-hidden">{{ row.names }}</span>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('exchangePrice')" min-width="130">
                        <template #default="{ row }">
                            <p v-if="row.point">{{ row.point }}{{ t('pointUnit') }}</p>
                            <p v-if="row.price">{{ row.price }}{{ t('priceUnit') }}</p>
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('redeemedAndSurplus')" min-width="130">
                        <template #default="{ row }">
                            <span>{{ row.total_exchange_num }}/{{ row.stock }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="create_time" :label="t('createTime')" min-width="150">
                        <template #default="{ row }">
                            <div>{{ row.create_time }}</div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="status_name" :label="t('status')" min-width="130" />
                    <el-table-column :label="t('operation')" fixed="right" align="right" min-width="160">
                        <template #default="{ row }">
                            <el-button type="primary" link @click="editEvent(row.id)">{{ t('edit') }}</el-button>
                            <el-button type="primary" link @click="spreadEvent(row)">{{ t('spreadGoods') }}</el-button>
                            <el-button v-if="row.status" type="primary" link @click="statusEvent(row.id,0)">{{ t('down') }}</el-button>
                            <el-button v-else type="primary" link @click="statusEvent(row.id,1)">{{ t('up') }}</el-button>
                            <el-button v-if="!row.status" type="primary" link @click="deleteEvent(row.id)">{{ t('delete') }}</el-button>
                        </template>
                    </el-table-column>
                </el-table>

                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="tableData.page" v-model:page-size="tableData.limit"
                                   layout="total, sizes, prev, pager, next, jumper" :total="tableData.total"
                                   @size-change="loadExchangeGoodsList()" @current-change="loadExchangeGoodsList" />
                </div>
            </div>
        </el-card>

        <!-- 商品推广弹出框 -->
        <spread-popup ref="spreadPopupRef" />
    </div>
</template>

<script lang="ts" setup>
import { t } from '@/lang'
import { reactive, ref } from 'vue'
import { img, setTablePageStorage, getTablePageStorage } from '@/utils/common'
import { useRoute, useRouter } from 'vue-router'
import { ElMessageBox, FormInstance } from 'element-plus'
import spreadPopup from '@/components/spread-popup/index.vue'
import {
    getActiveExchangePageList,
    deleteActiveExchange,
    editActiveExchangeStatus,
    getActiveExchangeStatus,
    batchDeleteActiveExchange,
    batchDownActiveExchange,
    batchUpActiveExchange
} from '@/addon/shop/api/marketing'

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title

// 表单内容
const tableData = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: false,
    data: [],
    searchParam: {
        names: '',
        status: '',
        create_time: [],
        active_id: route.query.active_id || ''
    }
})
const searchFormRef = ref<FormInstance>()

const tabHandleClick = (tab: any, event: Event) => {
    tableData.searchParam.status = tab.props.name
    loadExchangeGoodsList()
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

const loadExchangeGoodsList = (page: number = 1) => {
    tableData.loading = true
    tableData.page = page

    getActiveExchangePageList({
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
loadExchangeGoodsList(getTablePageStorage(tableData.searchParam).page)
// 获取状态列表
const statusOption = ref([])
const getActiveExchangeStatusFn = () => {
    getActiveExchangeStatus().then(res => {
        statusOption.value = res.data
    })
}
getActiveExchangeStatusFn()
// 添加商品
const handleChange = () => {
    router.push('/shop/marketing/exchange/goods_add')
}

// 编辑商品
const editEvent = (id: number) => {
    router.push({ path: '/shop/marketing/exchange/goods_edit', query: { id } })
}
// 商品推广
const spreadPopupRef = ref(null)

const spreadEvent = (data: any) => {
    const pagePath = '/addon/shop/pages/point/detail'
    const paramsArr = [
        { name: 'id', value: data.id },
    ];
    const title = '积分商品推广'
    const folder = 'goods'
    spreadPopupRef.value?.show(pagePath, paramsArr, title, folder);
}
// 上下架
const statusEvent = (id: number, status: number) => {
    ElMessageBox.confirm(status ? t('upTips') : t('downTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        editActiveExchangeStatus({ id, status }).then(() => {
            loadExchangeGoodsList()
        }).catch(() => {
        })
    })
}
// 删除
const deleteEvent = (id: number) => {
    ElMessageBox.confirm(t('deleteTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        deleteActiveExchange(id).then(() => {
            loadExchangeGoodsList()
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
        const exchange_ids: any = []
        multipleSelection.value.forEach((item: any) => {
            exchange_ids.push(item.id)
        })

        batchDeleteActiveExchange({
            ids: exchange_ids
        }).then(() => {
            loadExchangeGoodsList()
        }).catch(() => {
        })
    })
}

// 批量下架
const batchDownEvent = () => {
    if (multipleSelection.value.length == 0) {
        ElMessage({
            type: 'warning',
            message: `${t('batchEmptySelectedGoodsTips')}`
        })
        return
    }

    ElMessageBox.confirm(t('batchDownTips'), t('warning'), {
        confirmButtonText: t('confirm'),
        cancelButtonText: t('cancel'),
        type: 'warning'
    }).then(() => {
        const exchange_ids: any = []
        multipleSelection.value.forEach((item: any) => {
            exchange_ids.push(item.id)
        })

        batchDownActiveExchange({
            ids: exchange_ids
        }).then(() => {
            loadExchangeGoodsList()
        }).catch(() => {
        })
    })
}

// 批量上架
const batchUpEvent = () => {
    if (multipleSelection.value.length == 0) {
        ElMessage({
            type: 'warning',
            message: `${t('batchEmptySelectedGoodsTips')}`
        })
        return
    }

    ElMessageBox.confirm(t('batchUpTips'), t('warning'), {
        confirmButtonText: t('confirm'),
        cancelButtonText: t('cancel'),
        type: 'warning'
    }).then(() => {
        const exchange_ids: any = []
        multipleSelection.value.forEach((item: any) => {
            exchange_ids.push(item.id)
        })

        batchUpActiveExchange({
            ids: exchange_ids
        }).then(() => {
            loadExchangeGoodsList()
        }).catch(() => {
        })
    })
}

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    loadExchangeGoodsList()
}
</script>

<style lang="scss" scoped></style>

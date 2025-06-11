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
                    <el-form-item :label="t('status')" prop='status'>
                        <el-select v-model="tableData.searchParam.status" clearable :placeholder="t('statusPlaceholder')" class="input-item">
                            <el-option v-for="(item, key) in statusOption" :key="key" :label="item" :value="key"></el-option>
                        </el-select>
                    </el-form-item>
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
                <el-table :data="tableData.data" size="large" v-loading="tableData.loading">
                    <template #empty>
                        <span>{{ !tableData.loading ? t('emptyData') : '' }}</span>
                    </template>

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
                                    <img v-else class="w-[70px] h-[60px]" src="@/addon/shop/assets/goods_default.png"
                                         fit="contain" />
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
    getActiveExchangeStatus
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
        create_time: []
    }
})
const searchFormRef = ref<FormInstance>()
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
        setTablePageStorage(tableData.page, tableData.limit, tableData.searchParam);
    }).catch(() => {
        tableData.loading = false
    })
}
loadExchangeGoodsList(getTablePageStorage(tableData.searchParam).page);
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
    const pagePath = "/addon/shop/pages/point/detail"
    const columnName = "id"
    const columnValue = data.id
    const title = "积分商品推广"
    const folder = "goods"
    spreadPopupRef.value?.show(pagePath, columnName, columnValue, title,folder)
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
const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    loadExchangeGoodsList()
}
</script>

<style lang="scss" scoped></style>

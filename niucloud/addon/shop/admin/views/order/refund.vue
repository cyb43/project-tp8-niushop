<template>
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">

            <div class="flex justify-between items-center">
                <span class="text-page-title">{{ pageName }}</span>
            </div>

            <el-card class="box-card !border-none my-[10px] table-search-wrap" shadow="never">
                <el-form :inline="true" :model="orderTable.searchParam" ref="searchFormRef">
                    <el-form-item :label="t('orderRefundNo')" prop="order_refund_no">
                        <el-input v-model.trim="orderTable.searchParam.order_refund_no" :placeholder="t('orderRefundNoPlaceholder')" @keyup="filterNumber($event)" maxlength="30" />
                    </el-form-item>
                    <el-form-item :label="t('createTime')" prop="create_time">
                        <el-date-picker v-model="orderTable.searchParam.create_time" type="datetimerange" value-format="YYYY-MM-DD HH:mm:ss" :start-placeholder="t('startDate')" :end-placeholder="t('endDate')" />
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="loadOrderList()">{{ t('search') }}</el-button>
                        <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
                        <el-button type="primary" @click="exportEvent">{{ t('export') }}</el-button>
                    </el-form-item>
                </el-form>
            </el-card>
            <el-tabs v-model="activeName" class="demo-tabs" @tab-change="handleClick">
                <el-tab-pane :label="t('all')" name=""></el-tab-pane>
                <el-tab-pane :label="t('applyForRefund')" name="1"></el-tab-pane>
                <el-tab-pane :label="t('refundEnd')" name="8"></el-tab-pane>
                <el-tab-pane :label="t('toBeReturned')" name="2"></el-tab-pane>
                <el-tab-pane :label="t('receivedGoods')" name="4"></el-tab-pane>
                <el-tab-pane :label="t('refundRefuse')" name="3"></el-tab-pane>
            </el-tabs>
            <div>
                <el-table :data="orderTable.data" size="large" class="table-top" @select-all="selectAllCheck">
                    <el-table-column type="selection" width="40" />
                    <el-table-column :label="t('goodsInfo')" min-width="240" />
                    <el-table-column :label="t('goodsMoney')" min-width="120" />
                    <el-table-column :label="t('realityMoney')" min-width="120" />
                    <el-table-column :label="t('buyMember')" min-width="120" />
                    <el-table-column :label="t('refundMoney')" min-width="120" />
                    <el-table-column :label="t('refundType')" min-width="120" />
                    <el-table-column :label="t('createTime')" min-width="120" />
                    <el-table-column :label="t('refundStatus')" min-width="120" />
                    <el-table-column :label="t('operation')" fixed="right" align="right" min-width="120" />
                </el-table>
                <div class="table-body min-h-[150px]" v-loading="orderTable.loading">
                    <div v-if="!orderTable.loading">
                        <template v-if="orderTable.data.length">
                            <div v-for="(item, index) in orderTable.data" :key="index">
                                <div class="flex items-center justify-between bg-[#f7f8fa] mt-[10px] border-[#e4e7ed] border-solid border-b-[1px] px-3 h-[35px] text-[12px] text-[#666]">
                                    <div>
                                        <span class="ml-5">{{ t('orderRefundNo') }}：{{ (item as any).order_refund_no }}</span>
                                    </div>
                                </div>

                                <el-table :data="(item as any).order_goods" size="large" :show-header="false" ref="multipleTable">
                                    <el-table-column type="selection" width="40" />
                                    <el-table-column align="left" min-width="240">
                                        <template #default="{ row }">
                                            <div class="flex cursor-pointer">
                                                <div class="flex items-center min-w-[50px] mr-[10px]">
                                                    <img class="w-[50px] h-[50px]" v-if="row.goods_image_thumb_small" :src="img(row.goods_image_thumb_small)" alt="">
                                                    <img class="w-[50px] h-[50px]" v-else src="" alt="">
                                                </div>
                                                <div class="flex  flex-col">
                                                    <el-tooltip class="box-item" effect="light" placement="top">
                                                        <template #content>
                                                            <div class="max-w-[250px]">{{ row.goods_name }}</div>
                                                        </template>
                                                        <p class="multi-hidden">{{ row.goods_name }}</p>
                                                    </el-tooltip>
                                                    <span class="text-[12px] text-[#999] truncate">{{ row.sku_name }}</span>
                                                </div>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column min-width="120">
                                        <template #default="{ row }">
                                            <div class="flex flex-col">
                                                <span class="text-[14px]">￥{{ row.goods_money }}</span>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column min-width="120">
                                        <template #default="{ row }">
                                            <div class="flex flex-col">
                                                <span class="text-[14px]">￥{{ parseFloat(row.goods_money - row.discount_money).toFixed(2) }}</span>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column min-width="120">
                                        <template #default>
                                            <el-button link type="primary" @click="memberEvent(item.member.member_id)" v-if="item.member">{{ item.member.nickname }}</el-button>
                                        </template>
                                    </el-table-column>
                                    <el-table-column min-width="120">
                                        <template #default>
                                            <span class="text-[14px]" v-if="item.status == 8">￥{{ item.money }}</span>
                                            <span class="text-[14px]" v-else>￥{{ item.apply_money }}</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column min-width="120">
                                        <template #default>
                                            <span class="text-[14px]">{{ item.refund_type_name }}</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column min-width="120">
                                        <template #default>
                                            <span class="text-[14px]">{{ item.create_time }}</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column min-width="120">
                                        <template #default>
                                            <span class="text-[14px]">{{ item.status_name }}</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column align="right" min-width="120">
                                        <template #default>
                                            <el-button type="primary" link @click="detailEvent(item)">{{ t('info') }}</el-button>
                                        </template>
                                    </el-table-column>
                                </el-table>
                                <div v-if="item.shop_remark"
                                     class="text-[14px] h-[30px] leading-[30px] px-3 bg-[#fff0e5] text-[#ff7f5b]">
                                    <span class="mr-[5px]">{{ t('notes') }}：</span>
                                    <span>{{ item.shop_remark }}</span>
                                </div>
                            </div>
                        </template>
                        <el-empty v-else :image-size="1" :description="t('emptyData')" />
                    </div>
                </div>
                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="orderTable.page" v-model:page-size="orderTable.limit"
                                   layout="total, sizes, prev, pager, next, jumper" :total="orderTable.total"
                                   @size-change="loadOrderList()" @current-change="loadOrderList" />
                </div>
            </div>
        </el-card>
        <order-notes ref="orderNotesDialog" @complete="loadOrderList"></order-notes>
        <export-sure ref="exportSureDialog" :show="flag" type="shop_order_refund" :searchParam="orderTable.searchParam" @close="handleClose" />
        <refund-detail ref="refundDetailDialog" @load="loadOrderList"></refund-detail>
    </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import { orderRefund } from '@/addon/shop/api/order'
import { img, filterNumber, setTablePageStorage, getTablePageStorage } from '@/utils/common'
import type { FormInstance } from 'element-plus'
import { useRouter, useRoute } from 'vue-router'
import refundDetail from '@/addon/shop/views/order/components/refund-detail.vue'

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title
const activeName = ref('')
const refundDetailDialog: Record<string, any> | null = ref(null)

const multipleTable: Record<string, any> | null = ref(null)
const isSelectAll = ref(false)
const selectAllCheck = () => {
    if (isSelectAll.value == false) {
        isSelectAll.value = true
        for (const i in orderTable.data) {
            for (const j in orderTable.data[i].order_goods) {
                multipleTable.value[i].toggleRowSelection(orderTable.data[i].order_goods[j], true)
            }
        }
    } else {
        isSelectAll.value = false
        for (const v in orderTable.data) {
            for (const k in orderTable.data[v].order_goods) {
                multipleTable.value[v].clearSelection()
            }
        }
    }
}

interface orderTableType {
    page: number
    limit: number
    total: number
    loading: boolean
    data: any[]
    searchParam: {
        order_refund_no: string
        create_time: string[]
        status: string
    }
}

const orderTable = reactive<orderTableType>({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        order_refund_no: '',
        create_time: [],
        status: ''
    }
})

const searchFormRef = ref<FormInstance>()

/**
 * 获取订单列表
 */
const loadOrderList = (page: number = 1) => {
    orderTable.loading = true
    orderTable.page = page

    orderRefund({
        page: orderTable.page,
        limit: orderTable.limit,
        ...orderTable.searchParam
    }).then(res => {
        orderTable.loading = false
        orderTable.data = res.data.data.map((el: any) => {
            el.order_goods = [el.order_goods]
            return el
        })
        orderTable.total = res.data.total
        setTablePageStorage(orderTable.page, orderTable.limit, orderTable.searchParam);
    }).catch(() => {
        orderTable.loading = false
    })
}
loadOrderList(getTablePageStorage(orderTable.searchParam).page)

const handleClick = (event: string) => {
    orderTable.searchParam.status = event
    loadOrderList()
}

/**
 * 订单导出
 */
const exportSureDialog = ref(null)
const flag = ref(false)
const handleClose = (val) => {
    flag.value = val
}
const exportEvent = (data: any) => {
    flag.value = true
}

// 订单详情
const detailEvent = (data: any) => {
    let parameter = { id: data.refund_id };
    refundDetailDialog.value.setFormData(parameter);
    refundDetailDialog.value.showDialog = true;
}

const memberEvent = (id: number) => {
    const routeUrl = router.resolve({
        path: '/member/detail',
        query: { id }
    })
    window.open(routeUrl.href, '_blank')
}

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    loadOrderList()
}
</script>

<style lang="scss" scoped>
.table-top :deep(.el-table__body-wrapper) {
    display: none;
}

:deep(.el-table) {
    --el-table-row-hover-bg-color: var(--el-transfer-border-color);
}

/* 多行超出隐藏 */
.multi-hidden {
    word-break: break-all;
    text-overflow: ellipsis;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
</style>

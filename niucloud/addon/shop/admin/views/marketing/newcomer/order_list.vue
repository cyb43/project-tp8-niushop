<template>
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">

            <div class="flex justify-between items-center">
                <span class="text-page-title">{{ pageName }}</span>
            </div>

            <el-card class="box-card !border-none my-[10px] table-search-wrap" shadow="never">
                <el-form :inline="true" :model="orderTable.searchParam" ref="searchFormRef">
                    <el-form-item :label="t('orderInfo')" prop='search_name'>
                        <el-select v-model="orderTable.searchParam.search_type" clearable class="input-item">
                            <el-option :label="t('orderNo')" value="order_no"></el-option>
                            <el-option :label="t('outTradeNo')" value="out_trade_no"></el-option>
                        </el-select>
                        <el-input class="input-item ml-3" v-model.trim="orderTable.searchParam.search_name" />
                    </el-form-item>
                    <el-form-item :label="t('payType')" prop='pay_type'>
                        <el-select v-model="orderTable.searchParam.pay_type" clearable class="input-item">
                            <el-option v-for="(item, index) in payTypeData" :key="index" :label="item.name" :value="item.key"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item :label="t('fromType')" prop='order_from'>
                        <el-select v-model="orderTable.searchParam.order_from" clearable class="input-item">
                            <el-option v-for="(item, index) in orderFromData" :key="index" :label="item" :value="index"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item :label="t('createTime')" prop="create_time">
                        <el-date-picker v-model="orderTable.searchParam.create_time" type="datetimerange"
                            value-format="YYYY-MM-DD HH:mm:ss" :start-placeholder="t('startDate')"
                            :end-placeholder="t('endDate')" />
                    </el-form-item>
                    <el-form-item :label="t('payTime')" prop="pay_time">
                        <el-date-picker v-model="orderTable.searchParam.pay_time" type="datetimerange"
                            value-format="YYYY-MM-DD HH:mm:ss" :start-placeholder="t('startDate')"
                            :end-placeholder="t('endDate')" />
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="loadOrderList()">{{ t('search') }}</el-button>
                        <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
                    </el-form-item>
                </el-form>
            </el-card>

            <el-tabs v-model="activeName" class="demo-tabs" @tab-change="handleClick">
                <el-tab-pane :label="t('all')" name=""></el-tab-pane>
                <el-tab-pane :label="t('toBeShipped')" name="2"></el-tab-pane>
                <el-tab-pane :label="t('shipped')" name="3"></el-tab-pane>
                <el-tab-pane :label="t('completed')" name="5"></el-tab-pane>
                <el-tab-pane :label="t('closed')" name="-1"></el-tab-pane>
            </el-tabs>
            <div>
                <el-table :data="orderTable.data" size="large" class="table-top">
                    <el-table-column :label="t('orderGoods')" min-width="200" />
                    <el-table-column :label="t('goodsPriceNumber')" min-width="150" />
                    <el-table-column :label="t('orderMoney')" min-width="150" />
                    <el-table-column :label="t('orderStatus')" min-width="100" />
                    <el-table-column :label="t('operation')" fixed="right" align="right" min-width="100" />
                </el-table>
            </div>
            <div class="table-body min-h-[150px]" v-loading="orderTable.loading">
                <div v-if="!orderTable.loading">
                    <template v-if="orderTable.data.length">
                        <div v-for="(item, index) in orderTable.data" :key="index">
                            <div class="flex items-center justify-between bg-[#f7f8fa] mt-[10px] border-[#e4e7ed] border-solid border-b-[1px] px-3 h-[35px] text-[12px] text-[#666]">
                                <div>
                                    <span>{{ t('orderNo') }}：{{ (item as any).order_no }}</span>
                                    <span class="ml-5">{{ t('createTime') }}：{{ (item as any).create_time }}</span>
                                    <!-- <span class="ml-5">{{ t('orderFrom') }}：{{ (item as any).order_form_name }}</span> -->
                                    <span class="ml-5" v-if="item.pay">{{ t('payType') }}：{{ (item as any).pay.type_name }}</span>
                                </div>
                            </div>
                            <el-table :data="item.order_goods" size="large" :show-header="false" :span-method="arraySpanMethod">
                                <template #empty>
                                    <span>{{ !orderTable.loading ? t('emptyData') : '' }}</span>
                                </template>

                                <!-- <el-table-column :label="t('orderNo')" min-width="250">
                                    <template #default="{ row }">
                                        <span class="text-[14px]">{{ row.order_no }}</span>
                                    </template>
                                </el-table-column> -->
                                <el-table-column min-width="200">
                                    <template #default="{ row }">
                                        <div class="flex cursor-pointer"  @click="detailEvent(row)">
                                            <div class="flex items-center min-w-[50px] mr-[10px]">
                                                <img class="w-[50px] h-[50px]" v-if="row.goods_image_thumb_small" :src="img(row.goods_image_thumb_small)" alt="">
                                                <img class="w-[50px] h-[50px]" v-else src="@/addon/shop/assets/goods_default.png" alt="">
                                            </div>
                                            <div class="flex flex-col">
                                                <p class="multi-hidden text-[14px]">{{ row.goods_name }}</p>
                                                <span class="text-[12px] text-[#999]">{{ row.sku_name }}</span>
                                            </div>
                                        </div>
                                    </template>
                                </el-table-column>
                                <el-table-column min-width="150">
                                    <template #header>
                                        <div style="display: inline-flex; align-items: center">
                                            <span class="mr-[5px]">{{ t('goodsPriceNumber') }}</span>
                                            <el-tooltip class="box-item" effect="light" placement="top">
                                                <template #content>{{t('goodsPriceNumberTips')}}</template>
                                                <el-icon color="#666">
                                                    <QuestionFilled />
                                                </el-icon>
                                            </el-tooltip>
                                        </div>
                                    </template>
                                    <template #default="{ row }">
                                        <div class="flex flex-col">
                                            <span class="text-[13px]">￥{{ row.price }}</span>
                                            <span v-if="row.extend && row.extend.newcomer_price" class="text-[13px] mt-[5px]">
                                                <span v-if="parseFloat(row.extend.newcomer_price) && row.num > 1">{{ row.num }}{{ t('piece') }}<span class="text-[#999]">（第1{{ t('piece') }}，￥{{parseFloat(row.extend.newcomer_price).toFixed(2)}}/{{ t('piece') }}；第{{row.num>2?'2~'+row.num:'2'}}{{ t('piece') }}，￥{{parseFloat(row.price).toFixed(2)}}/{{ t('piece') }}）</span></span>
                                                <span v-else>{{ row.num }}{{ t('piece') }}</span>
                                            </span>
                                            <span v-else class="text-[13px] mt-[5px]">{{ row.num }}{{ t('piece') }}</span>
                                        </div>
                                    </template>
                                </el-table-column>
                                <el-table-column :label="t('orderMoney')" min-width="150" class-name="border-0 border-l-[1px] border-solid border-[var(--el-table-border-color)]">
                                    <template #default="{ row }">
                                        <span class="text-[14px]">￥{{ item.order_money }}</span>
                                    </template>
                                </el-table-column>
                                <el-table-column :label="t('orderStatus')" min-width="100">
                                    <template #default="{ row }">
                                        <span class="text-[14px]">{{ item.status_name.name }}</span>
                                    </template>
                                </el-table-column>
                                <el-table-column :label="t('operation')" fixed="right" align="right" min-width="100">
                                    <template #default="{ row }">
                                        <el-button type="primary" link @click="detailEvent(item)">{{ t('info') }}</el-button>
                                    </template>
                                </el-table-column>
                            </el-table>
                        </div>
                    </template>
                </div>
            </div>
            <div class="mt-[16px] flex justify-end">
                <el-pagination v-model:current-page="orderTable.page" v-model:page-size="orderTable.limit"
                    layout="total, sizes, prev, pager, next, jumper" :total="orderTable.total"
                    @size-change="loadOrderList()" @current-change="loadOrderList" />
            </div>

        </el-card>
    </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import { getOrderList, getOrderStatus, getOrderPayType, getOrderFrom } from '@/addon/shop/api/order'
import { img,setTablePageStorage,getTablePageStorage } from '@/utils/common'
import { FormInstance } from 'element-plus'
import { useRouter, useRoute } from 'vue-router'

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title

const activeName = ref('')
const statusData = ref([])
const payTypeData = ref<any[]>([])
const orderFromData = ref([])
const setFormData = async () => {
    statusData.value = await (await getOrderStatus()).data
    payTypeData.value = await (await getOrderPayType()).data
    orderFromData.value = await (await getOrderFrom()).data
}

setFormData()

interface OrderTable {
    page: number
    limit: number
    total: number
    loading: boolean
    data: any[]
    searchParam: any,
}
const orderTable = reactive<OrderTable>({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        search_type: 'order_no',
        search_name: '',
        pay_type: '',
        order_from: '',
        status: '',
        create_time: [],
        pay_time: [],
        activity_type:'newcomer_discount'
    }
})

const searchFormRef = ref<FormInstance>()

/**
 * 获取订单列表
 */
const loadOrderList = (page: number = 1) => {
    orderTable.loading = true
    orderTable.page = page

    getOrderList({
        page: orderTable.page,
        limit: orderTable.limit,
        ...orderTable.searchParam
    }).then(res => {
        orderTable.loading = false
        orderTable.data = res.data.data.map((el: any) => {
            el.order_goods.forEach((v: any) => {
                v.rowNum = el.order_goods.length
            })
            return el
        })
        orderTable.total = res.data.total
        setTablePageStorage(orderTable.page, orderTable.limit, orderTable.searchParam);
    }).catch(() => {
        orderTable.loading = false
    })
}
loadOrderList(getTablePageStorage(orderTable.searchParam).page);
// 合并表格行
const arraySpanMethod = ({
    row,
    column,
    rowIndex,
    columnIndex
}:any) => {
    if (rowIndex === 0) {
        if (columnIndex > 1) {
            return [row.rowNum, 1]
        } else {
            return [1, 1]
        }
    } else {
        if (columnIndex > 1) {
            return [0, 0]
        } else {
            return [1, 1]
        }
    }
}
const handleClick = (event: any) => {
    orderTable.searchParam.status = event
    loadOrderList()
}

// 订单详情
const detailEvent = (data: any) => {
    const url = router.resolve({
        path: '/shop/order/detail',
        query: {
            order_id: data.order_id
        }
    })
    window.open(url.href)
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
.input-item {
    width: 150px !important;
}
</style>

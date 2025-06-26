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
                            <el-option :label="t('goodsName')" value="goods_name"></el-option>
                        </el-select>
                        <el-input class="input-item ml-3" v-model.trim="orderTable.searchParam.search_name" />
                    </el-form-item>
                    <el-form-item :label="t('memberInfo')" prop='keyword'>
                        <el-input class="w-[200px]" v-model.trim="orderTable.searchParam.keyword" :placeholder="t('memberInfoPlaceholder')" />
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
                        <el-date-picker v-model="orderTable.searchParam.create_time" type="datetimerange" value-format="YYYY-MM-DD HH:mm:ss" :start-placeholder="t('startDate')" :end-placeholder="t('endDate')" />
                    </el-form-item>
                    <el-form-item :label="t('payTime')" prop="pay_time">
                        <el-date-picker v-model="orderTable.searchParam.pay_time" type="datetimerange" value-format="YYYY-MM-DD HH:mm:ss" :start-placeholder="t('startDate')" :end-placeholder="t('endDate')" />
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="loadOrderList()">{{ t('search') }}</el-button>
                        <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
                        <el-button type="primary" @click="exportSelectEvent">{{ t('export') }}</el-button>
                    </el-form-item>
                </el-form>
            </el-card>
            <el-tabs v-model="activeName" class="demo-tabs" @tab-change="handleClick">
                <el-tab-pane :label="t('all')" name=""></el-tab-pane>
                <el-tab-pane :label="t('toBePaid')" name="1"></el-tab-pane>
                <el-tab-pane :label="t('toBeShipped')" name="2"></el-tab-pane>
                <el-tab-pane :label="t('shipped')" name="3"></el-tab-pane>
                <el-tab-pane :label="t('completed')" name="5"></el-tab-pane>
                <el-tab-pane :label="t('closed')" name="-1"></el-tab-pane>
            </el-tabs>
            <div>
                <!-- todo 后续完善，增加批量发货，再修改判断逻辑 -->
                <div class="mb-[10px] flex items-center" v-if="activeName == 3">
                    <el-button @click="batchPrintElectronicSheet" size="small" v-if="activeName == 3">
                        {{ t('batchPrintElectronicSheet') }}
                    </el-button>
                </div>
                <el-table :data="orderTable.data" size="large" class="table-top" @select-all="selectAllCheck">
                    <el-table-column type="selection" width="40" />
                    <el-table-column :label="t('orderGoods')" min-width="200" />
                    <el-table-column :label="t('goodsPriceNumber')" min-width="120" />
                    <el-table-column :label="t('rightsProtection')" min-width="120" />
                    <el-table-column :label="t('orderMoney')" min-width="120" />
                    <el-table-column :label="t('buyInfo')" min-width="120" />
                    <el-table-column :label="t('deliveryType')" min-width="100" />
                    <el-table-column :label="t('orderStatus')" min-width="100" />
                    <el-table-column :label="t('operation')" fixed="right" align="right" min-width="120" />
                </el-table>
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
                                        <span class="ml-5" v-if="item.delivery_type =='store' && item.buyer_ask_delivery_time">{{ t('buyerAskDeliveryTime') }}：：{{ (item as any).buyer_ask_delivery_time }}</span>
                                    </div>
                                    <div>
                                        <!-- <el-button type="primary" link>{{ t('offlinePayment') }}</el-button> -->
                                        <el-button type="primary" link @click="printTicketEvent(item)" v-if="item.isSupportPrintTicket">{{ t('printTicket') }}</el-button>
                                        <el-button type="primary" link @click="openElectronicSheetPrintDialog(item)" v-if="item.isSupportElectronicSheet">{{ t('electronicSheetPrintTitle') }}</el-button>
                                        <el-button type="primary" link @click="detailEvent(item)">{{ t('info') }}</el-button>
                                        <el-button type="primary" link @click="setNotes(item)">{{ t('notes') }}</el-button>
                                    </div>
                                </div>

                                <el-table :data="item.order_goods" size="large" :show-header="false" :span-method="arraySpanMethod" ref="multipleTable" @select="handleSelectChange">
                                    <el-table-column type="selection" width="40" />
                                    <el-table-column align="left" min-width="200">
                                        <template #default="{ row }">
                                            <div class="flex cursor-pointer">
                                                <div class="flex items-center min-w-[50px] mr-[10px]">
                                                    <img class="w-[50px] h-[50px]" v-if="row.goods_image" :src="img(row.goods_image)" alt="" />
                                                    <img class="w-[50px] h-[50px]" v-else src="" alt="" />
                                                </div>
                                                <div class="flex flex-col items-start">
                                                    <el-tooltip class="box-item" effect="light" placement="top">
                                                        <template #content>
                                                            <div class="max-w-[250px]">{{ row.goods_name }}</div>
                                                        </template>
                                                        <p class="multi-hidden text-[14px]">{{ row.goods_name }}</p>
                                                    </el-tooltip>
                                                    <span class="text-[12px] text-[#999] truncate">{{ row.sku_name }}</span>
                                                    <span class="px-[4px]  text-[12px] text-[#fff] rounded-[4px] bg-primary leading-[18px]" v-if="row.is_gift == 1">赠品</span>
                                                </div>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column min-width="120">
                                        <template #default="{ row }">
                                            <div class="flex flex-col">
                                                <span v-if="item.activity_type == 'exchange'">{{ row.extend.point }}{{ t('point') }}<span v-if="parseFloat(row.price)">+￥{{ row.price }}</span></span>
                                                <span v-else-if="row.impulse_buy_info && row.impulse_buy_info.is_impulse_buy">￥{{ parseFloat(row.impulse_buy_info.impulse_buy_price).toFixed(2) }}</span>
                                                <span v-else class="text-[13px]">￥{{ row.price }}</span>
                                                <span class="text-[13px] mt-[5px]">{{ row.num }}{{ t('piece') }}</span>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column min-width="120">
                                        <template #default="{ row }">
                                            <div class="flex flex-col cursor-pointer">
                                                <span>{{ row.status_name }}</span>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column min-width="120" class-name="border-0 border-l-[1px] border-solid border-[var(--el-table-border-color)]">
                                        <template #default>
                                            <div v-if="item.activity_type == 'exchange'" class="text-[14px]">
                                                {{ item.point }}{{ t('point') }}
                                                <span v-if="parseFloat(item.order_money)">+￥{{ item.order_money }}</span>
                                            </div>
                                            <span v-else class="text-[14px]">￥{{ item.order_money }}</span>
                                            <div v-if="item.pay">{{ item.member_id !== item.pay.main_id && item.pay.status == 2 ? item.pay.pay_type_name : '' }}</div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column min-width="120">
                                        <template #default>
                                            <div class="flex flex-col">
                                                <span class="text-[12px] text-primary cursor-pointer" @click="memberEvent(item.member.member_id)">{{ item.member.nickname }}</span>
                                                <span class="text-[12px] mt-[5px]">{{ item.taker_name }} {{ item.taker_mobile }}</span>
                                                <span class="text-[12px] mt-[5px]">{{ item.taker_full_address }}</span>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column min-width="100">
                                        <template #default>
                                            <span class="text-[14px]">{{ item.delivery_type_name }}</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column min-width="100">
                                        <template #default>
                                            <span class="text-[14px]">{{ item.status_name.name }}</span>
                                        </template>
                                    </el-table-column>
                                    <el-table-column align="right" min-width="120">
                                        <template #default>
                                            <template v-if="item.status == 1">
                                                <el-button type="primary" link @click="close(item)">{{ t('orderClose') }}</el-button>
                                                <el-button type="primary" link @click="orderAdjustMoney(item)">{{ t('editPrice') }}</el-button>
                                            </template>
                                            <el-button type="primary" v-if="(item.status == 2 || item.status == 1) && item.delivery_type != 'virtual' && item.delivery_type!='store' && item.activity_type != 'giftcard'" link @click="orderEditAddressFn(item)">{{ t('editAddress') }}</el-button>
                                            <el-button type="primary" link @click="delivery(item)" v-if="item.status == 2 && item.delivery_type!='store'">{{ t('sendOutGoods') }}</el-button>
                                            <el-button type="primary" link @click="finish(item)" v-if="item.status == 3">{{ t('confirmTakeDelivery') }}</el-button>
                                            <el-button type="primary" v-if="item.is_refund_show && item.status != 1 && item.status != -1" link @click="refundEvent(item)">{{ t('voluntaryRefund') }}</el-button>
                                        </template>
                                    </el-table-column>
                                </el-table>
                                <div v-if="item.shop_remark" class="text-[14px] min-h-[30px] leading-[30px] px-3 bg-[#fff0e5] text-[#ff7f5b]">
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

        <adjust-money ref="orderAdjustMoneyActionDialog" @complete="loadOrderList" />
        <delivery-action ref="deliveryActionDialog" @complete="loadOrderList" />
        <order-notes ref="orderNotesDialog" @complete="loadOrderList" />
        <order-export-select ref="selectExportDialog" @complete="exportEvent" />
        <export-sure ref="exportSureDialog" :show="flag" :type="export_type" :searchParam="orderTable.searchParam" @close="handleClose" />
        <order-edit-address ref="orderEditAddressDialog" @complete="loadOrderList" />
        <electronic-sheet-print ref="electronicSheetPrintDialog" @complete="electronicSheetPrintComplete" />
        <shop-active-refund ref="shopActiveRefundDialog" @complete="loadOrderList" />
    </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import {
    getOrderList,
    getOrderStatus,
    orderClose,
    orderFinish,
    getOrderPayType,
    getOrderFrom
} from '@/addon/shop/api/order'
import { printTicket } from '@/app/api/printer'
import DeliveryAction from '@/addon/shop/views/order/components/delivery-action.vue'
import OrderNotes from '@/addon/shop/views/order/components/order-notes.vue'
import OrderExportSelect from '@/addon/shop/views/order/components/order-export-select.vue'
import orderEditAddress from '@/addon/shop/views/order/components/order-edit-address.vue'
import AdjustMoney from '@/addon/shop/views/order/components/adjust-money.vue'
import ShopActiveRefund from '@/addon/shop/views/order/components/shop-active-refund.vue'
import electronicSheetPrint from '@/addon/shop/views/order/components/electronic-sheet-print.vue'
import { img } from '@/utils/common'
import { ElMessage, ElMessageBox, FormInstance } from 'element-plus'
import { useRouter, useRoute } from 'vue-router'
import { cloneDeep } from 'lodash-es'
import { setTablePageStorage, getTablePageStorage } from '@/utils/common'

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title
const activeName: any = ref(route.query.status || '')

const statusData = ref([])
const payTypeData = ref<any[]>([])
const orderFromData = ref([])

const setFormData = async() => {
    statusData.value = await (await getOrderStatus()).data
    payTypeData.value = await (await getOrderPayType()).data
    orderFromData.value = await (await getOrderFrom()).data
}
setFormData()

const multipleSelection: any = reactive({}) // 选中数据
const multipleTable: Record<string, any> | null = ref(null)
const isSelectAll = ref(false)
const selectAllCheck = () => {
    if (!isSelectAll.value) {
        isSelectAll.value = true
        for (const i in orderTable.data) {
            let isAdd = false
            for (const j in orderTable.data[i].order_goods) {
                // 存在一个没有退款的订单项就设为选中状态
                if (orderTable.data[i].order_goods[j].status == 1) {
                    multipleTable.value[i].toggleRowSelection(orderTable.data[i].order_goods[j], true)
                    isAdd = true
                }
            }
            if (isAdd) {
                multipleSelection['order_' + orderTable.data[i].order_id] = cloneDeep(orderTable.data[i])
            }
        }
    } else {
        isSelectAll.value = false
        for (const v in orderTable.data) {
            multipleTable.value[v].clearSelection()
            delete multipleSelection['order_' + orderTable.data[v].order_id]
        }
    }
}

// 监听表格复选框
const handleSelectChange = (selection: any, row: any) => {
    // 是否选中
    let isSelected = false
    let item: any = null

    for (let i = 0; i < orderTable.data.length; i++) {
        if (orderTable.data[i].order_id == row.order_id) {
            item = orderTable.data[i]
            break
        }
    }

    for (let i = 0; i < selection.length; i++) {
        if (selection[i].order_id == row.order_id) {
            isSelected = true
            break
        }
    }

    if (isSelected) {
        multipleSelection['order_' + row.order_id] = item
    } else {
        // 未选中，删除当前商品
        delete multipleSelection['order_' + row.order_id]
    }
}

const orderTable: any = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        search_type: 'order_no',
        search_name: '',
        keyword: '',
        pay_type: '',
        order_from: '',
        status: route.query.status || '',
        create_time: [],
        pay_time: []
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
            el.isSupportElectronicSheet = false // 是否支持打印电子面单
            el.isSupportPrintTicket = false // 是否支持打印小票

            // 只有待发货、待收货，物流配送的情况下才能打印电子面单
            if (el.delivery_type == 'express' && el.status == 3) {
                el.isSupportElectronicSheet = true
            }

            //  待发货、待收货、已完成状态下可以打印小票
            if (el.delivery_type != 'virtual' && (el.status == 2 || el.status == 3 || el.status == 5)) {
                el.isSupportPrintTicket = true
            }
            el.order_goods.forEach((v: any) => {
                v.rowNum = el.order_goods.length
            })
            return el
        })

        // 处理主力退款按钮是否出现
        orderTable.data.forEach((item: any, index: number, arr: any) => {
            let refundOrderNum = 0
            item.order_goods.forEach((orderItem: any, orderIndex: number) => {
                if (orderItem.is_enable_refund == 1) {
                    refundOrderNum++
                }
            })
            arr[index].is_refund_show = refundOrderNum > 0 ? true : false;
        })
        orderTable.total = res.data.total
        setTablePageStorage(orderTable.page, orderTable.limit, orderTable.searchParam);
    }).catch(() => {
        orderTable.loading = false
    })
}

loadOrderList(getTablePageStorage(orderTable.searchParam).page)

const handleClick = (event: any) => {
    orderTable.searchParam.status = event
    isSelectAll.value = false
    for (let key in multipleSelection) {
        delete multipleSelection[key]
    }
    loadOrderList()
}

// 合并表格行
const arraySpanMethod = ({ row, column, rowIndex, columnIndex }: any) => {
    if (rowIndex === 0) {
        if (columnIndex === 0) {
            return [row.rowNum, 1]
        } else if (columnIndex > 3) {
            return [row.rowNum, 1]
        } else {
            return [1, 1]
        }
    } else {
        if (columnIndex === 0) {
            return [0, 0]
        } else if (columnIndex > 3) {
            return [0, 0]
        } else {
            return [1, 1]
        }
    }
}

/**
 * 订单导出
 */
const exportSureDialog = ref(null)
const export_type = ref('')
const flag = ref(false)
const handleClose = (val: any) => {
    flag.value = val
}
const exportEvent = (data: any) => {
    export_type.value = data
    flag.value = true
}

const selectExportDialog: Record<string, any> | null = ref(null)

/**
 * 订单导出类型选择
 */
const exportSelectEvent = () => {
    selectExportDialog.value.showDialog = true
}

// 订单详情
const detailEvent = (data: any) => {
    router.push('/shop/order/detail?order_id=' + data.order_id)
}

const memberEvent = (id: number) => {
    const routeUrl = router.resolve({
        path: '/member/detail',
        query: { id }
    })
    window.open(routeUrl.href, '_blank')
}

const close = (data: any) => {
    ElMessageBox.confirm(t('orderCloseTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        orderClose(data.order_id).then(() => {
            loadOrderList()
        })
    })
}

// 订单调整价格
const orderAdjustMoneyActionDialog: Record<string, any> | null = ref(null)
const orderAdjustMoney = (data: any) => {
    orderAdjustMoneyActionDialog.value.setFormData(data)
    orderAdjustMoneyActionDialog.value.showDialog = true
}

const deliveryActionDialog: Record<string, any> | null = ref(null)
/**
 * 发货
 */
const delivery = (data: any) => {
    deliveryActionDialog.value.setFormData(data)
    deliveryActionDialog.value.showDialog = true
}

const orderNotesDialog: Record<string, any> | null = ref(null)
/**
 * 设置备注
 */
const setNotes = (data: any) => {
    orderNotesDialog.value.setFormData(data)
    orderNotesDialog.value.showDialog = true
}

// 订单完成
const finish = (data: any) => {
    ElMessageBox.confirm(t('orderFinishTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        orderFinish(data.order_id).then(() => {
            loadOrderList()
        })
    })
}

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    loadOrderList()
}

/**
 * 修改地址
 */
const orderEditAddressDialog: Record<string, any> | null = ref(null)
const orderEditAddressFn = async(data: any) => {
    orderEditAddressDialog.value.showDialog = true
    orderEditAddressDialog.value.setFormData(data)
}

// 打印电子面单
const electronicSheetPrintDialog: Record<string, any> | null = ref(null)

// 单个订单打印电子面单
const openElectronicSheetPrintDialog = (data: any) => {
    let formData = cloneDeep(data)
    formData.print_type = 'single'
    electronicSheetPrintDialog.value.setFormData(formData)
    electronicSheetPrintDialog.value.showDialog = true
}

// 批量打印电子面单
const batchPrintElectronicSheet = () => {
    let noSupportCount = 0
    let order_ids = []
    for (let key in multipleSelection) {
        if (multipleSelection[key].isSupportElectronicSheet) {
            order_ids.push(multipleSelection[key].order_id)
        } else {
            noSupportCount++
        }
    }

    if (noSupportCount && order_ids.length == 0) {
        ElMessage({
            type: 'warning',
            message: `${ t('notSupportPrintElectronicSheetTips') }`
        })
        return
    }

    if (order_ids.length == 0) {
        ElMessage({
            type: 'warning',
            message: `${ t('batchEmptySelectedOrderTips') }`
        })
        return
    }

    electronicSheetPrintDialog.value.setFormData({
        order_id: order_ids.toString(),
        print_type: 'multiple'
    })
    electronicSheetPrintDialog.value.showDialog = true
}

// 电子面单完成事件
const electronicSheetPrintComplete = () => {
    isSelectAll.value = false
    for (const v in orderTable.data) {
        multipleTable.value[v].clearSelection()
        delete multipleSelection['order_' + orderTable.data[v].order_id];
    }
}

const repeat = ref(false)

/**
 * 打印小票
 */
const printTicketEvent = (data: any) => {
    if (repeat.value) return
    repeat.value = true

    printTicket({
        type: 'shopGoodsOrder', // 小票模板类型
        trigger: 'manual', // 触发时机：手动触发
        // 业务参数，根据自身业务传值
        business: {
            order_id: data.order_id
        }
    }).then((res: any) => {
        repeat.value = false
    }).catch(() => {
        repeat.value = false
    })
}

/**
 * 商家主动退款
 */
const shopActiveRefundDialog: Record<string, any> | null = ref(null)
const refundEvent = (data: any) => {
    shopActiveRefundDialog.value.setFormData(data)
    shopActiveRefundDialog.value.showDialog = true
}

</script>

<style lang="scss" scoped>
.table-top :deep(.el-table__body-wrapper) {
    display: none;
}

.input-item {
    width: 150px !important;
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

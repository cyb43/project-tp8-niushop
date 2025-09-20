<template>
    <el-drawer v-model="showDialog" :title="popTitle" direction="rtl" :before-close="handleClose" class="detail-drawer">
        <div class="main-container" v-loading="loading">
            <el-tabs v-model="activeName" class="pb-[10px]" @tab-change="tabChange">
                <el-tab-pane label="订单信息" name="order" />
                <el-tab-pane label="订单状态" name="state" />
                <el-tab-pane label="商品信息" name="goods" />
                <el-tab-pane v-if="formData && formData.order_log && formData.order_log.length > 0" label="订单日志" name="log" />
            </el-tabs>

            <el-form :model="formData" label-width="100px" ref="formRef" class="page-form" v-if="!loading" label-position="left">
                <div v-if="activeName == 'order'">
                    <el-row class="row-bg px-[30px] mb-[20px]" :gutter="20">
                        <el-col :span="8">
                            <el-form-item :label="t('orderDetailNo')">
                                <div class="input-width">{{ formData.order_no }}</div>
                            </el-form-item>
                            <el-form-item :label="t('orderDetailForm')">
                                <div class="input-width">{{ formData.order_from_name }}</div>
                            </el-form-item>
                            <el-form-item :label="t('orderDetailoutTradeNo')" v-if="formData && formData.out_trade_no">
                                <div class="input-width">{{ formData.out_trade_no }}</div>
                            </el-form-item>
                            <el-form-item :label="t('payWay')" v-if="formData.pay">
                                <div class="input-width">{{ formData.pay.type_name }}</div>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item :label="t('deliveryType')">
                                <div class="input-width">{{ formData.delivery_type_name }}</div>
                            </el-form-item>
                            <div v-if="formData.delivery_type == 'express' || formData.delivery_type == 'local_delivery'">
                                <el-form-item :label="t('orderDetailTakerName')">
                                    <div class="input-width">{{ formData.taker_name }}</div>
                                </el-form-item>
                                <el-form-item :label="t('orderDetailTakerMobile')">
                                    <div class="input-width">{{ formData.taker_mobile }}</div>
                                </el-form-item>
                                <el-form-item :label="t('orderDetailTakerFullAddress')">
                                    <div class="input-width">{{ formData.taker_full_address }}</div>
                                </el-form-item>
                            </div>
                            <div v-if="formData.delivery_type == 'store'">
                                <el-form-item :label="t('storeName')">
                                    <div class="input-width">{{ formData.store.store_name }}</div>
                                </el-form-item>
                                <el-form-item :label="t('storeAddress')">
                                    <div class="input-width">{{ formData.store.full_address }}</div>
                                </el-form-item>
                                <el-form-item :label="t('storeMobile')">
                                    <div class="input-width">{{ formData.store.store_mobile }}</div>
                                </el-form-item>
                                <el-form-item :label="t('tradeTime')">
                                    <div class="input-width">{{ formData.store.trade_time }}</div>
                                </el-form-item>
                            </div>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item :label="t('memberRemark')">
                                <div class="input-width line-feed">{{ formData.member_remark ?? '--' }}</div>
                            </el-form-item>
                            <el-form-item :label="t('notes')">
                                <div class="input-width line-feed">{{ formData.shop_remark ?? '--' }}</div>
                            </el-form-item>
                        </el-col>
                    </el-row>
                </div>
                <div v-if="activeName == 'state'">
                    <div class="mb-[20px]">
                        <p>
                            <span class="ml-[30px] text-[14px] mr-[20px]">{{ t('orderStatus') }}：</span>
                            <span class="text-[14px]">{{ formData.status_name.name }}</span>
                        </p>
                        <div class="flex mt-[10px]">
                            <span class="text-[14px] px-[15px] py-[5px] ml-[30px] text-[#ff7f5b] bg-[#fff0e5] cursor-pointer" @click="setNotes">{{ t('notes') }}</span>
                            <span class="text-[14px] px-[15px] py-[5px] ml-[30px] text-[#5c96fc] bg-[#ebf3ff] cursor-pointer" @click="delivery" v-if="formData.status == 2">{{ t('delivery') }}</span>
                            <span class="text-[14px] px-[15px] py-[5px] ml-[30px] text-[#5c96fc] bg-[#ebf3ff] cursor-pointer" @click="close" v-if="formData.status == 1">{{ t('close') }}</span>
                            <span class="text-[14px] px-[15px] py-[5px] ml-[30px] text-[#5c96fc] bg-[#ebf3ff] cursor-pointer" @click="orderAdjustMoney" v-if="formData.status == 1">{{ t('editPrice') }}</span>
                            <span class="text-[14px] px-[15px] py-[5px] ml-[30px] text-[#5c96fc] bg-[#ebf3ff] cursor-pointer" @click="finish" v-if="formData.status == 3">{{ t('finish') }}</span>
                            <span class="text-[14px] px-[15px] py-[5px] ml-[30px] text-[#5c96fc] bg-[#ebf3ff] cursor-pointer"
                                @click="openElectronicSheetPrintDialog"
                                v-if="formData.delivery_type == 'express' && formData.status == 3">{{ t('electronicSheetPrintTitle') }}</span>
                            <span class="text-[14px] px-[15px] py-[5px] ml-[30px] text-[#5c96fc] bg-[#ebf3ff] cursor-pointer"
                                @click="printTicketEvent"
                                v-if="formData.delivery_type == 'virtual' && (formData.status == 2 || formData.status == 3 || formData.status == 5)">{{ t('printTicket') }}</span>
                            <span class="text-[14px] px-[15px] py-[5px] ml-[30px] text-[#5c96fc] bg-[#ebf3ff] cursor-pointer"
                                @click="orderEditAddressFn"
                                v-if="formData.status == 1 && formData.delivery_type != 'virtual' && formData.activity_type != 'giftcard'">{{ t('editAddress') }}</span>
                            <span class="text-[14px] px-[15px] py-[5px] ml-[30px] text-[#5c96fc] bg-[#ebf3ff] cursor-pointer"
                                @click="refundEvent"
                                v-if="formData.is_refund_show && formData.status != 1 && formData.status != -1">{{ t('voluntaryRefund') }}</span>
                            <div class="flex" v-if="formData.order_delivery">
                                <template v-for="(item, index) in formData.order_delivery" :key="index">
                                    <span v-if="item.delivery_type == 'express' && item.sub_delivery_type == 'express'"
                                          class="text-[14px] px-[15px] py-[5px] ml-[30px] text-[#ff7f5b] bg-[#fff0e5] cursor-pointer"
                                          @click="packageEvent(item.id, formData.taker_mobile)">{{ t('package') }}{{ index + 1 }}</span>
                                    <span v-if="item.delivery_type == 'express' && item.sub_delivery_type == 'none_express'"
                                        class="text-[14px] px-[15px] py-[5px] ml-[30px] text-[#ff7f5b] bg-[#fff0e5] cursor-pointer"
                                        @click="packageEvent(item.id, formData.taker_mobile)">{{ t('noLogisticsRequired') }}</span>
                                </template>
                            </div>
                        </div>
                        <div class="flex ml-[30px] mt-[15px]">
                            <span class="text-[14px] text-[#ff7f5b]">{{ t('remind') }}：</span>
                            <div class="ml-[10px]">
                                <p class="text-[14px] text-[#a4a4a4]">{{ t('remindTips1') }}</p>
                                <p class="text-[14px] text-[#a4a4a4]">{{ t('remindTips2') }}</p>
                                <p class="text-[14px] text-[#a4a4a4]">{{ t('remindTips3') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="activeName == 'goods'">
                    <el-table :data="formData.order_goods" size="large">
                        <el-table-column :label="t('orderDetailGoodsName')" align="left" width="300">
                            <template #default="{ row }">
                                <div class="flex">
                                    <div class="flex items-center shrink-0">
                                        <img class="w-[50px] h-[50px] mr-[10px]" :src="img(row.goods_image)" />
                                    </div>
                                    <div class="flex flex-col items-start">
                                        <p class="multi-hidden text-[14px]">{{ row.goods_name }}</p>
                                        <span class="text-[12px] text-[#999]">{{ row.sku_name }}</span>
                                        <span class="px-[4px]  text-[12px] text-[#fff] rounded-[4px] bg-primary leading-[18px]" v-if="row.is_gift == 1">赠品</span>
                                    </div>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column :label="t('orderDetailPrice')" min-width="50" align="left">
                            <template #default="{ row }">
                                <div class="flex flex-col">
                                    <span v-if="formData.activity_type == 'exchange'">{{ row.extend.point }}{{ t('point') }}
                                        <span v-if="parseFloat(row.price)">+￥{{ row.price }}</span>
                                    </span>
                                    <span v-else class="text-[13px]">￥{{ row.price }}</span>
                                    <span v-if="row.extend && row.extend.newcomer_price" class="text-[13px] mt-[5px]">
                                        <span v-if="parseFloat(row.extend.newcomer_price) && row.num > 1">{{row.num }}{{ t('piece') }}<span class="text-[#999]">（第1{{ t('piece') }}，￥{{ parseFloat(row.extend.newcomer_price).toFixed(2) }}/{{ t('piece') }}；第{{ row.num > 2 ? '2~' + row.num : '2' }}{{ t('piece') }}，￥{{ parseFloat(row.price).toFixed(2) }}/{{ t('piece') }}）</span></span>
                                        <span v-else>{{ row.num }}{{ t('piece') }}</span>
                                    </span>
                                    <span v-else class="text-[13px] mt-[5px]">{{ row.num }}{{ t('piece') }}</span>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column prop="num" :label="t('detailNum')" min-width="50" align="right" />
                    </el-table>
                    <div class="py-[12px] px-[16px] border-b border-color flex justify-end">
                        <div class="w-[310px] flex flex-col text-right">
                            <div class="flex mb-[10px]">
                                <div class="text-base flex-1">{{ t('orderDetailGoodsMoney') }}</div>
                                <div class="text-base flex-1 pl-[30px]">
                                    <span v-if="formData.activity_type == 'exchange'" class="text-[14px]">{{ formData.point }}{{ t('point') }}
                                        <span v-if="parseFloat(formData.goods_money)">+￥{{ formData.goods_money }}</span>
                                    </span>
                                    <span v-else class="text-[14px]">￥{{ formData.goods_money }}</span>
                                </div>
                            </div>
                            <div class="flex mb-[10px]" v-if="formData.coupon_money > 0">
                                <div class="text-base flex-1">{{ t('couponMoney') }}</div>
                                <div class="text-base flex-1 pl-[30px]">{{ formData.coupon_money }}</div>
                            </div>
                            <div class="flex mb-[10px]" v-if="formData.manjian_discount_money > 0">
                                <div class="text-base flex-1">{{ t('manjianDiscountMoney') }}</div>
                                <div class="text-base flex-1 pl-[30px]">{{ formData.manjian_discount_money }}</div>
                            </div>
                            <div class="flex mb-[10px]">
                                <div class="text-base flex-1">{{ t('discountMoney') }}</div>
                                <div class="text-base flex-1 pl-[30px]">{{ formData.discount_money }}</div>
                            </div>
                            <div class="flex mb-[10px]">
                                <div class="text-base flex-1">{{ t('orderDetailDeliveryMoney') }}</div>
                                <div class="text-base flex-1 pl-[30px]">{{ formData.delivery_money }}</div>
                            </div>
                            <div class="flex">
                                <div class="text-base flex-1">{{ t('detailOrderMoney') }}</div>
                                <div class="text-base flex-1 pl-[30px] text-[red]">
                                    <span v-if="formData.activity_type == 'exchange'" class="text-[14px]">{{ formData.point }}{{ t('point') }}
                                        <span v-if="parseFloat(formData.order_money)">+￥{{ formData.order_money }}</span>
                                    </span>
                                    <span v-else class="text-[14px]">￥{{ formData.order_money }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="activeName == 'log' && formData.order_log.length > 0">
                    <div class="mb-[100px] px-[20px]" style="min-height: 100px">
                        <div class="flex" v-for="(items, index) in formData.order_log" :key="index">
                            <div class="mr-[20px] min-w-[71px]">
                                <div class="leading-[1] text-[14px] w-[100px] flex justify-end">{{ items.create_time && items.create_time.split(' ')[0] }}</div>
                                <div class="leading-[1] text-[14px]  w-[100px] flex justify-end mt-[15px]">{{ items.create_time && items.create_time.split(' ')[1] }}</div>
                            </div>
                            <div>
                                <div class="w-[16px] h-[16px] flex items-center bg-[#D1EBFF] border-[1px] border-[#0091FF] rounded-[999px]">
                                    <div class="w-[8px] h-[8px] mx-auto bg-[#0091FF] rounded-[999px]"></div>
                                </div>
                                <div v-if="index + 1 != formData.order_log.length" class="w-[2px] h-[50px] bg-[#D1EBFF] mx-auto"></div>
                            </div>
                            <div>
                                <div class="leading-[1] ml-[20px] text-[14px]">{{ items.main_type_name }}{{ items.main_name }}</div>
                                <div class="leading-[1] ml-[20px] text-[14px] mt-[15px]">
                                    <span>{{ items.type_name }}</span>
                                    <span class="ml-[10px]">{{ items.content }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </el-form>

            <el-card class="box-card !border-none relative" shadow="never" v-if="!loading && !formData">
                <el-empty :description="t('orderInfoEmpty')" />
            </el-card>

            <adjust-money ref="orderAdjustMoneyActionDialog" @complete="resetFn()" />
            <delivery-action ref="deliveryActionDialog" @complete="resetFn()" />
            <order-notes ref="orderNotesDialog" @complete="resetFn()" />
            <delivery-package ref="packageDialog" />
            <electronic-sheet-print ref="electronicSheetPrintDialog" @complete="resetFn()" />
            <order-edit-address ref="orderEditAddressDialog" @complete="resetFn()" />
            <shop-active-refund ref="shopActiveRefundDialog" @complete="resetFn()" />
        </div>
    </el-drawer>
</template>

<script lang="ts" setup>
import { ref, reactive } from 'vue'
import { t } from '@/lang'
import { getOrderDetail, orderClose, orderFinish } from '@/addon/shop/api/order'
import { printTicket } from '@/app/api/printer'
import DeliveryAction from '@/addon/shop/views/order/components/delivery-action.vue'
import OrderNotes from '@/addon/shop/views/order/components/order-notes.vue'
import orderEditAddress from '@/addon/shop/views/order/components/order-edit-address.vue'
import deliveryPackage from '@/addon/shop/views/order/components/delivery-package.vue'
import AdjustMoney from '@/addon/shop/views/order/components/adjust-money.vue'
import electronicSheetPrint from '@/addon/shop/views/order/components/electronic-sheet-print.vue'
import ShopActiveRefund from '@/addon/shop/views/order/components/shop-active-refund.vue'
import { img } from '@/utils/common'
import { ElMessageBox } from 'element-plus'
import { cloneDeep } from 'lodash-es'

const showDialog = ref(false)
const loading = ref(false)
const repeat = ref(false)
const popTitle: string = '订单详情'
let orderId = ''

const emit = defineEmits(['load', 'close-event'])

const handleClose = (done: () => void) => {
    showDialog.value = false
    emit('close-event')
}

const activeName = ref('order')

/**
 * 表单数据
 */
const formData: Record<string, any> | null = ref(null)

const getOrderInfoFn = async () => {
    loading.value = true
    if (orderId) {
        await getOrderDetail(orderId).then(({ data }) => {
            formData.value = data
            let refundOrderNum = 0
            formData.value.order_goods.forEach((orderItem, orderIndex) => {
                if (orderItem.is_enable_refund == 1) {
                    refundOrderNum++
                }
            })
            formData.value.is_refund_show = refundOrderNum > 0
            loading.value = false
        }).catch(() => {
        })
    } else {
        loading.value = false
    }
}

const close = () => {
    ElMessageBox.confirm(t('orderCloseTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }).then(() => {
        orderClose(orderId).then(() => {
            setFormData(orderId)
        })
    })
}

// 订单调整价格
const orderAdjustMoneyActionDialog: Record<string, any> | null = ref(null)
const orderAdjustMoney = () => {
    orderAdjustMoneyActionDialog.value.setFormData(formData.value)
    orderAdjustMoneyActionDialog.value.showDialog = true
}

const deliveryActionDialog: Record<string, any> | null = ref(null)
/**
 * 发货
 */
const delivery = () => {
    deliveryActionDialog.value.setFormData(formData.value)
    deliveryActionDialog.value.showDialog = true
}

const orderNotesDialog: Record<string, any> | null = ref(null)
/**
 * 设置备注
 */
const setNotes = () => {
    orderNotesDialog.value.setFormData(formData.value)
    orderNotesDialog.value.showDialog = true
}
// 订单完成
const finish = () => {
    ElMessageBox.confirm(t('orderFinishTips'), t('warning'), {
        confirmButtonText: t('confirm'),
        cancelButtonText: t('cancel'),
        type: 'warning'
    }
    ).then(() => {
        orderFinish(orderId).then(() => {
            setFormData(orderId)
        })
    })
}

const packageDialog: Record<string, any> | null = ref(null)
/**
 * 发货
 */
const packageEvent = (id: number, mobile: number) => {
    packageDialog.value.setFormData(id, mobile)
    packageDialog.value.showDialog = true
}

// 打印电子面单
const electronicSheetPrintDialog: Record<string, any> | null = ref(null)

const openElectronicSheetPrintDialog = () => {
    const data = {
        print_type: 'single'
    }

    Object.assign(data, cloneDeep(formData.value))
    electronicSheetPrintDialog.value.setFormData(data)
    electronicSheetPrintDialog.value.showDialog = true
}

/**
 * 修改地址
 */
const orderEditAddressDialog: Record<string, any> | null = ref(null)
const orderEditAddressFn = async () => {
    const data = cloneDeep(formData.value)
    orderEditAddressDialog.value.showDialog = true
    orderEditAddressDialog.value.setFormData(data)
}

/**
 * 打印小票
 */
const printTicketEvent = () => {
    if (!formData.value.order_id) return

    if (repeat.value) return
    repeat.value = true

    printTicket({
        type: 'shopGoodsOrder', // 小票模板类型
        trigger: 'manual', // 触发时机：手动触发
        // 业务参数，根据自身业务传值
        business: {
            order_id: formData.value.order_id
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
const refundEvent = () => {
    shopActiveRefundDialog.value.setFormData(formData.value)
    shopActiveRefundDialog.value.showDialog = true
}

const setFormData = async (row: any = null) => {
    orderId = row.id
    formData.value = null
    activeName.value = 'order'
    getOrderInfoFn()
}

const resetFn = () => {
    showDialog.value = false
    emit('load')
}

defineExpose({
    showDialog,
    setFormData
})
</script>

<style lang="scss">
.detail-drawer {
    width: 1300px !important;
}
</style>

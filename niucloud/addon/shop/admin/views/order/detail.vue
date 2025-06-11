<template>
    <div class="main-container">
        <el-card class="card !border-none mb-[15px]" shadow="never">
            <el-page-header :content="pageName" :icon="ArrowLeft" @back="router.push({ path: '/shop/order/index' })" />
        </el-card>

        <el-form :model="formData" label-width="100px" ref="formRef" class="page-form" v-loading="loading" label-position="left">
            <el-card class="box-card !border-none relative" shadow="never" v-if="formData">
                <h3 class="panel-title">{{ t('orderInfo') }}</h3>
                <el-row class="row-bg px-[30px] mb-[20px]" :gutter="20">
                    <el-col :span="8">
                        <el-form-item :label="t('orderNo')">
                            <div class="input-width">{{ formData.order_no }}</div>
                        </el-form-item>
                        <el-form-item :label="t('orderForm')">
                            <div class="input-width">{{ formData.order_from_name }}</div>
                        </el-form-item>
                        <el-form-item :label="t('outTradeNo')" v-if="formData.out_trade_no">
                            <div class="input-width">{{ formData.out_trade_no }}</div>
                        </el-form-item>
                        <el-form-item :label="t('payType')" v-if="formData.pay">
                            <div class="input-width">
                                <span>{{ formData.pay.type_name }}</span>
                            </div>
                        </el-form-item>
                        <el-form-item :label="t('buyers')">
                            <div class="input-width">
                                <span class="text-[14px] text-primary cursor-pointer" @click="memberEvent(formData.member.member_id)">{{ formData.member.nickname }}</span>
                            </div>
                        </el-form-item>
                        <template v-if="formData.delivery_type == 'store'">
                            <el-form-item :label="t('storeTakerName')">
                                <div class="input-width">{{ formData.taker_name }}</div>
                            </el-form-item>
                            <el-form-item :label="t('buyersReserveMobile')">
                                <div class="input-width">{{ formData.taker_mobile }}</div>
                            </el-form-item>
                            <el-form-item :label="t('verifyCode')" v-if="formData.verify_code">
                                <div class="input-width">{{ formData.verify_code }}</div>
                            </el-form-item>
                            <el-form-item :label="t('verifierMember')" v-if="formData.verifier_member">
                                <div class="input-width text-primary cursor-pointer" @click="memberEvent(formData.verifier_member.member_id)">{{ formData.verifier_member.nickname }}</div>
                            </el-form-item>

                        </template>
                        <el-form-item v-if="formData.pay">
                            <div class="input-width" v-if="formData.member_id !== formData.pay.main_id && formData.pay.status == 2">
                                <span>{{ formData.pay.pay_type_name }}, 帮付人：</span>
                                <span class="text-primary cursor-pointer" @click="memberEvent(formData.pay.main_id)">{{ formData.pay.pay_member }}</span>
                            </div>
                        </el-form-item>
                    </el-col>
                    <el-col :span="8">
                        <el-form-item :label="t('deliveryType')">
                            <div class="input-width">{{ formData.delivery_type_name }}</div>
                        </el-form-item>
                        <div v-if="formData.delivery_type == 'express' || formData.delivery_type == 'local_delivery'">
                            <el-form-item :label="t('takerName')">
                                <div class="input-width">{{ formData.taker_name }}</div>
                            </el-form-item>
                            <el-form-item :label="t('takerMobile')">
                                <div class="input-width">{{ formData.taker_mobile }}</div>
                            </el-form-item>
                            <el-form-item :label="t('takerFullAddress')">
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
                            <el-form-item :label="t('buyerAskDeliveryTime')">
                                <div class="input-width">{{ formData.buyer_ask_delivery_time }}</div>
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
                <h3 class="panel-title">{{ t('orderStatus') }}</h3>
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
                        <span class="text-[14px] px-[15px] py-[5px] ml-[30px] text-[#5c96fc] bg-[#ebf3ff] cursor-pointer" @click="openElectronicSheetPrintDialog" v-if="formData.delivery_type == 'express' && formData.status == 3">{{ t('electronicSheetPrintTitle') }}</span>
                        <span class="text-[14px] px-[15px] py-[5px] ml-[30px] text-[#5c96fc] bg-[#ebf3ff] cursor-pointer" @click="printTicketEvent" v-if="formData.delivery_type == 'virtual' && (formData.status == 2 || formData.status == 3 || formData.status == 5)">{{ t('printTicket') }}</span>
                        <span class="text-[14px] px-[15px] py-[5px] ml-[30px] text-[#5c96fc] bg-[#ebf3ff] cursor-pointer" @click="orderEditAddressFn" v-if="formData.status == 1 && formData.delivery_type != 'virtual' && formData.activity_type != 'giftcard'">{{ t('editAddress') }}</span>
                        <span class="text-[14px] px-[15px] py-[5px] ml-[30px] text-[#5c96fc] bg-[#ebf3ff] cursor-pointer" @click="refundEvent" v-if="formData.is_refund_show && formData.status != 1 && formData.status != -1">{{ t('voluntaryRefund') }}</span>
                        <div class="flex" v-if="formData.order_delivery">
                            <template v-for="(item, index) in formData.order_delivery" :key="index">
                                <span v-if="item.delivery_type == 'express' && item.sub_delivery_type == 'express'" class="text-[14px] px-[15px] py-[5px] ml-[30px] text-[#ff7f5b] bg-[#fff0e5] cursor-pointer" @click="packageEvent(item.id, formData.taker_mobile)">{{ t('package') }}{{ index + 1 }}</span>
                                <span v-if="item.delivery_type == 'express' && item.sub_delivery_type == 'none_express'" class="text-[14px] px-[15px] py-[5px] ml-[30px] text-[#ff7f5b] bg-[#fff0e5] cursor-pointer" @click="packageEvent(item.id, formData.taker_mobile)">{{ t('noLogisticsRequired') }}</span>
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
                <h3 class="panel-title" v-if="formData.form_record_show==1">{{ t('formDetail') }}</h3>
                <div class="row-bg px-[30px] mb-[20px]" v-if="formData.form_record_show==1">
                    <div class="grid grid-cols-3 gap-[20px]">
                        <el-form-item v-for="(field, fieldKey) in tableData[0]?.recordsFieldList || []" :key="fieldKey" :label="field.field_name" min-width="200">
                            <component :is="tableData[0]?.recordsFieldList[fieldKey].detailComponent" :data="tableData[0]?.recordsFieldList[fieldKey]" />
                        </el-form-item>
                    </div>
                </div>

                <h3 class="panel-title mt-[30rpx]">{{ t('goodsDetail') }}</h3>
                <el-table :data="formData.order_goods" size="large">
                    <el-table-column :label="t('goodsName')" align="left" width="300">
                        <template #default="{ row }">
                            <div class="flex">
                                <div class="flex items-center shrink-0">
                                    <img class="w-[50px] h-[50px] mr-[10px]" :src="img(row.goods_image)" />
                                </div>
                                <div class="flex flex-col items-start">
                                    <p class="multi-hidden text-[14px]">{{ row.goods_name }}</p>
                                    <span class="text-[12px] text-[#999]">{{ row.sku_name }}</span>
                                    <span class="px-[4px]  text-[12px] text-[#fff] rounded-[4px] bg-primary leading-[18px]" v-if="row.is_gift == 1">赠品</span>
                                    <span class="px-[4px]  text-[12px] text-[#fff] rounded-[4px] bg-primary leading-[18px]" v-if="row.impulse_buy_info && row.impulse_buy_info.is_impulse_buy">顺手买</span>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('price')" min-width="50" align="left">
                        <template #default="{ row }">
                            <div class="flex flex-col">
                                <span v-if="formData.activity_type == 'exchange'">{{ row.extend.point }}{{ t('point') }}
                                    <span v-if="parseFloat(row.price)">+￥{{ row.price }}</span>
                                </span>
                                <span v-else-if="row.impulse_buy_info && row.impulse_buy_info.is_impulse_buy">￥{{ parseFloat(row.impulse_buy_info.impulse_buy_price).toFixed(2) }}</span>
                                <span v-else class="text-[13px]">￥{{ row.price }}</span>
                                <span v-if="row.impulse_buy_info && row.impulse_buy_info.is_impulse_buy && row.num > 1" class="text-[13px] mt-[5px]">
                                    {{ row.impulse_buy_info.show_tips }}
                                </span>
                                <span v-if="row.extend && row.extend.newcomer_price" class="text-[13px] mt-[5px]">
                                    <span v-if="parseFloat(row.extend.newcomer_price) && row.num > 1">{{ row.num }}{{ t('piece') }}<span
                                        class="text-[#999]">（第1{{ t('piece') }}，￥{{ parseFloat(row.extend.newcomer_price).toFixed(2) }}/{{ t('piece') }}；第{{ row.num > 2 ? '2~' + row.num : '2' }}{{ t('piece') }}，￥{{ parseFloat(row.price).toFixed(2) }}/{{ t('piece') }}）</span></span>
                                    <span v-else>{{ row.num }}{{ t('piece') }}</span>
                                </span>
                                <span v-else class="text-[13px] mt-[5px]">{{ row.num }}{{ t('piece') }}</span>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="num" :label="t('num')" min-width="50" align="right" />
                    <el-table-column align="right" min-width="120">
                        <template #default="{ row }">
                            <el-button type="primary" v-if="row.form_record_show==1" link @click="formDetailEvent(row.form_record_id)">{{ t('formDetail') }}</el-button>
                        </template>
                    </el-table-column>
                </el-table>
                <div class="py-[12px] px-[16px] border-b border-color flex justify-end">
                    <div class="w-[310px] flex flex-col text-right">
                        <div class="flex mb-[10px]">
                            <div class="text-base flex-1">{{ t('goodsMoney') }}</div>
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
                            <div class="text-base flex-1">{{ t('deliveryMoney') }}</div>
                            <div class="text-base flex-1 pl-[30px]">{{ formData.delivery_money }}</div>
                        </div>
                        <div class="flex">
                            <div class="text-base flex-1">{{ t('orderMoney') }}</div>
                            <div class="text-base flex-1 pl-[30px] text-[red]">
                                <span v-if="formData.activity_type == 'exchange'" class="text-[14px]">{{ formData.point }}{{ t('point') }}
                                    <span v-if="parseFloat(formData.order_money)">+￥{{ formData.order_money }}</span>
                                </span>
                                <span v-else class="text-[14px]">￥{{ formData.order_money }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <h3 class="mt-[50px] mb-[20px]" v-if="formData.order_log.length > 0">{{ t('operateLog') }}</h3>
                <div class="mb-[100px]" style="min-height: 100px" v-if="formData.order_log.length > 0">
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
            </el-card>
            <el-card class="box-card !border-none relative" shadow="never" v-if="!loading && !formData">
                <el-empty :description="t('orderInfoEmpty')" />
            </el-card>
        </el-form>
        <adjust-money ref="orderAdjustMoneyActionDialog" @complete="setFormData(orderId)" />
        <delivery-action ref="deliveryActionDialog" @complete="setFormData(orderId)" />
        <order-notes ref="orderNotesDialog" @complete="setFormData(orderId)" />
        <delivery-package ref="packageDialog" />
        <electronic-sheet-print ref="electronicSheetPrintDialog" @complete="loadOrderList" />
        <order-edit-address ref="orderEditAddressDialog" @complete="loadOrderList" />
        <shop-active-refund ref="shopActiveRefundDialog" @complete="setFormData(orderId)" />
        <form-detail ref="formDetailDialog" />
    </div>
</template>

<script lang="ts" setup>
import { ref, defineAsyncComponent } from 'vue'
import { t } from '@/lang'
import { ArrowLeft } from "@element-plus/icons-vue"
import { getOrderDetail, orderClose, orderFinish } from '@/addon/shop/api/order'
import { getFormRecordsInfo } from '@/app/api/diy_form'
import { printTicket } from '@/app/api/printer'
import DeliveryAction from '@/addon/shop/views/order/components/delivery-action.vue'
import OrderNotes from '@/addon/shop/views/order/components/order-notes.vue'
import orderEditAddress from '@/addon/shop/views/order/components/order-edit-address.vue'
import deliveryPackage from '@/addon/shop/views/order/components/delivery-package.vue'
import AdjustMoney from '@/addon/shop/views/order/components/adjust-money.vue'
import electronicSheetPrint from '@/addon/shop/views/order/components/electronic-sheet-print.vue'
import ShopActiveRefund from '@/addon/shop/views/order/components/shop-active-refund.vue'
import FormDetail from '@/addon/shop/views/order/components/form-detail.vue'
import { useRoute, useRouter } from 'vue-router'
import { img } from '@/utils/common'
import { ElMessageBox } from 'element-plus'
import { cloneDeep } from 'lodash-es'

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title
const orderId: number = parseInt(route.query.order_id as string)
const loading = ref(true)

const formData: Record<string, any> | null = ref(null)
const tableData = ref([]);
const modules: any = import.meta.glob('@/**/*.vue')
const formDetailDialog: any = ref(null)
const formDetailEvent = (data: any) => {
    formDetailDialog.value.show(data)
}

const setFormData = async(orderId: number = 0) => {
    loading.value = true;
    formData.value = null;

    try {
        // 获取订单详情
        const { data } = await getOrderDetail(orderId);
        formData.value = data;

        let refundOrderNum = 0;
        formData.value.form_details = null; // 订单表单详情
        formData.value.order_goods.forEach((orderItem: any) => {
            if (orderItem.is_enable_refund == 1) {
                refundOrderNum++;
            }
            orderItem.form_details = null; // 每个商品的表单详情
        });
        formData.value.is_refund_show = refundOrderNum > 0;

        // 获取订单的表单详情
        if (formData.value.form_record_id) {
            try {
                const orderFormResponse = await getFormRecordsInfo(formData.value.form_record_id);
                formData.value.form_details = orderFormResponse.data;
                if (formData.value.form_details && formData.value.form_details.recordsFieldList) {
                    const recordsFieldList = formData.value.form_details.recordsFieldList;
                    for (const key in recordsFieldList) {
                        if (modules[recordsFieldList[key].detailComponent]) {
                            recordsFieldList[key].detailComponent = defineAsyncComponent(modules[recordsFieldList[key].detailComponent]);
                        } else {
                            delete recordsFieldList[key];
                        }
                    }
                    tableData.value = [formData.value.form_details];
                }
            } catch (error) {
            }
        }
    } catch (error) {
    } finally {
        loading.value = false;
    }
};

if (orderId) setFormData(orderId)
else loading.value = false

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
    let data = {
        print_type: 'single'
    };

    Object.assign(data, cloneDeep(formData.value))
    electronicSheetPrintDialog.value.setFormData(data)
    electronicSheetPrintDialog.value.showDialog = true
}

const repeat = ref(false)

/**
 * 修改地址
 */
const orderEditAddressDialog: Record<string, any> | null = ref(null)
const orderEditAddressFn = async() => {
    let data = cloneDeep(formData.value);
    orderEditAddressDialog.value.showDialog = true
    orderEditAddressDialog.value.setFormData(data)
}

/**
 * 打印小票
 */
const printTicketEvent = () => {
    if (!formData.value.order_id) return;

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

const memberEvent = (id: number) => {
    const routeUrl = router.resolve({
        path: '/member/detail',
        query: { id }
    })
    window.open(routeUrl.href, '_blank')
}
</script>

<style lang="scss" scoped>
/* 多行超出隐藏 */
.multi-hidden {
    word-break: break-all;
    text-overflow: ellipsis;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.line-feed {
    word-wrap: break-word;
}
</style>

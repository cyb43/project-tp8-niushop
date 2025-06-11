<template>
    <el-drawer v-model="showDialog" :title="popTitle" direction="rtl" :before-close="handleClose" class="member-detail-drawer">
        <div class="main-container" v-loading="loading">
            <el-tabs v-model="activeName" class="pb-[10px]" @tab-change="tabChange">
                <el-tab-pane label="订单信息" name="order" />
                <el-tab-pane label="售后信息" name="refund" />
                <el-tab-pane label="商品信息" name="goods" />
                <el-tab-pane v-if="formData && formData.refund_log && formData.refund_log.length > 0" label="订单日志" name="log" />
            </el-tabs>

            <el-form :model="formData" label-width="100px" ref="formRef" class="page-form" v-if="!loading" label-position="left">
                <div v-if="activeName == 'order'">
                    <el-row class="row-bg px-[30px] mb-[20px]" :gutter="20">
                        <el-col :span="8">
                            <el-form-item :label="t('orderNo')">
                                <div class="input-width text-primary cursor-pointer" @click="toOrderDetail(formData.order_id)">{{ formData.order_main.order_no }}</div>
                            </el-form-item>
                            <el-form-item :label="t('orderForm')">
                                <div class="input-width">{{ formData.order_main.order_from_name }}</div>
                            </el-form-item>
                            <el-form-item :label="t('outTradeNo')" v-if="formData.order_main.out_trade_no">
                                <div class="input-width">{{ formData.order_main.out_trade_no }}</div>
                            </el-form-item>
                            <el-form-item :label="t('payType')" v-if="formData.pay_refund">
                                <div class="input-width">{{ formData.pay_refund.type_name }}</div>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item :label="t('deliveryType')">
                                <div class="input-width">{{ formData.order_main.delivery_type_name }}</div>
                            </el-form-item>
                            <div v-if="formData.order_main.delivery_type == 'express' || formData.order_main.delivery_type == 'local_delivery'">
                                <el-form-item :label="t('takerName')">
                                    <div class="input-width">{{ formData.order_main.taker_name }}</div>
                                </el-form-item>
                                <el-form-item :label="t('takerMobile')">
                                    <div class="input-width">{{ formData.order_main.taker_mobile }}</div>
                                </el-form-item>
                                <el-form-item :label="t('takerFullAddress')">
                                    <div class="input-width">{{ formData.order_main.taker_full_address }}</div>
                                </el-form-item>
                            </div>
                            <div v-if="formData.order_main.delivery_type == 'store'">
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
                                <div class="input-width">{{ formData.order_main.member_remark }}</div>
                            </el-form-item>
                            <el-form-item :label="t('notes')">
                                <div class="input-width line-feed">{{ formData.order_main.shop_remark }}</div>
                            </el-form-item>
                        </el-col>
                    </el-row>
                </div>
                <div v-if="activeName == 'refund'">
                    <div class="text-[14px] min-w-[110px] border-solid border-l-[3px] border-[var(--el-color-primary)] pl-[5px]">{{ t('afterSales') }}</div>
                    <div class="px-[30px] mt-[20px] mb-[20px]">
                        <el-row class="row-bg px-[30px] mb-[20px]" :gutter="20">
                            <el-col :span="12">
                                <el-form-item :label="t('orderRefundNo')">
                                    <div class="input-width">{{ formData.order_refund_no }}</div>
                                </el-form-item>
                                <el-form-item :label="t('refundType')">
                                    <div class="input-width">{{ formData.refund_type_name }}</div>
                                </el-form-item>
                                <el-form-item :label="t('createTime')">
                                    <div class="input-width">{{ formData.create_time }}</div>
                                </el-form-item>
                            </el-col>
                            <el-col :span="12">
                                <el-form-item :label="t('refundMoney')">
                                    <div class="input-width" v-if="formData.status == 8">￥{{ formData.money }}</div>
                                    <div class="input-width" v-else>￥{{ formData.apply_money }}</div>
                                </el-form-item>
                                <el-form-item :label="t('refundReason')">
                                    <div class="input-width">{{ formData.reason }}</div>
                                </el-form-item>
                            </el-col>
                        </el-row>
                        <el-row class="row-bg px-[30px] mb-[20px]" :gutter="20">
                            <el-col :span="24">
                                <el-form-item :label="t('refundVoucher')">
                                    <div class="input-width flex" v-if="formData.voucher">
                                        <div class="mr-3" v-for="(voucherItem, voucherIndex) in formData.voucher" :key="voucherIndex">
                                            <el-image v-if="voucherItem" class="w-[70px] h-[70px]" :src="img(voucherItem)" fit="contain" :preview-src-list="[img(voucherItem)]">
                                                <template #error>
                                                    <div class="image-slot">
                                                        <img class="w-[70px] h-[70px]" src="@/addon/shop/assets/goods_default.png" />
                                                    </div>
                                                </template>
                                            </el-image>
                                        </div>
                                    </div>
                                </el-form-item>
                                <el-form-item :label="t('refundRemark')">
                                    <div class="max-w-[100%] break-all">{{ formData.remark }}</div>
                                </el-form-item>
                            </el-col>
                        </el-row>
                    </div>
                    <div class="text-[14px] min-w-[110px] border-solid border-l-[3px] border-[var(--el-color-primary)] pl-[5px]" v-if="formData.status == 4">买家退货信息</div>
                    <div class="px-[30px] mt-[20px] mb-[20px]" v-if="formData.status == 4">
                        <el-row class="row-bg px-[30px] mb-[20px]" :gutter="20" v-if="formData.status == 4">
                            <el-col :span="8">
                                <el-form-item :label="t('expressCompany')">
                                    <div class="input-width">{{ formData.delivery.express_company }}</div>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item :label="t('expressNumber')">
                                    <div class="input-width">{{ formData.delivery.express_number }}</div>
                                </el-form-item>
                            </el-col>
                            <el-col :span="8">
                                <el-form-item :label="t('expressRemark')">
                                    <div class="input-width">{{ formData.delivery.remark }}</div>
                                </el-form-item>
                            </el-col>
                        </el-row>
                    </div>
                    <div class="text-[14px] min-w-[110px] border-solid border-l-[3px] border-[var(--el-color-primary)] pl-[5px]">{{ t('refundStatus') }}</div>
                    <div class="px-[30px] mt-[20px] mb-[20px]">
                        <p>
                            <span class="ml-[30px] text-[14px] mr-[20px]">{{ t('refundStatus') }}：</span>
                            <span class="text-[14px]">{{ formData.status_name }}</span>
                        </p>
                        <div class="mt-[20px] flex " v-if="formData.shop_reason">
                            <span class="ml-[30px] text-[14px] mr-[20px] flex-shrink-0">{{ t('refuseReason') }}：</span>
                            <div class="text-[14px] break-all">{{ formData.shop_reason }}</div>
                        </div>
                        <div class="flex mt-[10px]">
                            <span class="text-[14px] px-[15px] py-[5px] ml-[30px] text-[#5c96fc] bg-[#ebf3ff] cursor-pointer" v-if="formData.status == 1" @click="agreeEvent">{{ t('agree') }}</span>
                            <span class="text-[14px] px-[15px] py-[5px] ml-[30px] text-[#5c96fc] bg-[#ebf3ff] cursor-pointer" v-if="formData.status == 1" @click="refuseEvent">{{ t('refuse') }}</span>
                            <span class="text-[14px] px-[15px] py-[5px] ml-[30px] text-[#5c96fc] bg-[#ebf3ff] cursor-pointer" v-if="formData.status == 4 && formData.refund_type == 2" @click="deliverEvent">{{ t('confirmDelivery') }}</span>
                            <span class="text-[14px] px-[15px] py-[5px] ml-[30px] text-[#5c96fc] bg-[#ebf3ff] cursor-pointer" v-if="formData.status == 4 && formData.refund_type == 2" @click="deliveryRefuseEvent">{{ t('refuse') }}</span>
                            <span class="text-[14px] px-[15px] py-[5px] ml-[30px] text-[#5c96fc] bg-[#ebf3ff] cursor-pointer" v-if="formData.status == 6" @click="transferEvent">{{ t('transferAccounts') }}</span>
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
                        <el-table-column :label="t('goodsName')" align="left" width="300">
                            <template #default="{ row }">
                                <div class="flex">
                                    <div class="flex items-center w-[50px] h-[50px] mr-[10px]">
                                        <img class="w-[50px] h-[50px]" :src="img(row.goods_image)" />
                                    </div>
                                    <div class="flex flex-col flex-1">
                                        <span>{{ row.goods_name }}</span>
                                        <span class="text-[12px] text-[#999] truncate">{{ row.sku_name }}</span>
                                    </div>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column prop="price" :label="t('price')" min-width="50" align="left" />
                        <el-table-column prop="num" :label="t('num')" min-width="50" align="right" />
                    </el-table>
                </div>
                <div v-if="activeName == 'log' && formData.refund_log.length > 0">
                   <div class="mb-[100px] px-[20px]" style="min-height: 100px">
                        <template v-if="formData.refund_log.length > 0">
                            <div class="flex" v-for="(items, index) in formData.refund_log" :key="index">
                                <div class="mr-[20px] min-w-[71px]">
                                    <div class="leading-[1] w-full text-[14px] w-[100px] flex justify-end">
                                        {{ items.create_time.split(' ')[0] }}
                                    </div>
                                    <div class="leading-[1] w-full text-[14px]  w-[100px] flex justify-end mt-[15px]">
                                        {{ items.create_time.split(' ')[1] }}
                                    </div>
                                </div>
                                <div>
                                    <div class="w-[16px] h-[16px] flex items-center bg-[#D1EBFF] border-[1px] border-[#0091FF] rounded-[999px]">
                                        <div class="w-[8px] h-[8px] mx-auto bg-[#0091FF] rounded-[999px]"></div>
                                    </div>
                                    <div v-if="index + 1 != formData.refund_log.length" class="w-[2px] h-[50px] bg-[#D1EBFF] mx-auto">
                                    </div>
                                </div>
                                <div>
                                    <div class="leading-[1] ml-[20px] text-[14px]">
                                        {{ items.main_type_name }}{{ items.main_name }}
                                    </div>
                                    <div class="leading-[1] ml-[20px] text-[14px] mt-[15px]">
                                        {{ items.type_name }}
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </el-form>

            <el-card class="box-card !border-none relative" shadow="never" v-if="!loading && !formData">
                <el-empty :description="t('orderInfoEmpty')" />
            </el-card>

            <!-- 拒绝退款弹框 -->
            <el-dialog v-model="refuseShowDialog" :title="t('orderRefundRefuse')" width="460px" class="diy-dialog-wrap" :destroy-on-close="true">
                <el-form :model="refuseFormData" label-width="90px" ref="refuseFormRef" :rules="refundFormRules">
                    <el-form-item :label="t('refuseReason')" prop="shop_reason">
                        <el-input v-model.trim="refuseFormData.shop_reason" type="textarea" rows="4" clearable class="input-width" maxlength="200" show-word-limit />
                    </el-form-item>
                </el-form>
                <template #footer>
                    <span class="dialog-footer">
                        <el-button @click="refuseShowDialog = false">{{ t('cancel') }}</el-button>
                        <el-button type="primary" :loading="actionLoading" @click="confirm(refuseFormRef)">{{ t('confirm') }}</el-button>
                    </span>
                </template>
            </el-dialog>
            <!-- 同意弹框 -->
            <el-dialog v-model="agreeRefundDialog" :title="t('orderRefundAgree')" width="460px" class="diy-dialog-wrap" :destroy-on-close="true">
                <el-form @submit.native.prevent="onSubmit" :model="refuseFormData" label-width="120px" ref="formRef" :rules="formRules" class="page-form" v-loading="loading">
                    <el-form-item :label="t('applyMoney')">
                        <span>￥{{ refuseFormData.apply_money }}</span>
                    </el-form-item>
                    <el-form-item :label="t('agreeMoney')" prop="money">
                        <div>
                            <el-input v-model.trim="refuseFormData.money" clearable class="input-width" />
                            <div  class="mt-[10px] text-[#999] text-[12px] leading-[20px]" v-if="formData.gift_balance && (Number(formData.gift_balance) > Number(formData.member.balance))">
                                当前订单需退还{{ formData.gift_balance}}元赠品余额。若用户余额不足，则默认不进行扣除。请联系客户确认退款金额。
                            </div>
                        </div>
                    </el-form-item>
                    <el-form-item :label="t('refundDeliveryAddress')" prop="refund_address_id" v-if="refuseFormData.refund_type == 2" >
                        <el-select v-model="refuseFormData.refund_address_id" clearable class="input-item">
                            <el-option v-for="(item, index) in refundAddress" :key="index" :label="item.full_address" :value="item.id"></el-option>
                        </el-select>
                    </el-form-item>
                </el-form>
                <template #footer>
                    <span class="dialog-footer">
                        <el-button @click="agreeRefundDialog = false">{{ t('cancel') }}</el-button>
                        <el-button type="primary" :loading="actionLoading" @click="agreeRefundEvent(formRef)">{{ t('confirm') }}</el-button>
                    </span>
                </template>
            </el-dialog>
            <!-- 拒绝收货 -->
            <el-dialog v-model="deliveryRefuseDialog" :title="t('orderRefundRefuse')" width="460px" class="diy-dialog-wrap" :destroy-on-close="true">
                <el-form :model="deliveryRefuseFormData" label-width="90px" ref="deliveryRefuseFormRef" :rules="deliveryRefundFormRules">
                    <el-form-item :label="t('refuseReason')" prop="shop_reason">
                        <el-input v-model.trim="deliveryRefuseFormData.shop_reason" type="textarea" rows="4" clearable class="input-width" maxlength="200" show-word-limit />
                    </el-form-item>
                </el-form>
                <template #footer>
                    <span class="dialog-footer">
                        <el-button @click="deliveryRefuseDialog = false">{{ t('cancel') }}</el-button>
                        <el-button type="primary" :loading="actionLoading" @click="refundDeliveryFn(deliveryRefuseFormRef)">{{ t('confirm') }}</el-button>
                    </span>
                </template>
            </el-dialog>
        </div>
    </el-drawer>
</template>

<script lang="ts" setup>
import { ref, reactive, computed } from 'vue'
import { t } from '@/lang'
import { orderRefundDetail, auditRefund, refundDelivery } from '@/addon/shop/api/order'
import { getOrderRefundAddress } from '@/addon/shop/api/shop_address'
import { useRouter } from 'vue-router'
import { img } from '@/utils/common'
import { ElMessageBox, FormInstance } from 'element-plus'

const router = useRouter()
const loading = ref(false)
let popTitle: string = '订单详情'
const actionLoading = ref(false)

const formData: Record<string, any> | null = ref(null)
const showDialog = ref(false)
const handleClose = (done: () => void) => {
    showDialog.value = false;
}

const activeName = ref('order')
let refundId = '';
const emit = defineEmits(['load'])


const getOrderInfoFn = async () => {
    loading.value = true
    if (refundId) {
        await orderRefundDetail(refundId).then(({ data }) => {
            formData.value = data
            formData.value.order_goods = [data.order_goods]

            loading.value = false;
        }).catch(() => {
        })
    } else {
        loading.value = false
    }
}

// 退款参数
const refuseShowDialog = ref(false)
const initialFormData = {
    shop_reason: '',
    refund_address_id: '',
    money: '',
    type: '',
    apply_money: 0.00,
    refund_type: 1
}

const refuseFormData: Record<string, any> = reactive({ ...initialFormData })
const formRef = ref<FormInstance>()
const refuseFormRef = ref<FormInstance>()
// 表单验证规则
const formRules = computed(() => {
    return {
        money: [
            { required: true, message: t('moneyPlaceholder'), trigger: 'blur' }
        ],
        refund_address_id: [
            { required: true, message: t('refundaddressPlaceholder'), trigger: 'blur' }
        ]
    }
})
// 拒绝退款
const refundFormRules = computed(() => {
    return {
        shop_reason: [
            { required: true, message: t('shopReasonPlaceholder'), trigger: 'blur' }
        ]
    }
})

const agreeRefundDialog = ref(false) // 同意退款 弹窗
// 同意退款 弹窗
const agreeEvent = () => {
    refuseFormData.type = 'agree'
    refuseFormData.refund_type = formData.value.refund_type
    refuseFormData.apply_money = formData.value.apply_money
    refuseFormData.money = formData.value.apply_money
    agreeRefundDialog.value = true
    if (formData.value.refund_type == 2) {
        getRefundAddress()
    }
}

// 获取退货地址
const refundAddress = ref<any[]>([])
const getRefundAddress = () => {
    getOrderRefundAddress().then((data) => {
        refundAddress.value = data.data
        data.data.forEach((item:any) => {
            if (item.is_refund_address == 1) {
                refuseFormData.refund_address_id = item.id
            }
        })
    })
}

// 同意退款  提交
const agreeRefundEvent = async (formEl: FormInstance | undefined) => {
    if (actionLoading.value || !formEl) return
    await formEl.validate(async (valid) => {
        if (valid) {
            actionLoading.value = true
            auditRefund({
                order_refund_no: formData.value.order_refund_no,
                money: refuseFormData.money,
                is_agree: 1,
                refund_address_id: refuseFormData.refund_address_id
            }).then(() => {
                agreeRefundDialog.value = false
                actionLoading.value = false
                resetFn();
            }).catch(() => {
                actionLoading.value = false
            })
        }
    })
}

// 拒绝退款 弹出
const refuseEvent = () => {
    refuseFormData.type = 'refuse'
    refuseFormData.shop_reason = ''
    refuseShowDialog.value = true
}

// 拒绝退款  提交
const confirm = async (formEl: FormInstance | undefined) => {
    if (actionLoading.value || !formEl) return
    await formEl.validate(async (valid) => {
        if (valid) {
            actionLoading.value = true
            auditRefund({
                order_refund_no: formData.value.order_refund_no,
                is_agree: 0,
                shop_reason: refuseFormData.shop_reason
            }).then(res => {
                resetFn();
                actionLoading.value = false
                refuseShowDialog.value = false
            }).catch(() => {
                actionLoading.value = false
                refuseShowDialog.value = false
            })
        }
    })
}

// 确认收货
const deliverEvent = () => {
    ElMessageBox.confirm(t('orderDeliveryTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        refundDelivery({
            order_refund_no: formData.value.order_refund_no,
            is_agree: 1
        }).then(() => {
            resetFn();
        })
    })
}

// 收货拒绝弹出
const deliveryRefuseDialog = ref<boolean>(false)
const deliveryRefuseFormData = reactive<any>({
    shop_reason: ''
})
const deliveryRefuseFormRef = ref<FormInstance>()
const deliveryRefundFormRules = computed(() => {
    return {
        shop_reason: [
            { required: true, message: t('shopReasonPlaceholder'), trigger: 'blur' }
        ]
    }
})
// 拒绝收货提交
const refundDeliveryFn = async (formEl: FormInstance | undefined) => {
    if (actionLoading.value || !formEl) return
    await formEl.validate(async (valid) => {
        if (valid) {
            actionLoading.value = true
            refundDelivery({
                order_refund_no: formData.value.order_refund_no,
                is_agree: 0,
                shop_reason: deliveryRefuseFormData.shop_reason
            }).then(res => {
                resetFn();
                actionLoading.value = false
                deliveryRefuseDialog.value = false
            }).catch(() => {
                actionLoading.value = false
                deliveryRefuseDialog.value = false
            })
        }
    })
}
const deliveryRefuseEvent = () => {
    refuseFormData.type = 'refuse'
    refuseFormData.shop_reason = ''
    deliveryRefuseDialog.value = true
}

const transferEvent = () => {
    const routeUrl = router.resolve({
        path: '/member/refund/detail',
        query: { refund_no: formData.value.refund_no }
    })
    window.open(routeUrl.href, '_blank')
}

// 订单详情
const toOrderDetail = (id:number) => {
    const routeUrl = router.resolve({
        path: '/shop/order/detail',
        query: { order_id: id }
    })
    window.open(routeUrl.href, '_blank')
}


const setFormData = async (row: any = null) => {
    refundId = row.id;
    formData.value = null;
    activeName.value = 'order';
    getOrderInfoFn();
}

const resetFn = ()=>{
    showDialog.value = false;
    emit('load');
}

defineExpose({
    showDialog,
    setFormData
})
</script>

<style lang="scss">
.member-detail-drawer{
    width: 1300px !important;
}
.page-form .input-width{
    width: 170px;
}
</style>

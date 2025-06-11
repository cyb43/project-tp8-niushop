<template>
    <el-dialog v-model="showDialog" :title="t('refundTitle')" width="800px" class="diy-dialog-wrap" :destroy-on-close="true">
        <div v-loading="loading">
            <el-alert type="warning" :closable="false" class="!mb-[10px]">
                <template #default>
                    <p>商家主动退款功能仅作为退款售后业务的补充功能，请勿过度依赖和使用。此功能支持多次退款操作。</p>
                    <p>如果订单项全部退款，运费将在最后一次退款时一并退还，同时使用的优惠券也将返还给用户，退款金额将原路返还至用户的支付账户。</p>
                </template>
            </el-alert>
            <el-form :model="formData" label-width="100px" ref="formRef" class="page-form mb-[30px]">
                <el-form-item :label="t('refundMoney')">
                    <div class="text-[red]">
                        <span>￥{{ formData.refund_money }}</span>
                        <span v-if="isShowDelivery" class="text-[#999] text-[24rpx]">（运费:￥{{formData.delivery_money}}）</span>
                    </div>
                </el-form-item>
                <el-form-item :label="t('refundGoodsItem')">
                    <el-table :data="refundTable" size="large" ref="refundTableRef" @select="refundSelectChange" @select-all="refundSelectAll">
                        <el-table-column type="selection" width="40" :selectable="selectable" />
                        <el-table-column align="left" min-width="200" :label="t('refundGoodsInfo')">
                            <template #default="{ row }">
                                <div class="flex cursor-pointer">
                                    <div class="flex items-center min-w-[50px] mr-[10px]">
                                        <img class="w-[50px] h-[50px]" v-if="row.goods_image" :src="img(row.goods_image)" alt="">
                                        <img class="w-[50px] h-[50px]" v-else src="" alt="">
                                    </div>
                                    <div class="flex flex-col items-start">
                                        <el-tooltip class="box-item" effect="light" placement="top">
                                            <template #content>
                                                <div class="max-w-[250px]">{{row.goods_name}}</div>
                                            </template>
                                            <p class="multi-hidden text-[14px]">{{ row.goods_name }}</p>
                                        </el-tooltip>
                                        <span class="text-[12px] text-[#999] truncate">{{ row.sku_name }}</span>
                                        <span class="px-[4px]  text-[12px] text-[#fff] rounded-[4px] bg-primary leading-[18px]" v-if="row.is_gift == 1">赠品</span>
                                    </div>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column min-width="60" :label="t('refundPayPrice')">
                            <template #default="{ row }">
                                <div class="flex flex-col">
                                    <span class="text-[13px]">￥{{ row.refund_money }}</span>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column min-width="80" :label="t('refundGoodsPrice')">
                            <template #default="{ row }">
                                <div class="flex flex-col">
                                    <span class="text-[13px]">￥{{ row.price }}</span>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column min-width="60" :label="t('refundGoodsNum')">
                            <template #default="{ row }">
                                <div class="flex flex-col">
                                    <span class="text-[13px] mt-[5px]">{{ row.num }}{{ t('piece') }}</span>
                                </div>
                            </template>
                        </el-table-column>
                    </el-table>
                </el-form-item>
                <el-form-item :label="t('refundInstructions')" prop="shop_active_refund_remark">
                    <el-input v-model.trim="formData.shop_active_refund_remark" :rows="5" type="textarea" maxlength="200" show-word-limit />
                </el-form-item>
            </el-form>
        </div>
        <template #footer>
            <span class="dialog-footer">
                <el-button @click="showDialog = false">{{ t('cancel') }}</el-button>
                <el-button type="primary" :loading="loading" @click="confirm(formRef)">{{ t('confirm') }}</el-button>
            </span>
        </template>
    </el-dialog>
</template>

<script lang="ts" setup>
import { ref, reactive, nextTick } from 'vue'
import { t } from '@/lang'
import { img } from '@/utils/common'
import { FormInstance, ElMessage } from 'element-plus'
import { shopActiveRefund } from '@/addon/shop/api/order'
import { cloneDeep } from 'lodash-es'

const showDialog = ref(false)
const loading = ref(false)

/**
 * 表单数据
 */
const initialFormData = {
    order_goods_ids: [],
    refund_money: 0,
    shop_active_refund_remark: '',
}

const refundTable = ref([]);
const refundTableRef: Record<string, any> | null = ref(null)
const formData: Record<string, any> = reactive({ ...initialFormData })
const formRef = ref<FormInstance>()
let refundItemNum = 0; //表示可退款项数量
let isShowDelivery = ref(false);

const emit = defineEmits(['complete'])

// 监听选择
const refundSelectChange = (selection, row)=>{
    formData.refund_money = 0;
    isShowDelivery.value = false;
    formData.order_goods_ids = [];
    selection.forEach((item,index) => {
        formData.refund_money += item.refund_money;
        formData.order_goods_ids.push(item.order_goods_id);
    });
    if(selection.length == refundItemNum){
        formData.refund_money += parseFloat(formData.delivery_money);
        isShowDelivery.value = true;
    }
    formData.refund_money = formData.refund_money.toFixed(2);
}

// 全选
const refundSelectAll = (selection)=>{
    formData.refund_money = 0;
    isShowDelivery.value = false;
    formData.order_goods_ids = [];
    selection.forEach((item,index) => {
        formData.refund_money += item.refund_money;
        formData.order_goods_ids.push(item.order_goods_id);
    });
    if(selection.length == refundItemNum){
        formData.refund_money += parseFloat(formData.delivery_money);
        isShowDelivery.value = true;
    }
    formData.refund_money = formData.refund_money.toFixed(2);
}

const selectable = (row:any, index:number) => {
    let bool = false
    if (row.status == 1) {
        bool = true
    }
    if (row.is_gift == 1) {
        bool = false
    }

    return bool
}

/**
 * 确认
 * @param formEl
 */
const confirm = async (formEl: FormInstance | undefined) => {
    if (loading.value || !formEl) return
    if (formData.order_goods_ids.length <= 0) {
        ElMessage({
            message: t('refundGoodsPlaceholder'),
            type: 'warning'
        })
        return
    }

    await formEl.validate(async (valid) => {
        if (valid) {
            loading.value = true
            const data = cloneDeep(formData)
            data.shop_active_refund_money = formData.refund_money;
            delete data.refund_money;
            shopActiveRefund(data).then(res => {
                loading.value = false
                showDialog.value = false
                emit('complete')
                initFormData();
            }).catch(err => {
            })
        }
    })
}

const setFormData = async (row: any = null) => {
    loading.value = true;
    if (row) {
        refundItemNum = 0;
        formData.refund_money = 0;
        formData.order_goods_ids = [];
        refundTable.value = row.order_goods;
        refundTable.value.forEach((item,index,arr) => {
            arr[index].refund_money = item.goods_money - item.discount_money;
            if(item.status == 1){
                refundItemNum++;
                formData.refund_money += arr[index].refund_money;
                formData.order_goods_ids.push(item.order_goods_id);
            }
        });
        formData.delivery_money = row.delivery_money;
        formData.refund_money += parseFloat(row.delivery_money);
        isShowDelivery.value = true;

        formData.refund_money = formData.refund_money.toFixed(2);
        formData.shop_active_refund_remark = '';
    }
    nextTick(()=>{
        setTimeout(()=>{
            refundTableRef.value.toggleAllSelection();
        },100)
    })
    loading.value = false
}

const initFormData = ()=> {
    formData.order_goods_id = 0
    formData.refund_money = 0
    formData.shop_active_refund_status = 'partial_refund'
    formData.shop_active_money_type = 'back_refund'
    formData.shop_active_refund_remark = ''
}

defineExpose({
    showDialog,
    setFormData
})
</script>

<style lang="scss" scoped></style>
<style lang="scss">
.diy-dialog-wrap .el-form-item__label {
    height: auto !important;
}
</style>

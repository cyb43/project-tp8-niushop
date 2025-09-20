<template>
    <el-dialog v-model="showDialog" :title="t('adjustMoneyDialogTitle')" width="1000px" class="diy-dialog-wrap" :destroy-on-close="true">
        <div class="max-h-[600px] overflow-y-auto">
            <h3 class="panel-title ml-[10px]">{{ t('adjustMoneyTips') }}</h3>
            <el-form label-width="50px" ref="formRef" class="page-form" v-if="goodsTypeArr.indexOf('real') != -1">
                <el-form-item :label="t('adjustMoneyDeliveryMoney')" prop="express_number">
                    <el-input v-model.trim="deliveryMoney" clearable placeholder="0.00" class="!w-[200px]" maxlength="8" @keyup="deliveryChange($event)">
                        <template #append>{{ t('adjustMoneyUnit') }}</template>
                    </el-input>
                </el-form-item>
            </el-form>
            <div class="mb-[20px]">
                <el-table :data="orderData.order_goods" size="large">
                    <el-table-column :label="t('adjustMoneyGoodsInfo')" align="left" width="200">
                        <template #default="{ row }">
                            <p class="multi-hidden text-[14px]">{{ row.goods_name }}</p>
                        </template>
                    </el-table-column>
                    <el-table-column prop="price" :label="t('adjustMoneyPrice')" min-width="50" align="left" />
                    <el-table-column prop="num" :label="t('adjustMoneyNum')" min-width="50" align="right" />
                    <el-table-column prop="goods_money" :label="t('adjustMoneySubTotal')" min-width="50" align="right" />
                    <el-table-column prop="discount_money" :label="t('adjustMoneyDiscountMoney')" min-width="50" align="right" />
                    <el-table-column prop="goods_name" :label="t('adjustMoneyLabel')" min-width="100">
                        <template v-slot:header>
                            <div>
                                <span>{{ t('adjustMoneyLabel') }}</span>
                                <el-tooltip effect="dark" content="负数表示下调金额，正数表示上调金额" placement="top">
                                    <text class="nc-iconfont nc-icon-bangzhuV6xx ml-[5px] text-[12px]"></text>
                                </el-tooltip>
                            </div>
                        </template>
                        <template #default="{ row, $index }">
                            <el-input v-model.trim="row.adjust_money" clearable placeholder="0.00" maxlength="6" @change="adjustChange($event,$index,row)">
                                <template #append>{{ t('adjustMoneyUnit') }}</template>
                            </el-input>
                        </template>
                    </el-table-column>
                    <el-table-column prop="total" :label="t('adjustMoneyTotal')" min-width="70" align="right" />
                </el-table>
            </div>

            <h3 class="panel-title ml-[10px]">
                <span class="text-primary">实际商品金额</span>
                <span> = 商品总额 - 优惠金额 + 调价</span>
            </h3>
            <h3 class="panel-title ml-[10px]">
                <span class="text-primary">订单总额</span>
                <span v-if="goodsTypeArr.indexOf('real') != -1"> = 实际商品金额 + 运费</span>
                <span v-else> = 实际商品金额</span>
            </h3>

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
import { ref, reactive } from 'vue'
import { t } from '@/lang'
import { orderEditPrice } from '@/addon/shop/api/order'
import { filterDigit } from '@/utils/common'
import { cloneDeep } from 'lodash-es'

const showDialog = ref(false)
const loading = ref(false)

const orderData: any = reactive({})
const deliveryMoney: any = ref(0)
const goodsTypeArr = ref<any>([]) // 商品类型
const deliveryChange = (e: any) => {
    filterDigit(e)
    deliveryMoney.value = e.target.value
}
const setFormData = (data: any) => {
    for (const key in orderData) {
        delete orderData[key]
    }
    Object.assign(orderData, cloneDeep(data))
    orderData.order_goods = orderData.order_goods.filter((item: any) => {
        return item.is_gift != 1
    })
    goodsTypeArr.value = []
    orderData.order_goods.forEach((item: any) => {
        item.adjust_money = '' // 调价
        item.total = (parseFloat(item.goods_money) - parseFloat(item.discount_money)).toFixed(2) // 总计
        goodsTypeArr.value.push(item.goods_type)
    })
    deliveryMoney.value = orderData.delivery_money
}

// 监听调价计算总计
const adjustChange = (value: any, index: any, item: any) => {
    let money = parseFloat(item.goods_money) - parseFloat(item.discount_money)
    if (value.length == 0 || isNaN(value)) {
        value = 0
        orderData.order_goods[index].adjust_money = ''
    } else {
        value = parseFloat(value)
    }

    if (parseFloat(value) + money < 0) {
        value = money
        orderData.order_goods[index].adjust_money = -value
        money = 0
    } else {
        money += value
    }

    money = Math.round(money * 100) / 100

    orderData.order_goods[index].total = money.toFixed(2)
}

const emit = defineEmits(['complete'])

const isRepeat = ref(false)
const confirm = () => {
    loading.value = true

    if (isRepeat.value) return
    isRepeat.value = true

    const order_goods_data: any = {}
    orderData.order_goods.forEach((item: any) => {
        if (item.adjust_money) {
            order_goods_data[item.order_goods_id] = {
                money: item.adjust_money
            }
        }
    })
    orderEditPrice({
        order_id: orderData.order_id,
        delivery_money: parseFloat(deliveryMoney.value),
        order_goods_data
    }).then((res: any) => {
        isRepeat.value = false
        loading.value = false
        showDialog.value = false
        emit('complete')
    }).catch(() => {
        isRepeat.value = false
        loading.value = false
    })
}

// 提示信息
defineExpose({
    showDialog,
    setFormData
})
</script>

<style lang="scss" scoped></style>
<style lang="scss">
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

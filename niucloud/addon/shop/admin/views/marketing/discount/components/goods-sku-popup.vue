<template>
    <el-drawer v-model="showDialog" :title="t('skuDiscountSettings')" size="60%">
        <el-form :model="formData" label-width="120px" ref="formRef" class="page-form" v-if="showDialog">
            <div class="w-full sku_list">
                <el-table class="!w-[1400px] !max-w-[100%]" v-if="goodsTable.list.length" :data="goodsTable.list" size="large" ref="goods_listTableRef" @selection-change="handleSelectionChange">
                    <template #empty>
                        <span>{{ t('emptyData') }}</span>
                    </template>
                    <el-table-column type="selection" width="55" />
                    <el-table-column :label="t('goodsSelectPopupGoodsInfo')" min-width="300">
                        <template #default="{ row }">
                            <div class="flex items-center cursor-pointer">
                                <div class="min-w-[60px] h-[60px] flex items-center justify-center">
                                    <!-- {{ img(row.sku_image) }} -->
                                    <el-image v-if="row.sku_image" class="w-[60px] h-[60px]" :src="img(row.sku_image)" fit="contain">
                                        <template #error>
                                            <div class="image-slot">
                                                <img class="w-[60px] h-[60px]" src="@/addon/shop/assets/goods_default.png" />
                                            </div>
                                        </template>
                                    </el-image>
                                    <img v-else class="w-[70px] h-[60px]" src="@/addon/shop/assets/goods_default.png" fit="contain" />
                                </div>
                                <div class="ml-2">
                                    <span :title="row.sku_name" class="multi-hidden">{{ row.sku_name || row.goods?.goods_name }}</span>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="price" :label="t('price')" min-width="130" />

                    <el-table-column :label="t('discounts')" width="170">
                        <template #default="{ row }">
                            <el-form-item  v-if="row.is_enabled" :key="row.sku_id" :prop="'skuList.'+row.index + '.discount_rate'" :rules="[{
                                        trigger: 'blur',
                                        validator: (rule: any, value: any, callback: any) => {
                                            if (value.length == 0) {
                                                callback(t('discountsPlaceholder'))
                                            } else if (isNaN(value) || !regExp.number.test(value)) {
                                                callback(t('discountsTips'))
                                            } else if (value <= 0) {
                                                callback(t('discountsTipsTwo'))
                                            } else if (value>9.9) {
                                                callback(t('discountsTipsThree'))
                                            } else {
                                                callback();
                                            }
                                        }
                                    }]" class="sku-form-item-wrap">
                                <el-input v-model.trim="row.discount_rate" @blur="inputBlur(row,'discount',row.index)" clearable placeholder="0" maxlength="8" />
                            </el-form-item>
                            <span v-else>{{ row.discount_rate }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('reduceMoney')" width="170">
                        <template #default="{ row }">
                            <el-form-item v-if="row.is_enabled" :key="row.sku_id" :prop="'skuList.'+row.index + '.reduce_money'" :rules="[{
                                        trigger: 'blur',
                                        validator: (rule: any, value: any, callback: any) => {
                                                if (value.length == 0) {
                                                    callback(t('reduceMoneyPlaceholder'))
                                                } else if (isNaN(value) || !regExp.digit.test(value)) {
                                                    callback(t('reduceMoneyTips'))
                                                } else if (value <=0) {
                                                    callback(t('reduceMoneyTipsTwo'))
                                                } else if (value >= parseFloat(row.price) ) {
                                                    callback(t('reduceMoneyTipsThree'))
                                                } else {
                                                    callback();
                                                }
                                        }
                                        }]" class="sku-form-item-wrap">
                                <el-input v-model.trim="row.reduce_money" @blur="inputBlur(row,'reduce',row.index)" clearable placeholder="0.00" maxlength="8" />
                            </el-form-item>
                            <span v-else>{{ row.reduce_money }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('promotional')" width="170">
                        <template #default="{ row }">
                            <el-form-item v-if="row.is_enabled" :key="row.sku_id"
                                          :prop="'skuList.'+row.index + '.specify_price'" :rules="[{
                                        trigger: 'blur',
                                        validator: (rule: any, value: any, callback: any) => {
                                                if (value.length == 0) {
                                                    callback(t('promotionalPlaceholder'))
                                                } else if (isNaN(value) || !regExp.digit.test(value)) {
                                                    callback(t('promotionalTips'))
                                                } else if (value <=0 ) {
                                                    callback(t('promotionalTipsTwo'))
                                                }else if (value >= parseFloat(row.price) ) {
                                                    callback(t('promotionalTipsThree'))
                                                } else {
                                                    callback();
                                                }
                                        }
                                    }]" class="sku-form-item-wrap">
                                <el-input v-model.trim="row.specify_price" clearable @blur="inputBlur(row,'specify',row.index)" placeholder="0.00" maxlength="8" />
                            </el-form-item>
                            <span v-else>{{ row.specify_price }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('discountType')" width="130">
                        <template #default="{ row }">
                            <span>{{ row.discount_type == 'discount' ? t('discounts') : row.discount_type == 'reduce' ? t('reduceMoney') : t('promotional') }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('operation')" fixed="right" align="right" min-width="160">
                        <template #default="{row}">
                            <el-button type="primary" link @click="enabledEvent(row)">{{ row.is_enabled ? t('noEnabled') : t('enabled') }}</el-button>
                        </template>
                    </el-table-column>
                </el-table>
                <div class="flex items-center justify-between mt-[15px] !w-[1400px] !max-w-[100%]">
                    <div class="flex items-center mb-[15px]">
                        <el-checkbox v-model="toggleCheckbox" size="large" class="!mr-[15px]" @change="toggleChange" :indeterminate="isIndeterminate">
                            <span>已选{{ multipleSelection.length }}项</span>
                        </el-checkbox>

                        <label>{{ t('batchOperation') }}</label>
                        <el-select v-model="batchOperation.discount_type" class="!w-[130px] ml-[10px]" @change="batchOperation.discountNumber=''">
                            <el-option :label="t('discounts')" value="discount" />
                            <el-option :label="t('reduceMoney')" value="reduce" />
                            <el-option :label="t('promotional')" value="specify" />
                        </el-select>
                        <el-input v-model.trim="batchOperation.discountNumber" clearable
                                  :placeholder="batchOperation.discount_type=='discount'?t('discounts'):batchOperation.discount_type=='reduce'?t('reduceMoney'):t('promotional')"
                                  class="!w-[130px] ml-[10px]" maxlength="8" />
                        <el-button class="ml-[10px]" type="primary" @click="saveBatch">{{ t('confirm') }}</el-button>
                    </div>
                    <el-pagination v-model:current-page="goodsTable.page" v-model:page-size="goodsTable.limit"
                                   layout="total, prev, pager, next, jumper" :total="goodsTable.total"
                                   @current-change="setGoodsList" />
                </div>
            </div>
        </el-form>
        <template #footer>
            <span class="dialog-footer">
                <el-button @click="showDialog = false">{{ t("cancel") }}</el-button>
                <el-button type="primary" @click="save(formRef)">{{ t("confirm") }}</el-button>
            </span>
        </template>
    </el-drawer>
</template>

<script lang="ts" setup>
import { t } from "@/lang";
import { img, deepClone } from "@/utils/common";
import { FormInstance, ElMessage } from 'element-plus'
import { ref, reactive, nextTick } from "vue";

const showDialog = ref(false);
const emit = defineEmits(["skuSave"]);
const formData: Record<string, any> = ref({ skuList: [] })
const formRef = ref<FormInstance>()

// 表单验证规则
// 正则表达式
const regExp = {
    number: /^\d{0,10}(.?\d{0,1})$/,
    digit: /^\d{0,10}(.?\d{0,2})$/
}

const show = (data: any) => {
    formData.value = deepClone(data)
    //设置校验下标
    formData.value.skuList.forEach((el: any, index: number) => {
        // if (el.is_enabled == 1){

        // }else{
        //     el.is_enabled = 0
        // }
        el.index = index
    })
    setGoodsList()
    showDialog.value = true;
}

//设置展示商品
interface goodsTableInterface {
    page: number,
    limit: number,
    total: number,
    data: any,
    list: Array<any>,
}

const goodsTable = reactive<goodsTableInterface>({
    page: 1,
    limit: 5,
    total: 0,
    data: [],
    list: []
})

const setGoodsList = (page = 1) => {
    if (formData.value.skuList.length) {
        goodsTable.data = splitArray(formData.value.skuList, goodsTable.limit)
        goodsTable.list = goodsTable.data[page - 1]
        goodsTable.total = parseInt(formData.value.skuList.length.toString())
        nextTick(() => {
            batchOperation.value.discountNumber = ''
            multipleSelection.value = []
            if (goods_listTableRef.value) goods_listTableRef.value.clearSelection()
            if (isIndeterminate.value) isIndeterminate.value = false
            if (toggleCheckbox.value) toggleCheckbox.value = false
        })
    } else {
        goodsTable.data = []
        goodsTable.list = []
        goodsTable.total = 0
        goodsTable.page = 1
    }
}

//完整数据转分页数据
const splitArray = (array: [], size: number) => {
    var result = [];
    for (var i = 0; i < array.length; i += size) {
        result.push(array.slice(i, i + size));
    }
    return result;
}

/*****批量设置 ****/
interface batchOperationInterface {
    discount_type: any,
    discountNumber: any,
}

const batchOperation = ref<batchOperationInterface>({
    discount_type: 'discount',
    discountNumber: ''
})

// 批量复选框
const toggleCheckbox = ref()

// 复选框中间状态
const isIndeterminate = ref(false)

// 监听批量复选框事件
const toggleChange = (value: any) => {
    isIndeterminate.value = false
    goods_listTableRef.value.toggleAllSelection()
}
const goods_listTableRef = ref()

// 选中数据
const multipleSelection: any = ref([])

// 监听表格单行选中
const handleSelectionChange = (val: []) => {
    multipleSelection.value = val

    toggleCheckbox.value = false
    if (multipleSelection.value.length > 0 && multipleSelection.value.length < goodsTable.list.length) {
        isIndeterminate.value = true
    } else {
        isIndeterminate.value = false
    }

    if (multipleSelection.value.length == goodsTable.list.length) {
        toggleCheckbox.value = true
    }
}
const saveBatch = () => {
    if (!multipleSelection.value.length) {
        ElMessage({
            type: 'warning',
            message: `${ t('batchEmptySelectedGoodsTips') }`
        })
        return
    }
    if (batchOperation.value.discount_type == 'discount') {
        if (batchOperation.value.discountNumber.length == 0) {
            ElMessage({
                type: 'warning',
                message: `${ t('discountsPlaceholder') }`
            })
            return
        } else if (isNaN(batchOperation.value.discountNumber) || !regExp.number.test(batchOperation.value.discountNumber)) {
            ElMessage({
                type: 'warning',
                message: `${ t('discountsTips') }`
            })
            return
        } else if (batchOperation.value.discountNumber <= 0) {
            ElMessage({
                type: 'warning',
                message: `${ t('discountsTipsTwo') }`
            })
            return
        } else if (batchOperation.value.discountNumber > 9.9) {
            ElMessage({
                type: 'warning',
                message: `${ t('discountsTipsThree') }`
            })
            return
        }
    } else if (batchOperation.value.discount_type == 'reduce') {
        if (batchOperation.value.discountNumber.length == 0) {
            ElMessage({
                type: 'warning',
                message: `${ t('reduceMoneyPlaceholder') }`
            })
            return
        } else if (isNaN(batchOperation.value.discountNumber) || !regExp.digit.test(batchOperation.value.discountNumber)) {
            ElMessage({
                type: 'warning',
                message: `${ t('reduceMoneyTips') }`
            })
            return
        } else if (batchOperation.value.discountNumber <= 0) {
            ElMessage({
                type: 'warning',
                message: `${ t('reduceMoneyTipsTwo') }`
            })
            return
        }

    } else {
        if (batchOperation.value.discountNumber.length == 0) {
            ElMessage({
                type: 'warning',
                message: `${ t('promotionalPlaceholder') }`
            })
            return
        } else if (isNaN(batchOperation.value.discountNumber) || !regExp.digit.test(batchOperation.value.discountNumber)) {
            ElMessage({
                type: 'warning',
                message: `${ t('promotionalTips') }`
            })
            return
        } else if (batchOperation.value.discountNumber <= 0) {
            ElMessage({
                type: 'warning',
                message: `${ t('promotionalTipsTwo') }`
            })
            return
        }
    }
    formData.value.skuList.forEach((el: any, index: number) => {
        multipleSelection.value.forEach((v: any) => {

            if (v.sku_id === el.sku_id && el.is_enabled === 1) {

                if (batchOperation.value.discount_type == 'discount') {
                    //折扣
                    el.discount_rate = batchOperation.value.discountNumber + ''
                    //实际
                    el.specify_price = (el.price * (batchOperation.value.discountNumber / 10)).toFixed(2)
                    el.discount_price = (el.price * (batchOperation.value.discountNumber / 10)).toFixed(2)
                    //减价
                    el.reduce_money = (el.price - el.specify_price).toFixed(2)
                } else if (batchOperation.value.discount_type == 'reduce') {//减价
                    el.reduce_money = batchOperation.value.discountNumber + ''
                    el.specify_price = (el.price - el.reduce_money).toFixed(2)
                    el.discount_price = (el.price - el.reduce_money).toFixed(2)
                    el.discount_rate = (el.specify_price / el.price * 10).toFixed(1)

                } else {//实际
                    el.specify_price = batchOperation.value.discountNumber + ''
                    el.discount_price = batchOperation.value.discountNumber + ''
                    el.reduce_money = (el.price - el.specify_price).toFixed(2)
                    el.discount_rate = (el.specify_price / el.price * 10).toFixed(1)
                }
                el.discount_type = batchOperation.value.discount_type + ''
                if (formRef.value) {
                    formRef.value.validateField('skuList.' + index + '.discount_rate')
                    formRef.value.validateField('skuList.' + index + '.specify_price')
                    formRef.value.validateField('skuList.' + index + '.reduce_money')
                }
            }
        })
    })
    // isIndeterminate.value = false
    // toggleCheckbox.value = false
    // batchOperation.value.discountNumber = ''
    // goods_listTableRef.value.clearSelection()
}

/**** 修改单行 *****/
const inputBlur = (row: any, discount_type: string, index: number) => {
    if (discount_type == 'discount') {
        if (row.discount_rate.length) {
            //实际
            row.specify_price = (row.price * (row.discount_rate / 10)).toFixed(2)
            row.discount_price = (row.price * (row.discount_rate / 10)).toFixed(2)
            //减价
            row.reduce_money = (row.price - row.specify_price).toFixed(2)
        }
    } else if (discount_type == 'reduce') { // 减价
        if (row.reduce_money.length) {
            row.specify_price = (row.price - row.reduce_money).toFixed(2)
            row.discount_price = (row.price - row.reduce_money).toFixed(2)
            row.discount_rate = (row.specify_price / row.price * 10).toFixed(1)
        }

    } else { // 实际
        if (row.specify_price.length) {
            row.discount_price = row.specify_price + ''
            row.reduce_money = (row.price - row.specify_price).toFixed(2)
            row.discount_rate = (row.specify_price / row.price * 10).toFixed(1)
        }

    }
    row.discount_type = discount_type + ''
    if (formRef.value) {
        // console.log('skuList.' + index + '.discount_rate')
        // formRef.value.validateField('skuList.'+ index + '.discount_rate').catch(()=>{})
        // formRef.value.validateField('skuList.'+ index + '.specify_price').catch(()=>{})
        // formRef.value.validateField('skuList.'+ index + '.reduce_money').catch(()=>{})
    }
}

//sku状态
const enabledEvent = (row: any) => {
    row.is_enabled = row.is_enabled ? 0 : 1
    if (row.is_enabled) {
        row.discount_type = 'discount'
        row.discount_rate = ''
        row.reduce_money = ''
        row.specify_price = ''
    } else {
        row.discount_type = 'discount'
        row.discount_rate = '10'
        row.reduce_money = '0'
        row.specify_price = row.price + ''
    }
}
const validFn = (row: any) => {
    if (row.is_enabled === 0) {
        return true
    }
    if (row.discount_rate.length == 0) {
        return false
    } else if (isNaN(row.discount_rate) || !regExp.number.test(row.discount_rate)) {
        return false
    } else if (row.discount_rate <= 0) {
        return false
    } else if (row.discount_rate > 9.9) {
        return false
    } else if (row.reduce_money.length == 0) {
        return false
    } else if (isNaN(row.reduce_money) || !regExp.digit.test(row.reduce_money)) {
        return false
    } else if (row.reduce_money <= 0) {
        return false
    } else if (row.reduce_money >= parseFloat(row.price)) {
        return false
    } else if (row.specify_price.length == 0) {
        return false
    } else if (isNaN(row.specify_price) || !regExp.digit.test(row.specify_price)) {
        return false
    } else if (row.specify_price <= 0) {
        return false
    } else if (row.specify_price >= parseFloat(row.price)) {
        return false
    } else {
        return true
    }
}

const save = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    for (var i = 0; i < formData.value.skuList.length; i++) {
        if (!validFn(formData.value.skuList[i])) {
            let page = Math.ceil(i + 1 <= goodsTable.limit ? 1 : (i + 1) / goodsTable.limit)
            goodsTable.list = goodsTable.data[page - 1]
            goodsTable.page = page
            break;
        }

    }
    nextTick(async() => {
        await formEl.validate((valid) => {
            if (valid) {
                formData.value.valid = true
                let discount_rate_list = formData.value.skuList.filter((el: any) => el.is_enabled === 1).map((el: any) => Number(el.discount_rate))
                let reduce_money_list = formData.value.skuList.filter((el: any) => el.is_enabled === 1).map((el: any) => Number(el.reduce_money))
                let specify_price_list = formData.value.skuList.filter((el: any) => el.is_enabled === 1).map((el: any) => Number(el.specify_price))
                formData.value.max_discount_rate = Math.max(...discount_rate_list)
                formData.value.min_discount_rate = Math.min(...discount_rate_list)
                formData.value.max_reduce_money = Math.max(...reduce_money_list)
                formData.value.min_reduce_money = Math.min(...reduce_money_list)
                formData.value.max_specify_price = Math.max(...specify_price_list)
                formData.value.min_specify_price = Math.min(...specify_price_list)
                emit("skuSave", formData.value);
                showDialog.value = false;
            }
        })
    })

};
defineExpose({
    show
});
</script>

<style lang="scss" scoped>
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    -o-appearance: none !important;
    -ms-appearance: none !important;
    appearance: none !important;
    margin: 0;
}

input[type="number"] {
    -webkit-appearance: textfield;
    -moz-appearance: textfield;
    -o-appearance: textfield;
    -ms-appearance: textfield;
    appearance: textfield;
}
.sku-form-item-wrap{
    margin-bottom: 0 !important;
}
.sku-form-item-wrap :deep(.el-form-item__content) {
    margin-left: 0 !important;
}

.sku_list :deep(.cell) {
    // min-height: 60px !important;
    overflow: initial !important;
}
</style>

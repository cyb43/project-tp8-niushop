<template>
    <div class="main-container">

        <el-card class="card !border-none" shadow="never">
            <el-page-header :content="pageName" :icon="ArrowLeft" @back="back()"/>
        </el-card>

        <!-- 表单 -->
        <el-card class="box-card mt-[15px] !border-none" shadow="never" v-loading="loading">
            <el-form :model="formData" label-width="120px" ref="formRef" :rules="formRules" class="page-form">
                <!-- 活动名称： -->
                <el-form-item :label="t('name')" prop="name">
                    <div>
                        <el-input v-model.trim="formData.name" clearable :placeholder="t('namePlaceholder')" class="input-width" :maxlength="20"/>
                        <p class=" text-[14px] text-[#999]">{{ t('nameTip') }}</p>
                    </div>
                </el-form-item>
                <!-- 活动标题： -->
                <el-form-item :label="t('title')" prop="remark">
                    <div>
                        <el-input v-model.trim="formData.remark" clearable :placeholder="t('titlePlaceholder')" class="input-width" :maxlength="20"/>
                        <p class=" text-[14px] text-[#999]">{{ t('titleTip') }}</p>
                    </div>
                </el-form-item>
                <!-- 活动时间 -->
                <el-form-item :label="t('activityTime')" prop="discount_time">
                    <div class="w-[180px]">
                        <el-date-picker v-model="formData.discount_time" type="datetimerange" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期"/>
                    </div>
                </el-form-item>
                <!-- 选择商品 -->
                <el-form-item :label="t('selectProduct')" class="!m-0">
                    <goods-select-popup ref="goodsSelectPopupRef" v-model="formData.goods_ids" @goodsSelect="goodsSelect" :min="1" :max="99"/>
                </el-form-item>
                <el-form-item prop="goods_list"></el-form-item>
                <el-form-item v-if="formData.goods_list.length&& goodsTable.data">
                    <div class="w-full sku_list">
                        <el-table class="!w-[1400px] !max-w-[100%]" :data="goodsTable.list" size="large" ref="goods_listTableRef" @selection-change="handleSelectionChange">
                            <template #empty>
                                <span>{{ t('emptyData') }}</span>
                            </template>

                            <el-table-column type="selection" width="55"/>
                            <el-table-column :label="t('goodsSelectPopupGoodsInfo')" min-width="300">
                                <template #default="{ row }">
                                    <el-form-item :prop="'goods_list.' + row.index + '.goods_id'" :rules="[{
                                        trigger: 'blur',
                                        validator: (rule: any, value: any, callback: any) => {
                                            if (validchcklist.length > 0) {
                                                let hasError = false;
                                                validchcklist.forEach((item: any) => {
                                                    if (row.goods_id == item.goods_id) {
                                                        hasError = true;
                                                        callback(item.error_msg);
                                                    }
                                                });
                                                if (!hasError) {
                                                    callback(); // 校验通过
                                                }
                                            } else {
                                                callback(); // 校验通过
                                            }

                                        }
                                    }]" class="sku-form-item-wrap py-[10px]">
                                        <div class="flex items-center cursor-pointer">
                                            <div class="min-w-[60px] h-[60px] flex items-center justify-center">
                                                <el-image v-if="row.goods_cover_thumb_small" class="w-[60px] h-[60px]" :src="img(row.goods_cover_thumb_small)" fit="contain">
                                                    <template #error>
                                                        <div class="image-slot">
                                                            <img class="w-[60px] h-[60px]" src="@/addon/shop/assets/goods_default.png"/>
                                                        </div>
                                                    </template>
                                                </el-image>
                                                <img v-else class="w-[70px] h-[60px]" src="@/addon/shop/assets/goods_default.png" fit="contain"/>
                                            </div>
                                            <div class="ml-2">
                                                <span :title="row.goods_name" class="multi-hidden">{{ row.goods_name }}</span>
                                                <span class="text-primary text-[12px]">{{ row.goods_type_name }}</span>
                                            </div>
                                        </div>
                                    </el-form-item>
                                </template>
                            </el-table-column>
                            <el-table-column prop="price" :label="t('price')" min-width="130">
                                <template #default="{ row }">
                                    <div>￥{{ row.goodsSku.price }}</div>
                                </template>
                            </el-table-column>

                            <el-table-column :label="t('discounts')" width="170">
                                <template #default="{ row,$index }">

                                    <el-form-item v-if="!row.goodsSku.sku_spec_format" :key="row.goods_id" :prop="'goods_list.'+row.index + '.discount_rate'" :rules="[{
                                        trigger: 'blur',
                                        validator: (rule: any, value: any, callback: any) => {
                                            if (value.length == 0) {
                                                callback(t('discountsPlaceholder'))
                                            } else if (isNaN(value) || !regExp.number.test(value)) {
                                                callback(t('discountsTips'))
                                            } else if (value < 0) {
                                                callback(t('discountsTipsTwo'))
                                            } else if (value>9.9) {
                                                callback(t('discountsTipsThree'))
                                            } else {
                                                callback();
                                            }
                                        }
                                    }]" class="sku-form-item-wrap">
                                        <el-input v-model.trim="row.discount_rate" @blur="inputBlur(row,'discount',row.index)" clearable placeholder="0" maxlength="8"/>
                                    </el-form-item>
                                    <el-form-item :prop="'goods_list.'+row.index + '.min_discount_rate'" :rules="[{
                                        trigger: 'blur',
                                        validator: (rule: any, value: any, callback: any) => {
                                            if (value === '' || value === null || value === undefined ) {
                                                callback(t('skuDiscountSettingsPlaceholder'))
                                            } else if(row.min_discount_rate<0 || row.max_discount_rate<0){
                                                callback(t('discountsTips'));
                                            }else if(row.min_discount_rate>9.9 || row.max_discount_rate>9.9){
                                                callback(t('discountsTips'));
                                            }else {
                                                callback();
                                            }
                                        }
                                    }]" v-else>
                                        <span v-if="row.valid && row.min_discount_rate!=Infinity&&row.max_discount_rate!=-Infinity">{{ row.min_discount_rate == row.max_discount_rate ? row.min_discount_rate : row.min_discount_rate + '-' + row.max_discount_rate }}</span>
                                        <span v-else>--</span>
                                    </el-form-item>
                                </template>
                            </el-table-column>
                            <el-table-column :label="t('reduceMoney')" width="170">
                                <template #default="{ row,$index }">
                                    <el-form-item v-if="!row.goodsSku.sku_spec_format" :key="row.goods_id" :prop="'goods_list.'+row.index + '.reduce_money'" :rules="[{
                                        trigger: 'blur',
                                        validator: (rule: any, value: any, callback: any) => {
                                                if (value.length == 0) {
                                                    callback(t('reduceMoneyPlaceholder'))
                                                } else if (isNaN(value) || !regExp.digit.test(value)) {
                                                    callback(t('reduceMoneyTips'))
                                                } else if (value <0) {
                                                    callback(t('reduceMoneyTipsTwo'))
                                                } else if (value > parseFloat(row.goodsSku.price) ) {
                                                    callback(t('reduceMoneyTipsThree'))
                                                } else {
                                                    callback();
                                                }
                                        }
                                        }]" class="sku-form-item-wrap">
                                        <el-input v-model.trim="row.reduce_money" @blur="inputBlur(row,'reduce',row.index)" clearable placeholder="0.00" maxlength="8"/>
                                    </el-form-item>
                                    <el-form-item v-else>
                                        <span v-if="row.valid && row.min_reduce_money!=Infinity&&row.max_reduce_money!=-Infinity">{{ row.min_reduce_money == row.max_reduce_money ? row.min_reduce_money : row.min_reduce_money + '-' + row.max_reduce_money }}</span>
                                        <span v-else>--</span>
                                    </el-form-item>
                                </template>
                            </el-table-column>
                            <el-table-column :label="t('promotional')" width="170">
                                <template #default="{ row,$index }">
                                    <el-form-item v-if="!row.goodsSku.sku_spec_format" :key="row.goods_id" :prop="'goods_list.'+row.index + '.specify_price'" :rules="[{
                                        trigger: 'blur',
                                        validator: (rule: any, value: any, callback: any) => {
                                                if (value.length == 0) {
                                                    callback(t('promotionalPlaceholder'))
                                                } else if (isNaN(value) || !regExp.digit.test(value)) {
                                                    callback(t('promotionalTips'))
                                                } else if (value <0 ) {
                                                    callback(t('promotionalTipsTwo'))
                                                }else if (value > parseFloat(row.goodsSku.price) ) {
                                                    callback(t('promotionalTipsThree'))
                                                } else {
                                                    callback();
                                                }
                                        }
                                    }]" class="sku-form-item-wrap">
                                        <el-input v-model.trim="row.specify_price" clearable @blur="inputBlur(row,'specify',row.index)" placeholder="0.00" maxlength="8"/>
                                    </el-form-item>
                                    <el-form-item v-else>
                                        <span v-if="row.valid && row.min_specify_price!=Infinity&&row.max_specify_price!=-Infinity">{{ row.min_specify_price == row.max_specify_price ? row.min_specify_price : row.min_specify_price + '-' + row.max_specify_price }}</span>
                                        <span v-else>--</span>
                                    </el-form-item>
                                </template>
                            </el-table-column>
                            <el-table-column :label="t('discountType')" width="130">
                                <template #default="{ row }">
                                    <span v-if="!row.goodsSku.sku_spec_format">{{ row.discount_type == 'discount' ? t('discounts') : row.discount_type == 'reduce' ? t('reduceMoney') : t('promotional') }}</span>
                                    <el-form-item v-else>请在设置中查看</el-form-item>
                                </template>
                            </el-table-column>
                            <el-table-column :label="t('operation')" align="right" min-width="160">
                                <template #default="{row}">
                                    <el-button v-if="row.goodsSku.sku_spec_format" type="primary" link @click="skuDiscountSettingsEvent(formData.goods_list[row.index])">{{ t('skuDiscountSettings') }}</el-button>
                                    <el-button type="primary" link @click="deleteEvent(row.index)">{{ t('delete') }}</el-button>
                                </template>
                            </el-table-column>
                        </el-table>
                        <div class="flex items-center justify-between mt-[15px] !w-[1400px] !max-w-[100%]">
                            <div class="flex items-center mb-[15px]">
                                <el-checkbox v-model="toggleCheckbox" size="large" class="!mr-[15px]" @change="toggleChange" :indeterminate="isIndeterminate">
                                    <span>已选 {{ multipleSelection.length }} 项</span>
                                </el-checkbox>

                                <label>{{ t('batchOperation') }}</label>
                                <el-select v-model="batchOperation.discount_type" class="!w-[130px] ml-[10px]" @change="batchOperation.discountNumber=''">
                                    <el-option :label="t('discounts')" value="discount"/>
                                    <el-option :label="t('reduceMoney')" value="reduce"/>
                                    <el-option :label="t('promotional')" value="specify"/>
                                </el-select>
                                <el-input v-model.trim="batchOperation.discountNumber" clearable :placeholder="batchOperation.discount_type=='discount'?t('discounts'):batchOperation.discount_type=='reduce'?t('reduceMoney'):t('promotional')" class="!w-[130px] ml-[10px]" maxlength="8"/>
                                <el-button class="ml-[10px]" type="primary" @click="saveBatch">{{ t('confirm') }}
                                </el-button>
                            </div>
                            <el-pagination v-model:current-page="goodsTable.page" v-model:page-size="goodsTable.limit"
                                           layout="total, prev, pager, next, jumper" :total="goodsTable.total"
                                           @current-change="setGoodsList"/>
                        </div>
                    </div>
                </el-form-item>
            </el-form>
        </el-card>

        <goods-sku-popup ref="goodsSkuPopupRef" @skuSave="skuSave"/>

        <!-- 提交按钮 -->
        <div class="fixed-footer-wrap">
            <div class="fixed-footer">
                <el-button type="primary" @click="onSave(formRef)">{{ t('save') }}</el-button>
                <el-button @click="back()">{{ t('cancel') }}</el-button>
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { ref, computed, reactive } from 'vue'
import { t } from '@/lang'
import { useRoute, useRouter } from 'vue-router'
import { addActiveDiscount, goodscheck } from "@/addon/shop/api/marketing";
import { FormInstance, ElMessage } from 'element-plus'
import { ArrowLeft } from '@element-plus/icons-vue'
import { deepClone, img } from '@/utils/common'
import goodsSelectPopup from '@/addon/shop/views/goods/components/goods-select-popup.vue'
import goodsSkuPopup from '@/addon/shop/views/marketing/discount/components/goods-sku-popup.vue'

const router = useRouter()
const route = useRoute()
const pageName = route.meta.title

const loading = ref(false)
const start_time = new Date()
const end_time = new Date()
const goodsSelectPopupRef: any = ref(null)
end_time.setTime(end_time.getTime() + 3600 * 1000 * 2 * 360) // 设置结束默认时间为当前时间30天后

const initialFormData = {
    name: '', // 名称
    remark: '', // 标题
    active_status: '',
    start_time: '',
    end_time: '',
    goods_data: '',
    goods_ids: [],
    goods_list: [],
    discount_time: [start_time, end_time],
}
const formData: Record<string, any> = ref({ ...initialFormData })
const formRef = ref<FormInstance>()

// 表单验证规则
// 正则表达式
const regExp = {
    number: /^\d{0,10}(.?\d{0,1})$/,
    digit: /^\d{0,10}(.?\d{0,2})$/
}

const formRules = computed(() => {
    return {
        name: [
            { required: true, message: t('namePlaceholder'), trigger: 'blur' },
            { validator: noSpaceValidator, trigger: 'blur' }
        ],
        remark: [
            { required: true, message: t('titlePlaceholder'), trigger: 'blur' },
            { validator: noSpaceValidator, trigger: 'blur' }
        ],
        goods_list: [
            { required: true, message: t('selectProductPlaceholder'), trigger: 'change' }
        ],
        discount_time: [
            { required: true, validator: receiveTime, trigger: 'change' }
        ],
    }
})

const noSpaceValidator = (rule: any, value: any, callback: any) => {
    if (value.trim() === '') {
        return callback(new Error(t('noSpaceAllowed')));
    }
    callback(); // 通过验证
}

const receiveTime = (rule: any, value: any, callback: any) => {
    if (!formData.value.discount_time || (formData.value.discount_time && !formData.value.discount_time[0] && !formData.value.discount_time[1])) {
        callback(new Error(t('请选择活动时间')))
    } else if (!formData.value.discount_time[0]) {
        callback(new Error(t('请选择活动开始时间')))
    } else if (!formData.value.discount_time[1]) {
        callback(new Error(t('请选择活动结束时间')))
    } else if (formData.value.discount_time[1] <= formData.value.discount_time[0]) {
        callback(new Error(t('活动结束时间不能小于等于活动开始时间')))
    }
    callback()
}

const validFn = (row: any) => {
    if (row.discount_rate.length == 0) {
        return false
    } else if (isNaN(row.discount_rate) || !regExp.number.test(row.discount_rate)) {
        return false
    } else if (row.discount_rate < 0) {
        return false
    } else if (row.discount_rate > 9.9) {
        return false
    } else if (row.reduce_money.length == 0) {
        return false
    } else if (isNaN(row.reduce_money) || !regExp.digit.test(row.reduce_money)) {
        return false
    } else if (row.reduce_money < 0) {
        return false
    } else if (row.reduce_money > parseFloat(row.goodsSku.price)) {
        return false
    } else if (row.specify_price.length == 0) {
        return false
    } else if (isNaN(row.specify_price) || !regExp.digit.test(row.specify_price)) {
        return false
    } else if (row.specify_price < 0) {
        return false
    } else if (row.specify_price > parseFloat(row.goodsSku.price)) {
        return false
    } else {
        return true
    }
}

const onSave = async (formEl: FormInstance | undefined) => {
    if (loading.value || !formEl) return
    for (var i = 0; i < formData.value.goods_list.length; i++) {
        let el = formData.value.goods_list[i]
        if (el.goodsSku.sku_spec_format) {
            if (!el.valid) {
                let page = Math.ceil(i + 1 <= goodsTable.limit ? 1 : (i + 1) / goodsTable.limit)
                goodsTable.list = goodsTable.data[page - 1]
                goodsTable.page = page
                break;
            } else {
                el.sku_list = el.skuList
            }
        } else {
            if (!validFn(el)) {
                let page = Math.ceil(i + 1 <= goodsTable.limit ? 1 : (i + 1) / goodsTable.limit)
                goodsTable.list = goodsTable.data[page - 1]
                goodsTable.page = page
                break;
            } else {
                el.skuList[0].discount_rate = el.discount_rate
                el.skuList[0].reduce_money = el.reduce_money
                el.skuList[0].specify_price = el.specify_price
                el.skuList[0].discount_price = el.discount_price
                el.skuList[0].discount_type = el.discount_type
                el.skuList[0].is_enabled = el.is_enabled
                el.sku_list = el.skuList
            }
        }
    }
    await formEl.validate(async (valid) => {
        if (valid) {
            loading.value = true
            formData.value.start_time = formData.value.discount_time[0]
            formData.value.end_time = formData.value.discount_time[1]
            formData.value.goods_data = JSON.stringify(formData.value.goods_list)
            addActiveDiscount(formData.value).then(res => {
                loading.value = false
                history.back()
            }).catch(() => {
                loading.value = false
            })
        }
    })
}

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
const validchcklist = ref([])
const goodsSelect = (value: any) => {
    if (formData.value.goods_list.length) {
        let goods_list = deepClone(Object.values(value)).map((el: any, index: number) => {
            if (!el.goodsSku.sku_spec_format) {
                el.discount_type = 'discount'
                el.discount_rate = ''
                el.reduce_money = ''
                el.specify_price = ''
                el.is_enabled = 1
            } else {
                el.skuList = setSku(el.skuList)
            }
            el.index = index
            el.valid = false
            formData.value.goods_list.forEach((v: any) => {
                if (v.goods_id == el.goods_id) {
                    el = Object.assign(el, v)//合并已填写数据及新选择的数据
                    el.index = index
                }
            })
            return el
        })
        formData.value.goods_list = goods_list
    } else {
        formData.value.goods_list = deepClone(Object.values(value)).map((el: any, index: number) => {
            if (!el.goodsSku.sku_spec_format) {
                el.discount_type = 'discount'
                el.discount_rate = ''
                el.reduce_money = ''
                el.specify_price = ''
                el.is_enabled = 1
            } else {
                el.skuList = setSku(el.skuList)
            }
            el.index = index
            el.valid = false
            return el
        })
    }
    setGoodsList()
    if (formRef.value) formRef.value.validateField('goods_list').catch(() => {
    })
    goodscheck({
        goods_ids: formData.value.goods_ids,
        start_time: formData.value.discount_time[0],
        end_time: formData.value.discount_time[1],
        discount_id: ""
    }).then((res: any) => {
        validchcklist.value = res.data.data
        formRef.value.validateField().catch(() => {
        })
    })
}

//设置sku初始数据
const setSku = (sku: []) => {
    return sku.map((el: any) => {
        if (!el.discount_rate) {
            el.discount_type = 'discount'
            el.discount_rate = ''
            el.reduce_money = ''
            el.specify_price = ''
            el.is_enabled = 1
        }
        return el
    })
}

//设置展示商品
const setGoodsList = (page = 1) => {
    if (formData.value.goods_list.length) {
        goodsTable.data = splitArray(formData.value.goods_list, goodsTable.limit)
        goodsTable.list = goodsTable.data[page - 1]
        goodsTable.total = parseInt(formData.value.goods_list.length.toString())
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

//删除商品
const deleteEvent = (index: number) => {
    formData.value.goods_list.splice(index, 1)
    formData.value.goods_list.forEach((el: any, index: number) => {
        el.index = index
    })
    formData.value.goods_ids.splice(index, 1)
    setGoodsList(goodsTable.page)
    if (formRef.value) formRef.value.validateField('goods_list').catch(() => {
    })
    formRef.value.validateField().catch(() => {    })
}

//设置sku折扣
const goodsSkuPopupRef = ref()
const skuDiscountSettingsEvent = (row: any) => {
    goodsSkuPopupRef.value?.show(row)
}

const skuSave = (row: any) => {
    formData.value.goods_list.forEach((el: any) => {
        if (el.goods_id == row.goods_id) el = deepClone(Object.assign(el, row))
    })
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
        } else if (batchOperation.value.discountNumber < 0) {
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
        } else if (batchOperation.value.discountNumber < 0) {
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
        } else if (batchOperation.value.discountNumber < 0) {
            ElMessage({
                type: 'warning',
                message: `${ t('promotionalTipsTwo') }`
            })
            return
        }
    }
    formData.value.goods_list.forEach((el: any, index: number) => {
        multipleSelection.value.forEach((v: any) => {

            if (v.goods_id === el.goods_id) {
                if (!el.goodsSku.sku_spec_format) {
                    if (batchOperation.value.discount_type == 'discount') {
                        //折扣
                        el.discount_rate = batchOperation.value.discountNumber + ''
                        //实际
                        el.specify_price = (el.goodsSku.price * (batchOperation.value.discountNumber / 10)).toFixed(2)
                        el.discount_price = (el.goodsSku.price * (batchOperation.value.discountNumber / 10)).toFixed(2)
                        //减价
                        el.reduce_money = (el.goodsSku.price - el.specify_price).toFixed(2)
                    } else if (batchOperation.value.discount_type == 'reduce') {//减价
                        el.reduce_money = batchOperation.value.discountNumber + ''
                        el.specify_price = (el.goodsSku.price - el.reduce_money).toFixed(2)
                        el.discount_price = (el.goodsSku.price - el.reduce_money).toFixed(2)
                        el.discount_rate = (el.specify_price / el.goodsSku.price * 10).toFixed(1)

                    } else {//实际
                        el.specify_price = batchOperation.value.discountNumber + ''
                        el.discount_price = batchOperation.value.discountNumber + ''
                        el.reduce_money = (el.goodsSku.price - el.specify_price).toFixed(2)
                        el.discount_rate = (el.specify_price / el.goodsSku.price * 10).toFixed(1)
                    }
                    el.discount_type = batchOperation.value.discount_type + ''
                    if (formRef.value) {
                        formRef.value.validateField('goods_list.' + index + '.discount_rate')
                        formRef.value.validateField('goods_list.' + index + '.specify_price')
                        formRef.value.validateField('goods_list.' + index + '.reduce_money')
                    }
                } else {
                    el.skuList.forEach((sku: any) => {
                        if (sku.is_enabled === 1) {
                            if (batchOperation.value.discount_type == 'discount') {
                                //折扣
                                sku.discount_rate = batchOperation.value.discountNumber + ''
                                //实际
                                sku.specify_price = (sku.price * (batchOperation.value.discountNumber / 10)).toFixed(2)
                                sku.discount_price = (sku.price * (batchOperation.value.discountNumber / 10)).toFixed(2)
                                //减价
                                sku.reduce_money = (sku.price - sku.specify_price).toFixed(2)
                            } else if (batchOperation.value.discount_type == 'reduce') {//减价
                                sku.reduce_money = batchOperation.value.discountNumber + ''
                                sku.specify_price = (sku.price - sku.reduce_money).toFixed(2)
                                sku.discount_price = (sku.price - sku.reduce_money).toFixed(2)
                                sku.discount_rate = (sku.specify_price / sku.price * 10).toFixed(1)

                            } else {//实际
                                sku.specify_price = batchOperation.value.discountNumber + ''
                                sku.discount_price = batchOperation.value.discountNumber + ''
                                sku.reduce_money = (sku.price - sku.specify_price).toFixed(2)
                                sku.discount_rate = (sku.specify_price / sku.price * 10).toFixed(1)
                            }
                            sku.discount_type = batchOperation.value.discount_type + ''
                            if (formRef.value) {
                                formRef.value.validateField('goods_list.' + index + '.discount_rate')
                                formRef.value.validateField('goods_list.' + index + '.specify_price')
                                formRef.value.validateField('goods_list.' + index + '.reduce_money')
                            }
                        }
                    })
                    let discount_rate_list = el.skuList.filter((sku: any) => sku.is_enabled === 1).map((sku: any) => Number(sku.discount_rate))
                    let reduce_money_list = el.skuList.filter((sku: any) => sku.is_enabled === 1).map((sku: any) => Number(sku.reduce_money))
                    let specify_price_list = el.skuList.filter((sku: any) => sku.is_enabled === 1).map((sku: any) => Number(sku.specify_price))
                    el.max_discount_rate = Math.max(...discount_rate_list)
                    el.min_discount_rate = Math.min(...discount_rate_list)
                    el.max_reduce_money = Math.max(...reduce_money_list)
                    el.min_reduce_money = Math.min(...reduce_money_list)
                    el.max_specify_price = Math.max(...specify_price_list)
                    el.min_specify_price = Math.min(...specify_price_list)
                }
                el.valid = true
            }
        })
    })
    formRef.value.validateField().catch(() => {
    })
}

/**** 修改单行 *****/
const inputBlur = (row: any, discount_type: string, index: number) => {
    if (discount_type == 'discount') {
        if (row.discount_rate.length) {
            //实际
            row.specify_price = (row.goodsSku.price * (row.discount_rate / 10)).toFixed(2)
            row.discount_price = (row.goodsSku.price * (row.discount_rate / 10)).toFixed(2)
            //减价
            row.reduce_money = (row.goodsSku.price - row.specify_price).toFixed(2)
            if (formRef.value) {
                formRef.value.validateField('goods_list.' + index + '.specify_price').catch(() => {
                })
                formRef.value.validateField('goods_list.' + index + '.reduce_money').catch(() => {
                })
            }
        }
    } else if (discount_type == 'reduce') {//减价
        if (row.reduce_money.length) {
            row.specify_price = (row.goodsSku.price - row.reduce_money).toFixed(2)
            row.discount_price = (row.goodsSku.price - row.reduce_money).toFixed(2)
            row.discount_rate = (row.specify_price / row.goodsSku.price * 10).toFixed(1)
            if (formRef.value) {
                formRef.value.validateField('goods_list.' + index + '.discount_rate').catch(() => {
                })
                formRef.value.validateField('goods_list.' + index + '.specify_price').catch(() => {
                })
            }
        }

    } else {//实际
        if (row.specify_price.length) {
            row.discount_price = row.specify_price + ''
            row.reduce_money = (row.goodsSku.price - row.specify_price).toFixed(2)
            row.discount_rate = (row.specify_price / row.goodsSku.price * 10).toFixed(1)
            if (formRef.value) {
                formRef.value.validateField('goods_list.' + index + '.discount_rate').catch(() => {
                })
                formRef.value.validateField('goods_list.' + index + '.reduce_money').catch(() => {
                })
            }
        }

    }
    row.discount_type = discount_type + ''

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
        row.specify_price = row.goodsSku.price + ''
    }

}
const back = () => {
    router.push('/shop/marketing/discount/list')
}
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

.sku-form-item-wrap :deep(.el-form-item__content) {
    margin-left: 0 !important;
}

.sku_list :deep(.cell) {
    overflow: initial !important;
}
</style>

<template>
    <div>
        <el-dialog v-model="showDialog" :title="t('editMemberPricePopupTitle')" width="1100px" :destroy-on-close="true" :close-on-click-modal="false">

            <el-form :model="formData" label-width="120px" class="page-form">
                <el-form-item :label="t('memberDiscount')">
                    <div>
                        <el-radio-group v-model="formData.member_discount">
                            <el-radio label="">{{ t('nonparticipation') }}</el-radio>
                            <el-radio label="discount">{{ t('discount') }}</el-radio>
                            <el-radio label="fixed_price">{{ t('fixedPrice') }}</el-radio>
                        </el-radio-group>
                        <div class="text-[12px] text-[#999] leading-[20px]" v-if="formData.member_discount == 'discount'">{{t('discountHint')}}</div>
                        <div class="text-[12px] text-[#999] leading-[20px]" v-if="formData.member_discount == 'fixed_price'">{{t('fixedPriceHint')}}</div>
                    </div>
                </el-form-item>
                <el-form-item v-if="formData.member_discount == 'discount' && memberDiscountLevel.length">
                    <el-table :data="[{}]" size="large" v-loading="tableData.loading" max-height="450" @selection-change="handleSelectionChange">
                        <template #empty>
                            <span>{{ !tableData.loading ? t('emptyData') : '' }}</span>
                        </template>
                        <el-table-column fixed prop="sku_name" :label="t('memberLevel')" width="150" >
                            <template #default>
                                {{t('memberEnjoyDiscount')}}
                            </template>
                        </el-table-column>
                        <el-table-column v-for="(item,index) in memberDiscountLevel" :key="index" :label="item.level_name">
                            <template #default="{ row }">
                                {{item.level_benefits.discount.discount}}
                            </template>
                        </el-table-column>
                    </el-table>
                </el-form-item>
                <el-form-item v-if="formData.member_discount == 'fixed_price'">
                    <div class="mb-[10px] flex items-center">
                        <el-checkbox v-model="toggleCheckbox"  @change="toggleChange" size="large" class="px-[14px]" :indeterminate="isIndeterminate" />
                        <el-button v-for="(item,index) in tableData.member_level" :key="index" size="small" @click="batchGoodsBtn(item.level_id)">
                            {{item.level_name}}
                        </el-button>
                    </div>
                    <el-table :data="tableData.data" size="large" v-loading="tableData.loading" max-height="450" @selection-change="handleSelectionChange" ref="goodsListTableRef">
                        <template #empty>
                            <span>{{ !tableData.loading ? t('emptyData') : '' }}</span>
                        </template>
                        <el-table-column type="selection" width="55" />
                        <el-table-column fixed prop="sku_name" :label="t('goodsSku')" width="150" />
                        <el-table-column fixed prop="price" :label="t('skuPrice')" width="120" />
                        <el-table-column v-for="(item,index) in tableData.member_level" :key="index" :label="item.level_name" width="190">
                            <template #default="{ row }">
                                <el-input v-model.trim="row[`level_${item.level_id}`]" maxlength="8" clearable class="w-full"  @keyup="filterDigit($event)">
                                    <template #append>{{t('yuanUnit')}}</template>
                                </el-input>
                            </template>
                        </el-table-column>
                    </el-table>
                </el-form-item>
            </el-form>

            <el-dialog v-model="memberPriceDialog" :title="t('editMemberPrice')" width="400px" :destroy-on-close="true" :close-on-click-modal="false">
                <el-form :model="formData" label-width="80px" class="page-form">
                    <el-form-item :label="t('memberPrice')"  prop="member_price">
                        <el-input v-model.trim="memberPrice" :placeholder="t('memberPricePlaceholder')" maxlength="8" clearable  @keyup="filterDigit($event)" />
                    </el-form-item>
                </el-form>

                <template #footer>
                    <span class="dialog-footer">
                        <el-button @click="memberPriceDialog = false">{{ t('cancel') }}</el-button>
                        <el-button type="primary" @click="memberPriceSave">{{ t('confirm') }}</el-button>
                    </span>
                </template>
            </el-dialog>

            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="showDialog = false">{{ t('cancel') }}</el-button>
                    <el-button type="primary" @click="save">{{ t('confirm') }}</el-button>
                </span>
            </template>
        </el-dialog>
    </div>
</template>

<script lang="ts" setup>
import { t } from '@/lang'
import { ref, reactive } from 'vue'
import { img, deepClone, filterDigit } from '@/utils/common'
import { ElMessage } from 'element-plus'

import {
    getGoodsSkuList,
    editGoodsListMemberPrice
} from '@/addon/shop/api/goods'

const goodsListTableRef = ref()
const formData: any = reactive({
    member_discount: ''
})
const goods: any = reactive({})
const showDialog = ref(false)
const memberPrice = ref('') // 会员价

const emit = defineEmits(['load'])

const tableData = reactive({
    loading: true,
    data: [],
    member_level: []
})
/**
 * 获取商品列表
 */
const loadGoodsList = () => {
    tableData.loading = true

    getGoodsSkuList({
        goods_id: goods.goods_id
    }).then(res => {
        tableData.loading = false
        tableData.data = res.data
        tableData.data.forEach((item: any, index, Array: any) => {
            // 针对于单规格是，sku_name为空就展示商品名称
            if (!item.sku_name) {
                Array[index].sku_name = goods.goods_name
            }
            // 处理商品的会员价
            tableData.member_level.forEach((levelItem: any, levelIndex) => {
                if (!item.member_price) {
                    Array[index][`level_${levelItem.level_id}`] = parseFloat(item.price).toFixed(2)
                } else if (item.member_price) {
                    const memberPrice = JSON.parse(item.member_price)
                    if (memberPrice[`level_${levelItem.level_id}`]) {
                        Array[index][`level_${levelItem.level_id}`] = parseFloat(memberPrice[`level_${levelItem.level_id}`]).toFixed(2)
                    } else {
                        Array[index][`level_${levelItem.level_id}`] = parseFloat(item.price).toFixed(2)
                    }
                }
            })
        })
    }).catch(() => {
        tableData.loading = false
    })
}

// 用于会员折扣展示
const memberDiscountLevel:any = ref([])
const show = (data: any, levelData: any) => {
    Object.assign(goods, data)
    tableData.member_level = []
    Object.assign(tableData.member_level, levelData)
    formData.member_discount = data.member_discount

    memberDiscountLevel.value = deepClone(levelData)
    memberDiscountLevel.value.forEach((item: any, index: any, Array: any) => {
        if (!item.level_benefits || item.level_benefits == null) {
            Array[index].level_benefits = {
                discount: {
                    discount: '原价'
                }
            }
        } else if (item.level_benefits && item.level_benefits != null && !item.level_benefits.discount) {
            Array[index].level_benefits.discount = {
                discount: '原价'
            }
        } else if (item.level_benefits && item.level_benefits != null && item.level_benefits.discount && !item.level_benefits.discount.discount) {
            Array[index].level_benefits.discount.discount = '原价'
        } else {
            Array[index].level_benefits.discount.discount += '折'
        }
    })
    loadGoodsList()
    showDialog.value = true
    saveLoad = false
}

/** ******* 批量复选框-start *************/
const toggleCheckbox = ref()
// 复选框中间状态
const isIndeterminate = ref(false)
// 监听批量复选框事件
const toggleChange = (value: any) => {
    isIndeterminate.value = false
    goodsListTableRef.value.toggleAllSelection()
}

// 选中数据
const multipleSelection: any = ref([])

// 监听表格单行选中
const handleSelectionChange = (val: []) => {
    multipleSelection.value = deepClone(val)

    toggleCheckbox.value = false
    if (multipleSelection.value.length > 0 && multipleSelection.value.length < tableData.data.length) {
        isIndeterminate.value = true
    } else {
        isIndeterminate.value = false
    }

    if (multipleSelection.value.length == tableData.data.length) {
        toggleCheckbox.value = true
    }
}

// 按钮
const currLevelId = ref('')
const batchGoodsBtn = (level_id:any) => {
    if (!multipleSelection.value.length) {
        ElMessage({
            message: '请选择要操作的商品',
            type: 'warning'
        })
        return false
    }
    currLevelId.value = level_id
    memberPriceDialog.value = true
}

/** ******* 批量复选框-end *************/
const memberPriceDialog = ref(false)
const memberPriceSave = () => {
    if (!memberPrice.value) {
        ElMessage.error('请输入会员价')
        return false
    }

    const idArr = multipleSelection.value.map((obj: any) => obj.sku_id)
    tableData.data.forEach((item: any, index, Array: any) => {
        if (idArr.indexOf(item.sku_id) > -1) { Array[index][`level_${currLevelId.value}`] = parseFloat(memberPrice.value).toFixed(2) }
    })

    memberPrice.value = ''
    memberPriceDialog.value = false
}

let saveLoad = false
const save = () => {
    const sku_list: any = []
    let verify = true // 用于校验价格是否小于等于零或大于商品原价
    if (formData.member_discount == 'fixed_price') {
        tableData.data.forEach((item: any, index, Array) => {
            const obj: any = {}
            obj.sku_id = item.sku_id
            obj.member_price = {}
            tableData.member_level.forEach((levelItem: any, levelIndex) => {
                if (verify) {
                    obj.member_price[`level_${ levelItem.level_id }`] = item[`level_${ levelItem.level_id }`]
                    if (parseFloat(item[`level_${ levelItem.level_id }`]) <= 0) {
                        verify = false
                        ElMessage.error(`[${ item.sku_name }][${ levelItem.level_name }]的指定价格不能小于等于零`)
                    }
                    if (parseFloat(item[`level_${ levelItem.level_id }`]) > parseFloat(item.price)) {
                        verify = false
                        ElMessage.error(`[${ item.sku_name }][${ levelItem.level_name }]的指定价格不能大于商品原价`)
                    }
                }
            })
            sku_list.push(obj)
        })

        if (!verify) return false
    }

    if (saveLoad) return false
    saveLoad = true

    editGoodsListMemberPrice({
        goods_id: goods.goods_id,
        member_discount: formData.member_discount,
        sku_list
    }).then(res => {
        showDialog.value = false
        emit('load')
    }).catch(() => {
        saveLoad = false
        showDialog.value = false
    })
}

defineExpose({
    showDialog,
    show
})
</script>

<style lang="scss" scoped>
.form-item-wrap {
    margin-right: 10px !important;

    &.last-child {
        margin-right: 0 !important;
    }
}
</style>

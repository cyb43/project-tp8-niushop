<template>
    <div class="main-container" v-loading="loading">

        <el-card class="card !border-none" shadow="never">
            <el-page-header :content="pageName" :icon="ArrowLeft" @back="back()" />
        </el-card>

        <!-- 表单 -->
        <el-form class="page-form mt-[15px]" :model="formData" label-width="120px" ref="formRef" :rules="formRules">
            <!-- 基础设置 -->
            <el-card class="box-card !border-none" shadow="never">
                <h3 class="panel-title !text-sm">{{ t('baseInfo') }}</h3>

                <!-- 选择商品： -->
                <el-form-item :label="t('goodsSelect')" class="!m-0" v-if="formData.type=='goods'">
                    <div>
                        <goods-select-popup ref="goodsSelectPopupRef" v-model="formData.goods_ids" @goodsSelect="goodsSelect" :min="1" :max="1" />

                        <div class="flex min-w-[512px] relative border-[1px] border-[#e7e7e7] border-solid mt-[16px]" v-if="formData.goods_ids.length">
                            <div class="min-w-[78px] h-[78px] flex items-center justify-center">
                                <el-image v-if="formData.goods_info.goods_cover" class="w-[78px] h-[78px]" :src="img(formData.goods_info.goods_cover)" fit="contain">
                                    <template #error>
                                        <div class="image-slot">
                                            <img class="w-[78px] h-[78px]" src="@/addon/shop/assets/goods_default.png" />
                                        </div>
                                    </template>
                                </el-image>
                                <img v-else class="w-[78px] h-[78px]" src="@/addon/shop/assets/goods_default.png" fit="contain" />
                            </div>
                            <div class="flex-1 py-[5px] pl-[10px] pr-[30px]">
                                <div class="text-[14px]">{{ formData.goods_info.goods_name }}</div>
                                <div class="text-[14px] !text-[var(--el-color-primary)]">{{ formData.goods_info.goods_price }}</div>
                            </div>
                            <el-icon class="absolute right-[10px] top-[10px] cursor-pointer" @click="deleteGoods">
                                <Close size="24px" color="#999" />
                            </el-icon>
                        </div>
                    </div>
                </el-form-item>
                <el-form-item prop="product_list" v-if="formData.type=='goods'"></el-form-item>

                <!-- 商品名称： -->
                <el-form-item :label="t('goodsName')" prop="names">
                    <el-input v-model.trim="formData.names" type="textarea" clearable :placeholder="t('goodsNamePlaceholder')" class="!w-[512px]" show-word-limit :maxlength="60" />
                </el-form-item>
                <!-- 商品标题： -->
                <el-form-item :label="t('goodsTitle')">
                    <el-input v-model.trim="formData.title" type="textarea" clearable :placeholder="t('goodsTitlePlaceholder')" class="!w-[512px]" show-word-limit :maxlength="60" />
                </el-form-item>
                <!-- 商品图片 -->
                <el-form-item :label="t('image')" prop="image">
                    <upload-image v-model="formData.image" :limit="6"/>
                </el-form-item>
            </el-card>

            <el-card class="box-card !border-none" shadow="never" v-if="formData.type=='goods'&&formData.goods_info&&formData.product_list.length">
                <h3 class="panel-title !text-sm">{{ t('redemptionSettings') }}</h3>

                <el-form-item>
                    <div class="w-full sku_list">
                        <div class="flex items-center mb-[15px]">
                            <el-checkbox v-model="toggleCheckbox" size="large" class="!mr-[15px]" @change="toggleChange" :indeterminate="isIndeterminate">
                                <span>已选{{ multipleSelection.length }}项</span>
                            </el-checkbox>

                            <label>{{ t('batchOperation') }}</label>
                            <el-input v-model.trim="batchOperation.stock" clearable :placeholder="t('stock')" class="!w-[130px] ml-[10px]" maxlength="8" />
                            <el-input v-model.trim="batchOperation.limit_num" clearable :placeholder="t('limit')" class="!w-[130px] ml-[10px]" maxlength="8" />
                            <el-input v-model.trim="batchOperation.point" clearable :placeholder="t('integralUnit')" class="!w-[130px] ml-[10px]" maxlength="8" />
                            <el-input v-model.trim="batchOperation.price" clearable :placeholder="t('newPrice')" class="!w-[130px] ml-[10px]" maxlength="8" />
                            <el-button class="ml-[10px]" type="primary" @click="saveBatch">{{ t('confirm') }}</el-button>
                        </div>
                        <el-table class="!max-w-[100%]" :data="formData.product_list" size="large" ref="productListTableRef" @selection-change="handleSelectionChange">
                            <template #empty>
                                <span>{{ t('emptyData')}}</span>
                            </template>
                            <el-table-column type="selection" width="55" />
                            <el-table-column :label="t('goodsSelectPopupGoodsInfo')" min-width="300">
                                <template #default="{ row }">
                                    <div class="flex items-center cursor-pointer">
                                        <div class="min-w-[60px] h-[60px] flex items-center justify-center">
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
                                            <span :title="row.sku_name" class="multi-hidden">{{ row.sku_name||row.goods?.goods_name }}</span>
                                        </div>
                                    </div>
                                </template>
                            </el-table-column>
                            <el-table-column prop="goods_price" :label="t('price')" min-width="170" />
                            <el-table-column prop="goods_stock" :label="t('goodsStock')" min-width="170" />
                            <el-table-column :label="t('stock')" min-width="200">
                                <template #default="{ row,$index }">
                                    <el-form-item v-if="row.is_enabled" :prop="'product_list.'+ $index + '.stock'" :rules="[{
                                        trigger: 'blur',
                                        validator: (rule: any, value: any, callback: any) => {
                                                if (value.length == 0) {
                                                    callback(t('stockPlaceholder'))
                                                } else if (isNaN(value) || !regExp.number.test(value)) {
                                                    callback(t('stockTips'))
                                                } else if (value <=0) {
                                                    callback(t('stockTipsTwo'))
                                                }else if(parseInt(value)>parseInt(row.goods_stock)){
                                                    callback(t('stockTipsThree'))
                                                }else {
                                                    callback();
                                                }
                                        }
                                        }]" class="sku-form-item-wrap">
                                        <!-- @blur="inputBlur(row,'reduce',$index)" -->
                                        <el-input v-model.trim="row.stock"  class="!w-[200px]" clearable placeholder="0" maxlength="8" />
                                    </el-form-item>
                                    <span v-else>--</span>
                                </template>
                            </el-table-column>
                            <el-table-column :label="t('limit')" min-width="200">
                                <template #header>
                                    <div style="display: inline-flex; align-items: center">
                                        <span class="mr-[5px]">{{ t('limit') }}</span>
                                        <el-tooltip class="box-item" effect="light" :content="t('limitRules')" placement="top">
                                        <el-icon color="#666">
                                            <QuestionFilled />
                                        </el-icon>
                                        </el-tooltip>
                                    </div>
                                </template>
                                <template #default="{ row,$index }">
                                    <el-form-item v-if="row.is_enabled" :prop="'product_list.'+ $index + '.limit_num'" :rules="[{
                                        trigger: 'blur',
                                        validator: (rule: any, value: any, callback: any) => {
                                                if (value.length == 0) {
                                                    callback(t('limitPlaceholder'))
                                                } else if (isNaN(value) || !regExp.number.test(value)) {
                                                    callback(t('limitTips'))
                                                } else if (value <=0) {
                                                    callback(t('limitTipsTwo'))
                                                } else if (value > Number(row.stock)) {
                                                    callback(t('limitTipsThree'))
                                                } else if(parseInt(value)>parseInt(row.goods_stock)){
                                                    callback(t('stockTipsThree'))
                                                } else {
                                                    callback();
                                                }
                                        }
                                        }]" class="sku-form-item-wrap">
                                        <!-- @blur="inputBlur(row,'reduce',$index)" -->
                                            <el-input v-model.trim="row.limit_num" class="!w-[200px]" clearable placeholder="0" maxlength="8" >
                                                <template #append>
                                                    <span>{{t('limitUnit')}}</span>
                                                </template>
                                            </el-input>
                                        </el-form-item>
                                        <span v-else>--</span>
                                </template>
                            </el-table-column>
                            <el-table-column :label="t('money')" min-width="550" align="center">
                                <template #default="{ row,$index }">
                                    <div class="flex justify-center">
                                        <el-form-item v-if="row.is_enabled" :prop="'product_list.'+ $index + '.point'" :rules="[{
                                            trigger: 'blur',
                                            validator: (rule: any, value: any, callback: any) => {
                                                    if (value.length == 0) {
                                                        callback(t('pointPlaceholder'))
                                                    } else if (isNaN(value) || !regExp.number.test(value)) {
                                                            callback(t('pointTips'))
                                                        } else if (value <=0) {
                                                            callback(t('pointTipsTwo'))
                                                        } else{
                                                            callback();
                                                        }
                                            }
                                            }]" class="sku-form-item-wrap">
                                            <!-- @blur="inputBlur(row,'reduce',$index)" -->
                                            <el-input v-model.trim="row.point" class="!w-[200px]"  clearable placeholder="0" maxlength="8" >
                                                <template #append>
                                                    <span>{{t('integralUnit')}}</span>
                                                </template>
                                            </el-input>
                                        </el-form-item>
                                        <span v-else>--</span>
                                        <span v-if="row.is_enabled" class="mx-[20px]">+</span>
                                        <el-form-item v-if="row.is_enabled" :prop="'product_list.'+ $index + '.price'" :rules="[{
                                            trigger: 'blur',
                                            validator: (rule: any, value: any, callback: any) => {
                                                    if(value.length){
                                                        if (isNaN(value) || !regExp.digit.test(value)) {
                                                            callback(t('moneyTips'))
                                                        } else if (value <0) {
                                                            callback(t('moneyTipsTwo'))
                                                        }else{
                                                            callback();
                                                        }
                                                    }else {
                                                        callback();
                                                    }
                                            }
                                            }]" class="sku-form-item-wrap">
                                            <!-- @blur="inputBlur(row,'reduce',$index)" -->
                                            <el-input v-model.trim="row.price" class="!w-[200px]"  clearable placeholder="0.00" maxlength="8" >
                                                <template #append>
                                                    <span>{{t('prickUnit')}}</span>
                                                </template>
                                            </el-input>
                                        </el-form-item>
                                    </div>
                                </template>
                            </el-table-column>
                            <el-table-column :label="t('operation')" fixed="right" align="right" min-width="160">
                                <template #default="{row}">
                                    <el-button type="primary" link @click="enabledEvent(row)">{{ row.is_enabled?t('noEnabled'):t('enabled') }}</el-button>
                                </template>
                            </el-table-column>
                        </el-table>
                    </div>
                </el-form-item>
            </el-card>

            <el-card class="box-card !border-none" shadow="never" v-if="formData.type=='coupon'&&formData.product_list.length">
                <h3 class="panel-title !text-sm">{{ t('redemptionSettings') }}</h3>

                <!-- 兑换库存 -->
                <el-form-item :label="t('stock')" prop="stock"  :rules="[{
                    required: true,
                    trigger: 'blur',
                    validator: (rule: any, value: any, callback: any) => {
                        if (value.length == 0) {
                            callback(t('stockPlaceholder'))
                        } else if (isNaN(value) || !regExp.number.test(value)) {
                            callback(t('stockTips'))
                        } else if (value <=0) {
                            callback(t('stockTipsTwo'))
                        }else if(formData.product_list[0].sum_count != '-1' && parseInt(value)>parseInt(formData.product_list[0].sum_count.sum_count)){
                            callback(t('stockTipsThree'))
                        }else {
                            callback();
                        }
                    }
                }]">
                    <el-input v-model.trim="formData.stock" clearable :placeholder="t('stockPlaceholder')" class="input-width" />
                </el-form-item>
                <!-- 兑换限制 -->
                <el-form-item :label="t('limit')" prop="limit_num"  :rules="[{
                    trigger: 'blur',
                    validator: (rule: any, value: any, callback: any) => {
                        if(value){
                            if (isNaN(value) || !regExp.number.test(value)) {
                                callback(t('limitTips'))
                            } else if (value <=0) {
                                callback(t('limitTipsTwo'))
                            } else {
                                callback();
                            }
                        }else{
                            callback();
                        }
                    }
                }]">
                    <el-input v-model.trim="formData.limit_num" clearable :placeholder="t('limitPlaceholder')" class="input-width" />
                </el-form-item>
                <!-- 兑换价 -->
                <el-form-item :label="t('money')" required>
                    <div class="flex justify-center">
                        <el-form-item prop="point" :rules="[{
                            trigger: 'blur',
                            validator: (rule: any, value: any, callback: any) => {
                                    if (value.length == 0) {
                                        callback(t('pointPlaceholder'))
                                    } if (isNaN(value) || !regExp.number.test(value)) {
                                            callback(t('pointTips'))
                                        } else if (value <=0) {
                                            callback(t('pointTipsTwo'))
                                        } else{
                                            callback();
                                        }
                                    }
                            }]" class="sku-form-item-wrap">
                            <!-- @blur="inputBlur(row,'reduce',$index)" -->
                            <el-input v-model.trim="formData.point" class="!w-[200px]"  clearable placeholder="0" maxlength="8" >
                                <template #append>
                                    <span>{{t('integralUnit')}}</span>
                                </template>
                            </el-input>
                        </el-form-item>
                        <span class="mx-[20px]">+</span>
                        <el-form-item prop="price" :rules="[{
                            trigger: 'blur',
                            validator: (rule: any, value: any, callback: any) => {
                                     if(value.length){
                                        if (isNaN(value) || !regExp.digit.test(value)) {
                                            callback(t('moneyTips'))
                                        } else if (value <0) {
                                            callback(t('moneyTipsTwo'))
                                        }else{
                                            callback();
                                        }
                                    }else {
                                        callback();
                                    }
                            }
                            }]" class="sku-form-item-wrap">
                            <!-- @blur="inputBlur(row,'reduce',$index)" -->
                            <el-input v-model.trim="formData.price" class="!w-[200px]"  clearable placeholder="0.00" maxlength="8" >
                                <template #append>
                                    <span>{{t('prickUnit')}}</span>
                                </template>
                            </el-input>
                        </el-form-item>
                    </div>
                </el-form-item>
            </el-card>

            <el-card class="box-card !border-none" shadow="never" v-if="formData.type=='balance'">
                <h3 class="panel-title !text-sm">{{ t('redemptionSettings') }}</h3>
                <!-- 兑换余额 -->
                <el-form-item :label="t('balance')" prop="balance"  :rules="[{
                    required: true,
                    trigger: 'blur',
                    validator: (rule: any, value: any, callback: any) => {
                        if (value.length == 0) {
                            callback(t('balancePlaceholder'))
                        } else if (isNaN(value) || !regExp.digit.test(value)) {
                            callback(t('balanceTips'))
                        } else if (value <=0) {
                            callback(t('balanceTipsTwo'))
                        }else {
                            callback();
                        }
                    }
                }]">
                    <el-input v-model.trim="formData.balance" clearable :placeholder="t('balancePlaceholder')" class="input-width" />
                </el-form-item>
                <!-- 余额类型 -->
                <el-form-item :label="t('balance')">
                    <el-radio-group v-model="formData.isBalance" class="ml-4">
                        <el-radio :label="0" size="large">可提现余额</el-radio>
                        <el-radio :label="1" size="large">不可提现余额</el-radio>
                    </el-radio-group>
                </el-form-item>
                <!-- 兑换库存 -->
                <el-form-item :label="t('stock')" prop="stock"  :rules="[{
                    required: true,
                    trigger: 'blur',
                    validator: (rule: any, value: any, callback: any) => {
                        if (value.length == 0) {
                            callback(t('stockPlaceholder'))
                        } else if (isNaN(value) || !regExp.number.test(value)) {
                            callback(t('stockTips'))
                        } else if (value <=0) {
                            callback(t('stockTipsTwo'))
                        }else {
                            callback();
                        }
                    }
                }]">
                    <el-input v-model.trim="formData.stock" clearable :placeholder="t('stockPlaceholder')" class="input-width" />
                </el-form-item>
                <!-- 兑换限制 -->
                <el-form-item :label="t('limit')" prop="limit_num"  :rules="[{
                    trigger: 'blur',
                    validator: (rule: any, value: any, callback: any) => {
                        if(value){
                            if (isNaN(value) || !regExp.number.test(value)) {
                                callback(t('limitTips'))
                            } else if (value <=0) {
                                callback(t('limitTipsTwo'))
                            } else {
                                callback();
                            }
                        }else{
                            callback();
                        }
                    }
                }]">
                    <el-input v-model.trim="formData.limit_num" clearable :placeholder="t('limitPlaceholder')" class="input-width" />
                </el-form-item>
                <!-- 兑换价 -->
                <el-form-item :label="t('money')" required>
                    <div class="flex justify-center">
                        <el-form-item prop="point" :rules="[{
                            trigger: 'blur',
                            validator: (rule: any, value: any, callback: any) => {
                                if (value.length == 0) {
                                    callback(t('pointPlaceholder'))
                                                    } else if(value.length){
                                                        if (isNaN(value) || !regExp.number.test(value)) {
                                                            callback(t('pointTips'))
                                                        } else if (value <=0) {
                                                            callback(t('pointTipsTwo'))
                                                        } else{
                                                            callback();
                                                        }
                                                    } else {
                                                        callback();
                                                    }
                                            }
                                            }]" class="sku-form-item-wrap">
                                            <!-- @blur="inputBlur(row,'reduce',$index)" -->
                                            <el-input v-model.trim="formData.point" class="!w-[200px]"  clearable placeholder="0" maxlength="8" >
                                                <template #append>
                                                    <span>{{t('integralUnit')}}</span>
                                                </template>
                                            </el-input>
                                        </el-form-item>
                                        <span class="mx-[20px]">+</span>
                                        <el-form-item prop="price" :rules="[{
                                            trigger: 'blur',
                                            validator: (rule: any, value: any, callback: any) => {
                                                    if(value.length){
                                                        if (isNaN(value) || !regExp.digit.test(value)) {
                                                            callback(t('moneyTips'))
                                                        } else if (value <0) {
                                                            callback(t('moneyTipsTwo'))
                                                        }else{
                                                            callback();
                                                        }
                                                    }else {
                                                        callback();
                                                    }
                                            }
                                            }]" class="sku-form-item-wrap">
                                            <!-- @blur="inputBlur(row,'reduce',$index)" -->
                                            <el-input v-model.trim="formData.price" class="!w-[200px]"  clearable placeholder="0.00" maxlength="8" >
                                                <template #append>
                                                    <span>{{t('prickUnit')}}</span>
                                                </template>
                                            </el-input>
                                        </el-form-item>
                                    </div>
                </el-form-item>
            </el-card>

            <el-card class="box-card !border-none" shadow="never">
                <h3 class="panel-title !text-sm">{{ t('goodsDetail') }}</h3>
                <el-form-item>
                    <editor v-model="formData.content" :height="600"  class="!w-[512px]" />
                </el-form-item>
            </el-card>
        </el-form>

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
import { img, deepClone } from '@/utils/common'
import { FormInstance, ElMessage } from 'element-plus'
import { ArrowLeft } from '@element-plus/icons-vue'
import { addActiveExchange } from '@/addon/shop/api/marketing'
import goodsSelectPopup from '@/addon/shop/views/goods/components/goods-select-popup.vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title

const loading = ref(false)
// 商品类型
const goodsType = reactive([
    { name: '商品', type: 'goods' }
    // {name:'优惠券',type:'coupon'},
    // {name:'余额',type:'balance'},
])
const initialFormData = {
    type: 'goods', // 名称
    names: '', // 标题
    title: '',
    image: '',
    goods_ids: [],
    coupon_ids: [],
    product_list: [],
    stock: '',
    limit_num: '',
    point: '',
    price: '',
    balance: '',
    isBalance: 1,
    content: ''
}
const formData: Record<string, any> = ref({ ...initialFormData })
const formRef = ref<FormInstance>()
// 表单验证规则
// 正则表达式
const regExp = {
    required: /[\S]+/,
    number: /^\d{0,10}$/,
    digit: /^\d{0,10}(.?\d{0,2})$/,
    special: /^\d{0,10}(.?\d{0,3})$/
}
const formRules = computed(() => {
    return {
        names: [
            { required: true, message: t('goodsNamePlaceholder'), trigger: 'blur' }
        ],
        image: [
            { required: true, message: t('imagePlaceholder'), trigger: 'change' }
        ],
        product_list: [
            { required: true, message: t('selectGoodsPlaceholder'), trigger: 'change' }
        ],
        coupon_ids: [
            { required: true, message: t('couponSelectPlaceholder'), trigger: 'change' }
        ]
    }
})
const changeGoodsType = (item:any) => {
    formData.value.type = item.type
    formData.value.coupon_ids = []
    formData.value.goods_ids = []
    if (formData.value.type == 'goods') {
        formData.value.goods_info = {}
    } else {
        if (formData.value.goods_info) delete formData.value.goods_info
    }
    formData.value.product_list = []
}

/** ******* 商品 **********/
// 删除商品
const deleteGoods = () => {
    delete formData.value.goods_info
    formData.value.goods_ids = []
    formData.value.product_list = []
}
// 设置商品sku是否参与
const enabledEvent = (row:any) => {
    row.is_enabled = row.is_enabled ? 0 : 1
    if (formData.value.product_list.every((el: any) => el.is_enabled === 0)) {
        row.is_enabled = 1
        ElMessage({
            type: 'warning',
            message: `${t('noEnabledTip')}`
        })
        return
    }
    row.stock = ''
    row.limit_num = ''
    row.point = ''
    row.price = ''
}

const goodsSelectPopupRef = ref()
const goodsSelect = (value:any) => {
    const goods_info: any = Object.values(deepClone(value))[0]
    formData.value.product_list = deepClone(goods_info.skuList.map((el: any) => {
        el.goods_stock = el.stock + ''
        el.goods_price = el.price + ''
        el.limit_num = ''
        el.stock = ''
        el.point = ''
        el.price = ''
        el.is_enabled = 1
        return el
    }))
    formData.value.goods_info = {
        goods_name: goods_info.goods_name,
        goods_cover: goods_info.goods_cover,
        goods_price: goods_info.goodsSku.price,
        goods_id: goods_info.goods_id
    }
    formData.value.image = goods_info.goods_image
    formData.value.names = goods_info.goods_name
    formData.value.title = goods_info.sub_title
    formData.value.content = goods_info.goods_desc
    if (formRef.value) {
        formRef.value.validateField('product_list').catch(() => {
        })
    }
}

interface batchOperationInterface {
    stock:any,
    limit_num:any,
    point:any,
    price:any,
}
const batchOperation = ref<batchOperationInterface>({
    stock: '',
    limit_num: '',
    point: '',
    price: ''
})
// 批量复选框
const toggleCheckbox = ref()

// 复选框中间状态
const isIndeterminate = ref(false)

// 监听批量复选框事件
const toggleChange = (value: any) => {
    isIndeterminate.value = false
    productListTableRef.value.toggleAllSelection()
}
const productListTableRef = ref()

// 选中数据
const multipleSelection: any = ref([])

// 监听表格单行选中
const handleSelectionChange = (val: []) => {
    multipleSelection.value = val

    toggleCheckbox.value = false
    if (multipleSelection.value.length > 0 && multipleSelection.value.length < formData.value.product_list.length) {
        isIndeterminate.value = true
    } else {
        isIndeterminate.value = false
    }

    if (multipleSelection.value.length == formData.value.product_list.length) {
        toggleCheckbox.value = true
    }
}
// 批量设置确认按钮
const saveBatch = () => {
    if (!multipleSelection.value.length) {
        ElMessage({
            type: 'warning',
            message: `${t('batchEmptySelectedGoodsTips')}`
        })
        return
    }
    if (batchOperation.value.stock) {
        if (isNaN(batchOperation.value.stock) || !regExp.number.test(batchOperation.value.stock)) {
            ElMessage({
                type: 'warning',
                message: `${t('stockTips')}`
            })
            return
        } else if (batchOperation.value.stock <= 0) {
            ElMessage({
                type: 'warning',
                message: `${t('stockTipsTwo')}`
            })
            return
        }
    }
    if (batchOperation.value.limit_num) {
        if (isNaN(batchOperation.value.limit_num) || !regExp.number.test(batchOperation.value.limit_num)) {
            ElMessage({
                type: 'warning',
                message: `${t('limitTips')}`
            })
            return
        } else if (batchOperation.value.limit_num <= 0) {
            ElMessage({
                type: 'warning',
                message: `${t('limitTipsTwo')}`
            })
            return
        }
    }
    if (batchOperation.value.point) {
        if (isNaN(batchOperation.value.point) || !regExp.number.test(batchOperation.value.point)) {
            ElMessage({
                type: 'warning',
                message: `${t('pointTips')}`
            })
            return
        } else if (batchOperation.value.point <= 0) {
            ElMessage({
                type: 'warning',
                message: `${t('pointTipsTwo')}`
            })
            return
        }
    }
    if (batchOperation.value.price) {
        if (isNaN(batchOperation.value.price) || !regExp.digit.test(batchOperation.value.price)) {
            ElMessage({
                type: 'warning',
                message: `${t('moneyTips')}`
            })
            return
        } else if (batchOperation.value.price < 0) {
            ElMessage({
                type: 'warning',
                message: `${t('moneyTipsTwo')}`
            })
            return
        }
    }
    formData.value.product_list.forEach((el: any, index: number) => {
        multipleSelection.value.forEach((v: any) => {
            if (v.sku_id === el.sku_id) {
                if (batchOperation.value.stock) el.stock = batchOperation.value.stock + ''
                if (batchOperation.value.limit_num) el.limit_num = batchOperation.value.limit_num + ''
                if (batchOperation.value.point) el.point = batchOperation.value.point + ''
                if (batchOperation.value.price) el.price = batchOperation.value.price + ''
                if (formRef.value) {
                    formRef.value.validateField('product_list.' + index + '.stock').catch(() => {
                    })
                    formRef.value.validateField('product_list.' + index + '.limit_num').catch(() => {
                    })
                    formRef.value.validateField('product_list.' + index + '.point').catch(() => {
                    })
                    formRef.value.validateField('product_list.' + index + '.price').catch(() => {
                    })
                }
            }
        })
    })
    isIndeterminate.value = false
    toggleCheckbox.value = false
    batchOperation.value = {
        stock: '',
        limit_num: '',
        point: '',
        price: ''
    }
    productListTableRef.value.clearSelection()
}
/** ******** 优惠券 ***********/
const couponSelect = (value:any) => {
    formData.value.product_list = Object.values(value)
}
/** ** 提交 ****/
const onSave = async (formEl: FormInstance | undefined) => {
    if (loading.value || !formEl) return
    await formEl.validate(async (valid) => {
        if (valid) {
            loading.value = true

            if (formData.value.type == 'goods') {
                formData.value.product_detail = JSON.stringify(formData.value.product_list.map((el: any) => {
                    return {
                        goods_id: el.goods_id,
                        sku_id: el.sku_id,
                        point: el.point || '0',
                        price: el.price || '0',
                        limit_num: el.limit_num || '0',
                        stock: el.stock || '0',
                        is_enabled: el.is_enabled
                    }
                }).filter((el: any) => el.is_enabled === 1))
            } else if (formData.value.type == 'coupon') {
                formData.value.product_detail = JSON.stringify(formData.value.product_list.map((el: any) => {
                    return {
                        coupon_id: el.id
                    }
                }))
            } else {
                if (formData.value.isBalance) {
                    formData.value.product_detail = JSON.stringify([{ balance: formData.value.balance }])
                } else {
                    formData.value.product_detail = JSON.stringify([{ money: formData.value.balance }])
                }
            }
            // formData.value.goods_data =  JSON.stringify(formData.value.product_list.filter((el:any)=>{
            //     return el.is_enabled==1
            // }))
            const save = addActiveExchange
            save(formData.value).then(res => {
                loading.value = false
                history.back()
            }).catch(() => {
                loading.value = false
            })
        }
    })
}

const back = () => {
    router.push('/shop/marketing/exchange/goods_list')
}
</script>

<style lang="scss" scoped>
.goods-type-wrap {
    width: 120px;
    height: 60px;
    background: #fff;
    border-radius: 3px;
    float: left;
    text-align: center;
    // padding-top: 8px;
    position: relative;
    cursor: pointer;
    line-height: 23px;
    border: 1px solid #e7e7e7;

    .goods-type-name {
        font-size: 14px;
        font-weight: 600;
        color: rgba(0, 0, 0, .85);
    }

    .goods-type-desc {
        font-size: 12px;
        font-weight: 400;
        color: #999;
    }

    .triangle {
        position: absolute;
        bottom: 0;
        right: 0;
        width: 0;
        height: 0;
        border-bottom: 26px solid var(--el-color-primary);
        border-left: 26px solid transparent;
    }

    .selected {
        position: absolute;
        bottom: -2px;
        right: 2px;
        color: #fff;
    }

    &.selected {
        border: 1px solid var(--el-color-primary);
    }

    &.disabled {
        cursor: not-allowed;

        .goods-type-name {
            color: #999;
        }
    }

    &:nth-child(2n) {
        margin: 0 12px;
    }

}
.sku_list :deep(.cell) {
    // min-height: 60px !important;
    overflow: initial !important;
}
</style>

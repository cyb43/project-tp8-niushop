<template>
    <el-dialog v-model="showDialog" :title="t('batchSetting')" width="700px" class="diy-dialog-wrap" :destroy-on-close="true">
        <el-alert :title="t('batchSettingTip')" type="info" :closable="false" />
        <el-form :model="formData" label-width="120px" ref="formRef" :rules="formRules" class="mt-[10px] page-form" v-loading="loading">
            <el-row>
                <!-- 左侧菜单栏 -->
                <el-col :span="4" class="menu-column">
                    <el-menu :default-active="activeMenu" @select="handleMenuSelect">
                        <el-menu-item v-for="([key, value]) in Object.entries(setTypeList)" :key="key" :index="key">{{ value }}</el-menu-item>
                    </el-menu>
                </el-col>

                <!-- 右侧内容展示 -->
                <el-col :span="20" class="content-column p-[5px]">
                    <!-- 商品标签 -->
                    <el-form-item v-if="activeMenu === 'label'" :label="t('label')">
                        <el-checkbox-group v-model="formData.label_ids">
                            <el-checkbox :label="item.label_id" v-for="(item, index) in labelOptions" :key="index">{{ item.label_name }}</el-checkbox>
                        </el-checkbox-group>
                        <div class="ml-[10px]">
                            <span class="cursor-pointer text-primary mr-[10px]" @click="refreshGoodsLabel">{{ t('refresh') }}</span>
                            <span class="cursor-pointer text-primary" @click="toGoodsLabelEvent">{{ t('addGoodsLabel') }}</span>
                        </div>
                    </el-form-item>
                    <!-- 商品服务 -->
                    <el-form-item v-if="activeMenu === 'service'" :label="t('label')">
                        <el-checkbox-group v-model="formData.service_ids">
                            <el-checkbox :label="item.service_id" v-for="(item, index) in serviceOptions" :key="index">{{ item.service_name }}</el-checkbox>
                        </el-checkbox-group>
                        <div class="ml-[10px]">
                            <span class="cursor-pointer text-primary mr-[10px]" @click="refreshGoodsService">{{ t('refresh') }}</span>
                            <span class="cursor-pointer text-primary" @click="toGoodsServiceEvent">{{ t('addGoodsService') }}</span>
                        </div>
                    </el-form-item>
                    <!-- 虚拟销量 -->
                    <el-form-item v-if="activeMenu === 'virtual_sale_num'" :label="t('virtualSaleNum')" prop="virtual_sale_num">
                        <div>
                            <el-input v-model.trim="formData.virtual_sale_num"
                                :placeholder="t('virtualSaleNumPlaceholder')" class="input-width" show-word-limit
                                maxlength="8" clearable @keyup="filterNumber($event)"
                                @blur="formData.virtual_sale_num = $event.target.value">
                                <template #append>{{ formData.unit ? formData.unit : '件' }}</template>
                            </el-input>
                            <div class="mt-[10px] text-[12px] text-[#999] leading-[20px]">{{ t('virtualSaleNumDesc') }}</div>
                        </div>
                    </el-form-item>
                    <!-- 商品分类 -->
                    <el-form-item v-if="activeMenu === 'category'" prop="goods_category" :label="t('goodsCategory')">
                        <el-cascader v-model="formData.goods_category" :options="goodsCategoryOptions"
                            :placeholder="t('productClassificationPlaceholder')" :props="goodsCategoryProps" clearable
                            filterable @change="categoryHandleChange" />
                        <div class="ml-[10px]">
                            <span class="cursor-pointer text-primary mr-[10px]" @click="refreshGoodsCategory(true)">{{ t('refresh') }}</span>
                            <span class="cursor-pointer text-primary" @click="toGoodsCategoryEvent">{{ t('addGoodsCategory') }}</span>
                        </div>
                    </el-form-item>
                    <!-- 商品品牌 -->
                    <el-form-item v-if="activeMenu === 'brand'" :label="t('goodsBrand')">
                        <el-select v-model="formData.brand_id" :placeholder="t('brandPlaceholder')" clearable>
                            <el-option v-for="item in brandOptions" :key="item.brand_id" :label="item.brand_name" :value="item.brand_id" />
                        </el-select>
                        <div class="ml-[10px]">
                            <span class="cursor-pointer text-primary mr-[10px]" @click="refreshGoodsBrand(true)">{{ t('refresh') }}</span>
                            <span class="cursor-pointer text-primary" @click="toGoodsBrandEvent">{{ t('addGoodsBrand') }}</span>
                        </div>
                    </el-form-item>
                    <!-- 商品海报 -->
                    <el-form-item v-if="activeMenu === 'poster'" :label="t('goodsPoster')">
                        <el-select v-model="formData.poster_id" :placeholder="t('posterPlaceholder')" clearable>
                            <el-option v-for="item in posterOptions" :key="item.id" :label="item.name" :value="item.id" />
                        </el-select>
                        <div class="ml-[10px]">
                            <span class="cursor-pointer text-primary mr-[10px]" @click="refreshGoodsPoster(true)">{{ t('refresh') }}</span>
                            <span class="cursor-pointer text-primary" @click="toPosterEvent">{{ t('addGoodsPoster') }}</span>
                        </div>
                    </el-form-item>
                    <!-- 是否赠品 -->
                    <el-form-item v-if="activeMenu === 'gift'" :label="t('isGift')">
                        <div>
                            <el-radio-group v-model="formData.is_gift">
                                <el-radio :label="1">{{ t('yes') }}</el-radio>
                                <el-radio :label="0">{{ t('no') }}</el-radio>
                            </el-radio-group>
                            <div class="mt-[10px] text-[12px] text-[#999] leading-[20px]">{{ t('giftTips') }}</div>
                        </div>
                    </el-form-item>
                    <!-- 配送设置 -->
                    <div v-if="activeMenu === 'delivery'">
                        <el-form-item :label="t('deliveryType')" prop="delivery_type">
                            <div>
                                <el-checkbox-group v-model="formData.delivery_type">
                                    <el-checkbox v-for="(item, index) in deliveryTypeCheckBox" :key="index" :label="item.key">{{ item.name }}</el-checkbox>
                                </el-checkbox-group>
                                <div v-if="deliveryTypeCheckBox && deliveryTypeCheckBox.length" class="text-[12px] text-[#999] leading-[20px]">只针对实物商品有效</div>
                                <div v-if="deliveryTypeFlag" class="text-[12px] text-[#999] leading-[20px]">请先在配置设置中设置配送方式</div>
                            </div>
                        </el-form-item>

                        <el-form-item :label="t('isFreeShipping')" v-show="formData.delivery_type.indexOf('express') != -1">
                            <el-switch v-model="formData.is_free_shipping" :active-value="1" :inactive-value="0" />
                        </el-form-item>

                        <el-form-item :label="t('feeType')" prop="fee_type" v-show="formData.delivery_type.indexOf('express') != -1 && formData.is_free_shipping == 0">
                            <el-radio-group v-model="formData.fee_type">
                                <el-radio label="template">{{ t('selectTemplate') }}</el-radio>
                                <el-radio label="fixed">{{ t('fixedShipping') }}</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item :label="t('deliveryMoney')" prop="delivery_money"
                            v-show="formData.delivery_type.indexOf('express') != -1 && formData.is_free_shipping == 0 && formData.fee_type == 'fixed'">
                            <el-input v-model.trim="formData.delivery_money" clearable placeholder="0.00" class="input-width-short" maxlength="8">
                                <template #append>{{ t('yuan') }}</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item :label="t('deliveryTemplateId')" prop="delivery_template_id"
                            v-show="formData.delivery_type.indexOf('express') != -1 && formData.is_free_shipping == 0 && formData.fee_type == 'template'">
                            <el-select v-model="formData.delivery_template_id" :placeholder="t('deliveryTemplateIdPlaceholder')" filterable autocomplete="off" clearable>
                                <el-option v-for="item in deliveryTemplateOptions" :key="item.template_id" :label="item.template_name" :value="item.template_id" />
                            </el-select>
                            <div class="ml-[10px]">
                                <span class="cursor-pointer text-primary mr-[10px]" @click="refreshDeliveryTemplate">{{ t('refresh') }}</span>
                                <span class="cursor-pointer text-primary" @click="toDeliveryTemplateEvent">{{ t('addDeliveryTemplateId') }}</span>
                            </div>
                        </el-form-item>
                    </div>
                    <!-- 库存设置 -->
                    <div v-if="activeMenu === 'stock'">
                        <el-form-item :label="t('setStock')" prop="stock_type">
                            <el-radio-group v-model="formData.stock_type">
                                <el-radio label="inc">{{ t('addStock') }}</el-radio>
                                <el-radio label="dec">{{ t('reduceStock') }}</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item :label="t('stockNum')" prop="">
                            <el-input v-model.trim="formData.stock" clearable placeholder="0" class="input-width-short"
                                show-word-limit maxlength="8" @keyup="filterNumber($event)"
                                @blur="formData.stock = $event.target.value" />
                        </el-form-item>
                        <div class="mt-[10px] ml-[120px] text-[12px] text-[#999] leading-[20px]">{{ t('stockNumTips') }}
                        </div>
                    </div>
                    <!-- 会员折扣 -->
                    <el-form-item v-if="activeMenu === 'member_discount'" label="是否参与">
                        <el-radio-group v-model="formData.member_discount">
                            <el-radio label="">{{ t('不参与') }}</el-radio>
                            <el-radio label="discount">{{ t('参与') }}</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <!-- 万能表单 -->
                    <el-form-item v-if="activeMenu === 'diy_form'" :label="t('diyForm')">
                        <el-select v-model="formData.form_id" :placeholder="t('diyFormPlaceholder')" clearable>
                            <el-option v-for="item in diyFormOptions" :key="item.form_id" :label="item.page_title" :value="item.form_id" />
                        </el-select>
                        <div class="ml-[10px]">
                            <span class="cursor-pointer text-primary mr-[10px]" @click="refreshDiyForm(true)">{{ t('refresh') }}</span>
                            <span class="cursor-pointer text-primary" @click="toDiyFormEvent">{{ t('addDiyForm') }}</span>
                        </div>
                    </el-form-item>
                    <!-- 商品详情模板 -->
                    <el-form-item v-if="activeMenu === 'diy_detail'" label="商品详情模板">
                        <el-select v-model="formData.diy_detail_id" placeholder="请选择商品详情模板" clearable>
                            <el-option v-for="item in detailTemplateOptions" :key="item.id" :label="item.page_title" :value="item.id" />
                        </el-select>
                        <div class="ml-[10px]">
                            <span class="cursor-pointer text-primary mr-[10px]" @click="refreshDetailTemplate(true)">{{ t('refresh') }}</span>
                            <span class="cursor-pointer text-primary" @click="toDetailTemplateEvent">添加模板</span>
                        </div>
                    </el-form-item>
                </el-col>
            </el-row>
        </el-form>

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
import { ElMessage, FormInstance } from 'element-plus'
import { useRouter } from 'vue-router'
import { filterNumber } from '@/utils/common'
import {
    getBrandList,
    getLabelList,
    getServeList,
    getCategoryTree,
    getGoodsBatchSetDict,
    goodsBatchSet,
    getGoodsInfoTemplate
} from '@/addon/shop/api/goods'
import { getPosterList } from '@/app/api/poster'
import { getDiyFormList } from '@/app/api/diy_form'
import { getShopDeliveryList, getShippingTemplateList } from '@/addon/shop/api/delivery'

const emit = defineEmits(['load'])
const showDialog = ref(false)
const loading = ref(false)
const activeMenu = ref('label')
const router = useRouter()

/**
 * 表单数据
 */
const initialFormData = {
    label_ids: [],
    service_ids: [],
    poster_id: '',
    form_id: '',
    diy_detail_id: '',
    brand_id: '',
    goods_category: [],
    virtual_sale_num: 0,
    delivery_template_id: '',
    delivery_money: 0,
    fee_type: 'template',
    delivery_type: [],
    stock_type: 'inc',
    member_discount: '',
    is_free_shipping: 1,
    is_gift: 0,
    stock: ''
}

const formData: Record<string, any> = reactive({ ...initialFormData })
// 正则表达式
const regExp: any = {
    required: /[\S]+/,
    number: /^\d{0,10}$/,
    digit: /^\d{0,10}(.?\d{0,2})$/,
    special: /^\d{0,10}(.?\d{0,3})$/
}
const formRef = ref<FormInstance>()
const formRules = reactive({
    virtual_sale_num: [
        {
            trigger: 'blur',
            validator: (rule: any, value: any, callback: any) => {
                if (activeMenu.value == 'virtual_sale_num') {
                    if (isNaN(value) || !regExp.number.test(value)) {
                        callback(new Error(t('virtualSaleNumTips')))
                    } else if (value < 0) {
                        callback(new Error(t('virtualSaleNumNotZeroTips')))
                    } else {
                        callback()
                    }
                } else {
                    callback()
                }
            }
        }
    ],
    delivery_type: [
        { required: true, message: t('deliveryTypePlaceholder'), trigger: 'blur' }
    ],
    goods_category: [
        { required: true, message: t('goodsCategoryPlaceholderTwo'), trigger: 'blur' }
    ],
    delivery_money: [
        {
            trigger: 'blur',
            validator: (rule: any, value: any, callback: any) => {
                if (activeMenu.value == 'delivery') {
                    if (formData.delivery_type.indexOf('express') != -1 && formData.is_free_shipping == 0 && formData.fee_type == 'fixed') {
                        if (formData.delivery_template_id.length == 0 && value === '') {
                            callback(new Error(t('deliveryMoneyPlaceholder')))
                        } else if (isNaN(value) || !regExp.digit.test(value)) {
                            callback(new Error(t('deliveryMoneyTips')))
                        } else if (value < 0) {
                            callback(new Error(t('deliveryMoneyNotZeroTips')))
                        } else {
                            callback()
                        }
                    } else {
                        callback()
                    }
                } else {
                    callback()
                }
            }
        }
    ],
    delivery_template_id: [
        {
            trigger: 'blur',
            validator: (rule: any, value: any, callback: any) => {
                if (activeMenu.value == 'delivery') {
                    if (formData.delivery_type.indexOf('express') != -1 && formData.is_free_shipping == 0 && formData.fee_type == 'template') {
                        if (formData.delivery_money.length == 0 && value === '') {
                            callback(new Error(t('deliveryTemplateIdPlaceholder')))
                        } else {
                            callback()
                        }
                    } else {
                        callback()
                    }
                } else {
                    callback()
                }
            }
        }
    ]
})

const goods_ids = ref([])
const is_all = ref(null)
const where = ref({})
const show = (info: any) => {
    goods_ids.value = info.ids
    is_all.value = info.is_all
    where.value = info.where
    showDialog.value = true
}

const confirm = async (formEl: FormInstance | undefined) => {
    if (loading.value || !formEl) return
    await formEl.validate(async (valid) => {
        if (valid) {
            loading.value = true
            const goodsCategory: any = []
            formData.goods_category.forEach((item: any) => {
                if (typeof item == 'object') {
                    item.forEach((second: any) => {
                        if (goodsCategory.indexOf(second) == -1) {
                            goodsCategory.push(second)
                        }
                    })
                } else {
                    if (goodsCategory.indexOf(item) == -1) {
                        goodsCategory.push(item)
                    }
                }
            })

            formData.goods_category = goodsCategory
            const data = {
                is_all: is_all.value,
                where: where.value,
                goods_ids: goods_ids.value,
                set_type: activeMenu.value,
                set_value: formData
            }
            goodsBatchSet(data).then((res) => {
                if (['stock'].indexOf(activeMenu.value) != -1) {
                    activeMenu.value = 'label'
                    showDialog.value = false
                    goods_ids.value.splice(0, goods_ids.value.length)
                    Object.assign(formData, {
                        label_ids: [],
                        service_ids: [],
                        poster_id: '',
                        form_id: '',
                        diy_detail_id: '',
                        brand_id: '',
                        goods_category: [],
                        virtual_sale_num: '',
                        delivery_template_id: '',
                        delivery_money: '',
                        fee_type: 'template',
                        delivery_type: [],
                        is_free_shipping: 1,
                        is_gift: 0
                    })
                   
                }
                emit('load')
                loading.value = false
            }).catch(err => {
                loading.value = false
            })
        }
    })
}

const handleMenuSelect = (index: string) => {
    activeMenu.value = index
}

const setTypeList = reactive([])
const getGoodsTypeList = () => {
    getGoodsBatchSetDict().then((res) => {
        Object.assign(setTypeList, res.data)
    })
}
getGoodsTypeList()

/** ***************** 商品标签-start *************************/
// 标签组列表复选框
const labelOptions = reactive([])
// 跳转到商品标签，添加标签
const toGoodsLabelEvent = () => {
    const url = router.resolve({
        path: '/shop/goods/label'
    })
    window.open(url.href)
}

// 商品标签
const refreshGoodsLabel = (bool = false) => {
    getLabelList({}).then((res) => {
        const data = res.data
        if (data) {
            labelOptions.splice(0, labelOptions.length, ...data)
            if (bool) {
                ElMessage({
                    message: t('refreshSuccess'),
                    type: 'success'
                })
            }
        }
    })
}
refreshGoodsLabel()
/** *****************商品标签-end *************************/

/** ***************** 商品服务-start *************************/
// 商品服务列表复选框
const serviceOptions = reactive([])
// 跳转到商品服务，添加服务
const toGoodsServiceEvent = () => {
    const url = router.resolve({
        path: '/shop/goods/service'
    })
    window.open(url.href)
}

// 商品服务
const refreshGoodsService = (bool = false) => {
    getServeList({}).then((res) => {
        const data = res.data
        if (data) {
            serviceOptions.splice(0, serviceOptions.length, ...data)
            if (bool) {
                ElMessage({
                    message: t('refreshSuccess'),
                    type: 'success'
                })
            }
        }
    })
}

refreshGoodsService()
/** *****************商品服务-end *************************/

/** ***************** 商品分类-start *************************/
// 商品分类
const goodsCategoryOptions = reactive([])
const goodsCategoryProps = {
    multiple: true
}
const categoryHandleChange = (value: any) => {
    // console.log(value, goodsEdit.formData.goods_category, goodsEdit.formData.goods_category.toString())
}

// 跳转到商品分类，添加分类
const toGoodsCategoryEvent = () => {
    const url = router.resolve({
        path: '/shop/goods/category'
    })
    window.open(url.href)
}

// 刷新商品分类
const refreshGoodsCategory = (bool = false) => {
    getCategoryTree().then((res) => {
        const data = res.data
        if (data) {
            const goodsCategoryTree: any = []
            data.forEach((item: any) => {
                const children: any = []
                if (item.child_list) {
                    item.child_list.forEach((childItem: any) => {
                        children.push({
                            value: childItem.category_id,
                            label: childItem.category_name
                        })
                    })
                }
                goodsCategoryTree.push({
                    value: item.category_id,
                    label: item.category_name,
                    children
                })
            })
            goodsCategoryOptions.splice(0, goodsCategoryOptions.length, ...goodsCategoryTree)
            if (bool) {
                ElMessage({
                    message: t('refreshSuccess'),
                    type: 'success'
                })
            }
        }
    })
}

refreshGoodsCategory()
/** *****************商品分类-end *************************/

/** ***************** 商品品牌-start *************************/
// 品牌列表下拉框
const brandOptions = reactive([])

// 跳转到商品品牌，添加品牌
const toGoodsBrandEvent = () => {
    const url = router.resolve({
        path: '/shop/goods/brand'
    })
    window.open(url.href)
}

// 商品品牌
const refreshGoodsBrand = (bool = false) => {
    getBrandList({}).then((res) => {
        const data = res.data
        if (data) {
            brandOptions.splice(0, brandOptions.length, ...data)
            if (bool) {
                ElMessage({
                    message: t('refreshSuccess'),
                    type: 'success'
                })
            }
        }
    })
}

refreshGoodsBrand()
/** *****************商品品牌-end *************************/

/** *****************商品海报-start *************************/
// 海报列表下拉框
const posterOptions = reactive([])
// 跳转到海报列表，添加海报
const toPosterEvent = () => {
    const url = router.resolve({
        path: '/poster/list'
    })
    window.open(url.href)
}

// 商品海报
const refreshGoodsPoster = (bool = false) => {
    getPosterList({
        type: 'shop_goods'
    }).then((res) => {
        const data = res.data
        if (data) {
            posterOptions.splice(0, posterOptions.length, ...data)
            if (bool) {
                ElMessage({
                    message: t('refreshSuccess'),
                    type: 'success'
                })
            }
        }
    })
}

refreshGoodsPoster()
/** *****************商品海报-end *************************/

// 配送方式复选框
const deliveryTypeCheckBox = reactive([])

// 运费模板下拉框
const deliveryTemplateOptions = reactive([])

// 配送方式
const deliveryTypeFlag = ref(false)
getShopDeliveryList().then((res) => {
    const data = res.data
    if (data) {
        deliveryTypeCheckBox.splice(0, deliveryTypeCheckBox.length, ...data)
        // deliveryTypeFlag.value = deliveryTypeCheckBox.every(el => {
        // return el.status == '2'
        // })
    }
})

// 跳转到运费模板
const toDeliveryTemplateEvent = () => {
    const url = router.resolve({
        path: '/shop/order/shipping/template'
    })
    window.open(url.href)
}

// 运费模板
const refreshDeliveryTemplate = (bool = false) => {
    getShippingTemplateList({}).then((res) => {
        const data = res.data
        if (data) {
            deliveryTemplateOptions.splice(0, deliveryTemplateOptions.length, ...data)
            if (bool) {
                ElMessage({
                    message: t('refreshSuccess'),
                    type: 'success'
                })
            }
        }
    })
}

refreshDeliveryTemplate()

/** ***************** 万能表单-start *************************/
// 万能表单列表下拉框
const diyFormOptions = reactive([])
// 跳转到万能表单列表，添加表单
const toDiyFormEvent = () => {
    const url = router.resolve({
        path: '/diy_form/list'
    })
    window.open(url.href)
}

// 刷新万能表单
const refreshDiyForm = (bool = false) => {
    getDiyFormList({
        type: 'DIY_FORM_GOODS_DETAIL',
        status: 1
    }).then((res) => {
        const data = res.data
        if (data) {
            diyFormOptions.splice(0, diyFormOptions.length, ...data)
            if (bool) {
                ElMessage({
                    message: t('refreshSuccess'),
                    type: 'success'
                })
            }
        }
    })
}

refreshDiyForm()
/** *****************万能表单-end *************************/

/** ***************** 商品详情模板-start *************************/
// 商品详情模板列表下拉框
const detailTemplateOptions = reactive([])
// 跳转到微页面，添加详情模板
const toDetailTemplateEvent = () => {
    const url = router.resolve({
        path: '/diy/list'
    })
    window.open(url.href)
}

// 刷新微页面
const refreshDetailTemplate = (bool = false) => {
    getGoodsInfoTemplate({
        type: 'DIY_SHOP_GOODS_DETAIL'
    }).then((res) => {
        const data = res.data
        if (data) {
            detailTemplateOptions.splice(0, detailTemplateOptions.length, ...data)
            if (bool) {
                ElMessage({
                    message: t('refreshSuccess'),
                    type: 'success'
                })
            }
        }
    })
}

refreshDetailTemplate()
/** *****************商品详情模板-end *************************/

defineExpose({
    showDialog,
    show
})
</script>

<style lang="scss" scoped>
.el-menu-item {
    height:40px;
}
.input-width-short{
    width: 200px;
}
</style>

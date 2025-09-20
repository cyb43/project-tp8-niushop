<template>
  <div v-loading="loading">
        <el-card class="box-card !border-none main-container" shadow="never">
            <div class="flex justify-between items-center mb-[10px]">
                <span class="text-page-title">{{ pageName }}</span>
            </div>
            <!-- <el-tabs v-model="activeName">
                <el-tab-pane :label="t('basicInfoTab')" name="basic"> -->
                    <el-alert type="warning" :closable="false" class="!mb-[0px]">
                        <template #default>
                            <p class="mb-[5px]">* 新人专享活动旨在通过专属优惠提升新老客户的转化率。达到参与门槛的用户可以享受特定商品的限时新人价优惠。</p>
                            <p class="mb-[5px]">* 每位用户限购1件新人专享商品，超出1件的部分将按正常价购买。</p>
                            <p class="mb-[5px]">* 订单支付成功后，将视为用户已参与过新人专享活动。</p>
                            <p class="mb-[5px]">* 若订单中的单个商品发生退款，用户将无法重新参与新人专享活动，只有当整个订单全部退货后，用户方可重新参与该活动。</p>
                        </template>
                    </el-alert>
                    <el-form :model="formData" label-width="120px" ref="formRef" :rules="formRules" class="page-form">
                        <el-card class="box-card !border-none" shadow="never">
                            <h3 class="panel-title !text-sm pl-[15px]">{{ t('basicInfoTab') }}</h3>
                            <el-form-item :label="t('activeStatus')">
                                <el-switch v-model="formData.active_status" active-value="active" inactive-value="close" />
                            </el-form-item>
                            <template v-if="formData.active_status==='active'">
                                <el-form-item :label="t('participationWay')">
                                    <el-radio-group v-model="formData.participation_way">
                                        <el-radio label="never_order">{{ t('neverOrder') }}</el-radio>
                                        <el-radio label="assign_time_order">{{ t('assignTimeOrder') }}</el-radio>
                                        <el-radio label="assign_time_register">{{ t('assignTimeRegister') }}</el-radio>
                                    </el-radio-group>
                                </el-form-item>
                                <el-form-item prop="appoint_time" v-if="formData.participation_way != 'never_order'">
                                    <div class="date-picker mr-[5px]">
                                        <el-date-picker class="!w-[200px]" v-model="formData.appoint_time" value-format="YYYY-MM-DD HH:mm:ss" type="datetime"/>
                                    </div>
                                    <span v-if="formData.participation_way === 'assign_time_order'">之前未下过单的会员</span>
                                    <span v-else>之后注册的会员</span>
                                </el-form-item>

                                <el-form-item :label="t('validityType')">
                                    <el-radio-group v-model="formData.validity_type">
                                        <el-radio label="day">{{ t('validityDay') }}</el-radio>
                                        <el-radio label="date">{{ t('validityTime') }}</el-radio>
                                    </el-radio-group>
                                </el-form-item>
                                <el-form-item prop="validity_day" v-if="formData.validity_type==='day'">
                                    <span>{{ t('validityDayTipsLeft') }}</span>
                                    <div class="flex items-center mx-[5px]">
                                        <el-input v-model.trim="formData.validity_day" @keyup="filterNumber($event)" @blur="()=>{if(formData.validity_day>0) formData.validity_day = parseInt(formData.validity_day)}" clearable class="input-width-short" maxlength="3" />
                                    </div>
                                    <span>{{ t('validityDayTipsRight') }}</span>
                                    <div class="form-tip">{{ t('validityTimeTipsTwo') }}</div>
                                </el-form-item>
                                <el-form-item prop="validity_time" v-if="formData.validity_type==='date'">
                                    <span>{{ t('validityTimeTips') }}</span>
                                    <div class="flex items-center px-[5px] w-[200px] date-picker">
                                        <el-date-picker v-model="formData.validity_time" value-format="YYYY-MM-DD HH:mm:ss" type="datetime" />
                                    </div>
                                    <div class="form-tip">{{ t('validityTimeTipsTwo') }}</div>
                                </el-form-item>
                                <el-form-item :label="t('activeDesc')">
                                <div class="flex">
                                    <el-input v-model="formData.active_desc" :placeholder="t('activeDescPlaceholder')" type="textarea" maxlength="500" show-word-limit rows="5" class="!w-[400px]" clearable />
                                    <el-button class="ml-[20px]" type="primary" @click="defaultActiveDescEvent()" plain>{{ t('useDefaultActiveDesc') }}</el-button>
                                </div>
                            </el-form-item>
                            </template>
                        </el-card>
                        <el-card v-if="formData.active_status==='active'" class="box-card !border-none" shadow="never">
                            <h3 class="panel-title !text-sm pl-[15px]">{{ t('bannerList') }}</h3>
                            <div v-for="(item,index) in formData.banner_list" :key="index">
                                <el-form-item :label="t('image')" :prop="`banner_list.${index}.imageUrl`"  :rules="[{
                                    required: true,
                                    trigger: 'change',
                                    validator: (rule: any, value: any, callback: any) => {
                                        if (!value) {
                                            callback(t('imagePlaceholder'))
                                        }
                                        callback()
                                    }
                                }]">
                                    <upload-image v-model="item.imageUrl"  :limit="1"/>
                                </el-form-item>
                                <!-- :prop="`banner_list.${index}.toLink.name`"  :rules="[{
                                    required: true,
                                    trigger: 'change',
                                    validator: (rule: any, value: any, callback: any) => {
                                        if (!value) {
                                            callback(t('toLinkPlaceholder'))
                                        }
                                        callback()
                                    }
                                }]" -->
                                <el-form-item :label="t('toLink')" >
                                    <diy-link v-model="item.toLink"/>
                                </el-form-item>
                                <!-- <span v-if="formData.banner_list.length>1" class="cursor-pointer absolute top-[-8px] right-[-8px] delete" @click="deleteConfigList(index)"><el-icon color="#bbbbbb" size="20px"><CircleCloseFilled /></el-icon></span> -->
                            </div>
                        </el-card>
                        <el-card v-if="formData.active_status==='active'" class="box-card !border-none" shadow="never">
                            <h3 class="panel-title !text-sm pl-[15px]">{{ t('activityGoods') }}</h3>
                            <el-form-item :label="t('selectGoods')" prop="goodsSkuIds">
                                <goods-select-popup ref="goodsSelectPopupRef" v-model="formData.goodsSkuIds" @goodsSelect="goodsSelect" mode="sku" :min="1" :max="99" />
                            </el-form-item>
                            <el-form-item v-if="formData.goodsSkuList && formData.goodsSkuList.length">
                                <div>
                                    <el-table class="sku_list" ref="goods_listTableRef" :data="formData.goodsSkuList" size="large" max-height="480" @selection-change="handleSelectionChange">
                                        <el-table-column type="selection" width="55" />
                                        <el-table-column :label="t('goodsSelectPopupGoodsInfo')" min-width="550">
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
                                                        <span :title="row.sku_name" class="multi-hidden">{{ row.goods_name }}</span>
                                                        <div class="flex items-center">
                                                            <span class="text-primary text-[12px]">{{row.sku_name}}</span>
                                                            <span class="mx-[8px] text-[#999] text-[12px]" v-if="row.sku_name">|</span>
                                                            <span class="text-primary text-[12px]">{{ row.goods_type_name }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </el-table-column>
                                        <el-table-column :label="t('oldPrice')" min-width="160">
                                            <template #default="{ row }">
                                                <div>￥{{ row.price }}</div>
                                            </template>
                                        </el-table-column>
                                        <el-table-column :label="t('newcomerPrice')" min-width="160">
                                            <template #default="{ row,$index }">
                                                <el-form-item :prop="'goodsSkuList.'+ $index + '.newcomer_price'" :rules="[{
                                                        trigger: 'blur',
                                                        validator: (rule: any, value: any, callback: any) => {
                                                            if(!value){
                                                                callback(t('newcomerPricePlaceholder'))
                                                            }else if (isNaN(value) || !regExp.digit.test(value)) {
                                                                callback(t('newcomerPriceTips'))
                                                            } else if (parseFloat(value) <0) {
                                                                callback(t('newcomerPriceTipsOne'))
                                                            }if (parseFloat(value) > parseFloat(row.price)) {
                                                                callback(t('newcomerPriceTipsTwo'))
                                                            }else{
                                                                callback();
                                                            }
                                                        }
                                                        }]" class="sku-form-item-wrap">
                                                    <el-input v-model.trim="row.newcomer_price" clearable placeholder="0" maxlength="8" />
                                                </el-form-item>
                                            </template>
                                        </el-table-column>
                                        <el-table-column prop="stock" :label="t('goodsSelectPopupStock')" min-width="160" align="right" />
                                        <el-table-column :label="t('operation')" align="right" min-width="160">
                                            <template #default="{ row,$index }">
                                                <el-button type="primary" link @click="deleteGoodsEvent(row,$index)">{{ t('delete') }}</el-button>
                                            </template>
                                        </el-table-column>
                                    </el-table>
                                    <div class="flex items-center mb-[15px] mt-[10px] pl-[14px]">
                                        <el-checkbox v-model="toggleCheckbox" size="large" class="!mr-[15px]" @change="toggleChange" :indeterminate="isIndeterminate">
                                            <span>已选 {{ multipleSelection.length }} 项</span>
                                        </el-checkbox>

                                        <label>{{ t('batchOperation') }}</label>
                                        <el-input v-model.trim="newcomer_price" clearable class="!w-[130px] ml-[10px]" :placeholder="t('newcomerPricePlaceholder')" maxlength="8" />
                                        <el-button class="ml-[10px]" type="primary" @click="saveBatch">{{ t('confirm') }}</el-button>
                                        <el-button class="ml-[10px]" type="primary" @click="deleteBatch">{{ t('批量删除') }}</el-button>
                                    </div>
                                </div>
                            </el-form-item>
<!--                            <el-form-item :label="t('limitNum')" prop="limit_num" v-if="formData.goodsSkuList && formData.goodsSkuList.length">-->
<!--                                <div>-->
<!--                                    <div class="flex items-center">-->
<!--                                        <el-input v-model.trim="formData.limit_num" @keyup="filterNumber($event)" @blur="()=>{if(formData.limit_num>0) formData.limit_num = parseInt(formData.limit_num)}" clearable class="!w-[200px]" maxlength="3" />-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                            </el-form-item>-->
                        </el-card>
                    <!-- </el-form>
                </el-tab-pane>
                <el-tab-pane :label="t('bannerList')" name="banner"> -->
                    <!-- <el-form class="page-form" :model="formData" label-width="120px" ref="bannerFormRef"> -->

                        <!-- <div class="flex w-full justify-center">
                            <el-button class="w-[400px]" @click="addConfigList">{{ t('addConfigList') }}</el-button>
                        </div> -->
                    </el-form>
                <!-- </el-tab-pane>
            </el-tabs> -->
        </el-card>
        <!-- 提交按钮 -->
        <div class="fixed-footer-wrap">
            <div class="fixed-footer h-[48px]">
                <el-button type="primary" @click="onSave">{{ t('save') }}</el-button>
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { ref,computed } from 'vue'
import { t } from '@/lang'
import { useRoute } from 'vue-router'
import {FormInstance, ElMessage} from 'element-plus'
import { filterNumber,img, deepClone } from '@/utils/common'
import { getActiveNewcomerConfig,editActiveNewcomerConfig } from "@/addon/shop/api/marketing";
import goodsSelectPopup from '@/addon/shop/views/goods/components/goods-select-popup.vue'

const route = useRoute()
const pageName = route.meta.title
const loading = ref(true)
const formData = ref({
    active_status:'active',
    banner_list:[
        {imageUrl: '', toLink: {name: ''}},
    ],
    validity_type:'day',
    validity_day:7,
    validity_time:'',
    participation_way:'never_order',
    appoint_time:'',
    goodsSkuIds:<Array<any>>[],
    goodsSkuList:<Array<any>>[],
    goods_data:<any>'',
    limit_num:1,
    active_desc:''
})
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
        appoint_time: [
            { required: true, message: t('appointTimePlaceholder'), trigger: 'change' }
        ],
        validity_day: [
            { required: true, validator: (rule: any, value: any, callback: any) => {
                if (!value) {
                    callback(t('validityDayPlaceholder'))
                }else if(parseInt(value)<=0){
                    callback(t('validityDayTips'))
                }else {
                    callback()
                }

            }, trigger: 'blur' }
        ],
        validity_time: [
            { required: true,validator: (rule: any, value: any, callback: any) => {
                if (!value) {
                    callback(t('validityTimePlaceholder'))
                }else if(formData.value.participation_way != 'never_order'){
                    if(!formData.value.appoint_time){
                        callback(t('validityTimePlaceholderTwo'))
                    }else if(new Date(value).getTime()<=new Date(formData.value.appoint_time).getTime()){
                        callback(t('validityTimePlaceholderThree'))
                    }else{
                        callback()
                    }
                }else {
                    callback()
                }

            }, trigger: 'change' }
        ],
        goodsSkuIds: [
            { required: true, message: t('goodsSkuIdsPlaceholder'), trigger: 'blur' }
        ],
        active_desc: [
            { required: true, message: t('activeDescPlaceholder'), trigger: 'blur' }
        ],
        // limit_num:[
        //     { required: true,  validator: (rule: any, value: any, callback: any) => {
        //         if (!value) {
        //             callback(t('limitNumPlaceholder'))
        //         }else if(parseInt(value)<=0){
        //             callback(t('limitNumTips'))
        //         }else if(parseInt(value)>formData.value.goodsSkuIds.length) {
        //             callback(t('limitNumTipsThree'))
        //         }else{
        //             callback()
        //         }
        //
        //     }, trigger: 'blur' }
        // ]
    }
})

const formRef = ref<FormInstance>()
const bannerFormRef = ref<FormInstance>()
// 商品选择回调
const goodsSelect = (value: any) => {

//    formData.value.goodsSkuList.splice(0,formData.value.goodsSkuList.length);
    let arr = []
    for (let key in value) {
        let goods_sku: any = value[key];
        let sku: any = {
            goods_id: goods_sku.goods_id,
            sku_id: goods_sku.sku_id,
            goods_type_name: goods_sku.goods_type_name,
            price: goods_sku.price,
            sku_image: goods_sku.sku_image,
            goods_name: goods_sku.goods_name,
            sku_name: goods_sku.sku_name,
            stock: goods_sku.stock,
            newcomer_price: "", // 金额
        };
        if (formData.value.goodsSkuList.length) {
            formData.value.goodsSkuList.forEach((el: any) => {
                if (el.sku_id == sku.sku_id) {
                    sku = Object.assign(sku, el)
                }
            })

        }
        arr.push(deepClone(sku))
    }
    formData.value.goodsSkuList = arr;

}

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
    if (multipleSelection.value.length > 0 && multipleSelection.value.length < formData.value.goodsSkuList.length) {
        isIndeterminate.value = true
    } else {
        isIndeterminate.value = false
    }

    if (multipleSelection.value.length == formData.value.goodsSkuList.length) {
        toggleCheckbox.value = true
    }
}
const newcomer_price = ref(null)
const saveBatch = () => {
    if (!multipleSelection.value.length) {
        ElMessage({
            type: 'warning',
            message: `${t('batchEmptySelectedGoodsTips')}`
        })
        return
    }
    if(!newcomer_price.value){
        ElMessage({
            type: 'warning',
            message: `${t('newcomerPricePlaceholder')}`
        })
    }else if (isNaN(newcomer_price.value) || !regExp.digit.test(newcomer_price.value)) {
        ElMessage({
            type: 'warning',
            message: `${t('newcomerPriceTips')}`
        })
        return
    } else if (parseFloat(newcomer_price.value) <0) {
        ElMessage({
            type: 'warning',
            message: `${t('newcomerPriceTipsOne')}`
        })
        return
    }
    formData.value.goodsSkuList.forEach((item: any) => {
        if(multipleSelection.value.some((el: any) => el.sku_id == item.sku_id)){
            item.newcomer_price = newcomer_price.value
        }
    })
}
const deleteBatch = () => {
  if (!multipleSelection.value.length) {
    ElMessage({
      type: 'warning',
      message: `${t('batchEmptySelectedGoodsTips')}`
    })
    return
  }
  formData.value.goodsSkuList = formData.value.goodsSkuList.filter(item => !multipleSelection.value.some(selected => selected.sku_id === item.sku_id))
  formData.value.goodsSkuIds = formData.value.goodsSkuList.map(item => item.sku_id)
  multipleSelection.value = []
  toggleCheckbox.value = false
  isIndeterminate.value = false
  ElMessage.success(t('删除成功'))
}
// 删除商品
const deleteGoodsEvent = (row: any, index: any) => {
    formData.value.goodsSkuList.splice(index, 1);
    formData.value.goodsSkuIds.splice(formData.value.goodsSkuIds.indexOf(row.sku_id), 1);
}
const getActiveNewcomerConfigFn = () => {
    getActiveNewcomerConfig().then((res: any) => {
        Object.keys(formData.value).forEach((key) => {
            if(res.data[key]) formData.value[key] = res.data[key]
        })
        formData.value.goodsSkuIds = []
        if(formData.value.banner_list.length == 0) formData.value.banner_list.push({imageUrl: '', toLink: {name: ''}})
        formData.value.goodsSkuList = res.data.active_goods.map((item: any) => {
            formData.value.goodsSkuIds.push(item.sku_id)
            item.newcomer_price = item.active_goods_value.newcomer_price
            return item
        });
        loading.value = false;

    }).catch(() => {
        loading.value = false
    })
}

getActiveNewcomerConfigFn()
/**** 提交 ****/
const preventDuplication = ref(false)
const onSave = async () => {
    if (preventDuplication.value) return
    await formRef.value?.validate(async (valid) => {
        if (valid) {
            preventDuplication.value = true
            formData.value.goods_data = JSON.stringify(formData.value.goodsSkuList.map((item: any) => {
                return {
                    goods_id: item.goods_id,
                    sku_id: item.sku_id,
                    price: item.price,
                    newcomer_price: item.newcomer_price,
                }
            }))
            editActiveNewcomerConfig(formData.value).then(() => {
                newcomer_price.value = null
                getActiveNewcomerConfigFn()
                preventDuplication.value = false
            }).catch(() => {
                preventDuplication.value = false
            })
        }
    })

}
/**
 * 使用默认说明
 */
 const defaultActiveDescEvent = () => {
    formData.value.active_desc = `1、新人价是面向${formData.value.participation_way==='never_order'?t('neverOrder'):formData.value.participation_way==='assign_time_order'?t('assignTimeOrder'):t('assignTimeRegister')}提供的一种专属优惠价格，同一账号仅限享受一次优惠；\n2、仅限${formData.value.participation_way==='never_order'?t('neverOrder'):formData.value.participation_way==='assign_time_order'?formData.value.appoint_time+'之前未下过单的会员':formData.value.appoint_time+'之后注册的会员'}可参与；\n3、活动有效期：${formData.value.validity_type=='day'?'参与活动后'+formData.value.validity_day+'天内':formData.value.validity_time+'后截止'}。`;
}
// const deleteConfigList = (index:number)=>{
//     formData.value.banner_list.splice(index,1)
// }

// const addConfigList = ()=>{
//     formData.value.banner_list.push({imageUrl:'',toLink:{name: ''}})
// }
</script>

<style lang="scss" scoped>
.main-container {
    min-height: calc(100vh - 64px - 48px - 30px);
}
.sku_list :deep(.cell) {
    // min-height: 60px !important;
    overflow: initial !important;
}
.input-width-short{
  width: 100px;
}
.date-picker :deep(.el-input__wrapper){
    width: 200px !important;
}
</style>

<template>
    <div class="main-container">

        <el-card class="card !border-none" shadow="never">
            <el-page-header :content="pageName" :icon="ArrowLeft" @back="back()" />
        </el-card>

        <!-- 表单 -->
        <el-card class="box-card mt-[15px] !border-none " shadow="never" v-if="Object.keys(formData).length" v-loading="loading">
            <el-form class="page-form" :model="formData" label-width="120px" ref="formRef" :rules="formRules">
                <!-- 优惠券信息 -->
                <!-- 优惠券名称 -->
                <el-form-item :label="t('title')" prop="title">
                    <el-input v-model.trim="formData.title" clearable :placeholder="t('titlePlaceholder')" class="input-width" :maxlength="20" />
                </el-form-item>

                <!-- 优惠券面额 -->
                <el-form-item :label="t('price')" prop="price">
                    <el-input v-model.trim="formData.price" @keyup="filterDigit($event)"  clearable :placeholder="t('pricePlaceholder')" class="input-width" maxlength="5" />
                </el-form-item>

                <!-- 优惠券类型 -->
                <el-form-item :label="t('type')" prop="type">
                    <el-radio-group v-model="formData.type" disabled>
                        <el-radio :label="1">通用券</el-radio>
                        <el-radio :label="2">品类券</el-radio>
                        <el-radio :label="3">商品券</el-radio>
                    </el-radio-group>
                </el-form-item>

                <el-form-item v-if="formData.type == 2" prop="goods_category_ids">
                    <div>
                        <el-cascader v-model="formData.goods_category_ids" :options="options" :props="props" collapse-tags collapse-tags-tooltip clearable />
                    </div>
                </el-form-item>

                <el-form-item v-if="formData.type == 3" prop="goods_ids">
                    <div>
                        <el-form-item>
                            <goods-select-popup ref="goodsSelectPopupRef" v-model="formData.goods_ids" :min="1" :max="99" />
                        </el-form-item>
                    </div>
                </el-form-item>

                <!-- 使用门槛 -->
                <el-form-item :label="t('threshold')">
                    <el-radio-group v-model="formData.threshold">
                        <el-radio :label="1">{{ t('reduction') }}</el-radio>
                        <el-radio :label="2">{{ t('noThreshold') }}</el-radio>
                    </el-radio-group>
                </el-form-item>

                <el-form-item v-if="formData.threshold == 1" prop="min_condition_money">
                    最低满
                    <div class="flex items-center px-[5px]">
                        <el-input v-model.trim="formData.min_condition_money" @keyup="filterDigit($event)" clearable class="!w-[100px]" />
                    </div>
                    元可用
                </el-form-item>

                <!-- 使用时间 -->
                <el-form-item :label="t('validType')">
                    <el-radio-group v-model="formData.valid_type">
                        <el-radio :label="1">{{ t('days') }}</el-radio>
                        <el-radio :label="2">{{ t('times') }}</el-radio>
                    </el-radio-group>
                </el-form-item>

                <el-form-item v-show="formData.valid_type == 1">
                    领劵后立即生效，有效期
                    <div class="flex items-center px-[5px]">
                        <el-input v-model.trim="formData.length" @keyup="filterNumber($event)" clearable class="!w-[100px]" />
                    </div>
                    天
                </el-form-item>

                <el-form-item prop="valid_time" v-if="formData.valid_type == 2">
                    领劵后立即生效，使用时间截止至
                    <div class="w-[220px] pl-[5px]">
                        <el-date-picker v-model="formData.valid_time" type="datetime" :placeholder="t('validTimePlaceholder')" />
                    </div>
                </el-form-item>

                <!--  活动信息  -->
                <!-- 领取方式 -->
                <el-form-item :label="t('receiveType')">
                    <div>
                        <el-radio-group v-model="formData.receive_type" >
                            <el-radio :label="1">{{ t('user') }}</el-radio>
                            <el-radio :label="2">{{ t('grant') }}</el-radio>
                        </el-radio-group>
                    </div>
                    <div class="form-tip">开启手动领取后，会员可以直接在优惠券列表以及优惠券推广中直接领取</div>
                </el-form-item>

                <!--  领取时间  -->
                <el-form-item :label="t('receiveTime')" v-show="formData.receive_type == 1">
                    <el-radio-group v-model="formData.receive_type_time">
                        <el-radio :label="1">{{ t('limitedTime') }}</el-radio>
                        <el-radio :label="2">{{ t('unlimitedTime') }}</el-radio>
                    </el-radio-group>
                </el-form-item>

                <el-form-item prop="receive_time" v-show="formData.receive_type_time == 1 && formData.receive_type == 1">
                    <div class="w-[180px]">
                        <el-date-picker v-model="formData.receive_time" type="datetimerange" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期"/>
                    </div>
                </el-form-item>

                <!--  优惠券数量  -->
                <el-form-item :label="t('receiveNumber')" v-show="formData.receive_type == 1">
                    <el-radio-group v-model="formData.limit">
                        <el-radio :label="1">{{ t('limit') }}</el-radio>
                        <el-radio :label="2">{{ t('unlimited') }}</el-radio>
                    </el-radio-group>
                </el-form-item>

                <el-form-item v-show="formData.limit == 1 && formData.receive_type == 1" prop="remain_count">
                    <div>
                        <el-input onkeypress='return( /[\d]/.test(String.fromCharCode(event.keyCode) ) )'
                            v-model.trim="formData.remain_count" clearable :placeholder="t('remainCountPlaceholder')"
                            class="input-width" :min="formData.remain_count" :max="100000" :controls="false" maxlength="6">
                            <template #append>张</template>
                        </el-input>
                    </div>
                </el-form-item>

                <!-- 用户可领取数量 -->
                <el-form-item :label="t('userLimitCount')" prop="limit_count" v-show="formData.receive_type == 1">
                    <el-input onkeypress='return( /[\d]/.test(String.fromCharCode(event.keyCode) ) )'
                        v-model.trim="formData.limit_count" clearable :placeholder="t('userLimitCountPlaceholder')"
                        class="input-width" :min="1" :max="100000" maxlength="6">
                        <template #append>张</template>
                    </el-input>
                    <div class="form-tip">每个会员通过前端直接可领用数量</div>
                </el-form-item>

            </el-form>
        </el-card>

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
import { ref, computed, onMounted } from 'vue'
import { t } from '@/lang'
import { useRoute, useRouter } from 'vue-router'
import { getGoodsCategoryList, editCoupon, getCouponInfo } from '@/addon/shop/api/marketing'
import type { FormInstance } from 'element-plus'
import { ArrowLeft } from '@element-plus/icons-vue'
import { filterNumber, filterDigit } from '@/utils/common'
import goodsSelectPopup from '@/addon/shop/views/goods/components/goods-select-popup.vue'
import { cloneDeep } from 'lodash-es'

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title

const couponId: number = parseInt(route.query.id as string)
const loading = ref(false)
const start = new Date()
const goodsSelectPopupRef: any = ref(null)
const end = new Date()

end.setTime(end.getTime() + 3600 * 1000 * 2 * 360) // 设置结束默认时间为当前时间30天后
const initialFormData = {
    title: '', // 名称
    price: '', // 面值
    type: 1, // 优惠券类型 1通用券 2品类券 3商品券
    limit: 2,
    receive_type: 2, // 领取方式
    remain_count: 1000, // 剩余数量
    threshold: 2, // 门槛
    limit_count: '', // 单个用户领取数量
    // status: 1,  //状态 1 正常 2 未开启 3 已无效
    min_condition_money: '', // 商品最低多少金额可用优惠券
    length: 30, // 有效期时长(天)
    goods_ids: [], // 关联商品id
    goods_category_ids: [], // 关联商品分类id
    receive_type_time: 2,
    valid_type: 1, // 有效方式，1=时长，2=范围
    receive_time: [start, end],
    valid_time: end
}
const formData: Record<string, any> = ref({ ...initialFormData })
const formRef = ref<FormInstance>()

// 表单验证规则
const formRules = computed(() => {
    return {
        title: [
            { required: true, message: t('titlePlaceholder'), trigger: 'blur' }
        ],
        price: [
            { required: true, validator: priceRule, trigger: 'blur' }
        ],
        goods_ids: [
            { required: true, message: t('请选择商品'), trigger: 'blur' }
        ],
        goods_category_ids: [
            { required: true, message: t('请选择商品分类'), trigger: 'blur' }
        ],
        min_condition_money: [
            { required: true, validator: minConditionMoney, trigger: 'blur' }
        ],
        valid_time: [
            { required: true, validator: validTime, trigger: 'blur' }
        ],
        receive_time: [
            { required: true, validator: receiveTime, trigger: 'blur' }
        ],
        remain_count: [
            { required: true, validator: remainCount, trigger: 'blur' }
        ],
        limit_count: [
            { required: true, validator: limitCountRule, trigger: 'blur' }
        ]
    }
})

const receiveTime = (rule: any, value: any, callback: any) => {
    if (formData.value.receive_type_time == 1 && formData.value.receive_type == 1) {
        if (!formData.value.receive_time[0] || timestampFn(formData.value.receive_time[0]) <= Date.now()) {
            callback(new Error(t('领取开始时间不能小于等于当前时间')))
        }
        if (!formData.value.receive_time[1] || timestampFn(formData.value.receive_time[1]) <= timestampFn(formData.value.receive_time[0])) {
            callback(new Error(t('领取结束时间不能小于等于领取开始时间')))
        }
    }
    callback()
}

const validTime = (rule: any, value: any, callback: any) => {
    if (formData.value.valid_type == 2 && formData.value.valid_time <= Date.now()) {
        callback(new Error(t('有效期不能小于等于当前时间')))
    }
    if (formData.value.valid_type == 2 && formData.value.receive_type_time == 1 && formData.value.receive_type == 1) {
        if (timestampFn(formData.value.valid_time) <= timestampFn(formData.value.receive_time[1])) {
            callback(new Error(t('有效期不能小于等于领取结束时间')))
        }
    }
    callback()
}

const minConditionMoney = (rule: any, value: any, callback: any) => {
    if (formData.value.threshold == 1 && formData.value.min_condition_money <= 0) {
        callback(new Error(t('使用门槛最低不能小于0元')))
    }
    callback()
}

const remainCount = (rule: any, value: any, callback: any) => {
    if (formData.value.remain_count != '' && formData.value.remain_count > 100000) {
        callback(new Error(t('remainCountPlaceholder')))
    }
    if (!formData.value.remain_count || formData.value.remain_count != '' && formData.value.remain_count < 1) {
        callback(new Error(t('发放数量不能小于1张')))
    }
    callback()
}
const priceRule = (rule: any, value: any, callback: any) => {
    if (!formData.value.price || formData.value.price == '' || formData.value.price <= 0) {
        callback(new Error(t('pricePlaceholder')))
    }
    callback()
}

const limitCountRule = (rule: any, value: any, callback: any) => {
    if (!formData.value.limit_count || formData.value.limit_count != '' && formData.value.limit_count < 1) {
        callback(new Error(t('userLimitCountPlaceholder')))
    }
    if (formData.value.limit == 1 && formData.value.limit_count != '' && formData.value.remain_count != '' && parseInt(formData.value.limit_count) > parseInt(formData.value.remain_count)) {
        callback(new Error(t('限领张数不能大于发放数量')))
    }
    callback()
}

// 优惠券类型
const props = { multiple: true }
const options = ref([])
const categoryList = () => {
    getGoodsCategoryList({}).then((res) => {
        options.value = res.data.goods_category_tree
    })
}
const onSave = async (formEl: FormInstance | undefined) => {
    if (loading.value || !formEl) return
    await formEl.validate(async (valid) => {
        if (valid) {
            loading.value = true
            const data = cloneDeep(formData.value)
            if (data.type == 1) {
                delete data.goods_category_ids
                delete data.goods_ids
            } else if (data.type == 2) {
                delete data.goods_ids
            } else if (data.type == 3) {
                delete data.goods_category_ids
            }
            const save = editCoupon
            save(data).then(res => {
                loading.value = false
                history.back()
            }).catch(() => {
                loading.value = false
            })
        }
    })
}
onMounted(async () => {
    await categoryList()
    getCouponInfoFn(couponId)
})

// 时间格式转换时间戳
const timestampFn = (data) => {
    const dateObject = new Date(data)
    return dateObject.getTime()
}

// 详情查询
const getCouponInfoFn = (id: any) => {
    loading.value = true
    getCouponInfo(id).then(res => {
        formData.value = Object.assign(formData.value, res.data)
        if (parseInt(formData.value.start_time) != 0 && parseInt(formData.value.end_time) != 0) {
            const start_time = new Date(formData.value.start_time)
            const end_time = new Date(formData.value.end_time)
            formData.value.receive_type_time = 1
            formData.value.receive_time = [start_time, end_time]
        }

        if (res.data.valid_end_time) formData.value.valid_time = res.data.valid_end_time

        if (formData.value.type == 2) {
            goodsCategoryFormatting(formData.value.goods_category_ids)
        }
        loading.value = false
    }).catch(() => {
        loading.value = false
    })
}

const goodsCategoryFormatting = (data: any) => {
    const arr: any = []
    data.forEach((item: any, index: any) => {
        options.value.forEach((twoItem: any, twoIndex) => {
            if (twoItem.value == item) {
                arr[index] = []
                arr[index].push(twoItem.value)
            } else {
                twoItem.children.forEach((threeItem: any, threeIndex: any) => {
                    if (threeItem.value == item) {
                        arr[index] = []
                        arr[index].push(twoItem.value)
                        arr[index].push(threeItem.value)
                    }
                })
            }
        })
    })
    formData.value.goods_category_ids = arr
}

const back = () => {
    router.push('/shop/marketing/coupon/list')
}
</script>

<style lang="scss">
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
</style>

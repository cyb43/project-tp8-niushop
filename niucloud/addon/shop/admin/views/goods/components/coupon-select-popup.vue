<template>
    <div>
        <div @click="show">
            <slot>
                <el-button>{{ t('选择优惠券') }}</el-button>
                <div class="inline-block ml-[10px] text-[14px]" v-show="couponIds.length">
                    <span>{{ t('已选') }}</span>
                    <span class="text-primary mx-[2px]">{{ couponIds.length }}</span>
                    <span>{{ t('张') }}</span>
                </div>
            </slot>
        </div>
        <el-dialog v-model="showDialog" :title="t('优惠券选择')" width="1000px" :destroy-on-close="true" :close-on-click-modal="false">

            <el-form :inline="true" :model="couponTable.searchParam" ref="searchFormRef">
                <el-form-item :label="t('优惠券名称')" prop="keyword" class="form-item-wrap">
                    <el-input v-model.trim="couponTable.searchParam.title" :placeholder="t('请输入优惠券名称')" maxlength="60" />
                </el-form-item>
                <el-form-item class="form-item-wrap">
                    <el-button type="primary" @click="loadCouponList()">{{ t('search') }}</el-button>
                    <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
                </el-form-item>
            </el-form>

            <el-table :data="couponTable.data" size="large" v-loading="couponTable.loading" ref="couponListTableRef" max-height="400" @select="handleSelectChange" @select-all="handleSelectAllChange">
                <template #empty>
                    <span>{{ !couponTable.loading ? t('emptyData') : '' }}</span>
                </template>
                <el-table-column type="selection" width="55" />

                <el-table-column prop="title" :label="t('名称')" min-width="130" />
                <el-table-column prop="type_name" :label="t('类型')" min-width="130" />
                <el-table-column prop="price" :label="t('面值')" min-width="130" >
                    <template #default="{ row }">
                        <span >¥{{row.price}}</span>
                    </template>
                </el-table-column>
                <el-table-column  :label="t('使用门槛')" min-width="130" >
                    <template #default="{ row }">
                        <span v-if="row.min_condition_money == '0.00'">无门槛</span>
                        <span v-else >满{{ row.min_condition_money }}元可用</span>
                    </template>
                </el-table-column>
                <el-table-column  :label="t('有效期')" min-width="210">
                    <template #default="{ row }">
                        <span v-if="row.valid_type == 1">  领取之日起{{ row.length || '' }} 天内有效</span>
                        <span v-else> 使用截止时间至{{ row.valid_end_time || ''}} </span>
                    </template>
                </el-table-column>

            </el-table>
            <div class="mt-[16px] flex">
                <div class="flex items-center flex-1">
                    <div class="mr-[10px]" v-show="selectCouponNum">
                        <span>{{ t('已选择') }}</span>
                        <span class="text-primary mx-[2px]">{{ selectCouponNum }}</span>
                        <span>{{ t('张优惠券') }}</span>
                    </div>
                    <el-button type="primary" link @click="clear" v-show="selectCouponNum">{{ t('取消选择') }}</el-button>
                </div>
                <el-pagination v-model:current-page="couponTable.page" v-model:page-size="couponTable.limit"
                    layout="total, sizes, prev, pager, next, jumper" :total="couponTable.total"
                    @size-change="loadCouponList()" @current-change="loadCouponList" />
            </div>

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
import { ref, reactive, computed, nextTick } from 'vue'
import { cloneDeep } from 'lodash-es'
import { ElMessage } from 'element-plus'
import { getCouponSelectList } from '@/addon/shop/api/marketing'

const prop = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    max: {
        type: Number,
        default: 0
    },
    min: {
        type: Number,
        default: 0
    }
})

const emit = defineEmits(['update:modelValue','couponSelect'])

const couponIds: any = computed({
    get () {
        return prop.modelValue
    },
    set (value) {
        emit('update:modelValue', value)
    }
})

const showDialog = ref(false)

// 已选优惠券列表
const selectCoupon: any = reactive({})

// 已选优惠券数量
const selectCouponNum: any = computed(() => {
    return Object.keys(selectCoupon).length
})

const couponTable = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        title: '',
        status: 1,
        verify_coupon_ids:'',
    }
})

const searchFormRef = ref()

// 初始化数据
const initData = () => {
}

initData()

const couponListTableRef = ref()

// 监听表格复选框
const handleSelectChange = (selection: any, row: any) => {
    // 是否选中
    let isSelected = false
    for (let i = 0; i < selection.length; i++) {
        if (selection[i].id == row.id) {
            isSelected = true
            break
        }
    }
    if (isSelected) {
        selectCoupon['coupon_' + row.id] = row
    } else {
        // 未选中，删除当前优惠券
        delete selectCoupon['coupon_' + row.id]
    }
}

// 监听表格全选
const handleSelectAllChange = (selection: any) => {
    if (selection.length) {
        selection.forEach((item: any) => {
            selectCoupon['coupon_' + item.id] = item
        })
    } else {
        // 未选中，删除当前页面的数据
        couponTable.data.forEach((item: any) => {
            delete selectCoupon['coupon_' + item.id]
        })
    }
}

/**
 * 获取优惠券列表
 */
const loadCouponList = (page: number = 1, callback: any = null) => {
    couponTable.loading = true
    couponTable.page = page

    const searchData: any = cloneDeep(couponTable.searchParam);
    getCouponSelectList({
        page: couponTable.page,
        limit: couponTable.limit,
        ...searchData
    }).then(res => {
        couponTable.loading = false
        couponTable.data = res.data.data
        couponTable.total = res.data.total

        if (callback) callback(res.data.verify_coupon_ids)

        setCouponSelected();
    }).catch(() => {
        couponTable.loading = false
    })

}

// 表格设置选中状态
const setCouponSelected = () => {
    nextTick(() => {
        if (!couponListTableRef.value) return;
        for (let i = 0; i < couponTable.data.length; i++) {
            couponListTableRef.value.toggleRowSelection(couponTable.data[i], false);
            if (selectCoupon['coupon_' + couponTable.data[i].id]) {
                couponListTableRef.value.toggleRowSelection(couponTable.data[i], true);
            }
        }
    });
}

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()

    loadCouponList()
}

const show = () => {
    // 检测商品id集合是否存在，移除不存在的商品id，纠正数据准确性
    couponTable.searchParam.verify_coupon_ids = couponIds.value;
    loadCouponList(1, (verify_coupon_ids:any) => {
        // 第一次打开弹出框时，纠正数据，并且赋值已选优惠券
        if (couponIds.value) {
            couponIds.value.splice(0, couponIds.value.length, ...verify_coupon_ids)
            // 先删除 selectCoupon 中已经不再存在于 couponIds 中的优惠券
            for (let key in selectCoupon) {
                const couponId = key.replace('coupon_', '');
                if (!couponIds.value.includes(Number(couponId))) {
                    delete selectCoupon[key]; // 删除不存在的优惠券
                }
            }
            couponIds.value.forEach((item: any) => {
                if (!selectCoupon['coupon_' + item]) {
                    selectCoupon['coupon_' + item] = {};
                }
            })

            // 赋值已选择的优惠券
            for (let i = 0; i < couponTable.data.length; i++) {
                if (couponIds.value.indexOf(couponTable.data[i].id) != -1) {
                    selectCoupon['coupon_' + couponTable.data[i].id] = couponTable.data[i];
                }
            }
        }
    })
    showDialog.value = true
}

// 清空已选优惠券
const clear = () => {
    for (let k in selectCoupon) {
        delete selectCoupon[k];
    }
    setCouponSelected();
}

const save = () => {
    if (prop.min && selectCouponNum.value < prop.min) {
        ElMessage({
            type: 'warning',
            message: `${t('所选优惠券数量不能少于')}${prop.min}${t('张')}`,
        });
        return;
    }

    if (prop.max && prop.max > 0 && selectCouponNum.value && selectCouponNum.value > prop.max) {
        ElMessage({
            type: 'warning',
            message: `${t('所选优惠券数量不能超过')}${prop.max}${t('张')}`,
        });
        return;
    }

    let ids: any = [];
    for (let k in selectCoupon) {
        ids.push(parseInt(k.replace('coupon_', '')));
    }

    couponIds.value.splice(0, couponIds.value.length, ...ids)
    emit('couponSelect',selectCoupon)
    showDialog.value = false
}

defineExpose({
    showDialog,
    selectCoupon,
    selectCouponNum
})
</script>

<style lang="scss" scoped>
.form-item-wrap {
    margin-right: 10px !important;
    margin-bottom: 10px !important;

    &.last-child {
        margin-right: 0 !important;
    }
}
</style>

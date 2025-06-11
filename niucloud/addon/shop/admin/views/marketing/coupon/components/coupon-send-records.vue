<template>
    <el-drawer v-model="showDialog" :title="popTitle" direction="rtl" :before-close="handleClose" class="collection-detail-drawer">
        <div class="main-container" v-loading="loading">

            <el-card class="box-card !border-none mb-[10px] table-search-wrap" shadow="never">
                <el-form :inline="true" :model="tableData.searchParam" ref="searchFormRef">
                    <!-- <el-form-item :label="t('memberInfo')" prop="keywords">
                        <el-input v-model.trim="tableData.searchParam.keywords" class="w-[240px]" :placeholder="t('memberInfoPlaceholder')" />
                    </el-form-item> -->
                    <el-form-item :label="t('rangeType')" prop="range_type">
                        <el-select v-model="tableData.searchParam.range_type"  :placeholder="t('rangeTypePlaceholder')">
                            <el-option  v-for="(label, key) in rangeTypeList"  :key="key"  :label="label" :value="key"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="couponCollection()">{{ t('search') }}</el-button>
                        <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
                    </el-form-item>
                </el-form>
            </el-card>

            <div class="mt-[10px]">
                <el-table :data="tableData.data" ref="tableRef" size="large" v-loading="tableData.loading">
                    <template #empty>
                        <span>{{ !tableData.loading ? t('emptyData') : '' }}</span>
                    </template>
                    <el-table-column prop="" :label="t('collectionTitle')" >
                        <template #default="{row}">
                            <span >{{row.coupon.title }}</span>
                        </template>
                     </el-table-column>
                    <el-table-column prop="range_type_name" :label="t('rangeType')" />
                    <el-table-column prop="success_num" :label="t('collectionSumCount')" />
                    <el-table-column prop="status_name" :label="t('sendStatus')" />
                    <el-table-column prop="create_time" :label="t('sendTime')" />
                </el-table>
                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="tableData.page" v-model:page-size="tableData.limit"
                        layout="total, sizes, prev, pager, next, jumper" :total="tableData.total"
                        :page-sizes="[5,10,20,50,100]"
                        @size-change="couponCollection()" @current-change="couponCollection" />
                </div>
            </div>
        </div>

    </el-drawer>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import { getCouponSendRecords, initCoupon } from '@/addon/shop/api/marketing'
import { useRouter } from 'vue-router'
import { FormInstance } from 'element-plus'

const showDialog = ref(false)
const loading = ref(false)
let popTitle: string = '优惠券发放记录'
let couponId = '';

const router = useRouter()

const handleClose = (done: () => void) => {
    showDialog.value = false;
}

const activeName = ref('order')

const formData: Record<string, any> | null = ref(null)

const tableData = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: false,
    data: [],
    searchParam: {
        keywords: '',
        range_type: '',
    }
})
const searchFormRef = ref<FormInstance>()
const rangeTypeList = ref({})
/**
 * 获取领取记录列表
 */
const couponCollection = () => {
    tableData.loading = true
    getCouponSendRecords({
        page: tableData.page,
        limit: tableData.limit,
        id: couponId,
        ...tableData.searchParam
    }).then(res => {
        tableData.loading = false
        tableData.data = res.data.data
        tableData.total = res.data.total
    }).catch(() => {
        tableData.loading = false
    })
}

const init = () => {
    initCoupon().then(res => {
        rangeTypeList.value = res.data.range_type_list
    })
}
init()

// 订单详情
const showOrder = (data: any) => {
    showDialog.value = false;
    router.push('/shop/order/detail?order_id=' + data.trade_id)
}

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    couponCollection()
}

const setFormData = async (row: any = null) => {
    couponId = row.id;
    formData.value = null;
    activeName.value = 'order';
    couponCollection();
}

defineExpose({
    showDialog,
    setFormData
})
</script>

<style lang="scss">
.collection-detail-drawer{
    width: 1300px !important;
}
</style>

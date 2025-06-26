<template>
    <el-drawer v-model="showDialog" :title="popTitle" direction="rtl" :before-close="handleClose"
        class="collection-detail-drawer">
        <div class="main-container" v-loading="loading">
            <el-card class="box-card !border-none" shadow="never">
                <el-row class="flex">
                    <el-col :span="6" class="min-w-[100px]">
                        <div class="statistic-card">
                            <el-statistic :value="couponStatistics.receive_count ? Number.parseInt(couponStatistics.receive_count) : '0'"></el-statistic>
                            <div class="statistic-footer">
                                <div class="footer-item text-[14px] text-[#666]">
                                    <span>{{ t('receiveCount') }}</span>
                                </div>
                            </div>
                        </div>
                    </el-col>
                    <el-col :span="6" class="min-w-[100px]">
                        <div class="statistic-card">
                            <el-statistic :value="couponStatistics.receive_use_count ? Number.parseInt(couponStatistics.receive_use_count) : '0'"></el-statistic>
                            <div class="statistic-footer">
                                <div class="footer-item text-[14px] text-[#666]">
                                    <span>{{ t('receiveUseCount') }}</span>
                                </div>
                            </div>
                        </div>
                    </el-col>
                    <el-col :span="6" class="min-w-[100px]">
                        <div class="statistic-card">
                            <el-statistic :value="couponStatistics.receive_expire_count ? Number.parseInt(couponStatistics.receive_expire_count) : '0'"></el-statistic>
                            <div class="statistic-footer">
                                <div class="footer-item text-[14px] text-[#666]">
                                    <span>{{ t('receiveExpireCount') }}</span>
                                </div>
                            </div>
                        </div>
                    </el-col>
                    <el-col :span="6" class="min-w-[100px]">
                        <div class="statistic-card">
                            <el-statistic :value="couponStatistics.receive_invalid_count ? Number.parseInt(couponStatistics.receive_invalid_count) : '0'"></el-statistic>
                            <div class="statistic-footer">
                                <div class="footer-item text-[14px] text-[#666]">
                                    <span>{{ t('receiveInvalidCount') }}</span>
                                </div>
                            </div>
                        </div>
                    </el-col>
                </el-row>
            </el-card>

            <el-card class="box-card !border-none mb-[10px] table-search-wrap" shadow="never">
                <el-form :inline="true" :model="tableData.searchParam" @submit.prevent ref="searchFormRef">
                    <el-form-item :label="t('memberInfo')" prop="keywords">
                        <el-input v-model.trim="tableData.searchParam.keywords" class="w-[240px]" :placeholder="t('memberInfoPlaceholder')" />
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

                    <el-table-column prop="title" :label="t('collectionTitle')" />
                    <el-table-column :label="t('userName')">
                        <template #default="{ row }">
                            <div class="flex flex-col">
                                <span class="text-[12px] mt-[5px]">{{ row.member.nickname }}</span>
                                <span class="text-[12px] mt-[5px]"> {{ row.member.mobile }}</span>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="receive_type_name" :label="t('collectionReceiveType')" />
                    <el-table-column prop="create_time" :label="t('createTime')" />
                    <el-table-column prop="expire_time" :label="t('expireTime')" />
                    <el-table-column prop="status_name" :label="t('status')" />
                    <el-table-column :label="t('useTime')">
                        <template #default="{ row }">
                            {{ row.use_time || '--' }}
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('validity')">
                        <template #default="{ row }">
                            {{ row.create_time }}至{{ row.expire_time }}
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('operation')" fixed="right" align="right">
                        <template #default="{ row }">
                            <el-button type="primary" v-if="row.use_time != 0" link @click="showOrder(row)">{{t('showOrder') }}</el-button>
                        </template>
                    </el-table-column>
                </el-table>
                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="tableData.page" v-model:page-size="tableData.limit"
                        layout="total, sizes, prev, pager, next, jumper" :total="tableData.total"
                        :page-sizes="[5,10,20,50,100]" @size-change="couponCollection()"
                        @current-change="couponCollection" />
                </div>
            </div>
        </div>

    </el-drawer>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import { getCouponRecords, getCouponInfo } from '@/addon/shop/api/marketing'
import { useRouter } from 'vue-router'
import { FormInstance } from 'element-plus'

const showDialog = ref(false)
const loading = ref(false)
const popTitle: string = ref('优惠券领取记录')
let couponId = ''

const router = useRouter()

const handleClose = (done: () => void) => {
    showDialog.value = false;
}

const activeName = ref('order')

const formData: Record<string, any> | null = ref(null)

const tableData = reactive({
    page: 1,
    limit: 5,
    total: 0,
    loading: false,
    data: [],
    searchParam: {
        keywords: ''
    }
})
const searchFormRef = ref<FormInstance>()
const couponStatistics = ref([])
/**
 * 获取领取记录列表
 */
const couponCollection = () => {
    tableData.loading = true
    getCouponRecords({
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

    // 详情查询
    getCouponInfo(couponId).then(res => {
        couponStatistics.value = res.data
        loading.value = false
    }).catch(() => {
        loading.value = false
    })
}

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

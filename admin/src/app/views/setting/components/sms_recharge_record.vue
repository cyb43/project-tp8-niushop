<template>
    <div>
        <el-table :data="tableData.data" size="large" v-loading="tableData.loading" ref="goodBankListTableRef">
            <template #empty>
                <span>{{ !tableData.loading ? t("emptyData") : "" }}</span>
            </template>
            <el-table-column prop="order_no" :label="t('订单编号')" min-width="130" />
            <el-table-column prop="package_name" :label="t('短信套餐')" min-width="130" />
            <el-table-column prop="sms_num" :label="t('短信条数')" min-width="130" />
            <el-table-column prop="order_money" :label="t('订单总价')" min-width="130" />
            <el-table-column prop="pay_money" :label="t('实付金额')" min-width="130" />
            <el-table-column prop="order_status_name" :label="t('订单状态')" min-width="130" />
            <el-table-column prop="sms" :label="t('创建时间')" min-width="130" >
                <template #default="{ row }">
                    <div>{{ row.create_time }}</div>
                </template>
            </el-table-column>

            <el-table-column :label="t('operation')" fixed="right" align="right" min-width="120">
                <template #default="{ row }">
                    <el-button type="primary" link @click="detailEvent(row)">{{ t("详情") }}</el-button>
                </template>
            </el-table-column>
        </el-table>
        <div class="mt-[16px] flex justify-end">
            <el-pagination v-model:current-page="tableData.page" v-model:page-size="tableData.limit"
            layout="total, sizes, prev, pager, next, jumper" :total="tableData.total" @size-change="loadRankList()" @current-change="loadRankList" />
        </div>
        <el-dialog v-model="visibleDetail" :title="t('模版详情')" width="600px" destroy-on-close >
            <el-form label-width="100px" ref="formRef" class="page-form" v-loading="loading">
                <el-form-item :label="t('订单编号')" prop="template_id">
                    <div>{{ detail.order_no }}</div>
                </el-form-item>
                <el-form-item :label="t('用户名称')" prop="template_id">
                    <div>{{ detail.username }}</div>
                </el-form-item>
                <el-form-item :label="t('套餐名称')" prop="template_id">
                    <div>{{ detail.package_name }}</div>
                </el-form-item>
                <el-form-item :label="t('订单状态')" prop="title">
                    <div >{{ detail.order_status_name }}</div>
                </el-form-item>
                <el-form-item :label="t('短信条数')" prop="title">
                    <div >{{ detail.sms_num }}</div>
                </el-form-item>
                <el-form-item :label="t('订单金额')" prop="title">
                    <div >{{ detail.order_money }}</div>
                </el-form-item>
                <el-form-item :label="t('付款金额')" prop="title">
                    <div >{{ detail.pay_money }}</div>
                </el-form-item>
                <el-form-item :label="t('创建时间')" prop="title">
                    <div >{{ detail.create_time }}</div>
                </el-form-item>
            </el-form>
            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="visibleDetail = false">{{ t("cancel") }}</el-button>
                    <el-button type="primary" @click="visibleDetail = false">{{ t("confirm") }}</el-button>
                </span>
            </template>
        </el-dialog>
    </div>
</template>

<script lang="ts" setup>
import { ref, reactive, onMounted } from 'vue'
import { getSmsOrdersList, getOrderInfo } from '@/app/api/notice'
import { t } from '@/lang'

const props = defineProps({
    username: {
        type: String,
        default: ''
    }
})
// 表单内容
const tableData = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: false,
    data: [],
    searchParam: {
        name: '',
        order: '',
        sort: ''
    }
})

// 获取列表
const loadRankList = () => {
    tableData.loading = true
    const params = {
        page: tableData.page,
        limit: tableData.limit,
        ...tableData.searchParam
    }
    getSmsOrdersList(props.username, params).then((res) => {
        tableData.loading = false
        tableData.data = res.data.data
        tableData.total = res.data.total
    }).catch(() => {
        tableData.loading = false
    })
}

// 详情
const detail = ref({})
const visibleDetail = ref(false)
const loading = ref(false)
const detailEvent = (row:any) => {
    loading.value = true
    visibleDetail.value = true
    getOrderInfo(props.username, { out_trade_no: row.out_trade_no }).then(res => {
        detail.value = res.data
        loading.value = false
    })
    visibleDetail.value = true
}
onMounted(() => {
    if (props.username) {
        loadRankList()
    }
})

</script>

<style lang="scss" scoped>

</style>

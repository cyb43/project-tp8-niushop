<template>
    <div>
        <el-form :inline="true" :model="tableData.searchParam" ref="searchFormRef">
            <el-form-item :label="t('短信标题')" prop="content">
                <el-input v-model.trim="tableData.searchParam.content" :placeholder="t('请输入短信标题')" />
            </el-form-item>
            <el-form-item :label="t('接收人手机号')" prop="mobile">
                <el-input v-model.trim="tableData.searchParam.mobile" :placeholder="t('请输入接收人手机号')" />
            </el-form-item>
            <el-form-item :label="t('状态')" prop="smsStatus">
                <el-select v-model="tableData.searchParam.smsStatus" :placeholder="t('请选择状态')">
                    <el-option :label="t('全部')" :value="''"></el-option>
                    <el-option :label="t('发送成功')" :value="1"></el-option>
                    <el-option :label="t('发送失败')" :value="2"></el-option>
                    <el-option :label="t('待返回')" :value="3"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="loadRankList()">{{ t("search") }}</el-button>
                <el-button @click="resetForm(searchFormRef)">{{ t("reset") }}</el-button>
            </el-form-item>
        </el-form>
        <el-table :data="tableData.data" size="large" v-loading="tableData.loading" ref="goodBankListTableRef">
            <template #empty>
                <span>{{ !tableData.loading ? t("emptyData") : "" }}</span>
            </template>
            <el-table-column prop="content" :label="t('标题')" min-width="250" show-overflow-tooltip />
            <el-table-column prop="mobile" :label="t('接收人')" min-width="130" />
            <el-table-column prop="smsStatusName" :label="t('发送状态')" min-width="130" />

            <el-table-column prop="reportTime" :label="t('创建时间')" min-width="130" >
                <template #default="{ row }">
                    <div>{{ timeStampTurnTime(row.reportTime) }}</div>
                </template>
            </el-table-column>
            <el-table-column prop="sendTime" :label="t('发送时间')" min-width="130" >
                <template #default="{ row }">
                    <div>{{timeStampTurnTime(row.sendTime)  }}</div>
                </template>
            </el-table-column>
        </el-table>
        <div class="mt-[16px] flex justify-end">
            <el-pagination v-model:current-page="tableData.page" v-model:page-size="tableData.limit"
            layout="total, sizes, prev, pager, next, jumper" :total="tableData.total" @size-change="loadRankList()" @current-change="loadRankList" />
        </div>
    </div>
</template>

<script lang="ts" setup>
import { ref, reactive, onMounted } from 'vue'
import { getSmsSendList } from '@/app/api/notice'
import { timeStampTurnTime } from '@/utils/common'
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
        mobile: '',
        content: '',
        smsStatus: ''
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
    getSmsSendList(props.username, params).then((res) => {
        tableData.loading = false
        tableData.data = res.data.data
        tableData.total = res.data.total
    }).catch(() => {
        tableData.loading = false
    })
}
onMounted(() => {
    if (props.username) {
        loadRankList()
    }
})
const searchFormRef = ref<FormInstance>()
const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    loadRankList()
}
</script>

<style lang="scss" scoped>
</style>

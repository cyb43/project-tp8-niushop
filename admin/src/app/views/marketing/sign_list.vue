<template>
    <!--签到记录-->
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">

            <div class="flex justify-between items-center">
                <span class="text-page-title">{{ pageName }}</span>
            </div>

            <el-card class="box-card !border-none my-[10px] table-search-wrap" shadow="never">
                <el-form :inline="true" :model="memberSignListTableData.searchParam" ref="searchFormRef">
                    <el-form-item :label="t('memberInfo')" prop="keywords">
                        <el-input v-model.trim="memberSignListTableData.searchParam.keywords" class="!w-[230px]" :placeholder="t('memberInfoPlaceholder')" />
                    </el-form-item>
                    <el-form-item :label="t('createTime')" prop="create_time">
                        <el-date-picker v-model="memberSignListTableData.searchParam.create_time" type="datetimerange" value-format="YYYY-MM-DD HH:mm:ss" :start-placeholder="t('startDate')" :end-placeholder="t('endDate')" />
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="loadMemberSignList()">{{ t('search') }}</el-button>
                        <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
                    </el-form-item>
                </el-form>
            </el-card>

            <div class="mt-[10px]">
                <el-table :data="memberSignListTableData.data" size="large" v-loading="memberSignListTableData.loading">
                    <template #empty>
                        <span>{{ !memberSignListTableData.loading ? t('emptyData') : '' }}</span>
                    </template>

                    <el-table-column prop="member_id" :label="t('memberId')" min-width="100" :show-overflow-tooltip="true">
                        <template #default="{ row }">
                            {{ row.member.member_no }}
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('memberInfo')" min-width="140" :show-overflow-tooltip="true">
                        <template #default="{ row }">
                            <div class="flex items-center cursor-pointer" @click="toMember(row.member_id)">
                                <img class="w-[50px] h-[50px] mr-[10px]" v-if="row.member.headimg" :src="img(row.member.headimg)" alt="">
                                <img class="w-[50px] h-[50px] mr-[10px] rounded-full" v-else src="@/app/assets/images/member_head.png" alt="">
                                <div class="flex flex-col">
                                    <span>{{ row.member.nickname || '' }}</span>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="mobile" :label="t('mobile')" min-width="90">
                        <template #default="{ row }">
                            {{ row.member.mobile || '' }}
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('days')" min-width="110">
                        <template #default="{ row }">
                            {{ row.days }}{{ t('day') }}
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('dayAward')" min-width="100">
                        <template #default="{ row }">
                            <div v-if="row.day_award">
                                <template v-for="item in row.day_award">
                                    <div v-if="item.content">{{ item.content }}</div>
                                </template>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('continueAward')" min-width="150">
                        <template #default="{ row }">
                            <div v-if="row.continue_award">
                                <template v-for="item in row.continue_award">
                                    <div v-if="item.content">{{ item.content }}</div>
                                </template>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="create_time" :show-overflow-tooltip="true" :label="t('createTime')" min-width="150" />
                </el-table>

                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="memberSignListTableData.page"
                        v-model:page-size="memberSignListTableData.limit" layout="total, sizes, prev, pager, next, jumper"
                        :total="memberSignListTableData.total" @size-change="loadMemberSignList()"
                        @current-change="loadMemberSignList" />
                </div>
            </div>
        </el-card>
    </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import { getMemberSignList } from '@/app/api/member'
import { FormInstance } from 'element-plus'
import { img } from '@/utils/common'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title

const memberSignListTableData = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        keywords: '',
        create_time: '',
        member_id: ''
    }
})

const searchFormRef = ref<FormInstance>()

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    loadMemberSignList()
}

/**
 * 获取会员账单表列表
 */
const loadMemberSignList = (page: number = 1) => {
    memberSignListTableData.loading = true
    memberSignListTableData.page = page

    getMemberSignList({
        page: memberSignListTableData.page,
        limit: memberSignListTableData.limit,
        ...memberSignListTableData.searchParam
    }).then(res => {
        memberSignListTableData.loading = false
        memberSignListTableData.data = res.data.data
        memberSignListTableData.total = res.data.total
    }).catch(() => {
        memberSignListTableData.loading = false
    })
}
loadMemberSignList()

/**
 * 会员详情
 */
const toMember = (member_id: number) => {
    router.push(`/member/detail?id=${member_id}`)
}
</script>

<style lang="scss" scoped></style>

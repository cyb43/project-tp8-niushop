<template>
    <el-drawer v-model="showDialog" :title="t('dataAndStatistics')" direction="rtl" size="70%" :before-close="handleClose" class="member-detail-drawer">
        <el-tabs v-model="activeName" class="demo-tabs">
            <el-tab-pane :label="t('detailData')" name="detail_data">
                <el-card class="box-card !border-none my-[10px] table-search-wrap" shadow="never">
                    <el-form :inline="true" :model="formData.searchParam" ref="searchFormDiyFormRef">
                        <el-form-item :label="t('fillInFormPerson')" prop="keyword">
                            <el-input v-model.trim="formData.searchParam.keyword" :placeholder="t('fillInFormPersonplaceholder')" />
                        </el-form-item>
                        <el-form-item :label="t('fillInFormDate')" prop="create_time">
                            <el-date-picker v-model="formData.searchParam.create_time" type="datetimerange" value-format="YYYY-MM-DD HH:mm:ss" :start-placeholder="t('startDate')" :end-placeholder="t('endDate')" />
                        </el-form-item>
                        <el-form-item>
                            <el-button type="primary" @click="loadFormRecordsListFn()">{{ t('search') }}</el-button>
                            <el-button @click="resetForm(searchFormDiyFormRef)">{{ t('reset') }}</el-button>
                            <el-button type="primary" @click="exportEvent">{{ t('export') }}</el-button>
                        </el-form-item>
                    </el-form>
                </el-card>

                <el-table :data="formData.data" size="large" v-loading="formData.loading">
                    <template #empty>
                        <span>{{ !formData.loading ? t('emptyData') : '' }}</span>
                    </template>
                    <el-table-column fixed :label="t('fillInFormPersonInfo')" min-width="160">
                        <template #default="{ row }">
                            <div class="flex items-center cursor-pointer" @click="detailEvent(row.member.member_id)">
                                <div class="min-w-[50px] h-[50px] flex items-center justify-center">
                                    <el-image v-if="row.member.headimg" class="w-[50px] h-[50px]" :src="img(row.member.headimg)" fit="contain">
                                        <template #error>
                                            <div class="image-slot">
                                                <img class="w-[50px] h-[50px] rounded-full" src="@/app/assets/images/member_head.png" alt="">
                                            </div>
                                        </template>
                                    </el-image>
                                    <img class="w-[50px] h-[50px] rounded-full" v-else src="@/app/assets/images/member_head.png" alt="">
                                </div>
                                <div class="ml-2">
                                    <span :title="(row.member.nickname || row.member.username)" class="multi-hidden">{{row.member.nickname || row.member.username}}</span>
                                    <span class="text-primary text-[12px]">{{row.member.mobile || ''}}</span>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column fixed prop="create_time" :label="t('fillInFormDate')" min-width="120" />

                    <el-table-column v-for="item in formFieldsList" :key="item.field_key" :label="item.field_name" min-width="200">
                        <template #default="{ row }">
                            <!-- 动态渲染表单组件内容 -->
                            <template v-if="row.recordsFieldList[item.field_key]">
                                <component :is="row.recordsFieldList[item.field_key].detailComponent" :data="row.recordsFieldList[item.field_key]"/>
                            </template>
                        </template>
                    </el-table-column>

                    <el-table-column :label="t('operation')" fixed="right" align="right" min-width="70">
                        <template #default="{ row }">
                            <!-- <el-button type="primary" link @click="formDetailEvent(row)">{{ t('详情') }}</el-button> -->
                            <el-button type="primary" link @click="deleteEvent(row)">{{ t('delete') }}</el-button>
                        </template>
                    </el-table-column>

                </el-table>
                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="formData.page" v-model:page-size="formData.limit" :page-sizes="[6,10,20,30,50,100]"
                                   layout="total, sizes, prev, pager, next, jumper" :total="formData.total"
                                   @size-change="loadFormRecordsListFn()" @current-change="loadFormRecordsListFn" />
                </div>
            </el-tab-pane>
            <el-tab-pane :label="t('fillInFormPersonStatics')" name="member_stat">
                <el-card class="box-card !border-none my-[10px] table-search-wrap" shadow="never">
                    <el-form :inline="true" :model="formMemberList.searchParam" ref="searchFormDiyMemberRef">
                        <el-form-item :label="t('fillInFormPerson')" prop="keyword">
                            <el-input v-model.trim="formMemberList.searchParam.keyword" :placeholder="t('fillInFormPersonplaceholder')" />
                        </el-form-item>
                        <el-form-item>
                            <el-button type="primary" @click="getFormRecordsMemberFn()">{{ t('search') }}</el-button>
                            <el-button @click="resetFormMember(searchFormDiyMemberRef)">{{ t('reset') }}</el-button>
                            <el-button type="primary" @click="exportMemberEvent">{{ t('export') }}</el-button>
                        </el-form-item>
                    </el-form>
                </el-card>
                <el-table :data="formMemberList.data" size="large" v-loading="formMemberList.loading">
                    <template #empty>
                        <span>{{ !formMemberList.loading ? t('emptyData') : '' }}</span>
                    </template>
                    <el-table-column fixed :label="t('fillInFormPersonInfo')" min-width="200">
                        <template #default="{ row }">
                            <div class="flex items-center cursor-pointer" @click="detailEvent(row.member.member_id)">
                                <div class="min-w-[50px] h-[50px] flex items-center justify-center">
                                    <el-image v-if="row.member.headimg" class="w-[50px] h-[50px]" :src="img(row.member.headimg)" fit="contain">
                                        <template #error>
                                            <div class="image-slot">
                                                <img class="w-[50px] h-[50px] rounded-full" src="@/app/assets/images/member_head.png" alt="">
                                            </div>
                                        </template>
                                    </el-image>
                                    <img class="w-[50px] h-[50px] rounded-full" v-else src="@/app/assets/images/member_head.png" alt="">
                                </div>
                                <div class="ml-2">
                                    <span :title="(row.member.nickname || row.member.username)" class="multi-hidden">{{row.member.nickname || row.member.username}}</span>
                                    <span class="text-primary text-[12px]">{{row.member.mobile || ''}}</span>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <!-- <el-table-column fixed prop="create_time" :label="t('填表时间')" min-width="120" /> -->
                    <el-table-column fixed prop="create_time" :label="t('fillInFormTotal')" min-width="500">
                        <template #default="{ row }">
                            {{ row.write_count }}
                        </template>
                     </el-table-column>

                </el-table>
                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="formMemberList.page" v-model:page-size="formMemberList.limit"
                                   layout="total, sizes, prev, pager, next, jumper" :total="formMemberList.total"
                                   @size-change="getFormRecordsMemberFn()" @current-change="getFormRecordsMemberFn()" />
                </div>

            </el-tab-pane>
            <el-tab-pane :label="t('fieldStatistics')" name="field_stat">
                <el-card class="box-card !border-none my-[10px] table-search-wrap" shadow="never">
                    <el-form :inline="true" :model="formData.searchParam" ref="searchFormDiyFieldsRef">
                        <el-form-item>
                            <el-button type="primary" @click="exportFieldsEvent">{{ t('export') }}</el-button>
                        </el-form-item>
                    </el-form>
                </el-card>
                <el-collapse v-model="activeNames" class="diy-collapse mt-[15px]">
                    <el-collapse-item :title="item.field_name" :name="item.field_id" v-for="(item, index) in formFieldsStat" :key="index">
                        <template #title>
                            <div class="text-[16px] font-bold">{{item.field_name}}</div>
                        </template>
                        <el-table :data="item.value_list" border>
                            <el-table-column :label="item.field_name" prop="render_value">
                                <template #default="{ row }">
                                    {{row.render_value ? row.render_value : '未填写'}}
                                </template>
                            </el-table-column>
                            <el-table-column label="小计" prop="write_count"></el-table-column>
                            <el-table-column label="比例">
                                <template #default="{ row }">
                                    <el-progress :percentage="row.write_percent"></el-progress>
                                </template>
                            </el-table-column>
                        </el-table>
                    </el-collapse-item>
                </el-collapse>
            </el-tab-pane>
        </el-tabs>

        <el-dialog v-model="dialogVisible" :title="t('viewInformation')" width="400px">
            <div class="flex flex-col">
                <div class="flex mb-[10px]"  v-for="(item, index) in formDetail"  :key="index">
                    <div class="flex justify-end w-[100px]">{{ item.label }}：</div>
                    <div class="flex ml-[20px]">
                        <template v-if="Array.isArray(item.text)">
                            <div class="mr-[10px]" v-for="(textItem, i) in item.text" :key="i">
                                {{ textItem }}
                            </div>
                        </template>
                        <div v-else>{{ item.text }}</div>
                    </div>
                </div>
            </div>

            <template #footer>
                <span class="dialog-footer">
                    <el-button type="primary" @click="dialogVisible = false">{{ t('confirm') }}</el-button>
                </span>
            </template>
        </el-dialog>

    </el-drawer>
    <export-sure ref="exportSureDialog" :show="flag" type="diy_form_records" :searchParam="formData.searchParam" @close="handleExportClose" />
    <export-sure ref="exportSureDialog" :show="flagMember" type="diy_form_records_member" :searchParam="formMemberList.searchParam" @close="handleMemberExportClose" />
    <export-sure ref="exportSureDialog" :show="flagFields" type="diy_form_records_fields" :searchParam="formData.searchParam" @close="handleFieldsExportClose" />
</template>

<script lang="ts" setup>
import { reactive, ref, defineAsyncComponent } from 'vue'
import { t } from '@/lang'
import { getDiyFormFieldsList, getDiyFormFieldStat, getFormRecords, getFormRecordsInfo, deleteFormRecords, getFormRecordsMember } from '@/app/api/diy_form'
import { useRouter } from 'vue-router'
import { img } from '@/utils/common'
import { ElMessageBox, FormInstance } from 'element-plus'

const router = useRouter()
const showDialog = ref(false)
const activeName = ref('detail_data')
const formId = ref(0)
const dialogVisible = ref(false)
const searchFormDiyFormRef = ref<FormInstance>()
const searchFormDiyMemberRef = ref<FormInstance>()
const searchFormDiyFieldsRef = ref<FormInstance>()
const handleClose = (done: () => void) => {
    showDialog.value = false
}

const formData = reactive({
    page: 1,
    limit: 6,
    total: 0,
    loading: false,
    data: [],
    searchParam: {
        form_id: 0,
        keyword: '',
        create_time: ''
    }
})

const formFieldsList = ref([])

// 获取万能表单字段列表
const getDiyFormFieldsListFn = (form_id: any) => {
    getDiyFormFieldsList({
        form_id,
        order: 'field_id',
        sort: 'asc'
    }).then((res: any) => {
        formFieldsList.value = res.data
    })
}

// 获取字段统计列表
const formFieldsStat = ref([])
const getDiyFormFieldStatFn = (form_id: any) => {
    getDiyFormFieldStat({
        form_id
    }).then((res: any) => {
        formFieldsStat.value = res.data
    })
}

const modules: any = import.meta.glob('@/**/*.vue')
const formDetail = ref([])

const formDetailEvent = (row: any) => {
    getFormRecordsInfo(row.record_id).then((res:any) => {
        formDetail.value = res.data.value
        dialogVisible.value = true
    })
}

// 删除
const deleteEvent = (row: any) => {
    ElMessageBox.confirm(t('deleteTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        deleteFormRecords({
            record_id: row.record_id,
            form_id: row.form_id
        }).then(() => {
            initData()
        })
    })
}

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    loadFormRecordsListFn()
}
const resetFormMember = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    getFormRecordsMemberFn()
}

const loadFormRecordsListFn = (page: number = 1) => {
    formData.loading = true
    formData.page = page
    getFormRecords({
        page: formData.page,
        limit: formData.limit,
        ...formData.searchParam
    }).then((res: any) => {
        formData.loading = false
        formData.data = res.data.data
        formData.data.forEach((item: any) => {
            for (const key: any in item.recordsFieldList) {
                if (modules[item.recordsFieldList[key].detailComponent]) {
                    item.recordsFieldList[key].detailComponent && (item.recordsFieldList[key].detailComponent = defineAsyncComponent(modules[item.recordsFieldList[key].detailComponent]))
                }
            }
        })
        formData.total = res.data.total
    }).catch(() => {
        formData.loading = false
    })
}
const formMemberList = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: false,
    data: [],
    searchParam: {
        keyword: '',
        form_id: 0
    }
})

const getFormRecordsMemberFn = (page: number = 1) => {
    formMemberList.loading = true
    formMemberList.page = page
    getFormRecordsMember({
        page: formMemberList.page,
        limit: formMemberList.limit,
        ...formMemberList.searchParam
    }).then((res: any) => {
        formMemberList.data = res.data.data
        formMemberList.total = res.total
        formMemberList.loading = false
    }).catch((error) => {
        formMemberList.loading = false
    })
}

// 查看会员详情
const detailEvent = (member_id:number) => {
    const routeData = router.resolve(`/member/detail?id=${member_id}`)
    window.open(routeData.href, ' blank')
}

const setFormData = async (row: any = null) => {
    formId.value = row.form_id
    formData.searchParam.form_id = row.form_id
    formMemberList.searchParam.form_id = row.form_id

    getDiyFormFieldsListFn(row.form_id)
    initData()
}

const initData = () => {
    getFormRecordsMemberFn()
    getDiyFormFieldStatFn(formId.value)
    loadFormRecordsListFn()
}

/**
 * 表单填写记录明细导出
 */
const exportSureDialog = ref(null)
const flag = ref(false)
const handleExportClose = (val) => {
    flag.value = val
}
const exportEvent = () => {
    flag.value = true
}

const flagMember = ref(false)
const handleMemberExportClose = (val) => {
    flagMember.value = val
}
const exportMemberEvent = () => {
    flagMember.value = true
}

const flagFields = ref(false)
const handleFieldsExportClose = (val) => {
    flagFields.value = val
}
const exportFieldsEvent = () => {
    flagFields.value = true
}

defineExpose({
    showDialog,
    setFormData
})
</script>
<style lang="scss">
.diy-collapse .el-collapse-item__header{
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    .el-icon.el-collapse-item__arrow{
        margin-left: inherit;
        margin-right: 10px;
    }
}
</style>

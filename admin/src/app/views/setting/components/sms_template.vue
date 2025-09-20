<template>
    <div>
        <div class="flex justify-between items-start my-[10px]">
            <el-form :inline="true" :model="tableData.searchParam" ref="searchFormRef">
                <el-form-item :label="t('模版ID')" prop="template_id">
                    <el-input v-model.trim="tableData.searchParam.template_id" :placeholder="t('请输入模版ID')" />
                </el-form-item>
                <el-form-item :label="t('模版名称')" prop="name">
                    <el-input v-model.trim="tableData.searchParam.name" :placeholder="t('请输入模版名称')" />
                </el-form-item>
                <el-form-item :label="t('状态')" prop="status">
                    <el-select v-model="tableData.searchParam.status" :placeholder="t('请选择状态')">
                        <el-option :label="t('全部')" :value="''"></el-option>
                        <el-option  v-for="(statusText, statusValue) in template_status_list" :key="statusValue"  :label="statusText"  :value="statusValue"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="loadSmsTemplateList()">{{ t("search") }}</el-button>
                    <el-button @click="resetForm(searchFormRef)">{{ t("reset") }}</el-button>
                </el-form-item>
            </el-form>
            <el-button type="primary" @click="syncEvent()">{{ t("同步模版状态") }}</el-button>
        </div>
        <el-table :data="pagedData" size="large" v-loading="tableData.loading" ref="goodBankListTableRef">
            <template #empty>
                <span>{{ !tableData.loading ? t("emptyData") : "" }}</span>
            </template>
            <el-table-column prop="template_id" :label="t('模版ID')" min-width="100" />
            <el-table-column prop="name" :label="t('模版名称')" min-width="130" />
            <el-table-column prop="template_type_name" :label="t('模版类型')" min-width="130" >
                <template #default="{ row }">
                    <div>{{ row.template_type_name ? row.template_type_name : '--'}}</div>
                </template>
            </el-table-column>
            <el-table-column prop="sms" :label="t('模版内容')" min-width="200" >
                <template #default="{ row }">
                    <div>{{ row.sms?.content }}</div>
                </template>
            </el-table-column>
            <el-table-column prop="audit_info" :label="t('审核状态')" min-width="130" >
                <template #default="{ row }">
                    <div>{{ row.audit_info?.audit_status_name }} <span v-if="row.audit_info?.error_status_name" class="text-red-600">（{{row.audit_info?.error_status_name}}）</span> </div>
                </template>
            </el-table-column>
            <el-table-column :label="t('operation')" fixed="right" align="right" min-width="120">
                <template #default="{ row }">
                    <el-button type="primary" v-if="row.audit_info.audit_status!=2" link @click="reportEvent(row)">{{ row.audit_info.audit_status!=1 && row.audit_info.audit_status!=2? t("报备") : t("修改") }}</el-button>
                    <el-button type="primary" v-if="row.audit_info.audit_status==2" link @click="clearEvent(row)">{{ t('清除报备信息') }}</el-button>
                    <el-button type="primary" link @click="editEvent(row)">{{ t("详情") }}</el-button>
                </template>
            </el-table-column>
        </el-table>
        <div class="mt-[16px] flex justify-end">
            <el-pagination v-model:current-page="tableData.page" v-model:page-size="tableData.limit"
                           layout="total, sizes, prev, pager, next, jumper" :total="tableData.total"/>
        </div>
        <el-dialog v-model="visibleDetail" :title="t('模版详情')" width="600px" destroy-on-close >
            <el-form label-width="100px" ref="formRef" class="page-form">
                <el-form-item :label="t('短信类型')" prop="template_id">
                    <div>{{ detail.sms_type }}</div>
                </el-form-item>
                <el-form-item :label="t('模版名称')" prop="template_id">
                    <div>{{ detail.name }}</div>
                </el-form-item>
                <el-form-item :label="t('模版类型')" prop="title">
                    <div >{{ detail.title }}</div>
                </el-form-item>
                <el-form-item :label="t('短信内容')" prop="title" v-if="detail.sms">
                    <div >{{ detail.sms?.content }}</div>
                </el-form-item>
                <el-form-item :label="t('审核状态')" prop="title">
                    <div >{{ detail.audit_info.audit_status_name }}</div>
                </el-form-item>
            </el-form>
            <template #footer>
                <span class="dialog-footer">
                    <!-- <el-button @click="visibleDetail = false">{{ t("cancel") }}</el-button> -->
                    <el-button type="primary" @click="visibleDetail = false">{{ t("confirm") }}</el-button>
                </span>
            </template>
        </el-dialog>
        <el-dialog v-model="visibleReport" :title="t('模版报备')" width="820px" destroy-on-close >
            <el-form label-width="100px" ref="formRef" class="page-form" v-loading="reportLoading">
                <el-form-item :label="t('模版名称')" prop="template_id">
                    <div class="input-width">{{ detail.name }}</div>
                </el-form-item>
                <el-form-item :label="t('模版类型')" prop="title">
                    <el-radio-group v-model="reportData.template_type">
                        <el-radio v-for="[key, value] in Object.entries(template_type_list)" :key="key" :label="Number(key)">{{ value }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <div class="ml-[100px] mb-[10px] mt-[-10px] text-[12px] text-[#999] leading-[20px]">
                    <div>验证码：仅支持验证码类型变量</div>
                    <div>行业通知：不支持验证码类型变量</div>
                    <div>营销推广：不支持变量</div>
                </div>

                <el-form-item :label="t('变量类型')" prop="params_json" v-if="detail.variable && Object.keys(detail.variable).length > 0">
                    <div v-for="(label, key) in detail.variable" :key="key" class="mb-2 flex items-center">
                        <div class="flex flex-1 items-center">
                            <div class="w-32 mr-1 ">{{ label }}</div>
                            <el-select v-model="reportData.params_json[key]" placeholder="请选择类型" class="flex-1" filterable clearable :disabled="isMarketingWithVariable">
                                <el-option v-for="item in filteredParamTypes" :key="item.type" :label="item.name + '（' + item.desc + '）'" :value="item.type"/>
                            </el-select>
                        </div>
                    </div>
                </el-form-item>

            </el-form>
            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="visibleReport = false">{{ t("cancel") }}</el-button>
                    <el-button type="primary" @click="reportTemplateFn()" :disabled="isMarketingWithVariable">{{ t("confirm") }}</el-button>
                </span>
            </template>
        </el-dialog>
        <el-dialog v-model="visibleAsync" :title="t('同步模版状态')" width="800px" destroy-on-close >
            <el-alert type="warning" :closable="false" class="!mb-[10px]">
                <template #default>
                    以下模版名称重复，请先调整模版名称后重新同步模版
                </template>
            </el-alert>
            <el-form label-width="100px" ref="formRef" class="page-form">
                <div v-if="Object.keys(repeatList).length" class="h-[500px] overflow-y-auto">
                    <el-table :data="repeatListArray" border style="width: 100%;">
                        <el-table-column label="模版名称" prop="name" />
                        <el-table-column label="插件名称">
                        <template #default="{ row }">
                            <el-tag v-for="item in row.platforms" :key="item" class="mr-1 mb-1">{{ item }}</el-tag>
                        </template>
                        </el-table-column>
                    </el-table>
                </div>
            </el-form>
            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="visibleAsync = false">{{ t("cancel") }}</el-button>
                    <el-button type="primary" @click="visibleAsync = false">{{ t("confirm") }}</el-button>
                </span>
            </template>
        </el-dialog>
    </div>
</template>

<script lang="ts" setup>
import { ref, computed, reactive, onMounted, watch } from 'vue'
import { getTemplateList, getTemplateReportConfig, reportTemplate, templateSync, getreportTemplateInfo, clearTemplate } from '@/app/api/notice'
import { t } from '@/lang'

const props = defineProps({
    username: {
        type: String,
        default: ''
    },
    signature: {
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
    data: [], // 当前页展示的数据（通过 computed 生成）
    allData: [], // 原始完整数据
    searchParam: {
        template_id: '',
        name: '',
        status: ''
    }
})
const filterData = () => {
    const { template_id, name, status } = tableData.searchParam
    return tableData.allData.filter(item => {
        const matchId = !template_id || String(item.template_id || '').includes(template_id)
        const matchName = !name || String(item.name || '').includes(name)
        const matchStatus = !status || item.audit_info.audit_status == status
        return matchId && matchName && matchStatus
    })
}

watch(() => [tableData.limit, tableData.page], () => {
    pagedDataChange()
})

// 获取列表
const loadSmsTemplateList = () => {
    tableData.loading = true
    getTemplateList({ sms_type: 'niuyun', username: props.username }).then((res) => {
        tableData.allData = res.data
        tableData.page = 1 // 搜索后回到第一页
        pagedDataChange()
    }).catch(() => {
        tableData.loading = false
    })
}
const searchFormRef = ref(null)
const resetForm = (formRef) => {
    if (!formRef) return
    tableData.searchParam = {
        template_id: '',
        name: '',
        status: ''
    }
    loadSmsTemplateList()
}
const pagedData = ref([])
const pagedDataChange = () => {
    const filtered = filterData() // 使用筛选后的数据
    tableData.total = filtered.length
    const start = (tableData.page - 1) * tableData.limit
    const end = start + tableData.limit
    pagedData.value = filtered.slice(start, end)
    tableData.loading = false
}

onMounted(() => {
    if (props.username) {
        loadSmsTemplateList()
    }
})

const visibleAsync = ref(false)
const repeatList = ref({})
const syncEvent = () => {
    templateSync('niuyun', props.username).then((res) => {
        repeatList.value = res.data.repeat_list
        if (repeatList.value && Object.keys(repeatList.value).length > 0) {
            visibleAsync.value = true
        } else {
            loadSmsTemplateList()
        }
    })
}

const repeatListArray = computed(() => {
    return Object.entries(repeatList.value).map(([name, platforms]) => ({
        name,
        platforms
    }))
})

// 详情
const detail = ref(null)
const visibleDetail = ref(false)
const editEvent = (row:any) => {
    visibleDetail.value = true
    detail.value = row
}

// 清除报备
const clearEvent = (row:any) => {
    ElMessageBox.confirm(t('确定要清除报备信息吗'), t('提示'), {
        confirmButtonText: t('确定'),
        cancelButtonText: t('取消'),
        type: 'warning'
    }).then(() => {
        clearTemplate(props.username, row.template_id).then(() => {
            loadSmsTemplateList()
        })
    }).catch(() => {
    })
}
// 报备
const template_params_type_list = ref({})
const template_type_list = ref({})
const template_status_list = ref({})
const visibleReport = ref(false)
const reportData = ref({
    template_type: 1,
    template_key: '',
    params_json: {}
})
const getTemplateReportConfigFn = () => {
    getTemplateReportConfig().then((res) => {
        template_params_type_list.value = res.data.template_params_type_list
        template_type_list.value = res.data.template_type_list
        template_status_list.value = res.data.template_status_list
    })
}

getTemplateReportConfigFn()

const filteredParamTypes = computed(() => {
    if (reportData.value.template_type === 1) {
        return template_params_type_list.value.filter(item => item.type === 'valid_code')
    } else {
        return template_params_type_list.value
    }
})
const isMarketingWithVariable = computed(() => {
    return reportData.value.template_type === 3 && detail.value.variable && Object.keys(detail.value.variable).length > 0
})

watch(isMarketingWithVariable, (val) => {
    if (val) {
        ElMessage.error('营销推广类型不支持变量')
    }
})

const reportLoading = ref(false)
const reportEvent = (row:any) => {
    reportLoading.value = true
    const signature = props.signature
    if (!signature) {
        ElMessage.error('请先配置签名')
    } else {
        if (row.template_id) {
            visibleReport.value = true
            detail.value = row
            getreportTemplateInfo('niuyun', props.username, { template_key: row.key }).then((res) => {
                const paramJson = res.data?.param_json ?? {}
                reportData.value.template_key = res.data.template_key
                reportData.value.template_type = Number(res.data.template_type)
                reportData.value.params_json = {}
                if (detail.value.variable) {
                    for (const key in detail.value.variable) {
                        reportData.value.params_json[key] = paramJson[key] ?? ''
                    }
                }
                reportLoading.value = false
            })
        } else {
            visibleReport.value = true
            reportLoading.value = false
            detail.value = row
            reportData.value.template_type = 1
            reportData.value.template_key = detail.value.key
            reportData.value.params_json = {}
        }
    }
}

const reportTemplateFn = () => {
    if (!detail.value.sms) {
        ElMessage.error('请先配置模版内容')
        return
    }
    // 校验每个变量是否已选择类型
    const missingParams = Object.entries(detail.value.variable).some(
        ([key]) => !reportData.value.params_json[key]
    )
    if (missingParams) {
        ElMessage.error('请为每个变量选择类型')
        return
    }
    if (detail.value.template_id) {
        reportData.value.template_id = Number(detail.value.template_id)
    }
    reportTemplate(detail.value.sms_type, props.username, reportData.value).then((res) => {
        visibleReport.value = false
        loadSmsTemplateList()
    })
}
</script>

<style lang="scss" scoped>
</style>

<template>
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">

            <div class="flex justify-between items-center">
                <span class="text-page-title">{{ pageName }}</span>
                <el-button type="primary" class="w-[100px]" @click="dialogVisible = true">{{ t('addDiyForm') }}</el-button>
            </div>

            <el-card class="box-card !border-none my-[10px] table-search-wrap" shadow="never">
                <el-form :inline="true" :model="diyFormTableData.searchParam" ref="searchFormDiyFormRef">
                    <el-form-item :label="t('title')" prop="title">
                        <el-input v-model.trim="diyFormTableData.searchParam.title" :placeholder="t('titlePlaceholder')" />
                    </el-form-item>
<!--                    <el-form-item :label="t('forAddon')" prop="addon_name">-->
<!--                        <el-select v-model="diyFormTableData.searchParam.addon_name" :placeholder="t('forAddonPlaceholder')" @change="handleSelectAddonChange">-->
<!--                            <el-option :label="t('all')" value="" />-->
<!--                            <el-option v-for="(item, key) in apps" :label="item.title" :value="key" :key="key"/>-->
<!--                        </el-select>-->
<!--                    </el-form-item>-->
                    <el-form-item :label="t('typeName')" prop="type">
                        <el-select v-model="diyFormTableData.searchParam.type" :placeholder="t('formTypePlaceholder')">
                            <el-option :label="t('all')" value="" />
                            <el-option v-for="(item, key) in formType" :label="item.title" :value="key" :key="key"/>
                        </el-select>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="loadDiyFormList()">{{ t('search') }}</el-button>
                        <el-button @click="resetForm(searchFormDiyFormRef)">{{ t('reset') }}</el-button>
                    </el-form-item>
                </el-form>
            </el-card>
            <div class="mb-[10px] flex items-center">
                <el-checkbox v-model="toggleCheckbox" size="large" class="px-[14px]" @change="toggleChange" :indeterminate="isIndeterminate" />
                <el-button @click="batchDeleteForms" size="small">{{t("batchDeletion")}}</el-button>
            </div>
            <el-table :data="diyFormTableData.data" size="large" ref="diyFormListTableRef" v-loading="diyFormTableData.loading" @selection-change="handleSelectionChange">
                <template #empty>
                    <span>{{ !diyFormTableData.loading ? t('emptyData') : '' }}</span>
                </template>
                <el-table-column type="selection" width="55" />
                <el-table-column prop="page_title" :label="t('title')" min-width="120" />
<!--                <el-table-column prop="addon_name" :label="t('forAddon')" min-width="80" />-->
                <el-table-column prop="type_name" :label="t('typeName')" min-width="80" />
                <el-table-column :label="t('status')" min-width="80">
                    <template #default="{ row }">
                        <el-tag type="success" v-if="row.status == 1" class="cursor-pointer" @click="showClick(row)">{{ t('statusOn') }}</el-tag>
                        <el-tag type="info" v-else class="cursor-pointer" @click="showClick(row)">{{ t('statusOff') }}</el-tag>
                    </template>
                </el-table-column>
                <el-table-column prop="update_time" :label="t('updateTime')" min-width="120" />

                <el-table-column :label="t('operation')" fixed="right" align="right" min-width="130">
                    <template #default="{ row }">
                        <div class="flex items-center justify-end">
                            <el-button type="primary" v-if="row.status == 1 && row.type=='DIY_FORM'" link @click="spreadEvent(row)">{{ t('promotion') }}</el-button>
<!--                            <el-button type="primary" link @click="toPreview(row)">{{ t('preview') }}</el-button>-->
                            <el-button type="primary" link @click="editEvent(row)">{{ t('edit') }}</el-button>
                            <el-button v-if="row.status == 0" type="primary" link @click="deleteEvent(row.form_id)">{{ t('delete') }}</el-button>
                            <el-button type="primary" link @click="detailEvent(row)">{{ t('detail') }}</el-button>
                            <el-dropdown placement="bottom" trigger="click" class="ml-[12px]">
                                <el-button type="primary" link>{{ t('more') }}</el-button>
                                <template #dropdown>
                                    <el-dropdown-menu>
                                        <el-dropdown-item v-if="row.type=='DIY_FORM'">
                                            <el-button type="primary" class="w-full" link @click="submitConfigEvent(row)">{{ t('submitSuccess') }}</el-button>
                                        </el-dropdown-item>
                                        <el-dropdown-item>
                                            <el-button type="primary" class="w-full" link @click="writeConfigEvent(row)">{{ t('writeSet') }}</el-button>
                                        </el-dropdown-item>
                                        <el-dropdown-item v-if="row.type=='DIY_FORM'">
                                            <el-button type="primary" class="w-full" link @click="openShare(row)">{{ t('shareSet') }}</el-button>
                                        </el-dropdown-item>
                                        <el-dropdown-item>
                                            <el-button type="primary" class="w-full" link @click="exportEvent(row)">{{ t('export') }}</el-button>
                                        </el-dropdown-item>
                                        <el-dropdown-item>
                                            <el-button type="primary" class="w-full" link @click="copyEvent(row.form_id)">{{ t('copy') }}</el-button>
                                        </el-dropdown-item>
                                    </el-dropdown-menu>
                                </template>
                            </el-dropdown>
                        </div>
                    </template>
                </el-table-column>

            </el-table>
            <div class="mt-[16px] flex justify-end">
                <el-pagination v-model:current-page="diyFormTableData.page" v-model:page-size="diyFormTableData.limit"
                    layout="total, sizes, prev, pager, next, jumper" :total="diyFormTableData.total"
                    @size-change="loadDiyFormList()" @current-change="loadDiyFormList" />
            </div>

        </el-card>

        <!--添加表单-->
        <el-dialog v-model="dialogVisible" :title="t('addFormTips')" width="980px">
            <el-form :model="formData" ref="formRef" :rules="formRules">
            <!--                <el-form-item :label="t('title')" prop="title">-->
            <!--                    <el-input v-model.trim="formData.title" :placeholder="t('titlePlaceholder')" clearable maxlength="12" show-word-limit class="w-full" />-->
            <!--                </el-form-item>-->
                <el-form-item  prop="type">
                    <div class="image-selection-container">
                        <div
                            v-for="(item, key) in formType"
                            :key="key"
                            class="image-option"
                            :class="{ selected: formData.type === key }"
                            @click="selectType(key)"
                        >
                            <img :src="img(item.preview)" class="option-image" />
                            <div class="option-title">{{ item.title }}</div>
                        </div>
                    </div>
                    <!-- <el-select v-model="formData.type" :placeholder="t('formTypePlaceholder')" class="!w-full">
                        <el-option v-for="(item, key) in formType" :label="item.title" :value="key" :key="key"/>
                    </el-select> -->
                </el-form-item>
            </el-form>

            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="dialogVisible = false">{{ t('cancel') }}</el-button>
                    <el-button type="primary" @click="addEvent(formRef)">{{ t('confirm') }}</el-button>
                </span>
            </template>
        </el-dialog>

        <!-- 分享设置-->
        <el-dialog v-model="shareDialogVisible" :title="t('shareSet')" width="30%">
            <el-tabs v-model="tabShareType">
                <el-tab-pane :label="t('wechat')" name="wechat"></el-tab-pane>
                <el-tab-pane :label="t('weapp')" name="weapp"></el-tab-pane>
            </el-tabs>
            <el-form :model="shareFormData[tabShareType]" label-width="90px" ref="shareFormRef" :rules="shareFormRules">
                <el-form-item :label="t('sharePage')">
                    <span>{{ sharePage }}</span>
                </el-form-item>
                <el-form-item :label="t('shareTitle')" prop="title">
                    <el-input v-model.trim="shareFormData[tabShareType].title" :placeholder="t('shareTitlePlaceholder')" clearable maxlength="30" show-word-limit />
                </el-form-item>
                <el-form-item :label="t('shareDesc')" prop="desc" v-if="tabShareType == 'wechat'">
                    <el-input v-model.trim="shareFormData[tabShareType].desc" :placeholder="t('shareDescPlaceholder')" type="textarea" rows="4" clearable maxlength="100" show-word-limit />
                </el-form-item>
                <el-form-item :label="t('shareImageUrl')" prop="url">
                    <upload-image v-model="shareFormData[tabShareType].url" :limit="1" />
                </el-form-item>
            </el-form>

            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="shareDialogVisible = false">{{ t('cancel') }}</el-button>
                    <el-button type="primary" @click="shareEvent(shareFormRef)">{{ t('confirm') }}</el-button>
                </span>
            </template>
        </el-dialog>

        <!-- 推广弹出框 -->
        <spread-popup ref="spreadPopupRef" />

        <!-- 表单提交成功页弹出框 -->
        <form-submit-popup ref="formSubmitPopupRef" @complete="loadDiyFormList" />

        <!-- 表单填写设置弹出框 -->
        <form-write-popup ref="formWritePopupRef" @complete="loadDiyFormList" />

        <records-detail ref="recordsDetailDialog"/>

        <!-- 表单明细导出弹出框 -->
        <export-sure ref="exportSureDialog" :show="flag" type="diy_form_records" :searchParam="diyFormDetailData" @close="handleExportClose" />

    </div>
</template>

<script lang="ts" setup>
import { reactive, ref, computed } from 'vue'
import { t } from '@/lang'
import { getFormType, getApps, getDiyFormPageList, deleteDiyForm, editDiyFormShare, editFormStatus, copyForm } from '@/app/api/diy_form'
import { FormInstance, ElMessage, ElMessageBox } from 'element-plus'
import { useRoute, useRouter } from 'vue-router'
import { setTablePageStorage, getTablePageStorage, img } from '@/utils/common'

import recordsDetail from '@/app/views/diy_form/records.vue'
import formSubmitPopup from '@/app/views/diy_form/components/form-submit-popup.vue'
import formWritePopup from '@/app/views/diy_form/components/form-write-popup.vue'
import spreadPopup from '@/components/spread-popup/index.vue'

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title
const repeat = ref(false)

const formType: any = reactive({}) // 表单类型

// 添加自定义表单
const formData = reactive({
    title: '',
    type: ''
})

// 详情
const recordsDetailDialog: Record<string, any> | null = ref(null)
const detailEvent = (row: any) => {
    const data = { form_id: row.form_id }
    recordsDetailDialog.value.setFormData(data)
    recordsDetailDialog.value.showDialog = true
}

// 表单验证规则
const formRules = computed(() => {
    return {
        title: [
            { required: true, message: t('titlePlaceholder'), trigger: 'blur' }
        ],
        type: [
            { required: true, message: t('formTypePlaceholder'), trigger: 'blur' }
        ]
    }
})

const formRef = ref<FormInstance>()
const dialogVisible = ref(false)
const addEvent = async (formEl: FormInstance | undefined) => {
    if (!formEl) return

    await formEl.validate(async (valid) => {
        if (valid) {
            const query = { type: formData.type } // , title: formData.title
            const url = router.resolve({
                path: '/decorate/form/edit',
                query
            })
            window.open(url.href)
            dialogVisible.value = false
            formData.title = ''
            formData.type = ''
        }
    })
}

const showClick = (row: any) => {
    const data = row.status === 1 ? 0 : 1
    const obj = {
        form_id: row.form_id,
        status: data
    }
    editFormStatus(obj).then((res) => {
        row.status = data
    })
}

// 获取万能表单类型
const loadFormType = (addon = '') => {
    getFormType({}).then(res => {
        for (const key in formType) {
            delete formType[key]
        }

        for (const key in res.data) {
            formType[key] = res.data[key]
        }
        formData.type = Object.keys(formType)[0]
    })
}

loadFormType()

const apps: any = reactive({}) // 应用插件列表

// todo 靠后完善
// getApps({}).then(res=>{
//     if(res.data){
//         for (const key in res.data) {
//             apps[key] = res.data[key];
//         }
//     }
// });

// 根据所属插件，查询表单类型
const handleSelectAddonChange = (value: any) => {
    diyFormTableData.searchParam.type = ''
    loadFormType(value)
}

const diyFormTableData: any = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        title: '',
        type: '',
        mode: '',
        addon_name: ''
    }
})

const searchFormDiyFormRef = ref<FormInstance>()

// 获取自定义表单列表
const loadDiyFormList = (page: number = 1) => {
    diyFormTableData.loading = true
    diyFormTableData.page = page

    getDiyFormPageList({
        page: diyFormTableData.page,
        limit: diyFormTableData.limit,
        ...diyFormTableData.searchParam
    }).then(res => {
        diyFormTableData.loading = false
        diyFormTableData.data = res.data.data
        diyFormTableData.total = res.data.total
        setTablePageStorage(diyFormTableData.page, diyFormTableData.limit, diyFormTableData.searchParam)
    }).catch(() => {
        diyFormTableData.loading = false
    })
}

loadDiyFormList(getTablePageStorage(diyFormTableData.searchParam).page)

const selectType = (index: number) => {
    formData.type = index.toString()
}

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    loadDiyFormList()
}

// 编辑自定义表单
const editEvent = (data: any) => {
    const url = router.resolve({
        path: '/decorate/form/edit',
        query: { form_id: data.form_id }
    })
    window.open(url.href)
}

// 复制页面
const copyEvent = (id: any) => {
    ElMessageBox.confirm(t('diyFormCopyTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        if (repeat.value) return
        repeat.value = true

        copyForm({ form_id: id }).then((res: any) => {
            if (res.code == 1) {
                loadDiyFormList()
            }
            repeat.value = false
        }).catch(() => {
            repeat.value = false
        })
    })
}

// 删除自定义表单
const deleteEvent = (form_id: number) => {
    ElMessageBox.confirm(t('diyFormDeleteTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        deleteDiyForm({ form_ids: [form_id] }).then(() => {
            loadDiyFormList()
        }).catch(() => {
        })
    })
}
// 批量复选框
const toggleCheckbox = ref()

// 复选框中间状态
const isIndeterminate = ref(false)

// 监听批量复选框事件
const toggleChange = (value: any) => {
    isIndeterminate.value = false
    diyFormListTableRef.value.toggleAllSelection()
}

const diyFormListTableRef = ref()

// 选中数据
const multipleSelection: any = ref([])

// 监听表格单行选中
const handleSelectionChange = (val: []) => {
    multipleSelection.value = val

    toggleCheckbox.value = false
    if (
        multipleSelection.value.length > 0 &&
        multipleSelection.value.length < diyFormTableData.data.length
    ) {
        isIndeterminate.value = true
    } else {
        isIndeterminate.value = false
    }

    if (multipleSelection.value.length == diyFormTableData.data.length) {
        toggleCheckbox.value = true
    }
}

// 批量删除
const batchDeleteForms = () => {
    if (multipleSelection.value.length == 0) {
        ElMessage({
            type: 'warning',
            message: `${t('batchEmptySelectedFormsTips')}`
        })
        return
    }

    ElMessageBox.confirm(t('batchFormsDeleteTips'), t('warning'), {
        confirmButtonText: t('confirm'),
        cancelButtonText: t('cancel'),
        type: 'warning'
    }).then(() => {
        if (repeat.value) return
        repeat.value = true

        const form_ids: any = []
        multipleSelection.value.forEach((item: any) => {
            form_ids.push(item.form_id)
        })

        deleteDiyForm({
            form_ids
        }).then(() => {
            loadDiyFormList()
            repeat.value = false
        }).catch(() => {
            repeat.value = false
        })
    })
}

// 跳转去预览
const toPreview = (data: any) => {
    const url = router.resolve({
        path: '/preview/wap',
        query: {
            page: '/app/pages/index/diy_form?form_id=' + data.form_id
        }
    })
    window.open(url.href)
}

const tabShareType = ref('wechat')
const sharePage = ref('')
const shareFormId = ref(0)
const shareFormData = reactive({
    wechat: {
        title: '',
        desc: '',
        url: ''
    },
    weapp: {
        title: '',
        url: ''
    }
})

const shareDialogVisible = ref(false)
const shareFormRules = computed(() => {
    return {}
})

const shareFormRef = ref<FormInstance>()
const openShare = async (row: any) => {
    shareFormId.value = row.form_id
    sharePage.value = row.title
    const share = row.share
    shareFormData.wechat = share.wechat
    shareFormData.weapp = share.weapp

    shareDialogVisible.value = true
}

const shareEvent = async (formEl: FormInstance | undefined) => {
    if (!formEl) return

    await formEl.validate(async (valid) => {
        if (valid) {
            editDiyFormShare({
                form_id: shareFormId.value,
                share: JSON.stringify(shareFormData)
            }).then(() => {
                loadDiyFormList()
                shareDialogVisible.value = false
            }).catch(() => {
            })
        }
    })
}

// 表单推广
const spreadPopupRef = ref(null)

const spreadEvent = (data: any) => {
    const pagePath = '/app/pages/index/diy_form'
    const paramsArr = [
        { name: 'form_id', value: data.form_id },
    ];
    const title = '表单推广'
    const folder = 'diy_form'
    spreadPopupRef.value?.show(pagePath, paramsArr, title, folder);
}

// 表单提交成功页弹出框
const formSubmitPopupRef: any = ref(null)

const submitConfigEvent = (data: any) => {
    formSubmitPopupRef.value.setFormData(data)
    formSubmitPopupRef.value.showDialog = true
}

// 表单填写设置弹出框
const formWritePopupRef: any = ref(null)

const writeConfigEvent = (data: any) => {
    formWritePopupRef.value.setFormData(data)
    formWritePopupRef.value.showDialog = true
}

/**
 * 表单填写记录明细导出
 */
const exportSureDialog = ref(null)
const flag = ref(false)
const handleExportClose = (val) => {
    flag.value = val
}

const diyFormDetailData: any = reactive({
    form_id: 0
})
const exportEvent = (data: any) => {
    diyFormDetailData.form_id = data.form_id
    flag.value = true
}

</script>

<style lang="scss" scoped>
.image-selection-container {
    width: 100%;
    display: flex;
    gap: 20px;
    justify-content: center;
}
.image-option {
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 300px;
    border: 2px solid transparent;
    border-radius: 10px;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
}
.image-option.selected {
    border-color: var(--el-color-primary);
    box-shadow: 0 0 10px var(--el-color-primary-light-9);
}
.option-image {
    width: 100%;
    height: auto;
    object-fit: cover;
}
.option-title {
    margin-top: 10px;
    font-size: 14px;
    color: #303133;
    font-weight: bold;
    text-align: center;
}
</style>

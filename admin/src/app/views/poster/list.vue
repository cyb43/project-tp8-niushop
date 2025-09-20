<template>
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">

            <div class="flex justify-between items-center">
                <span class="text-page-title">{{ pageName }}</span>
                <el-button type="primary" class="w-[100px]" @click="dialogVisible = true">{{ t('添加海报') }}</el-button>
            </div>
            <div class="mt-[20px]" v-if="!isImagick">
                <el-alert type="warning" show-icon :closable="false">
                    <template #title>
                        <span class="!text-[14px]">检测到PHP未安装ImageMagick扩展，需安装后才能使用海报功能</span>
                    </template>
                </el-alert>
            </div>
            <el-card class="box-card !border-none my-[10px] table-search-wrap" shadow="never">
                <el-form :inline="true" :model="posterTableData.searchParam" ref="searchFormDiyPosterRef">
                    <el-form-item :label="t('posterName')" prop="name">
                        <el-input v-model.trim="posterTableData.searchParam.name" :placeholder="t('posterNamePlaceholder')" />
                    </el-form-item>
                    <el-form-item :label="t('posterType')" prop="type">
                        <el-select v-model="posterTableData.searchParam.type" :placeholder="t('posterTypePlaceholder')">
                            <el-option :label="t('all')" value="" />
                            <el-option v-for="item in posterType" :label="item.name" :value="item.type" :key="item.type"/>
                        </el-select>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="loadPosterPageList()">{{ t('search') }}</el-button>
                        <el-button @click="resetForm(searchFormDiyPosterRef)">{{ t('reset') }}</el-button>
                    </el-form-item>
                </el-form>
            </el-card>

            <el-table :data="posterTableData.data" size="large" v-loading="posterTableData.loading">

                <template #empty>
                    <span>{{ !posterTableData.loading ? t('emptyData') : '' }}</span>
                </template>

                <el-table-column prop="name" :label="t('posterName')" min-width="120" />
                <el-table-column prop="type_name" :label="t('posterType')" min-width="80" />
                <el-table-column :label="t('status')" min-width="80">
                    <template #default="{ row }">
                        <el-tag type="success" v-if="row.status == 1" @click="modifyPosterStatusFn(row.id, 0)" class="cursor-pointer">{{ t('启用') }}</el-tag>
                        <el-tag type="info" v-else @click="modifyPosterStatusFn(row.id, 1)" class="cursor-pointer">{{ t('未启用') }}</el-tag>
                    </template>
                </el-table-column>
                <el-table-column :label="t('isDefault')" min-width="80">
                    <template #default="{ row }">
                        <span v-if="row.is_default == 1" class="text-primary">{{ t('defaultPoster') }}</span>
                        <span v-else>{{ t('noDefault') }}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="update_time" :label="t('updateTime')" min-width="120" />

                <el-table-column :label="t('operation')" fixed="right" align="right" min-width="160">
                    <template #default="{ row }">
                        <el-button type="primary" link @click="previewPoster(row)">{{ t('preview') }}</el-button>
                        <el-button v-if="row.is_default == 0" type="primary" link @click="modifyPosterDefaultFn(row.id)">{{ t('modifyDefault') }}</el-button>
                        <el-button type="primary" link @click="editEvent(row)">{{ t('edit') }}</el-button>
                        <el-button v-if="row.is_default == 0" type="primary" link @click="deleteEvent(row.id)">{{ t('delete') }}</el-button>
                    </template>
                </el-table-column>

            </el-table>
            <div class="mt-[16px] flex justify-end">
                <el-pagination v-model:current-page="posterTableData.page" v-model:page-size="posterTableData.limit"
                    layout="total, sizes, prev, pager, next, jumper" :total="posterTableData.total"
                    @size-change="loadPosterPageList()" @current-change="loadPosterPageList" />
            </div>

        </el-card>

        <!--添加海报-->
        <el-dialog v-model="dialogVisible" :title="t('addPosterTitle')" width="350px" destroy-on-close="true">

            <el-form :model="formData" label-width="90px" ref="formRef" :rules="formRules">
                <el-form-item :label="t('posterName')" prop="name">
                    <el-input v-model.trim="formData.name" :placeholder="t('posterNamePlaceholder')" clearable maxlength="12" show-word-limit class="w-full" />
                </el-form-item>
                <el-form-item :label="t('posterType')" prop="type">
                    <el-select v-model="formData.type" :placeholder="t('posterTypePlaceholder')" class="!w-full">
                        <el-option v-for="item in posterType" :label="item.name" :value="item.type" :key="item.type"/>
                    </el-select>
                </el-form-item>
            </el-form>

            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="dialogVisible = false">{{ t('cancel') }}</el-button>
                    <el-button type="primary" @click="addEvent(formRef)">{{ t('confirm') }}</el-button>
                </span>
            </template>
        </el-dialog>

        <!--预览海报-->
        <el-dialog v-model="previewDialogVisible" :title="t('previewDialogTitle')" width="400px" height="640px">

            <div v-if="previewPosterUrl">
                <img :src="img(previewPosterUrl)" class="w-[360px] h-[640px]" />
            </div>
        </el-dialog>

    </div>
</template>

<script lang="ts" setup>
import { reactive, ref, computed } from 'vue'
import { t } from '@/lang'
import { ElMessageBox, FormInstance } from 'element-plus'
import { useRoute, useRouter } from 'vue-router'
import { getPosterPageList, getPosterType, modifyPosterStatus, modifyPosterDefault, deletePoster, getPreviewPoster ,checkImagick} from '@/app/api/poster'
import { img, setTablePageStorage, getTablePageStorage } from '@/utils/common'

const router = useRouter()
const route = useRoute()
const pageName = route.meta.title

const posterType: any = reactive({}) // 自定义海报类型

// 添加自定义海报
const formData = reactive({
    name: '',
    type: ''
})

// 表单验证规则
const formRules = computed(() => {
    return {
        name: [
            { required: true, message: t('posterNamePlaceholder'), trigger: 'blur' }
        ],
        type: [
            { required: true, message: t('posterTypePlaceholder'), trigger: 'blur' }
        ]
    }
})

const formRef = ref<FormInstance>()
const dialogVisible = ref(false)
const addEvent = async (formEl: FormInstance | undefined) => {
    if (!formEl) return

    await formEl.validate(async (valid) => {
        if (valid) {
            const query = { type: formData.type, name: formData.name }
            const url = router.resolve({
                path: '/poster/edit',
                query
            })
            window.open(url.href)
            dialogVisible.value = false
            formData.name = ''
            formData.type = ''
        }
    })
}

// 获取自定义海报类型
const loadPosterType = () => {
    getPosterType({}).then((res:any) => {
        for (const key in posterType) {
            delete posterType[key]
        }

        for (const key in res.data) {
            posterType[key] = res.data[key]
        }
    })
}

loadPosterType()

const posterTableData: any = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        name: '',
        type: ''
    }
})

const searchFormDiyPosterRef = ref<FormInstance>()

// 获取自定义海报列表
const loadPosterPageList = (page: number = 1) => {
    posterTableData.loading = true
    posterTableData.page = page

    getPosterPageList({
        page: posterTableData.page,
        limit: posterTableData.limit,
        ...posterTableData.searchParam
    }).then(res => {
        posterTableData.loading = false
        posterTableData.data = res.data.data
        posterTableData.total = res.data.total
        setTablePageStorage(posterTableData.page, posterTableData.limit, posterTableData.searchParam)
    }).catch(() => {
        posterTableData.loading = false
    })
}

loadPosterPageList(getTablePageStorage(posterTableData.searchParam).page)

// 编辑自定义海报
const editEvent = (data: any) => {
    const url = router.resolve({
        path: '/poster/edit',
        query: { id: data.id }
    })
    window.open(url.href)
}

const isRepeat = ref(false)

// 修改海报启用状态
const modifyPosterStatusFn = (id:any, status:any) => {
    if (isRepeat.value) return
    isRepeat.value = true

    modifyPosterStatus({
        id, status
    }).then((res) => {
        loadPosterPageList()
        isRepeat.value = false
    })
}

// 将自定义海报修改为默认海报
const modifyPosterDefaultFn = (id:any) => {
    if (isRepeat.value) return
    isRepeat.value = true
    modifyPosterDefault({
        id
    }).then((res) => {
        loadPosterPageList()
        isRepeat.value = false
    })
}

// 删除自定义海报
const deleteEvent = (id: number) => {
    ElMessageBox.confirm(t('diyPosterDeleteTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        if (isRepeat.value) return
        isRepeat.value = true
        deletePoster(id).then(() => {
            loadPosterPageList()
            isRepeat.value = false
        }).catch(() => {
            isRepeat.value = false
        })
    })
}

const previewDialogVisible = ref(false)
const previewPosterUrl = ref('')

// 预览海报
const previewPoster = (data: any) => {
    if (isRepeat.value) return
    isRepeat.value = true

    getPreviewPoster({
        id: data.id,
        type: data.type
    }).then((res:any) => {
        if (res.data) {
            previewPosterUrl.value = res.data
            previewDialogVisible.value = true
        }
        isRepeat.value = false
    })
}

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    loadPosterPageList()
}
const isImagick = ref(false)
// 判断是否安装imagemagick扩展
const checkImagickFn = () => {
    checkImagick().then((res:any) => {
        console.log(res)
        isImagick.value = res.data
        
    })
}
checkImagickFn()

</script>

<style lang="scss" scoped></style>

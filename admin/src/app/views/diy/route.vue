<template>
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">

            <div class="flex justify-between items-center">
                <span class="text-page-title">{{pageName}}</span>
            </div>

            <el-card class="box-card !border-none my-[10px] table-search-wrap" shadow="never">
                <el-form :inline="true" :model="diyRouteTableData.searchParam" ref="searchFormDiyRouteRef">
                    <el-form-item :label="t('title')" prop="title">
                        <el-input v-model.trim="diyRouteTableData.searchParam.title" :placeholder="t('titlePlaceholder')" />
                    </el-form-item>
                    <el-form-item :label="t('forAddon')" prop="addon_name">
                        <el-select v-model="diyRouteTableData.searchParam.addon_name" :placeholder="t('forAddonPlaceholder')">
                            <el-option :label="t('all')" value="" />
                            <el-option v-for="(item, key) in apps" :label="item.title" :value="key" :key="key"/>
                        </el-select>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="loadDiyRouteList()">{{ t('search') }}</el-button>
                        <el-button @click="resetForm(searchFormDiyRouteRef)">{{ t('reset') }}</el-button>
                    </el-form-item>
                </el-form>
            </el-card>

            <el-table :data="diyRouteTableData.data" size="large" v-loading="diyRouteTableData.loading">
                <template #empty>
                    <span>{{ !diyRouteTableData.loading ? t('emptyData') : '' }}</span>
                </template>

                <el-table-column prop="title" :label="t('title')" min-width="70" />
                <el-table-column prop="addon_title" :label="t('forAddon')" min-width="70">
                    <template #default="{ row }">
                        <span>{{ row.addon_info.title }}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="page" :label="t('wapUrl')" min-width="230">
                    <template #default="{ row }">
                        <span class="mr-[10px]">{{ wapDomain + row.page }}</span>
                        <el-button type="primary" link @click="copyEvent(wapDomain + row.page)">{{ t('copy') }}</el-button>
                    </template>
                </el-table-column>
                <el-table-column prop="page" :label="t('weappUrl')" min-width="120">
                    <template #default="{ row }">
                        <span class="mr-[10px]">{{ row.page }}</span>
                        <el-button type="primary" link @click="copyEvent(row.page)">{{ t('copy') }}</el-button>
                    </template>
                </el-table-column>
                <el-table-column :label="t('share')" fixed="right"  align="right" min-width="40">
                    <template #default="{ row }">
                        <el-button v-if="row.is_share == 1" type="primary" link @click="openShare(row)">{{ t('shareSet') }}</el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div class="mt-[16px] flex justify-end">
                <el-pagination v-model:current-page="diyRouteTableData.page" v-model:page-size="diyRouteTableData.limit" layout="total, sizes, prev, pager, next, jumper" :total="diyRouteTableData.total" @size-change="getDiyRouteListFn" @current-change="loadDiyRouteList" />
            </div>
        </el-card>

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

    </div>
</template>

<script lang="ts" setup>
import { reactive, ref, watch, computed } from 'vue'
import { t } from '@/lang'
import { getDiyRouteAppList, getDiyTemplate, getDiyRouteList, getDiyRouteInfo, editDiyRouteShare } from '@/app/api/diy'
import { ElMessage, FormInstance } from 'element-plus'
import { useRoute, useRouter } from 'vue-router'
import { useClipboard } from '@vueuse/core'
import { getUrl } from '@/app/api/sys'
import { cloneDeep } from 'lodash-es'

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title

const pageTemplate: any = reactive({})

const formRef = ref<FormInstance>()
const dialogVisible = ref(false)

const diyRouteTableData = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        title: '',
        addon_name: ''
    }
})

const diyRouteList: any = ref([])

const wapDomain = ref('')
const getDomain = async () => {
    wapDomain.value = (await getUrl()).data.wap_url
}

getDomain()

const apps: any = reactive({}) // 应用插件列表

getDiyRouteAppList().then(res => {
    if (res.data) {
        for (const key in res.data) {
            apps[key] = res.data[key]
        }
    }
})

const getDiyRouteListFn = () => {
    getDiyRouteList({}).then(res => {
        diyRouteTableData.loading = false
        diyRouteList.value = cloneDeep(res.data)
        loadDiyRouteList(diyRouteTableData.page)
    }).catch(() => {
        diyRouteTableData.loading = false
    })
}

getDiyRouteListFn()

/**
 * 获取自定义路由列表
 */
const loadDiyRouteList = (page: number = 1) => {
    diyRouteTableData.page = page

    const tempData = cloneDeep(diyRouteList.value)
    const data: any = []

    // 筛选条件
    for (let i = 0; i < tempData.length; i++) {
        let isAdd = true
        if (diyRouteTableData.searchParam.title && tempData[i].title.indexOf(diyRouteTableData.searchParam.title) == -1) {
            isAdd = false
        }

        if (diyRouteTableData.searchParam.addon_name && tempData[i].addon_info && tempData[i].addon_info.key != diyRouteTableData.searchParam.addon_name) {
            isAdd = false
        }

        if (isAdd) {
            data.push(tempData[i])
        }
    }

    diyRouteTableData.total = data.length
    const len = Math.ceil(data.length / diyRouteTableData.limit)
    const dataGather = []

    for (let i = 0; i < len; i++) {
        dataGather[i] = data.splice(0, diyRouteTableData.limit)
    }
    diyRouteTableData.data = dataGather[diyRouteTableData.page - 1]
}

// 获取自定义页面模板
getDiyTemplate({}).then(res => {
    for (const key in res.data) {
        pageTemplate[key] = res.data[key]
    }
})

const searchFormDiyRouteRef = ref<FormInstance>()

/**
 * 复制
 */
const { copy, isSupported, copied } = useClipboard()
const copyEvent = (text: string) => {
    if (!isSupported.value) {
        ElMessage({
            message: t('notSupportCopy'),
            type: 'warning'
        })
    }
    copy(text)
}

watch(copied, () => {
    if (copied.value) {
        ElMessage({
            message: t('copySuccess'),
            type: 'success'
        })
    }
})

const tabShareType = ref('wechat')
const sharePage = ref('')
const shareFormId = ref(0)
const diyRouteData = reactive({
    title: '',
    name: '',
    page: '',
    is_share: 0,
    sort: 0
})
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
    // 基础页面
    const info = (await getDiyRouteInfo({
        name: row.name
    })).data

    if (info.title) {
        row.id = info.id
        row.title = info.title
        row.name = info.name
        row.page = info.page
        row.is_share = info.is_share
        row.sort = info.sort
        row.share = info.share
    }

    diyRouteData.title = row.title
    diyRouteData.name = row.name
    diyRouteData.page = row.page
    diyRouteData.is_share = row.is_share
    diyRouteData.sort = row.sort

    shareFormId.value = row.id
    sharePage.value = row.title
    const share = row.share
        ? JSON.parse(row.share)
        : {
            wechat: { title: '', desc: '', url: '' },
            weapp: { title: '', url: '' }
        }
    if (share) {
        shareFormData.wechat = share.wechat
        shareFormData.weapp = share.weapp
    }

    shareDialogVisible.value = true
}

const shareEvent = async (formEl: FormInstance | undefined) => {
    if (!formEl) return

    await formEl.validate(async (valid) => {
        if (valid) {
            const save = editDiyRouteShare
            save({
                id: shareFormId.value,
                share: JSON.stringify(shareFormData),
                ...diyRouteData
            }).then(() => {
                getDiyRouteListFn()
                shareDialogVisible.value = false
            }).catch(() => {
            })
        }
    })
}

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    getDiyRouteListFn()
}
</script>

<style lang="scss" scoped></style>

<template>
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">

            <div class="flex justify-between items-center">
                <span class="text-page-title">{{pageName}}</span>
                <el-button type="primary" @click="addEvent">
                    {{ t('addServe') }}
                </el-button>
            </div>

            <el-card class="box-card !border-none my-[10px] table-search-wrap" shadow="never">
                <el-form :inline="true" :model="serveTable.searchParam" ref="searchFormRef">
                    <el-form-item :label="t('serviceName')" prop="service_name">
                        <el-input v-model.trim="serveTable.searchParam.service_name" :placeholder="t('serviceNamePlaceholder')" />
                    </el-form-item>

                    <el-form-item>
                        <el-button type="primary" @click="loadServeList()">{{ t('search') }}</el-button>
                        <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
                    </el-form-item>
                </el-form>
            </el-card>

            <div class="mt-[10px]">
                <el-table :data="serveTable.data" size="large" v-loading="serveTable.loading">
                    <template #empty>
                        <span>{{ !serveTable.loading ? t('emptyData') : '' }}</span>
                    </template>
                    <el-table-column prop="service_name" :label="t('serviceName')" min-width="120" />
<!--					<el-table-column :label="t('image')" min-width="120">-->
<!--						<template #default="{ row }">-->
<!--							<div class="h-[50px]">-->
<!--							    <el-image class="w-[50px] h-[50px] " :src="img(row.image)" fit="contain">-->
<!--							        <template #error>-->
<!--							            <div class="image-slot">-->
<!--							                &lt;!&ndash; <img class="w-[30px] h-[30px]" src="@/addon/shop/assets/category_default.png" /> &ndash;&gt;-->
<!--							            </div>-->
<!--							        </template>-->
<!--							    </el-image>-->
<!--							</div>-->
<!--						</template>-->
<!--					</el-table-column>-->
                    <el-table-column prop="desc" :label="t('desc')" min-width="120" />
                    <el-table-column :label="t('operation')" fixed="right" align="right" min-width="120">
                       <template #default="{ row }">
                           <el-button type="primary" link @click="editEvent(row)">{{ t('edit') }}</el-button>
                           <el-button type="primary" link @click="deleteEvent(row.service_id)">{{ t('delete') }}</el-button>
                       </template>
                    </el-table-column>

                </el-table>
                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="serveTable.page" v-model:page-size="serveTable.limit"
                        layout="total, sizes, prev, pager, next, jumper" :total="serveTable.total"
                        @size-change="loadServeList()" @current-change="loadServeList" />
                </div>
            </div>

            <service-edit ref="editServeDialog" @complete="loadServeList" />
        </el-card>
    </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import { getServePageList, deleteServe } from '@/addon/shop/api/goods'
import { ElMessageBox, FormInstance } from 'element-plus'
import ServiceEdit from '@/addon/shop/views/goods/components/service-edit.vue'
import { useRoute } from 'vue-router'
import { setTablePageStorage, getTablePageStorage } from '@/utils/common'

const route = useRoute()
const pageName = route.meta.title

const serveTable = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        service_name: ''
    }
})

const searchFormRef = ref<FormInstance>()

/**
 * 获取商品服务列表
 */
const loadServeList = (page: number = 1) => {
    serveTable.loading = true
    serveTable.page = page

    getServePageList({
        page: serveTable.page,
        limit: serveTable.limit,
        ...serveTable.searchParam
    }).then(res => {
        serveTable.loading = false
        serveTable.data = res.data.data
        serveTable.total = res.data.total
        setTablePageStorage(serveTable.page, serveTable.limit, serveTable.searchParam)
    }).catch(() => {
        serveTable.loading = false
    })
}
loadServeList(getTablePageStorage(serveTable.searchParam).page)

const editServeDialog: Record<string, any> | null = ref(null)

/**
 * 添加商品服务
 */
const addEvent = () => {
    editServeDialog.value.setFormData()
    editServeDialog.value.showDialog = true
}

/**
 * 编辑商品服务
 * @param data
 */
const editEvent = (data: any) => {
    editServeDialog.value.setFormData(data)
    editServeDialog.value.showDialog = true
}

/**
 * 删除商品服务
 */
const deleteEvent = (id: number) => {
    ElMessageBox.confirm(t('serveDeleteTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        deleteServe(id).then(() => {
            loadServeList()
        }).catch(() => {
        })
    })
}

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    loadServeList()
}
</script>

<style lang="scss" scoped>
</style>

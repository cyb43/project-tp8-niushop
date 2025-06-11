<template>
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">
            <el-page-header :content="pageName" :icon="ArrowLeft" @back="$router.back()" />
        </el-card>

        <el-card class="box-card mt-[15px] !border-none" shadow="never">
            <el-card class="box-card !border-none my-[10px] table-search-wrap" shadow="never">
                <div class="flex justify-between items-center">
                    <el-form :inline="true" :model="storeTable.searchParam" ref="searchFormRef">
                        <el-form-item :label="t('storeName')" prop="store_name">
                            <el-input v-model.trim="storeTable.searchParam.store_name" :placeholder="t('storeNamePlaceholder')" />
                        </el-form-item>
                        <el-form-item>
                            <el-button type="primary" @click="loadStoreList()">{{ t('search') }}</el-button>
                            <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
                        </el-form-item>
                    </el-form>
                    <el-button type="primary" @click="addEvent">
                        {{ t('addStore') }}
                    </el-button>
                </div>
            </el-card>

            <div class="mt-[10px]">
                <el-table :data="storeTable.data" size="large" v-loading="storeTable.loading">
                    <template #empty>
                        <span>{{ !storeTable.loading ? t('emptyData') : '' }}</span>
                    </template>
                    <el-table-column :label="t('storeInfo')" min-width="170" align="left">
                        <template #default="{ row }">
                            <div class="h-[50px] flex items-center">
                                <el-image class="w-[50px] h-[50px] " :src="img(row.store_logo)" fit="contain">
                                    <template #error>
                                        <div class="image-slot">
                                            <img class="w-[50px] h-[50px]" src="@/addon/shop/assets/store_default.png" />
                                        </div>
                                    </template>
                                </el-image>
                                <p class="ml-[10px] text-[14px]">{{ row.store_name }}</p>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="store_mobile" :label="t('storeMobile')" min-width="120" />
                    <el-table-column prop="full_address" :label="t('fullAddress')" min-width="180" />
                    <el-table-column prop="trade_time" :label="t('tradeTime')" min-width="120" />
                    <el-table-column :label="t('createTime')" min-width="120">
                        <template #default="{ row }">
                            {{ row.create_time || '' }}
                        </template>
                    </el-table-column>

                    <el-table-column :label="t('operation')" fixed="right" align="right" min-width="120">
                        <template #default="{ row }">
                            <el-button type="primary" link @click="editEvent(row)">{{ t('edit') }}</el-button>
                            <el-button type="primary" link @click="deleteEvent(row.store_id)">{{ t('delete') }}</el-button>
                        </template>
                    </el-table-column>

                </el-table>
                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="storeTable.page" v-model:page-size="storeTable.limit"
                        layout="total, sizes, prev, pager, next, jumper" :total="storeTable.total"
                        @size-change="loadStoreList()" @current-change="loadStoreList" />
                </div>
            </div>

        </el-card>
    </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import { getStoreList, deleteStore } from '@/addon/shop/api/delivery'
import { img } from '@/utils/common'
import { ElMessageBox, FormInstance } from 'element-plus'
import { ArrowLeft } from '@element-plus/icons-vue'
import { useRouter, useRoute } from 'vue-router'
import { setTablePageStorage,getTablePageStorage } from "@/utils/common";

const route = useRoute()
const pageName = route.meta.title

const storeTable = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        store_name: '',
        create_time: ''
    }
})

const searchFormRef = ref<FormInstance>()

/**
 * 获取自提门店列表
 */
const loadStoreList = (page: number = 1) => {
    storeTable.loading = true
    storeTable.page = page

    getStoreList({
        page: storeTable.page,
        limit: storeTable.limit,
        ...storeTable.searchParam
    }).then(res => {
        storeTable.loading = false
        storeTable.data = res.data.data
        storeTable.total = res.data.total
        setTablePageStorage(storeTable.page, storeTable.limit, storeTable.searchParam);
    }).catch(() => {
        storeTable.loading = false
    })
}
loadStoreList(getTablePageStorage(storeTable.searchParam).page);

const router = useRouter()

/**
 * 添加自提门店
 */
const addEvent = () => {
    router.push('/shop/order/delivery/store/edit')
}

/**
 * 编辑自提门店
 * @param data
 */
const editEvent = (data: any) => {
    router.push('/shop/order/delivery/store/edit?id=' + data.store_id)
}

/**
 * 删除自提门店
 */
const deleteEvent = (id: number) => {
    ElMessageBox.confirm(t('storeDeleteTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        deleteStore(id).then(() => {
            loadStoreList()
        }).catch(() => {
        })
    })
}

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    loadStoreList()
}
</script>

<style lang="scss" scoped>
</style>

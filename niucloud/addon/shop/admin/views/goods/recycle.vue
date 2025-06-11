<template>
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">

            <div class="flex justify-between items-center">
                <span class="text-page-title">{{ pageName }}</span>
            </div>

            <el-card class="box-card !border-none my-[10px] table-search-wrap" shadow="never">
                <el-form :inline="true" :model="goodsTable.searchParam" ref="searchFormRef">
                    <el-form-item :label="t('goodsName')" prop="goods_name">
                        <el-input v-model.trim="goodsTable.searchParam.goods_name" :placeholder="t('goodsNamePlaceholder')" maxlength="60" />
                    </el-form-item>
                    <el-form-item :label="t('goodsCategory')" prop="goods_category">
                        <el-cascader v-model="goodsTable.searchParam.goods_category" :options="goodsCategoryOptions" :placeholder="t('goodsCategoryPlaceholder')" clearable :props="{ value: 'value', label: 'label', emitPath:false }"  />
                    </el-form-item>
                    <el-form-item :label="t('goodsType')" prop="goods_type">
                        <el-select v-model="goodsTable.searchParam.goods_type" :placeholder="t('goodsTypePlaceholder')" clearable>
                            <el-option v-for="item in goodsType" :key="item.type" :label="item.name" :value="item.type" />
                        </el-select>
                    </el-form-item>

                    <el-form-item>
                        <el-button type="primary" @click="loadGoodsList()">{{ t('search') }}</el-button>
                        <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
                    </el-form-item>
                </el-form>
            </el-card>

            <div class="mt-[10px]">

                <div class="mb-[10px] flex items-center">
                    <el-checkbox v-model="toggleCheckbox" size="large" class="px-[14px]" @change="toggleChange" :indeterminate="isIndeterminate" />
                    <el-button @click="batchRecycle" size="small">{{ t('batchRecycle') }}</el-button>
                </div>

                <el-table :data="goodsTable.data" size="large" v-loading="goodsTable.loading" ref="goodsListTableRef" @sort-change="sortChange" @selection-change="handleSelectionChange">
                    <template #empty>
                        <span>{{ !goodsTable.loading ? t('emptyData') : '' }}</span>
                    </template>
                    <el-table-column type="selection" width="55" />

                    <el-table-column prop="goods_id" :label="t('goodsInfo')" min-width="300">
                        <template #default="{ row }">
                            <div class="flex items-center cursor-pointer">
                                <div class="min-w-[70px] h-[70px] flex items-center justify-center">
                                    <img class="max-w-[70px] max-h-[70px]" :src="img(row.goods_cover_thumb_small)" />
                                </div>
                                <div class="ml-2">
                                    <span :title="row.goods_name" class="multi-hidden">{{ row.goods_name }}</span>
                                    <span class="text-primary text-[12px]">{{ row.goods_type_name }}</span>
                                </div>
                            </div>
                        </template>
                    </el-table-column>

                    <el-table-column prop="price" :label="t('price')" min-width="120" align="right" sortable="custom">
                        <template #default="{ row }">
                            <div>￥{{ row.goodsSku.price }}</div>
                        </template>
                    </el-table-column>

                    <el-table-column prop="stock" :label="t('stock')" min-width="120" sortable="custom" />
                    <el-table-column prop="sale_num" :label="t('saleNum')" min-width="100" sortable="custom" />
                    <el-table-column prop="status" :label="t('status')" min-width="100">
                        <template #default="{ row }">
                            <div v-if="row.status == 1">{{ t('statusOn') }}</div>
                            <div v-if="row.status == 0">{{ t('statusOff') }}</div>
                        </template>
                    </el-table-column>

                    <el-table-column prop="create_time" :label="t('createTime')" min-width="150" sortable="custom">
                        <template #default="{ row }">
                            <div>{{ row.create_time }}</div>
                        </template>
                    </el-table-column>

                    <el-table-column :label="t('operation')" fixed="right" align="right" min-width="120">
                        <template #default="{ row }">
                            <el-button type="primary" link @click="recycleEvent(row)">{{ t('recycle') }}</el-button>
                        </template>
                    </el-table-column>

                </el-table>
                <div class="mt-[16px] flex justify-end">
                    <!-- <div class="flex items-center flex-1">
                        <el-checkbox v-model="toggleCheckbox" size="large" class="px-[14px]" @change="toggleChange" :indeterminate="isIndeterminate" />
                        <el-button @click="batchRecycle" size="small">{{ t('batchRecycle') }}</el-button>
                    </div> -->

                    <el-pagination v-model:current-page="goodsTable.page" v-model:page-size="goodsTable.limit"
                        layout="total, sizes, prev, pager, next, jumper" :total="goodsTable.total"
                        @size-change="loadGoodsList()" @current-change="loadGoodsList" />
                </div>
            </div>

        </el-card>
    </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import { img ,setTablePageStorage,getTablePageStorage} from '@/utils/common'
import { ElMessageBox, ElMessage, FormInstance } from 'element-plus'
import { useRoute } from 'vue-router'
import { cloneDeep } from 'lodash-es'

import {
    getRecycleGoodsPageList,
    getCategoryTree,
    getGoodsType,
    recycleGoods
} from '@/addon/shop/api/goods'

const route = useRoute()
const pageName = route.meta.title
const repeat = ref(false)

const goodsTable = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        goods_name: '',
        goods_category: [],
        goods_type: '',
        order: '',
        sort: ''
    }
})

const searchFormRef = ref()

// 商品分类
const goodsCategoryOptions: any = reactive([])

// 商品类型
const goodsType: any = reactive([])

// 初始化数据
const initData = () => {
    // 查询商品分类树结构
    getCategoryTree().then((res) => {
        const data = res.data
        if (data) {
            const goodsCategoryTree: any = []
            data.forEach((item: any) => {
                const children: any = []
                if (item.child_list) {
                    item.child_list.forEach((childItem: any) => {
                        children.push({
                            value: childItem.category_id,
                            label: childItem.category_name
                        })
                    })
                }
                goodsCategoryTree.push({
                    value: item.category_id,
                    label: item.category_name,
                    children
                })
            })
            goodsCategoryOptions.splice(0, goodsCategoryOptions.length, ...goodsCategoryTree)
        }
    })
    // 商品类型
    getGoodsType().then((res) => {
        const data = res.data
        if (data) {
            for (const k in data) {
                goodsType.push(data[k])
            }
        }
    })
}

initData()

// 批量复选框
const toggleCheckbox = ref()

// 复选框中间状态
const isIndeterminate = ref(false)

// 监听批量复选框事件
const toggleChange = (value: any) => {
    isIndeterminate.value = false
    goodsListTableRef.value.toggleAllSelection()
}

const goodsListTableRef = ref()

// 选中数据
const multipleSelection: any = ref([])

// 监听表格单行选中
const handleSelectionChange = (val: []) => {
    multipleSelection.value = val

    toggleCheckbox.value = false
    if (multipleSelection.value.length > 0 && multipleSelection.value.length < goodsTable.data.length) {
        isIndeterminate.value = true
    } else {
        isIndeterminate.value = false
    }

    if (multipleSelection.value.length == goodsTable.data.length && goodsTable.data.length && multipleSelection.value.length) {
        toggleCheckbox.value = true
    }
}

// 批量恢复商品
const batchRecycle = () => {
    if (multipleSelection.value.length == 0) {
        ElMessage({
            type: 'warning',
            message: `${t('batchEmptySelectedGoodsTips')}`
        })
        return
    }

    ElMessageBox.confirm(t('batchGoodsRecycleTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        if (repeat.value) return
        repeat.value = true

        const goodsIds: any = []
        multipleSelection.value.forEach((item: any) => {
            goodsIds.push(item.goods_id)
        })

        recycleGoods({
            goods_ids: goodsIds
        }).then(() => {
            loadGoodsList()
            toggleCheckbox.value = false
            repeat.value = false
        }).catch(() => {
            repeat.value = false
        })
    })
}

/**
 * 获取商品列表
 */
const loadGoodsList = (page: number = 1) => {
    goodsTable.loading = true
    goodsTable.page = page

    const searchData = cloneDeep(goodsTable.searchParam)

    getRecycleGoodsPageList({
        page: goodsTable.page,
        limit: goodsTable.limit,
        ...searchData
    }).then(res => {
        goodsTable.loading = false
        goodsTable.data = res.data.data
        goodsTable.total = res.data.total
        setTablePageStorage(goodsTable.page, goodsTable.limit, goodsTable.searchParam)
    }).catch(() => {
        goodsTable.loading = false
    })
}

loadGoodsList(getTablePageStorage(goodsTable.searchParam).page)

// 恢复商品
const recycleEvent = (data: any) => {
    ElMessageBox.confirm(t('goodsRecycleTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }
    ).then(() => {
        if (repeat.value) return
        repeat.value = true

        recycleGoods({
            goods_ids: data.goods_id
        }).then((res: any) => {
            if (res.code == 1) {
                loadGoodsList()
            }
            repeat.value = false
        }).catch(() => {
            repeat.value = false
        })
    })
}

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()

    loadGoodsList()
}

// 监听排序
const sortChange = (event: any) => {
    let sort = ''
    if (event.order == 'ascending') {
        sort = 'asc'
    } else if (event.order == 'descending') {
        sort = 'desc'
    }
    if (sort) {
        goodsTable.searchParam.order = event.prop
        goodsTable.searchParam.sort = sort
    }
    loadGoodsList()
}

</script>

<style lang="scss" scoped>
/* 多行超出隐藏 */
.multi-hidden {
    word-break: break-all;
    text-overflow: ellipsis;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
</style>
<style>
.recyle .el-cascader-panel .el-radio{
    width: 100% !important ;
    height: 100% !important;
    z-index: 10;
    position: absolute;
    top: 0px;
    right: 0px;
}

.recyle .el-cascader-panel .el-radio__input{
    margin-left: 20px;
}

.recyle .el-cascader-panel .el-cascader-node__label {
    margin-left: 12px;
}
</style>

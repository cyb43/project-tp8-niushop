<template>
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">

            <div class="flex justify-between items-center">
                <span class="text-lg">{{pageName}}</span>
                <el-button type="primary" @click="addEvent">{{ t('addShopGoodsAttr') }}</el-button>
            </div>

            <el-card class="box-card !border-none my-[10px] table-search-wrap" shadow="never">
                <el-form :inline="true" :model="goodsAttrTable.searchParam" ref="searchFormRef">
                    <el-form-item :label="t('attrName')" prop="attr_name">
                        <el-input v-model.trim="goodsAttrTable.searchParam.attr_name" :placeholder="t('attrNamePlaceholder')" />
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="loadShopGoodsAttrList()">{{ t('search') }}</el-button>
                        <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
                    </el-form-item>
                </el-form>
            </el-card>

            <div class="mt-[10px]">
                <el-table :data="goodsAttrTable.data" size="large" v-loading="goodsAttrTable.loading" @sort-change="sortChange">
                    <template #empty>
                        <span>{{ !goodsAttrTable.loading ? t('emptyData') : '' }}</span>
                    </template>

                    <el-table-column prop="attr_name" :label="t('attrName')" min-width="320" :show-overflow-tooltip="true"/>

                    <el-table-column prop="sort" :label="t('sort')" min-width="120" sortable="custom">
                        <template #default="{ row }">
                            <el-input v-model.trim="row.sort" class="!w-[100px]" maxlength="8" @blur="sortInputListener(row.sort, row)" />
                        </template>
                    </el-table-column>

                    <el-table-column :label="t('operation')" fixed="right" align="right" min-width="120">
                       <template #default="{ row }">
                           <el-button type="primary" link @click="manageEvent(row)">{{ t('manage') }}</el-button>
                           <el-button type="primary" link @click="editEvent(row)">{{ t('edit') }}</el-button>
                           <el-button type="primary" link @click="deleteEvent(row.attr_id)">{{ t('delete') }}</el-button>
                       </template>
                    </el-table-column>

                </el-table>
                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="goodsAttrTable.page" v-model:page-size="goodsAttrTable.limit"
                        layout="total, sizes, prev, pager, next, jumper" :total="goodsAttrTable.total"
                        @size-change="loadShopGoodsAttrList()" @current-change="loadShopGoodsAttrList" />
                </div>
            </div>

        </el-card>

        <el-dialog v-model="showDialog" :title="titleDialog" width="500px" :close-on-click-modal="false" :destroy-on-close="true">
            <el-form :model="formData" label-width="120px" ref="formRef" :rules="formRules" class="page-form" v-loading="loading">
                <el-form-item :label="t('attrName')" prop="attr_name">
                    <el-input v-model.trim="formData.attr_name" clearable :placeholder="t('attrNamePlaceholder')" class="input-width"  maxlength="20" />
                </el-form-item>

                <el-form-item :label="t('sort')" >
                    <el-input v-model.trim="formData.sort"  maxlength="8" show-word-limit clearable :placeholder="t('sortPlaceholder')" class="input-width" @keyup="filterNumber($event)"/>
                </el-form-item>

            </el-form>

            <template #footer>
            <span class="dialog-footer">
                <el-button @click="showDialog = false">{{ t('cancel') }}</el-button>
                <el-button type="primary" :loading="loading" @click="confirm(formRef)">{{ t('confirm') }}</el-button>
            </span>
            </template>
        </el-dialog>

    </div>
</template>

<script lang="ts" setup>
import { reactive, ref,computed } from 'vue'
import { t } from '@/lang'
import { ElMessage,ElMessageBox,FormInstance } from 'element-plus'
import { useRoute, useRouter } from 'vue-router'
import { debounce,filterNumber, setTablePageStorage,getTablePageStorage } from '@/utils/common'
import { getAttrPageList, addAttr, deleteAttr,modifyAttrSort,editAttr} from '@/addon/shop/api/goods'

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title;

const goodsAttrTable = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        attr_name: '',
        order: '',
        sort: ''
    }
})

const searchFormRef = ref<FormInstance>()

const showDialog = ref(false)
const loading = ref(false)
const titleDialog = ref('')

const formData = reactive({
    attr_id: 0,
    attr_name: '',
    sort: 0
})

const formRef = ref<FormInstance>()

// 表单验证规则
const formRules = computed(() => {
    return {
        attr_name: [
            { required: true, message: t('attrNamePlaceholder'), trigger: 'blur' }
        ]
    }
})

// 监听排序
const sortChange = (event: any) => {
    let sort = ''
    if (event.order == 'ascending') {
        sort = 'asc'
    } else if (event.order == 'descending') {
        sort = 'desc'
    }
    if (sort) {
        goodsAttrTable.searchParam.order = event.prop
        goodsAttrTable.searchParam.sort = sort
    }
    loadShopGoodsAttrList()
}

/**
 * 获取商品参数列表
 */
const loadShopGoodsAttrList = (page: number = 1) => {
    goodsAttrTable.loading = true
    goodsAttrTable.page = page

    getAttrPageList({
        page: goodsAttrTable.page,
        limit: goodsAttrTable.limit,
         ...goodsAttrTable.searchParam
    }).then(res => {
        goodsAttrTable.loading = false
        goodsAttrTable.data = res.data.data
        goodsAttrTable.total = res.data.total
        setTablePageStorage(goodsAttrTable.page, goodsAttrTable.limit, goodsAttrTable.searchParam)
    }).catch(() => {
        goodsAttrTable.loading = false
    })
}

loadShopGoodsAttrList(getTablePageStorage(goodsAttrTable.searchParam).page)

/**
 * 添加商品参数
 */
const addEvent = () => {
    formData.attr_id = 0;
    formData.attr_name = '';
    formData.sort = 0;
    titleDialog.value = t('addShopGoodsAttr');
    showDialog.value = true
}

// 编辑商品参数
const editEvent = (data:any)=>{
    formData.attr_id = data.attr_id;
    formData.attr_name = data.attr_name;
    formData.sort = data.sort;
    titleDialog.value = t('updateShopGoodsAttr');
    showDialog.value = true
}

/**
 * 确认
 * @param formEl
 */
const confirm = async (formEl: FormInstance | undefined) => {
    if (loading.value || !formEl) return
    const save = formData.attr_id ? editAttr : addAttr

    await formEl.validate(async (valid) => {
        if (valid) {
            loading.value = true
            save(formData).then(res => {
                loading.value = false
                showDialog.value = false
                loadShopGoodsAttrList()
            }).catch(err => {
                loading.value = false
            })
        }
    })
}

/**
 * 编辑商品参数
 * @param data
 */
const manageEvent = (data: any) => {
    router.push('/shop/goods/attr_edit?attr_id=' + data.attr_id)
}

/**
 * 删除商品参数
 */
const deleteEvent = (id: number) => {
    ElMessageBox.confirm(t('goodsAttrDeleteTips'), t('warning'),
        {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning',
        }
    ).then(() => {
        deleteAttr(id).then(() => {
            loadShopGoodsAttrList()
        }).catch(() => {
        })
    })
}

// 修改排序号
const sortInputListener = debounce((sort, row) => {
    if (isNaN(sort) || !/^\d{0,8}$/.test(sort)) {
        ElMessage({
            type: 'warning',
            message: `${ t('sortTips') }`
        })
        return
    }
    if (sort > 99999999) {
        row.sort = 99999999
    }
    modifyAttrSort({
        attr_id: row.attr_id,
        sort
    }).then((res) => {
    })
})

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    loadShopGoodsAttrList()
}
</script>

<style lang="scss" scoped>
</style>

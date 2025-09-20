<template>
    <div>
        <div @click="show">
            <slot>
                <el-input v-model="selectData.title" :placeholder="t('请选择跳转页面')" readonly class="link-input">
                    <template #suffix>
                        <div @click.stop="clear">
                            <el-icon v-if="selectData.name">
                                <Close />
                            </el-icon>
                            <el-icon v-else>
                                <ArrowRight />
                            </el-icon>
                        </div>
                    </template>
                </el-input>
            </slot>
        </div>
        <el-dialog v-model="showDialog" :title="t('页面选择')" width="850px" :destroy-on-close="true" :close-on-click-modal="false">

        <el-table :data="tableData.data" size="large" v-loading="tableData.loading" ref="timeListTableRef" max-height="400">
            <template #empty>
                <span>{{ !tableData.loading ? t('emptyData') : '' }}</span>
            </template>
            <el-table-column min-width="7%">
                <template #default="{ row }">
                    <el-checkbox v-model="row.checked" @change="handleCheckChange($event,row)" />
                </template>
            </el-table-column>
            <el-table-column prop="page_title" :label="t('页面名称')"  min-width="25%" />
            <el-table-column prop="type_name" :label="t('页面类型')"   min-width="25%" />
            <el-table-column prop="url" :label="t('页面路径')"   min-width="25%" />
        </el-table>
        <div class="mt-[16px] flex">
            <el-pagination v-model:current-page="tableData.page" v-model:page-size="tableData.limit"
                layout="total, sizes, prev, pager, next, jumper" :total="tableData.total"
                @size-change="loadList()" @current-change="loadList" />
        </div>
        <template #footer>
                <span class="dialog-footer">
                    <el-button @click="showDialog = false">{{ t('cancel') }}</el-button>
                    <el-button type="primary" @click="save">{{ t('confirm') }}</el-button>
                </span>
            </template>
        </el-dialog>
    </div>
    
</template>

<script lang="ts" setup>
import { t } from '@/lang'
import { ref, reactive, nextTick,computed } from 'vue'
import { FormInstance, ElMessage } from "element-plus";
import { getPageLink } from '@/app/api/diy'

const prop = defineProps({
    modelValue: {
        type: Object,
        default: () => {
            return {
                id: 0,
                name: '',
                parent: '',
                title: '',
                url: ''
            }
        }
    },
    ignore: {
        type: Array,
        default: []
    }
})

const clear = () => {
    selectData.value = {
        id: 0,
        name: '',
        parent: '',
        title: '',
        url: ''
    }
}

const emit = defineEmits(['update:modelValue', 'confirm', 'success'])

const selectData: any = computed({
    get() {
        return prop.modelValue
    },
    set(value) {
        emit('update:modelValue', value)
    }
})
const searchFormRef = ref<FormInstance>()

const tableData = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        name: '',
    }
})

const timeListTableRef = ref()
const showDialog = ref(false)
const show = () => {
    showDialog.value = true
    loadList()
}

/**
 * 获取商品列表
 */
const loadList = (page: number = 1) => {
    tableData.loading = true
    tableData.page = page
    getPageLink({
        page: tableData.page,
        limit: tableData.limit,
        ...tableData.searchParam
    }).then(res => {
        tableData.loading = false
        tableData.data = res.data.data
        tableData.data.forEach((item: any) => {
            item.checked = item.id == selectData.value.id

        })
        tableData.total = res.data.total
        setTimesSelected();
    }).catch(() => {
        tableData.loading = false
    })
}
loadList()
const handleCheckChange = (isSelect: any, row: any) => {
    if (isSelect) {
        selectData.value.id = row.id
    } else {
        selectData.value.id = 0 // 未选中，移除当前
    }
    setTimesSelected()
}

// // 表格设置选中状态
const setTimesSelected = () => {
    nextTick(() => {
        for (let i = 0; i < tableData.data.length; i++) {
            tableData.data[i].checked = false
            if (selectData.value.id == tableData.data[i].id) {
                tableData.data[i].checked = true
                Object.assign(selectData.value, tableData.data[i])
            }
        }
    })
}

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()

    loadList()
}

const save = () => {
    if (selectData.value.id == 0) {
        ElMessage({
            type: 'warning',
            message: `${ t('请选择页面') }`
        })
        return;
    }
   selectData.value ={
       id:selectData.value.id,
       name:selectData.value.name,
       parent:selectData.value.parent,
       title:selectData.value.title,
       url:selectData.value.url
   }
   showDialog.value = false;

}

defineExpose({
    show,
    showDialog,
})
</script>

<style lang="scss" scoped>
.form-item-wrap {
    margin-right: 10px !important;
    margin-bottom: 10px !important;

    &.last-child {
        margin-right: 0 !important;
    }
}
</style>

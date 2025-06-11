<template>
    <el-dialog v-model="showDialog" :title="t('goodsSelectPopupSelectGoodsDialog')" width="60%" :destroy-on-close="true">
        <el-form :inline="true" :model="tableData.searchParam" ref="searchFormRef">
            <el-form-item :label="t('goodsSelectPopupGoodsName')" prop="active_name">
                <el-input v-model.trim="tableData.searchParam.name" :placeholder="t('goodsSelectPopupGoodsNamePlaceholder')" />
            </el-form-item>
            <el-form-item :label="t('status')" prop='status'>
                <el-select v-model="tableData.searchParam.status" clearable :placeholder="t('goodsSelectPopupGoodsStatusPlaceholder')" class="input-item">
                    <el-option v-for="(item, key) in statusOption" :key="key" :label="item" :value="key"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="loadExchangeGoodsList()">{{ t('search') }}</el-button>
                <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
            </el-form-item>
        </el-form>
        <!-- 列表 -->
        <div class="mt-[10px]">
            <el-table :data="tableData.data" ref="tableRef" row-key="id" size="large" v-loading="tableData.loading" @selection-change="handleSelectionChange" max-height="500">
                <template #empty>
                    <span>{{ !tableData.loading ? t('emptyData') : '' }}</span>
                </template>
                <el-table-column type="selection" width="55" reserve-selection />
                <el-table-column :label="t('goodsSelectPopupGoodsInfo')" min-width="130">
                    <template #default="{ row }">
                        <div class="flex items-center cursor-pointer">
                            <div class="min-w-[60px] h-[60px] flex items-center justify-center">
                                <el-image v-if="row.image" class="w-[60px] h-[60px]" :src="img(row.image)" fit="contain">
                                    <template #error>
                                        <div class="image-slot">
                                            <img class="w-[60px] h-[60px]" src="@/addon/shop/assets/goods_default.png" />
                                        </div>
                                    </template>
                                </el-image>
                                <img v-else class="w-[70px] h-[60px]" src="@/addon/shop/assets/goods_default.png" fit="contain" />
                            </div>
                            <div class="ml-2">
                                <span :title="row.names" class="multi-hidden">{{ row.names }}</span>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column :label="t('goodsSelectPopupPrice')" min-width="130">
                    <template #default="{ row }">
                        <p v-if="row.point">{{ row.point }}{{ t('goodsSelectPopupGoodsPointUnit') }}</p>
                        <p v-if="row.price">{{ row.price }}{{ t('goodsSelectPopupGoodsPriceUnit') }}</p>
                    </template>
                </el-table-column>
                <el-table-column :label="t('goodsSelectPopupStock')" min-width="130" align="right">
                    <template #default="{ row }">
                        <span>{{ row.stock }}</span>
                    </template>
                </el-table-column>
            </el-table>
            <div class="mt-[16px] flex justify-end">
                <el-pagination v-model:current-page="tableData.page" v-model:page-size="tableData.limit"
                               layout="total, sizes, prev, pager, next, jumper" :total="tableData.total"
                               @size-change="loadExchangeGoodsList()" @current-change="loadExchangeGoodsList" />
            </div>
        </div>
        <template #footer>
          <span class="dialog-footer">
            <el-button @click="showDialog = false">{{ t("cancel") }}</el-button>
            <el-button type="primary" @click="save">{{ t("confirm") }}</el-button>
          </span>
        </template>
    </el-dialog>
</template>

<script lang="ts" setup>
import { t } from "@/lang";
import { img } from "@/utils/common";
import { getActiveExchangePageList, getActiveExchangeStatus } from "@/addon/shop/api/marketing";
import { ref, nextTick, reactive } from "vue";
import { FormInstance, ElMessage } from 'element-plus'

const showDialog = ref(false);
const tableRef = ref();
// 表单内容
const tableData = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: false,
    data: [],
    searchParam: {
        name: '',
        status: '',
        create_time: []
    }
})
const prop = defineProps({
    max: {
        type: Number,
        default: 0
    },
    min: {
        type: Number,
        default: 0
    }
});
const searchFormRef = ref<FormInstance>()
const loadExchangeGoodsList = (page: number = 1) => {
    tableData.loading = true
    tableData.page = page

    getActiveExchangePageList({
        page: tableData.page,
        limit: tableData.limit,
        ...tableData.searchParam
    }).then((res: any) => {
        tableData.loading = false
        tableData.data = res.data.data
        tableData.total = res.data.total
        if (ids.value.length) {
            nextTick(() => {
                tableData.data.forEach((el: any) => {
                    if (ids.value.includes(el.id)) {
                        tableRef.value.toggleRowSelection(el, true);
                    }
                })
            })
        }

    }).catch(() => {
        tableData.loading = false
    })
}
//获取状态列表
const statusOption = ref([])
const getActiveExchangeStatusFn = () => {
    getActiveExchangeStatus().then(res => {
        statusOption.value = res.data
    })
}
getActiveExchangeStatusFn()
const emit = defineEmits(["select"]);
const ids = ref<Array<any>>([])
const show = (data: any) => {
    ids.value = data
    showDialog.value = true;
    loadExchangeGoodsList()
};
// 选中数据
const multipleSelection: any = ref([]);

// 监听表格单行选中
const handleSelectionChange = (val: []) => {
    multipleSelection.value = val;
};

const save = () => {
    if (prop.min && multipleSelection.value.length < prop.min) {
        ElMessage({
            type: 'warning',
            message: `${ t('goodsSelectPopupGoodsMinTip') }${ prop.min }${ t('goodsSelectPopupPiece') }`,
        });
        return;
    }

    emit("select", multipleSelection.value);
    showDialog.value = false;
    if (tableRef.value) tableRef.value.clearSelection();
};
defineExpose({
    show
});
</script>
<style lang="scss" scoped></style>

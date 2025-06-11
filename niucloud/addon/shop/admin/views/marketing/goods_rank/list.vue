<template>
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">
            <div class="flex justify-between items-center">
                <span class="text-page-title">{{ pageName }}</span>
                <el-button type="primary" @click="handleChange">{{ t("addRanking") }}</el-button>
            </div>

            <!-- 搜索 -->
            <el-card class="box-card !border-none my-[10px] table-search-wrap" shadow="never">
                <el-form :inline="true" :model="tableData.searchParam" ref="searchFormRef">
                    <el-form-item :label="t('rankName')" prop="rankName">
                        <el-input v-model.trim="tableData.searchParam.name" :placeholder="t('rankNamePlaceholder')" />
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="loadRankList()">{{ t("search") }}</el-button>
                        <el-button @click="resetForm(searchFormRef)">{{ t("reset") }}</el-button>
                    </el-form-item>
                </el-form>
            </el-card>

            <!-- 列表 -->
            <div>
                <div class="mb-[10px] flex items-center">
                    <el-checkbox v-model="toggleCheckbox" size="large" class="px-[14px]" @change="toggleChange" :indeterminate="isIndeterminate" />
                    <el-button @click="batchDeleteGoods" size="small">{{ t("batchDeletion") }}</el-button>
                </div>
                <el-table :data="tableData.data" size="large" v-loading="tableData.loading" ref="goodBankListTableRef" @sort-change="sortChange" @selection-change="handleSelectionChange">
                    <template #empty>
                        <span>{{ !tableData.loading ? t("emptyData") : "" }}</span>
                    </template>
                    <el-table-column type="selection" width="55" />
                    <el-table-column prop="name" :label="t('rankName')" min-width="130" />
                    <el-table-column prop="show_goods_num" :label="t('showGoodsNum')" min-width="130" />
                    <el-table-column prop="goods_source_name" :label="t('goodsSource')" min-width="130" />
                    <el-table-column prop="rule_type_name" :label="t('ruleType')" min-width="130" />
                    <el-table-column prop="rank_type_name" :label="t('rankType')" min-width="130" />
                    <el-table-column prop="status" :label="t('isShow')" width="130">
                        <template #default="{ row }">
                            <el-tag class="cursor-pointer" :type="row.status != 0 ? 'success' : 'danger'" @click="showClick(row)">{{ row.status != 0 ? '开启' : '关闭' }}</el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column prop="sort" min-width="120" :show-overflow-tooltip="true" sortable="custom">
                        <template #header>
                            <div style="display: inline-flex; align-items: center">
                                <span class="mr-[5px]">{{ t('sort') }}</span>
                                <el-tooltip class="box-item" effect="light" :content="t('sortRules')" placement="top">
                                    <el-icon color="#666">
                                        <QuestionFilled />
                                    </el-icon>
                                </el-tooltip>
                            </div>
                        </template>
                        <template #default="{ row }">
                            <el-input v-model.number="row.sort" class="w-[70px]" maxlength="8" @blur="sortInputListener(row.sort, row)" />
                        </template>
                    </el-table-column>
                    <el-table-column prop="create_time" :label="t('createTime')" min-width="150" sortable="custom">
                        <template #default="{ row }">
                            <div>{{ row.create_time }}</div>
                        </template>
                    </el-table-column>

                    <el-table-column :label="t('operation')" fixed="right" align="right" min-width="120">
                        <template #default="{ row }">
                            <el-button type="primary" link @click="editEvent(row)">{{ t("edit") }}</el-button>
                            <el-button type="primary" link @click="deleteEvent(row.rank_id)">{{ t("delete") }}</el-button>
                        </template>
                    </el-table-column>
                </el-table>
                <div class="mt-[16px] flex justify-end">
                    <el-pagination v-model:current-page="tableData.page" v-model:page-size="tableData.limit"
                                   layout="total, sizes, prev, pager, next, jumper" :total="tableData.total"
                                   @size-change="loadRankList()" @current-change="loadRankList" />
                </div>
            </div>
        </el-card>
    </div>
</template>

<script lang="ts" setup>
import { ref, reactive } from "vue";
import { t } from "@/lang";
import { useRoute, useRouter } from "vue-router";
import {
    getRankPageList,
    deleteGoodRank,
    batchDelete,
    modifyGoodsRankSort,
    editRankStatus
} from "@/addon/shop/api/marketing";
import { FormInstance, ElMessage, ElMessageBox } from "element-plus";
import { debounce, setTablePageStorage, getTablePageStorage } from "@/utils/common";

const router = useRouter();
const route = useRoute();
const pageName = route.meta.title;
const repeat = ref(false);
const searchFormRef = ref<FormInstance>();

// 表单内容
const tableData = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: false,
    data: [],
    searchParam: {
        name: "",
        order: '',
        sort: ''
    },
});

// 获取列表
const loadRankList = (page: number = 1) => {
    tableData.loading = true;
    tableData.page = page;

    getRankPageList({
        page: tableData.page,
        limit: tableData.limit,
        ...tableData.searchParam,
    }).then((res) => {
        tableData.loading = false;
        tableData.data = res.data.data;
        tableData.total = res.data.total;
        setTablePageStorage(tableData.page, tableData.limit, tableData.searchParam)
    }).catch(() => {
        tableData.loading = false;
    })
}

loadRankList(getTablePageStorage(tableData.searchParam).page)

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()
    tableData.searchParam.name = ""
    loadRankList()
}

// 添加榜单
const handleChange = () => {
    router.push("/shop/marketing/goods_rank/edit")
}

const editEvent = (data: any) => {
    router.push("/shop/marketing/goods_rank/edit?rank_id=" + data.rank_id)
}

const showClick = (row: any) => {
    row.status = row.status === 1 ? 0 : 1
    const obj = {
        rank_id: row.rank_id,
        status: row.status
    }
    editRankStatus(obj)
}

// 删除
const deleteEvent = (id: number) => {
    ElMessageBox.confirm(t("deleteTips"), t("warning"), {
        confirmButtonText: t("confirm"),
        cancelButtonText: t("cancel"),
        type: "warning"
    }).then(() => {
        deleteGoodRank(id).then(() => {
            loadRankList()
        })
    })
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
        tableData.searchParam.order = event.prop
        tableData.searchParam.sort = sort
    }
    loadRankList()
}

// 批量复选框
const toggleCheckbox = ref();

// 复选框中间状态
const isIndeterminate = ref(false);

// 监听批量复选框事件
const toggleChange = (value: any) => {
    isIndeterminate.value = false;
    goodBankListTableRef.value.toggleAllSelection()
};

const goodBankListTableRef = ref()

// 选中数据
const multipleSelection: any = ref([])

// 监听表格单行选中
const handleSelectionChange = (val: []) => {
    multipleSelection.value = val

    toggleCheckbox.value = false
    if (
        multipleSelection.value.length > 0 &&
        multipleSelection.value.length < tableData.data.length
    ) {
        isIndeterminate.value = true
    } else {
        isIndeterminate.value = false
    }

    if (multipleSelection.value.length == tableData.data.length) {
        toggleCheckbox.value = true
    }
};

// 批量删除
const batchDeleteGoods = () => {
    if (multipleSelection.value.length == 0) {
        ElMessage({
            type: "warning",
            message: `${ t("batchEmptySelectedGoodsTips") }`,
        })
        return
    }

    ElMessageBox.confirm(t("batchGoodsDeleteTips"), t("warning"), {
        confirmButtonText: t("confirm"),
        cancelButtonText: t("cancel"),
        type: "warning"
    }).then(() => {
        if (repeat.value) return
        repeat.value = true

        const rankIds: any = []
        multipleSelection.value.forEach((item: any) => {
            rankIds.push(item.rank_id)
        })

        batchDelete({
            rank_id: rankIds
        }).then(() => {
            loadRankList()
            repeat.value = false
        }).catch(() => {
            repeat.value = false
        })
    })
}

// 正则表达式
const regExp = {
    number: /^\d{0,10}$/,
    digit: /^\d{0,10}(.?\d{0,2})$/,
}

// 修改排序号
const sortInputListener = debounce((sort, row) => {
    if (isNaN(sort) || !regExp.number.test(sort)) {
        ElMessage({
            type: "warning",
            message: `${ t("sortTips") }`,
        })
        return
    }
    if (sort > 99999999) {
        row.sort = 99999999
    }
    modifyGoodsRankSort({
        rank_id: row.rank_id,
        sort
    }).then((res) => {
        loadRankList()
    })
})
</script>

<style lang="scss" scoped></style>

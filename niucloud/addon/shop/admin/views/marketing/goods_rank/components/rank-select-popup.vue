<template>
    <div>
        <div @click="show">
        <slot>
            <el-button>{{ t('rankSelectPopupSelectRankButton') }}</el-button>
            <div class="inline-block ml-[10px] text-[14px]" v-show="rankIds.length">
                <span>{{ t('goodsSelectPopupSelect') }}</span>
                <span class="text-primary mx-[2px]">{{ rankIds.length }}</span>
                <span>{{ t('goodsSelectPopupPiece') }}</span>
            </div>
        </slot>
        </div>
        <el-dialog v-model="showDialog" :name="t('rankSelect')" width="1000px" :destroy-on-close="true" :close-on-click-modal="false">
        <el-form :inline="true" :model="rankTable.searchParam" ref="searchFormRef">
            <el-form-item :label="t('rankName')" prop="keyword" class="form-item-wrap">
                <el-input v-model.trim="rankTable.searchParam.name" :placeholder="t('rankNamePlaceholder')" maxlength="60" />
            </el-form-item>
            <el-form-item class="form-item-wrap">
                <el-button type="primary" @click="loadRankList()">{{ t('search') }}</el-button>
                <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
            </el-form-item>
        </el-form>

        <el-table :data="rankTable.data" size="large" v-loading="rankTable.loading" ref="rankListTableRef" max-height="400" @select="handleSelectChange" @select-all="handleSelectAllChange">
            <template #empty>
                <span>{{ !rankTable.loading ? t('emptyData') : '' }}</span>
            </template>
            <!-- 多选框 -->
            <el-table-column type="selection" width="55" />
            <el-table-column prop="name" :label="t('rankName')" min-width="130" />
            <el-table-column prop="show_goods_num" :label="t('showGoodsNum')" min-width="130" />
            <el-table-column prop="goods_source_name" :label="t('goodsSourceName')" min-width="130" />
            <el-table-column prop="rule_type_name" :label="t('ruleTypeName')" min-width="130" />
            <el-table-column prop="rank_type_name" :label="t('rankTypeName')" min-width="130" />
        </el-table>

        <div class="mt-[16px] flex">
            <div class="flex items-center flex-1">
                <div class="mr-[10px]" v-show="selectRankNum">
                    <span>{{ t('goodsSelectPopupBeforeTip') }}</span>
                    <span class="text-primary mx-[2px]">{{ selectRankNum }}</span>
                    <span>{{ t('rankSelectPopupAfterTip') }}</span>
                </div>
                <el-button type="primary" link @click="clear" v-show="selectRankNum">{{ t('goodsSelectPopupClearGoods') }}</el-button>
            </div>
            <el-pagination v-model:current-page="rankTable.page" v-model:page-size="rankTable.limit" layout="total, sizes, prev, pager, next, jumper" :total="rankTable.total" @size-change="loadRankList()" @current-change="loadRankList" />
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
import { ref, reactive, computed, nextTick } from 'vue'
import { cloneDeep } from 'lodash-es'
import { ElMessage } from 'element-plus'
import { getSelectRankPageList } from '@/addon/shop/api/marketing'

const prop = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    max: {
        type: Number,
        default: 0
    },
    min: {
        type: Number,
        default: 0
    }
});

const emit = defineEmits(['update:modelValue', 'rankSelect']);

const rankIds = computed({
    get() {
        return prop.modelValue;
    },
    set(value) {
        emit('update:modelValue', value);
    }
});

const showDialog = ref(false)

// 已选榜单列表
const selectRank: any = reactive({})

// 已选榜单数量
const selectRankNum: any = computed(() => {
    return Object.keys(selectRank).length
})

const rankTable = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        name: '',
        verify_rank_ids:'',
    }
})

const searchFormRef = ref()

const rankListTableRef = ref()

// 监听表格复选框
const handleSelectChange = (selection: any, row: any) => {
    if (prop.max === 1) {
        // 单选模式
        rankListTableRef.value.clearSelection(); // 清除所有选中状态
        rankListTableRef.value.toggleRowSelection(row, true);
        for (const key in selectRank) {
            delete selectRank[key];
        }
        selectRank['rank_' + row.rank_id] = row;
    } else {
        let isSelected = false;
        for (let i = 0; i < selection.length; i++) {
            if (selection[i].rank_id === row.rank_id) {
                isSelected = true;
                break;
            }
        }
        if (isSelected) {
            selectRank['rank_' + row.rank_id] = row;
        } else {
            delete selectRank['rank_' + row.rank_id];
        }
    }
}

// 监听表格全选
const handleSelectAllChange = (selection: any) => {
    if (prop.max == 1) {
        rankListTableRef.value.clearSelection();
        for (const key in selectRank) {
            delete selectRank[key];
        }
    } else {
        if (selection.length) {
            selection.forEach((item: any) => {
                selectRank['rank_' + item.rank_id] = item;
            });
        } else {
            rankTable.data.forEach((item: any) => {
                delete selectRank['rank_' + item.rank_id];
            });
        }
    }
}

// 表格设置选中状态
const setRankSelected = () => {
    nextTick(() => {
        if (!rankListTableRef.value) return;
        for (let i = 0; i < rankTable.data.length; i++) {
            rankListTableRef.value.toggleRowSelection(rankTable.data[i], false);
            if (selectRank['rank_' + rankTable.data[i].rank_id]) {
                rankListTableRef.value.toggleRowSelection(rankTable.data[i], true);
            }
        }
    });
}

/**
 * 获取榜单列表
 */
const loadRankList = (page: number = 1, callback: any = null) => {
    rankTable.loading = true
    rankTable.page = page

    const searchData: any = cloneDeep(rankTable.searchParam);
    getSelectRankPageList({
        page: rankTable.page,
        limit: rankTable.limit,
        ...searchData
    }).then(res => {
        rankTable.loading = false
        rankTable.data = res.data.data
        rankTable.total = res.data.total

        if (callback) callback(res.data.verify_rank_ids)

        setRankSelected();
    }).catch(() => {
        rankTable.loading = false
    })

}

const resetForm = (formEl: any) => {
    if (!formEl) return
    formEl.resetFields()
    rankTable.searchParam.verify_rank_ids = '';
    rankTable.searchParam.name = '';
    loadRankList()
}

const show = () => {
    // 检测商品id集合是否存在，移除不存在的商品id，纠正数据准确性
    rankTable.searchParam.verify_rank_ids = rankIds.value;

    loadRankList(1, (verify_rank_ids: any) => {
        // 第一次打开弹出框时，纠正数据，并且赋值已选榜单
        if (rankIds.value) {
            rankIds.value.splice(0, rankIds.value.length, ...verify_rank_ids)
            rankIds.value.forEach((item: any) => {
                if (!selectRank['rank_' + item]) {
                    selectRank['rank_' + item] = {};
                }
            })
            // 赋值已选择的榜单
            for (let i = 0; i < rankTable.data.length; i++) {
                if (rankIds.value.indexOf(rankTable.data[i].rank_id) != -1) {
                    selectRank['rank_' + rankTable.data[i].rank_id] = rankTable.data[i];
                }
            }
        }
    })
    showDialog.value = true
}

// 清空已选榜单
const clear = () => {
    for (let k in selectRank) {
        delete selectRank[k];
    }
    setRankSelected();
}
const save = () => {
    if (prop.min && selectRankNum.value < prop.min) {
        ElMessage({
            type: 'warning',
            message: `${t('rankSelectPopupGoodsMinTip')}${prop.min}${t('goodsSelectPopupPiece')}`,
        });
        return;
    }
    if (prop.max && prop.max > 0 && selectRankNum.value && selectRankNum.value > prop.max) {
        ElMessage({
            type: 'warning',
            message: `${t('rankSelectPopupGoodsMaxTip')}${prop.max}${t('goodsSelectPopupPiece')}`,
        });
        return;
    }
    let ids: any = [];
    for (let k in selectRank) {
        ids.push(parseInt(k.replace('rank_', '')));
    }
    rankIds.value.splice(0, rankIds.value.length, ...ids)
    emit('rankSelect',selectRank)
    showDialog.value = false
}

defineExpose({
    showDialog,
    selectRank,
    selectRankNum
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


<template>
    <div>
        <div @click="show">
            <slot>
                <el-button>{{ t('选择会员') }}</el-button>
                <div class="inline-block ml-[10px] text-[14px]" v-show="memberIds.length">
                    <span>{{ t('已选') }}</span>
                    <span class="text-primary mx-[2px]">{{ memberIds.length }}</span>
                    <span>{{ t('个') }}</span>
                </div>
            </slot>
        </div>
        <el-dialog v-model="showDialog" :title="t('会员选择')" width="1000px" :destroy-on-close="true" :close-on-click-modal="false">

            <el-form :inline="true" :model="memberTable.searchParam" ref="searchFormRef">
                <el-form-item :label="t('会员名称')" prop="keyword" class="form-item-wrap">
                    <el-input v-model.trim="memberTable.searchParam.keyword" :placeholder="t('memberInfoPlaceholder')" maxlength="60" @keydown.enter.native.prevent  />
                </el-form-item>
                <el-form-item class="form-item-wrap">
                    <el-button type="primary" @click="loadMemberList()">{{ t('search') }}</el-button>
                    <el-button @click="resetForm(searchFormRef)">{{ t('reset') }}</el-button>
                </el-form-item>
            </el-form>

            <el-table :data="memberTable.data" size="large" v-loading="memberTable.loading" ref="memberListTableRef" max-height="400" @select="handleSelectChange" @select-all="handleSelectAllChange">
                <template #empty>
                    <span>{{ !memberTable.loading ? t('emptyData') : '' }}</span>
                </template>
                <el-table-column type="selection" width="55" />

                <el-table-column prop="nickname" :show-overflow-tooltip="true" :label="t('memberInfo')" min-width="150">
                    <template #default="{ row }">
                        <div class="flex items-center">
                            <div class="mr-[10px] rounded-full w-[50px] h-[50px] flex items-center justify-center">
                                <img class="max-w-[50px] max-h-[50px]" v-if="row.headimg" :src="img(row.headimg)" alt="">
                                <img class="max-w-[50px] max-h-[50px]" v-else src="@/app/assets/images/member_head.png" alt="">
                            </div>
                            <div class="flex flex-col">
                                <span>{{ row.nickname || '' }}</span>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="mobile" :label="t('mobile')" width="180" />
                <el-table-column prop="point" :label="t('point')" min-width="80" />
                <el-table-column prop="balance" :label="t('balance')" min-width="80" />

            </el-table>
            <div class="mt-[16px] flex">
                <div class="flex items-center flex-1">
                    <div class="mr-[10px]" v-show="selectMemberNum">
                        <span>{{ t('已选择') }}</span>
                        <span class="text-primary mx-[2px]">{{ selectMemberNum }}</span>
                        <span>{{ t('个会员') }}</span>
                    </div>
                    <el-button type="primary" link @click="clear" v-show="selectMemberNum">{{ t('取消选择') }}</el-button>
                </div>
                <el-pagination v-model:current-page="memberTable.page" v-model:page-size="memberTable.limit"
                    layout="total, sizes, prev, pager, next, jumper" :total="memberTable.total"
                    @size-change="loadMemberList()" @current-change="loadMemberList" />
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
import { img } from '@/utils/common'
import { getMemberList } from '@/app/api/member'

const prop = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    },
    max: {
        type: Number,
        default: 0
    },
    min: {
        type: Number,
        default: 0
    }
})

const emit = defineEmits(['update:modelValue','memberSelect'])

const memberIds: any = computed({
    get () {
        return prop.modelValue
    },
    set (value) {
        emit('update:modelValue', value)
    }
})

const showDialog = ref(false)

// 已选会员列表
const selectMember: any = reactive({})

// 已选会员数量
const selectMemberNum: any = computed(() => {
    return Object.keys(selectMember).length
})

const memberTable = reactive({
    page: 1,
    limit: 10,
    total: 0,
    loading: true,
    data: [],
    searchParam: {
        keyword: '',
        verify_member_ids:''
    }
})

const searchFormRef = ref()

// 初始化数据
const initData = () => {
}

initData()

const memberListTableRef = ref()

// 监听表格复选框
const handleSelectChange = (selection: any, row: any) => {
    // 是否选中
    let isSelected = false
    for (let i = 0; i < selection.length; i++) {
        if (selection[i].member_id == row.member_id) {
            isSelected = true
            break
        }
    }
    if (isSelected) {
        selectMember['member_' + row.member_id] = row
    } else {
        // 未选中，删除当前会员
        delete selectMember['member_' + row.member_id]
    }

}

// 监听表格全选
const handleSelectAllChange = (selection: any) => {
    if (selection.length) {
        selection.forEach((item: any) => {
            selectMember['member_' + item.member_id] = item
        })
    } else {
        // 未选中，删除当前页面的数据
        memberTable.data.forEach((item: any) => {
            delete selectMember['member_' + item.member_id]
        })
    }
}

/**
 * 获取会员列表
 */
const loadMemberList = (page: number = 1, callback: any = null) => {
    memberTable.loading = true
    memberTable.page = page

    const searchData: any = cloneDeep(memberTable.searchParam);
    getMemberList({
        page: memberTable.page,
        limit: memberTable.limit,
        ...searchData
    }).then(res => {
        memberTable.loading = false
        memberTable.data = res.data.data
        memberTable.total = res.data.total

        if (callback) callback(res.data.verify_member_ids)

        setCouponSelected();
    }).catch(() => {
        memberTable.loading = false
    })

}

// 表格设置选中状态
const setCouponSelected = () => {
    nextTick(() => {
        if (!memberListTableRef.value) return;
        for (let i = 0; i < memberTable.data.length; i++) {
            memberListTableRef.value.toggleRowSelection(memberTable.data[i], false);
            if (selectMember['member_' + memberTable.data[i].member_id]) {
                memberListTableRef.value.toggleRowSelection(memberTable.data[i], true);
            }
        }
    });
}

const resetForm = (formEl: FormInstance | undefined) => {
    if (!formEl) return
    formEl.resetFields()

    loadMemberList()
}

const show = () => {
    // 检测商品id集合是否存在，移除不存在的商品id，纠正数据准确性
    memberTable.searchParam.verify_member_ids = memberIds.value;
    loadMemberList(1, (verify_member_ids:any) => {
        // 第一次打开弹出框时，纠正数据，并且赋值已选会员
        if (memberIds.value) {
            // memberIds.value.splice(0, memberIds.value.length, ...verify_member_ids)
            // 先删除 selectMember 中已经不再存在于 memberIds 中的会员
            for (let key in selectMember) {
                const memberId = key.replace('member_', '');

                if (!memberIds.value.includes(Number(memberId))) {
                    delete selectMember[key]; // 删除不存在的会员
                }
            }
            memberIds.value.forEach((item: any) => {
                if (!selectMember['member_' + item]) {
                    selectMember['member_' + item] = {};
                }
            })
            // 赋值已选择的会员
            for (let i = 0; i < memberTable.data.length; i++) {
                if (memberIds.value.indexOf(memberTable.data[i].member_id) != -1) {
                    selectMember['member_' + memberTable.data[i].member_id] = memberTable.data[i];
                }
            }

        }
    })
    showDialog.value = true
}

// 清空已选会员
const clear = () => {
    for (let k in selectMember) {
        delete selectMember[k];
    }
    setCouponSelected();
}

const save = () => {
    if (prop.min && selectMemberNum.value < prop.min) {
        ElMessage({
            type: 'warning',
            message: `${t('所选会员数量不能少于')}${prop.min}${t('个')}`,
        });
        return;
    }

    if (prop.max && prop.max > 0 && selectMemberNum.value && selectMemberNum.value > prop.max) {
        ElMessage({
            type: 'warning',
            message: `${t('所选会员数量不能超过')}${prop.max}${t('个')}`,
        });
        return;
    }

    let ids: any = [];
    for (let k in selectMember) {
        ids.push(parseInt(k.replace('member_', '')));
    }

    memberIds.value.splice(0, memberIds.value.length, ...ids)
    emit('memberSelect',selectMember)
    showDialog.value = false
}

defineExpose({
    showDialog,
    selectMember,
    selectMemberNum
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

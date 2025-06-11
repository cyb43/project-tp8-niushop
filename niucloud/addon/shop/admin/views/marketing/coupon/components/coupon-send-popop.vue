<template>
    <el-dialog v-model="showDialog" :title="t('sendCouponTitle')" width="1000px" :destroy-on-close="true">
        <!-- 表单 -->
        <el-form class="page-form" :model="formData" @submit.prevent label-width="120px" ref="formRef" :rules="formRules" v-loading="loading">
            <el-form-item :label="t('selectCoupon')" prop="title">
                <div>{{ couponTitle }}</div>
            </el-form-item>
            <el-form-item :label="t('sendNum')" prop="send_num">
                <div>
                    <el-input v-model.number="formData.send_num" class="input-width" maxlength="6" :placeholder="t('sendNumPrompt')">
                        <template #append>{{ t('num') }}</template>
                    </el-input>
                    <div class="text-[12px] text-[#999]">{{ t('sendNumTip') }}</div>
                    <!-- <div class="text-[12px] text-[#999]">{{ t('sendNumTipTwo') }}</div> -->
                </div>
            </el-form-item>
            <el-form-item :label="t('rangeType')" prop="range_type" required>
                <el-radio-group v-model="formData.range_type">
                    <el-radio v-for="(label, key) in rangeTypeList" :key="key" :label="key">{{ label }}</el-radio>
                </el-radio-group>
            </el-form-item>
            <el-form-item :label="t('')" prop="range_param.member_ids" v-if="formData.range_type=='member'">
                <select-member ref="selectMemberRef" v-model="formData.range_param.member_ids" @memberSelect="memberSelect" :min="1" :max="20"/>
            </el-form-item>
            <el-form-item v-if="memberList.length && formData.range_type=='member'" prop="member_data">
                <el-table :data="memberList" size="large" max-height="400">
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
                    <el-table-column prop="mobile" :label="t('mobile')" width="180"/>
                    <el-table-column prop="point" :label="t('point')" min-width="80"/>
                    <el-table-column prop="balance" :label="t('balance')" min-width="80"/>
                    <el-table-column :label="t('operation')" align="right" min-width="160">
                        <template #default="{ row, $index }">
                            <el-button type="primary" link @click="deleteMemberEvent(row, $index)">{{ t("delete") }}</el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </el-form-item>
            <!-- 会员标签 -->
            <el-form-item :label="t('memberLabel')" prop="range_param.member_label" v-if="formData.range_type=='member_label'">
                <el-select v-model="formData.range_param.member_label" multiple clearable :placeholder="t('memberLabelPlaceholder')" class="input-width">
                    <el-option :label="item['label_name']" :value="item['label_id']" v-for="(item, index) in labelSelectData" :key="index"/>
                </el-select>
            </el-form-item>
            <!-- 会员等级 -->
            <el-form-item :label="t('memberLevel')" prop="range_param.member_level" v-if="formData.range_type=='member_level'">
                <el-select v-model="formData.range_param.member_level" multiple clearable :placeholder="t('memberLevelPlaceholder')" class="input-width">
                    <el-option :label="item['level_name']" :value="item['level_id']" v-for="(item, index) in levelSelectData" :key="index"/>
                </el-select>
            </el-form-item>
        </el-form>
        <template #footer>
            <span class="dialog-footer">
                <el-button type="primary" @click="onSave(formRef)">{{ t('confirm') }}</el-button>
                <el-button @click="showDialog = false">{{ t('cancel') }}</el-button>
            </span>
        </template>
    </el-dialog>
</template>

<script lang="ts" setup>
import { ref, computed } from 'vue'
import { t } from '@/lang'
import { img } from '@/utils/common'
import type { FormInstance } from 'element-plus'
import { getCouponInfo, sendCoupon, initCoupon } from '@/addon/shop/api/marketing'
import { getMemberLabelAll, getMemberLevelAll } from '@/app/api/member'
import selectMember from '@/addon/shop/views/marketing/coupon/components/select-member.vue'

const showDialog = ref(false)

const emit = defineEmits(['callback'])

const couponId = ref()
const loading = ref(false)

const initialFormData = {
    id: '', // 优惠券id
    send_num: '', // 发放数量
    range_type: 'all', // 发放范围
    range_param: {
        member_ids: [], // 会员
        member_level: [], // 等级
        member_label: [] // 标签
    }
}

// 正则表达式
const regExp: any = {
    required: /[\S]+/,
    number: /^\d{0,10}$/,
    digit: /^\d{0,10}(.?\d{0,2})$/,
    special: /^\d{0,10}(.?\d{0,3})$/
}

const formData: Record<string, any> = ref({ ...initialFormData })
const formRef = ref<FormInstance>()
const formRules = computed(() => {
    const rules = {
        send_num: [
            {
                required: true,
                trigger: 'blur',
                validator: (rule: any, value: any, callback: any) => {
                    if (value === '' || value === null || value === undefined) {
                        callback(new Error(t('sendNumTipThree')));
                    } else if (isNaN(value) || !regExp.number.test(value)) {
                        callback(new Error(t('sendNumTipFive')));
                    } else if (value <= 0 || value > 10) {
                        callback(new Error(t('sendNumTipFour')));
                    } else {
                        callback();
                    }
                }
            }
        ],
        'range_param.member_label': [
            {
                validator: (rule: any, value: any, callback: any) => {
                    if (formData.value.range_type == 'member_label') {
                        if (value.length == 0) {
                            callback(t("memberLabelTip")) // 提示错误信息
                        } else {
                            callback() // 校验通过
                        }
                    } else {
                        callback()
                    }
                },
                required: true,
                trigger: "change"
            }
        ],
        'range_param.member_level': [
            {
                validator: (rule: any, value: any, callback: any) => {
                    if (formData.value.range_type == 'member_level') {
                        if (value.length == 0) {
                            callback(t("memberLevelTip")) // 提示错误信息
                        } else {
                            callback() // 校验通过
                        }
                    } else {
                        callback()
                    }
                },
                required: true,
                trigger: "change"
            }
        ],
        'range_param.member_ids': [
            {
                required: true,
                trigger: 'blur',
                validator: (rule: any, value: any, callback: any) => {
                    if (formData.value.range_type == 'member') {
                        if (memberList.value.length == 0) {
                            callback(new Error(t('memberTip')))
                        } else {
                            callback()
                        }
                    }
                }
            }
        ]
    }
    return rules
})

/************** 会员-start *********************/
const selectMemberRef = ref<any>(null)

const memberList = ref<any>([])
// 选择会员
const memberSelect = (value: any) => {
    memberList.value = Object.values(value)
    formRef.value?.validateField('range_param.member_ids')
}

// 删除会员
const deleteMemberEvent = (row: any, index: number) => {
    memberList.value.splice(index, 1);
    // 找到并删除对应的 member_id
    const idIndex = formData.value.range_param.member_ids.indexOf(row.member_id)
    if (idIndex !== -1) {
        formData.value.range_param.member_ids.splice(idIndex, 1)
    }
}

/************** 会员-end *********************/

const levelSelectData = ref([])
const labelSelectData = ref([])
const rangeTypeList = ref({})
const couponTitle = ref('')

const show = (id: any) => {
    couponId.value = id
    loading.value = true
    // 获取全部标签
    getMemberLabelAll().then(({ data }) => {
        labelSelectData.value = data
    })

    getMemberLevelAll().then(({ data }) => {
        levelSelectData.value = data
    })

    getCouponInfo(couponId.value).then(({ data }) => {
        couponTitle.value = data.title
        loading.value = false
    })
    initCoupon().then(({ data }) => {
        rangeTypeList.value = data.range_type_list
    })
    showDialog.value = true
}

const onSave = async () => {
    await formRef.value?.validate(async (valid) => {
        if (valid) {
            formData.value.id = couponId.value
            loading.value = true
            sendCoupon(formData.value).then((res) => {
                loading.value = false
                showDialog.value = false
                emit('callback')
            }).catch(() => {
                loading.value = false
                showDialog.value = false
            })
        }
    })
}

defineExpose({
    showDialog,
    show
})
</script>

<style lang="scss">

</style>

<template>
    <!--签到设置-->
    <div class="main-container">

        <el-form class="page-form" :model="formData" label-width="150px" ref="ruleFormRef" :rules="formRules" v-loading="loading">
            <el-card class="box-card !border-none" shadow="never">
                <h3 class="text-page-title">{{ t('signRule') }}</h3>

                <el-form-item :label="t('isUse')">
                    <el-switch v-model="formData.is_use" />
                </el-form-item>

                <el-form-item :label="t('signPeriod')" prop="sign_period" v-if="formData.is_use">
                    <el-input v-model.trim="formData.sign_period" @keyup="filterNumber($event)" maxlength="3" clearable class="input-width" /><span class="ml-[10px]">天</span>
                </el-form-item>

                <el-form-item :label="t('daySignAward')" prop="day_award" v-if="formData.is_use">
                    <div v-for="(item, index) in daySignAwardText" :key="index">
                        <span v-if="item.is_use == '1'">{{ item.content }}&nbsp;&nbsp;</span>
                    </div>
                    <span class="cursor-pointer tutorial-btn ml-[5px]" @click="daySignAwardSet" v-if="formData.day_award == ''">{{ t('set') }}</span>
                    <div class="flex ml-[5px]" v-else>
                        <span class="cursor-pointer tutorial-btn" @click="daySignAwardSet">{{ t('modify') }}</span>
                        <span class="ml-[5px] mr-[5px]">|</span>
                        <span class="cursor-pointer tutorial-btn" @click="daySignAwardDel">{{ t('delete') }}</span>
                    </div>
                    <div class="form-tip">{{ t('daySignAwardTip') }}</div>
                </el-form-item>

                <el-form-item :label="t('continueSignAward')" prop="continue_award" v-if="formData.is_use">
                    <div>
                        <div class="form-tip">{{ t('continueSignAwardTipTop') }}</div>
                        <div class="mt-[10px]">
                            <el-table :data="continueSignAwardTableData.data" size="large" v-loading="continueSignAwardTableData.loading">
                                <template #empty>
                                    <span>{{ !continueSignAwardTableData.loading ? t('emptyData') : '' }}</span>
                                </template>

                                <el-table-column prop="continue_sign" :label="t('continueSign')" min-width="120" />

                                <el-table-column :label="t('continueSignAward')" min-width="300">
                                    <template #default="{ row }">
                                        <div v-for="(item, index) in row.continue_award" :key="index">
                                            <span v-if="item.is_use == '1'">{{ item.content }}</span>
                                        </div>
                                    </template>
                                </el-table-column>

                                <el-table-column :label="t('receiveLimit')" min-width="120">
                                    <template #default="{ row }">
                                        <span v-if="row.receive_limit == 1">{{ t('noLimit') }}</span>
                                        <span v-else>{{ t('everyOneLimit') }}{{ row.receive_num }}{{ t('time') }}</span>
                                    </template>
                                </el-table-column>

                                <el-table-column :label="t('operation')" align="right" fixed="right" width="130">
                                    <template #default="scope">
                                        <el-button type="primary" link @click="continueSignAwardModify(true, scope.$index)">{{ t('modify') }}</el-button>
                                        <el-button type="primary" link @click="deleteContinueSignAwardEvent(scope.$index)">{{ t('delete') }}</el-button>
                                    </template>
                                </el-table-column>
                            </el-table>
                        </div>
                        <div class="flex mt-[10px]">
                            <span class="cursor-pointer tutorial-btn" @click="continueSignAwardSet">{{ t('add') }}</span>
                        </div>
                        <div class="form-tip">{{ t('continueSignAwardTipBottom') }}</div>
                    </div>
                </el-form-item>

                <el-form-item :label="t('ruleExplain')" prop="rule_explain" v-if="formData.is_use">
                    <div class="flex">
                        <el-input v-model.trim="formData.rule_explain" :placeholder="t('ruleExplainTip')" type="textarea" maxlength="500" show-word-limit rows="5" class="textarea-width" clearable />
                        <el-button class="ml-[20px]" type="primary" @click="defaultExplainEvent()" plain>{{ t('useDefaultExplain') }}</el-button>
                    </div>
                </el-form-item>
            </el-card>
        </el-form>

        <!-- 日签奖励 -->
        <el-dialog v-model="daySignDialog" :title="t('daySignTitle')" width="1000px" :destroy-on-close="true" v-if="formData.is_use">
            <sign-day ref="benefitsRef" v-model="formData.day_award" />
            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="daySignDialog = false">{{ t('cancel') }}</el-button>
                    <el-button type="primary" @click="setDaySignAward()">{{ t('confirm') }}</el-button>
                </span>
            </template>
        </el-dialog>

        <!-- 连签奖励 -->
        <el-dialog v-model="continueSignDialog" :title="t('continueSignTitle')" width="1200px" :destroy-on-close="true" v-if="formData.is_use">
            <sign-continue ref="continueRef" v-model="continue_award" :sign_period="formData.sign_period" />
            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="continueSignDialog = false">{{ t('cancel') }}</el-button>
                    <el-button type="primary" @click="setContinueSignAward()">{{ t('confirm') }}</el-button>
                </span>
            </template>
        </el-dialog>

        <div class="fixed-footer-wrap">
            <div class="fixed-footer">
                <el-button type="primary" @click="onSave(ruleFormRef)">{{ t('save') }}</el-button>
            </div>
        </div>

    </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import { getSignConfig, setSignConfig, getMemberGiftsContent } from '@/app/api/member'
import signDay from '@/app/views/marketing/components/sign-day.vue'
import signContinue from '@/app/views/marketing/components/sign-continue.vue'
import { FormInstance, FormRules } from 'element-plus'
import { filterNumber } from '@/utils/common'
import { useRoute } from 'vue-router'
import { cloneDeep } from 'lodash-es'

const route = useRoute()
const pageName = route.meta.title
const tab = ref('signSet')

const loading = ref(true)
const daySignDialog = ref(false)
const continueSignDialog = ref(false)
const ruleFormRef = ref<FormInstance>()
const continue_award = ref({})
let isEdit = false // 是否为编辑状态
let editIndex = 0 // 连签奖励修改下标

// 正则表达式
const regExp: any = {
    required: /[\S]+/,
    number: /^\d{0,10}$/,
    digit: /^\d{0,10}(.?\d{0,2})$/,
    special: /^\d{0,10}(.?\d{0,3})$/
}
// 表单验证规则
const formRules = reactive<FormRules>({
    day_award: [
        {
            required: true,
            trigger: 'change',
            validator: (rule: any, value: any, callback: any) => {
                let isVerify = false
                daySignAwardText.value.forEach(item => {
                    item.is_use && (isVerify = true)
                })
                if (!isVerify) {
                    callback(t('daySignAwardPlaceholder'))
                } else {
                    callback()
                }
            }
        }
    ],
    sign_period: [{
        required: true,
        trigger: 'blur',
        validator: (rule: any, value: any, callback: any) => {
            if (value === null || value === '') {
                callback(t('signPeriodTip'))
            } else if (isNaN(value) || !regExp.number.test(value)) {
                callback(t('signPeriodLimitTips'))
            } else if (value < 2 || value > 365) {
                callback(t('signPeriodMustZeroTips'))
            } else {
                callback()
            }
        }
    }]
})

/**
 * 签到奖励文本请求参数
 */
const contentData = reactive<Record<string, any>>({
    gifts: []
})

/**
 * 连签奖励显示文本
 */
const continueSignAwardText = ref([])

const formData = reactive<Record<string, any>>({
    is_use: 0,
    sign_period: 30,
    day_award: '',
    continue_award: [],
    rule_explain: '',
})

const newArr = reactive<Record<string, any>>({
    receive_num: '',
    continue_sign: '',
    receive_limit: '',
    continue_award: []
})

/**
 * 连签奖励table数据
 */
const continueSignAwardTableData = reactive<Record<string, any>>({
    loading: false,
    data: []
})

/**
 * 获取显示签到设置
 */
const setFormData = async () => {
    const data = await (await getSignConfig()).data
    Object.keys(formData).forEach((key: string) => {
        if (data[key] != undefined) formData[key] = data[key]
    })

    if (formData.day_award) {
        contentData.gifts = formData.day_award
        setMemberBenefitsContent()
    }

    if (formData.continue_award) {
        formData.continue_award.forEach((item: any, index: number) => {
            continueSignAwardTableData.data.push(cloneDeep(item))

            contentData.gifts = []

            const val = cloneDeep(item)

            delete val['continue_sign']
            delete val['continue_tag']
            delete val['receive_limit']
            delete val['receive_num']

            contentData.gifts = val

            setMemberBenefitsContents(contentData, item, index)
        })
    }

    loading.value = false
}
setFormData()

/**
 * 获取日签奖励显示文本
 */
const daySignAwardText = ref([])
const setMemberBenefitsContent = async () => {
    const data = await (await getMemberGiftsContent(contentData)).data
    daySignAwardText.value = []
    Object.values(data).forEach((el: any) => {
        daySignAwardText.value.push(el)
    })
}

/**
 * 获取连签奖励显示文本
 * @param content 请求参数
 * @param item 连签奖励数据
 * @param index 数组索引
 * @param tag 0默认数据加载
 */
const setMemberBenefitsContents = async (content: any, item: any, index: number = 0, tag = 0) => {
    const data = await (await getMemberGiftsContent(content)).data
    continueSignAwardText.value = []
    Object.values(data).forEach((el: any) => {
        continueSignAwardText.value.push(el)
    })

    newArr.receive_num = item.receive_num
    newArr.continue_sign = item.continue_sign
    newArr.receive_limit = item.receive_limit
    newArr.continue_award = continueSignAwardText.value

    if (!isEdit) {
        if (tag == 0) {
            continueSignAwardTableData.data.splice(index, 1, cloneDeep(newArr))
        } else {
            continueSignAwardTableData.data.push(cloneDeep(newArr))
        }
    } else {
        continueSignAwardTableData.data.splice(editIndex, 1, cloneDeep(newArr))
    }

    isEdit = false
    editIndex = 0
}

/**
 * 设置签到设置
 */
const onSave = async (formEl: FormInstance | undefined) => {
    if (loading.value || !formEl) return
    await formEl.validate((valid) => {
        if (valid) {
            const save = cloneDeep(formData)
            setSignConfig(save).then(() => {
                loading.value = false
            }).catch(() => {
                loading.value = false
            })
        }
    })
}

/**
 * 打开日签奖励设置页
 */
const daySignAwardSet = () => {
    daySignDialog.value = true
}

/**
 * 日签奖励设置
 */
const benefitsRef = ref(null)
const setDaySignAward = async () => {
    if (!await benefitsRef.value?.verify()) return
    daySignDialog.value = false
    if (!formData.day_award.hasOwnProperty('balance') && !formData.day_award.hasOwnProperty('point') && formData.day_award.shop_coupon.is_use == 0) {
        formData.day_award = ''
    }
    if (formData.day_award.hasOwnProperty('balance') && formData.day_award.balance.is_use == 1) {
        formData.day_award.balance.money = Number(formData.day_award.balance.money)
    }
    contentData.gifts = formData.day_award

    setMemberBenefitsContent()
}

/**
 * 日签奖励删除
 */
const daySignAwardDel = () => {
    formData.day_award = ''
    daySignAwardText.value = []
}

/**
 * 打开连签奖励设置页
 */
const continueSignAwardSet = () => {
    continue_award.value = ''
    continueSignDialog.value = true
}

/**
 * 修改连签奖励设置页
 */
const continueSignAwardModify = (flag: boolean, index: any) => {
    isEdit = flag
    editIndex = index

    continue_award.value = formData.continue_award[index]
    continueSignDialog.value = true
}

/**
 * 连签奖励设置
 */
const continueRef = ref(null)
const setContinueSignAward = async () => {
    if (!await continueRef.value?.verify()) return
    continueSignDialog.value = false

    if (!continue_award.value.hasOwnProperty('balance') && !continue_award.value.hasOwnProperty('point') && continue_award.value.shop_coupon.is_use == 0) {
        continue_award.value = ''
    }
    if (continue_award.value.hasOwnProperty('balance') && continue_award.value.balance.is_use == 1) {
        continue_award.value.balance.money = Number(continue_award.value.balance.money)
    }
    if (Object.keys(continue_award.value).length > 0) {
        const val = cloneDeep(continue_award.value)

        delete val['continue_sign']
        delete val['continue_tag']
        delete val['receive_limit']
        delete val['receive_num']

        contentData.gifts = val

        let index = 0
        if (formData.continue_award.length > 0) {
            index = formData.continue_award.length - 1
        }

        setMemberBenefitsContents(contentData, continue_award.value, 0, 1)

        if (!isEdit) {
            formData.continue_award.push(cloneDeep(continue_award.value))
        } else {
            formData.continue_award.splice(editIndex, 1, cloneDeep(continue_award.value))
        }
    }
}

/**
 * 连签奖励删除
 */
const deleteContinueSignAwardEvent = (index: number) => {
    continueSignAwardTableData.data.splice(index, 1)
    formData.continue_award.splice(index, 1)
}

/**
 * 使用默认说明
 */
const defaultExplainEvent = () => {
    formData.rule_explain = t('ruleExplainDefault')
}
</script>

<style lang="scss" scoped>
    .input-width {
        width: 100px;
    }

    .textarea-width {
        width: 400px;
    }

    .el-form .form-tip {
        line-height: 1.5;
        margin-top: 5px;
    }

    .tutorial-btn {
        color: var(--el-color-primary);
    }
</style>

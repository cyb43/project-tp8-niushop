<template>
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">
            <el-page-header :content="formData.company_id ? t('updateCompany') : t('addCompany')" :icon="ArrowLeft" @back="back()" />
        </el-card>
        <el-card class="box-card mt-[15px] !border-none" shadow="never">
            <el-form :model="formData" label-width="150px" ref="formRef" :rules="formRules" class="page-form" v-loading="loading">
                <el-form-item :label="t('companyName')" prop="company_name">
                    <el-input v-model.trim="formData.company_name" maxlength="20" clearable :placeholder="t('companyNamePlaceholder')" class="input-width" />
                </el-form-item>
                <el-form-item :label="t('logo')">
                    <upload-image v-model="formData.logo" />
                </el-form-item>
                <el-form-item :label="t('url')">
                    <el-input v-model.trim="formData.url" clearable :placeholder="t('urlPlaceholder')" class="input-width" />
                </el-form-item>
                <el-form-item :label="t('expressNoKd100')">
                    <div>
                        <el-input v-model.trim="formData.kd100_express_no" clearable :placeholder="t('expressNoKd100Placeholder')" class="input-width" />
                        <p class="w-[380px] text-[12px] text-[#999] mt-[5px] leading-[20px]">{{ t('expressNoTips') }}</p>
                    </div>
                </el-form-item>
                <el-form-item :label="t('expressNo')">
                    <div>
                        <el-input v-model.trim="formData.express_no" clearable :placeholder="t('expressNoPlaceholder')" class="input-width" />
                        <p class="w-[380px] text-[12px] text-[#999] mt-[5px] leading-[20px]">{{ t('expressNoTips') }}</p>
                    </div>
                </el-form-item>
                <el-form-item :label="t('expressNoElectronicSheet')">
                    <div>
                        <el-input v-model.trim="formData.express_no_electronic_sheet" clearable :placeholder="t('expressNoElectronicSheetPlaceholder')" class="input-width" />
                        <p class="w-[380px] text-[12px] text-[#999] mt-[5px] leading-[20px]">{{ t('expressNoElectronicSheetTips') }}</p>
                    </div>
                </el-form-item>
                <el-form-item :label="t('electronicSheetSwitch')">
                    <el-switch v-model="formData.electronic_sheet_switch" :active-value="1" :inactive-value="0" />
                </el-form-item>

                <el-form-item :label="t('expType')" prop="exp_type" v-show="formData.electronic_sheet_switch">
                    <div class="w-[600px]">
                        <el-table :data="formData.exp_type" size="large" v-show="formData.exp_type.length">
                            <template #empty>
                                <span>{{ formData.exp_type.length == 0 ? t('emptyData') : '' }}</span>
                            </template>

                            <el-table-column prop="name" :label="t('expTypeName')" min-width="200">
                                <template #default="{ row }">
                                    <el-input v-model.trim="row.text" class="input-width" maxlength="20" clearable show-word-limit />
                                </template>
                            </el-table-column>

                            <el-table-column prop="name" :label="t('expTypeValue')" min-width="120">
                                <template #default="{ row }">
                                    <el-input v-model.trim="row.value" class="!w-[150px]" maxlength="6" clearable show-word-limit @keyup="filterNumber($event)" />
                                </template>
                            </el-table-column>

                            <el-table-column :label="t('operation')" fixed="right" align="right" min-width="60">
                                <template #default="{ row,$index }">
                                    <el-button type="primary" link @click="deleteExpTypeValueEvent($index)">
                                        {{ t('delete') }}
                                    </el-button>
                                </template>
                            </el-table-column>
                        </el-table>

                        <el-button type="primary" plain @click="addExpTypeValueEvent" :class="{'mt-[10px]': formData.exp_type.length}" v-show="formData.exp_type.length < expTypeMaxLength">{{ t('addExpType') }}</el-button>
                        <div class="text-[12px] text-[#999] mt-[5px] leading-[20px]">
                            <span>{{ t('expTypeTips') }}</span>
                            <a class="ml-[3px] text-[var(--el-color-primary)]" target="_blank" href="https://www.yuque.com/kdnjishuzhichi/dfcrg1/hgx758hom5p6wz0l">{{ t('examine') }}</a>
                        </div>
                        <p class="text-[12px] text-[#999] mt-[3px] leading-[20px]">{{ t('expTypeTips1') }}</p>
                    </div>
                </el-form-item>

                <el-form-item :label="t('printStyle')" prop="print_style" v-show="formData.electronic_sheet_switch">
                    <div class="w-[600px]">
                        <el-table :data="formData.print_style" size="large" v-show="formData.print_style.length">
                            <template #empty>
                                <span>{{ formData.print_style.length == 0 ? t('emptyData') : '' }}</span>
                            </template>

                            <el-table-column prop="name" :label="t('printStyleName')" min-width="200">
                                <template #default="{ row }">
                                    <el-input v-model.trim="row.template_name" class="input-width" maxlength="20" clearable show-word-limit />
                                </template>
                            </el-table-column>

                            <el-table-column prop="name" :label="t('printStyleId')" min-width="120">
                                <template #default="{ row }">
                                    <el-input v-model.trim="row.template_size" class="!w-[150px]" maxlength="6" clearable show-word-limit />
                                </template>
                            </el-table-column>

                            <el-table-column :label="t('operation')" fixed="right" align="right" min-width="60">
                                <template #default="{ row,$index }">
                                    <el-button type="primary" link @click="deletePrintStyleValueEvent($index)">{{ t('delete') }}</el-button>
                                </template>
                            </el-table-column>

                        </el-table>

                        <el-button type="primary" plain @click="addPrintStyleValueEvent" :class="{'mt-[10px]': formData.print_style.length}" v-show="formData.print_style.length < printStyleMaxLength">{{ t('addPrintStyle') }}</el-button>
                        <div class="text-[12px] text-[#999] mt-[5px] leading-[20px]">
                            <span>{{ t('printStyleTips') }}</span>
                            <a class="ml-[3px] text-[var(--el-color-primary)]" target="_blank" href="https://www.yuque.com/kdnjishuzhichi/dfcrg1/vpptucr1q5ahcxa7">{{ t('examine') }}</a>
                        </div>
                        <p class="text-[12px] text-[#999] mt-[3px] leading-[20px]">{{ t('printStyleTips1') }}</p>
                        <p class="text-[12px] text-[#999] mt-[3px] leading-[20px]">{{ t('printStyleTips2') }}</p>
                    </div>
                </el-form-item>

            </el-form>
        </el-card>
        <div class="fixed-footer-wrap">
            <div class="fixed-footer">
                <el-button type="primary" @click="save(formRef)">{{ t('save') }}</el-button>
                <el-button @click="back()">{{ t('back') }}</el-button>
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { reactive, ref, computed } from 'vue'
import { t } from '@/lang'
import { addCompany, editCompany, getCompanyInfo } from '@/addon/shop/api/delivery'
import { FormInstance } from 'element-plus'
import { ArrowLeft } from '@element-plus/icons-vue'
import { useRoute, useRouter } from 'vue-router'
import { filterNumber } from '@/utils/common'

const route = useRoute()
const router = useRouter()
const loading = ref(true)

const back = () => {
    router.push('/shop/order/delivery/company')
}

/**
 * 表单数据
 */
const initialFormData = {
    company_id: '',
    company_name: '',
    logo: '',
    url: '',
    kd100_express_no: '',
    express_no: '',
    express_no_electronic_sheet: "",
    print_style: [],
    exp_type: [],
    electronic_sheet_switch: 1
}

const printStyleMaxLength = ref(10)
const expTypeMaxLength = ref(10)

const formData: Record<string, any> = reactive({ ...initialFormData })
formData.company_id = ref(route.query.company_id)

const getCompanyInfoFn = () => {
    getCompanyInfo(formData.company_id).then(res => {
        loading.value = false;
        let data = res.data;
        if (data) {
            Object.keys(formData).forEach((key: string) => {
                if (data[key] != undefined) formData[key] = data[key]
            })
        }
    })
}
if (formData.company_id) {
    getCompanyInfoFn();
} else {
    loading.value = false;
}

const formRef = ref<FormInstance>()

// 表单验证规则
const formRules = computed(() => {
    return {
        company_name: [
            { required: true, message: t('companyNamePlaceholder'), trigger: 'blur' }
        ],
        exp_type: [
            {
                trigger: 'blur',
                validator: (rule: any, value: any, callback: any) => {
                    if (!value.length) {
                        callback()
                        return false;
                    }
                    let textArr = []; //业务名称集合
                    let valArr = []; //业务值集合
                    for (let i = 0; i < value.length; i++) {
                        if (!value[i].text) {
                            callback(new Error(t('expTypeTextTips')))
                            break;
                        } else if (value[i].text) {
                            textArr.push(value[i].text);
                        }
                        if (!value[i].value) {
                            callback(new Error(t('expTypeValueTips')))
                            break;
                        } else if (parseFloat(value[i].value) == 0) {
                            callback(new Error(t('expTypeValueNullTips')))
                            break;
                        } else if (value[i].value) {
                            valArr.push(value[i].value);
                        }
                    }
                    if (new Set(textArr).size !== textArr.length) {
                        callback(new Error(t('expTypeTextRepeatTips')))
                    }
                    if (new Set(valArr).size !== valArr.length) {
                        callback(new Error(t('expTypeValueRepeatTips')))
                    }
                    callback()
                }
            }
        ],
        print_style: [
            {
                trigger: 'blur',
                validator: (rule: any, value: any, callback: any) => {
                    if (!value.length) {
                        callback()
                        return false;
                    }
                    let nameArr = []; //业务名称集合
                    let sizeArr = []; //业务值集合
                    for (let i = 0; i < value.length; i++) {
                        if (!value[i].template_name) {
                            callback(new Error(t('printStyleNameTips')))
                            break;
                        } else if (value[i].template_name) {
                            nameArr.push(value[i].template_name);
                        }
                        if (!value[i].template_size) {
                            callback(new Error(t('printStyleSizeTips')))
                            break;
                        } else if (value[i].template_size) {
                            sizeArr.push(value[i].template_size);
                        }
                    }
                    if (new Set(nameArr).size !== nameArr.length) {
                        callback(new Error(t('printStyleNameRepeatTips')))
                    }
                    if (new Set(sizeArr).size !== sizeArr.length) {
                        callback(new Error(t('printStyleSizeRepeatTips')))
                    }
                    callback()
                }
            }
        ]
    }
})

// 添加模版样式
const addPrintStyleValueEvent = () => {
    formData.print_style.push({
        template_name: '',
        template_size: ''
    })
}

// 添加业务类型
const addExpTypeValueEvent = () => {
    formData.exp_type.push({
        text: '',
        value: ''
    })
}

// 删除模版样式
const deletePrintStyleValueEvent = (index: any) => {
    formData.print_style.splice(index, 1)
}

// 删除业务类型
const deleteExpTypeValueEvent = (index: any) => {
    formData.exp_type.splice(index, 1)
}

/**
 * 确认
 * @param formEl
 */
const repeat = ref(false)
const save = async(formEl: FormInstance | undefined) => {
    if (repeat.value || !formEl) return
    const api = formData.company_id ? editCompany : addCompany

    await formEl.validate(async(valid) => {

        if (valid) {
            repeat.value = true

            const data = formData
            api(data).then(res => {
                router.push('/shop/order/delivery/company')
                repeat.value = false
            }).catch(() => {
                repeat.value = false
            })
        }
    })
}

</script>

<style lang="scss" scoped>
</style>

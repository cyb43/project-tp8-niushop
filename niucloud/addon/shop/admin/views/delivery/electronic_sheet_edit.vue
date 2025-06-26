<template>
    <div class="main-container">
        <el-card class="card !border-none mb-[15px]" shadow="never">
            <el-page-header :content="pageName" :icon="ArrowLeft" @back="back" />
        </el-card>

        <el-form class="page-form" :model="formData" :rules="formRules" label-width="150px" ref="formRef" v-loading="loading">
            <el-card class="box-card !border-none" shadow="never">

                <h3 class="panel-title !text-sm">{{ t('basicSettings') }}</h3>
                <el-form-item :label="t('templateName')" prop="template_name">
                    <el-input v-model.trim="formData.template_name" clearable :placeholder="t('templateNamePlaceholder')" class="input-width" maxlength="30" />
                </el-form-item>

                <el-form-item :label="t('expressCompany')" prop="express_company_id">
                    <el-select v-model="formData.express_company_id" :placeholder="t('expressCompanyPlaceholder')" clearable @change="handleSelectCompanyChange">
                        <el-option v-for="item in companyList" :key="item.company_id" :label="item.company_name" :value="item.company_id" />
                    </el-select>
                </el-form-item>

                <el-form-item :label="t('expType')" prop="exp_type" v-show="expTypeList.length">
                    <el-radio-group v-model="formData.exp_type">
                        <el-radio v-for="(item,index) in expTypeList" :key="index" :value="item.value">{{ item.text }}</el-radio>
                    </el-radio-group>
                </el-form-item>

                <el-form-item :label="t('printStyle')" v-show="printStyleList.length">
                    <div>
                        <el-select v-model="formData.print_style" :placeholder="t('printStylePlaceholder')" clearable>
                            <el-option v-for="(item,index) in printStyleList" :key="index" :label="item.template_name" :value="item.template_size" />
                        </el-select>
                        <div class="text-[12px] text-[#999] mt-[3px] leading-[20px]">{{ t('printStyleTips1') }}</div>
                        <div class="text-[12px] text-[#999] mt-[3px] leading-[20px]">{{ t('printStyleTips2') }}</div>
                    </div>
                </el-form-item>

            </el-card>
            <el-card class="box-card !border-none" shadow="never">
                <h3 class="panel-title !text-sm">{{ t('otherSettings') }}</h3>

                <el-form-item :label="t('customerName')">
                    <div>
                        <el-input v-model.trim="formData.customer_name" clearable class="input-width" maxlength="20" />
                        <div class="flex items-center mt-[5px] text-[12px] text-[#999] leading-[20px]">
                            <span>{{ t('customerNameTips') }}</span>
                            <a class="ml-[3px] text-[var(--el-color-primary)]" target="_blank" href="https://www.yuque.com/kdnjishuzhichi/rg4owd">{{ t('examine') }}</a>
                        </div>
                        <div class="flex items-center mt-[3px] text-[12px] text-[#999] leading-[20px]">
                            <span>{{ t('customerNameTips1') }}</span>
                            <a class="ml-[3px] text-[var(--el-color-primary)]" target="_blank" href="https://www.yuque.com/kdnjishuzhichi/dfcrg1/hrfw43">{{ t('examine') }}</a>
                        </div>
                    </div>
                </el-form-item>

                <el-form-item :label="t('customerPwd')">
                    <div>
                        <el-input v-model.trim="formData.customer_pwd" clearable class="input-width" maxlength="50" />
                        <div class="mt-[5px] text-[12px] text-[#999] leading-[20px]">{{ t('customerPwdTips') }}</div>
                    </div>
                </el-form-item>

                <el-form-item :label="t('sendSite')">
                    <div>
                        <el-input v-model.trim="formData.send_site" clearable class="input-width" maxlength="20" />
                        <div class="mt-[5px] text-[12px] text-[#999] leading-[20px]">{{ t('sendSiteTips') }}</div>
                    </div>
                </el-form-item>

                <el-form-item :label="t('sendStaff')">
                    <div>
                        <el-input v-model.trim="formData.send_staff" clearable class="input-width" maxlength="20" />
                        <div class="mt-[5px] text-[12px] text-[#999] leading-[20px]">{{ t('sendStaffTips') }}</div>
                    </div>
                </el-form-item>

                <el-form-item :label="t('monthCode')">
                    <div>
                        <el-input v-model.trim="formData.month_code" clearable class="input-width" maxlength="20" />
                        <div class="mt-[5px] text-[12px] text-[#999] leading-[20px]">{{ t('monthCodeTips') }}</div>
                    </div>
                </el-form-item>

                <el-form-item :label="t('payType')">
                    <el-radio-group v-model="formData.pay_type">
                        <el-radio v-for="(item,index) in payType" :value="parseInt(index)">{{ item }}</el-radio>
                    </el-radio-group>
                </el-form-item>

                <el-form-item :label="t('isNotice')">
                    <div>
                        <el-radio-group v-model="formData.is_notice">
                            <el-radio :value="1">{{ t('yes') }}</el-radio>
                            <el-radio :value="0">{{ t('no') }}</el-radio>
                        </el-radio-group>
                        <div class="mt-[5px] text-[12px] text-[#999] leading-[20px]">{{ t('isNoticeTips') }}</div>
                    </div>
                </el-form-item>

                <el-form-item :label="t('status')">
                    <el-switch v-model="formData.status" :active-value="1" :inactive-value="0" />
                </el-form-item>

                <el-form-item :label="t('isDefault')">
                    <el-switch v-model="formData.is_default" :active-value="1" :inactive-value="0" />
                </el-form-item>

            </el-card>
        </el-form>

        <div class="fixed-footer-wrap">
            <div class="fixed-footer">
                <el-button type="primary" :loading="repeat" @click="confirm(formRef)">{{ t('save') }}</el-button>
                <el-button @click="back()">{{ t('cancel') }}</el-button>
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { ref, reactive, computed } from 'vue'
import { t } from '@/lang'
import { ArrowLeft } from "@element-plus/icons-vue"
import type { FormInstance } from 'element-plus'
import { useRoute, useRouter } from 'vue-router'
import {
    addElectronicSheet,
    editElectronicSheet,
    getElectronicSheetInfo,
    getElectronicSheetPayType
} from '@/addon/shop/api/electronic_sheet'
import { getCompanyList } from '@/addon/shop/api/delivery'

const loading = ref(false)
const route = useRoute()
const router = useRouter()
const repeat = ref(false)

const pageName = route.meta.title

/**
 * 表单数据
 */
const initialFormData: any = {
    id: route.query.id || 0,
    template_name: '',
    express_company_id: '',
    customer_name: '',
    customer_pwd: '',
    send_site: '',
    send_staff: '',
    month_code: '',
    pay_type: 1,
    is_notice: 0,
    status: 1,
    exp_type: 1,
    print_style: '',
    is_default: ''
}

const formData: Record<string, any> = reactive({ ...initialFormData })

const formRef = ref<FormInstance>()

// 表单验证规则
const formRules = computed(() => {
    return {
        template_name: [
            { required: true, message: t('templateNamePlaceholder'), trigger: 'blur' },
        ],
        express_company_id: [
            { required: true, message: t('expressCompanyPlaceholder'), trigger: 'blur' },
        ]
    }
})

const companyList: any = ref([]) // 物流公司列表
const expTypeList: any = ref([]) // 业务类型
const printStyleList: any = ref([]) // 模版样式
const payType = ref([])

const init = async() => {
    getElectronicSheetPayType().then((res: any) => {
        payType.value = res.data;
    })

    await getCompanyList({ electronic_sheet_switch: 1 }).then((res: any) => {
        companyList.value = res.data;
    })

    if (formData.id) {
        loading.value = true
        getElectronicSheetInfo(formData.id).then((res: any) => {
            let data = res.data;
            if (data) Object.keys(formData).forEach((key: string) => {
                if (data[key] != undefined) formData[key] = data[key]
            })
            loading.value = false
            handleSelectCompanyChange(formData.express_company_id, true)
        })
    }
}

init();

const handleSelectCompanyChange = (value: any, load: any = false) => {
    if (!value) {
        expTypeList.value = [];
        printStyleList.value = [];
        return;
    }

    for (let i = 0; i < companyList.value.length; i++) {
        if (companyList.value[i].company_id == value) {
            expTypeList.value = companyList.value[i].exp_type;
            expTypeList.value.forEach((item: any) => {
                if (item.value) item.value = parseInt(item.value);
            })
            printStyleList.value = companyList.value[i].print_style;

            if (!load) {
                if (expTypeList.value.length) {
                    formData.exp_type = expTypeList.value[0].value
                } else {
                    formData.exp_type = 1; // 默认为1
                }
                if (printStyleList.value.length) {
                    formData.print_style = printStyleList.value[0].value
                } else {
                    formData.print_style = ''; // 默认为空
                }
            }
            break;
        }
    }
}

/**
 * 确认
 * @param formEl
 */
const confirm = async(formEl: FormInstance | undefined) => {
    if (loading.value || !formEl) return
    let save = formData.id ? editElectronicSheet : addElectronicSheet

    await formEl.validate(async(valid) => {
        if (valid) {

            if (repeat.value) return
            repeat.value = true

            let data = formData

            save(data).then(res => {
                repeat.value = false
                if (!formData.id) {
                    router.push('/shop/delivery/electronic_sheet')
                }
            }).catch(err => {
                repeat.value = false
            })
        }
    })
}

const back = () => {
    router.push('/shop/delivery/electronic_sheet')
}
</script>

<style lang="scss" scoped></style>

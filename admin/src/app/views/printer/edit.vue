<template>
    <div class="main-container">
        <el-card class="card !border-none mb-[15px]" shadow="never">
            <el-page-header :content="pageName" :icon="ArrowLeft" @back="back" />
        </el-card>

        <el-form class="page-form" :model="formData" :rules="formRules" label-width="150px" ref="formRef"
                 v-loading="loading">
            <el-card class="box-card !border-none" shadow="never">

                <h3 class="panel-title !text-sm">{{ t('printerSet') }}</h3>

                <el-form-item :label="t('printerName')" prop="printer_name">
                    <el-input v-model.trim="formData.printer_name" clearable :placeholder="t('printerNamePlaceholder')" class="input-width" maxlength="20" />
                </el-form-item>

                <el-form-item :label="t('brand')" prop="brand">
                    <el-select v-model="formData.brand" :placeholder="t('brandPlaceholder')" clearable>
                        <el-option v-for="(item,key) in brandList" :key="key" :label="item" :value="key" />
                    </el-select>
                </el-form-item>

                <el-form-item :label="t('printerCode')" prop="printer_code">
                    <div>
                        <el-input v-model.trim="formData.printer_code" clearable :placeholder="t('printerCodePlaceholder')" class="input-width" maxlength="30" />
                        <p class="text-[12px] text-[#b2b2b2]">{{ t('printerCodeTips') }}</p>
                    </div>
                </el-form-item>

                <el-form-item :label="t('printerKey')" prop="printer_key">
                    <div>
                        <el-input v-model.trim="formData.printer_key" clearable :placeholder="t('printerKeyPlaceholder')" class="input-width" maxlength="30" />
                        <p class="text-[12px] text-[#b2b2b2]">{{ t('printerKeyTips') }}</p>
                    </div>
                </el-form-item>

                <el-form-item :label="t('openId')" prop="open_id">
                    <div>
                        <el-input v-model.trim="formData.open_id" clearable :placeholder="t('openIdPlaceholder')" class="input-width" maxlength="30" />
                        <p class="text-[12px] text-[#b2b2b2]">{{ t('openIdTips') }}</p>
                    </div>
                </el-form-item>

                <el-form-item :label="t('apikey')" prop="apikey">
                    <div>
                        <el-input v-model.trim="formData.apikey" clearable :placeholder="t('apikeyPlaceholder')" class="input-width" maxlength="60" />
                        <p class="text-[12px] text-[#b2b2b2]">{{ t('apikeyTips') }}</p>
                    </div>
                </el-form-item>

                <el-form-item :label="t('printWidth')" prop="print_width">
                    <el-radio-group v-model="formData.print_width">
                        <el-radio label="58mm">58mm</el-radio>
                    </el-radio-group>
                </el-form-item>

                <el-form-item :label="t('status')">
                    <el-switch v-model="formData.status" :active-value="1" :inactive-value="0" />
                </el-form-item>

            </el-card>
            <el-card v-for="(item,index) in printerType" :key="item.key" class="box-card !border-none" shadow="never">
                <h3 class="panel-title !text-sm">{{ item.title }}</h3>

                <div class="flex mb-[10px] py-[8px] bg-[#F5F7F9] text-[14px]">
                    <div class="px-[12px] w-[200px]">{{ t('printTrigger') }}</div>
                    <div class="px-[12px] w-[100px]">{{ t('status') }}</div>
                    <div class="px-[12px] w-[250px]">{{ t('usePrintTemplate') }}</div>
                    <div class="px-[12px] w-[300px] flex-1" v-for="childItem in item.condition" :key="childItem.key">{{ childItem.title }}</div>
                    <div class="px-[12px] w-[200px]">{{ t('printNum') }}</div>
                </div>

                <template v-if="item.trigger">
                    <div class="flex bg-[#f8f8f9] mb-[10px] py-[20px]" v-for="(triggerItem,triggerKey) in item.trigger" :key="triggerKey">
                        <template v-if="formData.value[item.key]['trigger_' + triggerKey]">
                            <div class="font-bold w-[200px] px-[12px]">{{ triggerItem }}</div>
                            <div class="w-[100px] px-[12px]">
                                <el-switch v-model="formData.value[item.key]['trigger_' + triggerKey].status" :active-value="1" :inactive-value="0" />
                            </div>
                            <div class="w-[250px] px-[12px]">
                                <el-select v-model="formData.value[item.key]['trigger_' + triggerKey].template_id" :placeholder="t('请选择小票打印模板')" clearable>
                                    <el-option v-for="templateItem in templateList[item.key]" :key="templateItem.template_id" :label="templateItem.template_name" :value="templateItem.template_id" />
                                </el-select>
                            </div>
                            <template v-for="childItem in item.condition">
                                <div class="w-[300px] px-[12px] flex-1" v-if="childItem.type == 'checkbox'">
                                    <el-checkbox-group
                                        v-model="formData.value[item.key]['trigger_' + triggerKey][childItem.key]">
                                        <el-checkbox v-for="(checkboxItem, index) in childItem.list" :label="checkboxItem.value" :key="index">{{ checkboxItem.name }}
                                        </el-checkbox>
                                    </el-checkbox-group>
                                </div>
                            </template>
                            <div class="w-[200px] px-[12px]">
                                <el-select v-model="formData.value[item.key]['trigger_' + triggerKey].print_num">
                                    <el-option label="1联" :value="1" />
                                    <el-option label="2联" :value="2" />
                                    <el-option label="3联" :value="3" />
                                    <el-option label="4联" :value="4" />
                                </el-select>
                            </div>
                        </template>
                    </div>
                </template>

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
import { FormInstance, ElMessage } from 'element-plus'
import { useRoute, useRouter } from 'vue-router'
import { deepClone } from '@/utils/common';
import {
    addPrinter,
    editPrinter,
    getPrinterInfo,
    getPrinterType,
    getPrinterBrand,
    getPrinterTemplateList
} from '@/app/api/printer'

const route = useRoute()
const router = useRouter()
const repeat = ref(false)
const loading = ref(true)

const pageName = route.meta.title

/**
 * 表单数据
 */
const initialFormData: any = {
    printer_id: route.query.printer_id || 0,
    brand: '',
    printer_name: '',
    printer_code: '',
    printer_key: '',
    open_id: '',
    apikey: '',
    template_type: [],
    trigger: [],
    value: {},
    print_width: '58mm',
    status: 1,
}

const formData: Record<string, any> = reactive({ ...initialFormData })

const formRef = ref<FormInstance>()

// 表单验证规则
const formRules = computed(() => {
    return {
        printer_name: [
            { required: true, message: t('printerNamePlaceholder'), trigger: 'blur' },
        ],
        brand: [
            { required: true, message: t('brandPlaceholder'), trigger: 'blur' },
        ],
        printer_code: [
            { required: true, message: t('printerCodePlaceholder'), trigger: 'blur' },
        ],
        printer_key: [
            { required: true, message: t('printerKeyPlaceholder'), trigger: 'blur' },
        ],
        open_id: [
            { required: true, message: t('openIdPlaceholder'), trigger: 'blur' },
        ],
        apikey: [
            { required: true, message: t('apikeyPlaceholder'), trigger: 'blur' },
        ]
    }
})

const printerType = ref([])

const init = async() => {
    await getPrinterType({}).then((res: any) => {
        if (res.data) {
            printerType.value = res.data;

            for (let i = 0; i < printerType.value.length; i++) {
                let item: any = printerType.value[i];

                formData.value[item.key] = {};

                let extendData: any = {};
                for (let ci = 0; ci < item.condition.length; ci++) {
                    let condition = item.condition[ci];
                    extendData[condition.key] = [];
                    if (condition.type == 'checkbox') {
                        extendData[condition.key] = []; // 默认全选
                        for (let k = 0; k < condition.list.length; k++) {
                            extendData[condition.key].push(condition.list[k].value);
                        }
                    }
                }

                for (let key in item.trigger) {
                    formData.value[item.key]['trigger_' + key] = {
                        status: 1,
                        template_id: '',
                        print_num: 1,
                    };
                    Object.assign(formData.value[item.key]['trigger_' + key], deepClone(extendData));
                }
            }

        }
        if (!formData.printer_id) {
            loading.value = false
        }
    })

    if (formData.printer_id) {
        getPrinterInfo(formData.printer_id).then((res: any) => {
            let data = res.data;
            if (data) Object.keys(formData).forEach((key: string) => {
                if (data[key] != undefined) {
                    if (key == 'value') {
                        for (let ck in formData[key]) {
                            Object.assign(formData[key][ck], data[key][ck])
                        }
                    } else {
                        formData[key] = data[key]
                    }
                }
            })
            loading.value = false
        })
    }
}

init()

const brandList = ref([])
getPrinterBrand({}).then((res: any) => {
    brandList.value = res.data;
})

const templateList: any = ref({}) // 小票打印模板列表
getPrinterTemplateList({}).then((res: any) => {
    if (res.data) {
        let data = res.data;
        for (let i = 0; i < data.length; i++) {
            let item = data[i];
            if (templateList.value[item.template_type] == undefined) {
                templateList.value[item.template_type] = []
            }
            templateList.value[item.template_type].push({
                template_id: item.template_id,
                template_name: item.template_name,
            })
        }
    }
})

/**
 * 确认
 * @param formEl
 */
const confirm = async(formEl: FormInstance | undefined) => {
    if (loading.value || !formEl) return

    if (printerType.value.length == 0) {
        ElMessage({
            type: 'warning',
            message: t('printTypeEmpty')
        })
        return;
    }

    let save = formData.printer_id ? editPrinter : addPrinter

    await formEl.validate(async(valid) => {
        if (valid) {

            let validateFlag = false;
            let validateMessage = '';

            for (let i = 0; i < printerType.value.length; i++) {
                let item: any = printerType.value[i];

                for (let k = 0; k < Object.keys(item.trigger).length; k++) {
                    let triggerItem = Object.keys(item.trigger)[k];
                    if (formData.value[item.key]['trigger_' + triggerItem].status == 0) {
                        continue;
                    }

                    if (!formData.value[item.key]['trigger_' + triggerItem].template_id) {
                        validateFlag = true;
                        validateMessage = `请设置${ item.title }[${ item.trigger[triggerItem] }]的小票打印模板`
                        break;
                    }

                    let isFail = false;
                    for (let ck = 0; ck < item.condition.length; ck++) {
                        let condition = item.condition[ck];
                        if (condition.type == 'checkbox') {
                            if (formData.value[item.key]['trigger_' + triggerItem][condition.key].length == 0) {
                                validateFlag = true;
                                validateMessage = `请设置${ item.title }[${ item.trigger[triggerItem] }]的${ condition.title }`
                                isFail = true;
                                break;
                            }
                        }
                    }

                    // 防止重复循环
                    if (isFail) {
                        break;
                    }

                }

                // 防止重复循环
                if (validateFlag) {
                    break;
                }
            }

            if (validateFlag) {
                ElMessage({
                    type: 'warning',
                    message: validateMessage
                })
                return;
            }

            formData.template_type = [];
            formData.trigger = [];

            for (let key in formData.value) {
                for (let childKey in formData.value[key]) {
                    formData.trigger.push(key + '_' + childKey);
                }
                formData.template_type.push(key)
            }

            if (repeat.value) return
            repeat.value = true

            let data = formData

            save(data).then(res => {
                repeat.value = false
                if (!formData.printer_id) {
                    router.push('/printer/list')
                }
            }).catch(err => {
                repeat.value = false
            })
        }
    })
}

const back = () => {
    router.push('/printer/list')
}
</script>

<style lang="scss" scoped></style>

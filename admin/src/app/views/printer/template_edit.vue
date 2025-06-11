<template>
    <div class="main-container">
        <el-card class="card !border-none mb-[15px]" shadow="never">
            <el-page-header :content="pageName" :icon="ArrowLeft" @back="back" />
        </el-card>

        <el-form class="page-form" :model="formData" :rules="formRules" label-width="150px" ref="formRef" v-loading="loading">

            <div class="flex">

                <div class="flex-1 mr-[20px] bg-[#fff]">

                    <el-card class="box-card !border-none" shadow="never">

                        <h3 class="panel-title !text-sm">{{ t('templateInfoLabel') }}</h3>

                        <el-form-item :label="t('templateName')" prop="template_name">
                            <el-input v-model.trim="formData.template_name" clearable :placeholder="t('templateNamePlaceholder')" class="input-width" maxlength="20" />
                        </el-form-item>

                        <el-form-item :label="t('templateType')" prop="template_type" v-if="printerType.length">
                            <el-radio-group v-model="formData.template_type">
                                <el-radio v-for="item in printerType" :key="item.key" :label="item.key" @change="handlePrintType">{{ item.title }}</el-radio>
                            </el-radio-group>
                        </el-form-item>

                    </el-card>
                    <el-card class="box-card !border-none" shadow="never" v-if="printerType.length">

                        <h3 class="panel-title !text-sm">{{ t('templateEditLabel') }}</h3>

                        <div v-for="item in template" :key="item.key" class="bg-[#f8f8f9] mb-[20px] py-[20px] px-[40px] text-[14px]">
                            <h4 class="panel-title !text-sm">{{ item.title }}</h4>
                            <div v-for="(childItem,index) in item.list" :key="childItem.key" class="ml-[30px]" :style="{ 'margin-bottom' : item.list.length == (index + 1) ? '0' : '20px' }">
                                <div class="flex">
                                    <el-checkbox v-model="formData.value[item.key][childItem.key].status"
                                                 v-if="childItem.label" :label="childItem.label"
                                                 :value="childItem.status" :true-value="1" :false-value="0"
                                                 class="w-[180px] mr-[10px]" :disabled="childItem.disabled" />

                                    <template v-if="childItem.type == 'input'">
                                        <el-input v-model.trim="formData.value[item.key][childItem.key].value" clearable
                                                  :placeholder="'请输入' + (childItem.placeholder ? childItem.placeholder : childItem.label)"
                                                  class="input-width mr-[30px]" maxlength="32" />
                                    </template>

                                    <template v-if="childItem.type == 'checkbox'">
                                        <el-checkbox-group v-model="formData.value[item.key][childItem.key].value" class="mr-[30px]">
                                            <el-checkbox v-for="(checkboxItem, key) in childItem.list" :label="key" :key="key" :disabled="childItem.disabled">{{ checkboxItem }}</el-checkbox>
                                        </el-checkbox-group>
                                    </template>

                                    <template v-if="childItem.type == 'select'">

                                        <div class="leading-[30px] w-[50px] text-center text-[#707070] bg-[#d7d7d7] border-1 border-solid border-[#ededed]">{{ childItem.text }}</div>
                                        <el-select v-model="formData.value[item.key][childItem.key].value" class="!w-[130px] mr-[30px]">
                                            <el-option v-for="(item,key) in childItem.list" :key="key" :label="item" :value="key" />
                                        </el-select>

                                    </template>

                                    <template v-if="childItem.fontSize">
                                        <div class="flex mr-[30px]">
                                            <div class="leading-[30px] w-[50px] text-center text-[#707070] bg-[#d7d7d7] border-1 border-solid border-[#ededed]">字号</div>
                                            <el-select v-model="formData.value[item.key][childItem.key].fontSize" class="!w-[130px]">
                                                <el-option label="小" value="normal" />
                                                <!--<el-option label="小" value="small" />-->
                                                <el-option label="大" value="big" />
                                            </el-select>
                                        </div>

                                    </template>

                                    <template v-if="childItem.fontWeight">

                                        <div class="flex mr-[30px]">
                                            <div class="leading-[30px] w-[50px] text-center text-[#707070] bg-[#d7d7d7] border-1 border-solid border-[#ededed]">粗细</div>
                                            <el-select v-model="formData.value[item.key][childItem.key].fontWeight" class="!w-[130px]">
                                                <el-option label="正常" value="normal" />
                                                <el-option label="加粗" value="bold" />
                                            </el-select>
                                        </div>
                                    </template>

                                </div>

                                <div v-if="childItem.remark" class="text-[12px] text-[#b2b2b2] mt-[10px]">{{ childItem.remark }}</div>

                            </div>

                        </div>

                    </el-card>
                </div>

                <el-card class="box-card !border-none w-[450px]" shadow="never">

                    <h3 class="panel-title !text-sm">{{ t('preview') }}</h3>

                    <!-- 动态加载组件 -->
                    <component :is="modules[previewPath]" :value="formData.value" />

                </el-card>

            </div>

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
import { getPrinterType, addPrinterTemplate, editPrinterTemplate, getPrinterTemplateInfo } from '@/app/api/printer'

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title
const repeat = ref(false)
const loading = ref(true)

/**
 * 表单数据
 */
const initialFormData: any = {
    template_id: route.query.template_id || 0,
    template_type: '',
    template_name: '',
    value: {},
}

const formData: Record<string, any> = reactive({ ...initialFormData })

const formRef = ref<FormInstance>()

// 表单验证规则
const formRules = computed(() => {
    return {
        template_name: [
            { required: true, message: t('templateNamePlaceholder'), trigger: 'blur' },
        ],
        template_type: [
            { required: true, message: t('templateTypePlaceholder'), trigger: 'blur' },
        ]
    }
})

// 动态加载组件
const modulesFiles = import.meta.glob('./components/*.vue', { eager: true })
const addonModulesFiles = import.meta.glob('@/addon/**/views/printer/components/*.vue', { eager: true })
addonModulesFiles && Object.assign(modulesFiles, addonModulesFiles)

const modules: any = {}
for (const [key, value] of Object.entries(modulesFiles)) {
    const moduleName: any = key.split('/').pop()
    const name = moduleName.split('.')[0]
    modules[name] = value.default
}

const previewPath = ref('')
const printerType: any = ref([])
const template: any = ref([])

const init = async() => {
    await getPrinterType({}).then((res: any) => {
        if (res.data && res.data.length) {
            printerType.value = res.data;
            handlePrintType(printerType.value[0].key, Boolean(parseInt(formData.template_id)));
        }
        if (!formData.template_id) {
            loading.value = false
        }
    })

    if (formData.template_id) {
        getPrinterTemplateInfo(formData.template_id).then((res: any) => {
            let data = res.data;
            if (data && Object.keys(data).length) {
                Object.keys(formData).forEach((key: string) => {
                    if (key == 'value') {
                        for (let ck in formData[key]) {
                            Object.assign(formData[key][ck], data[key][ck])
                        }
                    } else {
                        formData[key] = data[key]
                    }
                })
                loading.value = false
            } else {
                ElMessage({
                    type: 'warning',
                    duration: 1500,
                    message: t('printTemplateEmpty')
                })
                setTimeout(() => {
                    back();
                    loading.value = false
                }, 2000);
            }
        })
    }
};

init();

// 切换模板类型
const handlePrintType = (value: any, load: boolean = false) => {
    for (let i = 0; i < printerType.value.length; i++) {
        if (printerType.value[i].key == value) {
            formData.template_type = printerType.value[i].key;
            previewPath.value = printerType.value[i].path;
            template.value = printerType.value[i].template;
            break;
        }
    }

    // 清空模板内容数据
    for (let key in formData.value) {
        delete formData.value[key];
    }

    refreshTemplateData();
}

// 刷新模板内容数据
const refreshTemplateData = () => {
    for (let i = 0; i < template.value.length; i++) {
        let item: any = template.value[i];
        formData.value[item.key] = {};
        for (let k = 0; k < item.list.length; k++) {
            let childItem = item.list[k];
            formData.value[item.key][childItem.key] = {
                type: childItem.type,
                value: childItem.value,
                status: childItem.status,
                fontSize: childItem.fontSize,
                fontWeight: childItem.fontWeight,
            };
        }
    }
}

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

    let save = formData.template_id ? editPrinterTemplate : addPrinterTemplate

    await formEl.validate(async(valid) => {
        if (valid) {

            let validateFlag = false;
            let validateMessage = '';

            for (let i = 0; i < template.value.length; i++) {

                let item: any = template.value[i];
                let isFail = false;

                for (let k = 0; k < item.list.length; k++) {
                    let childItem = item.list[k];
                    if (formData.value[item.key][childItem.key].status == 0) {
                        continue;
                    }
                    if (childItem.type == 'input') {
                        if (formData.value[item.key][childItem.key].value == '') {
                            validateFlag = true;
                            validateMessage = `请输入${ childItem.label }`;
                            isFail = true;
                            break;
                        }
                    } else if (childItem.type == 'select') {
                        if (formData.value[item.key][childItem.key].value == '') {
                            validateFlag = true;
                            validateMessage = `${ childItem.label }未设置[${ childItem.text }]`;
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

            if (validateFlag) {
                ElMessage({
                    type: 'warning',
                    message: validateMessage
                })
                return;
            }

            for (let i = 0; i < template.value.length; i++) {

                let item: any = template.value[i];

                for (let k = 0; k < item.list.length; k++) {
                    let childItem = item.list[k];
                    if (childItem.type == 'checkbox') {
                        if (formData.value[item.key][childItem.key].value.length) {
                            formData.value[item.key][childItem.key].status = 1;
                        } else {
                            formData.value[item.key][childItem.key].status = 0;
                        }
                    }
                }
            }

            if (repeat.value) return
            repeat.value = true

            let data = formData

            save(data).then(res => {
                repeat.value = false
                if (!formData.template_id) {
                    back()
                }
            }).catch(err => {
                repeat.value = false
            })
        }
    })
}

const back = () => {
    router.push('/printer/template/list')
}
</script>

<style lang="scss" scoped>
</style>

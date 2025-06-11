<template>
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never">

            <div class="flex justify-between items-center mb-[5px] h-[32px]">
                <span class="text-lg">{{ pageName }}</span>
            </div>

            <el-tabs model-value="/shop/delivery/electronic_sheet/config" @tab-change="handleClick">
                <el-tab-pane :label="t('tabESTemplate')" name="/shop/delivery/electronic_sheet" />
                <el-tab-pane :label="t('tabESConfig')" name="/shop/delivery/electronic_sheet/config" />
            </el-tabs>

            <el-form class="page-form" :model="formData" :rules="formRules" label-width="150px" ref="formRef"
                     v-loading="loading">
                <el-card class="box-card !border-none" shadow="never">
                    <h3 class="panel-title !text-sm">{{ t('apiSet') }}</h3>

                    <el-form-item :label="t('interfaceType')" prop="interface_type">
                        <div>
                            <el-radio-group v-model="formData.interface_type">
                                <el-radio label="kdbird" size="large">{{ t('kdn') }}</el-radio>
                            </el-radio-group>
                            <template v-if="formData.interface_type == 'kdbird'">
                                <p class="text-[12px] text-[#b2b2b2]">
                                    {{ t('promptTips1-1') }}
                                    <el-button class="button-size" type="primary" link @click="kdnEvent('https://www.kdniao.com')">https://www.kdniao.com</el-button>
                                </p>
                            </template>
                        </div>
                    </el-form-item>
                    <div v-if="formData.interface_type == 'kdbird'">

                        <el-form-item :label="t('kdnEBusinessIDLabel')" class="input-item">
                            <div>
                                <el-input v-model.trim="formData.kdniao_id" :placeholder="t('kdnEBusinessIDPlaceholder')" class="input-width" clearable />
                                <p class="text-[12px] text-[#b2b2b2]">{{ t('kdnEBusinessIDTips') }}</p>
                            </div>
                        </el-form-item>

                        <el-form-item label="API key" class="input-item">
                            <div>
                                <el-input v-model.trim="formData.kdniao_api_key" clearable :placeholder="t('kdnAppKeyPlaceholder')" class="input-width" />
                                <p class="text-[12px] text-[#b2b2b2]">{{ t('kdnAppKeyTips') }}</p>
                            </div>
                        </el-form-item>

                    </div>

                </el-card>

                <el-card class="box-card !border-none" shadow="never">
                    <h3 class="panel-title !text-sm">{{ t('printerSet') }}</h3>

                    <el-alert type="warning" :closable="false" class="!mb-[10px]">
                        <template #default>
                            <p>用双端口加载主JS文件Lodop.js（或CLodopfuncs.js兼容老版本）以防其中某端口被占</p>
                            <p>HTTP推荐端口：8000/18000，HTTPS推荐端口：8443</p>
                            <p>1. 请将打印机连接至本机。 </p>
                            <p>2. 在本机上安装打印控件。下载链接：<a href="http://www.lodop.net/download.html" target="_blank" class="text-primary">http://www.lodop.net/download.html</a></p>
                            <p>3. 将打印控件中的打印端口下面的打印端口设为相同。</p>
                        </template>
                    </el-alert>

                    <el-form-item :label="t('serverPort1')" class="input-item-required" prop="server_port1">
                        <div>
                            <el-input v-model.trim="formData.server_port1" :placeholder="t('serverPort1Placeholder')" class="input-width" clearable />
                        </div>
                    </el-form-item>

                    <el-form-item :label="t('serverPort2')" class="input-item-required" prop="server_port2">
                        <div>
                            <el-input v-model.trim="formData.server_port2" :placeholder="t('serverPort2Placeholder')" class="input-width" clearable />
                        </div>
                    </el-form-item>

                    <el-form-item :label="t('httpsPort')" class="input-item-required" prop="https_port">
                        <div>
                            <el-input v-model.trim="formData.https_port" :placeholder="t('httpsPortPlaceholder')" class="input-width" clearable />
                        </div>
                    </el-form-item>

                </el-card>
            </el-form>

            <div class="fixed-footer-wrap">
                <div class="fixed-footer">
                    <el-button type="primary" :loading="loading" @click="save(formRef)">{{ t('save') }}</el-button>
                </div>
            </div>
        </el-card>

    </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import { FormInstance, FormRules } from 'element-plus'
import { useRoute, useRouter } from 'vue-router'
import { setElectronicSheetConfig, getElectronicSheetConfig } from '@/addon/shop/api/electronic_sheet'

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title;
const loading = ref(true)

const handleClick = (path: string) => {
    router.push({ path })
}

const formData: any = reactive({
    interface_type: 'kdbird',
    kdniao_id: '',
    kdniao_api_key: '',
    server_port1: '8000',
    server_port2: '18000',
    https_port: '8443'
})

const setFormData = async() => {
    const data = await (await getElectronicSheetConfig()).data
    Object.keys(formData).forEach((key: string) => {
        if (data[key] != undefined) formData[key] = data[key]
    })

    loading.value = false
}
setFormData()

const kdnEvent = (url: any) => {
    window.open(url, '_blank')
}

const formRef = ref<FormInstance>()

// 表单验证规则
const formRules = reactive<FormRules>({
    server_port1: [
        { required: true, message: t('serverPort1Placeholder'), trigger: 'blur' },
    ],
    server_port2: [
        { required: true, message: t('serverPort2Placeholder'), trigger: 'blur' },
    ],
    https_port: [
        { required: true, message: t('httpsPortPlaceholder'), trigger: 'blur' },
    ],
})

/**
 * 保存
 */
const save = async(formEl: FormInstance | undefined) => {
    if (loading.value || !formEl) return

    await formEl.validate(async(valid) => {
        if (valid) {
            loading.value = true
            setElectronicSheetConfig(formData).then(() => {
                loading.value = false
            }).catch(() => {
                loading.value = false
            })
        }
    })
}

</script>

<style lang="scss" scoped>
.input-item {
    margin-bottom: 10px !important
}

.input-item-required {
    margin-bottom: 20px !important
}

.button-size {
    font-size: 12px !important;
}

.el-radio.el-radio--large {
    height: auto !important
}
</style>

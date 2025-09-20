<template>
    <div class="main-container">
        <el-card class="card !border-none mb-[15px]" shadow="never">
            <el-page-header :content="pageName" :icon="ArrowLeft" @back="router.push({ path: '/shop/order/delivery' })" />
        </el-card>
        <el-form :model="formData" label-width="150px" ref="formRef" :rules="formRules" class="page-form" v-loading="loading">
            <el-card class="box-card !border-none" shadow="never">
                <el-form-item :label="t('interfaceType')" prop="interface_type">
                    <div>
                        <el-radio-group v-model="formData.interface_type">
                            <el-radio :label="1" size="large">{{ t('kdn') }}</el-radio>
                            <el-radio :label="2" size="large">{{ t('kd100') }}</el-radio>
                        </el-radio-group>
                        <p class="text-[12px] text-[#b2b2b2]" v-if="formData.interface_type == 1">
                            {{ t('promptTips1-1') }}
                            <el-button class="button-size" type="primary" link @click="openEvent('https://www.kdniao.com/reg?from=niucloud')">https://www.kdniao.com</el-button>
                        </p>
                        <p class="text-[12px] text-[#b2b2b2]" v-if="formData.interface_type == 2">
                        {{ t('promptTips2') }}<el-button class="button-size" type="primary" link @click="openEvent('https://www.kuaidi100.com')">https://www.kuaidi100.com</el-button>
                        </p>
                    </div>
                </el-form-item>
                <div v-if="formData.interface_type == 1">
                    <el-form-item :label="t('isPayEdition')" prop="kdn_is_pay">
                        <div>
                            <el-radio-group v-model="formData.kdniao_is_pay">
                                <el-radio :label="1" size="large">{{ t('PayPerOrder') }}</el-radio>
                                <el-radio :label="2" size="large">{{ t('PayPerUse') }}</el-radio>
                            </el-radio-group>
                            <p class="text-[12px] text-[#b2b2b2]" v-if="formData.interface_type == 1">
                                {{ t('promptTips1-2') }}
                            </p>
                            <p class="text-[12px] text-[#b2b2b2]" v-if="formData.interface_type == 1">
                                {{ t('promptTips1-3') }}
                            </p>
                        </div>
                    </el-form-item>

                    <el-form-item label="EBusinessID" class="input-item">
                        <div>
                            <el-input v-model.trim="formData.kdniao_id" :placeholder="t('kdnEBusinessIDPlaceholder')" class="input-width" clearable />
                            <p class="text-[12px] text-[#b2b2b2]">{{ t('kdnEBusinessIDTips') }}</p>
                        </div>
                    </el-form-item>

                    <el-form-item label="APPKEY" class="input-item">
                        <div>
                            <el-input v-model.trim="formData.kdniao_app_key" clearable :placeholder="t('kdnAppKeyPlaceholder')" class="input-width" />
                            <p class="text-[12px] text-[#b2b2b2]">{{ t('kdnAppKeyTips') }}</p>
                        </div>
                    </el-form-item>

                </div>

                <div v-if="formData.interface_type == 2">
                    <el-form-item label="APPKEY" class="input-item">
                        <div>
                            <el-input v-model.trim="formData.kd100_app_key" clearable :placeholder="t('kd100AppKeyPlaceholder')" class="input-width" />
                            <p class="text-[12px] text-[#b2b2b2]">{{ t('kd100AppKeyTips') }}</p>
                        </div>
                    </el-form-item>

                    <el-form-item label="CUSTOMER" class="input-item">
                        <div>
                            <el-input v-model.trim="formData.kd100_customer" :placeholder="t('kd100CustomerPlaceholder')" class="input-width" clearable />
                            <p class="text-[12px] text-[#b2b2b2]">{{ t('kd100CustomerTips') }}</p>
                        </div>
                    </el-form-item>
                </div>

            </el-card>
        </el-form>

        <div class="fixed-footer-wrap">
            <div class="fixed-footer">
                <el-button type="primary" :loading="loading" @click="save(formRef)">{{ t('save') }}</el-button>
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import { ArrowLeft } from '@element-plus/icons-vue'
import { FormInstance, FormRules } from 'element-plus'
import { useRoute, useRouter } from 'vue-router'
import { setDeliverySearch, getDeliverySearch } from '@/addon/shop/api/delivery'

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title
const loading = ref(true)

interface formDataType {
    interface_type: number
    kdniao_id: string
    kdniao_app_key: string
    kdniao_is_pay: number
    kd100_app_key: string
    kd100_customer: string
}

const formData = reactive<formDataType | any>({
    interface_type: 1,
    kdniao_id: '',
    kdniao_app_key: '',
    kdniao_is_pay: 1,
    kd100_app_key: '',
    kd100_customer: ''
})

const setFormData = async() => {
    const data = await (await getDeliverySearch()).data
    Object.keys(formData).forEach((key: string) => {
        if (data[key] != undefined) formData[key] = data[key]
    })

    loading.value = false
}
setFormData()

const openEvent = (url: any) => {
    window.open(url, '_blank')
}

const formRef = ref<FormInstance>()

// 表单验证规则
const formRules = reactive<FormRules>({})

/**
 * 保存
 */
const save = async(formEl: FormInstance | undefined) => {
    if (loading.value || !formEl) return

    await formEl.validate(async(valid) => {
        if (valid) {
            loading.value = true
            setDeliverySearch(formData).then(() => {
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

.button-size {
    font-size: 12px !important;
}

.el-radio.el-radio--large {
    height: auto !important
}
</style>

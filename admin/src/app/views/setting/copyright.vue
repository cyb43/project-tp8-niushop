<template>
    <div class="main-container">

        <el-form class="page-form" :model="formData" label-width="150px" ref="formRef" :rules="formRules" v-loading="loading">
            <el-card class="box-card !border-none" shadow="never">
                <h3 class="text-[16px] text-[#1D1F3A] font-bold mb-4">{{ pageName }}</h3>
                <h3 class="panel-title !text-[14px] bg-[#F4F5F7] p-3 border-[#E6E6E6] border-solid border-b-[1px]">{{ t('copyrightEdit') }}</h3>

                <el-form-item :label="t('logo')">
                    <upload-image v-model="formData.logo" />
                </el-form-item>
                <el-form-item :label="t('companyName')" prop="company_name">
                    <el-input v-model.trim="formData.company_name" :placeholder="t('companyNamePlaceholder')" class="input-width" clearable maxlength="30"/>
                </el-form-item>
                <el-form-item :label="t('copyrightLink')" prop="copyright_link">
                    <el-input v-model.trim="formData.copyright_link" :placeholder="t('copyrightLinkPlaceholder')" class="input-width" clearable />
                </el-form-item>
                <el-form-item :label="t('copyrightDesc')" >
                    <el-input v-model.trim="formData.copyright_desc" type="textarea" rows="4" clearable :placeholder="t('copyrightDescPlaceholder')" class="input-width" maxlength="150" />
                </el-form-item>
                <div class="mt-[20px]">
                    <h3 class="panel-title !text-[14px] bg-[#F4F5F7] p-3 border-[#E6E6E6] border-solid border-b-[1px]">{{ t('putOnRecordEdit') }}</h3>
                    <el-form-item :label="t('icp')" prop="icp">
                        <el-input v-model.trim="formData.icp" :placeholder="t('icpPlaceholder')" class="input-width" clearable maxlength="20"/>
                        <div class="form-tip">{{ t('网站的ICP备案号，显示在H5和PC端底部') }}</div>
                    </el-form-item>
                    <el-form-item :label="t('govRecord')" >
                        <el-input v-model.trim="formData.gov_record" :placeholder="t('govRecordPlaceholder')" class="input-width" clearable maxlength="50"/>
                        <div class="form-tip">{{ t('公安部门登记的备案信息，显示在pc底部') }}</div>
                    </el-form-item>
                    <el-form-item :label="t('govUrl')" >
                        <el-input v-model.trim="formData.gov_url" :placeholder="t('govUrlPlaceholder')" class="input-width" clearable />
                        <div class="form-tip">{{ t('H5和PC底部显示的网站公安点击跳转的链接') }}</div>
                    </el-form-item>
                    <el-form-item :label="t('marketSupervisionUrl')" >
                        <el-input v-model.trim="formData.market_supervision_url" rows="4" clearable :placeholder="t('marketSupervisionUrlPlaceholder')" class="input-width" />
                        <div class="form-tip">{{ t('H5和PC底部显示的市场监督管理局点击跳转的链接') }}</div>
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
import { setCopyright, getCopyright } from '@/app/api/sys'
import { FormInstance, FormRules } from 'element-plus'
import { useRoute } from 'vue-router'

const route = useRoute()
const pageName = route.meta.title

const loading = ref(true)

const formData = reactive<Record<string, string>>({
    icp: '',
    gov_record: '',
    gov_url: '',
    market_supervision_url: '',
    logo: '',
    company_name: '',
    copyright_link: '',
    copyright_desc: ''
})

const setFormData = async () => {
    const data = await (await getCopyright()).data
    Object.keys(formData).forEach((key: string) => {
        if (data[key] != undefined) formData[key] = data[key]
    })

    loading.value = false
}
setFormData()

const formRef = ref<FormInstance>()

// 表单验证规则
const formRules = reactive<FormRules>({
    copyright_link: [
        {
            validator(rule, value, callback) {
                // 允许为空，空值直接通过验证
                if (!value) return callback();

                // 非空时检查是否包含 http/https
                const reg = /^.*?(http|https).*?$/i;
                if (!reg.test(value)) {
                    callback(new Error('链接必须包含 http 或 https'));
                } else {
                    callback();
                }
            },
            trigger: 'blur'
        }
    ]
})

/**
 * 保存
 */
const save = async (formEl: FormInstance | undefined) => {
    if (loading.value || !formEl) return

    await formEl.validate(async (valid) => {
        if (valid) {
            loading.value = true
            setCopyright(formData).then(() => {
                loading.value = false
            }).catch(() => {
                loading.value = false
            })
        }
    })
}

</script>

<style lang="scss" scoped></style>

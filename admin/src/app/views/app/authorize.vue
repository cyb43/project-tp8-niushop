<template>
    <!--授权信息-->
    <div class="main-container">
        <el-card class="box-card !border-none min-h-[300px]" shadow="never" v-loading="loading">
            <div v-if="!loading">
                <div class="title text-[16px] font-bold text-[#1D1F3A] mb-[30px]">授权信息</div>
                <div>
                    <div class="flex items-center">
                        <div class="w-[92px] h-[92px] rounded-[10px] flex justify-center items-center mr-[20px]">
                            <img src="@/app/assets/images/tools/authorize.png" class="w-[92px] h-[92px]" />
                        </div>
                        <div class="flex flex-col justify-between font-500">
                            <div class="flex flex-wrap items-center mb-[12px]">
                                <span class="mr-[6px] text-[14px] text-[#666666] w-[70px] text-left">授权公司：</span>
                                <span class="text-[14px] text-[#333]">{{ authinfo.company_name || "--" }}</span>
                            </div>
                            <div class="flex flex-wrap items-center mb-[12px]">
                                <span class="mr-[6px] text-[14px] text-[#666666] w-[70px] text-left">授权域名：</span>
                                <span class="text-[14px] text-[#333]">{{ authinfo.site_address || "--" }}</span>
                            </div>
                            <div class="flex flex-wrap items-center">
                                <span class="mr-[6px] text-[14px] text-[#666666] w-[70px] text-left">授权码：</span>
                                <span class="text-[14px] text-[#333]">
                                    <span class="mr-[10px]">{{ authinfo.auth_code ? (isCheck ? authinfo.auth_code : hideAuthCode(authinfo.auth_code)) : "--" }}</span>
                                    <el-icon v-if="!isCheck" @click="isCheck = !isCheck" class="text-[14px] cursor-pointer text-[#9699B6]">
                                        <View />
                                    </el-icon>
                                    <el-icon v-else @click="isCheck = !isCheck" class="text-[14px] cursor-pointer text-[#9699B6]"> <Hide /> </el-icon>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-[17px] ml-[110px]">
                        <el-button class="!w-[140px] !h-[32px] mt-[8px] !rounded-[4px]" type="primary" @click="authCodeApproveFn">授权码认证</el-button>
                        <el-popover ref="getAuthCodeDialog" placement="bottom-start" :width="478" trigger="click" class="mt-[8px]">
                            <div class="px-[18px] py-[8px]">
                                <p class="leading-[32px] text-[14px]">您在官方应用市场购买任意一款应用，即可获得授权码。输入正确授权码认证通过后，即可支持在线升级和其它相关服务</p>
                                <div class="flex justify-end mt-[36px]">
                                    <el-button class="w-[182px] !h-[48px]" plain @click="market">去应用市场逛逛</el-button>
                                    <el-button class="w-[100px] !h-[48px]" plain @click="getAuthCodeDialog.hide()">关闭</el-button>
                                </div>
                            </div>
                            <template #reference>
                                <el-button class="!w-[140px] !h-[32px] mt-[8px] !rounded-[4px] !text-[var(--el-color-primary)] hover:!text-[var(--el-color-primary)] !bg-transparent" plain type="primary">如何获取授权码?</el-button>
                            </template>
                        </el-popover>
                    </div>

                    <el-dialog v-model="authCodeApproveDialog" title="授权码认证" width="400px">
                        <el-form :model="formData" label-width="0" ref="formRef" :rules="formRules" class="page-form">
                            <el-card class="box-card !border-none" shadow="never">
                                <el-form-item prop="auth_code">
                                    <el-input v-model.trim="formData.auth_code" :placeholder="t('authCodePlaceholder')" class="input-width" clearable size="large" />
                                </el-form-item>

                                <div class="mt-[20px]">
                                    <el-form-item prop="auth_secret">
                                        <el-input v-model.trim="formData.auth_secret" clearable :placeholder="t('authSecretPlaceholder')" class="input-width" size="large" />
                                    </el-form-item>
                                </div>

                                <div class="text-sm mt-[10px] text-info">{{ t("authInfoTips") }}</div>

                                <div class="mt-[20px]">
                                    <el-button type="primary" class="w-full" size="large" :loading="saveLoading" @click="save(formRef)">{{ t("confirm") }}</el-button>
                                </div>
                                <div class="mt-[10px] text-right">
                                    <el-button type="primary" link @click="market">{{ t("notHaveAuth") }}</el-button>
                                </div>
                            </el-card>
                        </el-form>
                    </el-dialog>
                </div>
            </div>
        </el-card>
    </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from "vue"
import { t } from "@/lang"
import { getVersions } from "@/app/api/auth"
import { getAuthInfo, setAuthInfo } from "@/app/api/module"
import { FormInstance, FormRules } from "element-plus"
import { cloneDeep } from "lodash-es"

const getAuthCodeDialog: Record<string, any> | null = ref(null)
const authCodeApproveDialog = ref(false)
const isCheck = ref(false)

const hideAuthCode = (res: any) => {
    const authCode = cloneDeep(res)
    const data = authCode.slice(0, authCode.length / 2) + authCode.slice(authCode.length / 2, authCode.length - 1).replace(/./g, "*")
    return data
}

const authCodeApproveFn = () => {
    authCodeApproveDialog.value = true
}

interface AuthInfo {
    company_name: string
    site_address: string
    auth_code: string
}

const authinfo = ref<AuthInfo>({
    company_name: "",
    site_address: "",
    auth_code: ""
})
const loading = ref(true)
const saveLoading = ref(false)
const checkAppMange = () => {
    getAuthInfo().then((res) => {
        loading.value = false
        if (res.data.data && res.data.data.length != 0) {
            authinfo.value = res.data.data
            authCodeApproveDialog.value = false
        }
    }).catch(() => {
        loading.value = false
        authCodeApproveDialog.value = false
    })
}

checkAppMange()

const formData = reactive<Record<string, string>>({
    auth_code: "",
    auth_secret: ""
})
const formRef = ref<FormInstance>()

// 表单验证规则
const formRules = reactive<FormRules>({
    auth_code: [{ required: true, message: t("authCodePlaceholder"), trigger: "blur" }],
    auth_secret: [{ required: true, message: t("authSecretPlaceholder"), trigger: "blur" }]
})

const save = async(formEl: FormInstance | undefined) => {
    if (saveLoading.value || !formEl) return

    await formEl.validate(async(valid) => {
        if (valid) {
            saveLoading.value = true

            setAuthInfo(formData).then(() => {
                saveLoading.value = false
                checkAppMange()
            }).catch(() => {
                saveLoading.value = false
                authCodeApproveDialog.value = false
            })
        }
    })
}

const market = () => {
    window.open("https://www.niucloud.com/app")
}

const versions = ref("")
const getVersionsInfo = () => {
    getVersions().then((res) => {
        versions.value = res.data.version.version
    })
}
getVersionsInfo()
</script>

<style lang="scss" scoped>
:deep(.el-button){
    border-radius: 4px !important;
}
</style>

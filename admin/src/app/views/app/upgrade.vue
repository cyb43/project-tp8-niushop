<template>
    <!--授权信息-->
    <div class="main-container">
        <el-card class="box-card !border-none" shadow="never" v-if="newVersion">
            <div>
                <div class="mx-[20px] my-[20px]">
                    <div class="title text-[18px]">版本信息</div>
                    <div class="text-[18px] text-center mb-[7px] mt-[40px]">系统当前版本：v{{ version }}（{{ versionCode }}）</div>
                    <div class="text-center text-[#666] text-[14px]" v-if="!newVersion || (newVersion && newVersion.version_no == version)">
                        <span>当前已是最新版本，无需升级</span>
                        <span class="text-[14px] text-primary ml-[10px] cursor-pointer" @click="openUpgrade">更新说明</span>
                    </div>
                    <div class="text-[#666] text-[14px] text-center" v-else>
                        当前系统最新版本为 <span class="text-[18px] text-[#FF4D01]">v{{ newVersion.version_no }}</span>
                        <span class="text-[14px] text-primary ml-[10px]" style="cursor: pointer" @click="openUpgrade">更新说明</span>
                    </div>
                    <div class="mt-[30px] flex justify-center items-center">
                        <el-button class="text-[#4C4C4C] w-[150px] !h-[44px]" type="primary" :loading="loading" @click="handleUpgrade" v-if="!(!newVersion || (newVersion && newVersion.version_no == version))">一键升级</el-button>
                        <el-button class="text-[#4C4C4C] w-[130px] !h-[44px]" @click="upgradeRecord">升级记录</el-button>
                    </div>
                </div>
            </div>
        </el-card>

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

        <upgrade ref="upgradeRef" />
        <upgrade-log ref="upgradeLogRef" />
    </div>
</template>

<script lang="ts" setup>
import { ref, computed, reactive } from "vue"
import { t } from "@/lang"
import { getVersions } from "@/app/api/auth"
import { getAuthInfo, getFrameworkVersionList, setAuthInfo } from "@/app/api/module"
import { ElMessage, FormInstance, FormRules } from "element-plus"
import { useRouter } from "vue-router"
import Upgrade from "@/app/components/upgrade/index.vue"
import UpgradeLog from "@/app/components/upgrade-log/index.vue"

const upgradeRef = ref<any>(null)
const upgradeLogRef = ref<any>(null)
const authCodeApproveDialog = ref(false)
const frameworkVersionList = ref([])

const checkVersion = ref(false)

const formData = reactive<Record<string, string>>({
    auth_code: '',
    auth_secret: ''
})
const formRef = ref<FormInstance>()

// 表单验证规则
const formRules = reactive<FormRules>({
    auth_code: [{ required: true, message: t('authCodePlaceholder'), trigger: 'blur' }],
    auth_secret: [{ required: true, message: t('authSecretPlaceholder'), trigger: 'blur' }]
})

const saveLoading = ref(false)

const save = async(formEl: FormInstance | undefined) => {
    if (saveLoading.value || !formEl) return

    await formEl.validate(async (valid) => {
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

const getFrameworkVersionListFn = () => {
    getFrameworkVersionList().then(({ data }) => {
        frameworkVersionList.value = data
        if (checkVersion.value) {
            if (!newVersion.value || (newVersion.value && newVersion.value.version_no == version.value)) {
                ElMessage({
                    message: t('versionTips'),
                    type: 'success'
                })
            }
        } else {
            checkVersion.value = true
        }
    })
}
getFrameworkVersionListFn()

const newVersion: any = computed(() => {
    return frameworkVersionList.value.length ? frameworkVersionList.value[0] : null
})

const authCodeApproveFn = () => {
    authCodeApproveDialog.value = true
}

const version = ref('')
const versionCode = ref('')

const getVersionsInfo = () => {
    getVersions().then((res) => {
        version.value = res.data.version.version
        versionCode.value = res.data.version.code
    })
}
getVersionsInfo()

interface AuthInfo {
    company_name: string
    site_address: string
    auth_code: string
}

const authInfo = ref<AuthInfo>({
    company_name: '',
    site_address: '',
    auth_code: ''
})

const repeat = ref(false)
const loading = ref(false)

/**
 * 升级
 */
const handleUpgrade = () => {
    if (!authInfo.value.auth_code) {
        authCodeApproveFn()
        return
    }
    if (repeat.value) return
    repeat.value = true
    loading.value = true
    upgradeRef.value?.open('', () => {
        repeat.value = false
        loading.value = false;
    })
}

const checkAppMange = () => {
    getAuthInfo().then((res) => {
        if (res.data.data && res.data.data.length != 0) {
            authInfo.value = res.data.data
            authCodeApproveDialog.value = false
        }
    }).catch(() => {
        authCodeApproveDialog.value = false
    })
}

checkAppMange()

const router = useRouter()
const upgradeRecord = () => {
    router.push('/admin/tools/upgrade_records')
}

const openUpgrade = () => {
    upgradeLogRef.value?.open()
}
</script>

<style lang="scss" scoped></style>

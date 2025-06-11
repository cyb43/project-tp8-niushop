<template>
    <!--授权信息-->
    <div class="main-container">
        <el-card class="box-card !border-none min-h-[500px]" shadow="never" v-loading="loadingVersion">
            <div v-if="!loadingVersion">
                <div class="mb-[30px]" v-if="newVersion">
                    <div class="title text-[16px] font-bold text-[#1D1F3A] mb-[20px]">版本信息</div>
                    <div class="text-[14px] text-[#1D1F3A] mb-[20px]"><span>系统当前版本：</span><span class="font-bold ">v{{ version }}</span> </div>
                    <div class="flex">
                        <div class="w-[92px] h-[92px] rounded-[10px] flex justify-center items-center mr-[20px]">
                            <img src="@/app/assets/images/tools/upgrade.png" class="w-[92px] h-[92px]" />
                        </div>
                        <div class="flex flex-col justify-between items-start">
                            <div class="text-[14px] text-[#1D1F3A]">系统最新版本为</div>
                            <div class="text-[14px] text-[#1D1F3A] font-bold">v{{ newVersion.version_no }}（{{ versionCode }}）</div>
                            <div class="text-[#9699B6] text-[16px]" v-if="!shouldShowUpgradeButton">
                                <span>已是最新</span>
                            </div>
                            <div v-else>
                                <el-button class="w-[102px] !h-[32px]" type="primary" :loading="loading" @click="handleUpgrade" v-if="!(!newVersion || (newVersion && newVersion.version_no == version))">一键升级</el-button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-title bg-[#F4F5F7] border-[#E6E6E6] border-solid border-b-[1px] h-[40px] flex items-center p-[10px]">
                    <span class="text-[14px] font-500 text-[#1D1F3A]">升级记录</span>
                </div>
                <div >
                    <div class="time-dialog" style="overflow: auto">
                        <el-scrollbar>
                            <el-timeline style="width: 100%">
                                <el-timeline-item v-for="(item, index) in frameworkVersionList" :key="index" placement="left" :hollow="true">
                                    <el-collapse v-model="activeName" accordion>
                                        <el-collapse-item :name="index">
                                            <template #title>
                                                <div class="flex justify-between items-start flex-col">
                                                    <span class="text-[#1D1F3A] text-[14px] leading-[20px]">版本： v{{ item.version_no }}</span>
                                                    <span class="text-[#9699B6] text-[13px] mt-2">{{ item.release_time }}</span>
                                                </div>
                                            </template>
                                            <template #icon="{ isActive }">
                                                <div class="ml-auto text-[#374151] flex items-center">
                                                    <span class="text-[#374151] text-[14px]">{{ isActive ? '收起' : '更新内容' }}</span>
                                                    <span class="iconfont iconjiantouxia ml-[4px] !text-[10px] transition-transform duration-300" v-if="!isActive"></span>
                                                    <span class="iconfont iconjiantoushang ml-[4px] !text-[10px] transition-transform duration-300" v-if="isActive"></span>
                                                </div>
                                            </template>
                                            <div class="px-[20px] py-[20px] bg-overlay timeline-log-wrap whitespace-pre-wrap rounded-[4px] bg-[#F9F9FB] text-[#4F516D]" v-if="item['upgrade_log']">
                                                <div v-html="item['upgrade_log']"></div>
                                            </div>
                                        </el-collapse-item>
                                    </el-collapse>
                                </el-timeline-item>
                            </el-timeline>
                        </el-scrollbar>
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
const activeName = ref(0)
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
const loadingVersion = ref(false)
const getFrameworkVersionListFn = () => {
    loadingVersion.value = true
    getFrameworkVersionList().then(({ data }) => {
        frameworkVersionList.value = data
        loadingVersion.value = false
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
    }).catch(() => {
        loadingVersion.value = false
    })
}
getFrameworkVersionListFn()
const shouldShowUpgradeButton = computed(() => {
    if (!newVersion.value || newVersion.value.version_no === version.value) {
        return false;
    }
    
    // 将版本号转为字符串再处理
    const currentVersionStr = String(version.value);
    const latestVersionStr = String(newVersion.value.version_no);
    // 移除点号并转为数字比较
    const currentVersionNum = parseInt(currentVersionStr.replace(/\./g, ''), 10);
    const latestVersionNum = parseInt(latestVersionStr.replace(/\./g, ''), 10);
    return latestVersionNum > currentVersionNum;
});
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
const timeSplit = (str: string) => {
    const [date, time] = str.split(" ")
    const [hours, minutes] = time.split(":")
    return [date, `${ hours }:${ minutes }`]
}
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
<style lang="scss" scoped>
:deep(.el-timeline-item__node--normal){
    width: 16px !important;
    height: 16px !important;
}
:deep(.el-timeline-item__tail){
    left: 6px !important;
}
:deep(.el-timeline-item__node.is-hollow){
    background: #9699B6 !important;
    border-width: 3px !important;
}
:deep(.time-dialog .el-timeline-item__wrapper) {
    top: -2px !important;
}
:deep(.el-collapse-item__header){
    background: #F9F9FB !important;
    border: 1px solid #F1F1F8 !important;
    padding: 0 20px !important;
    line-height: normal !important;
    height: 70px !important;
}
:deep(.el-collapse-item__content){
    padding-bottom: 0 !important;
}
:deep(.el-button){
   border-radius: 4px !important;
}
</style>
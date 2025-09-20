<template>
    <!--微信小程序-->
    <div class="main-container">
        <el-card class="card !border-none" shadow="never">

            <div class="flex justify-between items-center">
                <span class="text-page-title">{{ pageName }}</span>
            </div>

            <el-tabs v-model="activeName" class="mt-[20px]" @tab-change="handleClick">
                <el-tab-pane :label="t('weappAccessFlow')" name="/channel/weapp" />
                <el-tab-pane :label="t('subscribeMessage')" name="/channel/weapp/message" />
                <el-tab-pane :label="t('weappRelease')" name="/channel/weapp/code" />
            </el-tabs>

            <div class="p-[20px]">
                <h3 class="panel-title !text-sm">{{ t("weappInlet") }}</h3>

                <el-row>
                    <el-col :span="20">
                        <el-steps class="!mt-[10px]" :active="4" direction="vertical">
                            <el-step>
                                <template #title>
                                    <p class="text-[14px] font-[700]">
                                        {{ t("weappAttestation") }}
                                    </p>
                                </template>
                                <template #description>
                                    <span class="text-[#999]">{{ t("weappAttest") }}</span>
                                    <div class="mt-[20px] mb-[40px] h-[32px]">
                                        <el-button type="primary" @click="linkEvent('https://mp.weixin.qq.com/')">{{ t("clickAccess") }}</el-button>
                                    </div>
                                </template>
                            </el-step>

                            <el-step>
                                <template #title>
                                    <p class="text-[14px] font-[700]">
                                        {{ t("weappSetting") }}
                                    </p>
                                </template>
                                <template #description>
                                    <span class="text-[#999]">{{ t("emplace") }}</span>
                                    <div class="mt-[20px] mb-[40px] h-[32px]">
                                        <el-button type="primary" @click="router.push('/channel/weapp/config')">{{ t("weappSettingBtn") }}</el-button>
                                        <el-button type="primary" plain @click="router.push('/channel/weapp/course')">配置教程</el-button>
                                    </div>
                                </template>
                            </el-step>

                            <el-step>
                                <template #title>
                                    <p class="text-[14px] font-[700]">
                                        {{ t("uploadVersion") }}
                                    </p>
                                </template>
                                <template #description>
                                    <span class="text-[#999]">{{ t("releaseCourse") }}</span>
                                    <div class="mt-[20px] mb-[40px] h-[32px]">
                                        <el-button type="primary" plain @click="router.push('/channel/weapp/code')">{{ t("weappRelease") }}</el-button>
                                    </div>
                                </template>
                            </el-step>

                            <el-step>
                                <template #title>
                                    <p class="text-[14px] font-[700]">
                                        {{ t("completeAccess") }}
                                    </p>
                                </template>
                                <template #description>
                                    <span class="text-[#999]">{{ t("releaseCourse") }}</span>
                                    <div class="mt-[20px] mb-[40px] h-[32px]"></div>
                                </template>
                            </el-step>
                        </el-steps>
                    </el-col>

                    <el-col :span="4">
                        <div class="flex justify-center">
                            <el-image class="w-[180px] h-[180px]" :src="qrCode ? img(qrCode) : ''">
                                <template #error>
                                    <div class="w-[100%] h-[100%] flex items-center  justify-center bg-[#f5f7fa]">
                                        <span>{{ qrCode ? t('fileErr') : t('emptyQrCode') }}</span>
                                    </div>
                                </template>
                            </el-image>
                        </div>
                        <div class="mt-[22px] text-center">
                            <p class=" text-[12px]">{{ t('clickAccess2') }}</p>
                        </div>
                    </el-col>
                </el-row>
            </div>
        </el-card>
    </div>
</template>

<script lang="ts" setup>
import { onMounted, onUnmounted, ref } from 'vue'
import { t } from '@/lang'
import { img } from '@/utils/common'
import { getWeappConfig } from '@/app/api/weapp'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title

const activeName = ref('/channel/weapp')
const qrCode = ref('')
const weappConfig = ref({})

const onShowGetWeappConfig = async () => {
    await getWeappConfig().then(({ data }) => {
        weappConfig.value = data
        qrCode.value = data.qr_code
    })
}

onMounted(async () => {
    onShowGetWeappConfig()
    
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            onShowGetWeappConfig()
        }
    })
})

onUnmounted(() => {
    document.removeEventListener('visibilitychange', () => {

    })
})

const linkEvent = (url: string) => {
    window.open(url, '_blank')
}
const handleClick = (val: any) => {
    router.push({ path: activeName.value })
}
</script>

<style lang="scss" scoped></style>

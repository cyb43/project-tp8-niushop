<template>
    <!--微信公众号-->
    <div class="main-container">
        <el-card class="card !border-none" shadow="never">

            <div class="flex justify-between items-center">
                <span class="text-page-title">{{ pageName }}</span>
            </div>

            <el-tabs v-model="activeName" class="my-[20px]" @tab-change="handleClick">
                <el-tab-pane :label="t('wechatAccessFlow')" name="/channel/wechat" />
                <el-tab-pane :label="t('customMenu')" name="/channel/wechat/menu" />
                <el-tab-pane :label="t('wechatTemplate')" name="/channel/wechat/message" />
                <el-tab-pane :label="t('reply')" name="/channel/wechat/reply" />
            </el-tabs>

            <div class="p-[20px]">
                <h3 class="panel-title !text-sm">{{ t("wechatInlet") }}</h3>

                <el-row>
                    <el-col :span="20">
                        <el-steps class="!mt-[10px]" :active="3" direction="vertical">
                            <el-step>
                                <template #title>
                                    <p class="text-[14px] font-[700]">
                                        {{ t("wechatAttestation") }}
                                    </p>
                                </template>
                                <template #description>
                                    <span class="text-[#999]">{{ t("wechatAttestation1") }}</span>
                                    <div class="mt-[20px] mb-[40px] h-[32px]">
                                        <el-button type="primary" @click="linkEvent('https://mp.weixin.qq.com/')">{{ t("clickAccess") }}</el-button>
                                    </div>
                                </template>
                            </el-step>

                            <el-step>
                                <template #title>
                                    <p class="text-[14px] font-[700]">
                                        {{ t("wechatSetting") }}
                                    </p>
                                </template>
                                <template #description>
                                    <span class="text-[#999]">{{ t("wechatSetting1") }}</span>
                                    <div class="mt-[20px] mb-[40px] h-[32px]">
                                        <el-button type="primary" @click="router.push('/channel/wechat/config')">{{ t("clickSetting") }}</el-button>
                                    </div>
                                </template>
                            </el-step>

                            <el-step>
                                <template #title>
                                    <p class="text-[14px] font-[700]">
                                        {{ t("wechatAccess") }}
                                    </p>
                                </template>
                                <template #description>
                                    <span class="text-[#999]">{{ t("wechatAccess") }}</span>
                                    <div class="mt-[20px] mb-[40px] h-[32px]">
                                        <el-button type="primary" plain @click="router.push('/channel/wechat/course')">{{ t("releaseCourse") }}</el-button>
                                    </div>
                                </template>
                            </el-step>
                        </el-steps>
                    </el-col>

                    <el-col :span="4">
                        <div class="flex justify-center">
                            <el-image class="w-[180px] h-[180px]" :src="qrcode ? img(qrcode) : ''">
                                <template #error>
                                    <div class="w-[100%] h-[100%] flex items-center justify-center bg-[#f5f7fa]">
                                        <span>{{ qrcode ? t('fileErr') : t('emptyQrCode') }}</span>
                                    </div>
                                </template>
                            </el-image>
                        </div>
                        <div class="mt-[22px] text-center">
                            <p class="text-[12px]">{{ t('clickAccess2') }}</p>
                        </div>
                    </el-col>
                </el-row>
            </div>
        </el-card>
    </div>
</template>

<script lang="ts" setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { t } from '@/lang'
import { img } from '@/utils/common'
import { getWechatConfig } from '@/app/api/wechat'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const pageName = route.meta.title

const activeName = ref('/channel/wechat')
const qrcode = ref('')
const wechatConfig = ref({})
const oplatformConfig = ref({})

const onShowGetWechatConfig = async () => {
    await getWechatConfig().then(({data}) => {
        wechatConfig.value = data
        qrcode.value = data.qr_code
    })
}

onMounted(async () => {
    onShowGetWechatConfig()
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            onShowGetWechatConfig()
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

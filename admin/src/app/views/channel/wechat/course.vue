<template>
    <!--发布教程-->
    <div class="main-container">

        <el-card class="card !border-none" shadow="never">
            <el-page-header :content="pageName" :icon="ArrowLeft" @back="back()" />
        </el-card>

        <el-card class="box-card mt-[15px] pt-[20px] !border-none" shadow="never">
            <div class="flex">
                <div class="min-w-[60px]">
                    <span class="flex justify-center items-center block w-[40px] h-[40px] border-[1px] border-primary rounded-[999px] text-primary">1</span>
                </div>
                <div>
                    <p class="flex items-center text-[14px]">{{ t('writingTipsOne1') }}--<el-button link type="primary" @click="linkEvent">{{ t('writingTipsOne2') }}</el-button>, {{ t('writingTipsOne3') }}<span class="text-primary">URL / Token / EncondingAESKey</span>{{ t('writingTipsOne4') }}</p>
                    <div class="w-[100%] mt-[10px]">
                        <img class="w-[100%]" src="@/app/assets/images/setting/wechat_1.png" />
                    </div>
                    <p class="flex items-center text-[14px] mt-[20px]">{{ t('writingTipsOne5') }}</p>
                    <div class="w-[100%] mt-[10px]">
                        <img class="w-[100%]" src="@/app/assets/images/setting/wechat_4.png" />
                    </div>
                </div>
            </div>
            <div class="flex mt-[40px]">
                <div class="min-w-[60px]">
                    <span class="flex justify-center items-center block w-[40px] h-[40px] border-[1px] border-primary rounded-[999px] text-primary">2</span>
                </div>
                <div>
                    <p class="flex items-center text-[14px]">{{ t('writingTipsTwo1') }}</p>
                    <div class="w-[100%] mt-[10px]">
                        <img class="w-[100%]" src="@/app/assets/images/setting/wechat_2.png" />
                    </div>
                </div>
            </div>
            <div class="flex mt-[40px]">
                <div class="min-w-[60px]">
                    <span class="flex justify-center items-center block w-[40px] h-[40px] border-[1px] border-primary rounded-[999px] text-primary">3</span>
                </div>
                <div>
                    <p class="flex items-center text-[14px]">{{ t('writingTipsThree1') }}<span class="text-primary">{{ t('writingTipsThree2') }}</span></p>
                    <div class="w-[100%] mt-[10px]">
                        <img class="w-[100%]" src="@/app/assets/images/setting/wechat_3.png" />
                    </div>
                </div>
            </div>
        </el-card>
    </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { t } from '@/lang'
import { getWechatConfig } from '@/app/api/wechat'
import { ArrowLeft } from '@element-plus/icons-vue'
import { useRouter, useRoute } from 'vue-router'

const route = useRoute()
const pageName = route.meta.title
const router = useRouter()
const back = () => {
    router.push('/channel/wechat')
}
const loading = ref(true)
const formData = reactive<Record<string, string>>({
    wechat_name: '',
    wechat_original: '',
    app_id: '',
    app_secret: '',
    qr_code: '',
    token: '',
    encoding_aes_key: '',
    encryption_type: 'not_encrypt'
})

/**
 * 获取微信配置
 */
getWechatConfig().then(res => {
    Object.assign(formData, res.data)
    loading.value = false
})

const linkEvent = () => {
    window.open('https://mp.weixin.qq.com/', '_blank')
}

</script>

<style lang="scss" scoped></style>

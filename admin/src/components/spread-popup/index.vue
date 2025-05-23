<template>
    <el-dialog v-model="showDialog" :title="titleName" width="500px" :destroy-on-close="true">
        <el-tabs v-model="channel" class="mb-[10px]">
            <el-tab-pane label="H5" name="h5"></el-tab-pane>
            <el-tab-pane label="微信小程序" name="weapp"></el-tab-pane>
        </el-tabs>

        <!-- H5推广 -->
        <div class="promote-flex flex" v-if="channel === 'h5'">
            <div class="promote-img flex justify-center items-center bg-[#f8f8f8] w-[150px] h-[150px]">
                <el-image :src="wapImage"/>
            </div>
            <div class="px-[20px] flex-1">
                <div class="mb-[10px]">{{ t('promoteUrl') }}</div>
                <el-input class="mb-[10px]" readonly :value="wapPreview">
                    <template #append>
                        <el-button @click="copyEvent(wapPreview)">{{ t('copy') }}</el-button>
                    </template>
                </el-input>
                <a class="text-primary" :href="wapImage" download>{{ t('downLoadQRCode') }}</a>
            </div>
        </div>

        <!-- 小程序推广 -->
        <div class="promote-flex flex" v-if="channel === 'weapp'">
            <div class="promote-img flex justify-center items-center bg-[#f8f8f8] w-[150px] h-[150px]">
                <el-image :src="img(weappData.path)" v-if="weappData.path" class="w-[150px] h-[150px]">
                    <template #error>
                        <div class="w-[150px] h-[150px] text-[14px] text-center leading-[150px]">{{ t('configureFailed') }}</div>
                    </template>
                </el-image>
                <div v-else class="w-[150px] h-[150px] text-[14px] text-center leading-[150px]">{{ t('configureFailed') }}</div>
            </div>
            <div class="px-[20px] flex-1" v-if="weappData.path">
                <a class="text-primary" :href="img(weappData.path)" target="_blank" download>{{ t('downLoadQRCode') }}</a>
            </div>
        </div>
    </el-dialog>
</template>

<script lang="ts" setup>
import { t } from '@/lang'
import { ref, reactive, watch } from 'vue'
import { ElMessage } from 'element-plus'
import QRCode from 'qrcode'
import storage from '@/utils/storage'
import { useClipboard } from '@vueuse/core'
import { getUrl, getQrcode } from '@/app/api/sys'
import { img } from '@/utils/common'

const showDialog = ref(false)
const channel = ref('h5')
const wapUrl = ref('')
const wapDomain = ref('')
const wapImage = ref('')
const wapPreview = ref('')
const pageName = ref('')
const weappData = reactive({
    path: ''
})

getUrl().then((res: any) => {
    wapUrl.value = res.data.wap_url

    // 生产模式禁止
    if (import.meta.env.MODE == 'production') return

    wapDomain.value = res.data.wap_domain

    // env文件配置过wap域名
    if (wapDomain.value) {
        wapUrl.value = wapDomain.value + '/wap'
    }

    const wapDomainStorage = storage.get('wap_domain')
    if (wapDomainStorage) {
        wapUrl.value = wapDomainStorage
    }
})

// **生成H5二维码**
const generateH5QRCode = () => {
    wapPreview.value = `${ wapUrl.value }${ pageName.value }`
    QRCode.toDataURL(wapPreview.value, { errorCorrectionLevel: 'L', margin: 0, width: 120 }).then(url => {
        wapImage.value = url
    })
}

// **获取小程序二维码**
const fetchWeAppQRCode = () => {
    // 去掉 page 参数前面的 '/'
    if (pagePath.value.startsWith('/')) {
        pagePath.value = pagePath.value.slice(1);
    }

    getQrcode({
        page: pagePath.value, // 传递页面路径
        folder: folder.value, // 传递模块目录
        params: [
            {
                column_name: columnName.value,
                column_value: columnValue.value
            }
        ]
    }).then((res: any) => {
        if (res.data) {
            weappData.path = res.data.weapp_path
        }
    })
}

// 定义变量存储传入的数据
const pagePath = ref("")
const columnName = ref("")
const columnValue = ref("")
const titleName = ref("")
const folder: any = ref("")

// **显示对话框**
const show = (page: string, column: string, value: string, title: string, dir: string) => {
    pagePath.value = page
    columnName.value = column
    columnValue.value = value
    titleName.value = title
    folder.value = dir
    pageName.value = `${ pagePath.value }?${ columnName.value }=${ columnValue.value }`

    generateH5QRCode()
    fetchWeAppQRCode()
    showDialog.value = true
}

// **复制链接**
const { copy, isSupported, copied } = useClipboard()
const copyEvent = (text: string) => {
    if (!isSupported.value) {
        ElMessage({
            message: t('notSupportCopy'),
            type: 'warning'
        })
    }
    copy(text)
}

watch(copied, () => {
    if (copied.value) {
        ElMessage({
            message: t('copySuccess'),
            type: 'success'
        })
    }
})

// **暴露给父组件**
defineExpose({
    show
})
</script>

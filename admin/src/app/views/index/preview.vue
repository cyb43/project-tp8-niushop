<template>
    <div class="main-container w-[375px] mx-auto mt-[20px] mb-[40px] relative">

        <div class="flex full-container h-[800px]">
            <iframe v-show="loadingIframe" class="w-[375px]" :src="wapPreview" frameborder="0" id="previewIframe"></iframe>
            <div v-show="loadingDev" class="w-[375px] border border-slate-100 bg-body pt-[20px] px-[20px]">
                <div class="font-bold text-xl mb-[40px]">{{ t('developTitle') }}</div>
                <div class="mb-[20px] flex flex-col">
                    <text class="mb-[10px]">{{ t('wapDomain') }}</text>
                    <el-input v-model.trim="wapDomain" :placeholder="t('wapDomainPlaceholder')" clearable />
                </div>
                <el-button type="primary" @click="save">{{ t('confirm') }}</el-button>
            </div>

            <div class="w-[400px] absolute bg-body top-[10%] -right-[450px]" v-if="loadingIframe">

                <div class="info-wrap mt-[20px]">

                    <div class="px-[20px] pb-[10px] font-bold">{{ t('h5') }}</div>
                    <el-form label-width="40px" class="px-[20px]">

                        <el-form-item :label="t('link')" v-show="wapPreview">
                            <el-input readonly :value="wapPreview">
                                <template #append>
                                    <el-button @click="copyEvent(wapPreview)" class="bg-primary copy">{{ t('copy') }}</el-button>
                                </template>
                            </el-input>
                        </el-form-item>

                        <el-form-item label=" " v-show="wapImage">
                            <el-image :src="wapImage" />
                        </el-form-item>

                    </el-form>

                    <div class="px-[20px] pb-[10px] font-bold mt-[40px]">{{ t('weapp') }}</div>
                    <el-form label-width="40px" class="px-[20px]">
                        <el-form-item label=" " v-if="weappConfig.qr_code">
                            <el-image class="w-[150px] h-[150px]" :src="img(weappConfig.qr_code)" />
                        </el-form-item>
                        <el-form-item label=" " v-else>
                            <span class="text-gray-400">{{ t('weappNotSet') }}</span>
                        </el-form-item>
                    </el-form>

                </div>

            </div>

        </div>

    </div>
</template>

<script lang="ts" setup>
import { ref, reactive, watch, inject } from 'vue'
import { t } from '@/lang'
import { useRoute } from 'vue-router'
import { getWeappConfig } from '@/app/api/weapp'
import { getUrl } from '@/app/api/sys'
import { useClipboard } from '@vueuse/core'
import { ElMessage } from 'element-plus'
import { img } from '@/utils/common'
import QRCode from 'qrcode'
import storage from '@/utils/storage'

const wapUrl = ref('')
const wapDomain = ref('')
const wapImage = ref('')
const wapPreview = ref('')

const loadingIframe = ref(false) // 加载iframe
const loadingDev = ref(false) // 加载开发环境配置
const timeIframe = ref(0) // iframe打开时间
const difference = ref(0) // 检测页面加载差异，小于1000毫秒，则配置wap端域名

const route = useRoute()
route.query.page = route.query.page || '' // 页面路径

const setLayout = inject('setLayout')
setLayout('decorate')

getUrl().then((res: any) => {
    wapUrl.value = res.data.wap_url

    let repeat = true // 防重复执行

    // 开发模式下执行
    if (import.meta.env.MODE == 'development') {
        wapDomain.value = res.data.wap_domain

        // env文件配置过wap域名
        if (wapDomain.value) {
            wapUrl.value = wapDomain.value + '/wap'
            repeat = false
            setDomain()
        }

        const wap_domain_storage = storage.get('wap_domain')
        if (wap_domain_storage) {
            wapUrl.value = wap_domain_storage
            repeat = false
            setDomain()
        }
    }

    if (repeat) {
        setDomain()
    }
})

const save = () => {
    if (wapDomain.value.trim().length == 0) {
        ElMessage({
            type: 'warning',
            message: `${t('wapDomainPlaceholder')}`
        })
        return
    }
    wapUrl.value = wapDomain.value + '/wap'
    setDomain()
    storage.set({ key: 'wap_domain', data: wapUrl.value })
    loadingIframe.value = true
    loadingDev.value = false
}

const setDomain = () => {
    if (route.query.page) {
        wapPreview.value = `${wapUrl.value}${route.query.page}`
        // errorCorrectionLevel：密度容错率L（低）H(高)
        QRCode.toDataURL(wapPreview.value, { errorCorrectionLevel: 'L', margin: 0, width: 100 }).then(url => {
            wapImage.value = url
        })

        const send = () => {
            timeIframe.value = new Date().getTime()
            postMessage()
        }

        // 同步发送一次消息
        send()

        // 如果同步发送消息的 uni-app没有接收到回应，则定时发送消息
        let sendCount = 0
        const timeInterVal = setInterval(() => {
            // 接收 uni-app 发送的消息 或者 发送50次后未响应，则停止发送
            if (uniAppLoadStatus.value || sendCount >= 50) {
                clearInterval(timeInterVal)
                return
            }

            send()
            sendCount++
        }, 200)

        // 如果10秒内加载不出来，则需要配置域名
        setTimeout(() => {
            if (difference.value == 0) initLoad()
        }, 1000 * 10)
    }
}

const uniAppLoadStatus = ref(false) // uni-app 加载状态，true：加载完成，false：未完成

// 监听 uni-app 端 是否加载完成
window.addEventListener('message', (event) => {
    try {
        let data = {
            type: ''
        }
        if (typeof event.data == 'string') {
            data = JSON.parse(event.data)
        } else if (typeof event.data == 'object') {
            data = event.data
        }
        if (data.type && ['appOnLaunch', 'appOnReady'].indexOf(data.type) != -1) {
            loadingDev.value = false
            loadingIframe.value = true
            const loadTime = new Date().getTime()
            uniAppLoadStatus.value = true // 加载完成
            difference.value = loadTime - timeIframe.value
        }
    } catch (e) {
        initLoad()
        console.log('preview 后台接受数据错误', e)
    }
}, false)

// 将数据发送到uniapp
const postMessage = () => {
    const data = JSON.stringify({
        type: 'appOnReady',
        message: '加载完成'
    })
    if (window.previewIframe) window.previewIframe.contentWindow.postMessage(data, '*')
}

// 初始化加载状态
const initLoad = () => {
    loadingDev.value = true
    loadingIframe.value = false
    wapPreview.value = ''
    wapImage.value = ''
}

const weappConfig = reactive({
    qr_code: ''
})

// 获取微信配置
getWeappConfig().then((res: any) => {
    if (res.code == 1) {
        const data = res.data
        weappConfig.qr_code = data.qr_code
    }
})

// 复制
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

</script>

<style lang="scss">
body {
    background: #edf0f3;
}
.main-container{
    overflow: inherit !important;
    border-radius: inherit;
    background: inherit;
}
.copy {
    background: var(--el-color-primary) !important;
    color: var(--el-color-white) !important;
}
</style>
<style lang="scss" scoped></style>

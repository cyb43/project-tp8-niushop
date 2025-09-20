<template>
    <div class="main-container flex-1">
        <el-header class="flex items-center h-[60px] bg-primary px-[20px]">
            <div class="text-white cursor-pointer flex items-center" @click="goBack">
                <el-icon size="14">
                    <ArrowLeft />
                </el-icon>
                <span class="pl-[5px] text-[14px]">{{ t('back') }}</span>
            </div>
            <div class="text-white ml-[10px] mr-[20px] flex items-center">
                <span class="mr-[5px] text-[rgba(255,255,255,.5)]">｜</span>
                <span class="mr-[5px] text-[14px]">{{ t('decorating') }}：{{ diyStore.typeName }}</span>
                <!--<el-icon class="font-bold"><EditPen /></el-icon>-->
            </div>

            <div v-if="diyStore.type && diyStore.type != 'DIY_PAGE'" class="flex items-center">
                <span class="text-white mr-[10px] text-base">{{ t('templatePagePlaceholder') }}</span>
                <div class="w-[180px]">
                    <el-select size="small" v-model="template" :placeholder="t('templatePagePlaceholder')" @change="changeTemplatePage">
                        <el-option :label="t('templatePageEmpty')" value="" />
                        <el-option v-for="(item, key) in templatePages" :label="item.title" :value="key" :key="key"/>
                    </el-select>
                </div>
            </div>
            <div class="flex-1"></div>
            <el-button @click="preview()">{{ t('preview') }}</el-button>
            <el-button @click="save()">{{ t('save') }}</el-button>
        </el-header>

        <div class="full-container flex flex-row flex-1 bg-page">

            <div class="component-list w-[290px]">

                <!-- 组件列表区域 -->
                <el-scrollbar class="px-[10px]">
                    <el-collapse v-model="activeNames" @change="handleChange">
                        <el-collapse-item v-for="(item, key) in component" :key="key" :title="item.title" :name="key">
                            <ul class="flex flex-row flex-wrap">
                                <li v-for="(compItem, compKey) in item.list" :key="compKey" class="w-2/6 text-center cursor-pointer h-[65px]" :title="compItem.title" @click="diyStore.addComponent(compKey, compItem)">
                                    <icon v-if="compItem.icon" :name="compItem.icon" size="20px" class="inline-block mt-[3px]" />
                                    <icon v-else name="iconfont iconkaifazujian" size="20px" class="inline-block mt-[3px]" />
                                    <span class="block text-[12px] truncate">{{ compItem.title }}</span>
                                </li>
                            </ul>
                        </el-collapse-item>
                    </el-collapse>
                </el-scrollbar>

            </div>

            <div class="preview-wrap flex-1 relative mt-[20px]">

                <el-scrollbar>
                    <el-button class="page-btn absolute right-[20px]" @click="diyStore.changeCurrentIndex(-99)">{{ t('pageSet') }}</el-button>
                    <div class="diy-view-wrap w-[375px] shadow-lg mx-auto">
                        <div class="preview-head bg-no-repeat bg-center bg-cover cursor-pointer h-[64px]" :class="[diyStore.global.topStatusBar.style]" :style="{backgroundColor :diyStore.global.topStatusBar.bgColor}" @click="diyStore.changeCurrentIndex(-99)">
                            <div v-if="diyStore.global.topStatusBar.style == 'style-1' && diyStore.global.topStatusBar.isShow" class="content-wrap">
                                <div class="title-wrap" :style="{ fontSize: '14px', color: diyStore.global.topStatusBar.textColor, textAlign: diyStore.global.topStatusBar.textAlign }">
                                    {{ diyStore.global.title }}
                                </div>
                            </div>
                            <div v-if="diyStore.global.topStatusBar.style == 'style-2' && diyStore.global.topStatusBar.isShow" class="content-wrap">
                                <div class="title-wrap" :style="{ color: diyStore.global.topStatusBar.textColor }">
                                    <div class="h-[28px] max-w-[150px] mr-[8px]" v-if="diyStore.global.topStatusBar.imgUrl">
                                        <img class="max-w-[100%] max-h-[100%]" :src="img(diyStore.global.topStatusBar.imgUrl)" mode="heightFix" />
                                    </div>
                                    <div :style="{ color: diyStore.global.topStatusBar.textColor }">{{ diyStore.global.title }}</div>
                                </div>
                            </div>
                            <div v-if="diyStore.global.topStatusBar.style == 'style-3' && diyStore.global.topStatusBar.isShow" class="content-wrap">
                                <div class="title-wrap" v-if="diyStore.global.topStatusBar.imgUrl">
                                    <img class="max-w-[100%] max-h-[100%]" :src="img(diyStore.global.topStatusBar.imgUrl)" />
                                </div>
                                <div class="search">
                                    <span class="nc-iconfont nc-icon-sousuo-duanV6xx1 !text-[12px] absolute left-[10px]"></span>
                                    <span class="text-[12px]">{{diyStore.global.topStatusBar.inputPlaceholder}}</span>
                                </div>
                            </div>
                            <div v-if="diyStore.global.topStatusBar.style == 'style-4' && diyStore.global.topStatusBar.isShow" class="content-wrap">
                                <span class="iconfont iconxiazai19 !text-[14px]" :style="{ color: diyStore.global.topStatusBar.textColor }"></span>
                                <div class="title-wrap" :style="{ color: diyStore.global.topStatusBar.textColor }">我的位置</div>
                                <span class="iconfont iconxiangyoujiantou !text-[12px]" :style="{ color: diyStore.global.topStatusBar.textColor }"></span>
                            </div>
                        </div>
                        <div class="preview-block relative">

                            <ul class="quick-action absolute text-center -right-[70px] top-[20px] w-[42px] rounded shadow-md">
                                <el-tooltip effect="light" :content="t('moveUpComponent')" placement="right">
                                    <icon name="iconfont iconjiantoushang" size="20px" class="block cursor-pointer leading-[40px]" @click="diyStore.moveUpComponent" />
                                </el-tooltip>
                                <el-tooltip effect="light" :content="t('moveDownComponent')" placement="right">
                                    <icon name="iconfont iconjiantouxia" size="20px" class="block cursor-pointer leading-[40px]" @click="diyStore.moveDownComponent" />
                                </el-tooltip>
                                <el-tooltip effect="light" :content="t('copyComponent')" placement="right">
                                    <icon name="iconfont iconcopy-line" size="20px" class="block cursor-pointer leading-[40px]" @click="diyStore.copyComponent" />
                                </el-tooltip>
                                <el-tooltip effect="light" :content="t('delComponent')" placement="right">
                                    <icon name="iconfont icondelete-line" size="20px" class="block cursor-pointer leading-[40px]" @click="diyStore.delComponent" />
                                </el-tooltip>
                                <el-tooltip effect="light" :content="t('resetComponent')" placement="right">
                                    <icon name="iconfont iconloader-line" size="20px" class="block cursor-pointer leading-[40px]" @click="diyStore.resetComponent" />
                                </el-tooltip>
                            </ul>

                            <!-- 组件预览渲染区域 -->
                            <iframe id="previewIframe" v-show="loadingIframe" :src="wapPreview" frameborder="0" class="preview-iframe w-[375px]"></iframe>

                            <div v-show="loadingDev" class="preview-iframe w-[375px] pt-[20px] px-[20px]">
                                <div class="font-bold text-xl mb-[40px]">{{ t('developTitle') }}</div>
                                <div class="mb-[20px] flex flex-col">
                                    <text class="mb-[10px]">{{ t('wapDomain') }}</text>
                                    <el-input v-model.trim="wapDomain" :placeholder="t('wapDomainPlaceholder')" clearable />
                                </div>
                                <el-button type="primary" @click="saveWapDomain">{{ t('confirm') }}</el-button>
                                <el-button type="primary" @click="settingTips()" plain>{{ t('settingTips') }}</el-button>
                            </div>

                        </div>
                    </div>
                </el-scrollbar>

            </div>

            <div class="edit-attribute-wrap w-[400px]">

                <!-- 编辑组件属性区域 -->
                <el-scrollbar>
                    <el-card class="box-card" shadow="never">
                        <template #header>
                            <div class="card-header flex justify-between items-center">
                                <span class="title flex-1">{{ diyStore.currentIndex == -99 ? t('pageSet') : diyStore.editComponent.componentTitle }}</span>
                                <div class="tab-wrap flex rounded-[50px] bg-gray-100 text-[14px]" v-if="diyStore.currentComponent">
                                    <span class="cursor-pointer rounded-[50px] py-[5px] px-[15px]" :class="{ 'bg-primary text-white': diyStore.editTab == 'content' }" @click="diyStore.editTab = 'content'">{{ t('tabEditContent') }}</span>
                                    <span class="cursor-pointer rounded-[50px] py-[5px] px-[15px]" :class="{ 'bg-primary text-white': diyStore.editTab == 'style' }" @click="diyStore.editTab = 'style'">{{ t('tabEditStyle') }}</span>
                                </div>
                            </div>
                        </template>

                        <div class="edit-component-wrap">

                            <component v-if="diyStore.currentComponent" :is="modules[diyStore.currentComponent]" :key="diyStore.currentIndex" :value="diyStore.value[diyStore.currentIndex]">
                                <template #style>
                                    <div class="edit-attr-item-wrap">
                                        <h3 class="mb-[10px]">{{ t('componentStyleTitle') }}</h3>
                                        <el-form label-width="90px" class="px-[10px]">

                                            <template v-if="diyStore.editComponent.ignore.indexOf('pageBgColor') == -1">
                                                <el-form-item :label="t('bottomBgColor')">
                                                    <el-color-picker v-model="diyStore.editComponent.pageStartBgColor" show-alpha :predefine="diyStore.predefineColors" />
                                                    <icon name="iconfont iconmap-connect" size="20px" class="block !text-gray-400 mx-[5px]"/>
                                                    <el-color-picker v-model="diyStore.editComponent.pageEndBgColor" show-alpha :predefine="diyStore.predefineColors" />
                                                </el-form-item>
                                                <div class="text-sm text-gray-400 ml-[90px] mb-[10px]">{{ t('bottomBgTips') }}</div>
                                            </template>

                                            <el-form-item :label="t('bgGradientAngle')" v-if="diyStore.editComponent.ignore.indexOf('pageBgColor') == -1">
                                                <el-radio-group v-model="diyStore.editComponent.pageGradientAngle">
                                                    <el-radio label="to bottom">{{ t('topToBottom') }}</el-radio>
                                                    <el-radio label="to right">{{ t('leftToRight') }}</el-radio>
                                                </el-radio-group>
                                            </el-form-item>

                                            <el-form-item :label="t('componentBgUrl')" v-if="diyStore.editComponent.ignore.indexOf('componentBgUrl') == -1">
                                                <upload-image v-model="diyStore.editComponent.componentBgUrl" :limit="1"/>
                                            </el-form-item>

                                            <el-form-item :label="t('componentBgAlpha')" v-if="diyStore.editComponent.ignore.indexOf('componentBgUrl') == -1 && diyStore.editComponent.componentBgUrl">
                                                <el-slider v-model="diyStore.editComponent.componentBgAlpha" show-input size="small" :min="0" :max="10" class="ml-[10px] diy-nav-slider" />
                                            </el-form-item>

                                            <el-form-item :label="t('componentBgColor')" v-if="diyStore.editComponent.ignore.indexOf('componentBgColor') == -1">
                                                <el-color-picker v-model="diyStore.editComponent.componentStartBgColor" show-alpha :predefine="diyStore.predefineColors" />
                                                <icon name="iconfont iconmap-connect" size="20px" class="block !text-gray-400 mx-[5px]"/>
                                                <el-color-picker v-model="diyStore.editComponent.componentEndBgColor" show-alpha :predefine="diyStore.predefineColors" />
                                            </el-form-item>

                                            <el-form-item :label="t('bgGradientAngle')" v-if="diyStore.editComponent.ignore.indexOf('componentBgColor') == -1">
                                                <el-radio-group v-model="diyStore.editComponent.componentGradientAngle">
                                                    <el-radio label="to bottom">{{ t('topToBottom') }}</el-radio>
                                                    <el-radio label="to right">{{ t('leftToRight') }}</el-radio>
                                                </el-radio-group>
                                            </el-form-item>

                                            <el-form-item :label="t('marginTop')" v-if="diyStore.editComponent.ignore.indexOf('marginTop') == -1">
                                                <el-slider v-model="diyStore.editComponent.margin.top" show-input size="small" :min="-100" class="ml-[10px] diy-nav-slider" />
                                            </el-form-item>
                                            <el-form-item :label="t('marginBottom')" v-if="diyStore.editComponent.ignore.indexOf('marginBottom') == -1">
                                                <el-slider v-model="diyStore.editComponent.margin.bottom" show-input size="small" class="ml-[10px] diy-nav-slider" />
                                            </el-form-item>
                                            <el-form-item :label="t('marginBoth')" v-if="diyStore.editComponent.ignore.indexOf('marginBoth') == -1">
                                                <el-slider v-model="diyStore.editComponent.margin.both" show-input size="small" class="ml-[10px] diy-nav-slider" />
                                            </el-form-item>
                                            <el-form-item :label="t('topRounded')" v-if="diyStore.editComponent.ignore.indexOf('topRounded') == -1">
                                                <el-slider v-model="diyStore.editComponent.topRounded" show-input size="small" class="ml-[10px] diy-nav-slider" :max="100" />
                                            </el-form-item>
                                            <el-form-item :label="t('bottomRounded')" v-if="diyStore.editComponent.ignore.indexOf('bottomRounded') == -1">
                                                <el-slider v-model="diyStore.editComponent.bottomRounded" show-input size="small" class="ml-[10px] diy-nav-slider" :max="100" />
                                            </el-form-item>
                                        </el-form>
                                    </div>
                                </template>
                            </component>

                        </div>

                    </el-card>
                </el-scrollbar>

            </div>

        </div>
    </div>
</template>

<script lang="ts" setup>
import { ref, reactive, toRaw, watch, inject } from 'vue'
import { t } from '@/lang'
import { ArrowLeft } from "@element-plus/icons-vue"
import { img } from '@/utils/common'
import { getDiyTemplatePages, addDiyPage, editDiyPage, initPage } from '@/app/api/diy'
import { useRoute, useRouter } from 'vue-router'
import { cloneDeep } from 'lodash-es'
import { ElMessage, ElMessageBox } from 'element-plus'
import useDiyStore from '@/stores/modules/diy'
import storage from '@/utils/storage'

const setLayout = inject('setLayout')
setLayout('decorate')

const diyStore = useDiyStore()
const route = useRoute()
const router = useRouter()

route.query.id = route.query.id || 0
route.query.name = route.query.name || ''
route.query.url = route.query.url || '' // 页面路径
route.query.type = route.query.type || '' // 页面模板，新页面传入
route.query.title = route.query.title || ''
route.query.back = route.query.back || '/admin/diy/list'

const backPath = route.query.back
const template = ref('')
const oldTemplate = ref('')
const wapUrl = ref('')
const wapDomain = ref('')
const wapPreview = ref('')

const loadingIframe = ref(false) // 加载iframe
const loadingDev = ref(false) // 加载开发环境配置
const timeIframe = ref(0) // iframe打开时间
const difference = ref(0) // 检测页面加载差异，小于1000毫秒，则配置wap端域名

const component = ref([])
const componentType: string[] = reactive([])
const page = ref('')

const activeNames = ref(componentType)
const handleChange = (val: string[]) => {
}

// 初始化原数据
const originData = reactive({
    id: diyStore.id,
    name: diyStore.name,
    pageTitle: diyStore.pageTitle,
    title: diyStore.global.title,
    value: JSON.stringify({
        global: toRaw(diyStore.global),
        value: toRaw(diyStore.value)
    })
})

// 返回上一页
const isChange = ref(true) // 数据是否发生变化，true：没变化，false：变化了
const goBack = () => {
    if (isChange.value) {
        location.href = `${location.origin}${backPath}`
        router.push(backPath)
    } else {
        // 数据发生变化，弹框提示：确定离开此页面
        ElMessageBox.confirm(
            t('leavePageTitleTips'),
            t('leavePageContentTips'),
            {
                confirmButtonText: t('confirm'),
                cancelButtonText: t('cancel'),
                type: 'warning',
                autofocus: false
            }
        ).then(() => {
            location.href = `${location.origin}${backPath}`
        }).catch(() => {
        })
    }
}

// 动态加载后台自定义组件编辑
const modulesFiles = import.meta.glob('./components/*.vue', { eager: true })
const addonModulesFiles = import.meta.glob('@/addon/**/views/diy/components/*.vue', { eager: true })
addonModulesFiles && Object.assign(modulesFiles, addonModulesFiles)

const modules = {}
for (const [key, value] of Object.entries(modulesFiles)) {
    const moduleName = key.split('/').pop()
    const name = moduleName.split('.')[0]
    modules[name] = value.default
}

// 获取模板页面列表
const templatePages: any = reactive({})

const loadDiyTemplatePages = (type:any)=>{
    getDiyTemplatePages({
        type,
        mode: 'diy'
    }).then(res => {
        for (const key in res.data) {
            templatePages[key] = res.data[key];
        }
    });
}

// 全局监听自定义数据变化
watch(
    () => template.value,
    (newValue, oldValue) => {
        oldTemplate.value = oldValue;
    }
)

// 切换模板页面
const changeTemplatePage = (value: any) => {
    // 存在数据则弹框提示确认
    if (diyStore.value.length) {
        ElMessageBox.confirm(t('changeTemplatePageTips'), t('warning'), {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }).then(() => {
            diyStore.changeCurrentIndex(-99)
            diyStore.init() // 清空
            if (value) {
                let data = cloneDeep(templatePages[value].data);
                diyStore.global = data.global;
                if (data.value.length) {
                    diyStore.value = data.value
                }
            } else {
                if (route.query.title) diyStore.global.title = diyStore.typeName
            }
        }).catch(() => {
            // 还原
            template.value = oldTemplate.value
        });
    } else {
        diyStore.init() // 清空
        if (value) {
            let data = cloneDeep(templatePages[value].data)
            diyStore.global = data.global
            if (data.value.length) {
                diyStore.value = data.value
            }
        } else {
            if (route.query.title) diyStore.global.title = diyStore.typeName
        }
    }
};

// 全局监听自定义数据变化
watch(
    () => diyStore,
    (newValue, oldValue) => {
        const data = {
            id: newValue.id,
            name: newValue.name,
            pageTitle: newValue.pageTitle,
            title: newValue.global.title,
            value: JSON.stringify({
                global: toRaw(newValue.global),
                value: toRaw(newValue.value)
            })
        }

        diyStore.postMessage()
        isChange.value = JSON.stringify(data) == JSON.stringify(originData)
    },
    { deep: true }
)

// 根据当前页面路由查询页面初始化数据
initPage({
    id: route.query.id,
    name: route.query.name,
    url: route.query.url,
    type: route.query.type,
    title: route.query.title
}).then(async (res) => {
    const data = res.data
    diyStore.init() // 初始化清空数据
    diyStore.id = data.id || 0
    diyStore.name = data.name
    diyStore.pageTitle = data.page_title
    diyStore.type = data.type
    diyStore.typeName = data.type_name
    diyStore.templateName = data.template
    template.value = data.template;
    diyStore.isDefault = data.is_default
    diyStore.pageMode = data.mode
    if (data.global) {
        for (const key in data.global) {
            if(data.global[key]) {
                for (const childKey in data.global[key]) {
                    diyStore.global[key][childKey] = data.global[key][childKey]
                }
            }else{
                diyStore.global[key] = data.global[key]
            }
        }
    }
    if (data.value) {
        const sources = JSON.parse(data.value)
        diyStore.global = sources.global
        if (sources.value.length) {
            diyStore.value = sources.value
            // diyStore.changeCurrentIndex(0,diyStore.value[0]);
        }
    } else {
        diyStore.global.title = data.title
    }

    // 初始化原数据
    originData.id = diyStore.id
    originData.name = diyStore.name
    originData.pageTitle = diyStore.pageTitle
    originData.title = diyStore.global.title
    originData.value = JSON.stringify({
        global: toRaw(diyStore.global),
        value: toRaw(diyStore.value)
    })

    component.value = data.component
    for (const type in component.value) {
        componentType.push(type)
        for (const key in component.value[type].list) {
            const com = cloneDeep(component.value[type].list[key])
            com.id = diyStore.generateRandom()
            com.componentName = key
            com.componentTitle = com.title
            Object.assign(com, com.value)
            delete com.name
            delete com.title
            delete com.value
            delete com.type
            delete com.icon
            diyStore.components.push(com)
        }
    }

    loadDiyTemplatePages(data.type)

    // 加载预览
    wapDomain.value = data.domain_url.wap_domain
    wapUrl.value = data.domain_url.wap_url
    page.value = data.page

    let repeat = true; // 防重复执行

    // 开发模式下执行
    if (import.meta.env.MODE == 'development') {

        // env文件配置过wap域名
        if (wapDomain.value) {
            wapUrl.value = wapDomain.value + '/wap'
            repeat = false
            setDomain()
        }

        let wap_domain_storage = storage.get('wap_domain')
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

const uniAppLoadStatus = ref(false) // uni-app 加载状态，true：加载完成，false：未完成

// 监听组件数据 uni-app端
window.addEventListener('message', (event) => {
    try {
        let data = {
            type: ''
        };
        if(typeof event.data == 'string') {
            data = JSON.parse(event.data)
        }else if(typeof event.data == 'object') {
            data = event.data
        }
        if (!data.type) return

        switch (data.type) {
            case 'appOnLaunch':
            case 'appOnReady':
                // uni-app 加载完成
                loadingDev.value = false
                loadingIframe.value = true
                let loadTime = new Date().getTime()
                difference.value = loadTime - timeIframe.value
                uniAppLoadStatus.value = true // 加载完成
                break
            case 'init':
                // 初始化，与uniapp建立连接传输数据
                diyStore.load = true
                diyStore.postMessage()
                break
            case 'change':
                // 切换
                diyStore.changeCurrentIndex(data.index, data.component)
                break
            case 'data':
                // 传数据
                diyStore.changeCurrentIndex(data.index, data.component)
                diyStore.global = data.global
                diyStore.value = data.value
                break
        }
    } catch (e) {
        console.log('diy edit 后台接受数据错误', e)
    }
}, false)

const saveWapDomain = () => {
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
    wapPreview.value = `${wapUrl.value}${page.value}?mode=decorate` // 模式：decorate 装修 访问预览页面

    const send = ()=>{
        timeIframe.value = new Date().getTime()
        postMessage()
    }

    // 同步发送一次消息
    send()

    // 如果同步发送消息的 uni-app没有接收到回应，则定时发送消息
    let sendCount = 0;
    let timeInterVal = setInterval(()=>{
        // 接收 uni-app 发送的消息 或者 发送50次后未响应，则停止发送
        if(uniAppLoadStatus.value || sendCount >= 50){
            clearInterval(timeInterVal)
            return
        }

        send()
        sendCount++;
    },200)

    // 如果10秒内加载不出来，则需要配置域名
    setTimeout(() => {
        if (difference.value == 0) initLoad()
    }, 1000 * 10)

}

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
}

const isRepeat = ref(false)
const save = (callback: any) => {
    if (!diyStore.verify()) {
        return
    }

    if (isRepeat.value) return
    isRepeat.value = true

    diyStore.templateName = template.value;

    let data = {
        id: diyStore.id,
        name: diyStore.name,
        page_title: diyStore.pageTitle,
        title: diyStore.global.title,
        type: diyStore.type,
        template: diyStore.templateName,
        is_default: diyStore.isDefault,
        is_change: isChange.value ? 0 : 1,
        value: JSON.stringify({
            global: toRaw(diyStore.global),
            value: toRaw(diyStore.value)
        })
    }

    const api = diyStore.id ? editDiyPage : addDiyPage
    api(data).then((res: any) => {
        isRepeat.value = false
        if (res.code == 1) {
            if (diyStore.id) {
                isRepeat.value = false // 不刷新
            } else {
                location.href = `${location.origin}${backPath}`;
            }
            if (callback) callback(res.data.id)
        }
    }).catch(() => {
        isRepeat.value = false
    })
}

// 预览
const preview = () => {
    save((id: number) => {
        id = diyStore.id || id
        const url = router.resolve({
            path: '/admin/preview/wap',
            query: {
                page: page.value + '?id=' + id
            }
        })
        window.open(url.href)
    })
}

const settingTips = () => {
    window.open('https://www.kancloud.cn/niucloud/niucloud-admin-develop/3213393')
}
</script>

<style lang="scss">
.el-collapse-item__wrap {
    border-bottom: none;
}

.el-collapse-item__content {
    padding-bottom: 0;
}

.el-collapse-item__header {
    font-size: var(--el-font-size-base);
}

.display-block {
    .el-form-item__content {
        display: block;
    }
}

.edit-component-wrap {
    .content-wrap,
    .style-wrap {
        .edit-attr-item-wrap {
            border-top: 2px solid var(--el-color-info-light-8);
            padding-top: 20px;

            &:first-of-type {
                border-top: none;
                padding-top: 0;
            }
        }
    }
}
.diy-nav-slider {
    .el-slider__input {
        width: 100px;
    }
}
</style>
<style lang="scss" scoped>
.full-container {
    height: calc(100vh - 60px);
}

.preview-iframe {
    height: calc(100vh - 160px);
}

.component-list {
    background: var(--el-bg-color);
}

.component-list ul li {
    &:not(.disabled):hover {
        color: var(--el-color-primary);
        background: var(--el-color-primary-light-9);
    }
}

.diy-view-wrap {
    background: var(--el-bg-color-page);
}

.diy-view-wrap .preview-head {
    background-image: url(@/app/assets/images/diy_preview_head.png);
    background-color: var(--el-bg-color);
}

.quick-action {
    background: var(--el-bg-color);
}

.edit-attribute-wrap {
    background: var(--el-bg-color);
}

.edit-attribute-wrap .box-card {
    border: none;
}

.diy-view-wrap .preview-head {
    padding: 28px 15px 0;

    .content-wrap {
        height: 30px;
    }

    &.style-1 {
        .content-wrap {
            .title-wrap {
                height: 30px;
                line-height: 30px;
            }
        }
    }

    &.style-2 {
        .content-wrap {
            .title-wrap {
                display: flex;
                align-items: center;

                > div {
                    height: 30px;
                    line-height: 30px;
                    max-width: 150px;
                    font-size: 14px;

                    &:last-child {
                        overflow: hidden; //超出的文本隐藏
                        text-overflow: ellipsis; //用省略号显示
                        white-space: nowrap; //不换行
                        flex: 1;
                        max-width: 200px;
                    }
                }
            }
        }
    }

    &.style-3 {
        .content-wrap {
            display: flex;
            align-items: center;

            .title-wrap {
                height: 30px;
                max-width: 85px;
                margin-right: 5px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .search {
                flex: 1;
                padding-right: 10px;
                padding-left: 31px;
                position: relative;
                background-color: #fff;
                text-align: left;
                border-radius: 30px;
                height: 30px;
                line-height: 30px;
                border: 1px solid #eeeeee;
                color: rgb(102, 102, 102);
                display: flex;
                align-items: center;
                margin-right: 105px;
                overflow: hidden;
                box-sizing: border-box;

                span {
                    overflow: hidden; //超出的文本隐藏
                    text-overflow: ellipsis; //用省略号显示
                    white-space: nowrap; //不换行
                }

                .iconfont {
                    color: #909399;
                    font-size: 16px;
                    margin-right: 5px;
                }
            }
        }
    }

    &.style-4 {
        .content-wrap {
            display: flex;
            align-items: center;

            .title-wrap {
                flex: none;
                margin: 0 5px;
                max-width: 180px;
                font-size: 14px;
            }
        }
    }
}
</style>

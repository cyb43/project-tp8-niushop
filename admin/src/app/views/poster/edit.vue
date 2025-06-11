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
                <span class="mr-[5px] text-[14px]">{{ t('decorating') }}：{{ posterStore.typeName }}</span>
            </div>

            <div class="flex items-center">
                <span class="text-white mr-[10px] text-base">{{ t('templatePosterPlaceholder') }}</span>
                <div class="w-[180px]">
                    <el-select size="small" v-model="template" :placeholder="t('templatePosterPlaceholder')" @change="changeTemplatePoster">
                        <el-option :label="t('templatePosterEmpty')" value="" />
                        <el-option v-for="(item, index) in templatePoster" :label="item.name" :value="index" :key="index"/>
                    </el-select>
                </div>
            </div>
            <div class="flex-1"></div>
            <el-button @click="preview()" v-if="posterStore.id">{{ t('preview') }}</el-button>
            <el-button @click="save()">{{ t('save') }}</el-button>
        </el-header>

        <div class="full-container flex flex-row flex-1 bg-page">

            <div class="component-list w-[290px]">

                <!-- 组件列表区域 -->
                <el-scrollbar class="px-[10px]">
                    <el-collapse v-model="activeNames" @change="handleChange">
                        <el-collapse-item v-for="(item, key) in component" :key="key" :title="item.title" :name="key">
                            <ul class="flex flex-row flex-wrap">
                                <li v-for="(compItem, compKey) in item.list" :key="compKey" class="w-2/6 text-center cursor-pointer h-[65px]" :title="compItem.title" @click="posterStore.addComponent(compKey, compItem)">
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
                    <el-button class="page-btn absolute right-[20px]" @click="posterStore.changeCurrentIndex(-99)">{{ t('posterSet') }}</el-button>
                    <div class="diy-view-wrap w-[360px] shadow-lg mx-auto">
                        <div class="preview-head bg-no-repeat bg-center bg-cover cursor-pointer h-[64px]" @click="posterStore.changeCurrentIndex(-99)">
                            <div class="content-wrap">
                                <div class="title-wrap text-center text-[14px]">{{ posterStore.name }}</div>
                            </div>
                        </div>
                        <div class="preview-block relative max-h-[640px]">
                            <ul class="quick-action absolute text-center -right-[70px] top-[20px] w-[42px] rounded shadow-md">
                                <el-tooltip effect="light" :content="t('moveUpComponentZIndex')" placement="right">
                                    <icon name="iconfont iconjiantoushang" size="20px" class="block cursor-pointer leading-[40px]" @click="posterStore.moveUpComponent" />
                                </el-tooltip>
                                <el-tooltip effect="light" :content="t('moveDownComponentZIndex')" placement="right">
                                    <icon name="iconfont iconjiantouxia" size="20px" class="block cursor-pointer leading-[40px]" @click="posterStore.moveDownComponent" />
                                </el-tooltip>
                                <el-tooltip effect="light" :content="t('copyComponent')" placement="right">
                                    <icon name="iconfont iconcopy-line" size="20px" class="block cursor-pointer leading-[40px]" @click="posterStore.copyComponent" />
                                </el-tooltip>
                                <el-tooltip effect="light" :content="t('delComponent')" placement="right">
                                    <icon name="iconfont icondelete-line" size="20px" class="block cursor-pointer leading-[40px]" @click="posterStore.delComponent" />
                                </el-tooltip>
                                <el-tooltip effect="light" :content="t('resetComponent')" placement="right">
                                    <icon name="iconfont iconloader-line" size="20px" class="block cursor-pointer leading-[40px]" @click="posterStore.resetComponent" />
                                </el-tooltip>
                            </ul>

                            <!-- 组件预览渲染区域 -->
                            <div class="preview-iframe" :style="posterStore.getGlobalStyle()" @click="posterStore.changeCurrentIndex(-99)">
                                <div class="item-wrap area-box select-none max-w-[720px] cursor-move" v-for="(item,index) in posterStore.value" :id="item.id" :key="item.id"
                                     :style="previewIframeStyle(item)"
                                     :class="{ 'selected' : posterStore.currentIndex == index }"
                                     @mousedown="posterStore.mouseDown($event,item.id,index)"
                                     @click.stop="posterStore.changeCurrentIndex(index,item)">
                                    <component :is="modules['preview-' + item.path]" :value="item"/>
                                    <span class="box1" @mousedown.stop="posterStore.resizeMouseDown($event,item, index)"></span>
                                    <span class="box2" @mousedown.stop="posterStore.resizeMouseDown($event,item, index)"></span>
                                    <span class="box3" @mousedown.stop="posterStore.resizeMouseDown($event,item, index)"></span>
                                    <span class="box4" @mousedown.stop="posterStore.resizeMouseDown($event,item, index)"></span>
                                </div>

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
                                <span class="title flex-1">{{ posterStore.currentIndex == -99 ? t('posterSet') : posterStore.editComponent?.componentTitle }}</span>
                            </div>
                        </template>

                        <div class="edit-component-wrap">

                            <component v-if="posterStore.currentComponent" :is="modules[posterStore.currentComponent]" :value="posterStore.value[posterStore.currentIndex]">
                                <template #common>
                                    <div class="edit-attr-item-wrap">
                                        <h3 class="mb-[10px]">{{ t('componentStyleTitle') }}</h3>
                                        <el-form label-width="100px" class="px-[10px]">

                                            <!-- 角度旋转、这里根据类型展示 文本、图片、绘画的编辑属性 -->

                                            <el-form-item :label="t('zIndex')">
                                                <span>{{ posterStore.editComponent.zIndex }}</span>
                                            </el-form-item>

                                            <!-- <el-form-item :label="t('coordinate')">
                                                <el-slider v-model="posterStore.editComponent.x" show-input size="small" class="ml-[10px]" :min="0" :max="posterStore.getMaxX()" />
                                                <div class="my-[10px]"></div>
                                                <el-slider v-model="posterStore.editComponent.y" show-input size="small" class="ml-[10px]" :min="0" :max="posterStore.getMaxY()" />
                                            </el-form-item>
                                            <div class="ml-[92px] mb-[18px] text-sm text-gray-400">{{ t('coordinateTips') }}</div> -->

                                            <!-- 文本 -->
                                            <template v-if="posterStore.editComponent.type == 'text'">
                                                <el-form-item :label="t('textFontSize')">
                                                    <el-slider v-model="posterStore.editComponent.fontSize" show-input size="small" class="ml-[10px]" :min="14" :max="100" />
                                                </el-form-item>
                                                <el-form-item :label="t('textColor')">
                                                    <el-color-picker v-model="posterStore.editComponent.fontColor" />
                                                </el-form-item>
                                                <el-form-item :label="t('weight')">
                                                    <el-switch v-model="posterStore.editComponent.weight" />
                                                </el-form-item>
                                                <!-- <el-form-item :label="t('space')">
                                                    <el-slider v-model="posterStore.editComponent.space" show-input size="small" class="ml-[10px]" :min="0" :max="20" />
                                                </el-form-item> -->
                                                <el-form-item :label="t('lineHeight')">
                                                    <el-slider v-model="posterStore.editComponent.lineHeight" show-input size="small" class="ml-[10px]" :min="0" :max="50" />
                                                </el-form-item>
                                            </template>

                                            <!-- 图片 -->
                                            <template v-if="posterStore.editComponent.type == 'image'">

                                                <el-form-item :label="t('width')">
                                                    <el-slider v-model="posterStore.editComponent.width" show-input size="small" class="ml-[10px]" :min="30" :max="posterStore.getMaxWidth()" />
                                                </el-form-item>
                                            </template>

                                            <!-- 绘画 -->
                                            <template v-if="posterStore.editComponent.type == 'draw'">
                                                <el-form-item :label="t('drawType')">
                                                    <el-radio-group v-model="posterStore.editComponent.drawType">
                                                        <el-radio label="Polygon">{{ t('polygon') }}</el-radio>
                                                    </el-radio-group>
                                                </el-form-item>
                                                <el-form-item :label="t('bgColor')">
                                                    <el-color-picker v-model="posterStore.editComponent.bgColor" />
                                                </el-form-item>
                                            </template>

                                            <!-- 对齐方式 -->
                                            <template v-if="posterStore.editComponent.type != 'draw' && posterStore.editComponent.type != 'image' && posterStore.editComponent.type != 'qrcode'">
                                                <el-form-item :label="t('horizontalAlignment')">
                                                    <ul class="flex items-center">
                                                        <template v-for="(item,i) in xAlignList">
                                                            <li :class="['w-[50px] h-[32px] flex items-center justify-center border-solid  border-[1px] border-[#eee] cursor-pointer', {'border-r-transparent': xAlignList.length != (i+1)}, (item.className == posterStore.editComponent.x) ?  '!border-[var(--el-color-primary)]' : '' ]" @click="alignChangeFn('x',item)">
                                                                <span :class="['iconfont !text-[20px]', item.src]" :title="item.name"></span>
                                                            </li>
                                                        </template>
                                                    </ul>
                                                    <div class="text-sm mt-[10px] leading-[1.4] text-gray-400">{{ t('AlignTips') }}</div>
                                                </el-form-item>

                                                <el-form-item :label="t('verticalAlignment')">
                                                    <ul class="flex items-center">
                                                        <template v-for="(item,i) in yAlignList">
                                                            <li :class="['w-[50px] h-[32px] flex items-center justify-center border-solid  border-[1px] border-[#eee] cursor-pointer', {'border-r-transparent': yAlignList.length != (i+1)}, (item.className == posterStore.editComponent.y) ?  '!border-[var(--el-color-primary)]' : '' ]" @click="alignChangeFn('y',item)">
                                                                <span :class="['iconfont !text-[20px]', item.src]" :title="item.name"></span>
                                                            </li>
                                                        </template>
                                                    </ul>
                                                </el-form-item>
                                            </template>

<!--                                            <el-form-item :label="t('旋转角度')">-->
<!--                                                <el-slider v-model="posterStore.editComponent.angle" show-input size="small" class="ml-[10px]" :min="0" :max="360" />-->
<!--                                            </el-form-item>-->

                                        </el-form>
                                    </div>
                                </template>
                            </component>

                        </div>

                    </el-card>
                </el-scrollbar>

            </div>

        </div>

        <!--预览海报-->
        <el-dialog v-model="previewDialogVisible" :title="t('previewDialogTitle')" width="400px" height="640px">

            <div v-if="previewPosterUrl">
                <img :src="img(previewPosterUrl)" class="w-[360px] h-[640px]" />
            </div>
        </el-dialog>

    </div>
</template>

<script lang="ts" setup>
import { ref, reactive, toRaw, watch, inject } from 'vue'
import { t } from '@/lang'
import { ArrowLeft } from "@element-plus/icons-vue"
import { img } from '@/utils/common'
import { useRoute, useRouter } from 'vue-router'
import { cloneDeep } from 'lodash-es'
import { ElMessageBox } from 'element-plus'
import usePosterStore from '@/stores/modules/poster'
import { initPoster, addPoster, editPoster, getPosterTemplate, getPreviewPoster } from '@/app/api/poster'

const setLayout = inject('setLayout')
setLayout('decorate')

const posterStore = usePosterStore()

const route = useRoute()
const router = useRouter()

if(route && route.query){
    route.query.id = route.query.id || 0
    route.query.type = route.query.type || '' // 海报类型
    route.query.name = route.query.name || ''
    route.query.back = route.query.back || '/admin/poster/list'
}

const backPath:any = route.query.back
const template = ref('')
const oldTemplate = ref('')

const component = ref([])
const componentType: string[] = reactive([])
const page = ref('')

const activeNames = ref(componentType)
const handleChange = (val: string[]) => {
}

const previewIframeStyle = (data: any)=>{
    let style: any = {
        transform: '',
        zIndex: '',
        top: '',
        left: '',
        right: '',
        bottom: ''
    }
    style.transform =  `rotate(${data.angle}deg)`
    style.zIndex =  `${data.zIndex}`
    switch(data.y) {
        case 'top':
            style.top = 0
            break;
        case 'center':
            style.top = '50%'
            style.transform = style.transform + ' translateY(-50%)'
            break;
        case 'bottom':
            style.bottom = 0
            break;
        default:
            style.top = data.y + 'px'
    }
    switch(data.x) {
        case 'left':
            style.left = 0
            break;
        case 'center':
            style.left = '50%'
            style.transform = style.transform + ' translateX(-50%)'
            break;
        case 'right':
            style.right = 0
            break;
        default:
            style.left = data.x + 'px'
    }
    return style
}

// 水平方向对齐
const xAlignList = ref([
    {
        name: '左',
        src: 'iconzuoduiqi1',
        className: 'left'
    },
    {
        name: '中',
        src: 'iconshuipingjuzhong1',
        className: 'center'
    },
    {
        name: '右',
        src: 'iconyouduiqi1',
        className: 'right'
    }
])

// 水平方向对齐
const yAlignList = ref([
    {
        name: '上',
        src: 'icondingduiqi1',
        className: 'top'
    },
    {
        name: '中',
        src: 'iconchuizhijuzhong1',
        className: 'center'
    },
    {
        name: '下',
        src: 'icondiduiqi1',
        className: 'bottom'
    },
])
const alignChangeFn = (type: any,data: any)=>{
    posterStore.editComponent[type] = data.className
}

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
const addonModulesFiles = import.meta.glob('@/addon/**/views/poster/components/*.vue', { eager: true })
addonModulesFiles && Object.assign(modulesFiles, addonModulesFiles)

const modules:any = {}
for (const [key, value] of Object.entries(modulesFiles)) {
    const moduleName:any = key.split('/').pop()
    const name = moduleName.split('.')[0]
    modules[name] = value.default
}

// 获取模板页面列表
const templatePoster: any = reactive([])

const loadPosterTemplate = ()=> {
    getPosterTemplate({
        addon: posterStore.addon,
        type: posterStore.type,
    }).then(res => {
        if (res.data) {
            templatePoster.splice(0, templatePoster.length, ...res.data)
            if (posterStore.id) {
                template.value = templatePoster.findIndex((item:any) => item.type == posterStore.type)
            }
        }
    })
}

// 全局监听自定义数据变化
watch(
    () => template.value,
    (newValue, oldValue) => {
        oldTemplate.value = oldValue
    }
)

// 切换模板页面
const changeTemplatePoster = (index:any)=> {
    // 存在数据则弹框提示确认
    if(posterStore.value.length) {
        ElMessageBox.confirm(t('changeTemplatePosterTips'), t('warning'), {
            confirmButtonText: t('confirm'),
            cancelButtonText: t('cancel'),
            type: 'warning'
        }).then(() => {
            posterStore.changeCurrentIndex(-99)
            if (index !== '') {
                let data = templatePoster[index].data
                posterStore.global = data.global
                if (data.value.length) {
                    posterStore.value = data.value
                }
            } else {
                // 清空
                posterStore.init()
            }
        }).catch(() => {
            // 还原
            template.value = oldTemplate.value
        })
    }else{
        if (index !== '') {
            let data = templatePoster[index].data
            posterStore.global = data.global
            if (data.value.length) {
                posterStore.value = data.value
            }
        } else {
            // 清空
            posterStore.init()
        }
    }
}

// 根据当前页面路由查询页面初始化数据
initPoster({
    id: route.query.id,
    type: route.query.type,
    name: route.query.name
}).then(async (res:any) => {
    const data = res.data

    posterStore.init() // 初始化清空数据

    posterStore.id = data.id
    posterStore.name = data.name
    posterStore.channel = data.channel
    posterStore.status = data.status
    posterStore.isDefault = data.is_default
    posterStore.addon = data.addon
    posterStore.type = data.type
    posterStore.typeName = data.poster_type.name

    if (data.value) {
        const sources = data.value
        posterStore.global = sources.global
        if (sources.value.length) {
            posterStore.value = sources.value
        }
    }

    component.value = data.component
    for (const type in component.value) {
        componentType.push(type)
        for (const key in component.value[type].list) {
            const com = cloneDeep(component.value[type].list[key])
            com.id = posterStore.generateRandom()
            com.componentName = key
            com.componentTitle = com.title
            delete com.title
            delete com.icon
            posterStore.components.push(com)
        }
    }

    loadPosterTemplate()

})

const isRepeat = ref(false)
const save = (callback: any) => {
    if (!posterStore.verify()) {
        return
    }

    if (isRepeat.value) return
    isRepeat.value = true

    posterStore.value.forEach((item:any,index:any, originalArr:any)=> {
        const box: any = document.getElementById(item.id)
        if (box) {
            item.width = box.offsetWidth
            item.height = box.offsetHeight
            if (item.type == 'draw') {
                // [x,y]：左上，右上，右下，左下
                let leftTop = [item.x * 1, item.y * 1] // 左上
                let rightTop = [(item.x + item.width) * 1, item.y * 1] // 右上
                let rightBottom = [(item.x + item.width) * 1, (item.y + item.height) * 1] // 右下
                let leftBottom = [item.x * 1, (item.y + item.height) * 1] // 左下
                item.points = [leftTop, rightTop, rightBottom, leftBottom]
            }
        }
        delete item.verify
    })

    let data = {
        id: posterStore.id,
        name: posterStore.name,
        type: posterStore.type,
        status: posterStore.status,
        is_default:posterStore.isDefault,
        channel: posterStore.channel,
        addon: posterStore.addon,
        value: JSON.stringify({
            global: toRaw(posterStore.global),
            value: toRaw(posterStore.value)
        })
    }

    const api = posterStore.id ? editPoster : addPoster
    api(data).then((res: any) => {
        isRepeat.value = false
        if (res.code == 1) {
            if (posterStore.id) {
                isRepeat.value = false // 不刷新
            } else {
                location.href = `${location.origin}${backPath}`
            }
            if (callback) callback(res.data.id)
        }
    }).catch(() => {
        isRepeat.value = false
    })
}

const previewDialogVisible = ref(false)
const previewPosterUrl = ref('')

// 预览海报
const preview = () => {
    if (isRepeat.value) return
    isRepeat.value = true

    getPreviewPoster({
        id:posterStore.id,
        type:posterStore.type
    }).then(((res:any)=>{
        if(res.data) {
            previewPosterUrl.value = res.data
            previewDialogVisible.value = true
        }
        isRepeat.value = false
    }))
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
    .edit-attr-item-wrap {
        border-top: 2px solid var(--el-color-info-light-8);
        padding-top: 20px;

        &:first-of-type {
            border-top: none;
            padding-top: 0;
        }
    }
}
</style>
<style lang="scss" scoped>
.full-container {
    height: calc(100vh - 60px);
}

.preview-iframe {
    transform: scale(0.5);
	margin: 0 auto;
	width: 720px;
	height: 1280px;
	background-size: 100% 1280px;
	background-repeat: no-repeat;
	position: relative;
    transform-origin: left top;

	.item-wrap {
		position: absolute;

		&.area-box {
			background-color: rgba(255, 255, 255, 0.7);
		}

		&:hover {
			&:after {
				content: "";
				position: absolute;
				top: 0;
				left: 0;
				right: 0;
				bottom: 0;
				border: 2px dotted var(--el-color-primary);
				z-index: 1;
			}
		}

		&.selected {
			&:after {
				content: "";
				position: absolute;
				top: 0;
				left: 0;
				right: 0;
				bottom: 0;
				border: 2px solid var(--el-color-primary);
				z-index: 1;
			}

			.box1,
			.box2,
			.box3,
			.box4 {
				width: 20px;
				height: 20px;
				background-color: #fff;
				position: absolute;
				border-radius: 50%;
				border: 1px solid #666;
				z-index: 10;
			}

			.box1 {
				top: -8px;
				left: -8px;
				cursor: nw-resize;
			}

			.box2 {
				top: -8px;
				right: -8px;
				cursor: ne-resize;
			}

			.box3 {
				left: -8px;
				bottom: -8px;
				cursor: sw-resize;
			}

			.box4 {
				bottom: -8px;
				right: -8px;
				cursor: se-resize;
			}
		}

	}
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

.diy-view-wrap .preview-head{
    padding: 28px 15px 0;
    .content-wrap{
        height: 30px;
    }
    .content-wrap {
        .title-wrap {
            height: 30px;
            line-height: 30px;
        }
    }

}

</style>

<template>
    <div>
        <div @click="show">
            <slot>
                <el-input v-model="value.title" :placeholder="t('linkPlaceholder')" readonly class="link-input">
                    <template #suffix>
                        <div @click.stop="clear">
                            <el-icon v-if="value.name">
                                <Close />
                            </el-icon>
                            <el-icon v-else>
                                <ArrowRight />
                            </el-icon>
                        </div>
                    </template>
                </el-input>
            </slot>
        </div>
        <el-dialog v-model="showDialog" :title="t('selectLinkTips')" width="850px" :destroy-on-close="true" :close-on-click-modal="false" @close="cancel">
            <div class="flex items-start">
                <el-scrollbar class="w-[140px] border-r !h-[550px] link-wrap">
                    <template v-for="(item, index) in link" :key="index">
                        <template v-if="item.type == 'folder'">
                            <div class="flex h-[40px] leading-[40px] cursor-pointer px-[10px] items-center select-none mr-[10px]"
                                @click="item.foldSwitch = !item.foldSwitch">
                                <span class="flex-1">{{ item.title }}</span>
                                <el-icon>
                                    <ArrowDown v-if="item.foldSwitch" />
                                    <ArrowUp v-else />
                                </el-icon>
                            </div>
                            <div class="child-list-wrap" v-show="item.foldSwitch">
                                <div v-for="(childItem, childIndex) in item.child_list" :key="childIndex" class="h-[40px] leading-[40px] cursor-pointer hover:bg-primary-light-9 hover:text-primary select-none truncate pl-[25px] mr-[20px]" :class="[ childItem.name == parentLinkName ? 'bg-primary-light-9 text-primary' : '' ]" @click="changeParentLink(childItem)">{{ childItem.title }}</div>
                            </div>
                        </template>
                        <div v-else
                             class="h-[40px] leading-[40px] cursor-pointer px-[10px] items-center select-none hover:bg-primary-light-9 hover:text-primary mr-[20px]"
                             :class="[ item.name == parentLinkName ? 'bg-primary-light-9 text-primary' : '' ]"
                             @click="changeParentLink(item)">
                            <span>{{ item.title }}</span>
                        </div>
                    </template>
                </el-scrollbar>
                <el-scrollbar class="pl-4 !h-[550px] flex-1">
                    <el-form label-width="100px" class="px-[10px]">
                        <component v-if="dynamicComponentName" :is="dynamicComponentName" v-bind="selectLink" ref="dynamicComponentRefs" />
                        <template v-else-if="parentLinkName == 'DIY_LINK'">
                            <div class="mb-[16px]">
                                <el-form-item :label="t('diyLinkName')">
                                    <el-input v-model="selectLink.title" :placeholder="t('diyLinkNamePlaceholder')" class="!w-[300px]" />
                                </el-form-item>
                            </div>
                            <div class="mb-[16px]">
                                <el-form-item :label="t('diyLinkUrl')">
                                    <el-input v-model="selectLink.url" :placeholder="t('diyLinkUrlPlaceholder')" class="!w-[300px]" />
                                </el-form-item>
                            </div>
                            <el-form-item label=" ">
                                <div class="text-sm text-gray-400 select-text">路径必须以“/”开头，例：/app/pages/index/index</div>
                            </el-form-item>
                            <el-form-item label=" ">
                                <div class="text-sm text-gray-400 select-text">跳转外部链接“http”或“https”开头，例：https://baidu.com</div>
                            </el-form-item>
                        </template>
                        <template v-else-if="parentLinkName == 'DIY_JUMP_OTHER_APPLET'">
                            <div class="mb-[16px]">
                                <el-form-item :label="t('diyAppletId')">
                                    <el-input v-model="selectLink.appid" :placeholder="t('diyAppletIdPlaceholder')" clearable maxlength="50" class="!w-[300px]" />
                                </el-form-item>
                            </div>
                            <div class="mb-[16px]">
                                <el-form-item :label="t('diyAppletPage')">
                                    <el-input v-model="selectLink.page" :placeholder="t('diyAppletPagePlaceholder')" clearable maxlength="100" class="!w-[300px]" />
                                </el-form-item>
                            </div>
                            <el-form-item label=" ">
                                <div class="text-sm text-gray-400 select-text">仅支持小程序之间的跳转，不支持从其他渠道跳转小程序</div>
                            </el-form-item>
                            <el-form-item label=" ">
                                <div class="text-sm text-gray-400 select-text">小程序路径格式如：app/pages/index/index</div>
                            </el-form-item>
                        </template>
                        <template v-else-if="parentLinkName == 'DIY_MAKE_PHONE_CALL'">
                            <div class="mb-[16px]">
                                <el-form-item :label="t('diyMakePhone')">
                                    <el-input v-model="selectLink.mobile" :placeholder="t('diyMakePhonePlaceholder')" clearable maxlength="30" class="!w-[300px]" />
                                </el-form-item>
                            </div>
                            <el-form-item label=" ">
                                <div class="text-sm text-gray-400 select-text">电话号码支持手机号码和固定电话</div>
                            </el-form-item>
                        </template>
                        <div v-else class="flex flex-wrap">
                            <div v-for="(item, index) in childList" :key="index"
                                 class="border border-br rounded-[3px] mr-[10px] mb-[10px] h-[32px] leading-[32px] cursor-pointer hover:bg-primary-light-9 px-[10px] hover:text-primary"
                                 :class="{ 'border-primary text-primary': (parentLinkName != 'DIY_PAGE' && item.name == selectLink.name) || (parentLinkName == 'DIY_PAGE' && item.url == selectLink.url) }"
                                 @click="changeChildLink(item)">{{ item.title }}
                            </div>
                        </div>
                    </el-form>
                </el-scrollbar>
            </div>

            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="cancel">{{ t('cancel') }}</el-button>
                    <el-button type="primary" @click="save">{{ t('confirm') }}</el-button>
                </span>
            </template>
        </el-dialog>
    </div>
</template>

<script lang="ts" setup>
import { t } from '@/lang'
import { ref, computed, defineAsyncComponent } from 'vue'
import { cloneDeep } from 'lodash-es'
import { getLink } from '@/app/api/diy'
import { ElMessage } from 'element-plus'

const prop = defineProps({
    modelValue: {
        type: Object,
        default: () => {
        }
    },
    ignore: {
        type: Array,
        default: []
    }
})

const emit = defineEmits(['update:modelValue', 'confirm', 'success'])

const value: any = computed({
    get() {
        return prop.modelValue
    },
    set(value) {
        emit('update:modelValue', value)
    }
})

const showDialog = ref(false)

const isDrag = ref(false)

const link: any = ref([])

const parentLinkName = ref('')

const childList: any = ref([])

const dynamicComponentName: any = ref('') // 动态组件名称

const dynamicComponentRefs: any = ref(null) // 动态组件ref

const selectLink: any = ref([])

const modules: any = import.meta.glob('@/**/*.vue')

const show = () => {
    getLinkFn(() => {
        // 每次打开时赋值
        if (value.value.name != '') {
            selectLink.value = cloneDeep(value.value)
            parentLinkName.value = selectLink.value.parent
            for (const key in link.value) {
                if (link.value[key].type == 'folder') {
                    // 兼容以前数据结构
                    if (link.value[key].name == parentLinkName.value) {
                        changeParentLink(link.value[key].child_list[0])
                    } else {
                        for (let i = 0; i < link.value[key].child_list.length; i++) {
                            if (link.value[key].child_list[i].name == parentLinkName.value) {
                                changeParentLink(link.value[key].child_list[i])
                                break
                            }
                        }
                    }
                } else {
                    if (link.value[key].name == parentLinkName.value) {
                        changeParentLink(link.value[key])
                    }
                }
            }
        }
        showDialog.value = true
        isDrag.value = true
        emit('confirm', isDrag.value)
    })
}

const getLinkFn = (callback: any = null) => {
    getLink({}).then((res: any) => {
        link.value = res.data
        if(link.value && link.value.length == 0) return;

        if (prop.ignore && prop.ignore.length) {
            for (let key in link.value) {
                for (let i = 0; i < prop.ignore.length; i++) {
                    if (key == prop.ignore[i]) {
                        delete link.value[key]
                        break
                    }
                }
                if (link.value[key] && link.value[key].child_list) {
                    for (let i = 0; i < link.value[key].child_list.length; i++) {
                        for (let j = 0; j < prop.ignore.length; j++) {
                            if (link.value[key].child_list[i].name == prop.ignore[j]) {
                                console.log(link.value[key].child_list[i])
                                link.value[key].child_list.splice(i, 1)
                                break
                            }
                        }
                    }
                }
            }
        }

        // 默认全部展开
        for (const field in link.value) {
            if (link.value[field].type == 'folder') {
                link.value[field].foldSwitch = true
            }
        }

        const firstLink: any = Object.values(link.value)[0]
        let parentName: any = ''
        if (firstLink.type == 'folder') {
            childList.value = firstLink.child_list[0].child_list
            parentName = firstLink.child_list[0].name
            if (!firstLink.child_list[0].component) {
                dynamicComponentName.value = ''
            }
        } else {
            childList.value = firstLink.child_list
            parentName = firstLink.parent_name
            dynamicComponentName.value = ''
        }

        if (value.value.name != '') {
            selectLink.value = cloneDeep(value.value)
        } else {
            selectLink.value = {
                parent: parentName
            }
        }
        parentLinkName.value = selectLink.value.parent

        if (callback) callback()
    })
}

// 选择父级链接
const changeParentLink = (item: any) => {
    parentLinkName.value = item.name
    childList.value = item.child_list
    if (item.component) {
        dynamicComponentName.value = item.component
        dynamicComponentName.value = defineAsyncComponent(modules[dynamicComponentName.value])
    } else {
        dynamicComponentName.value = ''
    }
}

// 选择子链接
const changeChildLink = (item: any) => {
    delete item.is_share
    Object.assign(selectLink.value, item)
}

const clear = () => {
    value.value = {
        name: '',
        parent: '',
        title: '',
        url: ''
    }
}

const save = () => {
    const fields = ['name', 'parent', 'title', 'url', 'appid', 'mobile', 'action', 'page']
    if (dynamicComponentName.value && dynamicComponentRefs.value) {
        // 扩展链接
        const data = dynamicComponentRefs.value.getData()
        if (!data) return

        // 删除上次选择的关联字段
        for (const key in selectLink.value) {
            if (fields.indexOf(key) == -1) {
                delete selectLink.value[key]
            }
        }
        Object.assign(selectLink.value, data)
    } else if (parentLinkName.value === 'DIY_LINK') {
        // 自定义链接

        if (!selectLink.value.title) {
            ElMessage({
                message: t('diyLinkNameNotEmpty'),
                type: 'warning'
            })
            return
        }

        if (!selectLink.value.url) {
            ElMessage({
                message: t('diyLinkUrlNotEmpty'),
                type: 'warning'
            })
            return
        }

        selectLink.value.name = parentLinkName.value
        selectLink.value.action = ''

        delete selectLink.value.appid
        delete selectLink.value.mobile

    } else if (parentLinkName.value == 'DIY_PAGE') {
        // 自定义页面

        selectLink.value.name = parentLinkName.value
        selectLink.value.action = 'decorate';

        delete selectLink.value.appid;
        delete selectLink.value.mobile;

    } else if (parentLinkName.value == 'DIY_JUMP_OTHER_APPLET') {
        // 小程序跳转

        if (!selectLink.value.appid) {
            ElMessage({
                message: t('diyAppletIdNotEmpty'),
                type: 'warning'
            });
            return
        }

        if (!selectLink.value.page) {
            ElMessage({
                message: t('diyAppletPageNotEmpty'),
                type: 'warning'
            });
            return
        }

        selectLink.value.name = parentLinkName.value
        selectLink.value.title = '微信小程序-' + selectLink.value.appid
        selectLink.value.action = ''

        delete selectLink.value.url
        delete selectLink.value.mobile
    } else if (parentLinkName.value == 'DIY_MAKE_PHONE_CALL') {
        // 拨打电话

        if (!selectLink.value.mobile) {
            ElMessage({
                message: t('diyMakePhoneNotEmpty'),
                type: 'warning'
            })
            return
        }

        selectLink.value.name = parentLinkName.value
        selectLink.value.title = '拨打电话：' + selectLink.value.mobile
        selectLink.value.action = ''

        delete selectLink.value.url
        delete selectLink.value.appid
    }

    selectLink.value.parent = parentLinkName.value

    // 删除无用字段
    if (dynamicComponentName.value == '') {
        for (const key in selectLink.value) {
            if (fields.indexOf(key) == -1) {
                delete selectLink.value[key]
            }
        }
    }

    value.value = cloneDeep(selectLink.value)
    showDialog.value = false
    isDrag.value = false
    emit('confirm', isDrag.value)
    emit('success')
}

const cancel = () => {
    showDialog.value = false
    isDrag.value = false
    emit('confirm', isDrag.value)
}
defineExpose({
    showDialog
})
</script>

<style lang="scss">
.link-wrap{

}
.link-input .el-input__inner {
    cursor: pointer;
}
</style>

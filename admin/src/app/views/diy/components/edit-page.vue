<template>
    <!-- 内容 -->
    <div class="content-wrap" v-show="diyStore.editTab == 'content'">

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('pageContent') }}</h3>
            <el-form label-width="80px" class="px-[10px]" @submit.prevent>
                <el-form-item :label="t('diyPageTitle')">
                    <el-input v-model.trim="diyStore.pageTitle" :placeholder="t('diyPageTitlePlaceholder')" clearable
                              maxlength="16" show-word-limit />
                    <div class="text-sm text-gray-400">{{ t('pageTitleTips') }}</div>
                </el-form-item>
            </el-form>
        </div>

        <!-- 表单布局 页面设置 -->
        <slot name="content"></slot>

        <div class="edit-attr-item-wrap" v-if="diyStore.global.topStatusBar.control">
            <h3 class="mb-[10px]">{{ t('statusBarContent') }}</h3>
            <el-form label-width="80px" class="px-[10px]" @submit.prevent>
                <el-form-item :label="t('topStatusBarNav')" class="display-block">
                    <el-switch v-model="diyStore.global.topStatusBar.isShow" />
                    <div class="text-sm text-gray-400">{{ t('statusBarSwitchTips') }}</div>
                </el-form-item>
                <template v-if="diyStore.global.topStatusBar.isShow">
                    <el-form-item :label="t('diyTitle')">
                        <el-input v-model.trim="diyStore.global.title" :placeholder="t('diyTitlePlaceholder')" clearable maxlength="12" show-word-limit />
                        <div class="text-sm text-gray-400">{{ t('titleTips') }}</div>
                    </el-form-item>
                    <el-form-item :label="t('selectStyle')" class="display-block">
                        <div class="flex">
                            <span class="text-primary flex-1 cursor-pointer" @click="showStyle">{{ diyStore.global.topStatusBar.styleName }}</span>
                            <el-icon>
                                <ArrowRight />
                            </el-icon>
                        </div>
                        <div class="text-sm text-gray-400 leading-[1.5]">{{ t('styleShowTips') }}</div>
                    </el-form-item>
                    <el-form-item :label="t('topStatusBarImg')" v-if="['style-2','style-3'].indexOf(diyStore.global.topStatusBar.style) > -1">
                        <upload-image v-model="diyStore.global.topStatusBar.imgUrl" :limit="1" />
                        <div class="text-sm text-gray-400 mt-[10px]">{{ t('topStatusBarImgTips') }}</div>
                    </el-form-item>
                    <el-form-item :label="t('topStatusBarSearchName')" v-if="'style-3' == diyStore.global.topStatusBar.style">
                        <el-input v-model.trim="diyStore.global.topStatusBar.inputPlaceholder" :placeholder="t('topStatusBarSearchNamePlaceholder')" clearable maxlength="12" show-word-limit />
                    </el-form-item>
                    <el-form-item :label="t('textAlign')" v-show="diyStore.global.topStatusBar.style == 'style-1'">
                        <el-radio-group v-model="diyStore.global.topStatusBar.textAlign">
                            <el-radio :label="'left'">{{ t('textAlignLeft') }}</el-radio>
                            <el-radio :label="'center'">{{ t('textAlignCenter') }}</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item :label="t('link')" v-if="['style-2','style-3'].indexOf(diyStore.global.topStatusBar.style) > -1">
                        <diy-link v-model="diyStore.global.topStatusBar.link" />
                    </el-form-item>
                </template>
            </el-form>
        </div>
        <div class="edit-attr-item-wrap" v-if="diyStore.global.bottomTabBar.control">
            <h3 class="mb-[10px]">{{ t('bottomNavContent') }}</h3>
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('tabbar')" class="display-block">
                    <el-switch v-model="diyStore.global.bottomTabBar.isShow" />
                    <div class="text-sm text-gray-400">{{ t('tabbarSwitchTips') }}</div>
                </el-form-item>
            </el-form>
        </div>
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('popWindowAds') }}</h3>
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('popAdsIsEnabled')" class="display-block">
                    <el-switch v-model="diyStore.global.popWindow.show" />
                </el-form-item>
                <div v-show="diyStore.global.popWindow.show">
                    <el-form-item :label="t('popAdsType')">
                        <el-radio-group v-model="diyStore.global.popWindow.count">
                            <el-radio label="once">{{ t('firstPop') }}</el-radio>
                            <el-radio label="always">{{ t('everyTimePops') }}</el-radio>
                        </el-radio-group>
                        <div class="text-sm text-gray-400">{{ t('popWindowCountTips') }}</div>
                    </el-form-item>
                    <el-form-item :label="t('popAdsImage')">
                        <upload-image v-model="diyStore.global.popWindow.imgUrl" :limit="1" @change="selectImg" />
                    </el-form-item>
                    <el-form-item :label="t('popAdsLink')">
                        <diy-link v-model="diyStore.global.popWindow.link" />
                    </el-form-item>
                </div>
            </el-form>
        </div>

        <el-dialog v-model="showDialog" :title="t('selectStyle')" width="800px">

            <div class="flex flex-wrap">
                <div class="flex items-center justify-center overflow-hidden w-[32%] h-[100px] mr-[2%] mb-[15px] cursor-pointer border bg-gray-50"
                    :class="{ 'border-primary': selectStyle == 'style-1' }" @click="selectStyle = 'style-1'">
                    <img class="max-w-[100%] max-h-[100%]" src="@/app/assets/images/diy/head/nav_style1.jpg" />
                </div>
                <div class="flex items-center justify-center overflow-hidden w-[32%] h-[100px] mr-[2%] mb-[15px] cursor-pointer border bg-gray-50"
                    :class="{ 'border-primary': selectStyle == 'style-2' }" @click="selectStyle = 'style-2'">
                    <img class="max-w-[100%] max-h-[100%]" src="@/app/assets/images/diy/head/nav_style2.jpg" />
                </div>
                <div class="flex items-center justify-center overflow-hidden w-[32%] h-[100px] mb-[15px] cursor-pointer border bg-gray-50"
                    :class="{ 'border-primary': selectStyle == 'style-3' }" @click="selectStyle = 'style-3'">
                    <img class="max-w-[100%] max-h-[100%]" src="@/app/assets/images/diy/head/nav_style3.jpg" />
                </div>
                <div class="flex items-center justify-center overflow-hidden w-[32%] h-[100px]  mr-[2%] cursor-pointer border bg-gray-50"
                    :class="{ 'border-primary': selectStyle == 'style-4' }" @click="selectStyle = 'style-4'">
                    <img class="max-w-[100%] max-h-[100%]" src="@/app/assets/images/diy/head/nav_style4.jpg" />
                </div>
            </div>

            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="showDialog = false">{{ t('cancel') }}</el-button>
                    <el-button type="primary" @click="changeStyle">{{ t('confirm') }}</el-button>
                </span>
            </template>

        </el-dialog>
    </div>

    <!-- 样式 -->
    <div class="style-wrap" v-show="diyStore.editTab == 'style'">
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('pageStyle') }}</h3>
            <el-form label-width="115px" class="px-[10px]">
                <el-form-item :label="t('pageBgColor')">
                    <el-color-picker v-model="diyStore.editComponent.pageStartBgColor" show-alpha :predefine="diyStore.predefineColors" />
                    <icon name="iconfont iconmap-connect" size="20px" class="block !text-gray-400 mx-[5px]" />
                    <el-color-picker v-model="diyStore.editComponent.pageEndBgColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('bgGradientAngle')">
                    <el-radio-group v-model="diyStore.editComponent.pageGradientAngle">
                        <el-radio label="to bottom">{{ t('topToBottom') }}</el-radio>
                        <el-radio label="to right">{{ t('leftToRight') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item :label="t('bgHeightScale')">
                    <el-slider v-model="diyStore.global.bgHeightScale" show-input size="small" class="ml-[10px] diy-nav-slider" />
                </el-form-item>
                <div class="text-sm text-gray-400 ml-[80px] mb-[10px]">{{ t('bgHeightScaleTip') }}</div>
                <el-form-item :label="t('bgUrl')">
                    <upload-image v-model="diyStore.global.bgUrl" :limit="1" />
                </el-form-item>
            </el-form>
        </div>
        <div class="edit-attr-item-wrap" v-if="diyStore.global.topStatusBar.control">
            <h3 class="mb-[10px]">{{ t('statusBarStyle') }}</h3>
            <el-form label-width="115px" class="px-[10px]">
                <el-form-item :label="t('topStatusBarBgColor')" class="display-block">
                    <el-color-picker v-model="diyStore.global.topStatusBar.bgColor" show-alpha />
                </el-form-item>
                <el-form-item :label="t('rollTopStatusBarBgColor')" class="display-block">
                    <el-color-picker v-model="diyStore.global.topStatusBar.rollBgColor" show-alpha />
                </el-form-item>
                <el-form-item :label="t('topStatusBarTextColor')" class="display-block">
                    <el-color-picker v-model="diyStore.global.topStatusBar.textColor" show-alpha />
                </el-form-item>
                <el-form-item :label="t('rollTopStatusBarTextColor')" class="display-block">
                    <el-color-picker v-model="diyStore.global.topStatusBar.rollTextColor" show-alpha />
                </el-form-item>
            </el-form>
        </div>
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('marginSet') }}</h3>
            <el-form label-width="115px" class="px-[10px]">
                <el-form-item :label="t('marginBoth')">
                    <el-slider v-model="diyStore.global.template.margin.both" show-input size="small" @input="inputBoth" class="ml-[10px] diy-nav-slider" />
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { t } from '@/lang'
import { watch, ref } from 'vue'
import useDiyStore from '@/stores/modules/diy'
import { img } from '@/utils/common'

const diyStore = useDiyStore()

watch(
    () => diyStore.global.bgUrl,
    (newValue, oldValue) => {
        // 设置图片宽高
        const image = new Image()
        image.src = img(diyStore.global.bgUrl)
        image.onload = async () => {
            diyStore.global.imgWidth = image.width
            diyStore.global.imgHeight = image.height
        }
        if (!diyStore.global.bgUrl) {
            diyStore.global.imgWidth = ''
            diyStore.global.imgHeight = ''
        }
    }
)

// 改变页面的左右边距时，更新所有组件的数值
const inputBoth = (value: any) => {
    diyStore.value.forEach((item, index) => {
        item.margin.both = value
    })
}

watch(
    () => diyStore.global,
    (newValue, oldValue) => {
        selectStyle.value = newValue.topStatusBar.style
    }, { deep: true }
)

const showDialog = ref(false)
const showStyle = () => {
    showDialog.value = true
}

const selectStyle = ref('style-1')
const changeStyle = () => {
    switch (selectStyle.value) {
        case 'style-1':
            diyStore.global.topStatusBar.styleName = '风格1'
            break
        case 'style-2':
            diyStore.global.topStatusBar.styleName = '风格2'
            break
        case 'style-3':
            diyStore.global.topStatusBar.styleName = '风格3'
            break
        case 'style-4':
            diyStore.global.topStatusBar.styleName = '风格4'
            break
    }
    diyStore.global.topStatusBar.style = selectStyle.value
    showDialog.value = false
}

const selectImg = (url: any) => {
    const image = new Image()
    image.src = img(url)
    image.onload = async () => {
        diyStore.global.popWindow.imgWidth = image.width
        diyStore.global.popWindow.imgHeight = image.height
    }
}

defineExpose({})
</script>

<style lang="scss" scoped></style>

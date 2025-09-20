<template>
    <!-- 内容 -->
    <div class="content-wrap" v-show="diyStore.editTab == 'content'">

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('titleContent') }}</h3>
            <el-form label-width="80px" class="px-[10px]" @submit.prevent>
                <el-form-item :label="t('selectStyle')" class="flex">
                    <span class="text-primary flex-1 cursor-pointer" @click="showTitleStyle">{{ diyStore.editComponent.titleStyle.title }}</span>
                    <el-icon @click="showTitleStyle" class="cursor-pointer">
                        <ArrowRight />
                    </el-icon>
                </el-form-item>
                <el-form-item :label="t('title')" v-if="diyStore.editComponent && diyStore.editComponent.titleStyle && diyStore.editComponent.titleStyle.value != 'style-5'">
                    <el-input v-model.trim="diyStore.editComponent.text" :placeholder="t('titlePlaceholder')" clearable maxlength="10" show-word-limit />
                </el-form-item>
                <el-form-item :label="t('image')" v-else>
                    <upload-image v-model="diyStore.editComponent.textImg" :limit="1" />
                </el-form-item>
                <el-form-item :label="t('link')">
                    <diy-link v-model="diyStore.editComponent.textLink" />
                </el-form-item>
                <el-form-item :label="t('subTitle')">
                    <el-input v-model.trim="diyStore.editComponent.subTitle.text" :placeholder="t('subTitlePlaceholder')" clearable maxlength="8" show-word-limit />
                </el-form-item>
                <el-form-item :label="t('link')">
                    <diy-link v-model="diyStore.editComponent.subTitle.link" />
                </el-form-item>
            </el-form>

            <el-dialog v-model="showTitleDialog" :title="t('selectStyle')" width="460px">

                <div class="flex flex-wrap">
                    <template v-for="(item,index) in titleStyleList" :key="index">
                        <div :class="{ 'border-primary': selectTitleStyle.value == item.value }"
                             @click="changeTitleStyle(item)"
                             class="flex items-center justify-center overflow-hidden w-[200px] h-[100px] mr-[12px] mb-[12px] cursor-pointer border bg-[#eee]">
                            <img :src="img(item.url)" />
                        </div>
                    </template>
                </div>

                <template #footer>
                    <span class="dialog-footer">
                        <el-button @click="showTitleDialog = false">{{ t('cancel') }}</el-button>
                        <el-button type="primary" @click="confirmTitleStyle">{{ t('confirm') }}</el-button>
                    </span>
                </template>

            </el-dialog>
        </div>

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('activeCubeBlockContent') }}</h3>
            <el-form label-width="90px" class="px-[10px]">

                <el-form-item :label="t('selectStyle')" class="flex">
                    <span class="text-primary flex-1 cursor-pointer"
                          @click="showBlockStyle">{{ diyStore.editComponent.blockStyle.title }}</span>
                    <el-icon @click="showBlockStyle" class="cursor-pointer">
                        <ArrowRight />
                    </el-icon>
                </el-form-item>

                <el-dialog v-model="showListDialog" :title="t('selectStyle')" width="600px">
                    <div class="flex flex-wrap">
                        <template v-for="(item,index) in blockStyleList" :key="index">
                            <div :class="{ 'border-primary': selectBlockStyle.value == item.value }"
                                 @click="changeBlockStyle(item)"
                                 class="flex items-center justify-center overflow-hidden w-[250px] h-[150px] mr-[12px] mb-[12px] cursor-pointer border bg-[#eee]">
                                <img :src="img(item.url)" />
                            </div>
                        </template>

                    </div>

                    <template #footer>
                        <span class="dialog-footer">
                            <el-button @click="showListDialog = false">{{ t('cancel') }}</el-button>
                            <el-button type="primary" @click="confirmBlockStyle">{{ t('confirm') }}</el-button>
                        </span>
                    </template>

                </el-dialog>

                <p class="text-sm text-gray-400 mb-[10px]">{{ t('dragMouseAdjustOrder') }}</p>

                <div ref="blockBoxRef">
                    <div v-for="(item,index) in diyStore.editComponent.list" :key="item.id"
                         class="item-wrap p-[10px] pb-0 relative border border-dashed border-gray-300 mb-[16px]">
                        <el-form-item :label="t('image')">
                            <upload-image v-model="item.imageUrl" :limit="1" />
                        </el-form-item>

                        <el-form-item :label="t('activeCubeTitle')">
                            <el-input v-model.trim="item.title.text" :placeholder="t('activeCubeTitlePlaceholder')"
                                      clearable maxlength="4" show-word-limit />
                        </el-form-item>

                        <el-form-item :label="t('activeCubeSubTitleTextColor')" v-show="diyStore.editComponent.blockStyle.value == 'style-3'">
                            <el-color-picker v-model="item.title.textColor" show-alpha :predefine="diyStore.predefineColors" />
                        </el-form-item>

                        <el-form-item :label="t('activeCubeSubTitle')" v-if="diyStore.editComponent.blockStyle.value != 'style-3'">
                            <el-input v-model.trim="item.subTitle.text" :placeholder="t('activeCubeSubTitlePlaceholder')" clearable maxlength="6" show-word-limit />
                        </el-form-item>

                        <div v-show="diyStore.editComponent.blockStyle.value == 'style-4'">
                            <el-form-item :label="t('activeCubeSubTitleTextColor')">
                                <el-color-picker v-model="item.subTitle.textColor" show-alpha :predefine="diyStore.predefineColors" />
                            </el-form-item>
                            <el-form-item :label="t('activeCubeSubTitleBgColor')">
                                <el-color-picker v-model="item.subTitle.startColor" show-alpha :predefine="diyStore.predefineColors" />
                                <icon name="iconfont iconmap-connect" size="20px" class="block !text-gray-400 mx-[5px]" />
                                <el-color-picker v-model="item.subTitle.endColor" show-alpha :predefine="diyStore.predefineColors" />
                            </el-form-item>
                        </div>

                        <el-form-item :label="t('activeListFrameColor')">
                            <el-color-picker v-model="item.listFrame.startColor" show-alpha :predefine="diyStore.predefineColors" />
                            <icon name="iconfont iconmap-connect" size="20px" class="block !text-gray-400 mx-[5px]" />
                            <el-color-picker v-model="item.listFrame.endColor" show-alpha :predefine="diyStore.predefineColors" />
                        </el-form-item>

                        <div
                            v-show="diyStore.editComponent.blockStyle.value != 'style-4' && diyStore.editComponent.blockStyle.value != 'style-3'">
                            <el-form-item :label="t('activeCubeButton')">
                                <el-input v-model.trim="item.moreTitle.text" :placeholder="t('activeCubeButtonPlaceholder')" clearable maxlength="3" show-word-limit />
                            </el-form-item>

                            <el-form-item :label="t('activeCubeButtonColor')">
                                <el-color-picker v-model="item.moreTitle.startColor" show-alpha :predefine="diyStore.predefineColors" />
                                <icon name="iconfont iconmap-connect" size="20px" class="block !text-gray-400 mx-[5px]" />
                                <el-color-picker v-model="item.moreTitle.endColor" show-alpha :predefine="diyStore.predefineColors" />
                            </el-form-item>
                        </div>

                        <el-form-item :label="t('link')">
                            <diy-link v-model="item.link" />
                        </el-form-item>

                        <div class="del absolute cursor-pointer z-[2] top-[-8px] right-[-8px]"
                             v-show="diyStore.editComponent.list.length > 1"
                             @click="diyStore.editComponent.list.splice(index,1)">
                            <icon name="element CircleCloseFilled" color="#bbb" size="20px" />
                        </div>
                    </div>
                </div>

                <el-button v-show="diyStore.editComponent.list.length < 10" class="w-full" @click="addItem">
                    {{ t('activeCubeAddItem') }}
                </el-button>

            </el-form>
        </div>

    </div>

    <!-- 样式 -->
    <div class="style-wrap" v-show="diyStore.editTab == 'style'">

        <div class="edit-attr-item-wrap" v-if="selectTitleStyle.value != 'style-5'">
            <h3 class="mb-[10px]">{{ t('titleStyle') }}</h3>
            <el-form label-width="90px" class="px-[10px]">
                <el-form-item :label="t('textColor')">
                    <el-color-picker v-model="diyStore.editComponent.titleColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
            </el-form>
        </div>

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('subTitleStyle') }}</h3>
            <el-form label-width="90px" class="px-[10px]">
                <el-form-item :label="t('textColor')">
                    <el-color-picker v-model="diyStore.editComponent.subTitle.textColor" show-alpha
                                     :predefine="diyStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('subTextBgColor')">
                    <el-color-picker v-model="diyStore.editComponent.subTitle.startColor" show-alpha :predefine="diyStore.predefineColors" />
                    <icon name="iconfont iconmap-connect" size="20px" class="block !text-gray-400 mx-[5px]" />
                    <el-color-picker v-model="diyStore.editComponent.subTitle.endColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
            </el-form>
        </div>

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('activeCubeBlockStyle') }}</h3>
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('activeCubeBlockTextFontWeight')">
                    <el-radio-group v-model="diyStore.editComponent.blockStyle.fontWeight">
                        <el-radio :label="'normal'">{{ t('fontWeightNormal') }}</el-radio>
                        <el-radio :label="'bold'">{{ t('fontWeightBold') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item :label="t('activeCubeBlockBtnText')" class="flex">
                    <el-radio-group v-model="diyStore.editComponent.blockStyle.btnText">
                        <el-radio :label="'normal'">{{ t('btnTextNormal') }}</el-radio>
                        <el-radio :label="'italics'">{{ t('btnTextItalics') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item :label="t('topRounded')">
                    <el-slider v-model="diyStore.editComponent.topElementRounded" show-input size="small" class="ml-[10px] diy-nav-slider" :max="50" />
                </el-form-item>
                <el-form-item :label="t('bottomRounded')">
                    <el-slider v-model="diyStore.editComponent.bottomElementRounded" show-input size="small" class="ml-[10px] diy-nav-slider" :max="50" />
                </el-form-item>
            </el-form>
        </div>

        <!-- 组件样式 -->
        <slot name="style"></slot>
    </div>

</template>

<script lang="ts" setup>
import { t } from '@/lang'
import useDiyStore from '@/stores/modules/diy'
import { img } from '@/utils/common'
import { ref, reactive, onMounted, nextTick } from 'vue'
import Sortable from 'sortablejs'
import { range } from 'lodash-es'

const diyStore = useDiyStore()
diyStore.editComponent.ignore = [] // 忽略公共属性

// 组件验证
diyStore.editComponent.verify = (index: number) => {
    const res = { code: true, message: '' }

    if (diyStore.value[index].text == '') {
        res.code = false
        res.message = t('activeCubeTitlePlaceholder')
        return res
    }

    diyStore.value[index].list.forEach((item: any) => {
        if (item.imageUrl === '') {
            res.code = false
            res.message = t('imageUrlTip')
            return res
        }
        if (item.title.text === '') {
            res.code = false
            res.message = t('activeCubeTitlePlaceholder')
            return res
        }
        if (['style-1', 'style-2', 'style-4'].indexOf(diyStore.value[index].blockStyle.value) != -1) {
            if (item.subTitle.text === '') {
                res.code = false
                res.message = t('activeCubeSubTitlePlaceholder')
                return res
            }
        }
        if (['style-1', 'style-2'].indexOf(diyStore.value[index].blockStyle.value) != -1) {
            if (item.moreTitle.text === '') {
                res.code = false
                res.message = t('activeCubeButtonPlaceholder')
                return res
            }
        }
    })
    return res
}

diyStore.editComponent.list.forEach((item: any) => {
    if (!item.id) item.id = diyStore.generateRandom()
})

// 标题风格样式
const showTitleDialog = ref(false)

const showTitleStyle = () => {
    selectTitleStyle.title = diyStore.editComponent.titleStyle.title
    selectTitleStyle.value = diyStore.editComponent.titleStyle.value
    showTitleDialog.value = true
}

const titleStyleList = reactive([
    {
        url: 'static/resource/images/diy/active_cube/title_style1.png',
        title: '风格1',
        value: 'style-1'
    }, {
        url: 'static/resource/images/diy/active_cube/title_style2.png',
        title: '风格2',
        value: 'style-2'
    }, {
        url: 'static/resource/images/diy/active_cube/title_style3.png',
        title: '风格3',
        value: 'style-3'
    }, {
        url: 'static/resource/images/diy/active_cube/title_style5.png',
        title: '风格4',
        value: 'style-4'
    }, {
        url: 'static/resource/images/diy/active_cube/title_style6.png',
        title: '风格5',
        value: 'style-5'
    }
])

const selectTitleStyle = reactive({
    title: diyStore.editComponent.titleStyle.title,
    value: diyStore.editComponent.titleStyle.value
})

const changeTitleStyle = (item: any) => {
    selectTitleStyle.title = item.title
    selectTitleStyle.value = item.value
}

const confirmTitleStyle = () => {
    diyStore.editComponent.titleStyle.title = selectTitleStyle.title
    diyStore.editComponent.titleStyle.value = selectTitleStyle.value
    initTitleStyle(diyStore.editComponent.titleStyle.value)
    showTitleDialog.value = false
}

const initTitleStyle = (style) => {
    if (diyStore.editComponent.titleStyle.value == 'style-1') {
        diyStore.editComponent.titleColor = '#F91700'
        diyStore.editComponent.subTitle.textColor = '#FFFFFF'
        diyStore.editComponent.subTitle.startColor = '#FB792F'
        diyStore.editComponent.subTitle.endColor = '#F91700'
    } else if (diyStore.editComponent.titleStyle.value == 'style-2') {
        diyStore.editComponent.titleColor = '#F91700'
        diyStore.editComponent.subTitle.textColor = '#FFFFFF'
        diyStore.editComponent.subTitle.startColor = '#FB792F'
        diyStore.editComponent.subTitle.endColor = '#F91700'
    } else if (diyStore.editComponent.titleStyle.value == 'style-3') {
        diyStore.editComponent.titleColor = '#F91700'
        diyStore.editComponent.subTitle.textColor = '#FFFFFF'
        diyStore.editComponent.subTitle.startColor = '#FB792F'
        diyStore.editComponent.subTitle.endColor = '#F91700'
    } else if (diyStore.editComponent.titleStyle.value == 'style-4') {
        diyStore.editComponent.titleColor = '#FFFFFF'
        diyStore.editComponent.subTitle.textColor = '#333333'
        diyStore.editComponent.subTitle.startColor = '#FFFFFF'
        diyStore.editComponent.subTitle.endColor = '#FFFFFF'
    } else if (diyStore.editComponent.titleStyle.value == 'style-5') {
        diyStore.editComponent.titleColor = ''
        diyStore.editComponent.subTitle.textColor = '#999999'
        diyStore.editComponent.subTitle.startColor = '#FFFFFF'
        diyStore.editComponent.subTitle.endColor = '#FFFFFF'
    }
}

// 板块风格样式
const showListDialog = ref(false)

const showBlockStyle = () => {
    showListDialog.value = true
}

const blockStyleList = reactive([
    {
        url: 'static/resource/images/diy/active_cube/block_style1.png',
        title: '风格1',
        value: 'style-1'
    },
    {
        url: 'static/resource/images/diy/active_cube/block_style2.png',
        title: '风格2',
        value: 'style-2'
    },
    {
        url: 'static/resource/images/diy/active_cube/block_style3.png',
        title: '风格3',
        value: 'style-3'
    },
    {
        url: 'static/resource/images/diy/active_cube/block_style4.png',
        title: '风格4',
        value: 'style-4'
    }
])

const selectBlockStyle = reactive({
    title: diyStore.editComponent.blockStyle.title,
    value: diyStore.editComponent.blockStyle.value
})

const changeBlockStyle = (item: any) => {
    selectBlockStyle.title = item.title
    selectBlockStyle.value = item.value
}

const confirmBlockStyle = () => {
    diyStore.editComponent.blockStyle.title = selectBlockStyle.title
    diyStore.editComponent.blockStyle.value = selectBlockStyle.value
    initBlockStyle(diyStore.editComponent.blockStyle.value)
    showListDialog.value = false
}

const initBlockStyle = (style: any) => {
    if (style == 'style-1') {
        diyStore.editComponent.blockStyle.fontWeight = 'normal'
        diyStore.editComponent.blockStyle.btnText = 'normal'

        diyStore.editComponent.list[0].title.textColor = '#303133'
        diyStore.editComponent.list[0].subTitle.textColor = '#999999'
        diyStore.editComponent.list[0].subTitle.startColor = ''
        diyStore.editComponent.list[0].subTitle.endColor = ''
        diyStore.editComponent.list[0].moreTitle.startColor = '#FEA715'
        diyStore.editComponent.list[0].moreTitle.endColor = '#FE1E00'
        diyStore.editComponent.list[0].listFrame.startColor = '#FFFAF5'
        diyStore.editComponent.list[0].listFrame.endColor = '#FFFFFF'

        diyStore.editComponent.list[1].title.textColor = '#303133'
        diyStore.editComponent.list[1].subTitle.textColor = '#999999'
        diyStore.editComponent.list[1].subTitle.startColor = ''
        diyStore.editComponent.list[1].subTitle.endColor = ''
        diyStore.editComponent.list[1].moreTitle.startColor = '#FFBF50'
        diyStore.editComponent.list[1].moreTitle.endColor = '#FF9E03'
        diyStore.editComponent.list[1].listFrame.startColor = '#FFFAF5'
        diyStore.editComponent.list[1].listFrame.endColor = '#FFFFFF'

        diyStore.editComponent.list[2].title.textColor = '#303133'
        diyStore.editComponent.list[2].subTitle.textColor = '#999999'
        diyStore.editComponent.list[2].subTitle.startColor = ''
        diyStore.editComponent.list[2].subTitle.endColor = ''
        diyStore.editComponent.list[2].moreTitle.startColor = '#A2E792'
        diyStore.editComponent.list[2].moreTitle.endColor = '#49CD2D'
        diyStore.editComponent.list[2].listFrame.startColor = '#FFFAF5'
        diyStore.editComponent.list[2].listFrame.endColor = '#FFFFFF'

        diyStore.editComponent.list[3].title.textColor = '#303133'
        diyStore.editComponent.list[3].subTitle.textColor = '#999999'
        diyStore.editComponent.list[3].subTitle.startColor = ''
        diyStore.editComponent.list[3].subTitle.endColor = ''
        diyStore.editComponent.list[3].moreTitle.startColor = '#4AC1FF'
        diyStore.editComponent.list[3].moreTitle.endColor = '#1D7CFF'
        diyStore.editComponent.list[3].listFrame.startColor = '#FFFAF5'
        diyStore.editComponent.list[3].listFrame.endColor = '#FFFFFF'
    } else if (style == 'style-2') {
        diyStore.editComponent.blockStyle.fontWeight = 'normal'
        diyStore.editComponent.blockStyle.btnText = 'normal'

        diyStore.editComponent.blockStyle.fontWeight = 'bold'
        diyStore.editComponent.blockStyle.btnText = 'italics'

        diyStore.editComponent.list[0].title.textColor = '#303133'
        diyStore.editComponent.list[0].subTitle.textColor = '#999999'
        diyStore.editComponent.list[0].subTitle.startColor = ''
        diyStore.editComponent.list[0].subTitle.endColor = ''
        diyStore.editComponent.list[0].moreTitle.startColor = '#FFC051'
        diyStore.editComponent.list[0].moreTitle.endColor = '#FF9C00'
        diyStore.editComponent.list[0].listFrame.startColor = '#FFF1DB'
        diyStore.editComponent.list[0].listFrame.endColor = '#FFFBF4'

        diyStore.editComponent.list[1].title.textColor = '#303133'
        diyStore.editComponent.list[1].subTitle.textColor = '#999999'
        diyStore.editComponent.list[1].subTitle.startColor = ''
        diyStore.editComponent.list[1].subTitle.endColor = ''
        diyStore.editComponent.list[1].moreTitle.startColor = '#A4E894'
        diyStore.editComponent.list[1].moreTitle.endColor = '#45CC2A'
        diyStore.editComponent.list[1].listFrame.startColor = '#E6F6E2'
        diyStore.editComponent.list[1].listFrame.endColor = '#F5FDF3'

        diyStore.editComponent.list[2].title.textColor = '#303133'
        diyStore.editComponent.list[2].subTitle.textColor = '#999999'
        diyStore.editComponent.list[2].subTitle.startColor = ''
        diyStore.editComponent.list[2].subTitle.endColor = ''
        diyStore.editComponent.list[2].moreTitle.startColor = '#4BC2FF'
        diyStore.editComponent.list[2].moreTitle.endColor = '#1F7DFF'
        diyStore.editComponent.list[2].listFrame.startColor = '#E2F6FF'
        diyStore.editComponent.list[2].listFrame.endColor = '#F2FAFF'

        diyStore.editComponent.list[3].title.textColor = '#303133'
        diyStore.editComponent.list[3].subTitle.textColor = '#999999'
        diyStore.editComponent.list[3].subTitle.startColor = ''
        diyStore.editComponent.list[3].subTitle.endColor = ''
        diyStore.editComponent.list[3].moreTitle.startColor = '#FB792F'
        diyStore.editComponent.list[3].moreTitle.endColor = '#F91700'
        diyStore.editComponent.list[3].listFrame.startColor = '#FFEAEA'
        diyStore.editComponent.list[3].listFrame.endColor = '#FFFCFB'
    } else if (style == 'style-3') {
        diyStore.editComponent.blockStyle.fontWeight = 'normal'
        diyStore.editComponent.blockStyle.btnText = 'normal'

        diyStore.editComponent.list[0].title.textColor = '#FF1128'
        diyStore.editComponent.list[0].subTitle.textColor = ''
        diyStore.editComponent.list[0].subTitle.startColor = ''
        diyStore.editComponent.list[0].subTitle.endColor = ''
        diyStore.editComponent.list[0].moreTitle.startColor = ''
        diyStore.editComponent.list[0].moreTitle.endColor = ''
        diyStore.editComponent.list[0].listFrame.startColor = ''
        diyStore.editComponent.list[0].listFrame.endColor = ''

        diyStore.editComponent.list[1].title.textColor = '#303133'
        diyStore.editComponent.list[1].subTitle.textColor = ''
        diyStore.editComponent.list[1].subTitle.startColor = ''
        diyStore.editComponent.list[1].subTitle.endColor = ''
        diyStore.editComponent.list[1].moreTitle.startColor = ''
        diyStore.editComponent.list[1].moreTitle.endColor = ''
        diyStore.editComponent.list[1].listFrame.startColor = ''
        diyStore.editComponent.list[1].listFrame.endColor = ''

        diyStore.editComponent.list[2].title.textColor = '#303133'
        diyStore.editComponent.list[2].subTitle.textColor = ''
        diyStore.editComponent.list[2].subTitle.startColor = ''
        diyStore.editComponent.list[2].subTitle.endColor = ''
        diyStore.editComponent.list[2].moreTitle.startColor = ''
        diyStore.editComponent.list[2].moreTitle.endColor = ''
        diyStore.editComponent.list[2].listFrame.startColor = ''
        diyStore.editComponent.list[2].listFrame.endColor = ''

        diyStore.editComponent.list[3].title.textColor = '#303133'
        diyStore.editComponent.list[3].subTitle.textColor = ''
        diyStore.editComponent.list[3].subTitle.startColor = ''
        diyStore.editComponent.list[3].subTitle.endColor = ''
        diyStore.editComponent.list[3].moreTitle.startColor = ''
        diyStore.editComponent.list[3].moreTitle.endColor = ''
        diyStore.editComponent.list[3].listFrame.startColor = ''
        diyStore.editComponent.list[3].listFrame.endColor = ''
    } else if (style == 'style-4') {
        diyStore.editComponent.blockStyle.fontWeight = 'bold'
        diyStore.editComponent.blockStyle.btnText = 'normal'

        diyStore.editComponent.list[0].title.textColor = '#303133'
        diyStore.editComponent.list[0].subTitle.textColor = '#ED6E00'
        diyStore.editComponent.list[0].subTitle.startColor = '#FFE4D9'
        diyStore.editComponent.list[0].subTitle.endColor = '#FFE4D9'
        diyStore.editComponent.list[0].moreTitle.startColor = ''
        diyStore.editComponent.list[0].moreTitle.endColor = ''
        diyStore.editComponent.list[0].listFrame.startColor = '#FFAD4D'
        diyStore.editComponent.list[0].listFrame.endColor = '#F93D02'

        diyStore.editComponent.list[1].title.textColor = '#303133'
        diyStore.editComponent.list[1].subTitle.textColor = '#2E59E9'
        diyStore.editComponent.list[1].subTitle.startColor = '#CAD7F8'
        diyStore.editComponent.list[1].subTitle.endColor = '#CAD7F8'
        diyStore.editComponent.list[1].moreTitle.startColor = ''
        diyStore.editComponent.list[1].moreTitle.endColor = ''
        diyStore.editComponent.list[1].listFrame.startColor = '#7CA7F4'
        diyStore.editComponent.list[1].listFrame.endColor = '#2B56E9'

        diyStore.editComponent.list[2].title.textColor = '#303133'
        diyStore.editComponent.list[2].subTitle.textColor = '#F62F55'
        diyStore.editComponent.list[2].subTitle.startColor = '#FCD6D9'
        diyStore.editComponent.list[2].subTitle.endColor = '#FCD6D9'
        diyStore.editComponent.list[2].moreTitle.startColor = ''
        diyStore.editComponent.list[2].moreTitle.endColor = ''
        diyStore.editComponent.list[2].listFrame.startColor = '#FF7F48'
        diyStore.editComponent.list[2].listFrame.endColor = '#EE335B'

        diyStore.editComponent.list[3].title.textColor = '#303133'
        diyStore.editComponent.list[3].subTitle.textColor = '#139B3C'
        diyStore.editComponent.list[3].subTitle.startColor = '#D3F1DA'
        diyStore.editComponent.list[3].subTitle.endColor = '#D3F1DA'
        diyStore.editComponent.list[3].moreTitle.startColor = ''
        diyStore.editComponent.list[3].moreTitle.endColor = ''
        diyStore.editComponent.list[3].listFrame.startColor = '#90D48C'
        diyStore.editComponent.list[3].listFrame.endColor = '#299F4F'
    }
}

const addItem = () => {
    diyStore.editComponent.list.push({
        id: diyStore.generateRandom(),
        title: {
            title: '标题',
            textColor: '#000000'
        },
        subTitle: {
            text: '副标题',
            textColor: '#999999',
            startColor: '',
            endColor: ''
        },
        listFrame: {
            startColor: '#4AC1FF',
            endColor: '#1D7CFF'
        },
        moreTitle: {
            text: '去看看',
            startColor: '#FEA715',
            endColor: '#FE1E00'
        },
        imageUrl: '',
        link: { name: '' }
    })
}

const blockBoxRef = ref()

onMounted(() => {
    nextTick(() => {
        const sortable = Sortable.create(blockBoxRef.value, {
            group: 'item-wrap',
            animation: 200,
            onEnd: event => {
                const temp = diyStore.editComponent.list[event.oldIndex!]
                diyStore.editComponent.list.splice(event.oldIndex!, 1)
                diyStore.editComponent.list.splice(event.newIndex!, 0, temp)
                sortable.sort(
                    range(diyStore.editComponent.list.length).map(value => {
                        return value.toString()
                    })
                )
            }
        })
    })
})

defineExpose({})

</script>

<style lang="scss" scoped>
</style>

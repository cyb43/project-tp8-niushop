<template>
    <!-- 内容 -->
    <div class="content-wrap notice-content-wrap" v-show="diyStore.editTab == 'content'">
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('noticeStyle') }}</h3>
            <el-form label-width="90px" class="px-[10px]">
                <el-form-item :label="t('noticeType')">
                    <el-radio-group v-model="diyStore.editComponent.noticeType">
                        <el-radio label="img">{{ t('noticeTypeImg') }}</el-radio>
                        <el-radio label="text">{{ t('noticeTypeText') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <div class="flex items-center flex-wrap py-[8px] px-[10px] bg-[#f4f3f7] rounded mb-[18px] mx-[18px]" v-show="diyStore.editComponent.noticeType == 'img'">
                    <div :class="['mr-[10px] rounded cursor-pointer border-[1px] border-solid', {'border-[var(--el-color-primary)]': diyStore.editComponent.systemUrl == 'style_1' && diyStore.editComponent.imgType == 'system'}]">
                        <img src="@/app/assets/images/diy/notice/style_1.png" :class="['h-[28px] px-[10px] py-[5px]']" @click="changeStyle('style_1')" />
                    </div>

                    <div :class="['mr-[10px] rounded cursor-pointer w-[100px] border-[1px] border-solid', {'border-[var(--el-color-primary)]': diyStore.editComponent.systemUrl == 'style_2' && diyStore.editComponent.imgType == 'system'}]">
                        <img src="@/app/assets/images/diy/notice/style_2.png" class="px-[10px] py-[5px]" @click="changeStyle('style_2')" />
                    </div>
                    <div @click.stop="diyStore.editComponent.imgType = 'diy'"
                         :class="['mr-[10px] rounded cursor-pointer diy-upload-img border-[1px] border-solid', {'border-[var(--el-color-primary)]': (diyStore.editComponent.imageUrl && diyStore.editComponent.imgType == 'diy') }]">
                        <upload-image v-model="diyStore.editComponent.imageUrl" :limit="1" />
                    </div>
                </div>

                <el-form-item :label="t('noticeTitle')" v-show="diyStore.editComponent.noticeType == 'text'">
                    <el-input v-model.trim="diyStore.editComponent.noticeTitle" :placeholder="t('titlePlaceholder')" clearable maxlength="20" show-word-limit />
                </el-form-item>

            </el-form>

        </div>

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('noticeText') }}</h3>
            <el-form label-width="90px" class="px-[10px]">

                <el-form-item :label="t('noticeScrollWay')">
                    <el-radio-group v-model="diyStore.editComponent.scrollWay">
                        <el-radio label="upDown">{{ t('noticeUpDown') }}</el-radio>
                        <el-radio label="horizontal">{{ t('noticeHorizontal') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item :label="t('noticeShowType')">
                    <el-radio-group v-model="diyStore.editComponent.showType">
                        <el-radio label="popup">{{ t('noticeShowPopUp') }}</el-radio>
                        <el-radio label="link">{{ t('noticeShowLink') }}</el-radio>
                    </el-radio-group>
                </el-form-item>

                <p class="text-sm text-gray-400 mb-[10px]">{{ t('dragMouseAdjustOrder') }}</p>

                <div ref="noticeBoxRef">
                    <div v-for="(item,index) in diyStore.editComponent.list" :key="item.id" class="item-wrap p-[10px] pb-0 relative border border-dashed border-gray-300 mb-[16px]">

                        <el-form-item :label="t('noticeText')">
                            <el-input v-model.trim="item.text" :placeholder="t('noticePlaceholderText')" clearable maxlength="40" show-word-limit />
                        </el-form-item>

                        <div class="del absolute cursor-pointer z-[2] top-[-8px] right-[-8px]"
                             v-show="diyStore.editComponent.list.length > 1"
                             @click="diyStore.editComponent.list.splice(index,1)">
                            <icon name="element CircleCloseFilled" color="#bbb" size="20px" />
                        </div>

                        <el-form-item :label="t('link')" v-if="diyStore.editComponent.showType == 'link'">
                            <diy-link v-model="item.link" />
                        </el-form-item>
                    </div>
                </div>

                <el-button class="w-full" @click="addNotice">{{ t('addNotice') }}</el-button>

            </el-form>
        </div>

    </div>

    <!-- 样式 -->
    <div class="style-wrap" v-show="diyStore.editTab == 'style'">
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('textSet') }}</h3>
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('textFontSize')">
                    <el-slider v-model="diyStore.editComponent.fontSize" show-input size="small" class="ml-[10px] diy-nav-slider" :min="12" :max="20" />
                </el-form-item>
                <el-form-item :label="t('textFontWeight')">
                    <el-radio-group v-model="diyStore.editComponent.fontWeight">
                        <el-radio :label="'normal'">{{ t('fontWeightNormal') }}</el-radio>
                        <el-radio :label="'bold'">{{ t('fontWeightBold') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item :label="t('textColor')">
                    <el-color-picker v-model="diyStore.editComponent.textColor" />
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
import { ref, watch, onMounted, nextTick } from 'vue'
import { range } from 'lodash-es'
import Sortable from 'sortablejs'

const diyStore = useDiyStore()
diyStore.editComponent.ignore = [] // 忽略公共属性

// 组件验证
diyStore.editComponent.verify = (index: number) => {
    const res = { code: true, message: '' }

    if (diyStore.value[index].noticeType == 'text') {
        if (diyStore.value[index].noticeTitle == '') {
            res.code = false
            res.message = t('noticeTypeTextPlaceholder')
            return res
        }
    }

    diyStore.value[index].list.forEach((item: any) => {
        if (item.text == '') {
            res.code = false
            res.message = t('noticePlaceholderText')
            return res
        }
    })
    return res
}

diyStore.editComponent.list.forEach((item: any) => {
    if (!item.id) item.id = diyStore.generateRandom()
})

const changeStyle = (value: any) => {
    diyStore.editComponent.systemUrl = value
    diyStore.editComponent.imgType = 'system'
}

watch(
    () => diyStore.editComponent.imageUrl,
    (newValue, oldValue) => {
        if (newValue) {
            diyStore.editComponent.imgType = 'diy'
        } else {
            changeStyle('style_1')
        }
    }
)

const addNotice = () => {
    diyStore.editComponent.list.push({
        id: diyStore.generateRandom(),
        text: '公告',
        link: { name: '' }
    })
}

const noticeBoxRef = ref()

onMounted(() => {
    nextTick(() => {
        const sortable = Sortable.create(noticeBoxRef.value, {
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

<style lang="scss">
.notice-content-wrap {
    .add-notice-width {
        width: calc(100% - 20px);
    }

    .diy-upload-img {
        .image-wrap {
            width: 50px !important;
            height: 50px !important;
            margin-right: 0 !important;
            background: #fff;
        }

        .content-wrap {
            div {
                display: none;
            }
        }

    }
}
</style>
<style lang="scss" scoped></style>

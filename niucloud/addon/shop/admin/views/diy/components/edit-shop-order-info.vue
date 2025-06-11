<template>
    <!-- 内容 -->
    <div class="content-wrap" v-show="diyStore.editTab == 'content'">
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('titleContent') }}</h3>
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('title')">
                    <el-input v-model.trim="diyStore.editComponent.text" :placeholder="t('titlePlaceholder')" clearable maxlength="6" show-word-limit />
                </el-form-item>
            </el-form>
        </div>

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('subTitle') }}</h3>
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('more')">
                    <el-input v-model.trim="diyStore.editComponent.more.text" :placeholder="t('morePlaceholder')" clearable maxlength="8" show-word-limit />
                </el-form-item>
            </el-form>
        </div>

    </div>

    <!-- 样式 -->
    <div class="style-wrap" v-show="diyStore.editTab == 'style'">

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('titleStyle') }}</h3>
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('textFontSize')">
                    <el-slider v-model="diyStore.editComponent.fontSize" show-input size="small" class="ml-[10px] diy-nav-slider" :min="12" :max="30" />
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

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('subTitleStyle') }}</h3>
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('textColor')">
                    <el-color-picker v-model="diyStore.editComponent.more.color" />
                </el-form-item>
            </el-form>
        </div>

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('textSet') }}</h3>
            <el-form label-width="90px" class="px-[10px]">
                <el-form-item :label="t('textFontSize')">
                    <el-slider v-model="diyStore.editComponent.item.fontSize" show-input size="small" class="ml-[10px] diy-nav-slider" :min="12" :max="16" />
                </el-form-item>
                <el-form-item :label="t('textFontWeight')">
                    <el-radio-group v-model="diyStore.editComponent.item.fontWeight">
                        <el-radio :label="'normal'">{{ t('fontWeightNormal') }}</el-radio>
                        <el-radio :label="'bold'">{{ t('fontWeightBold') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item :label="t('textColor')">
                    <el-color-picker v-model="diyStore.editComponent.item.color" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>

            </el-form>
        </div>
        <!-- 组件样式 -->
        <slot name="style"></slot>

    </div>
</template>

<script lang="ts" setup>
import { t } from '@/lang'
import { ref } from 'vue'
import useDiyStore from '@/stores/modules/diy'

const diyStore = useDiyStore()
diyStore.editComponent.ignore = ['componentBgUrl'] // 忽略公共属性

defineExpose({})

</script>

<style lang="scss" scoped></style>

<template>
    <!-- 内容 -->
    <div class="content-wrap" v-show="diyStore.editTab == 'content'">
        <div class="edit-attr-item-wrap">
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('floatBtnButton')" class="display-block">
                    <el-radio-group v-model="diyStore.editComponent.btnPosition" @change="btnPositionChangeFn">
                        <el-radio label="follow_content">{{ t('followContent') }}</el-radio>
                        <el-radio label="hover_screen_bottom">{{ t('hoverScreenBottom') }}</el-radio>
                    </el-radio-group>
                    <div class="text-sm text-gray-400 mb-[5px] leading-[1.4]"
                         v-show="diyStore.editComponent.btnPosition == 'follow_content'">{{ t('btnTips') }}
                    </div>
                    <div class="text-sm text-gray-400 mb-[5px]"
                         v-show="diyStore.editComponent.btnPosition == 'hover_screen_bottom'">{{ t('btnTipsTwo') }}
                    </div>
                    <div class="text-sm text-gray-400 mb-[10px] leading-[1.4]">{{ t('btnTipsThree') }}</div>
                </el-form-item>
            </el-form>

        </div>

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('submitBtn') }}</h3>
            <el-form label-width="80px" class="px-[10px]" @submit.prevent>
                <el-form-item :label="t('submitBtnName')">
                    <el-input v-model.trim="diyStore.editComponent.submitBtn.text" :placeholder="t('btnNamePlaceholder')" clearable maxlength="10" show-word-limit />
                </el-form-item>
                <el-form-item :label="t('textColor')">
                    <el-color-picker v-model="diyStore.editComponent.submitBtn.color" />
                </el-form-item>
                <el-form-item :label="t('subTextBgColor')">
                    <el-color-picker v-model="diyStore.editComponent.submitBtn.bgColor" />
                </el-form-item>
            </el-form>
        </div>

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('resetBtn') }}</h3>
            <el-form label-width="80px" class="px-[10px]" @submit.prevent>
                <el-form-item :label="t('carouselSearchTabControl')">
                    <el-switch v-model="diyStore.editComponent.resetBtn.control" />
                </el-form-item>
                <el-form-item :label="t('submitBtnName')">
                    <el-input v-model.trim="diyStore.editComponent.resetBtn.text" :placeholder="t('btnNamePlaceholder')" clearable maxlength="10" show-word-limit />
                </el-form-item>
                <el-form-item :label="t('textColor')">
                    <el-color-picker v-model="diyStore.editComponent.resetBtn.color" />
                </el-form-item>
                <el-form-item :label="t('subTextBgColor')">
                    <el-color-picker v-model="diyStore.editComponent.resetBtn.bgColor" />
                </el-form-item>
            </el-form>
        </div>

    </div>

    <!-- 样式 -->
    <div class="style-wrap" v-show="diyStore.editTab == 'style'">

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('btnStyle') }}</h3>
            <el-form label-width="100px" class="px-[10px]">
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
import { ref } from 'vue'
import useDiyStore from '@/stores/modules/diy'

const diyStore = useDiyStore()
diyStore.editComponent.ignore = ['componentBgUrl'] // 忽略公共属性

// 单选
const btnPositionChangeFn = (e) => {
    if (e == 'hover_screen_bottom') {
        diyStore.editComponent.margin.bottom = 0
        diyStore.editComponent.margin.both = 0
        diyStore.editComponent.margin.top = 0
    }
}

// 组件验证
diyStore.editComponent.verify = (index: number) => {
    const res = { code: true, message: '' }
    if (diyStore.value[index].submitBtn.text == '') {
        res.code = false
        res.message = t('submitBtnNamePlaceholder')
        return res
    }
    if (diyStore.value[index].resetBtn.text == '') {
        res.code = false
        res.message = t('resetBtnNamePlaceholder')
        return res
    }

    return res
}

defineExpose({})

</script>

<style lang="scss" scoped></style>

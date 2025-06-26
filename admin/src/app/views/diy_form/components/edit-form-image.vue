<template>
    <!-- 内容 -->
    <div class="content-wrap" v-show="diyStore.editTab == 'content'">

        <!-- 表单组件 字段内容设置 -->
        <slot name="field"></slot>
        <el-form label-width="100px" class="px-[10px]" @submit.prevent>
            <el-form-item :label="t('imageLimit')">
                <el-input v-model.trim="diyStore.editComponent.limit" :placeholder="t('imageLimitPlaceholder')" clearable maxlength="2" />
            </el-form-item>

            <el-form-item :label="t('上传方式')">
                <el-checkbox-group v-model="diyStore.editComponent.uploadMode" :min="1">
                    <el-checkbox label="拍照上传" value="take_pictures" />
                    <el-checkbox label="从相册选择" value="select_from_album" />
                </el-checkbox-group>
            </el-form-item>
        </el-form>

        <!-- 表单组件 其他设置 -->
        <slot name="other"></slot>

    </div>

    <!-- 样式 -->
    <div class="style-wrap" v-show="diyStore.editTab == 'style'">

        <!-- 表单组件 字段样式 -->
        <slot name="style-field"></slot>

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

// 组件验证
diyStore.editComponent.verify = (index: number) => {
    const res = { code: true, message: '' }
    if (diyStore.value[index].limit == '') {
        res.code = false
        res.message = t('imageLimitPlaceholder')
        return res
    }
    if (isNaN(diyStore.value[index].limit) || !regExp.number.test(diyStore.value[index].limit)) {
        res.code = false
        res.message = t('imageLimitErrorTips')
        return res
    }
    if (diyStore.value[index].limit < 0) {
        res.code = false
        res.message = t('imageLimitErrorTipsTwo')
        return res
    }
    if (diyStore.value[index].limit == 0) {
        res.code = false
        res.message = t('imageLimitErrorTipsThree')
        return res
    }
    if (diyStore.value[index].limit > 9) {
        res.code = false
        res.message = t('imageLimitErrorTipsFour')
        return res
    }
    return res
}

// 正则表达式
const regExp: any = {
    required: /[\S]+/,
    number: /^\d{0,10}$/,
    digit: /^\d{0,10}(.?\d{0,2})$/,
    special: /^\d{0,10}(.?\d{0,3})$/
}

defineExpose({})

</script>

<style lang="scss" scoped></style>

<template>
    <!-- 属性内容 -->
    <div class="content-wrap">
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('textSet') }}</h3>
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('textLabel')">
                    <el-input v-model.trim="posterStore.editComponent.value" type="textarea" rows="4" :placeholder="t('textPlaceholder')" clearable maxlength="50" show-word-limit />
                </el-form-item>
            </el-form>
        </div>

        <!-- 组件公共属性 -->
        <slot name="common"></slot>

    </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { t } from '@/lang'
import usePosterStore from '@/stores/modules/poster'

const posterStore = usePosterStore()

// 组件验证
posterStore.editComponent.verify = (index: number) => {
    const res = { code: true, message: '' }
    if (posterStore.value[index].value == '') {
        res.code = false
        res.message = t('textPlaceholder')
        return res
    }
    return res
}

defineExpose({})

</script>

<style lang="scss" scoped></style>

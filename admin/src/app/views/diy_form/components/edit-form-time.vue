<template>
    <!-- 内容 -->
    <div class="content-wrap" v-show="diyStore.editTab == 'content'">

        <!-- 表单组件 字段内容设置 -->
        <slot name="field"></slot>
        <el-form label-width="100px" class="px-[10px]" @submit.prevent>
            <el-form-item :label="t('formPlaceholder')">
                <el-input v-model.trim="diyStore.editComponent.placeholder" :placeholder="t('formPlaceholderTips')" clearable maxlength="15" show-word-limit />
            </el-form-item>
            <el-form-item :label="t('defaultValue')">
                <el-switch v-model="diyStore.editComponent.defaultControl" @change="changeDateDefaultControl" />
            </el-form-item>
            <el-form-item v-if="diyStore.editComponent.defaultControl">
                <el-radio-group v-model="diyStore.editComponent.timeWay">
                    <el-radio label="current">{{ t('currentTime') }}</el-radio>
                    <el-radio label="diy">{{ t('diyTime') }}</el-radio>
                </el-radio-group>
            </el-form-item>
            <el-form-item v-if="diyStore.editComponent.defaultControl && diyStore.editComponent.timeWay == 'diy'">
                <el-time-picker v-model="diyStore.editComponent.field.default" :placeholder="t('timePlaceholder')" format="HH:mm" value-format="HH:mm" />
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
import { ref, watch, onMounted } from 'vue'
import useDiyStore from '@/stores/modules/diy'

const diyStore = useDiyStore()
diyStore.editComponent.ignore = ['componentBgUrl'] // 忽略公共属性

// 组件验证
diyStore.editComponent.verify = (index: number) => {
    const res = { code: true, message: '' }
    return res
}

onMounted(() => {
    // 初始赋值当天时间
    if (!diyStore.editComponent.field.default) {
        const today = new Date()
        const hours = String(today.getHours()).padStart(2, '0')
        const minutes = String(today.getMinutes()).padStart(2, '0')
        diyStore.editComponent.field.default = `${hours}:${minutes}`
    }
})

const changeDateDefaultControl = (val: any) => {
    if (val) {
        const today = new Date()
        const hours = String(today.getHours()).padStart(2, '0')
        const minutes = String(today.getMinutes()).padStart(2, '0')
        diyStore.editComponent.field.default = `${hours}:${minutes}`
    }
}

watch(
    () => diyStore.editComponent.timeWay,
    (newVal) => {
        const today = new Date()
        const hours = String(today.getHours()).padStart(2, '0')
        const minutes = String(today.getMinutes()).padStart(2, '0')
        diyStore.editComponent.field.default = `${hours}:${minutes}`
    }
)

defineExpose({})

</script>

<style lang="scss" scoped></style>

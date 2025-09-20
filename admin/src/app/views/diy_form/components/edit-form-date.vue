<template>
    <!-- 内容 -->
    <div class="content-wrap" v-show="diyStore.editTab == 'content'">

        <!-- 表单组件 字段内容设置 -->
        <slot name="field"></slot>
        <el-form label-width="100px" class="px-[10px]" @submit.prevent>
            <el-form-item :label="t('dataFormat')">
                <el-radio-group v-model="diyStore.editComponent.dateFormat" class="!block">
                    <el-radio class="!block" label="YYYY年M月D日">{{ dateFormat.format1 }}</el-radio>
                    <el-radio class="!block" label="YYYY-MM-DD">{{ dateFormat.format2 }}</el-radio>
                    <el-radio class="!block" label="YYYY/MM/DD">{{ dateFormat.format3 }}</el-radio>
                    <el-radio class="!block" label="YYYY-MM-DD HH:mm">{{ dateFormat.format4 }}</el-radio>
                </el-radio-group>
            </el-form-item>
            <el-form-item :label="t('formPlaceholder')">
                <el-input v-model.trim="diyStore.editComponent.placeholder" :placeholder="t('formPlaceholderTips')" clearable maxlength="15" show-word-limit />
            </el-form-item>
            <el-form-item :label="t('defaultValue')">
                <el-switch v-model="diyStore.editComponent.defaultControl" />
            </el-form-item>
            <el-form-item v-if="diyStore.editComponent.defaultControl">
                <el-radio-group v-model="diyStore.editComponent.dateWay">
                    <el-radio label="current">{{ t('currentDate') }}</el-radio>
                    <el-radio label="diy">{{ t('diyDate') }}</el-radio>
                </el-radio-group>
            </el-form-item>
            <el-form-item v-if="diyStore.editComponent.defaultControl && diyStore.editComponent.dateWay == 'diy'">
                <el-date-picker v-if="diyStore.editComponent.dateFormat != 'YYYY-MM-DD HH:mm'" v-model="diyStore.editComponent.field.default.date" format="YYYY/MM/DD" value-format="YYYY-MM-DD" type="date" :placeholder="t('dataPlaceholder')" @change="dateChange" />
                <el-date-picker v-else v-model="diyStore.editComponent.field.default.date" format="YYYY/MM/DD HH:mm" value-format="YYYY-MM-DD HH:mm" type="datetime" :placeholder="t('dataPlaceholder')" @change="dateChange" />
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
import { ref, reactive, onMounted } from 'vue'
import { timeTurnTimeStamp } from '@/utils/common'
import useDiyStore from '@/stores/modules/diy'

const diyStore = useDiyStore()
diyStore.editComponent.ignore = ['componentBgUrl'] // 忽略公共属性

// 组件验证
diyStore.editComponent.verify = (index: number) => {
    const res = { code: true, message: '' }
    return res
}

const dateFormat: any = reactive({
    format1: '',
    format2: '',
    format3: '',
    format4: ''
})

onMounted(() => {
    // 初始赋值当天日期
    const today = new Date()
    if (!diyStore.editComponent.field.default.date) {
        diyStore.editComponent.field.default.date = today.toISOString().split('T')[0]
        diyStore.editComponent.field.default.timestamp = today.getTime() / 1000
    }
    const year = today.getFullYear()
    const month = String(today.getMonth() + 1).padStart(2, '0')
    const day = String(today.getDate()).padStart(2, '0')

    const hours = String(today.getHours()).padStart(2, '0')
    const minutes = String(today.getMinutes()).padStart(2, '0')
    dateFormat.format1 = `${year}年${month}月${day}日`
    dateFormat.format2 = `${year}-${month}-${day}`
    dateFormat.format3 = `${year}/${month}/${day}`
    dateFormat.format4 = `${year}-${month}-${day} ${hours}:${minutes}`
})

const dateChange = (date: any) => {
    diyStore.editComponent.field.default.date = date
    diyStore.editComponent.field.default.timestamp = timeTurnTimeStamp(date)
}

defineExpose({})

</script>

<style lang="scss" scoped></style>

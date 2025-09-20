<template>
    <!-- 内容 -->
    <div class="content-wrap" v-show="diyStore.editTab == 'content'">

        <!-- 表单组件 字段内容设置 -->
        <slot name="field"></slot>
        <el-form label-width="100px" class="px-[10px]">
            <el-form-item :label="t('dataFormat')">
                <el-radio-group v-model="diyStore.editComponent.dateFormat">
                    <div class="flex flex-col">
                        <el-radio label="YYYY年M月D日">{{ dateFormat.format1 }}</el-radio>
                        <el-radio label="YYYY-MM-DD">{{ dateFormat.format2 }}</el-radio>
                        <el-radio label="YYYY/MM/DD">{{ dateFormat.format3 }}</el-radio>
                    </div>
                </el-radio-group>
            </el-form-item>
        </el-form>

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('startDate') }}</h3>
            <el-form label-width="100px" class="px-[10px]" @submit.prevent>
                <el-form-item :label="t('formPlaceholder')">
                    <el-input v-model.trim="diyStore.editComponent.start.placeholder" :placeholder="t('formPlaceholderTips')" clearable maxlength="15" show-word-limit />
                </el-form-item>
                <el-form-item :label="t('defaultValue')">
                    <el-switch v-model="diyStore.editComponent.start.defaultControl" />
                </el-form-item>
                <el-form-item v-if="diyStore.editComponent.start.defaultControl">
                    <el-radio-group v-model="diyStore.editComponent.start.dateWay">
                        <el-radio label="current">{{ t('currentDate') }}</el-radio>
                        <el-radio label="diy">{{ t('diyDate') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item
                    v-if="diyStore.editComponent.start.defaultControl && diyStore.editComponent.start.dateWay == 'diy'">
                    <el-date-picker v-model="diyStore.editComponent.field.default.start.date" format="YYYY/MM/DD" value-format="YYYY-MM-DD" type="date" :placeholder="t('startDataPlaceholder')" @change="startDateChange" />
                </el-form-item>
            </el-form>
        </div>

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('endDate') }}</h3>
            <el-form label-width="100px" class="px-[10px]" @submit.prevent>
                <el-form-item :label="t('formPlaceholder')">
                    <el-input v-model.trim="diyStore.editComponent.end.placeholder" :placeholder="t('formPlaceholderTips')" clearable maxlength="15" show-word-limit />
                </el-form-item>
                <el-form-item :label="t('defaultValue')">
                    <el-switch v-model="diyStore.editComponent.end.defaultControl" />
                </el-form-item>
                <el-form-item v-if="diyStore.editComponent.end.defaultControl">
                    <el-radio-group v-model="diyStore.editComponent.end.dateWay">
                        <el-radio label="current">{{ t('currentDate') }}</el-radio>
                        <el-radio label="diy">{{ t('diyDate') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item
                    v-if="diyStore.editComponent.end.defaultControl && diyStore.editComponent.end.dateWay == 'diy'">
                    <el-date-picker :disabled-date="disabledEndDate"
                                    v-model="diyStore.editComponent.field.default.end.date" format="YYYY/MM/DD"
                                    value-format="YYYY-MM-DD" type="date" :placeholder="t('endDataPlaceholder')"
                                    @change="endDateChange" />
                </el-form-item>
            </el-form>
        </div>

        <!-- 表单组件 其他设置 -->
        <slot name="other"></slot>

    </div>

    <!-- 样式 -->
    <div class="style-wrap" v-show="diyStore.editTab == 'style'">

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('textStyle') }}</h3>
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('textFontSize')">
                    <el-slider v-model="diyStore.editComponent.fontSize" show-input size="small" class="ml-[10px] diy-nav-slider" :min="12" :max="18" />
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
import { ref, reactive, onMounted } from 'vue'
import { timeTurnTimeStamp } from '@/utils/common'
import useDiyStore from '@/stores/modules/diy'

const diyStore = useDiyStore()
diyStore.editComponent.ignore = ['componentBgUrl'] // 忽略公共属性

// 组件验证
diyStore.editComponent.verify = (index: number) => {
    const res = { code: true, message: '' }
    let starTime = diyStore.value[index].field.default.start.date
    let endTime = diyStore.value[index].field.default.end.date

    const today = new Date()
    const hours = String(today.getHours()).padStart(2, '0')
    const minutes = String(today.getMinutes()).padStart(2, '0')

    if (diyStore.editComponent.start.dateWay == 'current') {
        starTime = today.toISOString().split('T')[0]
    }
    if (diyStore.editComponent.end.dateWay == 'current') {
        endTime = today.toISOString().split('T')[0]
    }

    if (diyStore.editComponent.start.defaultControl && starTime == '' && diyStore.editComponent.end.dateWay == 'diy') {
        res.code = false
        res.message = t('startDataTips')
        return res
    }
    if (diyStore.editComponent.end.defaultControl && endTime == '' && diyStore.editComponent.end.dateWay == 'diy') {
        res.code = false
        res.message = t('endDataTips')
        return res
    }

    if (diyStore.editComponent.start.defaultControl && diyStore.editComponent.end.defaultControl && timeTurnTimeStamp(starTime) > timeTurnTimeStamp(endTime)) {
        res.code = false
        res.message = t('startEndDataTips')
        return res
    }
    return res
}

const dateFormat: any = reactive({
    format1: '',
    format2: '',
    format3: ''
})

// 结束日期-禁止的日期
const disabledEndDate = (time: Date) => {
    let cutoffDate = null
    let bool = false
    if (diyStore.editComponent.start && diyStore.editComponent.start.defaultControl) {
        if (diyStore.editComponent.start.dateWay == 'diy') {
            cutoffDate = new Date(diyStore.editComponent.field.default.start.date)
        } else {
            cutoffDate = new Date()
        }
        bool = time.getTime() < cutoffDate.getTime()
    }
    return bool
}

onMounted(() => {
    const today = new Date()
    const endDate = new Date()
    endDate.setDate(endDate.getDate() + 7) // 设置日期为7天后的日期

    if (diyStore.editComponent.field.default.start.timestamp) {
        diyStore.editComponent.field.default.start.date = today.toISOString().split('T')[0]
        diyStore.editComponent.field.default.start.timestamp = parseInt(today.getTime() / 1000)
    }

    if (diyStore.editComponent.field.default.end.timestamp) {
        diyStore.editComponent.field.default.end.date = endDate.toISOString().split('T')[0]
        diyStore.editComponent.field.default.end.timestamp = parseInt(endDate.getTime() / 1000)
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

// 开始日期选择器
const startDateChange = (date) => {
    diyStore.editComponent.field.default.start.date = date
    diyStore.editComponent.field.default.start.timestamp = timeTurnTimeStamp(date)

    const endDate = new Date(date)
    endDate.setDate(endDate.getDate() + 7)
    diyStore.editComponent.field.default.end.date = endDate.toISOString().split('T')[0]
    diyStore.editComponent.field.default.end.timestamp = parseInt(endDate.getTime() / 1000)
}
// 结束日期选择器
const endDateChange = (date) => {
    diyStore.editComponent.field.default.end.date = date
    diyStore.editComponent.field.default.end.timestamp = timeTurnTimeStamp(date)
}

defineExpose({})
</script>

<style lang="scss" scoped></style>

<template>
    <!-- 内容 -->
    <div class="content-wrap" v-show="diyStore.editTab == 'content'">

        <!-- 表单组件 字段内容设置 -->
        <slot name="field"></slot>

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('startTime') }}</h3>
            <el-form label-width="100px" class="px-[10px]" @submit.prevent>
                <el-form-item :label="t('formPlaceholder')">
                    <el-input v-model.trim="diyStore.editComponent.start.placeholder" :placeholder="t('formPlaceholderTips')" clearable maxlength="15" show-word-limit />
                </el-form-item>
                <el-form-item :label="t('defaultValue')">
                    <el-switch v-model="diyStore.editComponent.start.defaultControl" />
                </el-form-item>
                <el-form-item v-if="diyStore.editComponent.start.defaultControl">
                    <el-radio-group v-model="diyStore.editComponent.start.timeWay">
                        <el-radio label="current">{{ t('currentTime') }}</el-radio>
                        <el-radio label="diy">{{ t('diyTime') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item
                    v-if="diyStore.editComponent.start.defaultControl && diyStore.editComponent.start.timeWay == 'diy'">
                    <el-time-picker v-model="diyStore.editComponent.field.default.start.date" :placeholder="t('startTimePlaceholder')" format="HH:mm" value-format="HH:mm" @change="startTimePickerChange" />
                </el-form-item>
            </el-form>
        </div>

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('endTime') }}</h3>
            <el-form label-width="100px" class="px-[10px]" @submit.prevent>
                <el-form-item :label="t('formPlaceholder')">
                    <el-input v-model.trim="diyStore.editComponent.end.placeholder" :placeholder="t('formPlaceholderTips')" clearable maxlength="15" show-word-limit />
                </el-form-item>
                <el-form-item :label="t('defaultValue')">
                    <el-switch v-model="diyStore.editComponent.end.defaultControl" />
                </el-form-item>
                <el-form-item v-if="diyStore.editComponent.end.defaultControl">
                    <el-radio-group v-model="diyStore.editComponent.end.timeWay">
                        <el-radio label="current">{{ t('currentTime') }}</el-radio>
                        <el-radio label="diy">{{ t('diyTime') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item
                    v-if="diyStore.editComponent.end.defaultControl && diyStore.editComponent.end.timeWay == 'diy'">
                    <el-time-picker :disabled-hours="disabledHours" :disabled-minutes="disabledMinutes" v-model="diyStore.editComponent.field.default.end.date" :placeholder="t('endTimePlaceholder')" format="HH:mm" value-format="HH:mm" />
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
import { ref, onMounted } from 'vue'
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

    if (diyStore.editComponent.start.timeWay == 'current') {
        starTime = `${hours}:${minutes}`
    }
    if (diyStore.editComponent.end.timeWay == 'current') {
        endTime = `${hours}:${minutes}`
    }

    if (diyStore.editComponent.start.defaultControl && starTime == '') {
        res.code = false
        res.message = t('startTimeTips')
        return res
    }
    if (diyStore.editComponent.end.defaultControl && endTime == '') {
        res.code = false
        res.message = t('endTimeTips')
        return res
    }

    if (diyStore.editComponent.start.defaultControl && diyStore.editComponent.end.defaultControl && timeInvertSecond(starTime) > timeInvertSecond(endTime)) {
        res.code = false
        res.message = t('startEndTimeTips')
        return res
    }
    return res
}

onMounted(() => {
    const today = new Date()
    const hours = String(today.getHours()).padStart(2, '0')
    const minutes = String(today.getMinutes()).padStart(2, '0')

    if (!diyStore.editComponent.field.default.start.date) {
        diyStore.editComponent.field.default.start.date = `${hours}:${minutes}`
        diyStore.editComponent.field.default.start.timestamp = timeInvertSecond(`${hours}:${minutes}`)
    }
    if (!diyStore.editComponent.field.default.end.date) {
        const endDate = new Date()
        endDate.setHours(today.getHours(), today.getMinutes() + 10, 0, 0) // 在当前时间基础上加 10 分钟
        const endHours = String(endDate.getHours()).padStart(2, '0')
        const endMinutes = String(endDate.getMinutes()).padStart(2, '0')

        diyStore.editComponent.field.default.end.date = `${endHours}:${endMinutes}`
        diyStore.editComponent.field.default.end.timestamp = timeInvertSecond(`${endHours}:${endMinutes}`)
    }
})

// 开始时间选择器
const startTimePickerChange = (e) => {
    diyStore.editComponent.field.default.start.timestamp = timeInvertSecond(e)

    const startTimeArr = e.split(':')
    const date = new Date()
    date.setHours(parseInt(startTimeArr[0]), parseInt(startTimeArr[1]), 0, 0)
    date.setMinutes(date.getMinutes() + 10)
    const updatedEndTime = `${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`
    diyStore.editComponent.field.default.end.date = updatedEndTime
    diyStore.editComponent.field.default.end.timestamp = timeInvertSecond(updatedEndTime)
}

// 结束时间选择器
const endTimePickerChange = (e) => {
    diyStore.editComponent.field.default.end.timestamp = timeInvertSecond(e)
}

const disabledHours = () => {
    const timeArr = diyStore.editComponent.field.default.start.date.split(':')
    return makeRange(0, timeArr[0])
}

const disabledMinutes = (hour: number) => {
    const timeArr = diyStore.editComponent.field.default.start.date.split(':')
    return makeRange(0, timeArr[1])
}

const makeRange = (start: number, end: number) => {
    const result: number[] = []
    for (let i = start; i < end; i++) {
        result.push(i)
    }
    return result
}

const timeInvertSecond = (time: any) => {
    const arr = time.split(':')
    let num = 0
    if (arr[0]) {
        num += arr[0] * 60 * 60
    }
    if (arr[1]) {
        num += arr[1] * 60
    }
    if (arr[2]) {
        num += arr[2]
    }
    return num
}

defineExpose({})
</script>

<style lang="scss" scoped></style>

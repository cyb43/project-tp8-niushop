<template>
    <view v-if="diyComponent.viewFormDetail" class="form-item-frame">
        <view class="base-layout-one" v-if="diyGlobal.completeLayout == 'style-1'">
            <view class="detail-one-content">
                <text class="detail-one-content-label">{{ diyComponent.field.name }}</text>
                <text class="detail-one-content-value">{{ diyComponent.field.value.date }}</text>
            </view>
        </view>
        <view class="base-layout-two" v-if="diyGlobal.completeLayout == 'style-2'">
            <view class="detail-two-content">
                <text class="detail-two-content-label">{{ diyComponent.field.name }}</text>
                <view class="detail-two-content-value w-[80%]">
                    <text>{{ diyComponent.field.value.date || t('notHave') }}</text>
                    <text v-if="diyComponent.isShowArrow" class="iconfont iconfanhui1 text-[#888] !text-[20rpx] ml-[10rpx]"></text>
                </view>
            </view>
        </view>
    </view>
    <view v-else :style="warpCss" class="form-item-frame">
        <view class="base-layout-one" v-if="diyGlobal.completeLayout == 'style-1'">
            <view class="layout-one-label">
                <text class="name" :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx' ,'font-weight': diyComponent.fontWeight}">{{ diyComponent.field.name }}</text>
                <text class="required">{{ diyComponent.field.required ? '*' : '' }}</text>
                <text v-if="diyStore.mode == 'decorate' && diyComponent.isHidden" class="is-hidden">{{ t('diyForm.hidden') }}</text>
            </view>
            <view v-if="diyComponent.field.remark.text" class="layout-one-remark" :style="{ color: diyComponent.field.remark.color, fontSize: (diyComponent.field.remark.fontSize * 2 ) + 'rpx' }">{{ diyComponent.field.remark.text }}</view>
            <view class="layout-one-error-message" v-if="errorInfo && !errorInfo.code">{{ errorInfo.message }}</view>
            <view class="layout-one-content" @click="openCalendar">
                <view class="nc-iconfont nc-icon-a-riliV6xx-36 !text-[32rpx] text-[#999] mr-[16rpx]"></view>
                <view class="flex-1 text-overflow-ellipsis flex"
                      :class="{'!text-[#999]' : !diyComponent.field.value.date && !diyComponent.defaultControl}"
                      :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx' }">{{ startDate }}</view>
            </view>
            <view class="layout-one-attribute-wrap" v-if="inputAttribute().length">
                <view v-for="(item,index) in inputAttribute()" :key="index" @click="eventFn(item.type)" class="layout-one-attribute-item">{{ item.title }}</view>
            </view>
        </view>
        <view class="base-layout-two" v-if="diyGlobal.completeLayout == 'style-2'">
            <text v-if="diyStore.mode == 'decorate' && diyComponent.isHidden" class="layout-two-is-hidden">{{ t('diyForm.hidden') }}</text>
            <view class="layout-two-wrap" :class="{'no-border': !diyGlobal.borderControl}">
                <view class="layout-two-label"
                      :class="{'justify-start': diyGlobal.completeAlign == 'left', 'justify-end': diyGlobal.completeAlign == 'right'}">
                    <text class="required" v-if="diyComponent.field.required">{{ diyComponent.field.required ? '*' : '' }}</text>
                    <text class="name" :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx' ,'font-weight': diyComponent.fontWeight}">{{ diyComponent.field.name }}</text>
                </view>
                <view class="layout-two-content" @click="openCalendar">
                    <view class="flex-1 text-overflow-ellipsis flex justify-end"
                          :class="{'!text-[#999]' : !diyComponent.field.value.date && !diyComponent.defaultControl}"
                          :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx'}">{{ startDate }}</view>
                    <text class="nc-iconfont !text-[#666] !text-[36rpx] nc-icon-youV6xx -mr-[8rpx]"></text>
                </view>
            </view>
            <view class="layout-two-error-message" v-if="errorInfo && !errorInfo.code">{{ errorInfo.message }}</view>
            <view v-if="diyComponent.field.remark.text" class="layout-two-remark"
                  :style="{ color: diyComponent.field.remark.color, fontSize: (diyComponent.field.remark.fontSize * 2 ) + 'rpx' }">
                {{ diyComponent.field.remark.text }}
            </view>
            <view class="layout-two-attribute-wrap" v-if="inputAttribute().length">
                <view v-for="(item,index) in inputAttribute()" :key="index" @click="eventFn(item.type)" class="layout-two-attribute-item">{{ item.title }}</view>
            </view>
        </view>
        <view class="calendar-wrap">
            <u-calendar :show="calendarShow" mode="single" @confirm="confirm" @close="calendarShow=false"
                        closeOnClickOverlay="true"
                        :formatter="formatter" confirmDisabledText="禁止选择" color="var(--primary-color)"
                        ref="calendar" :maxDate="maxDate"></u-calendar>
            <u-datetime-picker :show="show" v-model="diyComponent.field.value.date" mode="datetime" @cancel="show=false"
                               closeOnClickOverlay="true" @confirm="calendarConfirm" @close="show=false"
                               :minDate="minDate"></u-datetime-picker>
        </view>
        <!-- 遮罩层，装修使用 -->
        <view v-if="diyStore.mode == 'decorate'" class="form-item-mask"></view>
    </view>
</template>

<script setup lang="ts">
// 表单 日期组件
import { ref, computed, watch, onMounted } from 'vue';
import { img, timeStampTurnTime, timeTurnTimeStamp } from '@/utils/common';
import { t } from '@/locale'
import useDiyStore from '@/app/stores/diy';

let currentDate = new Date();
currentDate.setFullYear(currentDate.getFullYear() + 1);
const maxDate = ref(currentDate.getTime());

let currentDateTime = new Date();
const minDate = ref(currentDateTime.getTime());

const props = defineProps(['component', 'index', 'global']);
const diyStore = useDiyStore();
const calendarShow = ref(false);
const show = ref(false);

const errorInfo: any = ref(null);

const diyComponent = computed(() => {
    if (diyStore.mode == 'decorate') {
        return diyStore.value[props.index];
    } else {
        return props.component;
    }
})

const diyGlobal = computed(() => {
    return props.global;
})

// input属性
const inputAttribute = () => {
    let arr = [];
    if (diyComponent.value.autofill) {
        let obj = {
            title: '已自动填充'
        };
        arr.push(obj);
    }

    arr.forEach((item, index, arr) => {
        if (index != (arr.length - 1)) {
            let obj = {
                title: '|'
            };
            arr.push(obj);
        }
    })
    return arr;
}

const eventFn = (type: any) => {
}

// 日期
const startDate = computed(() => {
    let date = '';
    if (diyComponent.value.field.value.date) {
        let timestamp = diyComponent.value.field.value.timestamp;
        date = getDateFn(timestamp);

        diyComponent.value.field.value.date = date;
        diyComponent.value.field.value.timestamp = timeTurnTimeStamp(date);
    } else {
        if (diyComponent.value.defaultControl) {
            if (diyComponent.value.dateWay == 'current') {
                date = getDateFn();
            } else if (diyComponent.value.dateWay == 'diy') {
                let timestamp = diyComponent.value.field.default.timestamp || '';
                date = getDateFn(timestamp);
            }

            diyComponent.value.field.value.date = date;
            diyComponent.value.field.value.timestamp = timeTurnTimeStamp(date);
        } else {
            date = diyComponent.value.placeholder;
        }
    }
    return date;
})

const warpCss = computed(() => {
    let style = '';
    style += 'position:relative;';
    if (diyComponent.value.componentStartBgColor && diyComponent.value.componentEndBgColor) style += `background:linear-gradient(${ diyComponent.value.componentGradientAngle },${ diyComponent.value.componentStartBgColor },${ diyComponent.value.componentEndBgColor });`;
    else if (diyComponent.value.componentStartBgColor) style += 'background-color:' + diyComponent.value.componentStartBgColor + ';';
    else if (diyComponent.value.componentEndBgColor) style += 'background-color:' + diyComponent.value.componentEndBgColor + ';';

    if (diyComponent.value.componentBgUrl) {
        style += `background-image:url('${ img(diyComponent.value.componentBgUrl) }');`;
        style += 'background-size: cover;background-repeat: no-repeat;';
    }

    if (diyComponent.value.topRounded) style += 'border-top-left-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
    if (diyComponent.value.topRounded) style += 'border-top-right-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    return style;
})

onMounted(() => {
    refresh();
    // 装修模式下刷新
    if (diyStore.mode == 'decorate') {
        watch(
            () => diyComponent.value,
            (newValue, oldValue) => {
                if (newValue && newValue.componentName == 'FormDate') {
                    refresh();
                }
            }
        )
    }
});

const refresh = () => {
    // todo 开始、结束时间给予默认值
    // {{ diyComponent.defaultControl ? diyComponent.value.field.value.date : diyComponent.placeholder }}
}

// 表单组件验证
const verify = () => {
    const res = { code: true, message: '' }
    if (diyComponent.value.field.required && (!diyComponent.value.field.value || !diyComponent.value.field.value.timestamp)) {
        res.code = false
        res.message = `请选择${ diyComponent.value.placeholder }`;
    }
    errorInfo.value = res;
    return res;
}

// 重置表单组件数据
const reset = () => {
    diyComponent.value.field.value.date = '';
    diyComponent.value.field.value.timestamp = '';
}

const openCalendar = () => {
    if (diyStore.mode === 'decorate') return;
    if (diyComponent.value.dateFormat == 'YYYY-MM-DD HH:mm') {
        show.value = true
    } else {
        calendarShow.value = true;
    }
}

const confirm = (e) => {
    diyComponent.value.field.value.date = e[0];
    diyComponent.value.field.value.timestamp = timeTurnTimeStamp(e[0]); // todo 需要赋值
    calendarShow.value = false;
}
const calendarConfirm = (e) => {
    diyComponent.value.field.value.date = e.value;
    diyComponent.value.field.value.timestamp = timeTurnTimeStamp(e.value); // todo 需要赋值
    show.value = false;
}

const formatter = (day) => {
    const time = timeStampTurnTime(Date.parse(day.date) / 1000, "year_month_day");
    return day
}

const getDateFn = (data: any = '', type: any = diyComponent.value.dateFormat) => {
    let date = data ? new Date(data * 1000) : new Date();
    let year = date.getFullYear();
    let month = String(date.getMonth() + 1).padStart(2, '0');
    let day = String(date.getDate()).padStart(2, '0');

    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    let str = '';
    if (type == 'YYYY年M月D日') {
        str = `${ year }年${ month }月${ day }日`;
    } else if (type == 'YYYY-MM-DD') {
        str = `${ year }-${ month }-${ day }`;
    } else if (type == 'YYYY/MM/DD') {
        str = `${ year }/${ month }/${ day }`;
    } else if (type == 'YYYY-MM-DD HH:mm') {
        str = `${ year }-${ month }-${ day } ${ hours }:${ minutes }`;
    }
    return str;
}

defineExpose({
    verify,
    reset
})
</script>

<style lang="scss" scoped>
@import '@/styles/diy_form.scss';
</style>

<style lang="scss">
.form-item-frame .calendar-wrap .u-calendar {
    :deep(.u-calendar-header__title), :deep(.u-calendar-header__subtitle), :deep(.u-calendar-month__title) {
        display: block;
    }
}

.form-item-frame :deep(.u-picker .u-toolbar__wrapper__cancel), .form-item-frame :deep(.u-picker .u-toolbar__wrapper__confirm) {
    font-size: 28rpx;
}
</style>

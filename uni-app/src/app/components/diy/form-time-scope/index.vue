<template>
    <view v-if="diyComponent.viewFormDetail" class="form-item-frame">
        <view class="base-layout-one" v-if="diyGlobal.completeLayout == 'style-1'">
            <view class="detail-one-content" v-if="diyComponent.field.value.start.date || diyComponent.field.value.end.date">
                <text class="detail-one-content-label">{{ diyComponent.field.name }}</text>
                <view class="detail-one-content-value">
                    <text> {{ diyComponent.field.value.start.date }}</text>
                    <text v-if="diyComponent.field.value.start.date && diyComponent.field.value.end.date"> -</text>
                    <text>{{ diyComponent.field.value.end.date }}</text>
                </view>
            </view>
        </view>
        <view class="base-layout-two" v-if="diyGlobal.completeLayout == 'style-2'">
            <view class="detail-two-content" v-if="diyComponent.field.value.start.date || diyComponent.field.value.end.date">
                <text class="detail-two-content-label">{{ diyComponent.field.name }}</text>
                <view class="detail-two-content-value w-[80%]">
                    <text>{{ diyComponent.field.value.start.date }}</text>
                    <text v-if="diyComponent.field.value.start.date && diyComponent.field.value.end.date"> -</text>
                    <text>{{ diyComponent.field.value.end.date }}</text>
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
            <view class="flex items-center">
                <view class="layout-one-content flex-1" @click="startDatetime = true">
                    <view class="nc-iconfont nc-icon-a-shijianV6xx-36 !text-[32rpx] text-[#999] mr-[16rpx]"></view>
                    <view class="flex-1 text-overflow-ellipsis"
                          :class="{'!text-[#999]' : !diyComponent.defaultControl && !diyComponent.field.value.start.date}"
                          :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx'}">{{ startTime }}</view>
                </view>
                <view class="mx-[10rpx]" :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx'}">-</view>
                <view class="layout-one-content flex-1" @click="endDatetime = true">
                    <view class="nc-iconfont nc-icon-a-shijianV6xx-36 !text-[32rpx] text-[#999] mr-[16rpx]"></view>
                    <view class="flex-1 text-overflow-ellipsis"
                          :class="{'!text-[#999]' : !diyComponent.defaultControl && !diyComponent.field.value.end.date}"
                          :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx'}">{{ endTime }}</view>
                </view>
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
                    <view class="text-overflow-ellipsis flex justify-center"
                          :class="{'!text-[#999]' : !diyComponent.field.value.start.date && !diyComponent.defaultControl}"
                          :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx'}"
                          @click="startDatetime = true">{{ startTime }}</view>
                    <view class="mx-[10rpx]" :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx'}">-</view>
                    <view class="text-overflow-ellipsis flex justify-center"
                          :class="{'!text-[#999]' : !diyComponent.field.value.end.date && !diyComponent.defaultControl}"
                          :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx'}"
                          @click="endDatetime = true">{{ endTime }}</view>
                    <text class="nc-iconfont !text-[#666] !text-[36rpx] nc-icon-youV6xx -mr-[8rpx]"></text>
                </view>
            </view>
            <view class="layout-two-error-message" v-if="errorInfo && !errorInfo.code">{{ errorInfo.message }}</view>
            <view v-if="diyComponent.field.remark.text" class="layout-two-remark"
                  :style="{ color: diyComponent.field.remark.color, fontSize: (diyComponent.field.remark.fontSize * 2 ) + 'rpx' }">{{ diyComponent.field.remark.text }}</view>
            <view class="layout-two-attribute-wrap" v-if="inputAttribute().length">
                <view v-for="(item,index) in inputAttribute()" :key="index" @click="eventFn(item.type)" class="layout-two-attribute-item">{{ item.title }}</view>
            </view>
        </view>
        <u-datetime-picker :show="startDatetime" v-model="diyComponent.field.value.start.date" mode="time"
                           @cancel="startDatetime=false" @confirm="startTimeConfirm" @close="startDatetime=false"
                           closeOnClickOverlay="true"></u-datetime-picker>
        <u-datetime-picker :show="endDatetime" :minHour="endMinHour" :minMinute="endMinMinute"
                           v-model="diyComponent.field.value.end.date" mode="time" @cancel="endTimeCancel"
                           @confirm="endTimeConfirm" @change="endTimeChange" @close="startDatetime=false"
                           closeOnClickOverlay="true"></u-datetime-picker>
        <!-- 遮罩层，装修使用 -->
        <view v-if="diyStore.mode == 'decorate'" class="form-item-mask"></view>
    </view>
</template>

<script setup lang="ts">
// 表单 时间范围组件
import { ref, computed, watch, onMounted } from 'vue';
import { img } from '@/utils/common';
import { t } from '@/locale'
import useDiyStore from '@/app/stores/diy';

const props = defineProps(['component', 'index', 'global']);
const diyStore = useDiyStore();
const startDatetime = ref(false);
const endDatetime = ref(false);
const calendarShow = ref(false);

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

// 开始时间
const startTime = computed(() => {
    let returnTime = '';
    let time = ''
    if (diyComponent.value.field.value.start.date) {
        returnTime = diyComponent.value.field.value.start.date;
        time = diyComponent.value.field.value.start.date;
    } else {
        if (diyComponent.value.start.defaultControl) {
            if (diyComponent.value.start.timeWay == 'current') {
                returnTime = getTimeFn();
                time = getTimeFn();
            } else if (diyComponent.value.start.timeWay == 'diy') {
                returnTime = diyComponent.value.field.default.start.date;
                time = diyComponent.value.field.default.start.date;
            }
        } else {
            returnTime = diyComponent.value.start.placeholder;
            time = '';
        }
    }

    diyComponent.value.field.value.start.date = time;
    diyComponent.value.field.value.start.timestamp = time ? timeInvertSecond(time) : 0;
    return returnTime;
})

// 结束时间
const endTime = computed(() => {
    let returnTime = '';
    let time = ''
    if (diyComponent.value.field.value.end.date) {
        returnTime = diyComponent.value.field.value.end.date;
        time = diyComponent.value.field.value.end.date;
    } else {
        if (diyComponent.value.end.defaultControl) {
            if (diyComponent.value.end.timeWay == 'current') {
                let now = new Date();
                let tenMinutesLater = new Date(now.getTime() + 10 * 60 * 1000);
                returnTime = getTimeFn(tenMinutesLater);
                time = getTimeFn(tenMinutesLater);
            } else if (diyComponent.value.end.timeWay == 'diy') {
                returnTime = diyComponent.value.field.default.end.date;
                time = diyComponent.value.field.default.end.date;
            }
        } else {
            returnTime = diyComponent.value.end.placeholder;
            time = '';
        }
    }
    diyComponent.value.field.value.end.date = time;
    diyComponent.value.field.value.end.timestamp = time ? timeInvertSecond(time) : 0;
    return returnTime;
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
                if (newValue && newValue.componentName == 'FormTimeScope') {
                    refresh();
                }
            }
        )
    }
});

const refresh = () => {
    // todo 开始、结束时间给予默认值
    // {{ diyComponent.start.defaultControl ? diyComponent.field.value.start : diyComponent.start.placeholder }}
}

// 表单组件验证
const verify = () => {
    const res = { code: true, message: '' }
    if (diyComponent.value.field.required && diyComponent.value.field.value.start.date == '') {
        res.code = false
        res.message = `请选择${ diyComponent.value.start.placeholder }`;
    } else if (diyComponent.value.field.required && diyComponent.value.field.value.end.date == '') {
        res.code = false
        res.message = `请选择${ diyComponent.value.end.placeholder }`;
    } else if (diyComponent.value.field.value.start.timestamp >= diyComponent.value.field.value.end.timestamp && diyComponent.value.field.value.start.timestamp && diyComponent.value.field.value.end.timestamp) {
        res.code = false
        res.message = `开始时间不能大于等于结束时间`;
    }
    errorInfo.value = res;
    return res;
}

// 重置表单组件数据
const reset = () => {
    diyComponent.value.field.value.start.date = '';
    diyComponent.value.field.value.start.timestamp = 0;
    diyComponent.value.field.value.end.date = '';
    diyComponent.value.field.value.end.timestamp = 0;
}

const openCalendar = () => {
    if (diyStore.mode === 'decorate') return;
    calendarShow.value = true;
}

const startTimeConfirm = (e) => {
    diyComponent.value.field.value.start.date = e.value;
    diyComponent.value.field.value.start.timestamp = timeInvertSecond(e.value);

    let returnTime = e.value;
    let startTime = new Date(`1970-01-01T${ e.value }:00`);
    startTime.setMinutes(startTime.getMinutes() + 10);
    returnTime = getTimeFn(startTime);
    diyComponent.value.field.value.end.date = returnTime;
    diyComponent.value.field.value.end.timestamp = timeInvertSecond(returnTime);

    startDatetime.value = false;
}

const endTimeConfirm = (e) => {
    diyComponent.value.field.value.end.date = e.value;
    diyComponent.value.field.value.end.timestamp = timeInvertSecond(e.value);
    endDatetime.value = false;
}

// 辅助设置结束时间可选的最小分钟
const endTimeChange = (e) => {
    if (!temporaryEndTime.value) temporaryEndTime.value = diyComponent.value.field.value.end.date;
    diyComponent.value.field.value.end.date = e.value;
}

const endTimeCancel = () => {
    if (temporaryEndTime.value) diyComponent.value.field.value.end.date = temporaryEndTime.value;
    temporaryEndTime.value = '';
    endDatetime.value = false;
}

// 设置结束时间的最小小时
const endMinHour = computed(() => {
    let arr = diyComponent.value.field.value.start.date.split(':')
    return Number(arr[0] ? arr[0] : '0');
})
// 设置结束时间的最小分钟
let temporaryEndTime: any = ref("");
const endMinMinute = computed({
    get: () => {
        let startArr = diyComponent.value.field.value.start.date.split(':');
        let endArr = diyComponent.value.field.value.end.date.split(':');
        let num = startArr[0] == endArr[0] ? startArr[1] : 0;
        return Number(num ? num : '0');
    },
    set: (newValue) => {

    }
})

const confirm = (e) => {
    calendarShow.value = false;
}

const getTimeFn = (data: any = '') => {
    let now = data ? new Date(data) : new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    let str = `${ hours }:${ minutes }`;
    return str;
}

// 时间转换为秒
const timeInvertSecond = (time: any) => {
    let arr = time.split(":");
    let num = 0;
    if (arr[0]) {
        num += arr[0] * 60 * 60;
    }
    if (arr[1]) {
        num += arr[1] * 60;
    }
    if (arr[2]) {
        num += arr[2];
    }
    return num;
}

defineExpose({
    verify,
    reset
})
</script>

<style lang="scss" scoped>
@import '@/styles/diy_form.scss';

.text-overflow-ellipsis {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.form-item-frame :deep(.u-picker .u-toolbar__wrapper__cancel), .form-item-frame :deep(.u-picker .u-toolbar__wrapper__confirm) {
    font-size: 28rpx;
}
</style>

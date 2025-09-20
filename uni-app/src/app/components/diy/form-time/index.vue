<template>
    <view v-if="diyComponent.viewFormDetail" class="form-item-frame">
        <view class="base-layout-one" v-if="diyGlobal.completeLayout == 'style-1'">
            <view class="detail-one-content">
                <text class="detail-one-content-label">{{ diyComponent.field.name }}</text>
                <text class="detail-one-content-value">{{ diyComponent.field.value }}</text>
            </view>
        </view>
        <view class="base-layout-two" v-if="diyGlobal.completeLayout == 'style-2'">
            <view class="detail-two-content">
                <text class="detail-two-content-label">{{ diyComponent.field.name }}</text>
                <view class="detail-two-content-value w-[80%]">
                    <text>{{ diyComponent.field.value || t('notHave') }}</text>
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
            <view class="layout-one-content">
                <view class="nc-iconfont nc-icon-a-shijianV6xx-36 !text-[32rpx] text-[#999] mr-[16rpx]"></view>
                <view class="flex-1 text-overflow-ellipsis flex"
                      :class="{'!text-[#999]' : !diyComponent.field.value && !diyComponent.defaultControl}"
                      :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx'}"
                      @click="startDatetime = true">{{ startTime }}</view>
            </view>
            <view class="layout-one-attribute-wrap" v-if="inputAttribute().length">
                <view v-for="(item,index) in inputAttribute()" :key="index" @click="eventFn(item.type)" class="layout-one-attribute-item">{{ item.title }}</view>
            </view>
        </view>
        <view class="base-layout-two" v-if="diyGlobal.completeLayout == 'style-2'">
            <text v-if="diyStore.mode == 'decorate' && diyComponent.isHidden" class="layout-two-is-hidden">{{ t('diyForm.hidden') }}</text>
            <view class="layout-two-wrap" :class="{'no-border': !diyGlobal.borderControl}">
                <view class="layout-two-label" :class="{'justify-start': diyGlobal.completeAlign == 'left', 'justify-end': diyGlobal.completeAlign == 'right'}">
                    <text class="required" v-if="diyComponent.field.required">{{ diyComponent.field.required ? '*' : '' }}</text>
                    <text class="name" :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx' ,'font-weight': diyComponent.fontWeight}">{{ diyComponent.field.name }}</text>
                </view>
                <view class="layout-two-content" @click="openCalendar">
                    <view class="flex-1 text-overflow-ellipsis flex justify-end "
                          :class="{'!text-[#999]' : !diyComponent.field.value && !diyComponent.defaultControl}"
                          :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx'}"
                          @click="startDatetime = true">{{ startTime }}</view>
                    <text class="nc-iconfont !text-[#666] !text-[36rpx] nc-icon-youV6xx -mr-[8rpx]"></text>
                </view>
            </view>
            <view class="layout-two-error-message" v-if="errorInfo && !errorInfo.code">{{ errorInfo.message }}</view>
            <view v-if="diyComponent.field.remark.text" class="layout-two-remark" :style="{ color: diyComponent.field.remark.color, fontSize: (diyComponent.field.remark.fontSize * 2 ) + 'rpx' }">{{ diyComponent.field.remark.text }}</view>
            <view class="layout-two-attribute-wrap" v-if="inputAttribute().length">
                <view v-for="(item,index) in inputAttribute()" :key="index" @click="eventFn(item.type)" class="layout-two-attribute-item">{{ item.title }}</view>
            </view>
        </view>
        <u-datetime-picker :show="startDatetime" v-model="diyComponent.field.value" mode="time"
                           @cancel="startDatetime=false" @close="startDatetime=false" @confirm="startTimeConfirm"
                           closeOnClickOverlay="true"></u-datetime-picker>
        <!-- 遮罩层，装修使用 -->
        <view v-if="diyStore.mode == 'decorate'" class="form-item-mask"></view>
    </view>
</template>

<script setup lang="ts">
// 表单 时间范围组件
import { ref, computed, watch, onMounted } from 'vue';
import { img, timeStampTurnTime } from '@/utils/common';
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
    let time = '';
    if (diyComponent.value.field.value) {
        time = diyComponent.value.field.value;
        diyComponent.value.field.value = time;
    } else {
        if (diyComponent.value.defaultControl) {
            if (diyComponent.value.timeWay == 'current') {
                time = getTimeFn();
            } else if (diyComponent.value.timeWay == 'diy') {
                time = diyComponent.value.field.default;
            }

            diyComponent.value.field.value = time;
        } else {
            time = diyComponent.value.placeholder;
        }
    }
    return time;
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
                if (newValue && newValue.componentName == 'FormTime') {
                    refresh();
                }
            }
        )
    }
});

const refresh = () => {
    // todo 开始、结束时间给予默认值
    // {{ diyComponent.start.defaultControl ? diyComponent.field.value : diyComponent.start.placeholder }}
}

// 表单组件验证
const verify = () => {
    const res = { code: true, message: '' }
    if (diyComponent.value.field.required && diyComponent.value.field.value == '') {
        res.code = false
        res.message = `请选择${ diyComponent.value.placeholder }`;
    }
    errorInfo.value = res;
    return res;
}

// 重置表单组件数据
const reset = () => {
    diyComponent.value.field.value = '';
}

const openCalendar = () => {
    if (diyStore.mode === 'decorate') return;
    calendarShow.value = true;
}

const startTimeConfirm = (e) => {
    diyComponent.value.field.value = e.value;
    startDatetime.value = false;
}

const confirm = (e) => {
    calendarShow.value = false;
}
const getTimeFn = (data: any = '') => {
    let now = data ? new Date(data) : new Date();

    let str = '';
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    str = `${ hours }:${ minutes }`;
    return str;
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

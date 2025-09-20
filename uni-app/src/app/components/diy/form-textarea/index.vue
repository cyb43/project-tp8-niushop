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
                <view>{{ diyComponent.field.name }}</view>
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
                <text class="text-overflow-ellipsis" :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx' ,'font-weight': diyComponent.fontWeight}">{{ diyComponent.field.name }}</text>
                <text class="required">{{ diyComponent.field.required ? '*' : '' }}</text>
                <text v-if="diyStore.mode == 'decorate' && diyComponent.isHidden" class="is-hidden">{{ t('diyForm.hidden') }}</text>
            </view>
            <view v-if="diyComponent.field.remark.text" class="layout-one-remark" :style="{ color: diyComponent.field.remark.color, fontSize: (diyComponent.field.remark.fontSize * 2 ) + 'rpx' }">{{ diyComponent.field.remark.text }}</view>
            <view class="layout-one-error-message" v-if="errorInfo && !errorInfo.code">{{ errorInfo.message }}</view>
            <view class="layout-one-content !py-[20rpx] !h-[auto]">
                <textarea class="w-[100%]" :placeholder="inputPlaceholder"
                          placeholderClass="layout-one-input-placeholder"
                          :placeholder-style="{'font-size': (diyComponent.fontSize * 2) + 'rpx'}"
                          :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx', 'height': textareaHeight}"
                          v-model="diyComponent.field.value" :disabled="isDisabled" @linechange="linechangeFn"
                          maxlength="500" />
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
                <viwe class="layout-two-content">
                    <textarea class="w-[100%]" :placeholder="inputPlaceholder"
                              placeholderClass="layout-one-input-placeholder"
                              :placeholder-style="{'font-size': (diyComponent.fontSize * 2) + 'rpx' }"
                              :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx', 'height': textareaHeight}"
                              v-model="diyComponent.field.value" :disabled="isDisabled" @linechange="linechangeFn"
                              maxlength="500" />
                </viwe>
            </view>
            <view class="layout-two-error-message" v-if="errorInfo && !errorInfo.code">{{ errorInfo.message }}</view>
            <view v-if="diyComponent.field.remark.text" class="layout-two-remark" :style="{ color: diyComponent.field.remark.color, fontSize: (diyComponent.field.remark.fontSize * 2 ) + 'rpx' }">{{ diyComponent.field.remark.text }}</view>
            <view class="layout-two-attribute-wrap" v-if="inputAttribute().length">
                <view v-for="(item,index) in inputAttribute()" :key="index" @click="eventFn(item.type)" class="layout-two-attribute-item">{{ item.title }}</view>
            </view>
        </view>
        <view v-if="diyStore.mode == 'decorate'" class="form-item-mask"></view>
        <form-privacy-pop ref="formPrivacyRef" :data="formPrivacyData" />
    </view>
</template>

<script setup lang="ts">
// 表单 多行文本组件
import { ref, computed, watch, onMounted } from 'vue';
import { img } from '@/utils/common';
import { t } from '@/locale'
import formPrivacyPop from './../form-privacy-pop/index.vue';
import useDiyStore from '@/app/stores/diy';

const props = defineProps(['component', 'index', 'global']);
const diyStore = useDiyStore();
const errorInfo: any = ref(null);
const formPrivacyRef: any = ref(null);

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

const inputPlaceholder = computed(() => {
    let str = '';
    str += diyComponent.value.placeholder
    return str;
})

const formPrivacyData = computed(() => {
    let str = `${ diyComponent.value.field.name }已开启隐私保护，提交后会部分打码，只有你自己和管理员才能查看完整信息`;
    return str;
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
    if (diyComponent.value.field.privacyProtection) {
        let obj = {
            title: '已开启隐私保护',
            type: 'privacy'
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
    if (type == 'privacy') {
        // 查看隐私
        formPrivacyRef.value.open();
    }
}

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
                if (newValue && newValue.componentName == 'FormTextarea') {
                    refresh();
                }
            }
        )
    } else {
    }
});

const refresh = () => {
    // 装修模式下，展示默认值
    if (diyStore.mode == 'decorate') {
        diyComponent.value.field.value = diyComponent.value.field.default;
    } else {
        // 实际展示，优先缓存，其次默认值
        if (diyComponent.value.field.value == '' && diyComponent.value.field.default) {
            diyComponent.value.field.value = diyComponent.value.field.default;
        }
    }
}
const textareaHeight = ref('38rpx')
const linechangeFn = (e) => {
    let height = e.detail.height / e.detail.lineCount;
    if (diyComponent.value.rowCount > e.detail.lineCount) {
        textareaHeight.value = e.detail.height ? (e.detail.height * 2 + 'rpx') : '38rpx';
    } else if (diyComponent.value.rowCount <= e.detail.lineCount) {
        textareaHeight.value = height ? (height * diyComponent.value.rowCount * 2 + 'rpx') : '38rpx';
    }
}

// 表单组件验证
const verify = () => {
    const res = { code: true, message: '' }
    if (diyComponent.value.field.required && diyComponent.value.field.value == '' && diyStore.mode != 'decorate') {
        res.code = false
        res.message = `${ inputPlaceholder.value }`;
    }
    errorInfo.value = res;
    return res;
}

// 重置表单组件数据
const reset = () => {
    diyComponent.value.field.value = '';
}

const isDisabled = computed(() => {
    return diyStore.mode == 'decorate';
});

defineExpose({
    verify,
    reset
})
</script>

<style lang="scss" scoped>
@import '@/styles/diy_form.scss';
</style>

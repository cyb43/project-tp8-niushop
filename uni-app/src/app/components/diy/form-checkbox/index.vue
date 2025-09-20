<template>
    <view v-if="diyComponent.viewFormDetail" class="form-item-frame">
        <view class="base-layout-one" v-if="diyGlobal.completeLayout == 'style-1'">
            <view class="detail-one-content">
                <view class="detail-one-content-label">{{ diyComponent.field.name }}</view>
                <view class="flex detail-one-content-value">
                    <view v-for="(item,index) in diyComponent.field.value" :key="index">{{ item.text }}
                        <text v-if="index !== diyComponent.field.value.length - 1">、</text>
                    </view>
                </view>
            </view>
        </view>
        <view class="base-layout-two" v-if="diyGlobal.completeLayout == 'style-2'">
            <view class="detail-two-content">
                <view class="detail-two-content-label">{{ diyComponent.field.name }}</view>
                <view class="detail-two-content-value flex w-[80%] justify-end">
                    <view v-for="(item,index) in diyComponent.field.value" :key="index">
                        <text>{{ item.text }}</text>
                        <text v-if="index !== diyComponent.field.value.length - 1">、</text>
                    </view>
                    <text v-if="!diyComponent.field.value || !diyComponent.field.value.length">{{ t('notHave') }}</text>
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
            <view class="layout-one-content" v-if="diyComponent.style == 'style-1'">
                <u-checkbox-group v-model="selectValue" @change="checkboxChange" iconPlacement="left">
                    <view v-for="(item, index) in diyComponent.options" :key="index" class="mr-[40rpx]">
                        <u-checkbox activeColor="var(--primary-color)" :labelSize="(diyComponent.fontSize * 2) + 'rpx'" :labelColor="diyComponent.textColor" :label="item.text" :name="item.id"></u-checkbox>
                    </view>
                </u-checkbox-group>
            </view>

            <u-checkbox-group v-if="diyComponent.style == 'style-2'" v-model="selectValue" @change="checkboxChange" iconPlacement="left" placement="column">
                <view v-for="(item, index) in diyComponent.options" :key="index" @click="selectRadio(item)"
                      class="layout-one-content mb-[16rpx]"
                      :class="{'!mb-[0]': (diyComponent.options.length-1) == index}">
                    <u-checkbox class="!m-[0]" activeColor="var(--primary-color)" :labelSize="(diyComponent.fontSize * 2) + 'rpx'" :labelColor="diyComponent.textColor" :label="item.text" :name="item.id"></u-checkbox>
                </view>
            </u-checkbox-group>

            <view v-if="diyComponent.style == 'style-3'" @click="openPicker" class="layout-one-content justify-between">
                <view v-if="diyComponent.field.value && diyComponent.field.value.length">
                    <text class="mr-[10rpx] text-[28rpx]" :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx'}" v-for="(item,index) in diyComponent.field.value">{{ item.text }}<text v-if="index !== diyComponent.field.value.length - 1">,</text>
                    </text>
                </view>
                <text v-else class="text-[28rpx] text-[#999]" :style="{'font-size': (diyComponent.fontSize * 2) + 'rpx'}">{{ checkboxPlaceholder }}</text>
                <text class="nc-iconfont nc-icon-xiaV6xx pull-down-arrow text-[#666]" :class="{'selected': selectShow}" :style="{'font-size': (diyComponent.fontSize * 2+2) + 'rpx !important'}"></text>
            </view>

            <view class="layout-one-attribute-wrap" v-if="inputAttribute().length">
                <view v-for="(item,index) in inputAttribute()" :key="index" @click="eventFn(item.type)" class="layout-one-attribute-item">{{ item.title }}</view>
            </view>
        </view>
        <view class="base-layout-two" v-if="diyGlobal.completeLayout == 'style-2'">
            <text v-if="diyStore.mode == 'decorate' && diyComponent.isHidden" class="layout-two-is-hidden">{{ t('diyForm.hidden') }}</text>
            <view class="layout-two-wrap" :class="{'!pb-[20rpx]': ((diyComponent.style == 'style-2' || diyComponent.style == 'style-3') && diyGlobal.borderControl),'no-border': !diyGlobal.borderControl}">
                <view class="layout-two-label"
                      :class="{'justify-start': diyGlobal.completeAlign == 'left', 'justify-end': diyGlobal.completeAlign == 'right'}">
                    <text class="required" v-if="diyComponent.field.required">{{ diyComponent.field.required ? '*' : '' }}</text>
                    <text class="name" :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx' ,'font-weight': diyComponent.fontWeight}">{{ diyComponent.field.name }}</text>
                </view>
                <view class="layout-two-content" v-if="diyComponent.style == 'style-1'">
                    <u-checkbox-group v-model="selectValue" @change="checkboxChange" iconPlacement="left" class="justify-end">
                        <view v-for="(item, index) in diyComponent.options" class="ml-[30rpx]">
                            <u-checkbox activeColor="var(--primary-color)" :labelSize="(diyComponent.fontSize * 2) + 'rpx'" :labelColor="diyComponent.textColor" :key="index" :label="item.text" :name="item.id"></u-checkbox>
                        </view>
                    </u-checkbox-group>
                </view>

                <view class="layout-two-content" v-if="diyComponent.style == 'style-2'">
                    <view class="justify-end w-full">
                        <u-checkbox-group v-model="selectValue" placement="column" @change="checkboxChange" iconPlacement="left">
                            <view v-for="(item, index) in diyComponent.options" :key="index" @click="selectRadio(item)"
                                  class="border-solid border-[2rpx] border-[#e6e6e6] rounded-[10rpx] flex items-center h-[80rpx] mb-[16rpx] px-[16rpx] box-border"
                                  :class="{'mb-[0]': diyComponent.options.length == (index+1)}">
                                <u-checkbox activeColor="var(--primary-color)"
                                            :labelSize="(diyComponent.fontSize * 2) + 'rpx'"
                                            :labelColor="diyComponent.textColor" class="!m-[0]" :label="item.text"
                                            :name="item.id"></u-checkbox>
                            </view>
                        </u-checkbox-group>
                    </view>
                </view>

                <view class="layout-two-content" v-if="diyComponent.style == 'style-3'">
                    <view @click="openPicker" class="px-[16rpx] box-border h-[80rpx] flex items-center justify-between border-solid border-[2rpx] border-[#e6e6e6] rounded-[10rpx] w-[100%]">
                        <view v-if="diyComponent.field.value && diyComponent.field.value.length">
                            <text class="mr-[10rpx] text-[28rpx]"
                                  :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx'}"
                                  v-for="(item,index) in diyComponent.field.value">{{ item.text }}
                                <text v-if="index !== diyComponent.field.value.length - 1">,</text>
                            </text>
                        </view>
                        <text v-else class="text-[28rpx] text-[#999]" :style="{'font-size': (diyComponent.fontSize * 2) + 'rpx'}">{{ checkboxPlaceholder }}</text>
                        <text class="nc-iconfont nc-icon-xiaV6xx pull-down-arrow text-[#666]" :class="{'selected': selectShow}" :style="{'font-size': (diyComponent.fontSize * 2+2) + 'rpx !important'}"></text>
                    </view>
                </view>
            </view>
            <view class="layout-two-error-message" v-if="errorInfo && !errorInfo.code">{{ errorInfo.message }}</view>
            <view v-if="diyComponent.field.remark.text" class="layout-two-remark" :style="{ color: diyComponent.field.remark.color, fontSize: (diyComponent.field.remark.fontSize * 2 ) + 'rpx' }">{{ diyComponent.field.remark.text }}</view>
            <view class="layout-two-attribute-wrap" v-if="inputAttribute().length">
                <view v-for="(item,index) in inputAttribute()" :key="index" @click="eventFn(item.type)" class="layout-two-attribute-item">{{ item.title }}</view>
            </view>
        </view>
        <!-- 样式三，下拉弹窗 -->
        <u-popup :show="selectShow" mode="bottom" @close="selectShow=false">
            <view class="p-[15rpx]">
                <scroll-view scroll-y="true" class="max-h-[450rpx] px-[14rpx] box-border">
                    <u-checkbox-group v-model="pullDownVal" placement="column" iconPlacement="right">
                        <view v-for="(item, index) in diyComponent.options" :key="index" class="border-solid border-[0] border-b-[2rpx] border-[#e6e6e6] py-[20rpx]">
                            <u-checkbox activeColor="var(--primary-color)" :labelSize="'30rpx'" labelColor="#333" :style="{'width': '100%'}" :label="item.text" :name="item.id"></u-checkbox>
                        </view>
                    </u-checkbox-group>
                </scroll-view>

                <view class="flex items-center pt-[20rpx]">
                    <view @click="pullDownCancelFn" class="flex-1 flex justify-center h-[70rpx] leading-[70rpx] text-[#333] bg-[#eee] text-[26rpx] border-[0] font-500 rounded-[10rpx] mr-[20rpx]">{{ t('cancel') }}</view>
                    <view @click="pullDownConfirmFn" class="flex-1 flex justify-center bg-[var(--primary-color)] h-[70rpx] leading-[70rpx] text-[#fff] text-[26rpx] border-[0] rounded-[10rpx]">{{ t('confirm') }}</view>
                </view>
            </view>
        </u-popup>
        <view v-if="diyStore.mode == 'decorate'" class="form-item-mask"></view>
        <!-- 隐私弹窗 -->
        <form-privacy-pop ref="formPrivacyRef" :data="formPrivacyData" />
    </view>
</template>

<script setup lang="ts">
// 表单 多选项组件
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { img } from '@/utils/common';
import { t } from '@/locale'
import useDiyStore from '@/app/stores/diy';
import formPrivacyPop from './../form-privacy-pop/index.vue';

const props = defineProps(['component', 'index', 'global']);
const diyStore = useDiyStore();
const selectValue = ref([])
const selectShow = ref(false);
const pullDownVal = ref([])

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
                if (newValue && newValue.componentName == 'FormCheckbox') {
                    refresh();
                }
            }
        )
    } else {
        watch(
            () => diyComponent.value.field.value,
            (newValue, oldValue) => {
                refresh();
            }
        )
    }
});

const refresh = () => {
    nextTick(()=>{
        if (diyComponent.value.style == 'style-3') {
            pullDownVal.value = diyComponent.value.field.value.map(item => item.id);
        }
        if (diyComponent.value.field.value?.length) {
            selectValue.value = diyComponent.value.field.value.map(item => item.id);
        }
    })
}

const checkboxPlaceholder = computed(() => {
    let str = '';
    str += `请选择${ diyComponent.value.field.name }`
    return str;
})

// 表单组件验证
const verify = () => {
    const res = { code: true, message: '' }
    if (diyComponent.value.field.required && diyComponent.value.field.value.length == '' && diyStore.mode != 'decorate') {
        res.code = false
        res.message = checkboxPlaceholder.value;
    }
    errorInfo.value = res;
    return res;
}
const backupValue = ref([]);

// 重置表单组件数据
const reset = () => {
    pullDownVal.value = [];
    selectValue.value = [];
    diyComponent.value.field.value = [];
}

const pullDownCancelFn = () => {
    selectShow.value = false;
    pullDownVal.value = backupValue.value.map(item => item.id);
}

const pullDownConfirmFn = () => {
    selectShow.value = false;
    const selectedItems = diyComponent.value.options.filter(item => pullDownVal.value.includes(item.id));
    diyComponent.value.field.value = selectedItems.map(item => ({ id: item.id, text: item.text }));
}

const isDisabled = computed(() => {
    return diyStore.mode === 'decorate';
});

const openPicker = () => {
    if (isDisabled.value) {
        return
    }
    backupValue.value = [...diyComponent.value.field.value];  // 保存当前的值
    selectShow.value = true;
}

// 保存数据到 store
const checkboxChange = (value: any) => {
    const selectedItems = diyComponent.value.options.filter(item => value.includes(item.id));
    diyComponent.value.field.value = selectedItems.map(item => ({ id: item.id, text: item.text }));
};
const selectRadio = (item) => {
    if (isDisabled.value) {
        return;
    }
    const itemId = item.id;
    const index = selectValue.value.indexOf(itemId);
    if (index > -1) {
        selectValue.value.splice(index, 1);
    } else {
        selectValue.value.push(itemId);
    }
    const selectedItems = diyComponent.value.options.filter(item => selectValue.value.includes(item.id));

    diyComponent.value.field.value = selectedItems.map(item => ({ id: item.id, text: item.text }));
};

defineExpose({
    verify,
    reset
})
</script>

<style lang="scss" scoped>
@import '@/styles/diy_form.scss';

.pull-down-arrow {
    transition: all .3s;
    transform: rotate(0);

    &.selected {
        transform: rotate(180deg);
    }
}

.form-item-frame :deep(.u-checkbox .u-checkbox__icon-wrap) {
    width: 30rpx !important;
    height: 30rpx !important;
}
</style>

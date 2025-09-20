<template>
    <view v-if="diyComponent.viewFormDetail" class="form-item-frame">
        <view class="base-layout-one" v-if="diyGlobal.completeLayout == 'style-1'">
            <view class="detail-one-content">
                <text class="detail-one-content-label">{{ diyComponent.field.name }}</text>
                <text v-for="(item,index) in diyComponent.field.value" :key="index" class="detail-one-content-value">{{ item.text }}</text>
            </view>
        </view>
        <view class="base-layout-two" v-if="diyGlobal.completeLayout == 'style-2'">
            <view class="detail-two-content">
                <text class="detail-two-content-label">{{ diyComponent.field.name }}</text>
                <view class="flex items-center justify-end">
                    <view class="detail-two-content-value" v-for="(item,index) in diyComponent.field.value" :key="index">{{ item.text }}</view>
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
            <view v-if="diyComponent.style == 'style-1'" class="layout-one-content !flex-initial">
                <u-radio-group v-model="selectedRadioId" @change="groupChange" iconPlacement="left">
                    <view v-for="(item, index) in diyComponent.options" :key="index" class="mr-[40rpx]">
                        <u-radio activeColor="var(--primary-color)" :labelSize="(diyComponent.fontSize * 2) + 'rpx'" :labelColor="diyComponent.textColor" :label="item.text" :name="item.id"></u-radio>
                    </view>
                </u-radio-group>
            </view>

            <u-radio-group v-if="diyComponent.style == 'style-2'" v-model="selectedRadioId" @change="groupChange" iconPlacement="left" placement="column">
                <view v-for="(item, index) in diyComponent.options" :key="index" @click="selectRadio(item)" class="layout-one-content mb-[16rpx]" :class="{'!mb-[0]': (diyComponent.options.length-1) == index}">
                    <u-radio activeColor="var(--primary-color)" :labelSize="(diyComponent.fontSize * 2) + 'rpx'" :labelColor="diyComponent.textColor" class="mr-[20rpx]" :label="item.text" :name="item.id"></u-radio>
                </view>
            </u-radio-group>

            <view v-if="diyComponent.style == 'style-3'" @click="openPicker" class="layout-one-content justify-between">
                <view v-if="diyComponent.field.value.length>0">
                    <text class="mr-[10rpx] text-[28rpx]" :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx'}">{{ getSelectRadioName }}</text>
                </view>
                <text v-else class="text-[28rpx] text-[#999]" :style="{'font-size': (diyComponent.fontSize * 2) + 'rpx'}">{{ radioPlaceholder }}</text>
                <text class="nc-iconfont nc-icon-xiaV6xx pull-down-arrow text-[#666]" :class="{'selected': selectShow}" :style="{'font-size': (diyComponent.fontSize * 2+2) + 'rpx !important'}"></text>
            </view>

            <view class="layout-one-attribute-wrap" v-if="inputAttribute().length">
                <view v-for="(item,index) in inputAttribute()" :key="index" @click="eventFn(item.type)" class="layout-one-attribute-item">{{ item.title }}</view>
            </view>
        </view>
        <view class="base-layout-two" v-if="diyGlobal.completeLayout == 'style-2'">
            <text v-if="diyStore.mode == 'decorate' && diyComponent.isHidden" class="layout-two-is-hidden">{{ t('diyForm.hidden') }}</text>
            <view class="layout-two-wrap" :class="{'!pb-[20rpx]': ((diyComponent.style == 'style-2' || diyComponent.style == 'style-3') && diyGlobal.borderControl),'no-border': !diyGlobal.borderControl}">
                <view class="layout-two-label" :class="{'justify-start': diyGlobal.completeAlign == 'left', 'justify-end': diyGlobal.completeAlign == 'right'}"><text class="required">{{ diyComponent.field.required ? '*' : '' }}</text>
                    <text class="name" :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx' ,'font-weight': diyComponent.fontWeight}">{{ diyComponent.field.name }}</text>
                </view>
                <view class="layout-two-content" v-if="diyComponent.style == 'style-1'">
                    <view class="justify-end">
                        <u-radio-group v-model="selectedRadioId" @change="groupChange" iconPlacement="left">
                            <view v-for="(item, index) in diyComponent.options" :key="index" class="ml-[30rpx]">
                                <u-radio activeColor="var(--primary-color)"
                                         :labelSize="(diyComponent.fontSize * 2) + 'rpx'"
                                         :labelColor="diyComponent.textColor" :label="item.text"
                                         :name="item.id"></u-radio>
                            </view>
                        </u-radio-group>
                    </view>
                </view>

                <view class="layout-two-content" v-if="diyComponent.style == 'style-2'">
                    <view class="justify-end w-full">
                        <u-radio-group v-model="selectedRadioId" @change="groupChange" placement="column" iconPlacement="left">
                            <view v-for="(item, index) in diyComponent.options" :key="index" @click="selectRadio(item)"
                                  class="border-solid border-[2rpx] border-[#e6e6e6] rounded-[10rpx] flex items-center h-[80rpx] mb-[16rpx] px-[16rpx] box-border"
                                  :class="{'mb-[0]': diyComponent.options.length == (index+1)}">
                                <u-radio activeColor="var(--primary-color)"
                                         :labelSize="(diyComponent.fontSize * 2) + 'rpx'"
                                         :labelColor="diyComponent.textColor" class="!m-[0]" :label="item.text"
                                         :name="item.id"></u-radio>
                            </view>
                        </u-radio-group>
                    </view>
                </view>

                <view class="layout-two-content" v-if="diyComponent.style == 'style-3'">
                    <view @click="openPicker" class="px-[16rpx] box-border h-[80rpx] flex items-center justify-between border-solid border-[2rpx] border-[#e6e6e6] rounded-[10rpx] w-[100%]">
                        <view v-if="diyComponent.field.value.length>0">
                            <text class="mr-[10rpx] text-[28rpx]" :style="{'color': diyComponent.textColor,'font-size': (diyComponent.fontSize * 2) + 'rpx'}">{{ getSelectRadioName }}</text>
                        </view>
                        <text v-else class="text-[28rpx] text-[#999]" :style="{'font-size': (diyComponent.fontSize * 2) + 'rpx'}">{{ radioPlaceholder }}</text>
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
                    <u-radio-group v-model="pullDownVal" placement="column" @change="groupChange" iconPlacement="right">
                        <view class="border-solid border-[0] border-b-[2rpx] border-[#e6e6e6] py-[20rpx]" @click.stop="pullDownConfirmFn(item)" v-for="(item, index) in diyComponent.options" :key="index">
                            <u-radio activeColor="var(--primary-color)" :labelSize="(diyComponent.fontSize * 2) + 'rpx'" :labelColor="diyComponent.textColor" :style="{'width': '100%'}" :label="item.text" :name="item.id"></u-radio>
                        </view>
                    </u-radio-group>
                </scroll-view>
            </view>
        </u-popup>
        <view v-if="diyStore.mode == 'decorate'" class="form-item-mask"></view>
        <!-- 隐私弹窗 -->
        <form-privacy-pop ref="formPrivacyRef" :data="formPrivacyData" />
    </view>
</template>

<script setup lang="ts">
// 表单 单选项组件
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
const selectedRadioId = ref('');  // 存储选中的 id

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
                if (newValue && newValue.componentName == 'FormRadio') {
                    refresh();
                }
            }
        )
    }else{
        watch(
            () => diyComponent.value.field.value,
            (newValue, oldValue) => {
                refresh();
            }
        )
    }

});

const refresh = () => {
    nextTick(() => {
       // 样式三，初始化下拉数据
        if (diyComponent.value.style == 'style-3' && diyComponent.value.field.value.length > 0) {
            pullDownVal.value = diyComponent.value.field.value[0].id;
        }
        if (diyComponent.value.field.value.length > 0) {
            selectedRadioId.value = diyComponent.value.field.value[0].id;
        } 
    })
}

const radioPlaceholder = computed(() => {
    let str = '';
    str += `请选择${ diyComponent.value.field.name }`
    return str;
})

// 表单组件验证
const verify = () => {
    const res = { code: true, message: '' }
    if (diyComponent.value.field.required && diyComponent.value.field.value.length == '' && diyStore.mode != 'decorate') {
        res.code = false
        res.message = radioPlaceholder.value;
    }
    errorInfo.value = res;
    return res;
}

// 重置表单组件数据
const reset = () => {
    selectedRadioId.value = '';
    pullDownVal.value = [];
    selectValue.value = [];
    diyComponent.value.field.value = [];
}

const pullDownCancelFn = () => {
    selectShow.value = false;
    // pullDownVal.value = diyComponent.value.field.value;
}

const pullDownConfirmFn = (item: any) => {
    selectShow.value = false;
    pullDownVal.value = item.id;

    diyComponent.value.field.value = [{ id: item.id, text: item.text }];
    // diyComponent.value.field.value = pullDownVal.value;
}

// 获取选中的单选项名称
const getSelectRadioName = computed(() => {
    let names = [];
    diyComponent.value.field.value.forEach((selectedItem) => {
        const item = diyComponent.value.options.find(option => option.id === selectedItem.id);
        if (item) {
            names.push(item.text);
        }
    });
    return names.join(', ');
});


const isDisabled = computed(() => {
    return diyStore.mode === 'decorate';
});

const openPicker = () => {
    if (isDisabled.value) {
        return
    }
    selectShow.value = true;
}

const groupChange = (value) => {
    selectedRadioId.value = value;
    const selectedItem = diyComponent.value.options.find(item => item.id === value);
    diyComponent.value.field.value = [{ id: selectedItem.id, text: selectedItem.text }];
    selectShow.value = false;
};
const selectRadio = (item) => {
    if (isDisabled.value) {
        return;
    }
    selectedRadioId.value = item.id;

    diyComponent.value.field.value = [{ id: item.id, text: item.text }];
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

/* 覆盖 u-radio 样式 */
// ::v-deep(.u-radio) {
//     border-bottom: 2rpx solid #e6e6e6;
// 	height: 90rpx;
// }
.form-item-frame :deep(.u-radio-group .u-radio__icon-wrap) {
    width: 30rpx !important;
    height: 30rpx !important;
}
</style>

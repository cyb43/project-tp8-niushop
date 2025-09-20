<template>
    <view>
        <view class="overflow-hidden">
            <view :style="maskLayer"></view>
            <view class="relative submit-wrap z-10" :style="warpCss">
                <view class="flex flex-col items-center" :style="boxCss">
                    <view class="w-[100%] h-[86rpx] text-[28rpx] flex items-center justify-center" @click="submit" :style="submitItem">{{ diyComponent.submitBtn.text }}</view>
                    <view v-if="diyComponent.resetBtn.control" class="w-[100%] h-[86rpx] mt-[20rpx] text-[28rpx] flex items-center justify-center" @click="reset" :style="resetItem">{{ diyComponent.resetBtn.text }}</view>
                </view>
            </view>
        </view>
        <view v-if="diyComponent.btnPosition == 'hover_screen_bottom' && diyStore.mode != 'decorate'" class="w-[100%]" :style="submitFillHeight"></view>
        <!-- 苹果安全距离，辅助计算 -->
        <view class="iphone-secure"></view>
        <!-- 遮罩层，装修使用 -->
        <view v-if="diyStore.mode == 'decorate'" class="form-item-mask"></view>
    </view>
</template>

<script setup lang="ts">
// 表单提交组件
import { ref, computed, watch, onMounted, nextTick, getCurrentInstance } from 'vue';
import useDiyStore from '@/app/stores/diy';
import { img, redirect, getValidTime, deepClone, getToken } from '@/utils/common';
import { useLogin } from "@/hooks/useLogin";
import { addFormRecord, editFormRecord } from '@/app/api/diy_form';

const props = defineProps(['component', 'index', 'global']);
const diyStore = useDiyStore();
const tabbarInfo = ref();

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

    if (diyStore.mode != 'decorate' && diyComponent.value.btnPosition == 'hover_screen_bottom') {
        style += "position: fixed !important;";
        const height = tabbarInfo.value ? tabbarInfo.value.height : 0;
        style += `left: 0;`;
        style += `right: 0;`;
        if (height && diyGlobal.value.bottomTabBar && diyGlobal.value.bottomTabBar.isShow) {
            style += `bottom: ${ height }px;`;
        } else {
            style += `bottom: 0;`;
        }

        if (diyComponent.value.pageStartBgColor) {
            if (diyComponent.value.pageStartBgColor && diyComponent.value.pageEndBgColor) style += `background:linear-gradient(${ diyComponent.value.pageGradientAngle },${ diyComponent.value.pageStartBgColor },${ diyComponent.value.pageEndBgColor });`;
            else if (diyComponent.value.pageStartBgColor) style += `background: ${ diyComponent.value.pageStartBgColor };`;
            else if (diyComponent.value.pageEndBgColor) style += `background: ${ diyComponent.value.pageEndBgColor };`;
        }

        if (diyComponent.value.margin) {
            if (diyComponent.value.margin.top > 0) {
                style += 'padding-top:' + diyComponent.value.margin.top * 2 + 'rpx' + ';';
            }
            if (height && diyGlobal.value.bottomTabBar && diyGlobal.value.bottomTabBar.isShow) {
                style += 'padding-bottom:' + diyComponent.value.margin.bottom * 2 + 'rpx' + ';';
            } else {
                style += `padding-bottom: ${ (diyComponent.value.margin.bottom + iphoneSecureVal.value) * 2 }rpx;`;
            }

            style += 'padding-right:' + diyComponent.value.margin.both * 2 + 'rpx' + ';';
            style += 'padding-left:' + diyComponent.value.margin.both * 2 + 'rpx' + ';';
        }
    } else {
        if (diyComponent.value.btnPosition == 'hover_screen_bottom') {
            style += "position: fixed !important;";
            style += `left: 0;`;
            style += `right: 0;`;
            style += `bottom: 0;`;
        }
    }
    return style;
})

const boxCss = computed(() => {
    let style = '';

    if (diyComponent.value.btnPosition == 'hover_screen_bottom') {
        if (diyComponent.value.componentStartBgColor && diyComponent.value.componentEndBgColor) style += `background:linear-gradient(${ diyComponent.value.componentGradientAngle },${ diyComponent.value.componentStartBgColor },${ diyComponent.value.componentEndBgColor });`;
        else if (diyComponent.value.componentStartBgColor) style += 'background-color:' + diyComponent.value.componentStartBgColor + ';';
        else if (diyComponent.value.componentEndBgColor) style += 'background-color:' + diyComponent.value.componentEndBgColor + ';';
    }
    return style;
})

// 背景图加遮罩层
const maskLayer = computed(() => {
    let style = '';
    if (diyComponent.value.componentBgUrl) {
        style += 'position:absolute;top:0;right:0;left:0;bottom:0;';
        style += `background: rgba(0,0,0,${ diyComponent.value.componentBgAlpha / 10 });`;

        if (diyComponent.value.topRounded) style += 'border-top-left-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
        if (diyComponent.value.topRounded) style += 'border-top-right-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
        if (diyComponent.value.bottomRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
        if (diyComponent.value.bottomRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    }

    return style;
});

// 重置样式
const resetItem = computed(() => {
    let style = '';
    style += `color: ${ diyComponent.value.resetBtn.color };`;
    style += `background-color: ${ diyComponent.value.resetBtn.bgColor };`;
    if (diyComponent.value.topElementRounded) style += 'border-top-left-radius:' + diyComponent.value.topElementRounded * 2 + 'rpx;';
    if (diyComponent.value.topElementRounded) style += 'border-top-right-radius:' + diyComponent.value.topElementRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomElementRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomElementRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomElementRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomElementRounded * 2 + 'rpx;';
    return style;
})

// 保存样式
const submitItem = computed(() => {
    let style = '';
    style += `color: ${ diyComponent.value.submitBtn.color };`;
    style += `background-color: ${ diyComponent.value.submitBtn.bgColor };`;
    if (diyComponent.value.topElementRounded) style += 'border-top-left-radius:' + diyComponent.value.topElementRounded * 2 + 'rpx;';
    if (diyComponent.value.topElementRounded) style += 'border-top-right-radius:' + diyComponent.value.topElementRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomElementRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomElementRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomElementRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomElementRounded * 2 + 'rpx;';
    return style;
})

// 提交按钮，悬浮时的填充
const submitFillHeight = computed(() => {
    let style = '';
    style += `padding-bottom: ${ submitHeight.value }px;`;
    return style;
})

const formComponent: any = ref([])

onMounted(() => {
    refresh();
    // 装修模式下刷新
    if (diyStore.mode == 'decorate') {
        watch(
            () => diyComponent.value,
            (newValue, oldValue) => {
                if (newValue && newValue.componentName == 'FormSubmit') {
                    refresh();
                }
            }
        )
    } else {
        getFormComponent();
        watch(
            () => diyStore.value,
            (newValue, oldValue) => {
                if (newValue) {
                    let formData = getFormSComponentsData(newValue);
                    if (formData.components.length) {
                        uni.setStorageSync('diyFormStorage_' + diyStore.id, formData)
                    }
                }
            },
            { deep: true }
        )
    }
    nextTick(() => {
        tabbarInfo.value = uni.getStorageSync('tabbarInfo')
    })
});

const refresh = () => {
}

const getFormComponent = () => {
    // 需要过滤 组件类型，筛选出来表单
    for (let i = 0; i < diyStore.value.length; i++) {
        let item = diyStore.value[i];
        // 筛选出来表单组件，排除表单提交组件
        if (item.componentType == 'diy_form' && item.componentName != 'FormSubmit') {
            formComponent.value.push(item);
        }
    }
}

const repeat = ref(false)
let currPage: any = getCurrentPages()[getCurrentPages().length - 1];
const submit = () => {
    if (diyStore.mode === 'decorate') return

    let allPass = true; // 是否全部通过验证

    // 需要过滤 组件类型，筛选出来表单
    for (let i = 0; i < formComponent.value.length; i++) {
        let item = formComponent.value[i];
        if (item.field.required || item.field.value) {
            let refKey = `diy${ item.componentName }Ref`;
            let isBreak = false;
            if (diyStore.componentRefs[refKey]) {
                for (let k = 0; k < diyStore.componentRefs[refKey].length; k++) {
                    let compRef = diyStore.componentRefs[refKey][k];
                    let verify = compRef.verify(); // 验证表单组件数据
                    if (verify && !verify.code) {
                        isBreak = true;
                        uni.showToast({
                            title: verify.message,
                            icon: 'none'
                        });
                        break;
                    }

                }
                if (isBreak) {
                    allPass = false;
                    break;
                }
            }
        }

    }

    //  填写万能表单需要检测登录
    if (!getToken()) {
        useLogin().setLoginBack({
            url: '/app/pages/index/diy_form',
            param: {
                form_id: diyStore.id
            }
        })
        return;
    }

    if (!allPass) return;

    if (repeat.value) return
    repeat.value = true

    let diyFormStorage = uni.getStorageSync('diyFormStorage_' + diyStore.id)
    let value = {};
    if (diyFormStorage) {
        value = diyFormStorage.components
    } else {
        value = getFormSComponentsData(formComponent.value).components;
    }

    const data = {
        form_id: diyStore.id,
        value,
        relate_id: '' // todo 关联业务id，需要考虑如何传入
    }

    let api = addFormRecord;
    if(uni.getStorageSync('personalFormRecordId')){
        api = editFormRecord;
        data.record_id = uni.getStorageSync('personalFormRecordId');
    }else{
        data.record_id = '';
    }

    api(data).then((res: any) => {
        uni.removeStorageSync('diyFormStorage_' + diyStore.id)
        uni.removeStorageSync('personalFormRecordId')
        if (currPage.route == 'app/pages/member/personal_form') {
            // 个人资料
            redirect({
                url: '/app/pages/member/personal',
                mode: 'redirectTo'
            })
        }else {
            // 跳转到 表单提交结果页面
            redirect({
                url: '/app/pages/index/diy_form_result',
                param: { record_id: res.data, form_id: diyStore.id },
                mode: 'redirectTo'
            })
        }
        repeat.value = false
    }).catch(() => {
        repeat.value = false
    })
};

// 重置表单组件数据
const reset = () => {
    // 需要过滤 组件类型，筛选出来表单
    for (let i = 0; i < formComponent.value.length; i++) {
        let item = formComponent.value[i];
        let refKey = `diy${ item.componentName }Ref`;
        if (diyStore.componentRefs[refKey]) {
            diyStore.componentRefs[refKey].forEach((compRef: any) => {
                if (compRef.reset) compRef.reset(item);
            })
        }
    }
    return;
}

// 获取表单组件数据
const getFormSComponentsData = (data: any) => {
    let formData: any = {
        validTime: getValidTime(5), // 缓存数据有效期为5分钟
        components: []
    };
    data.forEach((item: any) => {
        // 只存表单组件 -- 用于直接保存
        if (item.componentType == 'diy_form' && item.componentName != 'FormSubmit' && item.field.cache) {
            // 只存储表单数据，压缩存储空间
            let field = deepClone(item.field);
            // 移除不需要存储的数据
            delete field.remark; // 字段说明
            delete field.detailComponent; // 用于详情展示
            delete field.default; // 默认值
            formData.components.push({
                id: item.id,
                componentName: item.componentName,
                componentType: item.componentType,
                componentTitle: item.componentTitle,
                isHidden: item.isHidden,
                field: field
            })
        }
    });
    return formData;
}

let submitHeight = ref(0);
const instance = getCurrentInstance();
let iphoneSecureVal = ref(0);
nextTick(() => {
    const query = uni.createSelectorQuery().in(instance);
    query.select('.iphone-secure').boundingClientRect((data: any) => {
        iphoneSecureVal.value = data ? data.height : 0;
    }).exec();

    setTimeout(() => {
        query.select('.submit-wrap').boundingClientRect((data: any) => {
            submitHeight.value = data ? data.height : 0;
        }).exec();
    }, 500)
})
</script>

<style lang="scss" scoped>
@import '@/styles/diy_form.scss';

.iphone-secure {
    position: absolute;
    bottom: 0;
    z-index: -1;
    padding-bottom: constant(safe-area-inset-bottom);
    padding-bottom: env(safe-area-inset-bottom);
    left: 0;
    right: 0;
}
</style>

<template>
    <view :style="themeColor()">
        <!-- 自定义组件渲染 -->
        <view v-show="requestData.status == 1 && requestData.error && requestData.error.length === 0 && !diy.getLoading()" class="diy-template-wrap">
            <diy-group ref="diyGroupRef" :data="diyFormData"/>
        </view>
        <!-- 目前只有自定义表单才展示错误信息，其他类型暂时不控制 -->
        <view class="flex flex-col" v-if="requestData.error && requestData.error.length > 0 && diy.data == 'DIY_FORM'">
            <view class="flex-1 flex flex-col items-center pt-[20rpx]" v-for="(item, index) in requestData.error.slice(0, 1)" :key="index">
                <text class="nc-iconfont nc-icon-tanhaoV6mm text-[#ccc] mb-[30rpx] !text-[100rpx]"></text>
                <view class="text-[38rpx] font-bold mt-3">{{ item.title }}</view>
                <view class="p-[30rpx] mt-[20rpx] w-full">
                    <view class="flex w-full">
                        <view class="w-[30%] text-[#999] text-left">{{ item.type }}</view>
                        <view class="w-[70%] text-left">{{ item.desc }}</view>
                    </view>
                </view>
            </view>
        </view>
    </view>
</template>
<script lang="ts" setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useDiyForm } from '@/hooks/useDiyForm'
import { deepClone, getValidTime } from '@/utils/common'
import diyGroup from '@/addon/components/diy/group/index.vue'

const props = defineProps(['form_id', 'relate_id', 'storage_name', 'form_border']);

const diy = useDiyForm({
    form_id: props.form_id,
    needLogin: false // 不检测登录，调用业务自行处理
})

const diyGroupRef = ref(null)

const requestData = computed(() => {
    return diy.requestData;
})

const diyFormData: any = reactive({})

onMounted(() => {
    diy.getData(() => {
        diyFormData.status = diy.data.status;
        if (diyFormData.status && requestData.value.error.length == 0) {
            diyFormData.title = diy.data.title;
            diyFormData.global = diy.data.global;
            if (diyFormData.global) {
                diyFormData.global.topStatusBar.isShow = false; // 顶部导航栏强制隐藏
                diyFormData.global.bottomTabBar.isShow = false; // 底部导航强制隐藏
            }
            let value: any = [];
            if (props.form_border == 'none') {
                diyFormData.global.borderControl = false;
            }
            // 需要过滤 组件类型，筛选出来表单，排除表单提交组件
            diy.data.value.forEach((item: any) => {
                if (item.componentType == 'diy_form' && item.componentName != 'FormSubmit') {
                    value.push(item);
                }
            })
            diyFormData.value = deepClone(value);
            diyFormData.componentRefs = null;
            diyGroupRef.value?.refresh();
            watchFormData();
        }
    })
})

const watchFormData = () => {
    watch(
        () => diyFormData.value,
        (newValue, oldValue) => {
            if (newValue) {
                let formData: any = {
                    validTime: getValidTime(5), // 缓存数据有效期为5分钟
                    components: []
                };
                newValue.forEach((item: any) => {
                    // 只存表单组件
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
                })
                if (formData.components.length) {
                    uni.setStorageSync('diyFormStorage_' + props.form_id, formData)
                }
            }
        },
        { deep: true }
    )
}

const verify = () => {
    if (!diyFormData.status) return true;
    if (!diyFormData.value) return true;
    if (!requestData.value || requestData.value.error?.length > 0) return true;
    let allPass = true; // 是否全部通过验证

    let componentRefs = diyGroupRef.value.getFormRef().componentRefs;

    // 需要过滤 组件类型，筛选出来表单
    for (let i = 0; i < diyFormData.value.length; i++) {
        let item = diyFormData.value[i];
        if (item.field.required || item.field.value) {
            let refKey = `diy${ item.componentName }Ref`;
            let isBreak = false;
            if (componentRefs[refKey]) {
                for (let k = 0; k < componentRefs[refKey].length; k++) {
                    let compRef = componentRefs[refKey][k];
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

    if (!allPass) return false;

    const data = {
        form_id: props.form_id,
        value: uni.getStorageSync('diyFormStorage_' + props.form_id),
        relate_id: props.relate_id || 0 // 关联业务id
    }
    if (props.storage_name) {
        uni.setStorageSync(props.storage_name, data)
    }
    return allPass;
}

// 获取数据
const getData = () => {
    return {
        form_id: props.form_id,
        value: diyFormData.value,
        relate_id: props.relate_id || 0 // 关联业务id
    }
}

const clearStorage = (keys: any = []) => {
    uni.removeStorageSync('diyFormStorage_' + props.form_id)
    if (props.storage_name) uni.removeStorageSync(props.storage_name)
    if (keys) {
        keys.forEach((key: any) => {
            uni.removeStorageSync(key)
        })
    }
}

// 监听页面隐藏
diy.onHide();

// 监听页面卸载
diy.onUnload();

// 监听滚动事件
// diy.onPageScroll()

defineExpose({
    verify,
    getData,
    clearStorage
})

</script>

<style lang="scss">
.diy-template-wrap {
    /* #ifdef MP */
    .child-diy-template-wrap {
        ::v-deep .diy-group {
            > .draggable-element.top-fixed-diy {
                display: block !important;
            }
        }
    }

    /* #endif */
}
</style>

<template>
    <view :style="themeColor()" class="my-[var(--top-m)] sidebar-margin overflow-hidden card-template py-[12rpx] px-[26rpx]" >
        <view v-show="!loading" class="diy-template-wrap">
            <diy-group ref="diyGroupRef" :data="diyFormData" />
        </view>
    </view>
</template>
<script lang="ts" setup>
import { ref, reactive, onMounted } from 'vue';
import diyGroup from '@/addon/components/diy/group/index.vue'

const props = defineProps(['data', 'completeLayout']);
const loading = ref(true);
const diyFormData: any = reactive({
    global: {},
    value: []
})

onMounted(() => {
    diyFormData.global.completeLayout = props.completeLayout || 'style-1';
    if (props.data.formField) {

        props.data.formField.forEach((item: any) => {
            let comp = {
                
                componentIsShow: true, // 是否显示
                id: item.field_key,
                componentName: item.field_type,
                pageStyle: '',
                viewFormDetail: true, // 查看表单详情标识
                isShowArrow: true, // 是否显示箭头
                field: {
                    name: item.field_name,
                    value: item.field_value,
                    required: item.field_required,
                    unique: item.field_unique,
                    privacyProtection: item.privacy_protection,
                },
                margin: {
                    top: 0,
                    bottom: 0,
                    both: 0
                }
            };

            try {
                comp.field.value = JSON.parse(item.field_value)
                if (comp.componentName=="FormNumber" || comp.componentName=="FormIdentity") {
                    comp.field.value =String(item.field_value)
                }
            } catch (error) {
                comp.field.value = item.field_value
            }

            diyFormData.value.push(comp);
        })
    }
    loading.value = false;
})
</script>

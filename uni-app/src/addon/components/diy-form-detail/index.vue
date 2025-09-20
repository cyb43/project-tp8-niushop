<template>
    <view :style="themeColor()">
        <view v-show="!loading" class="diy-template-wrap">
            <diy-group ref="diyGroupRef" :data="diyFormData" />
        </view>
    </view>
</template>
<script lang="ts" setup>
import { ref, reactive, onMounted } from 'vue';
import diyGroup from '@/addon/components/diy/group/index.vue'
import { getFormRecord } from '@/app/api/diy_form';
import { deepClone } from '@/utils/common'

const props = defineProps(['record_id', 'completeLayout']);
const emits = defineEmits(['callback'])
const loading = ref(true);
const diyFormData: any = reactive({
    global: {},
    value: []
})
onMounted(() => {
    getFormRecord({
        record_id: props.record_id
    }).then((res: any) => {
        diyFormData.global.completeLayout = props.completeLayout || 'style-1';
        let recordsFieldList = deepClone(res.data.recordsFieldList)
        if (recordsFieldList) {

            recordsFieldList.forEach((item: any) => {
                let comp = {
                    id: item.field_key,
                    componentName: item.field_type,
                    pageStyle: '',
                    viewFormDetail: true, // 查看表单详情标识
                    componentIsShow: true,
                    field: {
                        name: item.field_name,
                        value: item.handle_field_value,
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
                diyFormData.value.push(comp);
            })
        }
        emits('callback', recordsFieldList)
        loading.value = false;
    }).catch(() => {
        loading.value = false;
        emits('callback', [])
    })
})
</script>

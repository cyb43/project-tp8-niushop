<template>
    <el-dialog v-model="showDialog" :title="t('formDetail')" width="600px" :destroy-on-close="true">
        <el-form label-width="100px" ref="formRef" class="page-form" label-position="left" v-loading="loading">
            <div class="row-bg px-[30px] mb-[20px]" v-if="tableData[0]?.recordsFieldList">
                <div>
                    <el-form-item v-for="(field, fieldKey) in tableData[0]?.recordsFieldList || []" :key="fieldKey" :label="field.field_name" min-width="200">
                        <component :is="tableData[0]?.recordsFieldList[fieldKey].detailComponent" :data="tableData[0]?.recordsFieldList[fieldKey]" />
                    </el-form-item>
                </div>
            </div>
            <div class="text-center" v-else>暂无表单数据</div>
        </el-form>
        <template #footer>
            <span class="dialog-footer">
                <el-button type="primary" @click="showDialog = false">{{ t('confirm') }}</el-button>
            </span>
        </template>
    </el-dialog>
</template>

<script lang="ts" setup>
import { t } from '@/lang'
import { ref, defineAsyncComponent } from 'vue'
import { getFormRecordsInfo } from '@/app/api/diy_form'

const loading = ref(true)
const showDialog = ref(false)
const tableData = ref({})
const modules: any = import.meta.glob('@/**/*.vue')

const show = (data: any) => {
    loading.value = true
    getFormRecordsInfo(data).then(res => {
        tableData.value = [res.data];
        const recordsFieldList = res.data.recordsFieldList;
        for (const key in recordsFieldList) {
            if (modules[recordsFieldList[key].detailComponent]) {
                recordsFieldList[key].detailComponent = defineAsyncComponent(modules[recordsFieldList[key].detailComponent]);
            } else {
                delete recordsFieldList[key];
            }
        }
        loading.value = false
    })
    showDialog.value = true
}

defineExpose({
    showDialog,
    show
})
</script>

<style lang="scss" scoped></style>

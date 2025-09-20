<template>
    <span @click="openDialog" class="cursor-pointer">
        <slot></slot>
    </span>
    <el-dialog v-model="showDialog" :title="t('upload.select' + type)" width="920px" class="attachment-dialog" :destroy-on-close="true">

        <attachment :limit="limit" :type="type" ref="attachmentRef" />

        <template #footer>
            <span class="dialog-footer">
                <el-button @click="showDialog = false">{{ t('cancel') }}</el-button>
                <el-button type="primary" @click="confirm">{{ t('confirm') }}</el-button>
            </span>
        </template>
    </el-dialog>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { deepClone } from '@/utils/common'
import { t } from '@/lang'
import attachment from './attachment.vue'

const prop = defineProps({
    // 选择数量限制
    limit: {
        type: Number,
        default: 1
    },
    type: {
        type: String,
        default: 'image'
    }
})

const showDialog = ref(false)
const attachmentRef: Record<string, any> | null = ref(null)

const openDialog = () => {
    showDialog.value = true
}

const emit = defineEmits(['confirm'])

/**
 * 确认选择
 */
const confirm = () => {
    showDialog.value = false
    let filesObj = attachmentRef?.value.selectedFile || {};
    let filesIndexObj = attachmentRef?.value.selectedFileIndex || {};
    // 整理图片顺序
    let arr = [];
    Object.values(filesIndexObj).forEach((item, index) => {
        for (let key in filesObj) {
            if (item == key) {
                arr.push(deepClone(filesObj[key]))
            }
        }
    })
    emit('confirm', prop.limit == 1 ? arr[0] ?? null : arr)
}

defineExpose({
    showDialog
})
</script>

<style lang="scss">
.attachment-dialog {
    .el-dialog__body {
        padding: 0 !important;
    }

    .el-upload-list {
        position: absolute;
        z-index: 5;
    }

    .el-upload-list__item {
        background: var(--el-dialog-bg-color);
        box-shadow: var(--el-dialog-box-shadow);
    }
}
</style>

<template>
    <!-- 内容 -->
    <div class="content-wrap">
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('pageContent') }}</h3>
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('posterName')">
                    <el-input v-model.trim="posterStore.name" :placeholder="t('posterNamePlaceholder')" clearable maxlength="12" show-word-limit />
                </el-form-item>
                <el-form-item :label="t('bgType')">
                    <el-radio-group v-model="posterStore.global.bgType">
                        <el-radio :label="'url'">{{ t('bgUrl') }}</el-radio>
                        <el-radio :label="'color'">{{ t('bgColor') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item :label="t('bgUrl')" v-show="posterStore.global.bgType == 'url'">
                    <upload-image v-model="posterStore.global.bgUrl" :limit="1" />
                    <div class="text-sm text-gray-400 mt-[10px]">{{ t('bgUrlTips') }}</div>
                </el-form-item>
                <el-form-item :label="t('bgColor')" v-show="posterStore.global.bgType == 'color'">
                    <el-color-picker v-model="posterStore.editComponent.bgColor" :predefine="posterStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('statusLabel')" class="display-block">
                    <el-switch v-model="posterStore.status" :active-value="1" :inactive-value="0" />
                    <div class="text-sm text-gray-400">{{ t('statusTips') }}</div>
                </el-form-item>
            </el-form>

        </div>

    </div>
</template>

<script lang="ts" setup>
import { t } from '@/lang'
import { ref } from 'vue'
import { img } from '@/utils/common'
import usePosterStore from '@/stores/modules/poster'

const posterStore = usePosterStore()

defineExpose({})
</script>

<style lang="scss" scoped></style>

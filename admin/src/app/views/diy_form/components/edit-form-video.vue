<template>
    <!-- 内容 -->
    <div class="content-wrap" v-show="diyStore.editTab == 'content'">

        <!-- 表单组件 字段内容设置 -->
        <slot name="field"></slot>
        <el-form label-width="100px" class="px-[10px]">
            <el-form-item>
                <template #label>
                    <div class="flex items-center">
                        <span class="mr-[3px]">{{ t('上传方式') }}</span>
                        <el-tooltip effect="light" :content="t('拍摄时长限制1分钟，从相册上传不限制时长。')" placement="top">
                            <el-icon>
                                <QuestionFilled color="#999999" />
                            </el-icon>
                        </el-tooltip>
                    </div>
                </template>
                <el-radio-group v-model="diyStore.editComponent.uploadMode">
                    <el-radio label="shoot_and_album">{{ t('拍摄和相册') }}</el-radio>
                    <el-radio label="shoot_only">{{ t('只允许拍摄') }}</el-radio>
                </el-radio-group>
            </el-form-item>
        </el-form>
        <!-- 表单组件 其他设置 -->
        <slot name="other"></slot>

    </div>

    <!-- 样式 -->
    <div class="style-wrap" v-show="diyStore.editTab == 'style'">

        <!-- 表单组件 字段样式 -->
        <slot name="style-field"></slot>

        <!-- 组件样式 -->
        <slot name="style"></slot>

    </div>
</template>

<script lang="ts" setup>
import { t } from '@/lang'
import { ref } from 'vue'
import useDiyStore from '@/stores/modules/diy'

const diyStore = useDiyStore()
diyStore.editComponent.ignore = ['componentBgUrl'] // 忽略公共属性

defineExpose({})

</script>

<style lang="scss" scoped></style>

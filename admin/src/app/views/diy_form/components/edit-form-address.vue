<template>
    <!-- 内容 -->
    <div class="content-wrap" v-show="diyStore.editTab == 'content'">

        <!-- 表单组件 字段内容设置 -->
        <slot name="field"></slot>
        <el-form label-width="100px" class="px-[10px]">
            <el-form-item :label="t('地址格式')">
                <el-radio-group v-model="diyStore.editComponent.addressFormat" class="!block">
                    <el-radio class="!block" label="province/city/district/address">{{ t('省/市/区/街道/详细地址') }}</el-radio>
                    <el-radio class="!block" label="province/city/district/street">{{ t('省/市/区/街道(镇)') }}</el-radio>
                    <el-radio class="!block" label="province/city/district">{{ t('省/市/区(县)') }}</el-radio>
                    <el-radio class="!block" label="province/city">{{ t('省/市') }}</el-radio>
                    <el-radio class="!block" label="province">{{ t('省') }}</el-radio>
                </el-radio-group>
            </el-form-item>
        </el-form>

        <!-- 表单组件 其他设置 -->
        <slot name="other"></slot>
        <el-form label-width="100px" class="px-[10px]">
            <el-form-item class="display-block">
                <template #label>
                    <div class="flex items-center">
                        <span class="mr-[3px]">{{ t('隐私保护') }}</span>
                        <el-tooltip effect="light" placement="top">
                            <template #content>
                                <p>会自动将提交的个人信息做加密展示。</p>
                                <p>适用于公开展示收集的数据且不暴露用户隐私。</p>
                            </template>
                            <el-icon>
                                <QuestionFilled color="#999999" />
                            </el-icon>
                        </el-tooltip>
                    </div>
                </template>
                <el-switch v-model="diyStore.editComponent.field.privacyProtection" :disabled ="diyStore.editComponent.addressFormat != 'province/city/district/address'" />
                <div class="text-sm text-gray-400">{{ t('提交后自动隐藏地址，仅管理员可查看') }}</div>
            </el-form-item>
        </el-form>

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
import { ref,watch } from 'vue'
import useDiyStore from '@/stores/modules/diy'

const diyStore = useDiyStore()
diyStore.editComponent.ignore = ['componentBgUrl'] // 忽略公共属性

// 组件验证
diyStore.editComponent.verify = (index: number) => {
    const res = { code: true, message: '' }
    return res
}
watch(
  () => diyStore.editComponent.addressFormat,
  (newVal) => {
    if (newVal !== 'province/city/district/address') {
      diyStore.editComponent.field.privacyProtection = false
    }
  },
  { immediate: true }
)

defineExpose({})

</script>

<style lang="scss" scoped></style>

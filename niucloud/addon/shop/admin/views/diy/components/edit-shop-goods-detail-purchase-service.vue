<template>
    <!-- 内容 -->
    <div class="content-wrap" v-show="diyStore.editTab == 'content'">
        <div class="edit-attr-item-wrap">
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('是否显示')">
                    <el-checkbox-group v-model="serviceConfig" @change="serviceConfigChange" :min="2">
                        <el-checkbox label="商品服务" value="goods_service" />
                        <el-checkbox label="规格选择" value="spec_select" />
                        <el-checkbox label="配送信息" value="delivery_info" />
                        <el-checkbox label="领券（领取优惠券）" value="coupons"/>
                        <el-checkbox label="优惠活动展示" value="activity" />
                    </el-checkbox-group>
                    <div class="text-sm text-gray-400">{{ t('商品服务最少选择2个') }}</div>
                </el-form-item>
            </el-form>
        </div>
    </div>

    <!-- 样式 -->
    <div class="style-wrap" v-show="diyStore.editTab == 'style'">
        <!-- 组件样式 -->
        <slot name="style"></slot>
    </div>
</template>

<script lang="ts" setup>
import { t } from '@/lang'
import useDiyStore from '@/stores/modules/diy'
import { ref, reactive } from 'vue'

const diyStore: any = useDiyStore()
diyStore.editComponent.ignore = [] // 忽略公共属性

const serviceConfig = ref([])
if (diyStore.editComponent.serviceConfig) {
    serviceConfig.value = (typeof diyStore.editComponent.serviceConfig == 'object') ? diyStore.editComponent.serviceConfig : diyStore.editComponent.serviceConfig.split(',')
}
const serviceConfigChange = (val: any) => {
    diyStore.editComponent.serviceConfig = val
}

// 组件验证
diyStore.editComponent.verify = (index: number) => {
    const res = { code: true, message: '' }
    return res
}

defineExpose({})

</script>

<style lang="scss" scoped></style>

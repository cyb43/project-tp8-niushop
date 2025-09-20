<template>
    <!-- 内容 -->
    <div class="content-wrap goods-detail-bottom" v-show="diyStore.editTab == 'content'">
        <div class="edit-attr-item-wrap">
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('是否显示')">
                    <el-checkbox-group v-model="menuContent" @change="menuContentChange" :min="1" :max="3">
                        <el-checkbox label="首页" value="index" />
                        <el-checkbox label="客服" value="service" />
                        <el-checkbox label="购物车" value="cart" />
                        <el-checkbox label="收藏" value="collect"/>
                        <el-checkbox label="分享" value="share" />
                    </el-checkbox-group>
                    <div class="text-sm text-gray-400">{{ t('菜单内容最多选择3个，最少选择1个') }}</div>
                </el-form-item>
                <el-form-item :label="t('购物车按钮')">
                    <el-radio-group v-model="diyStore.editComponent.cartIsShow">
                        <el-radio :label="true">{{ t('显示') }}</el-radio>
                        <el-radio :label="false">{{ t('隐藏') }}</el-radio>
                    </el-radio-group>
                    <div class="text-sm text-gray-400">{{ t('注意：该设置在实物商品或虚拟商品且不核销的情况下生效') }}</div>
                </el-form-item>
                <el-form-item :label="t('购物车名称')" v-show="diyStore.editComponent.cartIsShow">
                    <el-input v-model.trim="diyStore.editComponent.cartName"
                              :placeholder="t('goodsBtnTextPlaceholder')" clearable maxlength="5" show-word-limit />
                </el-form-item>
                <el-form-item :label="t('购买名称')">
                    <el-input v-model.trim="diyStore.editComponent.buyName"
                              :placeholder="t('goodsBtnTextPlaceholder')" clearable maxlength="5" show-word-limit />
                </el-form-item>
            </el-form>
        </div>
    </div>

    <!-- 样式 -->
    <div class="style-wrap" v-show="diyStore.editTab == 'style'">
        <div class="edit-attr-item-wrap" v-if="diyStore.editComponent.cartIsShow">
            <h3 class="mb-[10px]">{{ t('加入购物车') }}</h3>
            <el-form label-width="90px" class="px-[10px]">
                <el-form-item :label="t('文字大小')">
                    <el-slider v-model="diyStore.editComponent.cartStyle.fontSize" show-input size="small" class="ml-[10px] diy-nav-slider" :min="12" :max="15" />
                </el-form-item>
                <el-form-item :label="t('textColor')">
                    <el-color-picker v-model="diyStore.editComponent.cartStyle.textColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('bgGradientAngle')">
                    <el-radio-group v-model="diyStore.editComponent.cartStyle.gradientAngle">
                        <el-radio label="to bottom">{{ t('topToBottom') }}</el-radio>
                        <el-radio label="to right">{{ t('leftToRight') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item :label="t('背景颜色')">
                    <el-color-picker v-model="diyStore.editComponent.cartStyle.startColor" show-alpha :predefine="diyStore.predefineColors" />
                    <icon name="iconfont iconmap-connect" size="20px" class="block !text-gray-400 mx-[5px]" />
                    <el-color-picker v-model="diyStore.editComponent.cartStyle.endColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
            </el-form>
        </div>

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('立即购买') }}</h3>
            <el-form label-width="90px" class="px-[10px]">
                <el-form-item :label="t('文字大小')">
                    <el-slider v-model="diyStore.editComponent.buyStyle.fontSize" show-input size="small" class="ml-[10px] diy-nav-slider" :min="12" :max="15" />
                </el-form-item>
                <el-form-item :label="t('textColor')">
                    <el-color-picker v-model="diyStore.editComponent.buyStyle.textColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('bgGradientAngle')">
                    <el-radio-group v-model="diyStore.editComponent.buyStyle.gradientAngle">
                        <el-radio label="to bottom">{{ t('topToBottom') }}</el-radio>
                        <el-radio label="to right">{{ t('leftToRight') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item :label="t('背景颜色')">
                    <el-color-picker v-model="diyStore.editComponent.buyStyle.startColor" show-alpha :predefine="diyStore.predefineColors" />
                    <icon name="iconfont iconmap-connect" size="20px" class="block !text-gray-400 mx-[5px]" />
                    <el-color-picker v-model="diyStore.editComponent.buyStyle.endColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
            </el-form>
        </div>
        <!-- 组件样式 -->
        <slot name="style"></slot>
    </div>
</template>

<script lang="ts" setup>
import { t } from '@/lang'
import useDiyStore from '@/stores/modules/diy'
import { ref, onMounted } from 'vue'

const diyStore: any = useDiyStore()
diyStore.editComponent.ignore = ['pageBgColor', 'componentBgUrl', 'marginTop', 'marginBottom', 'marginBoth', 'topRounded', 'bottomRounded'] // 忽略公共属性

// 组件验证
diyStore.editComponent.verify = (index: number) => {
    const res = { code: true, message: '' }
    return res
}

const initFn = () => {
    if (diyStore.editComponent.menuContent) {
        menuContent.value = (typeof diyStore.editComponent.menuContent == 'object') ? diyStore.editComponent.menuContent : diyStore.editComponent.menuContent.split(',')
    }
}

// 菜单内容
const menuContent = ref([])
const menuContentChange = (val: any) => {
    diyStore.editComponent.menuContent = val
}

onMounted(() => {
    initFn()
})
defineExpose({})

</script>

<style lang="scss" scoped></style>
<style lang="scss">
.goods-detail-bottom {
    .el-form-item__label {
        width: 100px !important;
    }
}
</style>

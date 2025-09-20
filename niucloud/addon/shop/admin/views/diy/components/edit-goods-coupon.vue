<template>
    <!-- 内容 -->
    <div class="content-wrap" v-show="diyStore.editTab == 'content'">

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('selectStyle') }}</h3>
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('selectStyle')" class="flex">
                    <span class="text-primary flex-1 cursor-pointer"
                          @click="showCouponStyle">{{ diyStore.editComponent.styleName }}</span>
                    <el-icon @click="showCouponStyle" class="cursor-pointer">
                        <ArrowRight />
                    </el-icon>
                </el-form-item>
            </el-form>

            <el-dialog v-model="showCouponDialog" :title="t('selectStyle')" width="500px">
                <div class="flex flex-wrap">
                    <template v-for="(item,index) in couponStyleList" :key="index">
                        <div :class="{ 'border-primary': selectCouponStyle.value == item.value }"
                             @click="changeCouponStyle(item)"
                             class="flex items-center justify-center overflow-hidden w-[200px] h-[100px] m-[6px] cursor-pointer border bg-gray-50">
                            <img :src="img(item.url)" />
                        </div>
                    </template>
                </div>

                <template #footer>
                    <span class="dialog-footer">
                        <el-button @click="showCouponDialog = false">{{ t('cancel') }}</el-button>
                        <el-button type="primary" @click="confirmCouponStyle">{{ t('confirm') }}</el-button>
                    </span>
                </template>

            </el-dialog>
        </div>

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('couponContent') }}</h3>
            <el-form label-width="90px" class="px-[10px]">

                <el-form-item :label="t('couponTitle')">
                    <el-input v-model.trim="diyStore.editComponent.couponTitle" clearable :maxlength="diyStore.editComponent.style == 'style-3' ? 4 : 8" show-word-limit />
                </el-form-item>

                <el-form-item :label="t('couponSubTitle')">
                    <el-input v-model.trim="diyStore.editComponent.couponSubTitle" clearable :maxlength="diyStore.editComponent.style == 'style-3' ? 7 : 10" show-word-limit />
                </el-form-item>

            </el-form>

        </div>

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('couponData') }}</h3>
            <el-form label-width="90px" class="px-[10px]">

                <el-form-item :label="t('selectCoupon')">
                    <el-radio-group v-model="diyStore.editComponent.source" :title="t('goodsSelectPopupSelectGoodsButton')">
                        <el-radio label="all">{{ t('allSources') }}</el-radio>
                        <el-radio label="custom">{{ t('manualSelectionSources') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item :label="t('manualSelectionSources')" v-if="diyStore.editComponent.source == 'custom'">
                    <coupon-select-popup ref="couponSelectPopupRef" v-model="diyStore.editComponent.couponIds" :min="1" :max="20" />
                </el-form-item>
                <el-form-item :label="t('couponNum')" v-if="diyStore.editComponent.source == 'all'">
                    <el-slider show-input v-model="diyStore.editComponent.num" :min="1" max="20" size="small"
                               class="goods-coupon-slider" />
                </el-form-item>
                <el-form-item :label="t('couponBtnText')" v-if="diyStore.editComponent.style != 'style-4'">
                    <el-input v-model.trim="diyStore.editComponent.btnText" clearable maxlength="5" show-word-limit />
                </el-form-item>

            </el-form>

        </div>

    </div>

    <!-- 样式 -->
    <div class="style-wrap" v-show="diyStore.editTab == 'style'">

        <div class="edit-attr-item-wrap" v-if="diyStore.editComponent.style == 'style-4'">
            <h3 class="mb-[10px]">{{ t("couponTitleStyle") }}</h3>
            <el-form label-width="90px" class="px-[10px]">
                <el-form-item :label="t('couponTitleColor')">
                    <el-color-picker v-model="diyStore.editComponent.titleColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('couponSubTitleColor')">
                    <el-color-picker v-model="diyStore.editComponent.subTitleColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
            </el-form>
        </div>

        <div class="edit-attr-item-wrap" v-if="diyStore.editComponent.style == 'style-4'">
            <h3 class="mb-[10px]">{{ t("couponItemStyle") }}</h3>
            <el-form label-width="90px" class="px-[10px]">
                <el-form-item :label="t('couponMoney')">
                    <el-color-picker v-model="diyStore.editComponent.couponItem.moneyColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('textColor')">
                    <el-color-picker v-model="diyStore.editComponent.couponItem.textColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('subTextColor')">
                    <el-color-picker v-model="diyStore.editComponent.couponItem.subTextColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('listFrameColor')">
                    <el-color-picker v-model="diyStore.editComponent.couponItem.bgColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('goodsRounded')">
                    <el-slider v-model="diyStore.editComponent.couponItem.aroundRadius" show-input size="small" class="ml-[10px] diy-nav-slider" :max="50" />
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
import { img } from '@/utils/common'
import { ref, reactive } from 'vue'
import couponSelectPopup from '@/addon/shop/views/goods/components/coupon-select-popup.vue'

const diyStore = useDiyStore()
diyStore.editComponent.ignore = ['componentBgColor', 'componentBgUrl'] // 忽略公共属性

// 组件验证
diyStore.editComponent.verify = (index: number) => {
    const res = { code: true, message: '' }

    if (diyStore.value[index].source == 'custom') {
        if (diyStore.value[index].couponIds.length == 0) {
            res.code = false
            res.message = t('couponPlaceholder')
            return res
        }
    }

    if (diyStore.value[index].btnText == '') {
        res.code = false
        res.message = t('couponBtnTextPlaceholder')
        return res
    }

    if (diyStore.value[index].couponTitle == '') {
        res.code = false
        res.message = t('couponTitlePlaceholder')
        return res
    }

    if (diyStore.value[index].couponSubTitle == '') {
        res.code = false
        res.message = t('couponSubTitlePlaceholder')
        return res
    }

    return res
}

const selectCouponStyle = reactive({
    title: diyStore.editComponent.styleName,
    value: diyStore.editComponent.style
})

// 风格样式
const showCouponDialog = ref(false)

const showCouponStyle = () => {
    showCouponDialog.value = true
    selectCouponStyle.title = diyStore.editComponent.styleName
    selectCouponStyle.value = diyStore.editComponent.style
}

const couponStyleList = reactive([
    {
        url: 'addon/shop/diy/goods_coupon/style-1.png',
        title: '风格1',
        value: 'style-1'
    },
    {
        url: 'addon/shop/diy/goods_coupon/style-2.png',
        title: '风格2',
        value: 'style-2'
    },
    {
        url: 'addon/shop/diy/goods_coupon/style-3.png',
        title: '风格3',
        value: 'style-3'
    },
    {
        url: 'addon/shop/diy/goods_coupon/style-4.png',
        title: '风格4',
        value: 'style-4'
    }
])

const changeCouponStyle = (item: any) => {
    selectCouponStyle.title = item.title
    selectCouponStyle.value = item.value
}

const confirmCouponStyle = () => {
    diyStore.editComponent.styleName = selectCouponStyle.title
    diyStore.editComponent.style = selectCouponStyle.value
    if (diyStore.editComponent.style == 'style-3') {
        if (diyStore.editComponent.couponTitle && diyStore.editComponent.couponTitle.length > 4) {
            diyStore.editComponent.couponTitle = diyStore.editComponent.couponTitle.substring(0, 4)
        }
        if (diyStore.editComponent.couponSubTitle && diyStore.editComponent.couponSubTitle.length > 7) {
            diyStore.editComponent.couponSubTitle = diyStore.editComponent.couponSubTitle.substring(0, 7)
        }
    }
    initStyleFn()
    showCouponDialog.value = false
}

const initStyleFn = () => {
    const index = diyStore.editComponent.ignore.indexOf('componentBgColor')
    if (diyStore.editComponent.style == 'style-4' && index != -1) {
        diyStore.editComponent.ignore.splice(index, 1)
        diyStore.editComponent.titleColor = '#ffffff'
        diyStore.editComponent.subTitleColor = '#ffffff'

        diyStore.editComponent.couponItem.moneyColor = '#fa191d'
        diyStore.editComponent.couponItem.textColor = '#333333'
        diyStore.editComponent.couponItem.subTextColor = '#999999'
        diyStore.editComponent.couponItem.bgColor = '#ffffff'
        diyStore.editComponent.couponItem.aroundRadius = 10
        diyStore.editComponent.componentStartBgColor = '#fa191d'
    } else if (diyStore.editComponent.style != 'style-4' && index == -1) {
        diyStore.editComponent.ignore.push('componentBgColor')
    }
}

defineExpose({})

</script>

<style lang="scss" scoped>
</style>

<style lang="scss">
.goods-coupon-slider {
    .el-slider__input {
        width: 100px;
    }
}
</style>

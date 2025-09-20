<template>
    <!-- 内容 -->
    <div class="content-wrap" v-show="diyStore.editTab == 'content'">
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('styleRecommend') }}</h3>
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('selectStyle')" class="flex">
                    <span class="text-primary flex-1 cursor-pointer"
                          @click="showTitleStyle">{{ diyStore.editComponent.style.title }}</span>
                    <el-icon @click="showTitleStyle" class="cursor-pointer">
                        <ArrowRight />
                    </el-icon>
                </el-form-item>
            </el-form>

            <el-dialog v-model="showTitleDialog" :title="t('selectStyle')" width="460px">
                <div class="flex flex-wrap">
                    <template v-for="(item,index) in styleList" :key="index">
                        <div :class="{ 'border-primary': selectStyle.value == item.value }"
                             @click="changeTitleStyle(item)"
                             class="flex items-center justify-center overflow-hidden w-[200px] h-[100px] mr-[12px] mb-[12px] cursor-pointer border bg-[#eee]">
                            <img :src="img(item.url)" />
                        </div>
                    </template>
                </div>
                <template #footer>
                    <span class="dialog-footer">
                        <el-button @click="showTitleDialog = false">{{ t('cancel') }}</el-button>
                        <el-button type="primary" @click="confirmTitleStyle">{{ t('confirm') }}</el-button>
                    </span>
                </template>

            </el-dialog>
        </div>

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('titleContent') }}</h3>
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('image')">
                    <upload-image v-model="diyStore.editComponent.textImg" :limit="1" />
                </el-form-item>
                <el-form-item :label="t('subTitle')" v-show="diyStore.editComponent && diyStore.editComponent.style && diyStore.editComponent.style.value == 'style-3'">
                    <el-input v-model.trim="diyStore.editComponent.subTitle.text" :placeholder="t('subTitlePlaceholder')" clearable maxlength="8" show-word-limit />
                </el-form-item>
                <el-form-item :label="t('link')" v-show="diyStore.editComponent && diyStore.editComponent.style && diyStore.editComponent.style.value == 'style-3'">
                    <diy-link v-model="diyStore.editComponent.subTitle.link" />
                </el-form-item>
            </el-form>
        </div>

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t("selectSource") }}</h3>
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('goodsSelectPopupSelectGoodsButton')">
                    <el-radio-group v-model="diyStore.editComponent.source" :title="t('goodsSelectPopupSelectGoodsButton')">
                        <el-radio label="all">{{ t('goodsSelectPopupAllGoods') }}</el-radio>
                        <el-radio label="custom">{{ t('manualSelectionSources') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item :label="t('goodsNum')" v-if="diyStore.editComponent.source == 'all' || diyStore.editComponent.source == 'category'">
                    <el-slider class="goods-list-slider" show-input v-model="diyStore.editComponent.num" :min="1" max="20" size="small" />
                </el-form-item>
                <el-form-item :label="t('customGoods')" v-if="diyStore.editComponent.source == 'custom'">
                    <newcomer-goods-select-popup ref="goodsSelectPopupRef" v-model="diyStore.editComponent.goods_ids" :min="1" :max="99" mode="sku" />
                </el-form-item>
            </el-form>

        </div>
    </div>

    <!-- 样式 -->
    <div class="style-wrap" v-show="diyStore.editTab == 'style'">
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('goodsStyle') }}</h3>
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('imageRounded')">
                    <el-slider v-model="diyStore.editComponent.imgElementRounded" show-input size="small" class="ml-[10px] diy-nav-slider" :max="50" />
                </el-form-item>
                <el-form-item :label="t('topRounded')">
                    <el-slider v-model="diyStore.editComponent.topElementRounded" show-input size="small" class="ml-[10px] diy-nav-slider" :max="50" />
                </el-form-item>
                <el-form-item :label="t('bottomRounded')">
                    <el-slider v-model="diyStore.editComponent.bottomElementRounded" show-input size="small" class="ml-[10px] diy-nav-slider" :max="50" />
                </el-form-item>
            </el-form>
        </div>
        <div class="edit-attr-item-wrap"
             v-if="diyStore.editComponent && diyStore.editComponent.style && diyStore.editComponent.style.value == 'style-3'">
            <h3 class="mb-[10px]">{{ t('subTitleStyle') }}</h3>
            <el-form label-width="90px" class="px-[10px]">
                <el-form-item :label="t('textColor')">
                    <el-color-picker v-model="diyStore.editComponent.subTitle.textColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('subTextBgColor')">
                    <el-color-picker v-model="diyStore.editComponent.subTitle.startColor" show-alpha :predefine="diyStore.predefineColors" />
                    <icon name="iconfont iconmap-connect" size="20px" class="block !text-gray-400 mx-[5px]" />
                    <el-color-picker v-model="diyStore.editComponent.subTitle.endColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
            </el-form>
        </div>
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('countDownStyle') }}</h3>
            <el-form label-width="90px" class="px-[10px]">
                <el-form-item :label="t('newcomerNumberColor')">
                    <el-color-picker v-model="diyStore.editComponent.countDown.numberColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('newcomerNumberBg')">
                    <el-color-picker v-model="diyStore.editComponent.countDown.numberBg.startColor" show-alpha :predefine="diyStore.predefineColors" />
                    <icon name="iconfont iconmap-connect" size="20px" class="block !text-gray-400 mx-[5px]" />
                    <el-color-picker v-model="diyStore.editComponent.countDown.numberBg.endColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('newcomerOtherColor')">
                    <el-color-picker v-model="diyStore.editComponent.countDown.otherColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
            </el-form>
        </div>

        <!-- 组件样式 -->
        <slot name="style"></slot>
    </div>
</template>

<script lang="ts" setup>
import { t } from '@/lang'
import { img } from '@/utils/common'
import useDiyStore from '@/stores/modules/diy'
import { ref, reactive } from 'vue'
import newcomerGoodsSelectPopup from '@/addon/shop/views/goods/components/newcomer-goods-select-popup.vue'

const diyStore: any = useDiyStore()
diyStore.editComponent.ignore = ['componentBgUrl'] // 忽略公共属性

// 组件验证
diyStore.editComponent.verify = (index: number) => {
    const res = { code: true, message: '' }

    if (diyStore.value[index].source == 'custom') {
        if (diyStore.value[index].goods_ids.length == 0) {
            res.code = false
            res.message = t('goodsPlaceholder')
        }
    }

    return res
}

// 标题风格样式
const showTitleDialog = ref(false)

const showTitleStyle = () => {
    selectStyle.title = diyStore.editComponent.style.title;
    selectStyle.value = diyStore.editComponent.style.value;
    showTitleDialog.value = true
}

const changeTitleStyle = (item: any) => {
    selectStyle.title = item.title;
    selectStyle.value = item.value;
}

const confirmTitleStyle = () => {
    diyStore.editComponent.style.title = selectStyle.title;
    diyStore.editComponent.style.value = selectStyle.value;
    initStyle(diyStore.editComponent.style.value);
    showTitleDialog.value = false
}

const styleList = reactive([
    {
        url: 'addon/shop/diy/newcomer/style_01.png',
        title: '风格1',
        value: 'style-1'
    }, {
        url: 'addon/shop/diy/newcomer/style_02.png',
        title: '风格2',
        value: 'style-2'
    }, {
        url: 'addon/shop/diy/newcomer/style_03.png',
        title: '风格3',
        value: 'style-3'
    }, {
        url: 'addon/shop/diy/newcomer/style_04.png',
        title: '风格4',
        value: 'style-4'
    }
])

const initStyle = (style: any) => {
    if (style == 'style-1') {
        diyStore.editComponent.textImg = "addon/shop/diy/newcomer/style_title_01.png";
        diyStore.editComponent.countDown.numberColor = "rgba(255, 0, 0, 1)";
        diyStore.editComponent.countDown.numberBg.startColor = "rgba(255, 255, 255, 1)";
        diyStore.editComponent.countDown.numberBg.endColor = "";
        diyStore.editComponent.countDown.otherColor = "rgba(255, 255, 255, 1)";
        diyStore.editComponent.textColor = "#303133";
        diyStore.editComponent.pageStartBgColor = "";
        diyStore.editComponent.pageEndBgColor = "";
        diyStore.editComponent.pageGradientAngle = "to bottom";
        diyStore.editComponent.componentStartBgColor = "#ff6D1A";
        diyStore.editComponent.componentEndBgColor = "rgba(255, 70, 56, 1)";
        diyStore.editComponent.componentGradientAngle = "to right";
        diyStore.editComponent.bottomRounded = 12;
        diyStore.editComponent.topRounded = 12;
        diyStore.editComponent.elementBgColor = "";
        diyStore.editComponent.bottomElementRounded = 10;
        diyStore.editComponent.topElementRounded = 10;
        diyStore.editComponent.margin.top = 10;
        diyStore.editComponent.margin.bottom = 0;
        diyStore.editComponent.margin.both = 10;
    } else if (style == 'style-2') {
        diyStore.editComponent.textImg = "addon/shop/diy/newcomer/style_title_02.png";
        diyStore.editComponent.countDown.numberColor = "rgba(255, 255, 255, 1)";
        diyStore.editComponent.countDown.numberBg.startColor = "rgba(255, 44, 54, 1)";
        diyStore.editComponent.countDown.numberBg.endColor = "";
        diyStore.editComponent.countDown.otherColor = "rgba(102, 102, 102, 1)";
        diyStore.editComponent.textColor = "#303133";
        diyStore.editComponent.pageStartBgColor = "";
        diyStore.editComponent.pageEndBgColor = "";
        diyStore.editComponent.pageGradientAngle = "to bottom";
        diyStore.editComponent.componentStartBgColor = "rgba(255, 255, 255, 1)";
        diyStore.editComponent.componentEndBgColor = "";
        diyStore.editComponent.componentGradientAngle = "to bottom";
        diyStore.editComponent.bottomRounded = 12;
        diyStore.editComponent.topRounded = 12;
        diyStore.editComponent.elementBgColor = "";
        diyStore.editComponent.bottomElementRounded = 5;
        diyStore.editComponent.topElementRounded = 5;
        diyStore.editComponent.margin.top = 10;
        diyStore.editComponent.margin.bottom = 0;
        diyStore.editComponent.margin.both = 10;
    } else if (style == 'style-3') {
        diyStore.editComponent.textImg = "addon/shop/diy/newcomer/style_title_03.png";
        diyStore.editComponent.subTitle.text = "查看更多";
        diyStore.editComponent.subTitle.textColor = "rgba(239, 0, 12, 1)";
        diyStore.editComponent.subTitle.startColor = "rgba(255, 248, 217, 1)";
        diyStore.editComponent.subTitle.endColor = "rgba(255, 254, 251, 1)";
        diyStore.editComponent.subTitle.link.name = "";
        diyStore.editComponent.countDown.numberColor = "rgba(239, 0, 12, 1)";
        diyStore.editComponent.countDown.numberBg.startColor = "rgba(255, 248, 217, 1)";
        diyStore.editComponent.countDown.numberBg.endColor = "rgba(255, 253, 246, 1)";
        diyStore.editComponent.countDown.otherColor = "rgba(255, 253, 246, 1)";
        diyStore.editComponent.textColor = "#303133";
        diyStore.editComponent.pageStartBgColor = "";
        diyStore.editComponent.pageEndBgColor = "";
        diyStore.editComponent.pageGradientAngle = "to bottom";
        diyStore.editComponent.componentStartBgColor = "rgba(255, 12, 16, 1)";
        diyStore.editComponent.componentEndBgColor = "rgba(255, 101, 18, 1)";
        diyStore.editComponent.componentGradientAngle = "to right";
        diyStore.editComponent.bottomRounded = 12;
        diyStore.editComponent.topRounded = 12;
        diyStore.editComponent.elementBgColor = "";
        diyStore.editComponent.bottomElementRounded = 0;
        diyStore.editComponent.topElementRounded = 0;
        diyStore.editComponent.margin.top = 10;
        diyStore.editComponent.margin.bottom = 0;
        diyStore.editComponent.margin.both = 10;
    } else if (style == 'style-4') {
        diyStore.editComponent.textImg = "addon/shop/diy/newcomer/style_title_02.png";
        diyStore.editComponent.countDown.numberColor = "rgba(255, 255, 255, 1)";
        diyStore.editComponent.countDown.numberBg.startColor = "";
        diyStore.editComponent.countDown.numberBg.endColor = "";
        diyStore.editComponent.countDown.otherColor = "rgba(255, 253, 253, 1)";
        diyStore.editComponent.textColor = "#303133";
        diyStore.editComponent.pageStartBgColor = "";
        diyStore.editComponent.pageEndBgColor = "";
        diyStore.editComponent.pageGradientAngle = "to bottom";
        diyStore.editComponent.componentStartBgColor = "rgba(255, 255, 255, 1)";
        diyStore.editComponent.componentEndBgColor = "rgba(255, 255, 255, 1)";
        diyStore.editComponent.componentGradientAngle = "to bottom";
        diyStore.editComponent.bottomRounded = 12;
        diyStore.editComponent.topRounded = 12;
        diyStore.editComponent.elementBgColor = "";
        diyStore.editComponent.bottomElementRounded = 10;
        diyStore.editComponent.topElementRounded = 10;
        diyStore.editComponent.margin.top = 10;
        diyStore.editComponent.margin.bottom = 0;
        diyStore.editComponent.margin.both = 10;
    }

}

const selectStyle = reactive({
    title: diyStore.editComponent.style.title,
    value: diyStore.editComponent.style.value
})

defineExpose({})

</script>

<style lang="scss" scoped></style>
<style lang="scss">
.goods-list-slider {
    .el-slider__input {
        width: 100px;
    }
}
</style>

<template>
    <div>
        <!-- 内容 -->
        <div class="content-wrap" v-show="diyStore.editTab == 'content'">
            <!-- <div class="edit-attr-item-wrap">
              <h3 class="mb-[10px]">{{ t('selectStyle') }}</h3>
              <div class="flex items-center mb-[18px] rounded overflow-hidden">
                <span
                  class="iconfont icongudingzhanshi border-[1px] border-solid border-[#eee] cursor-pointer flex-1 flex items-center justify-center py-[5px]"
                  :class="{ 'border-[var(--el-color-primary)] text-[var(--el-color-primary)]': diyStore.editComponent.style == 'style-1' }"
                  @click="diyStore.editComponent.style = 'style-1'"></span>
                <span
                  class="iconfont icontuwendaohang3 border-[1px] border-solid border-[#eee] cursor-pointer flex-1 flex items-center justify-center py-[5px]"
                  :class="{ 'border-[var(--el-color-primary)] text-[var(--el-color-primary)]': diyStore.editComponent.style == 'style-2' }"
                  @click="diyStore.editComponent.style = 'style-2'"></span>
                <span
                  class="iconfont iconshangpinliebiaohengxianghuadong border-[1px] border-solid border-[#eee] cursor-pointer flex-1 flex items-center justify-center py-[5px]"
                  :class="{ 'border-[var(--el-color-primary)] text-[var(--el-color-primary)]': diyStore.editComponent.style == 'style-3' }"
                  @click="diyStore.editComponent.style = 'style-3'"></span>
              </div>
            </div> -->
            <div class="edit-attr-item-wrap">
                <h3 class="mb-[10px]">{{ t("selectSource") }}</h3>
                <el-form label-width="80px" class="px-[10px]">
                    <el-form-item :label="t('sortWay')">
                        <el-radio-group v-model="diyStore.editComponent.sortWay">
                            <el-radio label="total_order_num">{{ t('default') }}</el-radio>
                            <el-radio label="total_exchange_num">{{ t('sales') }}</el-radio>
                            <el-radio label="price">{{ t('price') }}</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item :label="t('goodsSelectPopupSelectGoodsButton')">
                        <el-radio-group v-model="diyStore.editComponent.source" :title="t('goodsSelectPopupSelectGoodsButton')">
                            <el-radio label="all">{{ t('goodsSelectPopupAllGoods') }}</el-radio>
                            <el-radio label="custom">{{ t('manualSelectionSources') }}</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <!-- <el-form-item :label="t('selectCategory')" v-if="diyStore.editComponent.source == 'category'">
                      <div class="flex items-center w-full">
                        <div class="cursor-pointer ml-auto" @click="categoryShowDialogOpen">
                          <span class="text-[var(--el-color-primary)]">{{ diyStore.editComponent.goods_category_name }}</span>
                          <span class="iconfont iconxiangyoujiantou"></span>
                        </div>
                      </div>
                    </el-form-item>
                    <el-form-item :label="t('goodsNum')" v-if="diyStore.editComponent.source == 'all' || diyStore.editComponent.source == 'category'">
                      <div class="flex items-center w-full ml-[5px]">
                        <el-slider class="flex-1" v-model="diyStore.editComponent.num" :min="1" max="20" size="small" />
                        <span class="ml-[15px]">{{ diyStore.editComponent.num }}</span>
                      </div>
                    </el-form-item> -->
                    <el-form-item :label="t('customGoods')" v-if="diyStore.editComponent.source == 'custom'">
                        <el-button type="primary" @click="goodsSelectPopupRef.show(diyStore.editComponent.goods_ids)">
                            {{ t('goodsSelectPopupSelectGoodsButton') }}
                        </el-button>
                        <div class="inline-block ml-[10px] text-[14px]"
                             v-show="diyStore.editComponent.goods_ids.length">
                            <span>{{ t('goodsSelectPopupSelect') }}</span>
                            <span class="text-primary mx-[2px]">{{ diyStore.editComponent.goods_ids.length }}</span>
                            <span>{{ t('goodsSelectPopupPiece') }}</span>
                        </div>
                        <goods-select-popup ref="goodsSelectPopupRef" :min="1" @select="goodsSelect" />
                    </el-form-item>
                </el-form>

            </div>
        </div>

        <!-- 样式 -->
        <div class="style-wrap" v-if="diyStore.editTab == 'style'">
            <div class="edit-attr-item-wrap">
                <h3 class="mb-[10px]">{{ t('goodsStyle') }}</h3>
                <el-form label-width="80px" class="px-[10px]">
                    <el-form-item :label="t('goodsNameColor')">
                        <el-color-picker v-model="diyStore.editComponent.goodsNameStyle.color" show-alpha :predefine="diyStore.predefineColors" />
                        <div class="mr-[20px]"></div>
                        <el-radio-group v-model="diyStore.editComponent.goodsNameStyle.fontWeight">
                            <el-radio :label="'normal'">{{ t('fontWeightNormal') }}</el-radio>
                            <el-radio :label="'bold'">{{ t('fontWeightBold') }}</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item :label="t('imageRounded')">
                        <el-slider v-model="diyStore.editComponent.imgElementRounded" show-input size="small" class="ml-[10px] diy-nav-slider" :max="50" />
                    </el-form-item>
                    <el-form-item :label="t('goodsNumColor')">
                        <el-color-picker v-model="diyStore.editComponent.saleStyle.color" show-alpha :predefine="diyStore.predefineColors" />
                    </el-form-item>
                    <el-form-item :label="t('goodsPriceColor')">
                        <el-color-picker v-model="diyStore.editComponent.priceStyle.mainColor" show-alpha :predefine="diyStore.predefineColors" />
                    </el-form-item>
                    <el-form-item :label="t('topRounded')">
                        <el-slider v-model="diyStore.editComponent.topElementRounded" show-input size="small" class="ml-[10px] diy-nav-slider" :max="50" />
                    </el-form-item>
                    <el-form-item :label="t('bottomRounded')">
                        <el-slider v-model="diyStore.editComponent.bottomElementRounded" show-input size="small" class="ml-[10px] diy-nav-slider" :max="50" />
                    </el-form-item>
                </el-form>
            </div>

            <!-- 组件样式 -->
            <slot name="style"></slot>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { t } from '@/lang'
import useDiyStore from '@/stores/modules/diy'
import { ref, reactive } from 'vue'
import goodsSelectPopup from '@/addon/shop/views/marketing/exchange/components/goods-select-popup.vue'

const diyStore: any = useDiyStore()
diyStore.editComponent.ignore = [] // 忽略公共属性

// 组件验证
diyStore.editComponent.verify = (index: number) => {
    const res = { code: true, message: '' }

    if (diyStore.value[index].source == 'category') {
        if (diyStore.value[index].goods_category == '') {
            res.code = false
            res.message = t('goodsCategoryPlaceholder')
        }
    } else if (diyStore.value[index].source == 'custom') {
        if (diyStore.value[index].goods_ids.length == 0) {
            res.code = false
            res.message = t('goodsPlaceholder')
        }
    }

    return res
}

const goodsSelectPopupRef = ref()
const goodsSelect = (val: any) => {
    diyStore.editComponent.goods_ids = val.map((el: any) => el.id)
}
defineExpose({})

</script>

<style lang="scss" scoped></style>

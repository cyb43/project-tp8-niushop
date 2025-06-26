<template>
    <!-- 内容 -->
    <div class="content-wrap" v-show="diyStore.editTab == 'content'">
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t("selectSource") }}</h3>
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('goodsSelectPopupSelectGoodsButton')">
                    <el-radio-group v-model="diyStore.editComponent.source" :title="t('goodsSelectPopupSelectGoodsButton')">
                        <el-radio label="all">{{ t('defaultGoodsSelect') }}</el-radio>
                        <el-radio label="custom">{{ t('manualSelectionSources') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item :label="t('customGoods')" v-if="diyStore.editComponent.source == 'custom'">
                    <goods-select-popup ref="goodsSelectPopupRef" v-model="diyStore.editComponent.goods_ids" :min="diyStore.editComponent.list.length" :max="diyStore.editComponent.list.length" />
                </el-form-item>
            </el-form>
        </div>

        <div class="edit-attr-item-wrap">
            <el-form label-width="120px" class="px-[10px]">
                <h3 class="mb-[10px]">{{ t('activeCubeBlockContent') }}</h3>
                <p class="text-sm text-gray-400 mb-[10px]">{{ t('dragMouseAdjustOrder') }}</p>
                <div ref="blockBoxRef">
                    <div v-for="(item,index) in diyStore.editComponent.list" :key="item.id" class="item-wrap p-[10px] pb-0 relative border border-dashed border-gray-300 mb-[16px]">
                        <el-form-item :label="t('activeCubeTitle')">
                            <el-input v-model.trim="item.title.text" :placeholder="t('activeCubeTitlePlaceholder')" clearable maxlength="4" show-word-limit />
                        </el-form-item>
                        <el-form-item :label="t('shopGoodsRecommendComponentTag')">
                            <el-input v-model.trim="item.moreTitle.text" :placeholder="t('shopGoodsRecommendComponentTagPlaceholder')" clearable maxlength="2" show-word-limit />
                        </el-form-item>
                        <el-form-item :label="t('activeCubeButton')">
                            <el-input v-model.trim="item.button.text" :placeholder="t('activeCubeButtonPlaceholder')" clearable maxlength="2" show-word-limit />
                        </el-form-item>
                        <el-form-item :label="t('activeCubeSubTitleTextColor')">
                            <el-color-picker v-model="item.title.textColor" show-alpha :predefine="diyStore.predefineColors" />
                        </el-form-item>
                        <el-form-item :label="t('shopGoodsRecommendComponentTagColor')">
                            <el-color-picker v-model="item.moreTitle.startColor" show-alpha :predefine="diyStore.predefineColors" />
                            <icon name="iconfont iconmap-connect" size="20px" class="block !text-gray-400 mx-[5px]" />
                            <el-color-picker v-model="item.moreTitle.endColor" show-alpha :predefine="diyStore.predefineColors" />
                        </el-form-item>
                        <el-form-item :label="t('activeCubeButtonColor')">
                            <el-color-picker v-model="item.button.color" show-alpha :predefine="diyStore.predefineColors" />
                        </el-form-item>
                        <el-form-item :label="t('activeListFrameColor')">
                            <el-color-picker v-model="item.listFrame.startColor" show-alpha :predefine="diyStore.predefineColors" />
                            <icon name="iconfont iconmap-connect" size="20px" class="block !text-gray-400 mx-[5px]" />
                            <el-color-picker v-model="item.listFrame.endColor" show-alpha :predefine="diyStore.predefineColors" />
                        </el-form-item>
                        <div class="del absolute cursor-pointer z-[2] top-[-8px] right-[-8px]"
                             v-show="diyStore.editComponent.list.length > 1" @click="deleteTempFn(index)">
                            <icon name="element CircleCloseFilled" color="#bbb" size="20px" />
                        </div>
                    </div>
                </div>
                <el-button v-show="diyStore.editComponent.list.length < 10" class="w-full" @click="addItem">{{ t('activeCubeAddItem') }}</el-button>
            </el-form>
        </div>
    </div>

    <!-- 样式 -->
    <div class="style-wrap" v-show="diyStore.editTab == 'style'">
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('goodsStyle') }}</h3>
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('goodsPriceColor')">
                    <el-color-picker v-model="diyStore.editComponent.priceStyle.mainColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
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
        <!-- 组件样式 -->
        <slot name="style"></slot>
    </div>
</template>

<script lang="ts" setup>
import { t } from '@/lang'
import useDiyStore from '@/stores/modules/diy'
import { ref, reactive, onMounted, nextTick } from 'vue'
import goodsSelectPopup from '@/addon/shop/views/goods/components/goods-select-popup.vue'
import Sortable from 'sortablejs'
import { range } from 'lodash-es'

const diyStore: any = useDiyStore()
diyStore.editComponent.ignore = ['componentBgUrl'] // 忽略公共属性

// 组件验证
diyStore.editComponent.verify = (index: number) => {
    const res = { code: true, message: '' }
    diyStore.value[index].list.forEach((item: any) => {
        if (!item.title.text) {
            res.code = false
            res.message = t('activeCubeTitlePlaceholder')
            return res
        }
        if (!item.moreTitle.text) {
            res.code = false
            res.message = t('shopGoodsRecommendComponentTagPlaceholder')
            return res
        }
        if (!item.button.text) {
            res.code = false
            res.message = t('activeCubeButtonPlaceholder')
            return res
        }
    })

    if (diyStore.value[index].source == 'custom' && diyStore.value[index].goods_ids.length < diyStore.value[index].list.length) {
        res.code = false
        res.message = t('goodsPlaceholder')
        return res
    }
    return res
}

diyStore.editComponent.list.forEach((item: any) => {
    if (!item.id) item.id = diyStore.generateRandom()
})

const blockBoxRef = ref()

onMounted(() => {
    nextTick(() => {
        const sortable = Sortable.create(blockBoxRef.value, {
            group: 'item-wrap',
            animation: 200,
            onEnd: event => {
                const temp = diyStore.editComponent.list[event.oldIndex!]
                diyStore.editComponent.list.splice(event.oldIndex!, 1)
                diyStore.editComponent.list.splice(event.newIndex!, 0, temp)
                sortable.sort(
                    range(diyStore.editComponent.list.length).map(value => {
                        return value.toString()
                    })
                )
            }
        })

        let listNum = diyStore.editComponent.list.length;
        let goodsIdNum = diyStore.editComponent.goods_ids.length;
        diyStore.editComponent.goods_ids.splice(listNum, goodsIdNum);
    })
})

const addItem = () => {
    diyStore.editComponent.list.push({
        id: diyStore.generateRandom(),
        title: {
            text: '标题',
            textColor: '#303133'
        },
        moreTitle: {
            text: '精选',
            startColor: '#FF7234',
            endColor: '#FF213F'
        },
        listFrame: {
            startColor: '#FFE5E5',
            endColor: '#FFF5F0'
        },
        button: {
            text: "首单",
            textColor: "#FFFFFF",
            color: "#FF1128",
        },
        goodsId: [],
    })
}

const deleteTempFn = (index) => {
    diyStore.editComponent.list.splice(index, 1);
    diyStore.editComponent.goods_ids.splice(index, 1);
}

defineExpose({})

</script>

<style lang="scss" scoped></style>

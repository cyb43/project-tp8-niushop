<template>
    <!-- 内容 -->
    <div class="content-wrap" v-show="diyStore.editTab == 'content'">
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('selectStyle') }}</h3>
            <div class="flex items-center mb-[18px] rounded overflow-hidden">
                <span
                    class="iconfont iconzuoyoutuwenpc border-[1px] border-solid border-[#eee] cursor-pointer flex-1 flex items-center justify-center py-[5px]"
                    :class="{ 'border-[var(--el-color-primary)] text-[var(--el-color-primary)]': diyStore.editComponent.style == 'style-1' }"
                    @click="styleChangeFn('style-1')"></span>

                <span
                    class="iconfont iconshangxiatuwenpc border-[1px] border-solid border-[#eee] cursor-pointer flex-1 flex items-center justify-center py-[5px]"
                    :class="{ 'border-[var(--el-color-primary)] text-[var(--el-color-primary)]': diyStore.editComponent.style == 'style-2' }"
                    @click="styleChangeFn('style-2')"></span>
                <span
                    class="iconfont iconliebiaopc border-[1px] border-solid border-[#eee] cursor-pointer flex-1 flex items-center justify-center py-[5px]"
                    :class="{ 'border-[var(--el-color-primary)] text-[var(--el-color-primary)]': diyStore.editComponent.style == 'style-3' }"
                    @click="styleChangeFn('style-3')"></span>
            </div>
        </div>
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t("selectSource") }}</h3>
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('sortWay')">
                    <el-radio-group v-model="diyStore.editComponent.sortWay">
                        <el-radio label="default">{{ t('default') }}</el-radio>
                        <el-radio label="sale_num">{{ t('sales') }}</el-radio>
                        <el-radio label="price">{{ t('price') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item :label="t('goodsSelectPopupSelectGoodsButton')">
                    <el-radio-group v-model="diyStore.editComponent.source" :title="t('goodsSelectPopupSelectGoodsButton')">
                        <el-radio label="all">{{ t('goodsSelectPopupAllGoods') }}</el-radio>
                        <el-radio label="category">{{ t('selectCategory') }}</el-radio>
                        <el-radio label="custom">{{ t('manualSelectionSources') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item :label="t('selectCategory')" v-if="diyStore.editComponent.source == 'category'">
                    <div class="flex items-center w-full">
                        <div class="cursor-pointer ml-auto" @click="categoryShowDialogOpen">
                            <span class="text-[var(--el-color-primary)]">{{ diyStore.editComponent.goods_category_name }}</span>
                            <span class="iconfont iconxiangyoujiantou"></span>
                        </div>
                    </div>
                </el-form-item>
                <el-form-item :label="t('goodsNum')" v-if="diyStore.editComponent.source == 'all' || diyStore.editComponent.source == 'category'">
                    <el-slider class="goods-list-slider" show-input v-model="diyStore.editComponent.num" :min="1" max="20" size="small" />
                </el-form-item>
                <el-form-item :label="t('customGoods')" v-if="diyStore.editComponent.source == 'custom'">
                    <goods-select-popup ref="goodsSelectPopupRef" v-model="diyStore.editComponent.goods_ids" :min="1" :max="99" />
                </el-form-item>
            </el-form>

            <el-dialog v-model="categoryShowDialog" :title="t('goodsCategoryTitle')" width="750px" :destroy-on-close="true" :close-on-click-modal="false">
                <el-table :data="categoryTable.data" ref="categoryTableRef" size="large"
                          v-loading="categoryTable.loading"
                          height="450px" @selection-change="handleSelectionChange" row-key="category_id"
                          :expand-row-keys="expand_category_ids"
                          :tree-props="{ hasChildren: 'hasChildren', children: 'child_list' }">
                    <template #empty>
                        <span>{{ !categoryTable.loading ? t('emptyData') : '' }}</span>
                    </template>
                    <el-table-column type="selection" width="55" />
                    <el-table-column :label="t('categoryName')" min-width="120">
                        <template #default="{ row }">
                            <span class="order-2">{{ row.category_name }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column :label="t('categoryImage')" width="170" align="left">
                        <template #default="{ row }">
                            <div class="h-[30px]">
                                <el-image class="w-[30px] h-[30px] " :src="img(row.image)" fit="contain">
                                    <template #error>
                                        <div class="image-slot">
                                            <img class="w-[30px] h-[30px]" src="@/addon/shop/assets/category_default.png" />
                                        </div>
                                    </template>
                                </el-image>
                            </div>
                        </template>
                    </el-table-column>
                </el-table>
                <div class="flex items-center justify-end mt-[15px]">
                    <el-button type="primary" @click="saveCategoryId">{{ t('confirm') }}</el-button>
                    <el-button @click="categoryShowDialog = false">{{ t('cancel') }}</el-button>
                </div>
            </el-dialog>

        </div>

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t("goodsBuyBtn") }}</h3>
            <el-form label-width="90px" class="px-[10px]">
                <el-form-item :label="t('goodsBtnIsShow')">
                    <el-switch v-model="diyStore.editComponent.btnStyle.control" />
                </el-form-item>
                <el-form-item :label="t('goodsCartIncident')" v-if="diyStore.editComponent.btnStyle.control">
                    <el-radio-group v-model="diyStore.editComponent.btnStyle.cartEvent">
                        <el-radio label="detail">{{ t('goodsDetail') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item :label="t('goodsBtnStyle')" class="!items-center" v-if="diyStore.editComponent.btnStyle.control">
                    <div class="flex">
                        <template v-for="(item,index) in btnStyleList">
                            <div v-if=" item.isShow == true"
                                 class="cursor-pointer flex items-center justify-center border-[1px] border-solid border-transparent rounded-[6px] py-[5px] px-[8px] mr-[10px]"
                                 :class="{'!border-[var(--el-color-primary)]': diyStore.editComponent.btnStyle.style == item.value}">
                                <div v-if="item.type == 'icon'"
                                     :class="['nc-iconfont !text-[25px] text-[var(--el-color-primary)]', item.title]" @click="changeBtnStyle(item)"></div>
                                <div v-if="item.type == 'button'"
                                     class="leading-[1] text-[12px] px-[10px] py-[8px] text-[#fff] rounded-[20px] bg-[var(--el-color-primary)]"
                                     @click="changeBtnStyle(item)">{{ item.title }}</div>
                            </div>
                        </template>
                    </div>
                </el-form-item>
                <el-form-item :label="t('goodsBtnText')"
                              v-if="diyStore.editComponent.btnStyle.control && diyStore.editComponent.btnStyle.style == 'button'">
                    <el-input v-model.trim="diyStore.editComponent.btnStyle.text"
                              :placeholder="t('goodsBtnTextPlaceholder')" clearable maxlength="4" show-word-limit />
                </el-form-item>
            </el-form>
        </div>


        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t("goodsShowContent") }}</h3>
            <el-form label-width="90px" class="px-[10px]">
                <el-form-item :label="t('goodsSelectPopupGoodsName')" v-if="diyStore.editComponent.goodsNameStyle.isShow">
                    <el-switch v-model="diyStore.editComponent.goodsNameStyle.control" />
                </el-form-item>
                <el-form-item :label="t('goodsPriceColor')" v-if="diyStore.editComponent.priceStyle.isShow">
                    <el-switch v-model="diyStore.editComponent.priceStyle.control" />
                </el-form-item>
                <el-form-item :label="t('goodsSaleNum')" v-if="diyStore.editComponent.saleStyle.isShow">
                    <el-switch v-model="diyStore.editComponent.saleStyle.control" />
                </el-form-item>
                <el-form-item :label="t('goodsLabel')" v-if="diyStore.editComponent.labelStyle.isShow">
                    <el-switch v-model="diyStore.editComponent.labelStyle.control" />
                </el-form-item>
            </el-form>
        </div>
    </div>

    <!-- 样式 -->
    <div class="style-wrap" v-show="diyStore.editTab == 'style'">
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('goodsStyle') }}</h3>
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('goodsBgColor')">
                    <el-color-picker v-model="diyStore.editComponent.elementBgColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
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
                <el-form-item :label="t('goodsPriceColor')">
                    <el-color-picker v-model="diyStore.editComponent.priceStyle.color" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('goodsSaleColor')">
                    <el-color-picker v-model="diyStore.editComponent.saleStyle.color" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('topRounded')">
                    <el-slider v-model="diyStore.editComponent.topElementRounded" show-input size="small" class="ml-[10px] diy-nav-slider" :max="50" />
                </el-form-item>
                <el-form-item :label="t('bottomRounded')">
                    <el-slider v-model="diyStore.editComponent.bottomElementRounded" show-input size="small" class="ml-[10px] diy-nav-slider" :max="50" />
                </el-form-item>
            </el-form>
        </div>

        <div class="edit-attr-item-wrap" v-if="diyStore.editComponent.btnStyle.control">
            <h3 class="mb-[10px]">{{ t("goodsBuyBtn") }}</h3>
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('goodsIsBold')" v-if="diyStore.editComponent.btnStyle.style == 'button'">
                    <el-switch v-model="diyStore.editComponent.btnStyle.fontWeight" />
                </el-form-item>
                <el-form-item :label="t('goodsTextColor')">
                    <el-color-picker v-model="diyStore.editComponent.btnStyle.textColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('listFrameColor')">
                    <el-color-picker v-model="diyStore.editComponent.btnStyle.startBgColor" show-alpha :predefine="diyStore.predefineColors" />
                    <icon name="iconfont iconmap-connect" size="20px" class="block !text-gray-400 mx-[5px]" />
                    <el-color-picker v-model="diyStore.editComponent.btnStyle.endBgColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('goodsRounded')" v-if="diyStore.editComponent.btnStyle.style == 'button'">
                    <el-slider v-model="diyStore.editComponent.btnStyle.aroundRadius" show-input size="small" class="ml-[10px] diy-nav-slider" :max="50" />
                </el-form-item>
            </el-form>
        </div>

        <!-- 组件样式 -->
        <slot name="style"></slot>
    </div>
</template>

<script lang="ts" setup>
import { getCategoryTree } from '@/addon/shop/api/goods'
import { t } from '@/lang'
import { img } from '@/utils/common'
import useDiyStore from '@/stores/modules/diy'
import { ref, reactive, onMounted, nextTick } from 'vue'
import { ElTable } from 'element-plus'
import goodsSelectPopup from '@/addon/shop/views/goods/components/goods-select-popup.vue'

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

const categoryShowDialog = ref(false)

const categoryTable = reactive({
    loading: true,
    data: [],
})
onMounted(() => {
    loadCategoryList()
})


const styleChangeFn = (style) => {
    btnStyleList.forEach((item, index, arr) => {
        if (item.type == "button") {
            if (style == "style-3") {
                item.isShow = false;
            } else {
                item.isShow = true;
            }
        }
    })

    if (style == "style-3") {
        diyStore.editComponent.btnStyle.style = btnStyleList[1].value
    } else {
        diyStore.editComponent.btnStyle.style = btnStyleList[0].value
    }

    if (style == "style-3") {
        diyStore.editComponent.saleStyle.isShow = false;
        diyStore.editComponent.labelStyle.isShow = false;
    } else {
        diyStore.editComponent.saleStyle.isShow = true;
        diyStore.editComponent.labelStyle.isShow = true;
    }

    diyStore.editComponent.style = style;
}

const btnStyleList = reactive([
    {
        isShow: true,
        type: 'button',
        title: diyStore.editComponent.btnStyle.text,
        value: 'button'
    },
    {
        isShow: true,
        type: 'icon',
        title: 'nc-icon-jiahaoV6xx',
        value: 'nc-icon-jiahaoV6xx'
    },
    {
        isShow: true,
        type: 'icon',
        title: 'nc-icon-gouwuche1',
        value: 'nc-icon-gouwuche1'
    }
])

const changeBtnStyle = (item: any) => {
    diyStore.editComponent.btnStyle.style = item.value
}

const categoryTableRef = ref<InstanceType<typeof ElTable>>()
/**
 * 获取商品分类列表
 */
let currCategoryData: any = null
const loadCategoryList = () => {
    categoryTable.loading = true

    getCategoryTree().then(res => {
        categoryTable.loading = false
        categoryTable.data = res.data
    }).catch(() => {
        categoryTable.loading = false
    })
}

// 选择商品分类
const handleSelectionChange = (val: string | any[]) => {
    let data = ''
    if (val) data = val[val.length - 1]
    if (val.length > 1) categoryTableRef.value!.clearSelection()
    if (data) categoryTableRef.value!.toggleRowSelection(data, true)
    currCategoryData = data
}

const saveCategoryId = () => {
    diyStore.editComponent.goods_category = currCategoryData.category_id
    diyStore.editComponent.goods_category_name = currCategoryData.category_name;
    categoryShowDialog.value = false
}

const categoryShowDialogOpen = () => {
    categoryShowDialog.value = true
    nextTick(() => {
        setRowSelection()
    })
}

//分类数据选中回填,设置展开行
const expand_category_ids = ref<Array<any>>([])
const setRowSelection = () => {
    expand_category_ids.value = []
    categoryTable.data.forEach((el: any) => {
        if (diyStore.editComponent.goods_category == el.category_id) {
            categoryTableRef.value!.toggleRowSelection(el, true)
        } else if (el.child_list && el.child_list.length) {
            el.child_list.forEach((v: any) => {
                if (diyStore.editComponent.goods_category == v.category_id) {
                    expand_category_ids.value.push(el.category_id.toString())
                    categoryTableRef.value!.toggleRowSelection(v, true)
                }
            })
        }
    })
}

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

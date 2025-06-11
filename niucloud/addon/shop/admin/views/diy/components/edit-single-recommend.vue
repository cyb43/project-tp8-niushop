<template>
    <!-- 内容 -->
    <div class="content-wrap" v-show="diyStore.editTab == 'content'">
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('titleContent') }}</h3>
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('selectStyle')" class="flex">
                    <span class="text-primary flex-1 cursor-pointer" @click="showTitleStyle">{{ diyStore.editComponent.titleStyle.title }}</span>
                    <el-icon>
                        <ArrowRight />
                    </el-icon>
                </el-form-item>
                <el-form-item :label="t('image')">
                    <upload-image v-model="diyStore.editComponent.textImg" :limit="1" />
                </el-form-item>
                <el-form-item :label="t('link')">
                    <diy-link v-model="diyStore.editComponent.textLink" />
                </el-form-item>
                <el-form-item :label="t('subTitle')">
                    <el-input v-model.trim="diyStore.editComponent.subTitle.text" :placeholder="t('subTitlePlaceholder')" clearable maxlength="8" show-word-limit />
                </el-form-item>
                <el-form-item :label="t('link')">
                    <diy-link v-model="diyStore.editComponent.subTitle.link" />
                </el-form-item>
            </el-form>

            <el-dialog v-model="showTitleDialog" :title="t('selectStyle')" width="460px">

                <div class="flex flex-wrap">
                    <template v-for="(item,index) in titleStyleList" :key="index">
                        <div :class="{ 'border-primary': selectTitleStyle.value == item.value }"
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
            <h3 class="mb-[10px]">{{ t("selectSource") }}</h3>
            <el-form label-width="80px" class="px-[10px]">
                <el-form-item :label="t('goodsSelectPopupSelectGoodsButton')">
                    <el-radio-group v-model="diyStore.editComponent.source" :title="t('goodsSelectPopupSelectGoodsButton')">
                        <el-radio label="all">{{ t('defaultGoodsSelect') }}</el-radio>
                        <el-radio label="custom">{{ t('manualSelectionSources') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item :label="t('customGoods')" v-if="diyStore.editComponent.source == 'custom'">
                    <goods-select-popup ref="goodsSelectPopupRef" v-model="diyStore.editComponent.goods_ids" :min="1" :max="1" />
                </el-form-item>
            </el-form>
        </div>

        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('imageSet') }}</h3>
            <el-form label-width="80px" class="px-[10px]">

                <div ref="imageBoxRef">
                    <div v-for="(item,index) in diyStore.editComponent.list" :key="item.id" class="item-wrap p-[10px] pb-0 relative border border-dashed border-gray-300 mb-[16px]">
                        <el-form-item :label="t('image')">
                            <upload-image v-model="item.imageUrl" :limit="1" />
                        </el-form-item>

                        <div class="del absolute cursor-pointer z-[2] top-[-8px] right-[-8px]"
                             v-show="diyStore.editComponent.list.length > 1"
                             @click="diyStore.editComponent.list.splice(index,1)">
                            <icon name="element CircleCloseFilled" color="#bbb" size="20px" />
                        </div>

                        <el-form-item :label="t('link')">
                            <diy-link v-model="item.link" />
                        </el-form-item>
                    </div>
                </div>

                <el-button v-show="diyStore.editComponent.list.length < 10" class="w-full" @click="addImageAd">{{ t('addImageAd') }}</el-button>

            </el-form>
        </div>

        <el-dialog v-model="categoryShowDialog" :title="t('goodsCategoryTitle')" width="750px" :destroy-on-close="true" :close-on-click-modal="false">
            <el-table :data="categoryTable.data" ref="categoryTableRef" size="large" v-loading="categoryTable.loading"
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

    <!-- 样式 -->
    <div class="style-wrap" v-show="diyStore.editTab == 'style'">
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('titleStyle') }}</h3>
            <el-form label-width="90px" class="px-[10px]">
                <el-form-item :label="t('textColor')">
                    <el-color-picker v-model="diyStore.editComponent.subTitle.textColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
            </el-form>
        </div>
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('carouselStyle') }}</h3>
            <el-form label-width="90px" class="px-[10px]">
                <el-form-item :label="t('topRounded')">
                    <el-slider v-model="diyStore.editComponent.topCarouselRounded" show-input size="small" class="ml-[10px] diy-nav-slider" :max="50" />
                </el-form-item>
                <el-form-item :label="t('bottomRounded')">
                    <el-slider v-model="diyStore.editComponent.bottomCarouselRounded" show-input size="small" class="ml-[10px] diy-nav-slider" :max="50" />
                </el-form-item>
            </el-form>
        </div>
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('recommendIndicatorStyle') }}</h3>
            <el-form label-width="90px" class="px-[10px]">
                <el-form-item :label="t('recommendIndicatorColor')">
                    <el-color-picker v-model="diyStore.editComponent.indicatorColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('recommendIndicatorActiveColor')">
                    <el-color-picker v-model="diyStore.editComponent.indicatorActiveColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
            </el-form>
        </div>
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
                <el-form-item :label="t('goodsPriceColor')">
                    <el-color-picker v-model="diyStore.editComponent.priceStyle.mainColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('goodsBtnColor')">
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
diyStore.editComponent.ignore = ['componentBgUrl'] // 忽略公共属性

const selectTitleStyle = reactive({
    title: diyStore.editComponent.titleStyle.title,
    value: diyStore.editComponent.titleStyle.value
})

// 标题风格样式
const showTitleDialog = ref(false)

const showTitleStyle = () => {
    selectTitleStyle.title = diyStore.editComponent.titleStyle.title;
    selectTitleStyle.value = diyStore.editComponent.titleStyle.value;
    showTitleDialog.value = true
}

const changeTitleStyle = (item: any) => {
    selectTitleStyle.title = item.title;
    selectTitleStyle.value = item.value;
}

const confirmTitleStyle = () => {
    diyStore.editComponent.titleStyle.title = selectTitleStyle.title;
    diyStore.editComponent.titleStyle.value = selectTitleStyle.value;
    showTitleDialog.value = false
}

const titleStyleList = reactive([
    {
        url: 'addon/shop/diy/single_recommend/title_style_01.png',
        title: '风格1',
        value: 'style-1'
    }
])

const addImageAd = () => {
    diyStore.editComponent.list.push({
        id: diyStore.generateRandom(),
        imageUrl: '',
        imgWidth: 0,
        imgHeight: 0,
        link: { name: '' }
    })
}

// 组件验证
diyStore.editComponent.verify = (index: number) => {
    const res = { code: true, message: '' }
    if (diyStore.value[index].source == 'custom') {
        if (diyStore.value[index].goods_ids.length == 0) {
            res.code = false
            res.message = t('goodsPlaceholder')
        }
    }

    diyStore.value[index].list.forEach((item, index) => {
        if (item.imageUrl === '') {
            res.code = false
            res.message = t('imageUrlTip')
            return res
        }
    });

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

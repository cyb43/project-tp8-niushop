<template>
    <!-- 内容 -->
    <div class="content-wrap goods-detail-basic-info" v-show="diyStore.editTab == 'content'">
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('顶部导航') }}</h3>
            <el-form label-width="100px" class="px-[10px]">
                <el-form-item :label="t('菜单内容')">
                    <el-checkbox-group v-model="menuContent" :min="2" @change="menuContentChange">
                        <el-checkbox label="首页" value="index" />
                        <el-checkbox label="搜索" value="search" />
                        <el-checkbox label="购物车" value="cart" />
                        <el-checkbox label="个人中心" value="member"/>
                        <el-checkbox label="我的收藏" value="collect" />
                        <el-checkbox label="商品列表" value="goods_list"/>
                        <el-checkbox label="商品分类" value="goods_category"/>
                    </el-checkbox-group>
                    <div class="text-sm text-gray-400">{{ t('菜单内容最少选择2个') }}</div>
                </el-form-item>
            </el-form>
        </div>
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('商品媒体') }}</h3>
            <el-form label-width="100px" class="px-[10px]">
                <el-form-item :label="t('商品主图')">
                    <el-radio-group v-model="diyStore.editComponent.medium.type">
                        <el-radio label="square_img">{{ t('固定方图') }}</el-radio>
                        <el-radio label="height_adaptive">{{ t('高度自适应') }}</el-radio>
                        <div class="text-sm text-gray-400 leading-[1.6]">{{ t('注意：高度自适应指商品主图的高度与第一张商品图的高度保持一致') }}</div>
                    </el-radio-group>
                </el-form-item>
                <el-form-item :label="t('轮播点')">
                    <el-radio-group v-model="diyStore.editComponent.medium.indicator">
                        <el-radio :label="true">{{ t('显示') }}</el-radio>
                        <el-radio :label="false">{{ t('隐藏') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
            </el-form>
        </div>
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('价格展示') }}</h3>
            <el-form label-width="100px" class="px-[10px]">
                <el-form-item :label="t('展示方式')">
                    <el-radio-group v-model="diyStore.editComponent.priceRegion.showWay">
                        <el-radio label="normal">{{ t('常规') }}</el-radio>
                        <el-radio label="fixed">{{ t('置顶') }}</el-radio>
                    </el-radio-group>
                </el-form-item>
            </el-form>
        </div>
        <div class="edit-attr-item-wrap" v-if="diyStore.editComponent.priceRegion.showWay == 'fixed'">
            <h3 class="mb-[10px]">{{ t('普通价格背景图') }}</h3>
            <el-form label-width="100px" class="px-[10px]">
                <el-form-item :label="t('风格选择')" class="flex">
                    <span class="text-primary flex-1 cursor-pointer" @click="showGoodsStyle">{{ diyStore.editComponent.priceRegion.goodsStyle.title }}</span>
                    <el-icon>
                        <ArrowRight />
                    </el-icon>
                </el-form-item>
                <el-form-item :label="t('背景图片')">
                    <upload-image v-model="diyStore.editComponent.priceRegion.bgImg" :limit="1" />
                    <div class="text-sm text-gray-400">{{ t('背景图片推荐尺寸：750*148') }}</div>
                </el-form-item>
            </el-form>
        </div>
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('活动价格背景图') }}</h3>
            <el-form label-width="100px" class="px-[10px]">
                <div class="text-sm text-gray-400 mb-[10px]">{{ t('适用场景：限时折扣、新人专享、会员价') }}</div>
                <el-form-item :label="t('风格选择')" class="flex">
                    <span class="text-primary flex-1 cursor-pointer" @click="showMarketingStyle">{{ diyStore.editComponent.priceRegion.marketingStyle.title }}</span>
                    <el-icon>
                        <ArrowRight />
                    </el-icon>
                </el-form-item>
                <el-form-item :label="t('背景图片')">
                    <upload-image v-model="diyStore.editComponent.priceRegion.marketingBgImg" :limit="1" />
                    <div class="text-sm text-gray-400">{{ t('背景图片推荐尺寸：750*148') }}</div>
                </el-form-item>
            </el-form>
        </div>
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('销售信息') }}</h3>
            <el-form label-width="100px" class="px-[10px]">
                <el-form-item :label="t('是否展示')">
                    <el-checkbox-group v-model="saleInfo" @change="saleInfoChange">
                        <el-checkbox label="划线价" value="underlined_price" />
                        <el-checkbox label="累计销量" value="sales" />
                        <el-checkbox label="库存" value="stock" />
                    </el-checkbox-group>
                </el-form-item>
            </el-form>
        </div>
        <!-- 普通商品风格 -->
        <el-dialog v-model="goodsStyleDialog" :title="t('selectStyle')" width="460px">
            <div class="flex flex-wrap">
                <template v-for="(item,index) in Object.values(goodsStyleList)" :key="index">
                    <div :class="{ 'border-primary': selectGoodsStyle.value == item.value }"
                            @click="changeGoodsStyle(item)"
                            class="flex items-center justify-center overflow-hidden w-[200px] h-[100px] mr-[12px] mb-[12px] cursor-pointer border bg-[#eee]">
                        <img :src="img(item.url)" />
                    </div>
                </template>
            </div>
            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="goodsStyleDialog = false">{{ t('cancel') }}</el-button>
                    <el-button type="primary" @click="confirmGoodsStyle">{{ t('confirm') }}</el-button>
                </span>
            </template>
        </el-dialog>

        <!-- 营销活动风格 -->
        <el-dialog v-model="marketingStyleDialog" :title="t('selectStyle')" width="460px">
            <div class="flex flex-wrap">
                <template v-for="(item,index) in Object.values(marketingGoodsStyleList)" :key="index">
                    <div :class="{ 'border-primary': selectMarketingStyle.value == item.value }"
                            @click="changeMarketingStyle(item)"
                            class="flex items-center justify-center overflow-hidden w-[200px] h-[100px] mr-[12px] mb-[12px] cursor-pointer border bg-[#eee]">
                        <img :src="img(item.url)" />
                    </div>
                </template>
            </div>
            <template #footer>
                <span class="dialog-footer">
                    <el-button @click="marketingStyleDialog = false">{{ t('cancel') }}</el-button>
                    <el-button type="primary" @click="confirmMarketingStyle">{{ t('confirm') }}</el-button>
                </span>
            </template>
        </el-dialog>
    </div>

    <!-- 样式 -->
    <div class="style-wrap" v-show="diyStore.editTab == 'style'">
        <!-- 价格模块样式 -->
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('价格样式') }}</h3>
            <div class="text-sm text-gray-400 mb-[10px] leading-[1.6]">{{ t('仅在普通价格置顶时或营销活动开启的情况下生效') }}</div>
            <el-form label-width="100px" class="px-[10px]">
                <el-form-item :label="t('价格背景色')">
                    <el-color-picker v-model="diyStore.editComponent.goodsInfo.priceBgColor" />
                </el-form-item>
                <el-form-item :label="t('价格上圆角')">
                    <el-slider v-model="diyStore.editComponent.goodsInfo.priceTopRounded" show-input size="small" class="ml-[10px] diy-nav-slider" :min="0" :max="100" />
                </el-form-item>
                <el-form-item :label="t('价格下圆角')">
                    <el-slider v-model="diyStore.editComponent.goodsInfo.priceBottomRounded" show-input size="small" class="ml-[10px] diy-nav-slider" :min="0" :max="100" />
                </el-form-item>
                <el-form-item :label="t('上边距')">
                    <el-slider v-model="diyStore.editComponent.goodsInfo.priceTopMargin" show-input size="small" class="ml-[10px] diy-nav-slider" :min="-50" :max="50" />
                </el-form-item>
                <el-form-item :label="t('左右边距')">
                    <el-slider v-model="diyStore.editComponent.goodsInfo.priceAboutMargin" show-input size="small" class="ml-[10px] diy-nav-slider" :min="0" :max="20" />
                </el-form-item>
            </el-form>
        </div>
        <!-- 商品信息样式 -->
        <div class="edit-attr-item-wrap">
            <h3 class="mb-[10px]">{{ t('商品信息样式') }}</h3>
            <el-form label-width="100px" class="px-[10px]">
                <el-form-item :label="t('标题颜色')">
                    <el-color-picker v-model="diyStore.editComponent.goodsInfo.titleColor" />
                </el-form-item>
                <el-form-item :label="t('副标题颜色')">
                    <el-color-picker v-model="diyStore.editComponent.goodsInfo.subTitleColor" />
                </el-form-item>
                <el-form-item :label="t('销售信息色')">
                    <el-color-picker v-model="diyStore.editComponent.goodsInfo.saleInfoColor" />
                </el-form-item>
                <el-form-item :label="t('商品背景色')">
                    <el-color-picker v-model="diyStore.editComponent.goodsInfo.startBgColor" show-alpha :predefine="diyStore.predefineColors" />
                    <icon name="iconfont iconmap-connect" size="20px" class="block !text-gray-400 mx-[5px]" />
                    <el-color-picker v-model="diyStore.editComponent.goodsInfo.endBgColor" show-alpha :predefine="diyStore.predefineColors" />
                </el-form-item>
                <el-form-item :label="t('商品上圆角')">
                    <el-slider v-model="diyStore.editComponent.goodsInfo.topRounded" show-input size="small" class="ml-[10px] diy-nav-slider" :min="0" :max="100" />
                </el-form-item>
                <el-form-item :label="t('商品下圆角')">
                    <el-slider v-model="diyStore.editComponent.goodsInfo.bottomRounded" show-input size="small" class="ml-[10px] diy-nav-slider" :min="0" :max="100" />
                </el-form-item>
                <el-form-item :label="t('上边距')">
                    <el-slider v-model="diyStore.editComponent.goodsInfo.topMargin" show-input size="small" class="ml-[10px] diy-nav-slider" :min="-50" :max="50" />
                </el-form-item>
                <el-form-item :label="t('左右边距')">
                    <el-slider v-model="diyStore.editComponent.goodsInfo.aboutMargin" show-input size="small" class="ml-[10px] diy-nav-slider" :min="0" :max="20" />
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
import { ref, reactive, onMounted } from 'vue'

const diyStore: any = useDiyStore()
diyStore.editComponent.ignore = ['pageBgColor', 'componentBgUrl', 'marginTop', 'marginBottom', 'marginBoth', 'topRounded', 'bottomRounded'] // 忽略公共属性

// 组件验证
diyStore.editComponent.verify = (index: number) => {
    const res = { code: true, message: '' }

    if (diyStore.value[index].priceRegion.showWay == 'fixed') {
        if (diyStore.value[index].priceRegion.bgImg.length == 0) {
            res.code = false
            res.message = t('请上传普通价格的置顶背景图')
        }
    }
    return res
}

const initFn = () => {
    if (diyStore.editComponent.menuContent) {
        menuContent.value = (typeof diyStore.editComponent.menuContent == 'object') ? diyStore.editComponent.menuContent : diyStore.editComponent.menuContent.split(',')
    }
    if (diyStore.editComponent.saleInfo) {
        saleInfo.value = (typeof diyStore.editComponent.saleInfo == 'object') ? diyStore.editComponent.saleInfo : diyStore.editComponent.saleInfo.split(',')
    }
}

// 菜单内容
const menuContent = ref([])
const menuContentChange = (val: any) => {
    diyStore.editComponent.menuContent = val
}

// 销售信息
const saleInfo = ref([])
const saleInfoChange = (val: any) => {
    diyStore.editComponent.saleInfo = val
}

// 普通商品风格
const goodsStyleDialog = ref(false)
const goodsStyleList = reactive({
    'style-1': {
        url: 'addon/shop/diy/goods_detail/style_01.jpg',
        title: '风格1',
        value: 'style-1'
    },
    'style-2': {
        url: 'addon/shop/diy/goods_detail/style_02.jpg',
        title: '风格2',
        value: 'style-2'
    },
    'style-3': {
        url: 'addon/shop/diy/goods_detail/style_03.jpg',
        title: '风格3',
        value: 'style-3'
    },
    'style-4': {
        url: 'addon/shop/diy/goods_detail/style_04.jpg',
        title: '风格4',
        value: 'style-4'
    }
})
const selectGoodsStyle = reactive({
    title: '风格1',
    url: 'addon/shop/diy/goods_detail/style_01.jpg',
    value: 'style-1'
})

const showGoodsStyle = () => {
    selectGoodsStyle.title = diyStore.editComponent.priceRegion.goodsStyle.title
    selectGoodsStyle.value = diyStore.editComponent.priceRegion.goodsStyle.value
    selectGoodsStyle.url = diyStore.editComponent.priceRegion.bgImg
    goodsStyleDialog.value = true
}

const changeGoodsStyle = (item: any) => {
    selectGoodsStyle.title = item.title
    selectGoodsStyle.value = item.value
    selectGoodsStyle.url = item.url
}

const confirmGoodsStyle = () => {
    diyStore.editComponent.priceRegion.goodsStyle.title = selectGoodsStyle.title
    diyStore.editComponent.priceRegion.goodsStyle.value = selectGoodsStyle.value
    diyStore.editComponent.priceRegion.bgImg = selectGoodsStyle.url || goodsStyleList[selectGoodsStyle.value].url
    goodsStyleDialog.value = false
}

// 营销商品风格
const marketingStyleDialog = ref(false)
const marketingGoodsStyleList = reactive({
    'style-1': {
        url: 'addon/shop/diy/goods_detail/marketing_style_01.jpg',
        title: '风格1',
        value: 'style-1'
    },
    'style-2': {
        url: 'addon/shop/diy/goods_detail/marketing_style_02.jpg',
        title: '风格2',
        value: 'style-2'
    }
})
const selectMarketingStyle = reactive({
    title: '风格1',
    url: 'addon/shop/diy/goods_detail/marketing_style_01.jpg',
    value: 'style-1'
})

const showMarketingStyle = () => {
    selectMarketingStyle.title = diyStore.editComponent.priceRegion.marketingStyle.title
    selectMarketingStyle.value = diyStore.editComponent.priceRegion.marketingStyle.value
    selectMarketingStyle.url = diyStore.editComponent.priceRegion.marketingBgImg
    marketingStyleDialog.value = true
}

const changeMarketingStyle = (item: any) => {
    selectMarketingStyle.title = item.title
    selectMarketingStyle.value = item.value
    selectMarketingStyle.url = item.url
}

const confirmMarketingStyle = () => {
    diyStore.editComponent.priceRegion.marketingStyle.title = selectMarketingStyle.title
    diyStore.editComponent.priceRegion.marketingStyle.value = selectMarketingStyle.value
    diyStore.editComponent.priceRegion.marketingBgImg = selectMarketingStyle.url || marketingGoodsStyleList[selectMarketingStyle.value].url
    marketingStyleDialog.value = false
}

onMounted(() => {
    initFn()
})

defineExpose({})

</script>

<style lang="scss" scoped></style>

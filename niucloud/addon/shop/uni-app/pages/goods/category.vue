<template>
    <view :style="themeColor()">
        <category-template-one-one class="category" v-if="config.level===1 && config.template === 'style-1'" :categoryId="categoryId" :config="config" />
        <category-template-two-one v-if="config.level===2 && config.template === 'style-1'" :categoryId="categoryId" :config="config" />
        <category-template-two-two class="category" v-if="config.level===2 && config.template === 'style-2'" :categoryId="categoryId" :config="config" />
    </view>
</template>
<script setup lang="ts">
import { onLoad, onShow } from '@dcloudio/uni-app'
import { ref } from 'vue';
import categoryTemplateTwoOne from '@/addon/shop/pages/goods/components/category-template-two-one.vue';
import categoryTemplateOneOne from '@/addon/shop/pages/goods/components/category-template-one-one.vue';
import categoryTemplateTwoTwo from '@/addon/shop/pages/goods/components/category-template-two-two.vue';
import { getGoodsCategoryConfig } from '@/addon/shop/api/goods';
import useCartStore from '@/addon/shop/stores/cart'

const cartStore = useCartStore();
const config: any = ref({})
const categoryId = ref(0)

const getGoodsCategoryConfigFn = () => {
    getGoodsCategoryConfig().then(res => {
        config.value = res.data
        uni.setNavigationBarTitle({
            title: config.value.page_title
        });
    })
}

onLoad((options: any) => {
    categoryId.value = options.category_id || 0;
    getGoodsCategoryConfigFn()
});

onShow(() => {
    // 查询购物车列表
    cartStore.getList();
})

</script>
<style>

/*  #ifdef  H5  */
:deep(.category .detail .mescroll-body) {
    padding-bottom: calc(50px + constant(safe-area-inset-bottom)) !important;
    padding-bottom: calc(50px + env(safe-area-inset-bottom)) !important;
}

:deep(.category .cart .mescroll-body) {
    padding-bottom: calc(100rpx + 50px + constant(safe-area-inset-bottom)) !important;
    padding-bottom: calc(100rpx + 50px + env(safe-area-inset-bottom)) !important;
}

/*  #endif  */
/*  #ifndef  H5  */
.category .detail .mescroll-body {
    padding-bottom: calc(100rpx + constant(safe-area-inset-bottom)) !important;
    padding-bottom: calc(100rpx + env(safe-area-inset-bottom)) !important;
}

.category .cart .mescroll-body {
    padding-bottom: calc(200rpx + constant(safe-area-inset-bottom)) !important;
    padding-bottom: calc(200rpx + env(safe-area-inset-bottom)) !important;
}

/*
.category .labelPopup :deep(.u-fade-enter-active) {
	top: 92rpx !important;
	left: 166rpx !important;
	z-index: 8 !important;
}
.category .labelPopup :deep(.u-slide-down-enter-active){
	top: 92rpx !important;
	left: 165rpx !important;
	z-index: 8 !important;
}
.category .labelPopup.active :deep(.u-fade-enter-active) {
	top: 190rpx !important;

}
.category .labelPopup.active :deep(.u-slide-down-enter-active){
	top: 190rpx !important;
}
.category .labelPopup :deep(.u-fade-enter-to){
	top: 92rpx !important;
	left: 166rpx !important;
	z-index: 8 !important;
}
.category .labelPopup :deep(.u-slide-down-leave-to){
	top: 92rpx !important;
	left: 165rpx !important;
	z-index: 8 !important;
} */
.category .labelPopup :deep(.u-transition) {
    top: 96rpx !important;
    left: 170rpx !important;
    z-index: 8 !important;

    &:nth-child(2) {
        left: 168rpx !important;
    }
}

.category .labelPopup.active :deep(.u-transition) {
    top: 190rpx !important;
}

/*  #endif  */

</style>
<style lang="scss" scoped>
:deep(.mescroll-upwarp) {
    box-sizing: border-box;
    padding-left: 182rpx;
}

:deep(.tab-bar-placeholder) {
    display: none !important;
}

:deep(.u-tabbar__placeholder) {
    display: none !important;
}

button::after {
    border: 0 !important;
}

:deep(.part .mescroll-empty) {
    width: 542rpx;
    height: 542rpx;
    margin-top: 0;
    margin-left: 0;
    margin-right: 0;
    padding-top: 50rpx;
}
</style>

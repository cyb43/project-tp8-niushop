<template>
    <view class=" bg-[var(--page-bg-color)] overflow-hidden min-h-screen">
        <view class="mescroll-box" v-if="tabsData.length">
            <view v-if="config.search.control" class="search-box box-border z-10 fixed top-0 left-0 right-0  h-[100rpx] bg-[#fff]" :style="{'top': systemStore.topTabbarInfo.fullHeight || 0}">
                <view class="flex-1 search-input">
                    <text @click.stop="searchNameFn" class="nc-iconfont nc-icon-sousuo-duanV6xx1 btn"></text>
                    <input class="input" type="text" v-model.trim="searchName" :placeholder="config.search.title" placeholderClass="text-[var(--text-color-light9)]" @confirm="searchNameFn">
                    <text v-if="searchName" class="nc-iconfont nc-icon-cuohaoV6xx1 clear" @click="searchName=''"></text>
                </view>
            </view>
            <view class="tabs-box z-2 fixed left-0 bg-[#fff] bottom-[50px] top-0" :style="tabsBoxCss">
                <scroll-view :scroll-y="true" class="scroll-height">
                    <view class="bg-[var(--temp-bg)]">
                        <view class="tab-item"
                              :class="{ 'tab-item-active': index == tabActive,'rounded-br-[12rpx]':tabActive-1===index,'rounded-tr-[12rpx]':tabActive+1===index  }"
                              v-for="(item, index) in tabsData" :key="index" @click="firstLevelClick(index, item)">
                            <view class="text-box text-[26rpx] text-left leading-[1.3] break-words px-[16rpx]">{{ item.category_name }}</view>
                        </view>
                    </view>
                </scroll-view>
            </view>
            <scroll-view class="h-[100vh]" :scroll-y="true">
                <view class="pl-[188rpx] scroll-ios pt-[20rpx] pr-[20rpx]" :class="{ '!pt-[120rpx]': config.search.control }">
                    <view class="bg-[#fff] grid grid-cols-3 gap-x-[50rpx] gap-y-[32rpx] py-[32rpx] px-[24rpx] rounded-[var(--rounded-big)]"
                        v-if="tabsData[tabActive]?.child_list && !loading">
                        <template v-for="(item, index) in tabsData[tabActive]?.child_list" :key="item.category_id">
                            <view @click="toLink(item.category_id)" class=" flex items-center justify-center flex-col">
                                <u--image radius="var(--goods-rounded-big)" width="120rpx" height="120rpx" :src="img(item.image ? item.image : '')" model="aspectFill">
                                    <template #error>
                                        <image class="rounded-[var(--goods-rounded-big)] overflow-hidden w-[120rpx] h-[120rpx]" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill" />
                                    </template>
                                </u--image>
                                <view class="text-[24rpx] text-center mt-[16rpx] leading-[34rpx]">{{item.category_name }}</view>
                            </view>
                        </template>
                    </view>
                    
                    <mescroll-empty class="part" v-if="!tabsData[tabActive]?.child_list && !loading" :option="{tip : '暂无商品分类'}"></mescroll-empty>
                </view>
            </scroll-view>
        </view>
        <tabbar />
        <mescroll-empty v-if="!tabsData.length && !loading" :option="{tip : '暂无商品分类'}"></mescroll-empty>
        <loading-page :loading="loading"></loading-page>
    </view>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { img, redirect } from '@/utils/common';
import { getGoodsCategoryTree } from '@/addon/shop/api/goods';
import MescrollEmpty from '@/components/mescroll/mescroll-empty/mescroll-empty.vue';
import { t } from '@/locale';
import useSystemStore from '@/stores/system';
const systemStore = useSystemStore()

const prop = defineProps({
    config: {
        type: Object,
        default: (() => {
            return {}
        })
    },
    categoryId: {
        type: [String, Number],
        default: 0
    }
})
let config = prop.config
let categoryId = prop.categoryId;
const searchName = ref("");
const loading = ref<boolean>(true);

onMounted(() => {
    getCategoryData()
})

const tabsBoxCss = computed(() => {
    let style = ''
    if(config.search.control){
        style += `top: calc(${ systemStore.topTabbarInfo.height || 0 }px + 100rpx);`
    }else{
        style += `top: ${ systemStore.topTabbarInfo.height || 0 }px;`
    }
    return style
})

/**
 * @description 获取分类数据
 * */
const tabsData: any = ref<Array<Object>>([])
const getCategoryData = () => {
    loading.value = true;
    getGoodsCategoryTree().then((res: any) => {
        tabsData.value = res.data;
        if (categoryId) {
            for (let i = 0; i < tabsData.value.length; i++) {
                if (tabsData.value[i].category_id == categoryId) {
                    tabActive.value = i;
                    break;
                }
                if (tabsData.value[i].child_list) {
                    for (let k = 0; k < tabsData.value[i].child_list.length; k++) {
                        if (tabsData.value[i].child_list[k].category_id == categoryId) {
                            tabActive.value = i;
                            break;
                        }
                    }
                }
            }
        }
        loading.value = false;
    }).catch(() => {
        loading.value = false;
    });
}

// 一级菜单样式控制
const tabActive = ref<number>(0)

// 一级菜单点击事件
const firstLevelClick = (index: number, data: Object) => {
    tabActive.value = index;
}
const toLink = (curr_goods_category: string) => {
    redirect({ url: '/addon/shop/pages/goods/list', param: { curr_goods_category } })
}
// 搜索名字
const searchNameFn = () => {
    // getMescroll().resetUpScroll();
    // if(searchName.value)
    redirect({ url: '/addon/shop/pages/goods/list', param: { goods_name: encodeURIComponent(searchName.value) } })
}
</script>

<style lang="scss" scoped>
.border-color {
    border-color: var(--primary-color) !important;
}

.text-color {
    color: var(--primary-color) !important;
}

.bg-color {
    background-color: var(--primary-color) !important;
}

.class-select {
    position: relative;
    font-weight: bold;

    &::before {
        content: "";
        position: absolute;
        bottom: 0;
        height: 6rpx;
        background-color: $u-primary;
        width: 90%;
        left: 50%;
        transform: translateX(-50%);
    }
}

.list-select {
    position: relative;
    margin-right: 28rpx;

    &::before {
        content: "";
        position: absolute;
        background-color: #999;
        width: 2rpx;
        height: 70%;
        top: 50%;
        right: -14rpx;
        transform: translatey(-50%);
    }
}

.transform-rotate {
    transform: rotate(180deg);
}

.text-color {
    color: $u-primary;
}

.bg-color {
    background-color: $u-primary;
}

.search-box {
    display: flex;
    align-items: center;
    padding: 0 30rpx;
}

.search-box .search-ipt {
    height: 58rpx;
    background-color: #fff;
    border-radius: 33rpx;
}

.search-box .search-ipt .input-placeholder {
    color: #A5A6A6;
}

.tabs-box {
    width: 168rpx;
    font-size: 28rpx;
}

.tabs-box .tab-item {
    min-height: 48rpx;
    padding: 18rpx 0;
    text-align: center;
    background-color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24rpx;
}

.tabs-box .tab-item-active {
    position: relative;
    color: var(--primary-color);
    background-color: var(--temp-bg);

    &::before {
        display: inline-block;
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        content: '';
        width: 6rpx;
        height: 48rpx;
        background-color: var(--primary-color);
    }

    &::after {
        display: inline-block;
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        content: '';
        width: 6rpx;
        height: 48rpx;
        background-color: var(--primary-color);
    }
}

$white-bj: #fff;

.mescroll-box {
    height: 100vh;
}

.panic-buying {
    background-color: var(--primary-color);
    color: $white-bj;
}

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

/*  #ifdef  H5  */
.scroll-ios {
    padding-bottom: calc(50px + 20rpx + constant(safe-area-inset-bottom)) !important;
    padding-bottom: calc(50px + 20rpx + env(safe-area-inset-bottom)) !important;
}

/*  #endif  */
/*  #ifndef  H5  */
.scroll-ios {
    padding-bottom: calc(120rpx + constant(safe-area-inset-bottom)) !important;
    padding-bottom: calc(120rpx + env(safe-area-inset-bottom)) !important;
}

/*  #endif  */
.scroll-height {
    height: 100%;
}

/*  #ifdef  H5  */
.noData1 {
    height: calc(100vh - 146rpx - 50px - constant(safe-area-inset-bottom));
    height: calc(100vh - 146rpx - 50px - env(safe-area-inset-bottom));
}

.noData2 {
    height: calc(100vh - 40rpx - 50px - constant(safe-area-inset-bottom));
    height: calc(100vh - 40rpx - 50px - env(safe-area-inset-bottom));
}

/*  #endif  */
/*  #ifndef  H5  */
.noData1 {
    height: calc(100vh - 146rpx - 100rpx - constant(safe-area-inset-bottom));
    height: calc(100vh - 146rpx - 100rpx - env(safe-area-inset-bottom));
}

.noData2 {
    height: calc(100vh - 40rpx - 100rpx - constant(safe-area-inset-bottom));
    height: calc(100vh - 40rpx - 100rpx - env(safe-area-inset-bottom));
}

/*  #endif  */
.mescroll-empty.empty-page.part {
    width: 542rpx;
    height: 542rpx;
    margin-top: 0;
    margin-left: 0;
    padding-top: 50rpx;

    .img {
        width: 160rpx !important;
        height: 120rpx !important;
    }
}
</style>
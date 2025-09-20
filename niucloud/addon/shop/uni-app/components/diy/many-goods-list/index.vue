<template>
    <view class="overflow-hidden">
        <scroll-view scroll-x="true" class="many-goods-list-head" :class="diyComponent.headStyle" :scroll-into-view="'a' + cateIndex" :style="warpCss">
            <template v-if="diyComponent.headStyle == 'style-3'">
                <template v-if="diyComponent.source == 'custom'">
                    <view v-for="(item,index) in diyComponent.list" :key="index"
                          :class="['flex-col inline-flex items-center justify-center', { 'pr-[40rpx]': (index != diyComponent.list.length-1) }]"
                          @click="changeCateIndex(item,index)">
                        <image :style="{ borderRadius : (diyComponent.aroundRadius * 2) + 'rpx' }"
                               :class="['w-[90rpx] h-[90rpx] overflow-hidden border-[2rpx] border-solid border-transparent', {'border-[var(--primary-color)]': index == cateIndex }]"
                               v-if="item.imageUrl" :src="img(item.imageUrl)" mode="aspectFit" />
                        <image :style="{ borderRadius : (diyComponent.aroundRadius * 2) + 'rpx' }"
                               :class="['w-[90rpx] h-[90rpx] overflow-hidden border-[2rpx] border-solid border-transparent', {'border-[var(--primary-color)]': index == cateIndex }]"
                               v-else :src="img('static/resource/images/diy/figure.png')" mode="scaleToFill" />
                        <text :class="['text-[28rpx] mt-[16rpx]', {'font-500 text-[var(--primary-color)]' : index == cateIndex  }]">{{ item.title }}</text>
                    </view>
                </template>
                <template v-else-if="diyComponent.source == 'goods_category'">
                    <view class="pr-[40rpx] inline-flex flex-col items-center justify-center" @click="changeGoodsCategory({ category_id : 0 })">
                        <image :style="{ borderRadius : (diyComponent.aroundRadius * 2) + 'rpx' }"
                               :class="['w-[90rpx] h-[90rpx] overflow-hidden border-[2rpx] border-solid border-transparent', {'border-[var(--primary-color)]': currentCategoryId == 0}]"
                               :src="img('static/resource/images/diy/figure.png')" mode="scaleToFill" />
                        <text :class="['text-[28rpx] mt-[16rpx]', {'font-500 text-[var(--primary-color)]': currentCategoryId == 0}]">全部</text>
                    </view>
                    <view v-for="(item,index) in goodsCategoryListData" :key="index"
                          :class="['flex-col inline-flex items-center justify-center', { 'pr-[40rpx]': (index != goodsCategoryListData.length-1) }]"
                          @click="changeGoodsCategory(item)">
                        <image :style="{ borderRadius : (diyComponent.aroundRadius * 2) + 'rpx' }"
                               :class="['w-[90rpx] h-[90rpx] overflow-hidden border-[2rpx] border-solid border-transparent', {'border-[var(--primary-color)]': currentCategoryId == item.category_id}]"
                               v-if="item.image" :src="img(item.image)" mode="aspectFit" />
                        <image :style="{ borderRadius : (diyComponent.aroundRadius * 2) + 'rpx' }"
                               :class="['w-[90rpx] h-[90rpx] overflow-hidden border-[2rpx] border-solid border-transparent', {'border-[var(--primary-color)]': currentCategoryId == item.category_id}]"
                               v-else :src="img('static/resource/images/diy/figure.png')" mode="scaleToFill" />
                        <text :class="['text-[28rpx] mt-[16rpx]', {'font-500 text-[var(--primary-color)]' : currentCategoryId == item.category_id}]">{{ item.category_name }}</text>
                    </view>
                </template>
            </template>
            <template v-else>
                <view v-for="(item, index) in diyComponent.list" class="scroll-item"
                      :class="[diyComponent.headStyle, { active: index == cateIndex }]" :id="'a' + index" :key="index"
                      @click="changeCateIndex(item, index)">
                    <view class="cate" v-if="diyComponent.headStyle == 'style-1'">
                        <view class="name">{{ item.title }}</view>
                        <view class="desc" :v-if="item.desc">{{ item.desc }}</view>
                    </view>
                    <view v-if="diyComponent.headStyle == 'style-2'" class="cate">
                        <view class="name">{{ item.title }}</view>
                        <text class="nc-iconfont nc-icon-xiaolian-2 !text-[40rpx] text-[var(--primary-color)] transform" v-if="index == cateIndex"></text>
                    </view>
                    <view v-if="diyComponent.headStyle == 'style-4'" class="cate">
                        <view class="name">{{ item.title }}</view>
                    </view>
                </view>
            </template>
        </scroll-view>

        <diy-goods-list class="many-goods-list-body" v-if="goodsValue" :value="goodsValue"></diy-goods-list>

    </view>
</template>

<script setup lang="ts">
// 多商品组 组件
import { ref, computed, watch, onMounted } from 'vue';
import { img } from '@/utils/common';
import useDiyStore from '@/app/stores/diy';
import diyGoodsList from '@/addon/shop/components/diy/goods-list/index.vue';
import { getGoodsCategoryList } from '@/addon/shop/api/goods';

const props = defineProps(['component', 'index']);
const diyStore = useDiyStore();

const diyComponent = computed(() => {
    if (diyStore.mode == 'decorate') {
        return diyStore.value[props.index];
    } else {
        return props.component;
    }
})

const warpCss = computed(() => {
    let style = '';
    if (diyComponent.value.componentStartBgColor) {
        if (diyComponent.value.componentStartBgColor && diyComponent.value.componentEndBgColor) style += `background:linear-gradient(${ diyComponent.value.componentGradientAngle },${ diyComponent.value.componentStartBgColor },${ diyComponent.value.componentEndBgColor });`;
        else style += 'background-color:' + diyComponent.value.componentStartBgColor + ';';
    }
    if (diyComponent.value.topRounded) style += 'border-top-left-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
    if (diyComponent.value.topRounded) style += 'border-top-right-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    return style;
})

onMounted(() => {
    refresh();
    // 装修模式下刷新
    if (diyStore.mode == 'decorate') {
        watch(
            () => diyComponent.value,
            (newValue, oldValue) => {
                if (newValue && newValue.componentName == 'ManyGoodsList') {
                    refresh();
                }
            }
        )
    }
});

const refresh = () => {
    // 装修模式下设置默认图
    if (diyComponent.value.headStyle == 'style-3' && diyComponent.value.source == 'goods_category' && diyComponent.value.goods_category) {
        getGoodsCategoryFn(diyComponent.value.goods_category);
    } else {
        changeCateIndex(diyComponent.value.list[0], 0, true)
    }
}

const cateIndex = ref(0) // 当前选中的分类id

const goodsValue: any = ref(null);

const changeCateIndex = (item: any, index: any, isFirst: any = false) => {
    // 装修模式禁止跳转
    if (diyStore.mode == 'decorate' && !isFirst) return;

    cateIndex.value = index;
    refreshGoodsList({
        source: item.source,
        goods_category: item.goods_category,
        goods_ids: item.goods_ids
    })
}

const goodsCategoryListData = ref([])
const currentCategoryId = ref(0) // 当前选中的分类id

// 获取商品分类列表
const getGoodsCategoryFn = (pid: any) => {
    getGoodsCategoryList({
        pid
    }).then(res => {
        if (res.code == 1) {
            goodsCategoryListData.value = res.data;
            if (goodsCategoryListData.value) {
                refreshGoodsList({
                    source: 'category',
                    goods_category: ''
                });
            }
        }
    })
}

// 切换商品分类，查询商品列表数据
const changeGoodsCategory = (item: any) => {
    // 装修模式禁止跳转
    if (diyStore.mode == 'decorate') return;
    currentCategoryId.value = item.category_id
    refreshGoodsList({
        source: 'category',
        goods_category: currentCategoryId.value
    });
}

const refreshGoodsList = (obj: any) => {
    goodsValue.value = {
        style: diyComponent.value.style,
        margin: diyComponent.value.margin,
        source: obj.source,

        num: diyComponent.value.num,
        sortWay: diyComponent.value.sortWay,

        goodsNameStyle: diyComponent.value.goodsNameStyle,
        priceStyle: diyComponent.value.priceStyle,
        saleStyle: diyComponent.value.saleStyle,
        btnStyle: diyComponent.value.btnStyle,
        labelStyle: diyComponent.value.labelStyle,

        imgElementRounded: diyComponent.value.imgElementRounded,

        elementBgColor: diyComponent.value.elementBgColor,
        topElementRounded: diyComponent.value.topElementRounded,
        bottomElementRounded: diyComponent.value.bottomElementRounded
    };

    if (obj.goods_category) {
        goodsValue.value.goods_category = obj.goods_category
    }
    if (obj.goods_ids && obj.goods_ids.length) {
        goodsValue.value.goods_ids = obj.goods_ids
    }
}

</script>

<style lang="scss" scoped>
.many-goods-list-head {
    left: 0;
    right: 0;
    z-index: 5;
    width: 100%;
    white-space: nowrap;
    box-sizing: border-box;
    padding: 20rpx 0 10rpx;
    margin-bottom: 20rpx;
    position: relative;

    &.style-1 {
        padding: 26rpx 0 14rpx;
    }

    &.style-2 {
        height: 100rpx;
        padding: 20rpx 0 16rpx;
    }

    &.style-3 {
        padding: 26rpx 20rpx;
        background-color: #fff;
        margin-bottom: 20rpx;
        width: 100%;
        white-space: nowrap;
        box-sizing: border-box;
    }

    &.style-4 {
        padding-bottom: 0;
    }

    .scroll-item {
        display: inline-block;
        text-align: center;
        width: auto;
        padding: 0 20rpx;

        &.style-1 {
            &:first-child {
                width: calc(25% - 20rpx);
                padding-left: 0;
            }

            &.active {
                .name {
                    color: var(--primary-color);
                    font-weight: 500;
                }

                .desc {
                    color: #ffffff;
                    border-radius: 20rpx;
                    background-color: var(--primary-color);
                }
            }

            .name {
                font-size: 30rpx;
                color: #333;
                line-height: 1;
            }

            .cate {
                display: inline-block;
            }

            .desc {
                font-size: 22rpx;
                color: #999;
                height: 38rpx;
                line-height: 38rpx;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-top: 10rpx;
                min-width: 110rpx;
                max-width: 220rpx;
                overflow: hidden;
                text-overflow: ellipsis;
                padding: 0 10rpx;
            }
        }

        &.style-2 {
            .cate {
                display: flex;
                flex-direction: column;
                align-items: center;
                position: relative;
            }

            .name {
                font-size: 28rpx;
                color: #333;
                line-height: 32rpx;
            }

            &.active {
                .name {
                    color: var(--primary-color);
                    font-weight: 500;
                }

                .nc-iconfont {
                    position: absolute;
                    bottom: -35rpx;
                }
            }
        }

        &.style-4 {
            padding: 0 10rpx 14rpx;

            .cate {
                display: flex;
                flex-direction: column;
                align-items: center;
                position: relative;
                padding: 10rpx 12rpx;
                background-color: #F6F6F6;
                border-radius: 12rpx;
                min-width: 136rpx;
                box-sizing: border-box;
                border: 2rpx solid #F6F6F6;
            }

            .name {
                font-size: 28rpx;
                color: #333;
                line-height: 32rpx;
            }

            &.active {
                .cate {
                    background-color: var(--primary-color-light);
                    border-color: var(--primary-color);
                    position: relative;

                    &::after {
                        content: "";
                        width: 18rpx;
                        height: 18rpx;
                        position: absolute;
                        background-color: var(--primary-color-light);
                        border: 2rpx solid transparent;
                        border-bottom-color: var(--primary-color);
                        border-right-color: var(--primary-color);
                        bottom: -12rpx;
                        left: 50%;
                        transform: translateX(-50%) rotate(45deg);
                        border-bottom-right-radius: 10rpx;
                    }
                }

                .name {
                    color: var(--primary-color);
                    font-weight: 500;
                }

                .nc-iconfont {
                    position: absolute;
                    bottom: -35rpx;
                }
            }
        }
    }

}

</style>

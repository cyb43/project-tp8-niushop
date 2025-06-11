<template>
    <x-skeleton :type="skeleton.type" :loading="skeleton.loading" :config="skeleton.config">
        <view :style="warpCss" class="overflow-hidden">
            <view :style="maskLayer"></view>
            <view :class="{'diy-shop-goods-list relative flex flex-wrap justify-between': diyComponent.style != 'style-2', 'biserial-goods-list': diyComponent.style == 'style-2'}">
                <template v-if="diyComponent.style == 'style-1'">
                    <view class="bg-white w-full flex p-[20rpx] overflow-hidden" :class="{ 'mt-[20rpx]': index > 0 }"
                          :style="itemCss" v-for="(item,index) in goodsList" :key="item.goods_id" @click="toLink(item)">
                        <u--image :radius="imageRounded.val" width="200rpx" height="200rpx" :src="img(item.goods_cover_thumb_mid || '')" model="aspectFill">
                            <template #error>
                                <image class="w-[200rpx] h-[200rpx] overflow-hidden" :style="imageRounded.style" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill" />
                            </template>
                        </u--image>
                        <view class="flex-1 flex flex-col ml-[20rpx] py-[6rpx] relative">
                            <view class="text-[28rpx] leading-[40rpx] text-[#303133] multi-hidden mb-[10rpx]"
                                  :style="{ color : diyComponent.goodsNameStyle.color, fontWeight : diyComponent.goodsNameStyle.fontWeight }"
                                  v-if="diyComponent.goodsNameStyle.control">
                                <view class="brand-tag" v-if="item.goods_brand" :style="diyGoods.baseTagStyle(item.goods_brand)">{{ item.goods_brand.brand_name }}</view>
                                {{ item.goods_name }}
                            </view>
                            <view v-if="item.goods_label_name && item.goods_label_name.length && diyComponent.labelStyle.control" class="flex flex-wrap mb-[10rpx]">
                                <template v-for="(tagItem, tagIndex) in item.goods_label_name">
                                    <image class="img-tag" v-if="tagItem.style_type == 'icon' && tagItem.icon" :src="img(tagItem.icon)" mode="heightFix" @error="diyGoods.error(tagItem,'icon')"/>
                                    <view class="base-tag" v-else-if="tagItem.style_type == 'diy' || !tagItem.icon" :style="diyGoods.baseTagStyle(tagItem)">{{ tagItem.label_name }}</view>
                                </template>
                            </view>
                            <view class="mt-auto flex justify-between items-center">
                                <view class="flex flex-col">
                                    <view class="flex items-baseline leading-[1]" v-if="diyComponent.priceStyle.control">
                                        <view
                                            class="font-bold text-[var(--price-text-color)] price-font block truncate max-w-[350rpx]"
                                            :style="{ color : diyComponent.priceStyle.color }">
                                            <text class="text-[24rpx] font-500 mr-[4rpx]">￥</text>
                                            <text class="text-[40rpx] font-500">{{ parseFloat(diyGoods.goodsPrice(item)).toFixed(2) }}</text>
                                        </view>
                                        <image v-if="diyGoods.priceType(item) == 'member_price'" class="max-w-[50rpx] h-[28rpx] ml-[6rpx]" :src="img('addon/shop/VIP.png')" mode="heightFix" />
										<image v-else-if="diyGoods.priceType(item) == 'newcomer_price'"  class="max-w-[60rpx] h-[28rpx] ml-[6rpx]" :src="img('addon/shop/newcomer.png')" mode="heightFix" />
										<image v-else-if="diyGoods.priceType(item) == 'discount_price'" class="max-w-[80rpx] h-[28rpx] ml-[6rpx]" :src="img('addon/shop/discount.png')" mode="heightFix" />
                                    </view>
                                    <text v-if="diyComponent.saleStyle.control"
                                          class="mt-[8rpx] text-[22rpx] text-[var(--text-color-light9)]"
                                          :style="{ color : diyComponent.saleStyle.color }">
                                        已售{{ item.sale_num }}{{ item.unit || '件' }}
                                    </text>
                                </view>
                                <view v-if="diyComponent.btnStyle.control" class="absolute right-[0] bottom-[0]">
                                    <view v-if="diyComponent.btnStyle.style == 'button'" :style="goodsBtnCss" class="px-[18rpx] min-w-[100rpx] box-border h-[48rpx] flex items-center justify-center">
                                        <text class="text-[20rpx]">{{ diyComponent.btnStyle.text }}</text>
                                    </view>
                                    <view v-else :style="goodsBtnCss" class="w-[50rpx] h-[50rpx] rounded-[50%] flex items-center justify-center">
                                        <text :class="['nc-iconfont', 'text-[30rpx]', diyComponent.btnStyle.style]"></text>
                                    </view>
                                </view>
                            </view>
                        </view>
                    </view>
                </template>
                <template v-if="diyComponent.style == 'style-2'">
                    <view>
                        <template v-for="(item,index) in goodsList">
                            <view v-if="(index%2) == 0" class="flex flex-col bg-[#fff] box-border overflow-hidden"
                                  :class="{'mt-[24rpx]': index > 1}" :style="itemCss" @click="toLink(item)">
                                <u--image :radius="imageRounded.val" :width="style2Width" :height="style2Width" :src="img(item.goods_cover_thumb_mid || '')" model="aspectFill">
                                    <template #error>
                                        <image :style="{'width': style2Width,'height': style2Width, 'border-radius': imageRounded.val}" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill" />
                                    </template>
                                </u--image>
                                <view class="relative min-h-[44rpx] px-[16rpx] flex-1 pt-[16rpx] pb-[20rpx] flex flex-col justify-between">
                                    <view class="text-[#303133] leading-[40rpx] text-[28rpx] multi-hidden"
                                          :style="{ color : diyComponent.goodsNameStyle.color, fontWeight : diyComponent.goodsNameStyle.fontWeight }"
                                          v-if="diyComponent.goodsNameStyle.control">
                                        <view class="brand-tag" v-if="item.goods_brand" :style="diyGoods.baseTagStyle(item.goods_brand)">{{ item.goods_brand.brand_name }}</view>
                                        {{ item.goods_name }}
                                    </view>
                                    <view v-if="item.goods_label_name && item.goods_label_name.length && diyComponent.labelStyle.control" class="flex flex-wrap">
                                        <template v-for="(tagItem, tagIndex) in item.goods_label_name">
                                            <image class="img-tag" v-if="tagItem.style_type == 'icon' && tagItem.icon" :src="img(tagItem.icon)" mode="heightFix" @error="diyGoods.error(tagItem,'icon')" />
                                            <view class="base-tag" v-else-if="tagItem.style_type == 'diy' || !tagItem.icon" :style="diyGoods.baseTagStyle(tagItem)">{{ tagItem.label_name }}</view>
                                        </template>
                                    </view>
                                    <view class="flex justify-between flex-wrap items-center mt-[20rpx]">
                                        <view class="flex flex-col">
                                            <view class="flex items-baseline leading-[1]" v-if="diyComponent.priceStyle.control">
                                                <view class="text-[var(--price-text-color)] price-font block truncate max-w-[270rpx]"
                                                    :style="{ color : diyComponent.priceStyle.color }">
                                                    <text class="text-[24rpx] font-400">￥</text>
                                                    <text class="text-[40rpx] font-500">{{ parseFloat(diyGoods.goodsPrice(item)).toFixed(2).split('.')[0] }}</text>
                                                    <text class="text-[24rpx] font-500">.{{ parseFloat(diyGoods.goodsPrice(item)).toFixed(2).split('.')[1] }}</text>
                                                </view>
                                                <image v-if="diyGoods.priceType(item) == 'member_price'" class="max-w-[50rpx] h-[28rpx] ml-[6rpx]" :src="img('addon/shop/VIP.png')" mode="heightFix" />
												<image v-else-if="diyGoods.priceType(item) == 'newcomer_price'"  class="max-w-[60rpx] h-[28rpx] ml-[6rpx]" :src="img('addon/shop/newcomer.png')" mode="heightFix" />
												<image v-else-if="diyGoods.priceType(item) == 'discount_price'" class="max-w-[80rpx] h-[28rpx] ml-[6rpx]" :src="img('addon/shop/discount.png')" mode="heightFix" />	 
                                            </view>
                                            <text v-if="diyComponent.saleStyle.control"
                                                  class="text-[22rpx] mt-[8rpx] text-[var(--text-color-light9)]"
                                                  :style="{ color : diyComponent.saleStyle.color }">
                                                已售{{ item.sale_num }}{{ item.unit || '件' }}
                                            </text>
                                        </view>
                                        <view class="absolute right-[16rpx] bottom-[16rpx]" v-if="diyComponent.btnStyle.control">
                                            <view v-if="diyComponent.btnStyle.style == 'button'" :style="goodsBtnCss" class="px-[18rpx] h-[48rpx] flex items-center justify-center">
                                                <text class="text-[20rpx]">{{ diyComponent.btnStyle.text }}</text>
                                            </view>
                                            <view v-else :style="goodsBtnCss" class="w-[46rpx] h-[46rpx] rounded-[50%] flex items-center justify-center">
                                                <text :class="['nc-iconfont', 'text-[30rpx]', diyComponent.btnStyle.style]"></text>
                                            </view>
                                        </view>
                                    </view>
                                </view>
                            </view>
                        </template>
                    </view>
                    <view>
                        <template v-for="(item,index) in goodsList">
                            <view v-if="(index%2) == 1" class="flex flex-col bg-[#fff] box-border overflow-hidden" :class="{'mt-[24rpx]': index > 1}" :style="itemCss" @click="toLink(item)">
                                <u--image :width="style2Width" :height="style2Width" :radius="imageRounded.val" :src="img(item.goods_cover_thumb_mid || '')" model="aspectFill">
                                    <template #error>
                                        <image :style="{'width': style2Width,'height': style2Width, 'border-radius': imageRounded.val}" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill" />
                                    </template>
                                </u--image>
                                <view class="relative min-h-[44rpx] px-[16rpx] flex-1 pt-[16rpx] pb-[20rpx] flex flex-col justify-between">
                                    <view class="text-[#303133] leading-[40rpx] text-[28rpx] multi-hidden"
                                          :style="{ color : diyComponent.goodsNameStyle.color, fontWeight : diyComponent.goodsNameStyle.fontWeight }"
                                          v-if="diyComponent.goodsNameStyle.control">
                                        <view class="brand-tag" v-if="item.goods_brand" :style="diyGoods.baseTagStyle(item.goods_brand)">{{ item.goods_brand.brand_name }}</view>
                                        {{ item.goods_name }}
                                    </view>
                                    <view v-if="item.goods_label_name && item.goods_label_name.length && diyComponent.labelStyle.control" class="flex flex-wrap">
                                        <template v-for="(tagItem, tagIndex) in item.goods_label_name">
                                            <image class="img-tag" v-if="tagItem.style_type == 'icon' && tagItem.icon" :src="img(tagItem.icon)" mode="heightFix" @error="diyGoods.error(tagItem,'icon')" />
                                            <view class="base-tag" v-else-if="tagItem.style_type == 'diy' || !tagItem.icon" :style="diyGoods.baseTagStyle(tagItem)">{{ tagItem.label_name }}</view>
                                        </template>
                                    </view>
                                    <view class="flex justify-between flex-wrap items-center mt-[20rpx]">
                                        <view class="flex flex-col">
                                            <view class="flex items-baseline leading-[1]" v-if="diyComponent.priceStyle.control">
                                                <view class="text-[var(--price-text-color)] price-font block truncate max-w-[270rpx]" :style="{ color : diyComponent.priceStyle.color }">
                                                    <text class="text-[24rpx] font-400">￥</text>
                                                    <text class="text-[40rpx] font-500">{{ parseFloat(diyGoods.goodsPrice(item)).toFixed(2).split('.')[0] }}</text>
                                                    <text class="text-[24rpx] font-500">.{{ parseFloat(diyGoods.goodsPrice(item)).toFixed(2).split('.')[1] }}</text>
                                                </view>
												<image v-if="diyGoods.priceType(item) == 'member_price'" class="max-w-[50rpx] h-[28rpx] ml-[6rpx]" :src="img('addon/shop/VIP.png')" mode="heightFix" />
												<image v-else-if="diyGoods.priceType(item) == 'newcomer_price'"  class="max-w-[60rpx] h-[28rpx] ml-[6rpx]" :src="img('addon/shop/newcomer.png')" mode="heightFix" />
												<image v-else-if="diyGoods.priceType(item) == 'discount_price'" class="max-w-[80rpx] h-[28rpx] ml-[6rpx]" :src="img('addon/shop/discount.png')" mode="heightFix" />	 
                                            </view>
                                            <text v-if="diyComponent.saleStyle.control"
                                                  class="text-[22rpx] mt-[8rpx] text-[var(--text-color-light9)]"
                                                  :style="{ color : diyComponent.saleStyle.color }">
                                                已售{{ item.sale_num }}{{ item.unit || '件' }}
                                            </text>
                                        </view>
                                        <view class="absolute right-[16rpx] bottom-[16rpx]" v-if="diyComponent.btnStyle.control">
                                            <view v-if="diyComponent.btnStyle.style == 'button'" :style="goodsBtnCss" class="px-[18rpx] h-[48rpx] flex items-center justify-center">
                                                <text class="text-[20rpx]">{{ diyComponent.btnStyle.text }}</text>
                                            </view>
                                            <view v-else :style="goodsBtnCss" class="w-[46rpx] h-[46rpx] rounded-[50%] flex items-center justify-center">
                                                <text :class="['nc-iconfont', 'text-[30rpx]', diyComponent.btnStyle.style]"></text>
                                            </view>
                                        </view>
                                    </view>
                                </view>
                            </view>
                        </template>
                    </view>
                </template>
                <template v-if="diyComponent.style == 'style-3'">
                    <view :style="style3Css" v-if="goodsList.length">
                        <scroll-view :id="'warpStyle3-'+diyComponent.id" class="whitespace-nowrap min-h-[290rpx]" :scroll-x="true">
                            <view :id="'item'+index+diyComponent.id"
                                  class="w-[214rpx] mb-[6rpx] inline-block bg-[#fff] box-border overflow-hidden"
                                  :class="{'!mr-[0rpx]' : index == (goodsList.length-1)}" :style="itemCss+itemStyle3"
                                  v-for="(item,index) in goodsList" :key="item.goods_id" @click="toLink(item)">
                                <u--image width="214rpx" height="160rpx" :radius="imageRounded.val" :src="img(item.goods_cover_thumb_mid || '')" model="aspectFill">
                                    <template #error>
                                        <image class="w-[214rpx] h-[160rpx]" :style="imageRounded.style" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill" />
                                    </template>
                                </u--image>
                                <view class="relative min-h-[40rpx] px-[10rpx] pt-[16rpx] pb-[10rpx]">
                                    <view class="text-[26rpx] text-[#303133] truncate" :style="{ color : diyComponent.goodsNameStyle.color, fontWeight : diyComponent.goodsNameStyle.fontWeight }" v-if="diyComponent.goodsNameStyle.control">{{ item.goods_name }}</view>
                                    <view class="text-[var(--price-text-color)] pt-[16rpx] pb-[6rpx] font-bold price-font block truncate max-w-[160rpx] leading-[1] overflow-hidden"
                                        :style="{ color : diyComponent.priceStyle.color }"
                                        v-if="diyComponent.priceStyle.control">
                                        <text class="text-[20rpx] font-400 mr-[2rpx]">￥</text>
                                        <text class="text-[36rpx] font-500">{{ parseFloat(diyGoods.goodsPrice(item)).toFixed(2) }}</text>
                                    </view>
                                    <view v-if="diyComponent.btnStyle.control" class="absolute right-[10rpx] bottom-[12rpx]">
                                        <view v-if="diyComponent.btnStyle.style != 'button'" :style="goodsBtnCss" class="w-[40rpx] h-[40rpx] rounded-[50%] flex items-center justify-center">
                                            <text :class="[diyComponent.btnStyle.style]" class="nc-iconfont text-[28rpx]"></text>
                                        </view>
                                    </view>
                                </view>
                            </view>
                        </scroll-view>
                    </view>

                </template>
            </view>
        </view>
    </x-skeleton>
</template>

<script setup lang="ts">
// 商品列表
import { ref, reactive, computed, watch, onMounted, nextTick, getCurrentInstance } from 'vue';
import { redirect, img } from '@/utils/common';
import useDiyStore from '@/app/stores/diy';
import { getGoodsComponents } from '@/addon/shop/api/goods';
import { useGoods } from '@/addon/shop/hooks/useGoods'

const diyGoods = useGoods();
const props = defineProps(['component', 'index', 'value']);
const diyStore = useDiyStore();
const emits = defineEmits(['loadingFn']); //商品数据加载完成之后触发

const skeleton = reactive({
    type: '',
    loading: diyStore.mode == 'decorate' ? false : true,
    config: {}
})

const goodsList = ref<Array<any>>([]);

const diyComponent = computed(() => {
    if (props.value) {
        return props.value;
    } else if (diyStore.mode == 'decorate') {
        return diyStore.value[props.index];
    } else {
        return props.component;
    }
})

const warpCss = computed(() => {
    var style = '';
    style += 'position:relative;';
    if (diyComponent.value.componentStartBgColor && diyComponent.value.componentEndBgColor) style += `background:linear-gradient(${ diyComponent.value.componentGradientAngle },${ diyComponent.value.componentStartBgColor },${ diyComponent.value.componentEndBgColor });`;
    else style += 'background-color:' + (diyComponent.value.componentStartBgColor || diyComponent.value.componentEndBgColor) + ';';

    if (diyComponent.value.componentBgUrl) {
        style += `background-image:url('${ img(diyComponent.value.componentBgUrl) }');`;
        style += 'background-size: cover;background-repeat: no-repeat;';
    }

    if (diyComponent.value.topRounded) style += 'border-top-left-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
    if (diyComponent.value.topRounded) style += 'border-top-right-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    return style;
})

const imageRounded = computed(() => {
    var obj = {
        val: '',
        style: ''
    };
    if (diyComponent.value.imgElementRounded) {
        obj.val = diyComponent.value.imgElementRounded * 2 + 'rpx';
        obj.style += 'border-radius:' + diyComponent.value.imgElementRounded * 2 + 'rpx;';
    }
    return obj;
})

// 背景图加遮罩层
const maskLayer = computed(() => {
    var style = '';
    if (diyComponent.value.componentBgUrl) {
        style += 'position:absolute;top:0;width:100%;';
        style += `background: rgba(0,0,0,${ diyComponent.value.componentBgAlpha / 10 });`;
        style += `height:${ height.value }px;`;

        if (diyComponent.value.topRounded) style += 'border-top-left-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
        if (diyComponent.value.topRounded) style += 'border-top-right-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
        if (diyComponent.value.bottomRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
        if (diyComponent.value.bottomRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    }

    return style;
});

const itemCss = computed(() => {
    var style = '';
    if (diyComponent.value.elementBgColor) style += 'background-color:' + diyComponent.value.elementBgColor + ';';
    if (diyComponent.value.topElementRounded) style += 'border-top-left-radius:' + diyComponent.value.topElementRounded * 2 + 'rpx;';
    if (diyComponent.value.topElementRounded) style += 'border-top-right-radius:' + diyComponent.value.topElementRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomElementRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomElementRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomElementRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomElementRounded * 2 + 'rpx;';
    if (diyComponent.value.style == 'style-2') {
        if (diyComponent.value.margin && diyComponent.value.margin.both) style += 'width: calc((100vw - ' + (diyComponent.value.margin.both * 4) + 'rpx - 20rpx) / 2);'
        else style += 'width: calc((100vw - 20rpx) / 2 );'
    }
    return style;
})

const goodsBtnCss = computed(() => {
    var style = '';
    if (diyComponent.value.btnStyle.style == 'button' && diyComponent.value.btnStyle.aroundRadius) style += 'border-radius:' + diyComponent.value.btnStyle.aroundRadius * 2 + 'rpx;';
    if (diyComponent.value.btnStyle.startBgColor && diyComponent.value.btnStyle.endBgColor) {
        style += `background:linear-gradient(${ diyComponent.value.btnStyle.startBgColor },${ diyComponent.value.btnStyle.endBgColor });`;
    } else {
        style += 'background-color:' + (diyComponent.value.btnStyle.startBgColor || diyComponent.value.btnStyle.endBgColor) + ';';
    }
    if (diyComponent.value.btnStyle.textColor) style += 'color:' + diyComponent.value.btnStyle.textColor + ';';
    if (diyComponent.value.btnStyle.style == 'button' && diyComponent.value.btnStyle.fontWeight) style += 'font-weight: bold;';
    return style;
})

const style2Width = computed(() => {
    var style = '';
    if (diyComponent.value.margin && diyComponent.value.margin.both) style += 'calc((100vw - ' + (diyComponent.value.margin.both * 4) + 'rpx - 20rpx) / 2)'
    else style += 'calc((100vw - 20rpx) / 2 )'
    return style;
})

const style3Css = computed(() => {
    var style = '';
    style += 'padding:0 20rpx;';
    if (diyComponent.value.margin && diyComponent.value.margin.both) {
        style += 'width: calc(100vw - ' + ((diyComponent.value.margin.both * 4) + 40) + 'rpx);'
    } else {
        style += 'box-sizing: border-box; width: 100vw;';
    }
    return style;
})

//商品样式三
const itemStyle3 = ref('');
const setItemStyle3 = () => {
    // #ifdef MP-WEIXIN
    uni.createSelectorQuery().in(instance).select('#warpStyle3-' + diyComponent.value.id).boundingClientRect((res: any) => {
        uni.createSelectorQuery().in(instance).select('#item0' + diyComponent.value.id).boundingClientRect((data: any) => {
            itemStyle3.value = `margin-right:${ (res.width - data.width * 3) / 2 }px;`
        }).exec()
    }).exec()
    // #endif
    // #ifdef H5
    itemStyle3.value = 'margin-right:14rpx;'
    // #endif
}

const getGoodsListFn = () => {
    let data = {
        num: (diyComponent.value.source == 'all' || diyComponent.value.source == 'category') ? diyComponent.value.num : '',
        goods_ids: diyComponent.value.source == 'custom' ? diyComponent.value.goods_ids : '',
        goods_category: diyComponent.value.source == 'category' ? diyComponent.value.goods_category : '',
        order: diyComponent.value.sortWay
    }
    getGoodsComponents(data).then((res) => {
        goodsList.value = res.data;

        // 数据为空时隐藏整个组件
        // if(goodsList.value.length == 0 && diyComponent.value.pageStyle) {
        //     diyComponent.value.pageStyle = '';
        // }

        skeleton.loading = false;
        emits('loadingFn', res.data)
        if (diyComponent.value.componentBgUrl) {
            setTimeout(() => {
                const query = uni.createSelectorQuery().in(instance);
                query.select('.diy-shop-goods-list').boundingClientRect((data: any) => {
                    if (data) height.value = data.height;
                }).exec();
            }, 1000)
        }
        nextTick(() => {
            setTimeout(() => {
                if (diyComponent.value.style == 'style-3') setItemStyle3()
            }, 500)
        })
    });
}

const initSkeleton = () => {
    if (diyComponent.value.style == 'style-1') {

        // 单列 风格
        skeleton.type = 'list'
        skeleton.type = 'list'
        skeleton.config = {
            textRows: 2
        };
    } else if (diyComponent.value.style == 'style-2') {

        // 两列 风格
        skeleton.type = 'waterfall'
        skeleton.config = {
            headHeight: '320rpx',
            gridRows: 1,
            textRows: 2,
            textWidth: ['100%', '80%']
        };
    } else if (diyComponent.value.style == 'style-3') {

        // 横向滑动 风格
        skeleton.type = 'waterfall'
        skeleton.config = {
            gridRows: 1,
            gridColumns: 3,
            headHeight: '200rpx',
            textRows: 2,
            textWidth: ['100%', '80%']
        };
    }
}

const instance = getCurrentInstance();
const height = ref(0)

onMounted(() => {
    refresh();
    // 装修模式下刷新
    if (diyStore.mode == 'decorate') {
        watch(
            () => diyComponent.value,
            (newValue, oldValue) => {
                if (newValue && newValue.componentName == 'GoodsList') {
                    nextTick(() => {
                        const query = uni.createSelectorQuery().in(instance);
                        query.select('.diy-shop-goods-list').boundingClientRect((data: any) => {
                            if (data) height.value = data.height;
                        }).exec();
                        if (diyComponent.value.style == 'style-3') setItemStyle3()
                    })
                }
            }
        )
    } else {
        watch(
            () => diyComponent.value,
            (newValue, oldValue) => {
                refresh();
            },
            { deep: true }
        )
    }
});

const refresh = () => {
    // 装修模式下设置默认图
    if (diyStore.mode == 'decorate') {
        let obj = {
            goods_cover_thumb_mid: "",
            goods_name: "商品名称",
            sale_num: "100",
            unit: "件",
            goodsSku: { show_price: 100 }
        };
        goodsList.value.push(obj);
        goodsList.value.push(obj);
        nextTick(() => {
            if (diyComponent.value.style == 'style-3') setItemStyle3()
        })
    } else {
        initSkeleton();
        getGoodsListFn();
    }

}

const toLink = (data: any) => {
    redirect({ url: '/addon/shop/pages/goods/detail', param: { goods_id: data.goods_id } })
}
</script>
<style lang="scss" scoped>
@import '@/addon/shop/styles/common.scss';

.biserial-goods-list {
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-gap: 10px;
}
</style>

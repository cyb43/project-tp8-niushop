<template>
    <x-skeleton :type="skeleton.type" :loading="skeleton.loading" :config="skeleton.config">
        <view class="overflow-hidden" :style="warpCss">
            <view :style="maskLayer"></view>
            <view class="diy-shop-exchange-goods-list relative flex flex-wrap justify-between" v-if="goodsList.length">
                <view class="overflow-hidden bg-[#fff] flex flex-col box-border w-[calc(50%-10rpx)]"
                      :class="{'mt-[20rpx]': index > 1}" :style="itemCss" v-for="(item,index) in goodsList"
                      :key="item.goods_id" @click="toLink(item)">
                    <u--image :width="style2Width" :height="style2Width" :radius="imageRounded.val" :src="img(item.goods_cover_thumb_mid || '')" model="aspectFill">
                        <template #error>
                            <image class="overflow-hidden" :style="{'width': style2Width,'height': style2Width, 'border-radius': imageRounded.val}" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill" />
                        </template>
                    </u--image>
<!--                    <easy-image :image-src="item.goods_cover_thumb_small" :imageStyle="imageStyle" />-->
                    <view class="flex-1 pt-[10rpx] pb-[20rpx] px-[16rpx] flex flex-col justify-between">
						<view class="text-[28rpx] leading-[40rpx] text-[#303133] multi-hidden mb-[10rpx]" :style="{ color : diyComponent.goodsNameStyle.color, fontWeight : diyComponent.goodsNameStyle.fontWeight }">
						    <view class="brand-tag" v-if="item.goods_brand" :style="diyGoods.baseTagStyle(item.goods_brand)">{{ item.goods_brand.brand_name }}</view>
						    {{ item.names }}
						</view>
						<view class="text-[24rpx] text-[#999] leading-[30rpx] using-hidden my-[5rpx]">
						    {{ item.title }}
						</view>
						<view v-if="item.goods_label && item.goods_label.length" class="flex flex-wrap">
						    <template v-for="(tagItem, tagIndex) in item.goods_label">
						        <image class="img-tag" v-if="tagItem.style_type == 'icon' && tagItem.icon" :src="img(tagItem.icon)" mode="heightFix" @error="diyGoods.error(tagItem,'icon')"/>
						        <view class="base-tag" v-else-if="tagItem.style_type == 'diy' || !tagItem.icon" :style="diyGoods.baseTagStyle(tagItem)">
						            {{ tagItem.label_name }}
						        </view>
						    </template>
						</view>
                        <view class="text-[22rpx] leading-[28rpx] mt-[10rpx] text-[var(--text-color-light9)]" :style="{ color : diyComponent.saleStyle.color }">已兑{{ item.total_exchange_num }}人</view>
                        <view class="flex justify-between flex-wrap items-center mt-[16rpx]">
                            <view class="flex flex-col">
                                <view :style="{ color : diyComponent.priceStyle.mainColor }" class="text-[var(--price-text-color)] price-font ml-[2rpx] flex items-baseline">
                                    <text class="text-[32rpx]">{{ item.point }}</text>
                                    <text class="text-[24rpx] ml-[4rpx]">积分</text>
                                </view>
                                <view v-if="item.price&&item.price>0" class="flex items-center mt-[6rpx] price-font">
                                    <text :style="{ color : diyComponent.priceStyle.mainColor }" class="text-[#333] font-400 text-[32rpx]">+{{ parseFloat(item.price).toFixed(2) }}</text>
                                    <text :style="{ color : diyComponent.priceStyle.mainColor }" class="text-[var(--price-text-color)] font-400 ml-[4rpx] text-[24rpx]">元</text>
                                </view>
                            </view>
                            <view class="w-[120rpx] h-[54rpx] text-[22rpx] flex-center !text-[#fff] m-0 rounded-full primary-btn-bg remove-border text-center" shape="circle">去兑换</view>
                        </view>
                    </view>
                </view>
            </view>
            <view v-else-if="!goodsList.length" class="empty-page">
                <image class="img" :src="img('static/resource/images/system/empty.png')" model="aspectFit" />
                <view class="desc">暂无商品</view>
            </view>
        </view>
    </x-skeleton>
</template>

<script setup lang="ts">
// 积分商品列表
import { ref, reactive, computed, watch, onMounted, nextTick, getCurrentInstance } from 'vue';
import { redirect, img } from '@/utils/common';
import useDiyStore from '@/app/stores/diy';
import { getExchangeComponentsList } from '@/addon/shop/api/point';
import { useGoods } from '@/addon/shop/hooks/useGoods'

const props = defineProps(['component', 'index', 'value']);
const diyStore = useDiyStore();
const diyGoods = useGoods();
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
    let style = '';
    style += 'position:relative;';
    if (diyComponent.value.componentStartBgColor) {
        if (diyComponent.value.componentStartBgColor && diyComponent.value.componentEndBgColor) style += `background:linear-gradient(${ diyComponent.value.componentGradientAngle },${ diyComponent.value.componentStartBgColor },${ diyComponent.value.componentEndBgColor });`;
        else style += 'background-color:' + diyComponent.value.componentStartBgColor + ';';
    }

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
    const obj = {
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
    let style = '';
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
    let style = '';
    if (diyComponent.value.topElementRounded) style += 'border-top-left-radius:' + diyComponent.value.topElementRounded * 2 + 'rpx;';
    if (diyComponent.value.topElementRounded) style += 'border-top-right-radius:' + diyComponent.value.topElementRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomElementRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomElementRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomElementRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomElementRounded * 2 + 'rpx;';
    return style;
})

const style2Width = computed(() => {
    let style = '';
    if (diyComponent.value.margin && diyComponent.value.margin.both) style += 'calc((100vw - ' + (diyComponent.value.margin.both * 4) + 'rpx - 20rpx) / 2)'
    else style += 'calc((100vw - 20rpx) / 2 )'
    return style;
})

const imageStyle = computed(() => {
    let style = 'border-radius:' + imageRounded.value.val + ';';
    style += 'width:'+ style2Width.value + ';';
    style += 'height:'+ style2Width.value + ';';
    return style;
})

const getGoodsListFn = () => {
    let data = {
        order: diyComponent.value.sortWay,
        num: diyComponent.value.source == 'all' ? diyComponent.value.num : '',
        ids: diyComponent.value.source == 'custom' ? diyComponent.value.goods_ids.join(',') : '',
    }
    getExchangeComponentsList(data).then((res) => {
        goodsList.value = res.data;
        skeleton.loading = false;

        // 数据为空时隐藏整个组件
        // if(goodsList.value.length == 0) {
        //     diyComponent.value.pageStyle = '';
        // }
        if (diyComponent.value.componentBgUrl) {
            setTimeout(() => {
                const query = uni.createSelectorQuery().in(instance);
                query.select('.diy-shop-exchange-goods-list').boundingClientRect((data: any) => {
                    height.value = data.height;
                }).exec();
            }, 1000)
        }
    });
}

const initSkeleton = () => {
    if (diyComponent.value.style == 'style-1') {

        // 单列 风格
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
                if (newValue && newValue.componentName == 'ShopExchangeGoods') {
                    nextTick(() => {
                        const query = uni.createSelectorQuery().in(instance);
                        query.select('.diy-shop-exchange-goods-list').boundingClientRect((data: any) => {
                            height.value = data.height;
                        }).exec();
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
            image: "",
            names: "商品名称",
            total_exchange_num: 100,
            point: 100,
            price: 100
        };
        goodsList.value.push(obj);
        goodsList.value.push(obj);
    } else {
        initSkeleton();
        getGoodsListFn();
    }
}

const toLink = (data: any) => {
    redirect({ url: '/addon/shop/pages/point/detail', param: { id: data.id } })
}

</script>

<style lang="scss" scoped>
	@import '@/addon/shop/styles/common.scss';
</style>

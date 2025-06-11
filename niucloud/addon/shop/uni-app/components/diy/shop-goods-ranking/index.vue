<template>
    <view :style="warpCss" class="overflow-hidden">
        <scroll-view scroll-x="true" class="w-[100%] whitespace-nowrap">
            <view class="flex items-start">
                <view class="inline-block" v-for="(item,index) in diyComponent.list" :key="index">
                    <view class="w-[460rpx] px-[20rpx] pb-[20rpx] pt-[20rpx] flex flex-col items-start"
                          :class="{'mr-[20rpx]': (diyComponent.list.length-1) != index}" :style="swiperItemCss(item)"
                          v-if="listGoods[item.rankIds[0]] && listGoods[item.rankIds[0]].length > 0">
                        <view class="flex items-center h-[50rpx]">
                            <image class="w-[30rpx] h-[30rpx] mr-[10rpx]" v-if="item.imgUrl" :src="img(item.imgUrl)" mode="aspectFill"/>
                            <view :style="{'color': item.textColor}">
                                <text class="text-[30rpx] font-bold" v-if="item.text">{{ item.text }}</text>
                                <text class="text-[30rpx] font-bold" v-else>{{ listGoodsNames[item.rankIds[0]] }}</text>
                            </view>
                        </view>
                        <view class="flex items-center mt-[6rpx]" :style="{'color': item.subTitle.textColor}" @click="diyStore.toRedirect(item.subTitle.link)">
                            <text class="text-[24rpx] font-500" v-if="item.subTitle.text">{{ item.subTitle.text }}</text>
                            <text class="iconfont iconyouV6xx !text-[24rpx]" v-if="item.subTitle.text"></text>
                        </view>
                        <view class="mt-[24rpx]">
                            <view class="flex bg-[rgba(255,255,255,0.94)] p-[10rpx] rounded-[16rpx] mb-[16rpx]"
                                  v-for="(goods, gIndex) in listGoods[item.rankIds[0]]"
                                  :class="{'mb-0': gIndex === listGoods[item.rankIds[0]].length - 1}"
                                  @click="toDetail(goods.goods_id)">
                                <view class="relative w-[130rpx] h-[130rpx] mr-[16rpx]">
                                    <image class="absolute top-[6rpx] left-[8rpx] w-[30rpx] h-[36rpx]" :style="{ zIndex:2 }" :src="getRankBadge(goods.rank_num)" mode="aspectFill"/>
                                    <view class="absolute top-[2rpx] left-[-3rpx] flex items-center justify-center w-[50rpx] h-[50rpx]" :style="{ zIndex: 3 }">
                                        <text class="text-[20rpx] font-bold text-[#fff]">{{ goods.rank_num }}</text>
                                    </view>
                                    <u--image radius="var(--goods-rounded-big)" width="130rpx" height="130rpx" :src="img(goods.goods_cover_thumb_mid || '')" model="aspectFill">
                                        <template #error>
                                            <image class="w-[130rpx] h-[130rpx] rounded-[var(--goods-rounded-big)] overflow-hidden" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill" />
                                        </template>
                                    </u--image>
                                </view>
                                <view class="flex flex-col">
                                    <view class="leading-[1.3] multi-hidden w-[290rpx] text-[28rpx] whitespace-normal">{{ goods.goods_name }}</view>
                                    <view class="flex items-center justify-between mt-[auto]">
                                        <view class="text-[var(--price-text-color)] price-font flex items-baseline">
                                            <text class="text-[24rpx] font-500">￥</text>
                                            <text class="text-[40rpx] font-500">{{ diyGoods.goodsPrice(goods).toFixed(2).split('.')[0] }}</text>
                                            <text class="text-[24rpx] font-500">.{{ diyGoods.goodsPrice(goods).toFixed(2).split('.')[1] }}</text>
                                        </view>
                                        <view>
                                            <text class="iconfont icongouwuche3 text-[var(--primary-color)] border-[2rpx] border-solid border-[var(--primary-color)] rounded-[50%] text-[22rpx] p-[6rpx]"></text>
                                        </view>
                                    </view>
                                </view>
                            </view>
                        </view>
                    </view>
                </view>
            </view>

        </scroll-view>
    </view>
</template>

<script lang="ts" setup>
// 排行榜
import { ref, reactive, computed, watch, onMounted } from 'vue';
import useDiyStore from '@/app/stores/diy';
import { redirect, img } from '@/utils/common';
import { getRankComponentsGoodsList } from '@/addon/shop/api/rank'
import { useGoods } from '@/addon/shop/hooks/useGoods'

const props = defineProps(['component', 'index']);
const diyGoods = useGoods();
const diyStore = useDiyStore();

const diyComponent = computed(() => {
    if (diyStore.mode == 'decorate') {
        return diyStore.value[props.index];
    } else {
        return props.component;
    }
})

const warpCss = computed(() => {
    var style = '';
    style += 'position:relative;';
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

const swiperItemCss = (data: any) => {
    var style = '';
    if (data.listFrame.startColor) {
        if (data.listFrame.startColor && data.listFrame.endColor) style += 'background: linear-gradient(90deg, ' + data.listFrame.startColor + ', ' + data.listFrame.endColor + ');';
        else style = 'background-color:' + data.listFrame.startColor + ';';
    }
    if (data.bgUrl) {
        style += 'background-image:' + 'url(' + img(data.bgUrl) + ');';
        style += 'background-size: 100%;';
        style += 'background-repeat: no-repeat;';
    }

    if (diyComponent.value.topElementRounded) style += 'border-top-left-radius:' + diyComponent.value.topElementRounded * 2 + 'rpx;';
    if (diyComponent.value.topElementRounded) style += 'border-top-right-radius:' + diyComponent.value.topElementRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomElementRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomElementRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomElementRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomElementRounded * 2 + 'rpx;';
    return style;
}

watch(
    () => diyComponent.value,
    (newValue, oldValue) => {
        refresh();
    }, { deep: true })

const refresh = () => {
    // 装修模式下刷新
    if (diyStore.mode == 'decorate') {
        if (diyComponent.value && diyComponent.value.componentName == 'ShopGoodsRanking') {
            const fakeGoods = {
                goods_name: '商品名称',
                goods_cover_thumb_mid: '',
                goodsSku: {
                    show_price: 10
                },
                rank_num: 0  // 初始化为 0，后续会递增
            };
            diyComponent.value.list.forEach(item => {
                if (!item.goodsList) {
                    item.goodsList = [];
                }

                if (item.goodsList.length === 0) {
                    const fakeGoodsList = [];
                    // 生成三个商品，并为每个商品的 rank_num 递增
                    for (let i = 0; i < 3; i++) {
                        const newItem = { ...fakeGoods, rank_num: i + 1 };  // rank_num 从 1 开始递增
                        fakeGoodsList.push(newItem);
                    }
                    listGoods[item.rankIds[0]] = fakeGoodsList;
                }
            });
        }
    } else {
        getRankGoodsListFn();
    }

};

const listGoods = reactive({});
const listGoodsNames = reactive({});
const getRankGoodsListFn = () => {
    diyComponent.value.list.forEach((item) => {
        const rank_id = Array.isArray(item.rankIds) ? item.rankIds[0] : 0;
        const data = {
            rank_id: item.source === 'custom' ? rank_id : 0
        };
        getRankComponentsGoodsList(data).then((res) => {
            if (res.data && res.data.goods_list && res.data.goods_list.length > 0) {
                listGoods[item.rankIds[0]] = res.data.goods_list;
                listGoodsNames[item.rankIds[0]] = res.data.name;
            }
        }).catch((error) => {
            console.error('获取商品数据失败:', error);
        });

    });
};

function getRankBadge(sort: any) {
    switch (sort) {
        case 1:
            return img('addon/shop/rank/rank_first.png');
        case 2:
            return img('addon/shop/rank/rank_second.png');
        case 3:
            return img('addon/shop/rank/rank_third.png');
        default:
            return img('addon/shop/rank/rank.png');
    }
}

const toDetail = (id: string | number) => {
    redirect({ url: '/addon/shop/pages/goods/detail', param: { goods_id: id }, mode: 'navigateTo' })
}

onMounted(() => {
    refresh();
});
</script>

<style lang="scss" scoped>
</style>

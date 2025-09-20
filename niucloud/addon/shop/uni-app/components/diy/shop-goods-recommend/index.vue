<template>
    <x-skeleton :type="skeleton.type" :loading="skeleton.loading" :config="skeleton.config">
        <view :style="warpCss" v-if="goodsNum">
            <view class="w-full">
                <scroll-view :id="'warpStyle-'+diyComponent.id" class="whitespace-nowrap h-[341rpx] w-full" :scroll-x="true">
                    <template v-for="(item,index) in goodsList" :key="index">
                        <view v-if="item.info" :id="'item'+index+diyComponent.id"
                              class="w-[224rpx] h-[341rpx] mr-[20rpx] inline-block bg-[#fff] box-border overflow-hidden"
                              :class="{'!mr-[0rpx]' : index == (goodsList.length-1)}" :style="itemCss+itemStyle"
                              @click="toLink(item)">
                            <view class="w-full h-[134rpx]" :style="listFrameStyle(item)">
                                <view class="flex pl-[16rpx] pr-[20rpx] justify-between h-[63rpx] items-center">
                                    <view class="text-[28rpx] leading-[34rpx] flex-1 mr-[8rpx]" :style="{'color':item.title.textColor}">{{ item.title.text }}</view>
                                    <view class="w-[68rpx] h-[34rpx] text-[22rpx] text-center leading-[34rpx] text-[#fff] rounded-[17rpx]" :style="moreTitleStyle(item)">{{ item.moreTitle.text }}</view>
                                </view>
                            </view>
                            <view class="mt-[-71rpx] h-[278rpx] w-full px-[20rpx] pt-[18rpx] box-border bg-white" :style="itemCss">
                                <view class="flex items-center justify-center w-[184rpx] h-[184rpx]">
                                    <u--image width="184rpx" height="184rpx" :radius="imageRounded.val" :src="img(item.info.goods_cover_thumb_mid || '')" model="aspectFill">
                                        <template #error>
                                            <image class="w-[184rpx] h-[184rpx]" :style="imageRounded.style" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill" />
                                        </template>
                                    </u--image>
<!--                                    <easy-image class="w-[184rpx] h-[184rpx]" :image-src="item.info.goods_cover_thumb_small" :image-style="imageStyle" />-->
                                </view>
                                <view class="pt-[12rpx]">
                                    <view
                                        class="h-[44rpx] pl-[4rpx] min-w-[168rpx] box-border flex justify-between items-center mx-auto border-[2rpx] border-solid border-[var(--primary-color)] rounded-[20rpx]"
                                        :style="{'border-color':item.button.color}">
                                        <view class="text-[var(--price-text-color)] font-bold price-font flex items-baseline leading-[40rpx] flex-1 justify-center">
                                            <view class="leading-[1] max-w-[105rpx] truncate" :style="{ color : diyComponent.priceStyle.mainColor }">
                                                <text class="text-[18rpx] font-400 mr-[2rpx]">￥</text>
                                                <text class="text-[28rpx] font-500">{{ parseFloat(diyGoods.goodsPrice(item.info)).toFixed(2) }}</text>
                                            </view>
                                        </view>
                                        <view class="w-[70rpx] box-border text-right text-[#fff] pr-[8rpx] text-[22rpx] font-500 leading-[44rpx] rounded-tr-[20rpx] rounded-br-[20rpx] rounded-tl-[24rpx] relative"
                                            :style="{'background-color':item.button.color}">
                                            <text>{{ item.button.text }}</text>
                                            <image class="w-[24rpx] h-[44rpx] absolute top-[-2rpx] left-0" :src="img('/addon/shop/Union.png')" />
                                        </view>
                                    </view>
                                </view>
                            </view>
                        </view>
                    </template>
                </scroll-view>

            </view>
        </view>
    </x-skeleton>
</template>

<script setup lang="ts">
// 商品推荐
import { ref, reactive, computed, watch, onMounted, nextTick, getCurrentInstance } from 'vue';
import { redirect, img, deepClone } from '@/utils/common';
import useDiyStore from '@/app/stores/diy';
import { getGoodsComponents } from '@/addon/shop/api/goods';
import { useGoods } from '@/addon/shop/hooks/useGoods'

const diyGoods = useGoods();
const props = defineProps(['component', 'index', 'value']);
const diyStore = useDiyStore();
const emits = defineEmits(['loadingFn']); //商品数据加载完成之后触发

const goodsNum = ref(0);

const skeleton = reactive({
    type: '',
    loading: false,
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

const itemCss = computed(() => {
    let style = '';
    if (diyComponent.value.elementBgColor) style += 'background-color:' + diyComponent.value.elementBgColor + ';';
    if (diyComponent.value.topElementRounded) style += 'border-top-left-radius:' + diyComponent.value.topElementRounded * 2 + 'rpx;';
    if (diyComponent.value.topElementRounded) style += 'border-top-right-radius:' + diyComponent.value.topElementRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomElementRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomElementRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomElementRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomElementRounded * 2 + 'rpx;';
    return style;
})

const imageStyle = computed(() => {
    let style = 'border-radius:' + imageRounded.value.val + ';';
    return style;
})

//listFrame样式
const listFrameStyle = (item: any) => {
    let style = '';
    if (item.listFrame.startColor) {
        if (item.listFrame.startColor && item.listFrame.endColor) style = `background:linear-gradient( 110deg, ${ item.listFrame.startColor } 0%, ${ item.listFrame.endColor } 100%);`;
        else style = 'background-color:' + item.listFrame.startColor + ';';
    }
    return style
}

//moreTitle样式
const moreTitleStyle = (item: any) => {
    let style = '';
    if (item.moreTitle.startColor) {
        if (item.moreTitle.startColor && item.moreTitle.endColor) style = `background:linear-gradient( 0deg, ${ item.moreTitle.startColor } 0%, ${ item.moreTitle.endColor } 100%);`;
        else style = 'background-color:' + item.moreTitle.startColor + ';';
    }
    return style
}

//商品样式
const itemStyle = ref('');
const setItemStyle = () => {
    // #ifdef  MP-WEIXIN
    // uni.createSelectorQuery().in(instance).select('#warpStyle-'+diyComponent.value.id).boundingClientRect((res:any) => {
    // 	uni.createSelectorQuery().in(instance).select('#item0'+diyComponent.value.id).boundingClientRect((data:any) => {
    // 		itemStyle.value = `margin-right:${(res.width - data.width*3)/2}px;`
    // 	}).exec()
    // }).exec()
    // #endif
    // #ifdef  H5
    // itemStyle.value= 'margin-right:19rpx;'
    // #endif
    if (diyComponent.value.margin && diyComponent.value.margin.both) itemStyle.value = 'width: calc((100vw - ' + (diyComponent.value.margin.both * 4) + 'rpx - 40rpx) / 3);'
    else itemStyle.value = 'width: calc((100vw - 40rpx) / 3 );'
}

setItemStyle();

const getGoodsListFn = () => {
    let data: any = {}
    if (diyComponent.value.source == 'all') {
        data.num = diyComponent.value.list.length;
    } else if (diyComponent.value.source == 'custom') {
        data.goods_ids = diyComponent.value.goods_ids;
    }

    getGoodsComponents(data).then((res) => {
        let goodsObj = res.data;
        goodsNum.value = goodsObj.length || 0;
        diyComponent.value.list.filter((el: any, index) => {
            el.info = deepClone(goodsObj[index])
        });
        goodsList.value = deepClone(diyComponent.value.list)
        skeleton.loading = false;
    });
}

const instance = getCurrentInstance();

onMounted(() => {
    refresh();
});
watch(
    () => diyComponent.value,
    (newValue, oldValue) => {
        refresh();
    },
)
const refresh = () => {
    // 装修模式下设置默认图
    if (diyStore.mode == 'decorate') {
        nextTick(() => {
            if (diyComponent.value && diyComponent.value.list) {
                goodsList.value = diyComponent.value.list.map((el: any) => {
                    let obj = deepClone(el)
                    obj.info = {
                        goods_cover_thumb_mid: '',
                        goodsSku: {
                            show_price: '10.00'
                        }
                    }
                    return obj
                })
                goodsNum.value = 3;
                setItemStyle()
            }
        })
    } else {
        getGoodsListFn();
    }
}

const toLink = (data: any) => {
    redirect({ url: '/addon/shop/pages/goods/detail', param: { goods_id: data.info.goods_id } })
}

</script>

<style lang="scss" scoped>
</style>

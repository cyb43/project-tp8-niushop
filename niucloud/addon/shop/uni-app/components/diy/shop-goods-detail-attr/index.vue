<template>
    <view :style="warpCss" class="overflow-hidden">
        <view class="goods-sku card-template" v-if="diyComponent.goods && diyComponent.goods.attr_format && Object.keys(diyComponent.goods.attr_format).length">
            <view class="title mb-[30rpx]">商品属性</view>
            <view>
                <template v-for="(item,index) in diyComponent.goods.attr_format" :key="index">
                    <view v-if="index < 4 || isAttrFormatShow" class="card-template-item">
                        <text class="text-[26rpx] leading-[30rpx] w-[160rpx] font-400 shrink-0 text-[var(--text-color-light9)]">{{ item.attr_value_name }}</text>
                        <view class="text-[#333] box-border value-wid text-[26rpx] leading-[30rpx] font-400 pl-[20rpx]">{{ Array.isArray(item.attr_child_value_name) ? item.attr_child_value_name.join(',') : item.attr_child_value_name }}</view>
                    </view>
                </template>
                <view v-if="diyComponent.goods.attr_format.length > 4" class="flex-center" @click="isAttrFormatShow = !isAttrFormatShow">
                    <text class="text-[24rpx] mr-[10rpx]">{{ !isAttrFormatShow ? '展开' : '收起' }}</text>
                    <text class="nc-iconfont !text-[22rpx]" :class="{'nc-icon-xiaV6xx': !isAttrFormatShow, 'nc-icon-shangV6xx-1': isAttrFormatShow}"></text>
                </view>
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { img } from '@/utils/common';
import useDiyStore from '@/app/stores/diy';
import useGoodsDetailStore from '@/addon/shop/stores/goodsDetail'

const props = defineProps(['component', 'index', 'value']);
const diyStore = useDiyStore();
const emits = defineEmits(['update:componentIsShow']);

const diyComponent = computed(() => {
    if (diyStore.mode == 'decorate') {
        const obj = {
            goods: {
                attr_format: [
                    {
                       attr_value_name: '颜色',
                        attr_child_value_name: '红色,蓝色,灰色'
                    },{
                        attr_value_name: '尺寸',
                        attr_child_value_name: '大,中,小'
                    }
                ]
            }
        }
        return Object.assign({},obj,diyStore.value[props.index]);
    } else {
        return Object.assign({}, props.component, useGoodsDetailStore().goodsDetail);
    }
})

const warpCss = computed(() => {
    let style = '';
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

const isAttrFormatShow = ref(false); //控制属性是否展开

onMounted(() => {
    // 装修模式下刷新
    if (diyStore.mode == 'decorate') {
        watch(
            () => diyComponent.value,
            (newValue, oldValue) => {
                if (newValue && newValue.componentName == 'ShopGoodsDetailAttr') {
                    nextTick(() => {})
                }
            }
        )
    } else {
        watch(
            () => diyComponent.value,
            (newValue, oldValue) => {
                if (newValue && newValue.componentName == 'ShopGoodsDetailAttr') {
                    nextTick(() => {
                        if (diyComponent.value.goods && Object.keys(diyComponent.value.goods).length && diyComponent.value.goods.attr_format && Object.keys(diyComponent.value.goods.attr_format).length && diyComponent.value.isShow) {
                            emits('update:componentIsShow', true)
                        }else{
                            emits('update:componentIsShow', false)
                        }
                    })
                }
            },
            { immediate: true }
        )
    }
});
</script>
<style lang="scss" scoped>
.card-template{
    background-color: transparent !important;
    border-radius: 0 !important;
}
.goods-sku .value-wid {
    width: calc(100% - 160rpx);
}
</style>

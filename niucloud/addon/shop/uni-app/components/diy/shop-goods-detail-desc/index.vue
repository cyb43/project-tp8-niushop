<template>
    <view :style="warpCss" class="overflow-hidden" v-if="diyComponent && diyComponent.goods && Object.keys(diyComponent.goods).length">
        <view class="card-template overflow-hidden">
            <view class="title">商品详情</view>
            <view class="u-content">
                <u-parse :content="diyComponent.goods.goods_desc" :tagStyle="{img: 'vertical-align: top;width:100%;',p:'overflow: hidden;word-break:break-word;' }"></u-parse>
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
import { computed, watch, onMounted, nextTick } from 'vue';
import { img } from '@/utils/common';
import useDiyStore from '@/app/stores/diy';
import useGoodsDetailStore from '@/addon/shop/stores/goodsDetail'

const props = defineProps(['component', 'index', 'value']);
const diyStore = useDiyStore();
const emits = defineEmits(['update:componentIsShow']); //商品数据加载完成之后触发
const diyComponent = computed(() => {
    if (diyStore.mode == 'decorate') {
        const obj = {
            goods: {
                goods_desc: '这里展示商品详情内容'
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

onMounted(() => {
    // 装修模式下刷新
    if (diyStore.mode == 'decorate') {
        watch(
            () => diyComponent.value,
            (newValue, oldValue) => {
                if (newValue && newValue.componentName == 'ShopGoodsDetailDesc') {
                    nextTick(() => {})
                }
            }
        )
    } else {
        watch(
            () => diyComponent.value,
            (newValue, oldValue) => {
                if (newValue && newValue.componentName == 'ShopGoodsDetailDesc') {
                    if (!diyComponent.value.isShow) {
                        emits('update:componentIsShow', false)   
                    }else{
                        emits('update:componentIsShow', true)
                    }
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
    padding: 30rpx 0 0;
    .title{
       padding: 0 24rpx; 
    }
}
</style>

<template>
    <view :style="warpCss" class="overflow-hidden">
        <view class="card-template" v-if="diyComponent.sow_show_list && diyComponent.sow_show_list.list && diyComponent.sow_show_list.list.length">
            <view class="flex justify-between items-center" @click="toLink">
                <view class="text-[30rpx]">
                    <text>种草秀</text>
                    <text class="ml-[6rpx] text-[24rpx] text-[var(--text-color-light9)]" v-if="diyComponent.sow_show_list.count">
                        ({{ diyComponent.sow_show_list.count }})
                    </text>
                </view>
                <text class="nc-iconfont nc-icon-youV6xx text-[26rpx] text-[var(--text-color-light6)] ml-[8rpx]"></text>
            </view>

            <view class="grid grid-cols-4 gap-[15rpx] mt-[24rpx]">
                <view class="w-[160rpx] h-[160rpx]" v-for="(item, index) in diyComponent.sow_show_list.list" :key="index" @click="handleClick(item)">
                    <image :src="img(item.content_cover)" mode="aspectFill" class="w-[160rpx] h-[160rpx] rounded-[20rpx]" />
                </view>
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
import { computed, watch, ref, onMounted, nextTick } from 'vue';
import { img, redirect } from '@/utils/common';
import useDiyStore from '@/app/stores/diy';
import useGoodsDetailStore from '@/addon/shop/stores/goodsDetail'

const props = defineProps(['component', 'index', 'value']);
const diyStore = useDiyStore();
const emits = defineEmits(['update:componentIsShow']);

const treasureId = ref(null);
const diyComponent = computed(() => {
    if (diyStore.mode == 'decorate') {
        const obj = {
            sow_show_list: {
                count: 2,
                list: [{
                        content_cover: 'static/resource/images/diy/shop_default.jpg'
                    },
                    {
                        content_cover: 'static/resource/images/diy/shop_default.jpg'
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

const extractTreasureId = (url: any) => {
    const match = url.match(/treasure_id=(\d+)/);
    return match ? match[1] : null;
}

const toLink = () => {
    redirect({ url: '/addon/sow_community/pages/sow_show', param: { treasure_id: treasureId.value } })
}

const handleClick = (item: any) => {
    if (item.content_type == 1) {
        redirect({ url: '/addon/sow_community/pages/image/detail', param: { content_id: item.content_id } })
    } else {
        redirect({ url: '/addon/sow_community/pages/video/detail', param: { content_id: item.content_id } })
    }
}

onMounted(() => {
    // 装修模式下刷新
    if (diyStore.mode == 'decorate') {
        watch(
            () => diyComponent.value,
            (newValue, oldValue) => {emits('update:componentIsShow', false)
                if (newValue && newValue.componentName == 'ShopGoodsDetailSow') {
                    nextTick(() => {})
                }
            }
        )
    } else {
        if (diyComponent.value.sow_show_list && diyComponent.value.sow_show_list.url) {
            treasureId.value = extractTreasureId(diyComponent.value.sow_show_list.url);
        }
        watch(
            () => diyComponent.value,
            (newValue, oldValue) => {
                if (newValue && newValue.componentName == 'ShopGoodsDetailSow') {
                    if (diyComponent.value.sow_show_list && Object.keys(diyComponent.value.sow_show_list).length && diyComponent.value.sow_show_list.list && Object.keys(diyComponent.value.sow_show_list.list).length) {
                        emits('update:componentIsShow', true)
                    }else{
                        emits('update:componentIsShow', false)
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
}
</style>

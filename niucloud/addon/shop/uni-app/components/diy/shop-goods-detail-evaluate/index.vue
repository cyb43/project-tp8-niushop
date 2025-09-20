<template>
    <view :style="warpCss" class="overflow-hidden" v-if="diyComponent && diyComponent.goods && Object.keys(diyComponent.goods).length">
        <view v-if="diyComponent.evaluate_is_show" class="card-template">
            <view class="flex items-center justify-between min-h-[40rpx]" :class="{'mb-[30rpx]': evaluate && evaluate.list && evaluate.list.length}">
                <text class="title !mb-[0]">宝贝评价({{ evaluate.count }})</text>
                <view v-if="evaluate.count" class="h-[40rpx] flex items-center" @click="toLink(diyComponent.goods_id)">
                    <text class="text-[24rpx] text-[var(--text-color-light9)]">查看全部</text>
                    <text class="nc-iconfont nc-icon-youV6xx text-[26rpx] text-[var(--text-color-light9)]"></text>
                </view>
                <text v-if="!evaluate.count" class="text-[24rpx] text-[var(--text-color-light6)]">暂无评价</text>
            </view>
            <view>
                <view :class="{'pb-[34rpx]': index != (evaluate.list.length-1)}" v-for="(item, index) in evaluate.list" :key="index">
                    <view class="flex items-center w-full">
                        <u-avatar :default-url="img('static/resource/images/default_headimg.png')" :src="img(item.member_head)" :size="'50rpx'" leftIcon="none" />
                        <text class="ml-[10rpx] text-[28rpx] text-[#333]">{{ item.member_name }}</text>
                    </view>
                    <view class="flex justify-between w-full mt-[16rpx]">
                        <view class="flex-1 w-[540rpx] text-[26rpx] text-[#333] max-h-[72rpx] leading-[36rpx] multi-hidden mr-[50rpx]">{{ item.content }}</view>
                        <view class="w-[80rpx] shrink-0">
                            <u--image v-if="item.image_mid && item.image_mid.length" width="80rpx"
                                        height="80rpx" radius="var(--goods-rounded-mid)"
                                        :src="img(item.image_mid[0])" model="aspectFill"
                                        @click="imgListPreview(item.images[0])">
                                <template #error>
                                    <u-icon name="photo" color="#999" size="50"></u-icon>
                                </template>
                            </u--image>
                        </view>
                    </view>
                </view>
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { redirect, img } from '@/utils/common';
import useDiyStore from '@/app/stores/diy';
import { getEvaluateList } from '@/addon/shop/api/goods';
import useGoodsDetailStore from '@/addon/shop/stores/goodsDetail'

const props = defineProps(['component', 'index', 'value']);
const diyStore = useDiyStore();
const emits = defineEmits(['update:componentIsShow']); //商品数据加载完成之后触发
const diyComponent = computed(() => {
    if (diyStore.mode == 'decorate') {
        const obj = {
            evaluate_is_show: true,
            goods: {goods_type: 'real'},
            goods_id: ''
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

// 获取评价
const evaluate = ref({
    count: 0
})

const getEvaluateListFn = () => {
    getEvaluateList(diyComponent.value.goods_id).then((res: any) => {
        evaluate.value = res.data
    })
}

//进入评论
const toLink = () => {
    redirect({ url: '/addon/shop/pages/evaluate/list', param: { goods_id: diyComponent.value.goods_id } })
}

//预览图片
const imgListPreview = (item: any, index: any) => {
    if (Array.isArray(item)) {
        if (!item.length) return false
        var urlList = item;
        uni.previewImage({
            indicator: "number",
            current: index,
            loop: true,
            urls: urlList
        })
    } else {
        if (item === '') return false
        var urlList = []
        urlList.push(img(item))  //push中的参数为 :src="item.img_url" 中的图片地址
        uni.previewImage({
            indicator: "number",
            loop: true,
            urls: urlList
        })
    }

}

onMounted(() => {
    // 装修模式下刷新
    if (diyStore.mode == 'decorate') {
        evaluate.value = {
            list: [
                {
                    member_head: '',
                    member_name: '用户01',
                    content: '超级良心，值得信赖！',
                    image_mid: []
                },
                {
                    member_head: '',
                    member_name: '用户01',
                    content: '质量非常棒，服务也很到位！',
                    image_mid: []
                }
            ],
            count: 2
        }
    } else {
        watch(
            () => diyComponent.value,
            (newValue, oldValue) => {
                if (newValue && newValue.componentName == 'ShopGoodsDetailEvaluate') {
                    if (!diyComponent.value.isShow) {
                        emits('update:componentIsShow', false)   
                    }else{
                        emits('update:componentIsShow', true)
                    }
                }
            },
            { immediate: true }
        )
        // 获取评价
        getEvaluateListFn();
    }
});
</script>
<style lang="scss" scoped>
.card-template{
    background-color: transparent !important;
    border-radius: 0 !important;
}
</style>

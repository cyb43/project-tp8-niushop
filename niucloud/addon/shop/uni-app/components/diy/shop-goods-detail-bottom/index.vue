<template>
    <view class="overflow-hidden relative" v-if="diyComponent && diyComponent.goods && Object.keys(diyComponent.goods).length">
        <view v-if="diyStore.mode != 'decorate'" class="tab-bar-placeholder"></view>
        <view :style="warpCss" class="border-[0] border-t-[2rpx] border-solid border-[#f5f5f5] w-[100%] flex justify-between pl-[32rpx] pr-[4rpx] box-border tab-bar z-10 items-center" :class="{'fixed left-0 bottom-0': diyStore.mode != 'decorate'}">
            <view class="flex items-center">
                <view v-if="menuContent.includes('index')" class="flex flex-col justify-center items-center mr-[38rpx]" @click="redirect({ url: '/addon/shop/pages/index', mode: 'reLaunch' })">
                    <view class="nc-iconfont nc-icon-shouyeV6xx11 text-[36rpx]"></view>
                    <text class="text-[20rpx] mt-[10rpx]">首页</text>
                </view>
                <view v-if="menuContent.includes('service')" class="flex flex-col justify-center items-center mr-[38rpx]" @click="redirect({ url: '/app/pages/member/contact' })">
                    <text class="nc-iconfont text-[36rpx] nc-icon-kefuV6xx-1 text-[#303133]"></text>
                    <text class="text-[20rpx] mt-[10rpx]">客服</text>
                </view>
                <view v-if="menuContent.includes('cart')" class="flex flex-col justify-center items-center mr-[38rpx]" @click="redirect({ url: '/addon/shop/pages/goods/cart'})">
                    <view class="iconfont icongouwuche2 text-[38rpx]"></view>
                    <text class="text-[20rpx] mt-[10rpx]">购物车</text>
                </view>
                <view v-if="menuContent.includes('collect')" class="flex flex-col justify-center items-center mr-[38rpx]" @click="collectFn">
                    <text class="nc-iconfont text-[36rpx]" :class="{'text-[#ff0000] nc-icon-xihuanV6mm': isCollect, 'text-[#303133] nc-icon-guanzhuV6xx' : !isCollect}"></text>
                    <text class="text-[20rpx] mt-[10rpx]">收藏</text>
                </view>
                <view v-if="menuContent.includes('share')" class="flex flex-col justify-center items-center mr-[38rpx]"  @click="openShareFn">
                    <text class="nc-iconfont text-[36rpx] nc-icon-fenxiangV6xx text-[#303133]"></text>
                    <text class="text-[20rpx] mt-[10rpx]">分享</text>
                </view>
                <!-- #ifdef MP-WEIXIN -->
                <!-- <view>
                    <nc-contact
                        :send-message-title="sendMessageTitle"
                        :send-message-path="sendMessagePath"
                        :send-message-img="sendMessageImg">
                        <view class="flex flex-col justify-center items-center mr-[38rpx]">
                            <text class="nc-iconfont nc-icon-kefuV6xx-1 text-[36rpx]"></text>
                            <text class="text-[20rpx] mt-[10rpx]">客服</text>
                        </view>
                    </nc-contact>
                </view> -->
                <!-- #endif -->
            </view>
            <view class="flex flex-1" v-if="diyComponent.goods.status == 1">
                <button v-if="diyComponent.goods.is_gift"
                        class="!w-[420rpx] flex-1 !h-[70rpx] font-500 text-[26rpx] !text-[#fff] !bg-[#ccc] !m-0 leading-[70rpx] rounded-full remove-border"
                >商品为赠品不可购买</button>
                <template v-else-if="maxBuy > 0 || maxBuy == -1">
                    <button
                        v-if="diyGlobal?.goodsParameter?.type != 'newcomer_discount' && (diyComponent.goods.goods_type == 'real' || (diyComponent.goods.goods_type == 'virtual' && diyComponent.goods.virtual_receive_type != 'verify')) && diyComponent.cartIsShow"
                        class="flex-1 !h-[70rpx] font-500 text-[26rpx] !m-0 !mr-[16rpx] leading-[70rpx] rounded-full remove-border" @click="buyFn('join_cart')" :style="cartBtnStyle">
                        {{ diyComponent.cartName }}
                    </button>
                    <button
                        v-if="isShowSingleSku"
                        class="flex-1 !h-[70rpx] font-500 text-[26rpx] !m-0 !mr-[16rpx] leading-[70rpx] rounded-full remove-border" :style="buyBtnStyle"
                        @click="buyFn('buy_now')">{{ diyComponent.buyName }}
                    </button>
                    <button v-else
                            :style="{ width : (diyComponent.goods.goods_type == 'real' || (diyComponent.goods.goods_type == 'virtual' && diyComponent.goods.virtual_receive_type != 'verify')) ?  '200rpx' : '400rpx' + '!important'  }"
                            class="flex-1 !h-[70rpx] font-500 text-[26rpx] !text-[#fff] !bg-[#ccc] !m-0 !mr-[16rpx] leading-[70rpx] rounded-full remove-border"
                    >已售罄</button>
                </template>
                <button v-else-if="maxBuy == 0"
                        :style="{ width : '420rpx' + '!important'  }"
                        class="flex-1 !h-[70rpx] font-500 text-[26rpx] !text-[#fff] !bg-[#ccc] !m-0 leading-[70rpx] rounded-full remove-border"
                >已达限购数量</button>
            </view>
            <template v-else-if="diyStore.mode == 'decorate'">
                <button v-if="diyComponent.cartIsShow"
                    class="flex-1 !h-[70rpx] font-500 text-[26rpx] !m-0 !mr-[16rpx] leading-[70rpx] rounded-full remove-border" :style="cartBtnStyle">
                    {{ diyComponent.cartName }}
                </button>
                <button
                    class="flex-1 !h-[70rpx] font-500 text-[26rpx] !m-0 !mr-[16rpx] leading-[70rpx] rounded-full remove-border" :style="buyBtnStyle"
                    @click="buyFn('buy_now')">{{ diyComponent.buyName }}
                </button>
            </template>
            <view class="flex flex-1" v-else>
                <button class="w-[100%] !h-[70rpx] font-500 text-[26rpx] !text-[#fff] !bg-[#ccc] !m-0 leading-[70rpx] rounded-full remove-border">该商品已下架</button>
            </view>
        </view>
        <!-- 装修时，防止点击 -->
        <view v-if="diyStore.mode == 'decorate'" class="absolute z-10 top-0 right-0 bottom-0 left-0"></view>
    </view>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { redirect, img } from '@/utils/common';
import useDiyStore from '@/app/stores/diy';
import useMemberStore from '@/stores/member'
import { collect, cancelCollect } from '@/addon/shop/api/goods';
import useGoodsDetailStore from '@/addon/shop/stores/goodsDetail'

const props = defineProps(['component', 'index', 'value', 'global']);
const diyStore = useDiyStore();
const emits = defineEmits(['loadingFn', 'update:componentIsShow']); //商品数据加载完成之后触发
const sendMessageTitle = ref('')
const sendMessagePath = ref('')
const sendMessageImg = ref('')

// 会员信息
const memberStore = useMemberStore()
const userInfo = computed(() => memberStore.info)

const diyComponent = computed(() => {
    if (diyStore.mode == 'decorate') {
        const obj = {
            goods: {
                sow_show_list: ''
            }
        }
        return Object.assign({},obj,diyStore.value[props.index]);
    } else {
        return Object.assign({}, props.component, useGoodsDetailStore().goodsDetail);
    }
})

const diyGlobal = computed(() => {
    return props.global;
})

const warpCss = computed(() => {
    let style = '';
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

// 立即购买按钮
const buyBtnStyle = computed(() => {
    let style = '';
    if (diyComponent.value.buyStyle.startColor && diyComponent.value.buyStyle.endColor) style += `background:linear-gradient(${ diyComponent.value.buyStyle.gradientAngle },${ diyComponent.value.buyStyle.startColor },${ diyComponent.value.buyStyle.endColor });`;
    else style += 'background-color:' + (diyComponent.value.buyStyle.startColor || diyComponent.value.buyStyle.endColor) + ';';

    if (diyComponent.value.buyStyle.textColor) style += 'color:' + diyComponent.value.buyStyle.textColor+';';
    if (diyComponent.value.buyStyle.fontSize) style += 'font-size:' + diyComponent.value.buyStyle.fontSize * 2 + 'rpx;';

    if (diyComponent.value.goods.goods_type == 'real' || (diyComponent.value.goods.goods_type == 'virtual' && diyComponent.value.goods.virtual_receive_type != 'verify')){
        style += 'width:200rpx !important;';
    }else{
        style += 'width:400rpx !important;';
    }
    return style;
})

// 加入购物车按钮
const cartBtnStyle = computed(() => {
    let style = '';
    if (diyComponent.value.cartStyle.startColor && diyComponent.value.cartStyle.endColor) style += `background:linear-gradient(${ diyComponent.value.cartStyle.gradientAngle }, ${ diyComponent.value.cartStyle.startColor },${ diyComponent.value.cartStyle.endColor });`;
    else style += 'background-color:' + (diyComponent.value.cartStyle.startColor || diyComponent.value.cartStyle.endColor) + ';';

    if (diyComponent.value.cartStyle.textColor) style += 'color:' + diyComponent.value.cartStyle.textColor+';';
    if (diyComponent.value.cartStyle.fontSize) style += 'font-size:' + diyComponent.value.cartStyle.fontSize * 2 + 'rpx;';
    return style;
})

// 收藏
const isCollect: any = ref(0);
const collectFn = () => {
    // 检测是否登录
    if (!userInfo.value) {
        uni.showToast({
            title: '未登录，请先登录后再收藏商品',
            icon: 'none'
        });
        return false
    }
    let api = isCollect.value ? cancelCollect({ goods_ids: [diyComponent.value.goods_id] }) : collect(diyComponent.value.goods_id);
    api.then(res => {
        isCollect.value = !isCollect.value;
        if (isCollect.value) {
            uni.showToast({
                title: '收藏成功',
                icon: 'none'
            });
        } else {
            uni.showToast({
                title: '取消收藏',
                icon: 'none'
            });
        }
    })
}

// 商品限购
const maxBuy = ref(-1);
const goodsMaxBuy = (data: any = {}) => {
    // 限购 - 是否开启限购
    if (data.goods.is_limit && userInfo.value && data.goods.stock > 0) {
        if (data.goods.max_buy) {
            let max_buy = 0;
            if (data.goods.limit_type == 1) { //单次限购
                max_buy = data.goods.max_buy;
            } else { // 单人限购
                let buyVal = data.goods.max_buy - (data.goods.has_buy || 0);
                max_buy = buyVal > 0 ? buyVal : 0;
            }

            if (max_buy > data.goods.stock) {
                maxBuy.value = data.goods.stock
            } else if (max_buy <= data.goods.stock) {
                maxBuy.value = max_buy;
            }
        }
    }
}

// 判断单规格库存是否为0
const isShowSingleSku = computed(() => {
    let isSingleSpec = false // 是否为单规格，true：多规格，false：单规格
    diyComponent.value.skuList.forEach((item: any, index: any) => {
        if (item.sku_spec_format) {
            isSingleSpec = true
        }
    })

    // 单规格，库存为0，显示已售罄
    if (!isSingleSpec && diyComponent.value.stock <= 0) {
        return false;
    } else if (!isSingleSpec && diyComponent.value.stock > 0) {
        // 单规格，库存大于0，可以购买
        return true;
    }
    return true;
})

const buyFn = (type: any) => {
    useGoodsDetailStore().setGoodsDetail({isOpenSkuBuy: true, skuBuyType: type});
}

onMounted(() => {
    refresh();
    // 装修模式下刷新
    if (diyStore.mode == 'decorate') {
        watch(
            () => diyComponent.value,
            (newValue, oldValue) => {
                if (newValue && newValue.componentName == 'ShopGoodsDetailBottom') {
                    nextTick(() => {
                        menuContent.value = (typeof diyComponent.value?.menuContent == 'object') ? diyComponent.value?.menuContent : diyComponent.value?.menuContent.split(',');
                    })
                }
            }
        )
    } else {
        watch(
            () => diyComponent.value,
            (newValue, oldValue) => {
                
            }
        )
        watch(
            () => diyComponent.value,
            (newValue, oldValue) => {
                if (newValue && newValue.componentName == 'ShopGoodsDetailBottom') {
                    refresh();
                    emits('update:componentIsShow', true)
                }
            },
            { immediate: true }
        )
        initFn()
    }
    // 商品限购
    goodsMaxBuy(diyComponent.value);
});

const menuContent = ref([])
const initFn = () => {
    isCollect.value = diyComponent.value.goods.is_collect;
    sendMessageTitle.value = diyComponent.value.goods.goods_name
    sendMessagePath.value = '/addon/shop/pages/goods/detail?sku_id=' + diyComponent.value.sku_id;
    if (diyComponent.value.type) {
        sendMessagePath.value += '&type=' + diyComponent.value.type;
    }
    sendMessageImg.value = img(diyComponent.value.goods.goods_cover_thumb_mid) 
    menuContent.value = (typeof diyComponent.value?.menuContent == 'object') ? diyComponent.value?.menuContent : diyComponent.value?.menuContent.split(',');
}

/************* 分享海报-start **************/
const openShareFn = () => {
    useGoodsDetailStore().setGoodsDetail({isOpenSharePoster: true});
}
/************* 分享海报-end **************/

const refresh = () => {
    menuContent.value = (typeof diyComponent.value?.menuContent == 'object') ? diyComponent.value?.menuContent : diyComponent.value?.menuContent.split(',');
}
</script>
<style lang="scss" scoped>
.tab-bar-placeholder {
    padding-bottom: calc(constant(safe-area-inset-bottom) + 100rpx);
    padding-bottom: calc(env(safe-area-inset-bottom) + 100rpx);
}
.tab-bar {
    padding-top: 16rpx;
    padding-bottom: calc(constant(safe-area-inset-bottom) + 16rpx);
    padding-bottom: calc(env(safe-area-inset-bottom) + 16rpx);
}
</style>

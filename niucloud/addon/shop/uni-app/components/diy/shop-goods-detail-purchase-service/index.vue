<template>
    <!-- 商品服务 -->
    <view :style="warpCss" class="overflow-hidden relative" v-if="diyComponent && diyComponent.goods && Object.keys(diyComponent.goods).length">
        <view class="card-template" v-if="serviceConfig && serviceConfig.length && isShowTemplate">
            <view @click="servicesDataShow = !servicesDataShow" v-if="diyComponent.service && diyComponent.service.length && serviceConfig.includes('goods_service')" class="card-template-item">
                <text class="text-[#333] text-[26rpx] leading-[30rpx] font-400 shrink-0">服务</text>
                <view class="text-[#343434] text-[26rpx] leading-[30rpx] font-400 truncate ml-auto">{{ diyComponent.service[0].service_name }}</view>
                <text class="nc-iconfont nc-icon-youV6xx text-[26rpx] text-[var(--text-color-light6)] ml-[8rpx]"></text>
            </view>
            <view @click="buyFn" v-if="diyComponent.goodsSpec && diyComponent.goodsSpec.length && serviceConfig.includes('spec_select')" class="card-template-item">
                <text class="text-[#333] text-[26rpx] leading-[30rpx] font-400 shrink-0 mr-[20rpx]">已选</text>
                <view class="ml-auto text-right truncate flex-1 text-[#343434] text-[26rpx] leading-[30rpx] font-400">{{ diyComponent.sku_spec_format }}</view>
                <text class="nc-iconfont nc-icon-youV6xx text-[26rpx] text-[var(--text-color-light6)] ml-[8rpx]"></text>
            </view>
            <view class="card-template-item" @click="distributionDataOpen"
                    v-if="diyComponent.goods.goods_type == 'real'&&diyComponent.goods.delivery_type_list&&diyComponent.goods.delivery_type_list.length && serviceConfig.includes('delivery_info')">
                <text class="text-[#333] text-[26rpx] leading-[30rpx] font-400 shrink-0">配送</text>
                <view class="ml-auto flex items-center text-[#343434] text-[26rpx] leading-[30rpx] font-400">{{ diyComponent.goods.delivery_type_list[selectDeliveryType].name }}</view>
                <text class="nc-iconfont nc-icon-youV6xx text-[26rpx] text-[var(--text-color-light6)] ml-[8rpx]"></text>
            </view>
            <view @click="couponListShow = true" v-if="couponList.length && serviceConfig.includes('coupons')" class="card-template-item">
                <text class="text-[#333] text-[26rpx] leading-[30rpx] font-400 shrink-0 mr-[20rpx]">领券</text>
                <view class="ml-auto flex-1 flex-nowrap flex items-center overflow-hidden h-[44rpx] content-between">
                    <template v-for="(item, index) in couponList" :key="index">
                        <text v-if="index < 3"
                                class="tag-item whitespace-nowrap border-[2rpx] px-[6rpx] h-[40rpx] border-solid border-[var(--primary-color)] text-[var(--primary-color)] mt-[4rpx]"
                                :class="{'mr-[12rpx]': couponList.length != (index+1) && index < 2, 'ml-auto': index == 0}">{{ item.title }}</text>
                    </template>
                </view>
                <text class="nc-iconfont nc-icon-youV6xx text-[26rpx] text-[var(--text-color-light6)] ml-[8rpx]"></text>
            </view>
            <view class="card-template-item" @click="manjianOpenFn" v-if="Object.keys(manjianData).length && serviceConfig.includes('activity')">
                <text class="text-[#333] text-[26rpx] leading-[30rpx] font-400 shrink-0 mr-[20rpx]">优惠</text>
                <view class="ml-auto flex-1 flex-nowrap flex items-center overflow-hidden h-[44rpx] justify-end">
                    <view class="bg-[var(--primary-color-light)] text-[var(--primary-color)] rounded-[6rpx]	text-[22rpx] flex items-center justify-center w-[86rpx] h-[34rpx] mr-[6rpx]">满减送</view>
                    <view class="truncate max-w-[430rpx] text-[26rpx]">{{ manjianData.name }}</view>
                </view>
                <text class="nc-iconfont nc-icon-youV6xx text-[26rpx] text-[var(--text-color-light6)] ml-[8rpx]"></text>
            </view>
        </view>
        <!-- 服务 -->
        <view @touchmove.prevent.stop>
            <u-popup class="popup-type" :show="servicesDataShow" @close="servicesDataShow = false">
                <view class="min-h-[480rpx] popup-common" @touchmove.prevent.stop>
                    <view class="title">商品服务</view>
                    <scroll-view class="h-[520rpx]" scroll-y="true">
                        <view class="pl-[22rpx] pb-[28rpx] pr-[37rpx]">
                            <view class="flex mb-[28rpx]" v-for="(item, index) in diyComponent.service">
                                <image class="mt-[4rpx] w-[32rpx] h-[32rpx] mr-[14rpx]" :src="img(item.image || 'addon/shop/icon_service.png')" mode="aspectFit" />
                                <view class="flex-1">
                                    <view class="text-[30rpx] leading-[36rpx] text-[#333] mb-[12rpx]">{{ item.service_name }}</view>
                                    <view class="text-[24rpx] leading-[36rpx] text-[var(--text-color-light9)]">{{ item.desc }}</view>
                                </view>
                            </view>
                        </view>
                    </scroll-view>
                </view>
            </u-popup>
        </view>
        <!-- 配送 -->
        <view @touchmove.prevent.stop>
            <u-popup class="popup-type" :show="distributionDataShow" @close="distributionDataShow = false">
                <view class="min-h-[360rpx] popup-common" @touchmove.prevent.stop>
                    <view class="title">配送方式</view>
                    <scroll-view class="h-[520rpx]" scroll-y="true">
                        <view class="px-[var(--popup-sidebar-m)]">
                            <view class="flex mb-[40rpx]" v-for="(item, index) in diyComponent.goods.delivery_type_list" @click="distributionListFn(item,index)">
                                <image class="mt-[4rpx] w-[32rpx] h-[32rpx] mr-[14rpx]" :src="img('addon/shop/icon_service.png')" mode="aspectFit" />
                                <view class="flex-1">
                                    <view class="text-[30rpx] leading-[36rpx] text-[#333] mb-[8rpx]">{{ item.name }}</view>
                                    <view class="text-[24rpx] leading-[36rpx] text-[var(--text-color-light9)]">{{ item.desc }}</view>
                                </view>
                            </view>
                        </view>
                    </scroll-view>
                </view>
            </u-popup>
        </view>
        <!-- 优惠券 -->
        <view @touchmove.prevent.stop>
            <u-popup class="popup-type" :show="couponListShow" @close="couponListShow = false">
                <view class="min-h-[480rpx] popup-common" @touchmove.prevent.stop>
                    <view class="title">优惠券</view>
                    <scroll-view class="h-[520rpx]" scroll-y="true">
                        <view class="px-[32rpx]">
                            <view class="mb-[30rpx] flex items-center border-[2rpx] border-solid border-[rgba(0,0,0,.1)] rounded-[var(--rounded-small)]"
                                v-for="(item, index) in couponList" :key="index">
                                <view class="flex flex-col items-center my-[20rpx] w-[200rpx] border-0 border-r-[2rpx] border-dashed border-[rgba(0,0,0,.1)]">
                                    <view class="text-xs price-font">
                                        <text class="text-[28rpx]">￥</text>
                                        <text class="text-[48rpx]">{{ Number(item.price) }}</text>
                                    </view>
                                    <text class="text-xs mt-[12rpx]">{{ Number(item.min_condition_money) ? ('满' + Number(item.min_condition_money) + '元可用') : '无门槛' }}</text>
                                </view>
                                <view class="ml-[20rpx] flex-1 flex flex-col py-[20rpx]">
                                    <text class="text-xs font-500">{{ item.title }}</text>
                                    <text class="text-xs text-[var(--text-color-light6)] mt-[12rpx]">{{ item.valid_type == 1 && ('领取之日起' + item.length + '天内有效') || item.valid_type == 2 && ('领取之日起至' + item.valid_end_time) }}</text>
                                </view>
                                <text v-if="item.btnType === 'collecting'" class="bg-[var(--primary-color)] mr-[20rpx] w-[106rpx] box-border text-center text-[#fff] h-[50rpx] text-[22rpx] px-[20rpx] leading-[50rpx] rounded-[100rpx]" @click="getCouponFn(item, index)">领取</text>
                                <text v-else class="!bg-[var(--primary-help-color4)] mr-[20rpx] text-[#fff] mr-[20rpx] h-[50rpx] text-[22rpx] px-[20rpx] leading-[50rpx] rounded-[100rpx]">{{ item.btnType === 'collected' ? '已领完' : '已领取' }}</text>
                            </view>
                        </view>
                    </scroll-view>
                    <view class="btn-wrap">
                        <button class="primary-btn-bg btn" @click="couponListShow = false">确定</button>
                    </view>
                </view>
            </u-popup>
        </view>
        <ns-goods-manjian ref="manjianShowRef"></ns-goods-manjian>
        <!-- 装修时，防止点击 -->
        <view v-if="diyStore.mode == 'decorate'" class="absolute z-10 top-0 right-0 bottom-0 left-0"></view>
    </view>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { img } from '@/utils/common';
import useDiyStore from '@/app/stores/diy';
import { getShopCouponList, getCoupon } from '@/addon/shop/api/coupon';
import { useLogin } from '@/hooks/useLogin'
import useMemberStore from '@/stores/member'
import { getManjian } from '@/addon/shop/api/goods';
import nsGoodsManjian from '@/addon/shop/components/ns-goods-manjian/ns-goods-manjian.vue';
import useGoodsDetailStore from '@/addon/shop/stores/goodsDetail'

const props = defineProps(['component', 'index', 'value', 'global']);
const diyStore = useDiyStore();
const emits = defineEmits(['update:componentIsShow']); //商品数据加载完成之后触发

const servicesDataShow = ref<boolean>(false)
const distributionDataShow = ref<boolean>(false) //配送
const couponListShow = ref<boolean>(false) //优惠券
const manjianShowRef: any = ref(null); //满减送

// 会员信息
const memberStore = useMemberStore()
const userInfo = computed(() => memberStore.info)

const diyComponent = computed(() => {
    if (diyStore.mode == 'decorate') {
        const obj = {
            service: [{service_name: '24小时配送'}],
            goods: {
                delivery_type_list: [
                    {
                        name: '物流配送'
                    }
                ]
            },
            goodsSpec: ['红色', '小'],
            sku_spec_format: '红色,小',
        }
        return Object.assign({}, obj, diyStore.value[props.index]);
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

// 判断商品属性模块是否展示
const isShowTemplate = ref(false);
const isGoodsPropertyTemp = () => {
    isShowTemplate.value = false;
    if (
        (diyComponent.value.service && diyComponent.value.service.length && serviceConfig.value.includes('goods_service')) ||
        (diyComponent.value.goodsSpec && diyComponent.value.goodsSpec.length && serviceConfig.value.includes('spec_select')) ||
        (diyComponent.value.goods.goods_type == 'real' && diyComponent.value.goods.delivery_type_list && diyComponent.value.goods.delivery_type_list.length && serviceConfig.value.includes('delivery_info')) || 
        (couponList.value && couponList.value.length && serviceConfig.value.includes('coupons')) || 
        (Object.keys(manjianData.value).length && serviceConfig.value.includes('activity')) || diyStore.mode == 'decorate'
        ) {
        isShowTemplate.value = true;
    }
    emits('update:componentIsShow', isShowTemplate.value)
}

// 优惠券
const couponList = ref([]);
const getShopCouponListFn = () => {
    getShopCouponList({
        category_id: diyComponent.value?.goods?.goods_category || '',
        goods_id: diyComponent.value.goods_id || ''
    }).then((res: any) => {
        couponList.value = res.data.data.map((el: any) => {
            if (el.sum_count != -1 && el.receive_count === el.sum_count) {
                el.btnType = 'collected'//已领完
            }
            if (!userInfo.value) {
                el.btnType = 'collecting'//领用
            } else {
                if (el.is_receive && el.limit_count === el.member_receive_count) {
                    el.btnType = 'using'//去使用
                } else {
                    el.btnType = 'collecting'//领用
                }
            }
            return el
        });
        isGoodsPropertyTemp()
    })
}

// 领取优惠券
const getCouponFn = (data: any, index: any) => {
    // 检测是否登录
    if (!userInfo.value) {
        useLogin().setLoginBack({
            url: '/addon/shop/pages/goods/detail',
            param: { sku_id: diyComponent.value.sku_id, type: diyComponent.value.type }
        })
        return false
    }
    getCoupon({
        coupon_id: data.id || '',
        number: 1,
    }).then(res => {
        getShopCouponListFn();
    })
}

const manjianOpenFn = () => {
    manjianShowRef.value.open(manjianData.value);
}

// 获取满减信息
let manjianData = ref({})
const getManjianInfo = () => {
    getManjian({
        goods_id: diyComponent.value.goods_id || '',
        sku_id: diyComponent.value.sku_id || ''
    }).then((res: any) => {
        if (Object.keys(res.data).length) {
            manjianData.value.condition_type = res.data.condition_type;
            manjianData.value.rule_json = res.data.rule_json;
            manjianData.value.name = res.data.manjian_name;
        }
        isGoodsPropertyTemp()
    })
}

/************ 选择配送方式-start ****************/
const selectDeliveryType = ref(0);
const distributionDataOpen = (() => {
    distributionDataShow.value = true;
});
const distributionListFn = ((data: any, index: any) => {
    selectDeliveryType.value = index;
    distributionDataShow.value = false;
    uni.setStorageSync('distributionType', data.name);
});
/************ 选择配送方式-end ****************/

const buyFn = (type: any) => {
    useGoodsDetailStore().setGoodsDetail({isOpenSkuBuy: true, skuBuyType: type});
}

onMounted(() => {
    refresh()
    // 装修模式下刷新
    if (diyStore.mode == 'decorate') {
        couponList.value = [
            { title: '满20减5' },
            { title: '满10减2' }
        ]
        manjianData.value = {
            name: '满100送积分'
        }
        isGoodsPropertyTemp()
        watch(
            () => diyComponent.value,
            (newValue, oldValue) => {
                if (newValue && newValue.componentName == 'ShopGoodsDetailPurchaseService') {
                    nextTick(() => {
                        refresh()
                    })
                }
            }
        )
    } else {
        // 获取优惠券列表
        getShopCouponListFn();
        getManjianInfo();
        watch(
            () => diyComponent.value,
            (newValue, oldValue) => {
                if (newValue && newValue.componentName == 'ShopGoodsDetailPurchaseService') {
                    emits('update:componentIsShow', isShowTemplate.value)
                }
            },
            { immediate: true }
        )
    }
});

const serviceConfig = ref([])
const refresh = () =>{
    serviceConfig.value = (typeof diyComponent.value?.serviceConfig == 'object') ? diyComponent.value?.serviceConfig : diyComponent.value?.serviceConfig.split(',');
}
</script>
<style lang="scss" scoped>
.card-template{
    background-color: transparent !important;
    border-radius: 0 !important;
}
</style>

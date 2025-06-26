<template>
    <!-- 满减 -->
    <view @touchmove.prevent.stop>
        <u-popup class="manjian-popup" :show="manjianShow" @close="manjianShow = false" zIndex="999999">
            <view class="min-h-[480rpx] popup-common" @touchmove.prevent.stop>
                <view class="title !pb-[30rpx]">满减送</view>
                <scroll-view class="h-[520rpx]" scroll-y="true">
                    <view class="px-[var(--popup-sidebar-m)] pt-[30rpx]">
                        <view v-for="(item,index) in data.content" :key="index" class="mb-[40rpx]">
                            <view class="flex items-center">
                                <text class="nc-iconfont nc-icon-qianbaoyueV6xx !text-[28rpx] mr-[10rpx]"></text>
                                <text class="text-[26rpx] font-500">{{ item.limit }}</text>
                            </view>
                            <view class="mt-[20rpx]">
                                <view v-if="item.goods && item.goods.length" class="flex mt-[20rpx]">
                                    <view class="w-[100rpx] flex justify-end">
                                        <view class="bg-[var(--primary-color-light)] text-[var(--primary-color)] rounded-[6rpx] text-[22rpx] flex items-center justify-center px-[12rpx] h-[38rpx] mr-[6rpx]">赠品</view>
                                    </view>
                                    <view class="flex-1 ml-[8rpx]">
                                        <view class="flex p-[20rpx] bg-[#f8f8f8] rounded-[var(--goods-rounded-big)] overflow-hidden"
                                            :class="{'mb-[20rpx]': goodsIndex != (item.goods.length-1)}"
                                            v-for="(goodsItem,goodsIndex) in item.goods" :key="goodsIndex"
                                            @click="goodsEvent(goodsItem.goods_id)">
                                            <u--image radius="var(--goods-rounded-mid)" width="120rpx" height="120rpx" :src="img(goodsItem.sku_image)" model="aspectFill">
                                                <template #error>
                                                    <image class="w-[120rpx] h-[120rpx] rounded-[var(--goods-rounded-big)] overflow-hidden" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill" />
                                                </template>
                                            </u--image>
                                            <view
                                                class="flex flex-1 w-0 flex-col justify-between ml-[20rpx] pt-[6rpx] pb-[10rpx]">
                                                <view class="truncate text-[#303133] text-[24rpx] leading-[32rpx]">{{ goodsItem.goods_name }}</view>
                                                <view class="flex items-baseline">
                                                    <view v-if="goodsItem.sku_name" class="truncate text-[22rpx] mt-[4rpx] text-[#999]">{{ goodsItem.sku_name }}</view>
                                                    <view class="font-400 ml-[auto] text-[24rpx] text-[#303133]">
                                                        <text>x</text>
                                                        <text>{{ goodsItem.num }}</text>
                                                    </view>
                                                </view>
                                            </view>
                                        </view>
                                    </view>
                                </view>
                                <template v-if="item.give && item.give.length">
                                    <view class="flex items-center mt-[24rpx]" v-for="(giveItem,giveIndex) in item.give" :key="giveIndex">
                                        <view class="w-[100rpx] flex justify-end">
                                            <view class="bg-[var(--primary-color-light)] text-[var(--primary-color)] rounded-[6rpx] text-[22rpx] flex items-center justify-center px-[12rpx] h-[38rpx] mr-[6rpx]">{{ giveItem.label }}</view>
                                        </view>
                                        <text class="text-[24rpx]">{{ giveItem.content }}</text>
                                    </view>
                                </template>
                                <view class="flex items-baseline mt-[24rpx]" v-if="item.coupon && item.coupon.length">
                                    <view class="w-[100rpx] flex justify-end">
                                        <view class="bg-[var(--primary-color-light)] text-[var(--primary-color)] rounded-[6rpx] text-[22rpx] flex items-center justify-center px-[12rpx] h-[38rpx] mr-[6rpx]">优惠券</view>
                                    </view>
                                    <view class="flex flex-wrap flex-1">
                                        <text class="flex items-center text-[24rpx] leading-[1.3]" :class="{'mb-[16rpx]': couponIndex != (item.coupon.length-1)}" v-for="(couponItem,couponIndex) in item.coupon" :key="couponIndex">{{ couponItem.num }}张{{ couponItem.coupon_name }}优惠券</text>
                                    </view>
                                </view>
                            </view>
                        </view>
                    </view>
                </scroll-view>
                <view class="btn-wrap">
                    <button class="primary-btn-bg btn" @click="manjianShow = false">确定</button>
                </view>
            </view>
        </u-popup>
    </view>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { img, deepClone, redirect } from '@/utils/common'
import { cloneDeep } from 'lodash-es'
import { t } from '@/locale'

const manjianShow = ref(false);
const data = ref({});
const open = (parameter: any = {}) => {
    data.value = cloneDeep(parameter);
    data.value.content = [];
    data.value.rule_json.forEach((item, index) => {
        if (item.is_show || item.is_show == undefined) {
            let obj = {};
            obj.limit = `门槛满${ data.value.condition_type == 'over_n_yuan' ? parseFloat(item.limit).toFixed(2) : item.limit }${ data.value.condition_type == 'over_n_yuan' ? '元' : '件' }`;
            if (item.is_give_goods) {
                obj.goods = deepClone(item.goods);
            }
            obj.give = [];
            if (item.is_discount && item.discount_money) {
                obj.give.push({
                    label: '满减',
                    content: `订单金额${ item.discount_type == 1 ? '减' : '打' }${ item.discount_type == 1 ? parseFloat(item.discount_money).toFixed(2) : item.discount_money }${ item.discount_type == 1 ? '元' : '折' }`
                })
            }
            if (item.is_free_shipping) {
                obj.give.push({
                    label: '包邮',
                    content: '商品包邮'
                });
            }
            if (item.is_give_point && item.point) {
                obj.give.push({
                    label: '积分',
                    content: `送${ item.point }积分`
                });
            }
            if (item.is_give_balance && item.balance) {
                obj.give.push({
                    label: '余额',
                    content: `送${ parseFloat(item.balance).toFixed(2) }余额`
                });
            }
            if (item.is_give_coupon) {
                obj.coupon = item.coupon;
            }
            data.value.content.push(obj);
        }
    });
    manjianShow.value = true;
}

const goodsEvent = (id: number) => {
    redirect({
        url: '/addon/shop/pages/goods/detail',
        param: {
            goods_id: id
        }
    })
}

defineExpose({
    open
})
</script>

<style lang="scss" scoped>
::v-deep .manjian-popup .u-slide-up-enter-to {
    z-index: 999999 !important;
}
</style>

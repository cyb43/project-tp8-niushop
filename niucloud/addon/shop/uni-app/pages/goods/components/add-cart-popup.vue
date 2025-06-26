<template>
    <view @touchmove.prevent.stop>
        <u-popup :show="goodsSkuPop" @close="closeFn" mode="bottom" zIndex="999999">
            <view v-if="Object.keys(goodsDetail).length" @touchmove.prevent.stop class="rounded-t-[20rpx] overflow-hidden bg-[#fff] py-[32rpx] relative">
                <view class="flex px-[32rpx] mb-[58rpx]">
                    <u--image width="180rpx" height="180rpx" :radius="'var(--goods-rounded-big)'" :src="img(detail.sku_image)" model="aspectFill">
                        <template #error>
                            <image class="w-[180rpx] h-[180rpx] rounded-[var(--goods-rounded-big)] overflow-hidden" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill" />
                        </template>
                    </u--image>

                    <view class="flex flex-1 flex-col justify-between ml-[20rpx] py-[10rpx]">
                        <view class="w-[100%]">
                            <view class=" text-[var(--price-text-color)]  flex items-baseline">
                                <text class="text-[32rpx] font-bold price-font mr-[4rpx]">￥</text>
                                <text class="text-[48rpx] price-font">{{ parseFloat(goodsPrice(detail)).toFixed(2).split('.')[0] }}</text>
                                <text class="text-[32rpx] price-font">.{{ parseFloat(goodsPrice(detail)).toFixed(2).split('.')[1] }}</text>
								<image v-if="priceType(detail) == 'member_price'" class="max-w-[50rpx] h-[28rpx] ml-[6rpx]" :src="img('addon/shop/VIP.png')" mode="heightFix" />
								<image v-else-if="priceType(detail) == 'newcomer_price'"  class="max-w-[60rpx] h-[28rpx] ml-[6rpx]" :src="img('addon/shop/newcomer.png')" mode="heightFix" />
								<image v-else-if="priceType(detail) == 'discount_price'" class="max-w-[80rpx] h-[28rpx] ml-[6rpx]" :src="img('addon/shop/discount.png')" mode="heightFix" />	 
                            </view>
                            <view class="text-[26rpx] leading-[32rpx] text-[#303133] mt-[12rpx]">库存{{ detail.stock }}{{ goodsDetail.goods.unit }}</view>
                        </view>
                        <view
                            class="w-[100%] text-[26rpx] leading-[30rpx] text-[var(--text-color-light6)] multi-hidden max-h-[60rpx]"
                            v-if="goodsDetail.goodsSpec && goodsDetail.goodsSpec.length">
                            已选规格：{{ detail.sku_spec_format }}
                        </view>
                    </view>
                </view>
                <scroll-view class="h-[500rpx] box-border px-[32rpx] mb-[30rpx]" scroll-y="true">
                    <view :class="{'mt-[36rpx]': 0 != index }" v-for="(item, index) in goodsDetail.goodsSpec" :key="index">
                        <view class="text-[26rpx] leading-[36rpx] mb-[24rpx]">{{ item.spec_name }}</view>
                        <view class="flex flex-wrap">
                            <view class="box-border bg-[#f2f2f2] text-[24rpx] px-[44rpx] text-center h-[56rpx] leading-[52rpx] mr-[20rpx] mb-[20rpx] border-1 border-solid rounded-[50rpx] border-[#f2f2f2]"
                                :class="{'!border-[var(--primary-color)] text-[var(--primary-color)] !bg-[var(--primary-color-light)]': subItem.selected}"
                                v-for="(subItem, subIndex) in item.values" :key="subIndex"
                                @click="change(subItem, index)">{{ subItem.name }}</view>
                        </view>
                    </view>
                    <view class="flex justify-between items-center mt-[8rpx]">
                        <view class="text-[26rpx]">购买数量</view>
                        <text v-if="maxBuyShow > 0 && minBuyShow > 1" class="ml-[20rpx] mr-[auto] text-[24rpx] text-[var(--primary-color)]">({{ minBuyShow }}{{ goodsDetail.goods.unit }}起售，限购{{ maxBuyShow }}{{ goodsDetail.goods.unit }})</text>
                        <text v-else-if="maxBuyShow > 0" class="ml-[20rpx] mr-[auto] text-[24rpx] text-[var(--primary-color)]">(限购{{ maxBuyShow }}{{ goodsDetail.goods.unit }})</text>
                        <text v-else-if="minBuyShow > 1" class="ml-[20rpx] mr-[auto] text-[24rpx] text-[var(--primary-color)]">({{ minBuyShow }}{{ goodsDetail.goods.unit }}起售)</text>
                        <u-number-box
                            v-if="cartList['goods_' + detail.goods_id] && cartList['goods_' + detail.goods_id]['sku_' + detail.sku_id]"
                            v-model="buyNum" :min="minBuy" :max="maxBuy" integer :step="1" input-width="98rpx" input-height="54rpx">
                            <template #minus>
                                <view class="relative w-[34rpx] h-[34rpx]" @click="reduceNumChange()">
                                    <text class="text-[34rpx] nc-iconfont nc-icon-jianV6xx font-500 absolute flex items-center justify-center -left-[8rpx] -bottom-[8rpx] -right-[8rpx] -top-[8rpx]"
                                        :class="{ '!text-[var(--text-color-light9)]': buyNum <= minBuy }"></text>
                                </view>
                            </template>
                            <template #input>
                                <input class="text-[#303133] text-[28rpx] mx-[10rpx] w-[80rpx] h-[44rpx] bg-[var(--temp-bg)] leading-[44rpx] text-center rounded-[6rpx]"
                                    type="number" @input="goodsSkuInputFn" @blur="goodsSkuBlurFn" v-model="buyNum" />
                            </template>
                            <template #plus>
                                <view class="relative w-[34rpx] h-[34rpx]" @click="addNumChange()">
                                    <text class="text-[34rpx] nc-iconfont nc-icon-jiahaoV6xx font-500 absolute flex items-center justify-center -left-[8rpx] -bottom-[8rpx] -right-[8rpx] -top-[8rpx]"
                                        :class="{ '!text-[var(--text-color-light9)]': buyNum >= maxBuy }"></text>
                                </view>
                            </template>
                        </u-number-box>
                        <u-number-box v-else v-model="buyNum" :min="minBuy" :max="maxBuy" integer :step="1" input-width="98rpx" input-height="54rpx">
                            <template #minus>
                                <view class="relative w-[34rpx] h-[34rpx]" @click="reduceNumChange()">
                                    <text class="text-[34rpx] nc-iconfont nc-icon-jianV6xx font-500 absolute flex items-center justify-center -left-[8rpx] -bottom-[8rpx] -right-[8rpx] -top-[8rpx]"
                                        :class="{ '!text-[var(--text-color-light9)]': buyNum <= minBuy }"></text>
                                </view>
                            </template>
                            <template #input>
                                <input class="text-[#303133] text-[28rpx] mx-[10rpx] w-[80rpx] h-[44rpx] bg-[var(--temp-bg)] leading-[44rpx] text-center rounded-[6rpx]"
                                    type="number" @input="goodsSkuInputFn" @blur="goodsSkuBlurFn" v-model="buyNum" />
                            </template>
                            <template #plus>
                                <view class="relative w-[34rpx] h-[34rpx]" @click="addNumChange()">
                                    <text class="text-[34rpx] nc-iconfont nc-icon-jiahaoV6xx font-500 absolute flex items-center justify-center -left-[8rpx] -bottom-[8rpx] -right-[8rpx] -top-[8rpx]"
                                        :class="{ '!text-[var(--text-color-light9)]': buyNum >= maxBuy }"></text>
                                </view>
                            </template>
                        </u-number-box>
                    </view>
                </scroll-view>
                <view class="px-[20rpx]">
                    <button v-if="detail.stock > 0" class="!h-[80rpx] font-500 primary-btn-bg leading-[80rpx] text-[26rpx] rounded-[50rpx]" type="primary" @click="save">确定</button>
                    <button v-else class="!h-[80rpx] leading-[80rpx] font-500 text-[26rpx] text-[#fff] bg-[#ccc] rounded-[50rpx]">已售罄</button>
                </view>
            </view>
        </u-popup>
    </view>
</template>

<script setup lang="ts">
import { ref, reactive, computed, watch, toRaw } from 'vue';
import { img, getToken } from '@/utils/common';
import { getGoodsSku } from '@/addon/shop/api/goods';
import useCartStore from '@/addon/shop/stores/cart'
import { cloneDeep } from 'lodash-es'

const cartStore = useCartStore();
const cartList = computed(() => cartStore.cartList)

const goodsSkuPop = ref(false);
const currSpec: any = ref({
    skuId: "",
    name: []
})
const info: any = ref({})//获取原始数据
const detail: any = ref({})//展示数据
const buyNum = ref(1)

const maxBuy = ref(0); // 限购
const minBuy = ref(0); // 起售
const maxBuyShow = ref(0); // 限购，只展示
const minBuyShow = ref(0); // 起售，只展示

const getGoodsSkuFn = (sku_id: any) => {
    getGoodsSku(sku_id).then((res: any) => {
        info.value = res.data
        // 当前详情内容
        currSpec.value.sku_id = sku_id
        if (info.value.skuList && Object.keys(info.value.skuList).length) {
            info.value.skuList.forEach((item: any) => {
                if (item.sku_id == sku_id) {
                    detail.value = item;
                    currSpec.value.name = item.sku_spec_format.split(",");
                }
            })
        }
        goodsSkuPop.value = true;
    })
}

const goodsSkuInputFn = () => {
    setTimeout(() => {
        if (!buyNum.value || buyNum.value <= minBuy.value) {
            buyNum.value = minBuy.value || 1;
        }
        if (buyNum.value >= maxBuy.value) {
            buyNum.value = maxBuy.value;
        }
        // 起售大于库存，初始值也应该是零
        if (minBuy.value > buyNum.value) {
            buyNum.value = 0;
        }
    }, 0)
}
const goodsSkuBlurFn = () => {
    setTimeout(() => {
        if (!buyNum.value || buyNum.value <= minBuy.value) {
            buyNum.value = minBuy.value || 1;
        }
        if (buyNum.value >= maxBuy.value) {
            buyNum.value = maxBuy.value;
        }

        // 起售大于库存，初始值也应该是零
        if (minBuy.value > buyNum.value) {
            buyNum.value = 0;
            uni.showToast({
                title: '库存数量小于起购数量',
                icon: 'none'
            });
        }
    }, 0)
}

const open = (sku_id: any) => {
    getGoodsSkuFn(sku_id)
}

const closeFn = () => {
    goodsSkuPop.value = false
}

const goodsDetail = computed(() => {
    let data = cloneDeep(info.value);

    // 重组数据结构
    if (Object.keys(data).length) {

        if (!Object.keys(currSpec.value.name).length) currSpec.value.name = data.sku_spec_format.split(",");

        data.goodsSpec.forEach((item: any, index: any) => {
            let specName = item.spec_values.split(",");
            item.values = [];
            specName.forEach((specItem: any, specIndex: any) => {
                item.values[specIndex] = {};
                item.values[specIndex].name = specItem;
                item.values[specIndex].selected = false;
                item.values[specIndex].disabled = false;

                // 选中规格
                currSpec.value.name.forEach((currSpecItem: any, currSpecIndex: any) => {
                    if (currSpecIndex == index && currSpecItem == specItem) {
                        item.values[specIndex].selected = true;
                    }
                })
            })
        })
        getSkuId();

        // 当前详情内容
        if (data.skuList && Object.keys(data.skuList).length) {
            data.skuList.forEach((item: any) => {
                if (item.sku_id == currSpec.value.skuId) {
                    detail.value = item;
                }
            })
        }
        /************************** 限购-start **************************/
        maxBuy.value = detail.value.stock
        // 限购 - 是否开启限购
        if (data.goods.is_limit) {
            if (data.goods && data.goods.max_buy) {
                let max_buy = 0;
                if (data.goods.limit_type == 1) { //单次限购
                    max_buy = data.goods.max_buy;
                } else { // 单人限购
                    let buyVal = data.goods.max_buy - (data.has_buy || 0);
                    max_buy = buyVal > 0 ? buyVal : 0;
                }

                if (max_buy > detail.value.stock) {
                    maxBuy.value = detail.value.stock
                } else if (max_buy <= detail.value.stock) {
                    maxBuy.value = max_buy;
                }
            }
            // 仅用于展示
            maxBuyShow.value = data.goods.max_buy; // 限购
            // 限购开启且最大购买变为零时，初始值也应该是零
            if (maxBuy.value == 0) {
                buyNum.value = 0;
            }
        }
        // 起售
        minBuy.value = data.goods.min_buy;
        buyNum.value = minBuy.value > 0 ? data.goods.min_buy : 1;
        // 仅用于展示
        minBuyShow.value = data.goods.min_buy;
        // 起售大于库存，初始值也应该是零
        if (minBuy.value > detail.value.stock) {
            buyNum.value = 0;
        }
        /************************** 限购-end **************************/
    }
    return data;
})

watch(
    () => detail.value,
    (newValue, oldValue) => {
        if (cartList.value['goods_' + detail.value.goods_id] && cartList.value['goods_' + detail.value.goods_id]['sku_' + detail.value.sku_id]) {
            buyNum.value = toRaw(cartList.value['goods_' + detail.value.goods_id]['sku_' + detail.value.sku_id].num);
            detail.value.cart_id = toRaw(cartList.value['goods_' + detail.value.goods_id]['sku_' + detail.value.sku_id].id)
        } else {
            // 起售大于库存，初始值也应该是零
            let num = 1;
            if (minBuy.value > 0) {
                num = minBuy.value;
            }
            if (minBuy.value > detail.value.stock) {
                num = 0;
            }
            buyNum.value = num;
            detail.value.cart_id = '';
        }

    }
)
const change = (data: any, index: any) => {
    currSpec.value.name[index] = data.name;
}

const getSkuId = () => {
    info.value.skuList.forEach((skuItem: any, skuIndex: any) => {
        let sku_spec_format = skuItem.sku_spec_format.split(',')
        if (currSpec.value.name.every((v: any) => sku_spec_format.includes(v))) {
            currSpec.value.skuId = skuItem.sku_id
        }
    })
}

const addNumChange = () => {
    if (minBuy.value && minBuy.value > detail.value.stock) {
        uni.showToast({ title: '商品库存小于起购数量', icon: 'none' })
        return;
    }
    if (goodsDetail.value.goods.is_limit) {
        let tips = `该商品单次限购${ goodsDetail.value.goods.max_buy }件`;
        if (goodsDetail.value.goods.limit_type != 1) { //单次限购
            tips = `该商品每人限购${ goodsDetail.value.goods.max_buy }件`;
        }
        if (buyNum.value >= goodsDetail.value.goods.max_buy) {
            uni.showToast({ title: tips, icon: 'none' })
        }
    }
}

const reduceNumChange = () => {
    if (goodsDetail.value.goods.is_limit && minBuy.value) {
        let tips = `该商品起购${ minBuy.value }件`;
        if (buyNum.value <= minBuy.value) {
            uni.showToast({ title: tips, icon: 'none' })
        }
    }
}
const save = () => {
    //删除商品
    if (buyNum.value == 0) {
        // 购物车减少数量
        cartStore.reduce({
            id: detail.value.cart_id || '',
            goods_id: detail.value.goods_id,
            sale_price: detail.value.show_price,
            sku_id: detail.value.sku_id
        }, 1, ()=>{
            cartStore.isAddCartRecommend = true;
        });
    } else {

        // let price = 0

        // if (goodsDetail.value.goods.member_discount && getToken() && detail.value.member_price != detail.value.price) {
        //     price = detail.value.member_price ? detail.value.member_price : detail.value.price // 会员价
        // } else {
        //     price = detail.value.price
        // }

        // 购物车添加数量
        cartStore.increase({
            id: detail.value.cart_id || '',
            goods_id: detail.value.goods_id,
            sku_id: detail.value.sku_id,
            stock: detail.value.stock,
            sale_price: detail.value.show_price,
            num: buyNum.value

        }, 0, () => {
            cartStore.isAddCartRecommend = true;
            uni.showToast({
                title: '加入购物车成功',
                icon: 'none'
            });
        });
    }
    goodsSkuPop.value = false
}

// 商品价格
const goodsPrice = (data: any) => {
    let price = "0.00";
	price = data.show_price
    return price;
}

// 价格类型
const priceType = (data: any) => {
    let type = "";
	type = data.show_type
    return type;
}

defineExpose({
    open
})
</script>

<style lang="scss" scoped>
::v-deep .u-number-box .u-number-box__slot {
    display: flex;
    align-items: center;
}
</style>

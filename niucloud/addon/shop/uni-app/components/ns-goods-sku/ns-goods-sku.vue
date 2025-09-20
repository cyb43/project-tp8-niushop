<template>
    <view @touchmove.prevent.stop>
        <u-overlay :show="goodsSkuPop" @click="closeFn" zIndex="490">
            <u-popup class="popup-type" :show="goodsSkuPop" @close="closeFn" mode="bottom" :overlay="false" zIndex="500">
                <view class="py-[32rpx] relative" v-if="goodsDetail.detail" @touchmove.prevent.stop>
                    <view class="flex px-[32rpx]" :class="{'mb-[58rpx]':!(goodsDetail.is_newcomer && goodsDetail.newcomer_price != goodsDetail.price && (Object.keys(cartSkuList).length?parseInt(cartSkuList.num)+buyNum:buyNum)>1)}">
                        <view class="rounded-[var(--goods-rounded-big)] overflow-hidden w-[180rpx] h-[180rpx]">
                            <u--image width="180rpx" height="180rpx" :src="img(goodsDetail.detail.sku_image)" @click="imgListPreview(goodsDetail.detail.sku_image)" model="aspectFill">
                                <template #error>
                                    <image class="w-[180rpx] h-[180rpx]" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill" />
                                </template>
                            </u--image>
                        </view>
                        <view class="flex flex-1 flex-col justify-between ml-[24rpx] py-[10rpx]">
                            <view class="w-[100%]">
                                <view class="text-[var(--price-text-color)] flex items-baseline">
                                    <text class="text-[32rpx] font-bold price-font">￥</text>
                                    <text class="text-[48rpx] price-font">{{ parseFloat(goodsPrice).toFixed(2).split('.')[0] }}</text>
                                    <text class="text-[32rpx] mr-[6rpx] price-font">.{{ parseFloat(goodsPrice).toFixed(2).split('.')[1] }}</text>
                                    <image class="h-[24rpx] ml-[6rpx] max-w-[60rpx]" v-if="priceType() == 'newcomer_price'" :src="img('addon/shop/newcomer.png')" mode="heightFix" />
                                    <image class="h-[24rpx] ml-[6rpx] max-w-[44rpx]" v-if="priceType() == 'member_price'" :src="img('addon/shop/VIP.png')" mode="heightFix" />
                                    <image class="h-[24rpx] ml-[6rpx] max-w-[80rpx]" v-if="priceType() == 'discount_price'" :src="img('addon/shop/discount.png')" mode="heightFix" />
                                </view>
                                <view class="text-[26rpx] leading-[32rpx] text-[var(--text-color-light6)] mt-[12rpx]">库存{{ goodsDetail.detail.stock }}{{ goodsDetail.goods.unit }}</view>
                            </view>
                            <view class="text-[26rpx] leading-[30rpx] text-[var(--text-color-light6)] w-[100%] max-h-[60rpx] multi-hidden" v-if="goodsDetail.goodsSpec && goodsDetail.goodsSpec.length">已选规格：{{ goodsDetail.detail.sku_spec_format }}</view>
                            <!-- 	<view v-if="goodsDetail.goodsSpec && goodsDetail.goodsSpec.length">
                                          <text>已选规格：{{goodsDetail.detail.sku_spec_format}}</text>
                                        </view> -->
                        </view>
                    </view>
                    <view class="flex items-center px-[32rpx] pt-[8rpx] pb-[16rpx] h-[58rpx] box-border"
                          v-if="goodsDetail.is_newcomer && goodsDetail.newcomer_price != goodsDetail.price && (Object.keys(cartSkuList).length?parseInt(cartSkuList.num)+buyNum:buyNum)>1">
                        <image class="h-[24rpx] w-[56rpx]" :src="img('addon/shop/newcomer.png')" mode="aspectFit" />
                        <view class="text-[24rpx] text-[#FFB000] leading-[34rpx] ml-[8rpx]">
                            第1{{ goodsDetail.goods.unit }}，￥{{ parseFloat(goodsDetail.newcomer_price).toFixed(2) }}/{{ goodsDetail.goods.unit }}；第{{ (parseInt(cartSkuList.num || 0) + buyNum) > 2 ? '2~' + (parseInt(cartSkuList.num || 0) + buyNum) : '2' }}{{ goodsDetail.goods.unit }}，￥{{ parseFloat(parseFloat(goodsDetail.show_price)).toFixed(2) }}/{{ goodsDetail.goods.unit }}
                        </view>
                    </view>
                    <scroll-view class="h-[500rpx] px-[32rpx] box-border mb-[60rpx]" scroll-y="true">
                        <view :class="{'mt-[20rpx]': 0 != index }" v-for="(item,index) in goodsDetail.goodsSpec" :key="index">
                            <view class="text-[28rpx] leading-[36rpx] mb-[24rpx]">{{ item.spec_name }}</view>
                            <view class="flex flex-wrap">
                                <view class="box-border bg-[var(--temp-bg)] text-[24rpx] px-[44rpx] text-center h-[56rpx] flex-center mr-[20rpx] mb-[20rpx] border-1 border-solid rounded-[50rpx] border-[var(--temp-bg)]"
                                    :class="{'!border-[var(--primary-color)] text-[var(--primary-color)] !bg-[var(--primary-color-light)]': subItem.selected}"
                                    v-for="(subItem,subIndex) in item.values" :key="subIndex"
                                    @click="change(subItem, index)">{{ subItem.name }}</view>
                            </view>
                        </view>
                        <view class="flex justify-between items-center my-[20rpx]">
                            <view class="text-[28rpx]">购买数量</view>
                            <text v-if="maxBuyShow > 0 && minBuyShow > 1" class="ml-[20rpx] mr-[auto] text-[24rpx] text-[var(--primary-color)]">({{ minBuyShow }}{{ goodsDetail.goods.unit }}起售，限购{{ maxBuyShow }}{{ goodsDetail.goods.unit }})</text>
                            <text v-else-if="maxBuyShow > 0" class="ml-[20rpx] mr-[auto] text-[24rpx] text-[var(--primary-color)]">(限购{{ maxBuyShow }}{{ goodsDetail.goods.unit }})</text>
                            <text v-else-if="minBuyShow > 1" class="ml-[20rpx] mr-[auto] text-[24rpx] text-[var(--primary-color)]">({{ minBuyShow }}{{ goodsDetail.goods.unit }}起售)</text>
                            <u-number-box :min="minBuy" :max="maxBuy" integer :step="1" input-width="68rpx" v-model="buyNum" input-height="52rpx">
                                <template #minus>
                                    <view class="relative w-[30rpx] h-[30rpx]" @click="reduceNumChange">
                                        <text class="text-[30rpx] nc-iconfont nc-icon-jianV6xx font-500 absolute flex items-center justify-center -left-[8rpx] -bottom-[8rpx] -right-[8rpx] -top-[8rpx]"
                                            :class="{ '!text-[var(--text-color-light9)]': buyNum <= minBuy }"></text>
                                    </view>
                                </template>
                                <template #input>
                                    <input class="text-[#303133] text-[28rpx] mx-[10rpx] w-[80rpx] h-[44rpx] bg-[var(--temp-bg)] leading-[44rpx] text-center rounded-[6rpx]"
                                        type="number" @input="goodsSkuInputFn" @blur="goodsSkuBlurFn" v-model="buyNum" />
                                </template>
                                <template #plus>
                                    <view class="relative w-[30rpx] h-[30rpx]" @click="addNumChange">
                                        <text class="text-[30rpx] nc-iconfont nc-icon-jiahaoV6xx font-500 absolute flex items-center justify-center -left-[8rpx] -bottom-[8rpx] -right-[8rpx] -top-[8rpx]"
                                            :class="{ '!text-[var(--text-color-light9)]': buyNum >= maxBuy || buyNum == 0 }"></text>
                                    </view>
                                </template>
                            </u-number-box>
                        </view>
                        <view class="mt-[40rpx]">
                            <diy-form ref="diyFormRef" :form_id="goodsDetail.goods.form_id" :storage_name="'diyFormStorageByGoodsDetail_' + goodsDetail.sku_id" />
                        </view>
                    </scroll-view>
                    <view class="px-[20rpx]">

                        <!-- #ifndef MP -->
                        <button v-if="goodsDetail.detail.stock > 0" hover-class="none" class="!h-[80rpx] leading-[80rpx] text-[26rpx] font-500 rounded-[50rpx] primary-btn-bg" type="primary" @click="confirm">确定</button>
                        <!-- #endif -->

                        <!-- #ifdef MP -->
                        <template v-if="goodsDetail.detail.stock > 0">
                            <!--<button v-if="isBindMobile && userInfo && !userInfo.mobile" hover-class="none" class="!h-[80rpx] leading-[80rpx] text-[26rpx] font-500 rounded-[50rpx] primary-btn-bg" type="primary" open-type="getPhoneNumber" @getphonenumber="memberStore.bindMobile">确定</button>-->
                            <!--<button v-else hover-class="none" class="!h-[80rpx] leading-[80rpx] text-[26rpx] font-500 rounded-[50rpx] primary-btn-bg" type="primary" @click="confirm">确定</button>-->
                            <button hover-class="none" class="!h-[80rpx] leading-[80rpx] text-[26rpx] font-500 rounded-[50rpx] primary-btn-bg" type="primary" @click="confirm">确定</button>
                        </template>
                        <!-- #endif -->

                        <button hover-class="none" v-else class="!h-[80rpx] leading-[80rpx] text-[26rpx] font-500 text-[#fff] bg-[#ccc] rounded-[50rpx]">已售罄</button>
                    </view>
                </view>
            </u-popup>
        </u-overlay>
        <!-- 强制绑定手机号 -->
        <bind-mobile ref="bindMobileRef" />
    </view>
</template>

<script setup lang="ts">
import { ref, computed, toRaw } from 'vue';
import { img, redirect, getToken } from '@/utils/common'
import useCartStore from '@/addon/shop/stores/cart'
import { useLogin } from '@/hooks/useLogin'
import useMemberStore from '@/stores/member'
import bindMobile from '@/components/bind-mobile/bind-mobile.vue';
import { cloneDeep } from 'lodash-es'
import { t } from '@/locale'
import diyForm from '@/addon/components/diy-form/index.vue'
import useGoodsDetailStore from '@/addon/shop/stores/goodsDetail'

const props = defineProps(['goodsDetail']);
const goodsSkuPop = ref(false);
const callback: any = ref(null);
const currSpec = ref({
    skuId: "",
    name: []
})
const openType = ref("");
const buyNum = ref(1)

const maxBuy = ref(0); // 限购
const minBuy = ref(0); // 起售
const maxBuyShow = ref(0); // 限购
const minBuyShow = ref(0); // 起售

// 商品价格
const goodsPrice = computed(() => {
    let price = "0.00";
    if (goodsDetail.value.detail.type == 'newcomer_discount' && goodsDetail.value.detail.newcomer_price) {
        price = goodsDetail.value.detail.newcomer_price
    }else{
        price = goodsDetail.value.show_price
    }
    return price;
})

// 价格类型
const priceType = () => {
    let type = "";
	if(goodsDetail.value.detail.type == 'newcomer_discount' && goodsDetail.value.detail.newcomer_price){
		type='newcomer_price'
	}else{
		type = goodsDetail.value.show_type
	}
    return type;
}

// 会员信息
const memberStore = useMemberStore()
const userInfo = computed(() => memberStore.info)

// 购物车数量
const cartStore = useCartStore();
cartStore.getList();
const cartSkuList = computed(() => {
    if (goodsDetail.value && cartStore.cartList['goods_' + goodsDetail.value.goods_id] && cartStore.cartList['goods_' + goodsDetail.value.goods_id]['sku_' + goodsDetail.value.sku_id]) {
        return cartStore.cartList['goods_' + goodsDetail.value.goods_id]['sku_' + goodsDetail.value.sku_id];
    } else {
        return {}
    }
})
const cartList = computed(() => cartStore.cartList)
const open = (type = "", fn = "") => {
    openType.value = type;
    goodsSkuPop.value = true;
    callback.value = fn;
}

const goodsSkuInputFn = () => {
    setTimeout(() => {
        buyNum.value = parseInt(buyNum.value)
        if (!buyNum.value || buyNum.value <= minBuy.value) {
            buyNum.value = minBuy.value || 1;
        }
        if (buyNum.value >= maxBuy.value) {
            buyNum.value = maxBuy.value;
        }
        // 起售大于库存，初始值也应该是零
        if (minBuy.value > goodsDetail.value.detail.stock) {
            buyNum.value = 0;
        }
    }, 0)
}

const goodsSkuBlurFn = () => {
    setTimeout(() => {
        buyNum.value = parseInt(buyNum.value)
        if (!buyNum.value || buyNum.value <= minBuy.value) {
            buyNum.value = minBuy.value || 1;
        }
        if (buyNum.value >= maxBuy.value) {
            buyNum.value = maxBuy.value;
        }

        // 起售大于库存，初始值也应该是零
        if (minBuy.value > goodsDetail.value.detail.stock) {
            buyNum.value = 0;
            uni.showToast({
                title: '商品库存小于起购数量',
                icon: 'none'
            });
        }
    }, 0)
}

const closeFn = () => {
    goodsSkuPop.value = false
    useGoodsDetailStore().setGoodsDetail({isOpenSkuBuy: false});
}

const goodsDetail = computed(() => {
    let data = cloneDeep(props.goodsDetail);
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
                currSpec.value.name.forEach((currSpecItem, currSpecIndex) => {
                    if (currSpecIndex == index && currSpecItem == specItem) {
                        item.values[specIndex].selected = true;
                    }
                })
            })
        })
        getSkuId();

        // 当前详情内容
        if (data.skuList && Object.keys(data.skuList).length) {
            data.skuList.forEach((idItem: any, idIndex: any) => {
                if (idItem.sku_id == currSpec.value.skuId) {
                    idItem.sku_image = idItem.sku_image ? idItem.sku_image : data.goods.goods_cover
                    data.detail = idItem;
                }
            })
        }

        // 限购 - 是否开启限购
        if (data.goods.is_limit) {
            if (data.goods.max_buy) {
                let max_buy = 0;
                if (data.goods.limit_type == 1) { //单次限购
                    max_buy = data.goods.max_buy;
                } else { // 单人限购
                    let buyVal = data.goods.max_buy - (data.goods.has_buy || 0);
                    max_buy = buyVal > 0 ? buyVal : 0;
                }
                if (max_buy > data.detail.stock) {
                    maxBuy.value = data.detail.stock
                } else if (max_buy <= data.detail.stock) {
                    maxBuy.value = max_buy;
                }

                // 限购开启且最大购买变为零时，初始值也应该是零
                if (maxBuy.value == 0) {
                    buyNum.value = 0;
                }
            }
            // 仅用于展示
            maxBuyShow.value = data.goods.max_buy; // 限购
        } else {
            maxBuy.value = data.detail.stock;
        }

        // 起售
        minBuy.value = data.goods.min_buy > 0 ? data.goods.min_buy : 1;
        // 起售大于库存，初始值也应该是零
        if (minBuy.value > data.detail.stock) {
            buyNum.value = 0;
        } else {
            buyNum.value = minBuy.value;
        }
        // 仅用于展示
        minBuyShow.value = data.goods.min_buy;
    }
    return data;
})

const change = (data: any, index: any) => {
    currSpec.value.name[index] = data.name;
    getSkuId(); // 刷新当前规格信息
}

const emits = defineEmits(['change'])
const getSkuId = () => {
    props.goodsDetail.skuList.forEach((skuItem: any, skuIndex: any) => {
        if (skuItem.sku_spec_format == currSpec.value.name.toString()) {
            currSpec.value.skuId = skuItem.sku_id
            emits('change', skuItem.sku_id)
        }
    })
}

const addNumChange = () => {
    if (minBuy.value && minBuy.value > goodsDetail.value.detail.stock) {
        uni.showToast({ title: '商品库存小于起购数量', icon: 'none' })
        return;
    }
    if (goodsDetail.value.goods.is_limit) {
        let tips = `该商品单次限购${ goodsDetail.value.goods.max_buy }件`;
        if (goodsDetail.value.goods.limit_type != 1) { //单次限购
            tips = `该商品每人限购${ goodsDetail.value.goods.max_buy }件`;
            if (goodsDetail.value.goods.max_buy - maxBuy.value) {
                tips += `,已购${ goodsDetail.value.goods.max_buy - maxBuy.value }件`;
            }
        }
        if (buyNum.value >= maxBuy.value) {
            uni.showToast({ title: tips, icon: 'none' })
        }
    }
}

const reduceNumChange = () => {
    if (minBuy.value > 1) {
        let tips = `该商品起购${ minBuy.value }件`;
        if (buyNum.value <= minBuy.value) {
            uni.showToast({ title: tips, icon: 'none' })
        }
    }
}

//强制绑定手机号
const bindMobileRef: any = ref(null)
const isBindMobile = ref(uni.getStorageSync('isBindMobile'))

const diyFormRef: any = ref(null)

//提交
const confirm = () => {
    if (!diyFormRef.value.verify()) return;

    if (buyNum.value < 1) return;

    // #ifdef H5
    // 绑定手机号
    if (!userInfo.value && uni.getStorageSync('isBindMobile')) {
        uni.setStorage({
            key: 'loginBack', data: {
                url: '/addon/shop/pages/goods/detail',
                param: {
                    sku_id: goodsDetail.value.sku_id,
                    type: goodsDetail.value.type
                }
            }
        })
        bindMobileRef.value.open()
        return false
    }
    // #endif

    // 检测是否登录
    if (!userInfo.value) {
        useLogin().setLoginBack({
            url: '/addon/shop/pages/goods/detail',
            param: {
                sku_id: goodsDetail.value.sku_id,
                type: goodsDetail.value.type
            }
        })
        return false
    }

    // 加入购物车
    if (openType.value == 'join_cart') {
        let num = 0;
        let limitNum = 0;
        let cartId = "";

        if (cartList.value['goods_' + goodsDetail.value.goods_id] && cartList.value['goods_' + goodsDetail.value.goods_id]['sku_' + goodsDetail.value.sku_id]) {
            num = toRaw(cartList.value['goods_' + goodsDetail.value.goods_id]['sku_' + goodsDetail.value.sku_id].num);
            cartId = toRaw(cartList.value['goods_' + goodsDetail.value.goods_id]['sku_' + goodsDetail.value.sku_id].id)
        }
        if (cartList.value['goods_' + goodsDetail.value.goods_id] && cartList.value['goods_' + goodsDetail.value.goods_id]) {
            limitNum = toRaw(cartList.value['goods_' + goodsDetail.value.goods_id].totalNum);
        }

        num += Number(buyNum.value);
        limitNum += Number(buyNum.value);
        /************** 限购-start **************/
        if (goodsDetail.value.goods.is_limit) {
            let tips = `该商品单次限购${ goodsDetail.value.goods.max_buy }件`;
            if (goodsDetail.value.goods.limit_type != 1) { //单次限购
                tips = `该商品每人限购${ goodsDetail.value.goods.max_buy }件`;
                if (goodsDetail.value.goods.max_buy - maxBuy.value) {
                    tips += `,已购${ goodsDetail.value.goods.max_buy - maxBuy.value }件`;
                }
            }
            if (limitNum > maxBuy.value) {
                uni.showToast({ title: tips, icon: 'none' })
                return false;
            }
        }
        /************** 限购-end **************/
        cartStore.increase({
            id: cartId || '',
            goods_id: goodsDetail.value.goods_id,
            sku_id: goodsDetail.value.sku_id,
            stock: goodsDetail.value.stock,
            sale_price: goodsDetail.value.sale_price,
            num: num
        }, 0, () => {
            uni.showToast({
                title: '加入购物车成功',
                icon: 'none'
            });
        });

    } else if (openType.value == 'buy_now') {
        // 立即购买
        const data = {
            sku_id: goodsDetail.value.sku_id,
            num: buyNum.value
        };

        let extend_data = {}

        // 新人专区进入且满足新人条件，需要传新人价类型
        if(goodsDetail.value.detail.type == 'newcomer_discount' && goodsDetail.value.detail.newcomer_price){
            extend_data.relate_id = ''
            extend_data.activity_type = 'newcomer_discount'
        }else{
            extend_data.relate_id = goodsDetail.value.show_type == 'discount_price'?goodsDetail.value.discount_info.discount_id:''
            extend_data.activity_type = goodsDetail.value.show_type == 'discount_price'?'discount': goodsDetail.value.detail.type
        }

        uni.setStorage({
            key: 'orderCreateData',
            data: {
                sku_data: [
                    data
                ],
                extend_data
            },
            success: () => {
                redirect({ url: '/addon/shop/pages/order/payment' })
            }
        });
    }

    closeFn();
}

//预览图片
const imgListPreview = (item: any) => {
    if (item === '') return false
    const urlList = [];
    urlList.push(img(item))  //push中的参数为 :src="item.img_url" 中的图片地址
    uni.previewImage({
        indicator: "number",
        loop: true,
        urls: urlList
    })
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
::v-deep .uni-scroll-view{
    position:inherit;
}
</style>

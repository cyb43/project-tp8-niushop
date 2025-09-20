<template>
    <view :style="themeColor()">
        <!-- #ifdef MP-WEIXIN -->
        <top-tabbar :data="topTabbarData" scrollBool="1" :isBack="false"/>
        <!-- #endif -->
        <view class="bg-page min-h-[100vh] overflow-hidden flex flex-col" v-if="!loading">
            <view v-if="!info" class="pb-[100rpx]">
                <view class="empty-page">
                    <image class="img" :src="img('static/resource/images/system/login.png')" model="aspectFit" />
                    <view class="desc">暂未登录</view>
                    <button shape="circle" plain="true" class="btn" @click="toLogin">去登录</button>
                </view>
                <ns-goods-recommend></ns-goods-recommend>
            </view>
            <view v-else-if="!cartList.length&&!invalidList.length" class="pb-[100rpx]">
                <view class="empty-page">
                    <image class="img" :src="img('addon/shop/cart-empty.png')" model="aspectFit" />
                    <view class="desc">赶紧去逛逛, 购买心仪的商品吧</view>
                    <button shape="circle" plain="true" class="btn" @click="redirect({ url: '/addon/shop/pages/goods/list' })">去逛逛</button>
                </view>
                <ns-goods-recommend></ns-goods-recommend>
            </view>
            <template v-else>
                <view class="flex-1 h-0">
                    <view class="scroll-height ">
                        <view class="py-[var(--top-m)] sidebar-margin">
                            <view class="bg-[#fff] pb-[10rpx] box-border rounded-[var(--rounded-big)]" v-if="cartList.length">
                                <view class="flex mx-[var(--rounded-big)] pt-[var(--pad-top-m)] justify-between items-center box-border font-400 text-[24rpx] mb-[24rpx] leading-[30rpx]">
                                    <view class="flex items-baseline text-[24rpx] text-[#333]">
                                        <text>共</text>
                                        <text class="text-[32rpx] mx-[2rpx] text-[var(--price-text-color)]">{{ cartList.length }}</text>
                                        <text>种商品</text>
                                    </view>
                                    <text @click="isEdit = !isEdit" class="text-[var(--text-color-light6)] text-[24rpx]">{{ isEdit ? '完成' : '管理' }}</text>
                                </view>
                                <u-swipe-action ref="swipeActive">
                                    <template v-for="(item, index) in cartList">
                                        <view v-if="item.goodsSku" class="py-[20rpx] overflow-hidden w-full">
                                            <u-swipe-action-item :options="cartOptions" @click="swipeClick(index,item)">
                                                <view class="flex px-[var(--pad-sidebar-m)]" @click.stop="selectOnlyGoods(item)">
                                                    <view class="self-center w-[34rpx] mr-[24rpx] h-[60rpx] flex items-center" @click.stop="selectOnlyGoods(item)">
                                                        <text
                                                            class=" iconfont text-color text-[34rpx] w-[34rpx] h-[34rpx] rounded-[17rpx] overflow-hidden shrink-0"
                                                            :class="{ 'iconxuanze1':item.checked,'bg-[#F5F5F5]':!item.checked}">
                                                        </text>
                                                    </view>
                                                    <view class="w-[200rpx] h-[200rpx] flex items-center justify-center rounded-[var(--goods-rounded-big)] overflow-hidden"
                                                        @click="toDetail(item)">
                                                        <u--image radius="var(--goods-rounded-big)" width="200rpx" height="200rpx" :src="img(item.goodsSku.sku_image_thumb_mid||'')" model="aspectFill">
                                                            <template #error>
                                                                <image class="w-[200rpx] h-[200rpx] rounded-[var(--goods-rounded-big)] overflow-hidden" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill" />
                                                            </template>
                                                        </u--image>
                                                    </view>
                                                    <view class="flex flex-1 flex-col justify-between ml-[20rpx]">
                                                        <view class="w-[100%] flex flex-col items-baseline">
                                                            <view class="text-[#333] text-[28rpx] max-h-[80rpx] leading-[40rpx] multi-hidden font-400">{{ item.goods.goods_name }}</view>
                                                            <view class="box-border max-w-[376rpx] mt-[10rpx] px-[14rpx] h-[36rpx] leading-[36rpx] truncate text-[var(--text-color-light6)] bg-[#F5F5F5] text-[22rpx] rounded-[20rpx]"
                                                                v-if="item.goodsSku && item.goodsSku.sku_name">{{ item.goodsSku.sku_name }}</view>
                                                        </view>
                                                        <view v-if="item.goods && item.goods.goods_label_name && item.goods.goods_label_name.length"
                                                            class="flex flex-wrap mb-[auto]">
                                                            <template v-for="(tagItem, tagIndex) in item.goods.goods_label_name">
                                                                <image class="img-tag" v-if="tagItem.style_type == 'icon' && tagItem.icon" :src="img(tagItem.icon)" mode="heightFix" @error="diyGoods.error(tagItem,'icon')" />
                                                                <view class="base-tag" v-else-if="tagItem.style_type == 'diy' || !tagItem.icon" :style="diyGoods.baseTagStyle(tagItem)">{{ tagItem.label_name }}</view>
                                                            </template>
                                                        </view>
                                                        <view v-if="item.manjian_info && Object.keys(item.manjian_info).length && item.manjian_info.is_join"
                                                            class="flex items-center mt-[8rpx] mb-[auto]"
                                                            @click.stop="manjianOpenFn(item.manjian_info)">
                                                            <view class="bg-[var(--primary-color-light)] text-[var(--primary-color)] rounded-[6rpx] text-[20rpx] flex items-center justify-center w-[88rpx] h-[36rpx] mr-[6rpx]">满减送</view>
                                                            <text class="text-[22rpx] text-[#999]">{{ item.manjian_info.manjian_name }}</text>
                                                        </view>
                                                        <view class="flex justify-between items-end self-end mt-[10rpx] w-[100%]">
                                                            <view class="text-[var(--price-text-color)] price-font truncate max-w-[200rpx]">
                                                                <text class="text-[24rpx] font-500">￥</text>
                                                                <text class="text-[40rpx] font-500">{{ parseFloat(goodsPrice(item)).toFixed(2).split('.')[0] }}</text>
                                                                <text class="text-[24rpx] font-500">.{{ parseFloat(goodsPrice(item)).toFixed(2).split('.')[1] }}</text>
                                                            </view>
                                                            <u-number-box v-model="item.num" :min="numLimit(item).min"
                                                                          :max="numLimit(item).max" integer :step="1"
                                                                          input-width="68rpx"
                                                                          input-height="52rpx" button-size="52rpx"
                                                                          disabledInput
                                                                          @change="numChange($event, index)">
                                                                <template #minus>
                                                                    <view class="relative w-[26rpx] h-[26rpx]" @click="reduceNumChange(item)">
                                                                        <text
                                                                            :class="{ 'text-[var(--text-color-light9)]': item.num === numLimit(item).min, 'text-[#303133]': item.num !== numLimit(item).min }"
                                                                            class="text-[24rpx] absolute flex items-center justify-center -left-[20rpx] -bottom-[20rpx] -right-[20rpx] -top-[20rpx] font-500 nc-iconfont nc-icon-jianV6xx"></text>
                                                                    </view>
                                                                </template>
                                                                <template #input>
                                                                    <input
                                                                        class="text-[#303133] text-[28rpx] mx-[14rpx] w-[80rpx] h-[44rpx] bg-[var(--temp-bg)] leading-[44rpx] text-center rounded-[6rpx]"
                                                                        type="number" @input="goodsSkuInputFn(item)"
                                                                        @blur="goodsSkuBlurFn($event, index)"
                                                                        @click.stop v-model="item.num" />
                                                                </template>
                                                                <template #plus>
                                                                    <view class="relative w-[26rpx] h-[26rpx]" @click="addNumChange(item)">
                                                                        <text :class="{ 'text-[var(--text-color-light9)]': item.num === numLimit(item).max, ' text-[#303133]': item.num !== numLimit(item).max }"
                                                                            class="text-[24rpx] absolute flex items-center justify-center -left-[20rpx] -bottom-[20rpx] -right-[20rpx] -top-[20rpx] font-500 nc-iconfont nc-icon-jiahaoV6xx"></text>
                                                                    </view>
                                                                </template>
                                                            </u-number-box>
                                                        </view>
                                                    </view>
                                                </view>
                                            </u-swipe-action-item>
                                        </view>
                                    </template>

                                </u-swipe-action>
                            </view>
                            <view class="bg-[#fff] pb-[10rpx] box-border rounded-[var(--rounded-big)] mt-[var(--top-m)]" v-if="invalidList.length">
                                <view class="flex mx-[var(--pad-sidebar-m)] pt-[var(--pad-top-m)] justify-between items-center box-border font-400 text-[#303133] text-[24rpx] mb-[24rpx] leading-[30rpx]">
                                    <view class="flex items-center text-[24rpx] text-[#333]">
                                        <text>共</text>
                                        <text class="text-[28rpx] text-[var(--price-text-color)]">{{ invalidList.length }}</text>
                                        <text>件失效商品</text>
                                    </view>
                                    <text class="text-[var(--text-color-light6)] text-[24rpx]" @click="deleteInvalidList">清空</text>
                                </view>
                                <view v-for="(item, index) in invalidList" class="py-[20rpx] overflow-hidden">
                                    <view class="flex px-[var(--pad-sidebar-m)]">
                                        <text class="self-center iconfont iconxuanze1 text-[34rpx] mr-[32rpx] text-[#F5F5F5] rounded-[50%] overflow-hidden shrink-0"></text>
                                        <view class="relative w-[200rpx] h-[200rpx] rounded-[var(--goods-rounded-big)] overflow-hidden">
                                            <u--image radius="var(--goods-rounded-big)" width="200rpx" height="200rpx" :src="img(item.goodsSku.sku_image_thumb_mid)" model="aspectFill">
                                                <template #error>
                                                    <image class="w-[200rpx] h-[200rpx] rounded-[var(--goods-rounded-big)] overflow-hidden" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill" />
                                                </template>
                                            </u--image>
                                            <view v-if="item.goodsSku.stock == 0 " class="absolute left-0 top-0  w-[200rpx] h-[200rpx]  leading-[200rpx] text-center " style="background-color: rgba(0,0,0,0.3);">
                                                <text class="text-[#fff] text-[28rpx]">已售罄</text>
                                            </view>
                                            <view v-if="item.goodsSku.stock != 0 " class="absolute left-0 top-0  w-[200rpx] h-[200rpx]  leading-[200rpx] text-center " style="background-color: rgba(0,0,0,0.3);">
                                                <text class="text-[#fff] text-[28rpx]">已失效</text>
                                            </view>
                                        </view>
                                        <view class="flex flex-1 flex-wrap ml-[20rpx]">
                                            <view class="w-[100%] flex flex-col items-baseline">
                                                <view class="text-[#333] text-[28rpx] max-h-[80rpx] leading-[40rpx] font-400 multi-hidden">{{ item.goods.goods_name }}</view>
                                                <view class="box-border max-w-[376rpx] mt-[10rpx] px-[14rpx] h-[36rpx] leading-[36rpx] truncate text-[var(--text-color-light6)] bg-[#F5F5F5] text-[22rpx] rounded-[20rpx]"
                                                    v-if="item.goodsSku && item.goodsSku.sku_name">
                                                    {{ item.goodsSku.sku_name }}
                                                </view>
                                            </view>
                                            <view class="flex justify-between items-end self-end w-[100%]">
                                                <view class="text-[var(--price-text-color)] price-font">
                                                    <text class="text-[24rpx] font-500">￥</text>
                                                    <text class="text-[36rpx] font-500">{{ parseFloat(goodsPrice(item)).toFixed(2).split('.')[0] }}</text>
                                                    <text class="text-[24rpx] font-500">.{{ parseFloat(goodsPrice(item)).toFixed(2).split('.')[1] }}</text>
                                                </view>
                                            </view>
                                        </view>
                                    </view>
                                </view>
                            </view>
                        </view>
                        <ns-goods-recommend></ns-goods-recommend>
                    </view>
                </view>
            </template>
        </view>

        <!-- 优惠明细 -->
        <view @touchmove.prevent.stop>
            <u-popup class="popup-type" :show="couponDetailsShow" @close="couponDetailsShow = false">
                <view class="min-h-[200rpx] popup-common" @touchmove.prevent.stop>
                    <view class="flex justify-center items-center pt-[36rpx] pb-[56rpx] px-[26rpx] bg-[#fff] relative">
                        <text class="text-[32rpx]">优惠明细</text>
                        <text class="nc-iconfont nc-icon-guanbiV6xx text-[var(--text-color-light6)] absolute text-[32rpx] right-[26rpx]" @click="couponDetailsShow = false"></text>
                    </view>
                    <scroll-view class="h-[360rpx]" scroll-y="true">
                        <view class="flex justify-between h-[60rpx] px-[var(--pad-sidebar-m)]">
                            <text class="text-[28rpx]">商品总额</text>
                            <text class="text-[28rpx]">￥{{ total.goods_money }}</text>
                        </view>
                        <view class="flex justify-between h-[60rpx] px-[var(--pad-sidebar-m)]" v-if="Number(total.promotion_money)">
                            <text class="text-[28rpx]">满减</text>
                            <text class="text-[28rpx] text-[red]">-￥{{ total.promotion_money }}</text>
                        </view>
                    </scroll-view>
                </view>
            </u-popup>
        </view>

        <!--  #ifdef  H5 -->
        <view v-if="cartList.length" class="flex h-[96rpx] items-center bg-[#fff] fixed z-99999 left-0 right-0 bottom-[50px] pl-[30rpx] pr-[20rpx] box-solid mb-ios justify-between border-0 border-t-[2rpx] border-solid border-[#f6f6f6]">
            <view class="flex items-center" @click="selectAll">
                <text class="self-center iconfont text-color text-[34rpx] mr-[10rpx] w-[34rpx] h-[34rpx] rounded-[17rpx] overflow-hidden shrink-0"
                    :class="cartList.length == checkedNum ? 'iconxuanze1' : 'bg-[#F5F5F5]'"></text>
                <text class="font-400 text-[#303133] text-[26rpx]">全选</text>
            </view>
            <view class="flex items-center">
                <view class="flex-1 flex items-center justify-between" v-if="!isEdit">
                    <view class="mr-[20rpx]">
                        <view class="flex items-center text-[var(--price-text-color)] leading-[45rpx]">
                            <view class="font-400 text-[#303133] text-[28rpx]">合计：</view>
                            <text class="text-[var(--price-text-color)] price-font text-[32rpx] font-bold">￥{{ parseFloat(total.order_money).toFixed(2) }}</text>
                        </view>
                        <view class="flex items-center justify-end mt-[6rpx]" v-if="Number(total.promotion_money)" @click="couponDetailsShow = true">
                            <text class="text-[22rpx] text-[#666]">优惠明细</text>
                            <text class="iconfont iconjiantoushang text-[#666] !text-[22rpx] ml-[4rpx] font-bold"></text>
                        </view>
                    </view>
                    <button class="w-[180rpx] h-[70rpx] font-500 text-[26rpx] leading-[70rpx] !text-[#fff] m-0 rounded-full primary-btn-bg remove-border" @click="settlement">结算</button>
                </view>
                <view class="flex-1 flex items-center justify-end" v-else>
                    <button class="w-[180rpx] h-[70rpx] font-500 text-[26rpx] leading-[70rpx] !text-[#fff] m-0 rounded-full primary-btn-bg remove-border" @click="deleteCartFn">删除</button>
                </view>
            </view>
        </view>
        <!--  #endif -->
        <!--  #ifndef  H5 -->
        <view v-if="cartList.length" class="pl-[30rpx] pr-[20rpx] flex h-[96rpx] items-center bg-[#fff] fixed z-99999 left-0 right-0 bottom-[100rpx] box-solid mb-ios justify-between border-0 border-t-[2rpx] border-solid border-[#f6f6f6]">
            <view class="flex items-center" @click="selectAll">
                <text class="self-center iconfont text-color text-[30rpx] mr-[20rpx] w-[34rpx] h-[34rpx] rounded-[17rpx] overflow-hidden shrink-0"
                    :class="{'iconxuanze1' :cartList.length == checkedNum, 'bg-[#F5F5F5]':cartList.length != checkedNum}"></text>
                <text class="font-400 text-[#303133] text-[26rpx]">全选</text>
            </view>
            <view class="flex items-center">
                <view class="flex-1 flex items-center justify-between" v-if="!isEdit">
                    <view class="mr-[20rpx]">
                        <view class="flex items-center text-[var(--price-text-color)] leading-[45rpx]">
                            <view class="font-400 text-[#303133] text-[28rpx]">合计：</view>
                            <text class="text-[var(--price-text-color)] price-font text-[32rpx] font-bold">￥{{ parseFloat(total.order_money).toFixed(2) }}</text>
                        </view>
                        <view class="flex items-center justify-end mt-[6rpx]" v-if="total.promotion_money" @click="couponDetailsShow = true">
                            <text class="text-[22rpx] text-[#666]">优惠明细</text>
                            <text class="iconfont iconjiantoushang text-[#666] !text-[22rpx] ml-[4rpx] font-bold"></text>
                        </view>
                    </view>

                    <!-- #ifdef H5 -->
                    <button class="w-[180rpx] h-[70rpx] font-500 text-[26rpx] leading-[70rpx] !text-[#fff] m-0 rounded-full primary-btn-bg remove-border"
                        @click="settlement">结算</button>
                    <!-- #endif -->

                    <!-- #ifdef MP-WEIXIN -->
                    <!--                    <button v-if="isBindMobile && info && !info.mobile" class="w-[180rpx] h-[70rpx] font-500 text-[26rpx] leading-[70rpx] !text-[#fff] m-0 rounded-full primary-btn-bg remove-border" open-type="getPhoneNumber" @getphonenumber="memberStore.bindMobile">结算</button>-->
                    <!--                    <button v-else class="w-[180rpx] h-[70rpx] font-500 text-[26rpx] leading-[70rpx] !text-[#fff] m-0 rounded-full primary-btn-bg remove-border" @click="settlement">结算</button>-->
                    <button class="w-[180rpx] h-[70rpx] font-500 text-[26rpx] leading-[70rpx] !text-[#fff] m-0 rounded-full primary-btn-bg remove-border"
                        @click="settlement">结算</button>
                    <!-- #endif -->

                </view>
                <view class="flex-1 flex items-center justify-end" v-else>
                    <button class="w-[180rpx] h-[70rpx] font-500 text-[26rpx] leading-[70rpx] !text-[#fff] m-0 rounded-full primary-btn-bg remove-border"
                        @click="deleteCartFn">删除</button>
                </view>
            </view>
        </view>
        <!--  #endif -->
        <loading-page :loading="loading"></loading-page>
        <ns-goods-manjian ref="manjianShowRef"></ns-goods-manjian>
        <tabbar />
        <!-- 强制绑定手机号 -->
        <bind-mobile ref="bindMobileRef" />
    </view>
</template>

<script setup lang="ts">
import { ref, computed, nextTick, watch } from 'vue'
import useMemberStore from '@/stores/member'
import { useLogin } from '@/hooks/useLogin'
import { onShow } from '@dcloudio/uni-app'
import { img, redirect } from '@/utils/common'
import useCartStore from '@/addon/shop/stores/cart'
import { getCartGoodsList, getCartCalculate } from '@/addon/shop/api/cart'
import bindMobile from '@/components/bind-mobile/bind-mobile.vue';
import { t } from "@/locale";
import { useGoods } from '@/addon/shop/hooks/useGoods'
import nsGoodsManjian from '@/addon/shop/components/ns-goods-manjian/ns-goods-manjian.vue';
import nsGoodsRecommend from '@/addon/shop/components/ns-goods-recommend/ns-goods-recommend.vue';
import { topTabar } from '@/utils/topTabbar';
import { useShare } from '@/hooks/useShare'
const { setShare } = useShare()
/********* 自定义头部 - start ***********/
const topTabarObj = topTabar()
let topTabbarData = topTabarObj.setTopTabbarParam({ title: '购物车', topStatusBar: { textColor: '#333' }})
/********* 自定义头部 - end ***********/

const diyGoods = useGoods();
const memberStore = useMemberStore()
const info = computed(() => memberStore.info)
const loading = ref(true)
const optionLoading = ref(false)
const total = ref({
    goods_money: 0, //商品金额
    order_money: 0, //订单金额
    promotion_money: 0 //优惠金额
})
const cartList = ref<object[]>([])
const invalidList = ref<object[]>([]) //  失效商品：已下架、已删除
const isEdit = ref(false)
const querOne = ref(true)
const cartStore = useCartStore();
const manjianShowRef: any = ref(null); //满减送
const couponDetailsShow: any = ref(false); //优惠明细

const getCartGoodsListFn = () => {
    getCartGoodsList({}).then(({ data }) => {
        cartList.value = []
        invalidList.value = []
        data.forEach(item => {
            item.checked = false
            if (item.goodsSku) {
                if (item.goods.status && item.goods.delete_time == 0) {
                    if (item.goodsSku.stock) {
                        if (item.num > item.goodsSku.stock) item.num = item.goodsSku.stock;
                        cartList.value.push(item)
                    } else {
                        // 库存为0 时，移动到售罄商品
                        invalidList.value.push(item)
                    }
                } else {
                    invalidList.value.push(item)
                }
            }
        })

        selectAll();
        cartCalculateFn();
        loading.value = false
        cartStore.isAddCartRecommend = false
        if (querOne.value) querOne.value = false
    }).catch((err) => {
        if (err.code == 401) {
            cartList.value = []
            invalidList.value = []
            loading.value = false
        }
    })
}

onShow(() => {
    console.log('购物车')
       
       setTimeout(() => {
            let share = {
                title: '购物车',
                desc: '',
                // url: img(detail.value.material_list[activeIndex.value].url)
            }

            setShare({
                wechat: {
                    ...share
                },
                weapp: {
                    ...share
                }
            });
    }, 600);
    getCartGoodsListFn()
})

const goodsSkuInputFn = (data) => {
    setTimeout(() => {
        if (!data.num || data.num <= numLimit(data).min) {
            data.num = numLimit(data).min;
        }
        if (data.num >= numLimit(data).max) {
            data.num = numLimit(data).max;
        }
        uni.$u.debounce((event: any) => {
            cartStore.increase({
                id: data.id,
                goods_id: data.goods_id,
                sku_id: data.sku_id,
                stock: data.goodsSku.stock,
                sale_price: data.goodsSku.sale_price,
                num: Number(data.num)
            }, 0);
        }, 500)
    }, 0)
}
const goodsSkuBlurFn = (event, index) => {
    setTimeout(() => {
        const data: any = cartList.value[index]
        if (!data.num || data.num <= numLimit(data).min) {
            data.num = numLimit(data).min;
        }
        if (data.num >= numLimit(data).max) {
            data.num = numLimit(data).max;
        }

        uni.$u.debounce((event: any) => {
            cartStore.increase({
                id: data.id,
                goods_id: data.goods_id,
                sku_id: data.sku_id,
                stock: data.goodsSku.stock,
                sale_price: data.goodsSku.sale_price,
                num: Number(data.num)
            }, 0, cartCalculateFn());
        }, 500)
    }, 0)
}

const checkedNum = computed(() => {
    let num = 0
    cartList.value.forEach((item: any) => {
        item.checked && (num += 1)
    })
    return num
})

const manjianOpenFn = (data: any) => {
    let obj = {};
    obj.condition_type = data.condition_type;
    obj.rule_json = data.rule_json;
    obj.name = data.manjian_name;
    manjianShowRef.value.open(obj);
}

let isLoadCalculate = false;
const cartCalculateFn = () => {
    let calculateArr: Object = [];
    cartList.value.forEach((item: any) => {
        if (item.checked && item.goodsSku) {
            let obj = {};
            obj.num = item.num;
            obj.sku_id = item.sku_id;
            calculateArr.push(obj);
        }
    })

    if (!calculateArr.length) {
        total.value.order_money = 0
        total.value.promotion_money = 0
        return false;
    }

    if (isLoadCalculate) return false;
    isLoadCalculate = true;

    getCartCalculate({ sku_ids: calculateArr }).then(({ data }) => {
        total.value.goods_money = data.goods_money;
        total.value.order_money = data.order_money;
        total.value.promotion_money = data.promotion_money;
        cartList.value.forEach((item, index) => {
            for (let subIndex = 0; subIndex < data.match_list.length; subIndex++) {
                if (item.goods_id == data.match_list[subIndex].goods_id && item.sku_id == data.match_list[subIndex].sku_id && item.manjian_info && Object.keys(item.manjian_info).length) {
                    item.manjian_info.is_show = true;
                    let subTempShowNum = 0;
                    item.manjian_info.rule_json.forEach((threeItem, threeIndex) => {
                        if (threeIndex == data.match_list[subIndex].level) {
                            threeItem.is_show = true;
                            subTempShowNum++;
                        } else {
                            threeItem.is_show = false;
                        }
                    })
                    if (subTempShowNum == 0) {
                        item.manjian_info.is_show = false;
                    } else {
                        item.manjian_info.is_show = true;
                    }
                    return;
                }
            }
            if (item.manjian_info && Object.keys(item.manjian_info).length) {
                item.manjian_info.is_show = false;
            }
        })
        isLoadCalculate = false;
    })
}

const toLogin = () => {
    useLogin().setLoginBack({ url: '/addon/shop/pages/goods/cart' })
}

const toDetail = (data: any) => {
    redirect({ url: '/addon/shop/pages/goods/detail', param: { goods_id: data.goods_id } })
}

const numChange = (event: any, index: any) => {
    uni.$u.debounce((event: any) => {
        const data: any = cartList.value[index]
        cartStore.increase({
            id: data.id,
            goods_id: data.goods_id,
            sku_id: data.sku_id,
            stock: data.goodsSku.stock,
            sale_price: data.goodsSku.sale_price,
            num: data.num
        }, 0, cartCalculateFn());
    }, 500)
}
const addNumChange = (data: any) => {
    if (data.num >= data.goods.stock) {
        uni.showToast({ title: "商品库存不足", icon: 'none' });
        return;
    }

    if (data.goods.is_limit) {
        let tips = `该商品单次限购${ data.goods.max_buy }件`;
        if (data.goods.limit_type != 1) { //单次限购
            tips = `该商品每人限购${ data.goods.max_buy }件`;
        }
        if (data.num >= data.goods.max_buy) {
            uni.showToast({ title: tips, icon: 'none' })
        }
    }
}

const reduceNumChange = (data: any) => {
    if (data.goods.is_limit && data.goods.min_buy) {
        let tips = `该商品起购${ data.goods.min_buy }件`;
        if (data.num <= data.goods.min_buy) {
            uni.showToast({ title: tips, icon: 'none' })
        }
    }
}

const numLimit = (data: any) => {
    let obj = {
        min: 1,
        max: data.goodsSku.stock || 1
    };

    // 限购 - 是否开启限购
    if (data.goods.is_limit) {
        if (data.goods.max_buy) {
            let max_buy = data.goods.max_buy;
            if (max_buy > data.goods.stock) {
                obj.max = data.goods.stock
            } else if (max_buy <= data.goods.stock) {
                obj.max = max_buy;
            }
        }
    }

    // 起售
    if (data.goods.min_buy > 0) {
        obj.min = data.goods.min_buy;
    }
    return obj;
}

const cartOptions = ref([
    {
        text: t('delete'),
        style: {
            backgroundColor: 'var(--primary-color)',
            width: '100rpx',
            height: '100%',
            borderRadius: '10rpx'
        }
    }
]);

const swipeActive = ref()
const swipeClick = (index: any, item: any) => {
    if (optionLoading.value) return
    optionLoading.value = true
    cartStore.delete(item.id, () => {
        cartList.value.splice(index, 1)
        nextTick(() => {
            if (swipeActive.value) swipeActive.value.closeOther()
        })
        cartCalculateFn();
        optionLoading.value = false
    })
}

/**
 * 选择单个商品
 */
const selectOnlyGoods = (data: any = {}) => {
    data.checked = !data.checked
    cartCalculateFn();
}

/**
 * 全选
 */
const selectAll = () => {
    const checked = cartList.value.length == checkedNum.value ? false : true
    cartList.value.forEach((item: any) => {
        item.checked = checked
    })
    cartCalculateFn();
}

//强制绑定手机号
const bindMobileRef: any = ref(null)
const isBindMobile = ref(uni.getStorageSync('isBindMobile'))

/**
 * 结算
 */
const settlement = () => {

    // #ifdef H5
    // if(uni.getStorageSync('isBindMobile')){
    //     bindMobileRef.value.open()
    //     return false
    // }
    // #endif

    if (!checkedNum.value) {
        uni.showToast({ title: '还没有选择商品', icon: 'none' })
        return
    }
    const ids: any = []
    cartList.value.forEach((item: any) => {
        if (item.checked) ids.push(item.id)
    })

    uni.setStorage({
        key: 'orderCreateData',
        data: {
            cart_ids: ids
        },
        success() {
            redirect({ url: '/addon/shop/pages/order/payment' })
        }
    })
}

/**
 * 删除
 */
const deleteCartFn = () => {
    if (!checkedNum.value) {
        uni.showToast({ title: '还没有选择商品', icon: 'none' })
        return
    }
    if (optionLoading.value) return
    optionLoading.value = true

    const ids: any = []
    cartList.value.forEach((item: any) => {
        if (item.checked) ids.push(item.id)
    })

    cartStore.delete(ids, () => {
        getCartGoodsListFn()
        optionLoading.value = false
    })
}
/**
 * 清空无效商品
 */
const deleteInvalidList = () => {
    if (optionLoading.value) return
    optionLoading.value = true
    const ids = invalidList.value.map((el: any) => el.id)

    cartStore.delete(ids, () => {
        getCartGoodsListFn()
        optionLoading.value = false
    })
    invalidList.value = []
}

// 商品价格
const goodsPrice = (data: any) => {
    return data.goodsSku.show_price
}

watch(
    () => cartStore.isAddCartRecommend,
    (newValue, oldValue) => {
        if(newValue){
            getCartGoodsListFn()
        }
    })
</script>
<style lang="scss" scoped>
@import '@/addon/shop/styles/common.scss';

.remove-border {
    &::after {
        border: none;
    }
}

:deep(uni-page) {
    background: var(--page-bg-color);
}

uni-page-body {
    height: 100%;
}

.text-color {
    color: var(--primary-color);
}

.bg-color {
    background-color: var(--primary-color);
}

:deep(.tab-bar-placeholder) {
    display: none !important;
}

:deep(.u-tabbar__placeholder) {
    display: none !important;
}

/*  #ifdef  H5  */
.scroll-height {
    padding-bottom: calc(100rpx + 50px + constant(safe-area-inset-bottom));
    padding-bottom: calc(100rpx + 50px + env(safe-area-inset-bottom));
}

/*  #endif  */
/*  #ifndef  H5  */
.scroll-height {
    padding-bottom: calc(100rpx + 50px + constant(safe-area-inset-bottom));
    padding-bottom: calc(100rpx + 50px + env(safe-area-inset-bottom));
}

/*  #endif  */

.text-ellipsis {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    overflow: hidden;
}

:deep(.u-swipe-action-item__right) {
    padding: 2rpx;
}

:deep(.u-swipe-action-item__right__button__wrapper) {
    padding: 0 10rpx !important;
}

:deep(.u-swipe-action-item__right__button__wrapper__text) {
    font-size: 24rpx !important;
}

:deep(.u-tabbar .u-tabbar__content) {
    z-index: 99999 !important;
}
</style>

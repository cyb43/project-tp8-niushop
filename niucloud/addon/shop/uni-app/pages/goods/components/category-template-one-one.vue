<template>
    <view class="min-h-screen bg-[var(--page-bg-color)] overflow-hidden">
        <view class="mescroll-box bg-[var(--page-bg-color)]"
              :class="{ 'cart': config.cart.control && config.cart.event === 'cart', 'detail': !(config.cart.control && config.cart.event === 'cart') }" v-if="tabsData.length">
            <mescroll-body ref="mescrollRef" :down="{ use: false }" @init="mescrollInit" @up="getListFn">
                <view v-if="config.search.control" class="search-box z-10 bg-[#fff] fixed top-0 left-0 right-0 h-[100rpx] box-border">
                    <view class="flex-1 search-input">
                        <text @click.stop="searchNameFn" class="nc-iconfont nc-icon-sousuo-duanV6xx1 btn"></text>
                        <input class="input" type="text" v-model.trim="searchName" :placeholder="config.search.title" @confirm="searchNameFn" placeholderClass="text-[var(--text-color-light9)]">
                        <text v-if="searchName" class="nc-iconfont nc-icon-cuohaoV6xx1 clear" @click="searchName=''"></text>
                    </view>
                </view>
                <!--  #ifdef H5 -->
                <view class="tabs-box z-2 fixed left-0 bg-[#fff] bottom-[50px] top-0"
                      :class="{ '!top-[100rpx]': config.search.control, 'pb-[98rpx]': config.cart.control && config.cart.event === 'cart' }">
                    <scroll-view :scroll-y="true" class="scroll-height">
                        <view class="bg-[var(--temp-bg)]">
                            <view class="tab-item" :class="{ 'tab-item-active': index == tabActive,'rounded-br-[12rpx]':tabActive-1===index,'rounded-tr-[12rpx]':tabActive+1===index}"
                                  v-for="(item, index) in tabsData" :key="index" @click="firstLevelClick(index, item)">
                                <view class="text-box text-left leading-[1.3] break-words px-[16rpx]">
                                    {{ item.category_name }}
                                </view>
                            </view>
                        </view>
                    </scroll-view>
                </view>
                <!--  #endif -->
                <!--  #ifndef  H5 -->
                <view class="tabs-box z-2 fixed left-0 bg-[#fff] pb-ios bottom-[100rpx] top-0" :class="{ 'top-[98rpx]': config.search.control, '!bottom-[198rpx]': config.cart.control && config.cart.event === 'cart' }">
                    <scroll-view :scroll-y="true" class="scroll-height">
                        <view class="bg-[var(--temp-bg)]">
                            <view class="tab-item" :class="{ 'tab-item-active': index == tabActive,'rounded-br-[12rpx]':tabActive-1===index,'rounded-tr-[12rpx]':tabActive+1===index}"
                                  v-for="(item, index) in tabsData" :key="index" @click="firstLevelClick(index, item)">
                                <view class="text-box text-left leading-[1.3] break-words px-[16rpx]">{{ item.category_name }}</view>
                            </view>
                        </view>
                    </scroll-view>
                </view>
                <!--  #endif -->
                <view class="flex justify-center flex-wrap pl-[188rpx] pb-[20rpx]" :class="{ ' pt-[120rpx]': config.search.control, ' pt-[20rpx]': !(config.search.control) }">
                    <template v-for="(item, index) in list" :key="item.goods_id">
                        <view class="box-border bg-white w-full flex mr-[20rpx] py-[24rpx] px-[20rpx] rounded-[var(--rounded-small)]" :class="{ 'mt-[16rpx]': index }" @click.stop="toLink(item.goods_id)">
                            <view class="w-[168rpx] h-[168rpx] flex items-center justify-center rounded-[var(--goods-rounded-small)] overflow-hidden">
                                <u--image width="168rpx" height="168rpx" radius="var(--goods-rounded-small)" :src="img( item.goods_cover_thumb_mid || '')" model="aspectFill">
                                    <template #error>
                                        <image class="w-[168rpx] h-[168rpx]" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill" />
                                    </template>
                                </u--image>
                            </view>
                            <view class="flex flex-1 ml-[20rpx] flex-wrap flex-col">
                                <view class="max-h-[80rpx] text-[26rpx] leading-[40rpx] multi-hidden">{{ item.goods_name }}</view>
                                <view class="flex-1 flex items-end justify-between">
                                    <view class="text-[var(--price-text-color)] price-font -mb-[8rpx]">
                                        <text class="text-[24rpx] font-500">￥</text>
                                        <text class="text-[40rpx] font-500">{{ parseFloat(goodsPrice(item)).toFixed(2).split('.')[0] }}</text>
                                        <text class="text-[24rpx] font-500">.{{ parseFloat(goodsPrice(item)).toFixed(2).split('.')[1] }}</text>
                                        <image class="h-[24rpx] max-w-[46rpx] ml-[6rpx]" v-if="priceType(item) == 'member_price'" :src="img('addon/shop/VIP.png')" mode="heightFix" />
										<image class="h-[24rpx] max-w-[60rpx] ml-[6rpx]" v-if="priceType(item) == 'newcomer_price'" :src="img('addon/shop/newcomer.png')" mode="heightFix" />
										<image class="h-[24rpx] max-w-[80rpx] ml-[6rpx]" v-if="priceType(item) == 'discount_price'" :src="img('addon/shop/discount.png')" mode="heightFix" /> 
                                    </view>
                                    <template v-if="!item.isMaxBuy">
                                        <view v-if=" (item.goods_type == 'real' || (item.goods_type == 'virtual' && item.virtual_receive_type != 'verify')) && item.goodsSku.sku_spec_format === '' && cartList['goods_' + item.goods_id] && cartList['goods_' + item.goods_id]['sku_' + item.goodsSku.sku_id] && config.cart.control && config.cart.event === 'cart'" class="flex items-center">
                                            <view class="relative w-[32rpx] h-[32rpx]">
                                                <text class="text-[32rpx] text-color nc-iconfont nc-icon-jianshaoV6xx absolute flex items-center justify-center -left-[14rpx] -bottom-[14rpx] -right-[14rpx] -top-[14rpx]"
                                                    @click.stop="reduceCart(item, cartList['goods_' + item.goods_id]['sku_' + item.goodsSku.sku_id])"></text>
                                            </view>
                                            <text class="text-[#333] text-[24rpx] mx-[16rpx]">{{ cartList['goods_' + item.goods_id]['sku_' + item.goodsSku.sku_id].num }}</text>
                                            <view class="relative w-[32rpx] h-[32rpx]">
                                                <text class="text-[32rpx] text-color iconfont iconjiahao2fill absolute flex items-center justify-center -left-[14rpx] -bottom-[14rpx] -right-[14rpx] -top-[14rpx]"
                                                    :id="'itemCart' + index" @click.stop="addCartBtn(item,cartList['goods_' + item.goods_id]['sku_' + item.goodsSku.sku_id],'itemCart' + index)"></text>
                                            </view>
                                        </view>
                                        <template
                                            v-else-if="(item.goods_type == 'virtual' && item.virtual_receive_type != 'verify') || item.goods_type == 'real'">
                                            <view v-if="config.cart.control && config.cart.style === 'style-1'" class="h-[44rpx] relative">
                                                <view :id="'itemCart' + index"
                                                      class="w-[102rpx] box-border text-center text-[#fff] primary-btn-bg h-[46rpx] text-[22rpx] leading-[46rpx] rounded-[100rpx]"
                                                      @click.stop="itemCart(item, 'itemCart' + index)">{{ config.cart.text }}</view>
                                                <view
                                                    v-if="cartList['goods_' + item.goods_id] && cartList['goods_' + item.goods_id].totalNum"
                                                    :class="['absolute right-[-16rpx] top-[-16rpx] rounded-[30rpx] h-[30rpx] min-w-[30rpx] text-center leading-[26rpx] bg-[var(--primary-color)]  text-[#fff] text-[20rpx] font-500 box-border box-border border-[2rpx] border-solid border-[#fff]', cartList['goods_' + item.goods_id].totalNum > 9 ? 'px-[10rpx]' : '']">{{ cartList['goods_' + item.goods_id].totalNum }}</view>
                                            </view>
                                            <view v-if="config.cart.control && config.cart.style === 'style-2'" class="w-[50rpx] h-[50rpx] relative" @click.stop="itemCart(item, 'itemCart' + index)">
                                                <text :id="'itemCart' + index" class="text-color nc-iconfont nc-icon-tianjiaV6xx text-[44rpx]"></text>
                                                <view v-if="cartList['goods_' + item.goods_id] && cartList['goods_' + item.goods_id].totalNum"
                                                    :class="['absolute right-[-16rpx] top-[-16rpx] rounded-[30rpx] h-[30rpx] min-w-[30rpx] text-center leading-[26rpx] bg-[var(--primary-color)] text-[#fff] text-[20rpx] font-500 box-border box-border border-[2rpx] border-solid border-[#fff]', cartList['goods_' + item.goods_id].totalNum > 9 ? 'px-[10rpx]' : '']">{{ cartList['goods_' + item.goods_id].totalNum }}</view>
                                            </view>
                                            <view v-if="config.cart.control && config.cart.style === 'style-3'"
                                                  class="w-[50rpx] flex justify-center items-end  h-[50rpx]  relative"
                                                  @click.stop="itemCart(item, 'itemCart' + index)">
                                                <text :id="'itemCart' + index" class="text-color nc-iconfont nc-icon-gouwucheV6xx6 !text-[34rpx]"></text>
                                                <view v-if="cartList['goods_' + item.goods_id] && cartList['goods_' + item.goods_id].totalNum"
                                                    :class="['absolute right-[-10rpx] top-[-2rpx] rounded-[30rpx] h-[30rpx] min-w-[30rpx] text-center leading-[26rpx] bg-[var(--primary-color)] text-[#fff] text-[20rpx] font-500 box-border box-border border-[2rpx] border-solid border-[#fff]', cartList['goods_' + item.goods_id].totalNum > 9 ? 'px-[10rpx]' : '']">{{ cartList['goods_' + item.goods_id].totalNum }}</view>
                                            </view>
                                            <view v-if="config.cart.control && config.cart.style === 'style-4'"
                                                  class="w-[50rpx] h-[50rpx] justify-center flex items-end relative"
                                                  @click.stop="itemCart(item, 'itemCart' + index)">
                                                <view :id="'itemCart' + index" class=" flex items-center justify-center text-[#fff] bg-color h-[44rpx] w-[44rpx] rounded-[22rpx] text-center">
                                                    <text class="nc-iconfont nc-icon-gouwucheV6xx6 !text-[26rpx]"></text>
                                                </view>
                                                <view v-if="cartList['goods_' + item.goods_id] && cartList['goods_' + item.goods_id].totalNum"
                                                    :class="['absolute right-[-10rpx] top-[-10rpx] rounded-[30rpx] h-[30rpx] min-w-[30rpx] text-center leading-[26rpx] bg-[var(--primary-color)] text-[#fff] text-[20rpx] font-500 box-border border-[2rpx] border-solid border-[#fff]', cartList['goods_' + item.goods_id].totalNum > 9 ? 'px-[10rpx]' : '']">{{ cartList['goods_' + item.goods_id].totalNum }}</view>
                                            </view>
                                        </template>
                                    </template>
                                </view>
                            </view>
                        </view>

                    </template>
                    <mescroll-empty class="part" v-if="!list.length && !loading && listLoading" :option="{tip : '暂无商品'}"></mescroll-empty>
                </view>
                <add-cart-popup ref="cartRef" />
            </mescroll-body>
            <!--  #ifdef  H5 -->
            <view v-if="config.cart.control && config.cart.event === 'cart'"
                  class="bg-[#fff] z-10 flex justify-between items-center fixed left-0 right-0 bottom-[50px] box-solid px-[24rpx] py-[17rpx] mb-ios border-[0] border-t-[2rpx] border-solid border-[#f6f6f6]">
                <view class="flex items-center">
                    <view class="w-[66rpx] h-[66rpx] mr-[27rpx] relative">
                        <view id="animation-end" class="w-[66rpx] h-[66rpx] rounded-[35rpx] bg-[var(--primary-color)] text-center leading-[70rpx]" @click.stop="toCart">
                            <text class="nc-iconfont nc-icon-gouwucheV6mm1 text-[#fff] text-[32rpx]"></text>
                        </view>
                        <view v-if="totalNum" class="border-[1rpx] border-solid border-[#fff]"
                              :class="['absolute left-[40rpx] top-[-10rpx] rounded-[28rpx] h-[28rpx] min-w-[28rpx] text-center leading-[26rpx] bg-[var(--primary-color)] text-[#fff] text-[20rpx] font-500 box-border', totalNum > 9 ? 'px-[10rpx]' : '']">
                            {{ totalNum > 99 ? "99+" : totalNum }}
                        </view>
                    </view>
                    <text class="text-[26rpx] font-500 text-[#333]">总计：</text>
                    <view class="text-[var(--price-text-color)] price-font font-bold flex items-baseline">
                        <text class="text-[26rpx] mr-[6rpx]">￥</text>
                        <text class="text-[44rpx]">{{ parseFloat(totalMoney) }}</text>
                    </view>
                </view>
                <button
                    class="w-[180rpx] h-[70rpx] text-[26rpx] leading-[70rpx] font-500 m-0 rounded-full remove-border"
                    :class="{'primary-btn-bg !text-[#fff]': parseFloat(totalMoney) > 0, 'bg-[#F7F7F7] !text-[var(--text-color-light9)]': parseFloat(totalMoney) <= 0}" @click="settlement">去结算</button>
            </view>
            <!--  #endif -->

            <!-- #ifdef MP-WEIXIN -->
            <view v-if="config.cart.control && config.cart.event === 'cart'" class="bg-[#fff] z-10 flex justify-between items-center fixed left-0 right-0 bottom-[100rpx] box-solid px-[24rpx] py-[17rpx] mb-ios border-[0] border-t-[2rpx] border-solid border-[#f6f6f6]">
                <view class="flex items-center">
                    <view class="w-[66rpx] h-[66rpx] mr-[27rpx] relative">
                        <view id="animation-end" class="w-[66rpx] h-[66rpx] rounded-[35rpx] bg-[var(--primary-color)] text-center leading-[66rpx]" @click.stop="toCart">
                            <text class="nc-iconfont nc-icon-gouwucheV6mm1 text-[#fff] text-[32rpx]"></text>
                        </view>
                        <view v-if="totalNum" class="border-[1rpx] border-solid border-[#fff]"
                              :class="['absolute left-[40rpx] top-[-10rpx] rounded-[28rpx] h-[28rpx] min-w-[28rpx] text-center leading-[26rpx] bg-[var(--primary-color)] text-[#fff] text-[20rpx] font-500 box-border', totalNum > 9 ? 'px-[10rpx]' : '']">
                            {{ totalNum > 99 ? "99+" : totalNum }}
                        </view>
                    </view>
                    <text class="text-[26rpx] font-500 text-[#333]">总计：</text>
                    <view class="text-[var(--price-text-color)] price-font font-bold inline-block">
                        <text class="text-[26rpx] mr-[6rpx]">￥</text>
                        <text class="text-[44rpx]">{{ parseFloat(totalMoney) }}</text>
                    </view>
                </view>
                <!--<button v-if="isBindMobile && userInfo && !userInfo.mobile" class="w-[180rpx] h-[70rpx] text-[26rpx] leading-[70rpx] font-500 m-0 rounded-full remove-border" :class="{'primary-btn-bg !text-[#fff]': parseFloat(totalMoney) > 0, 'bg-[#F7F7F7] !text-[var(&#45;&#45;text-color-light9)]': parseFloat(totalMoney) <= 0}" open-type="getPhoneNumber" @getphonenumber="memberStore.bindMobile">去结算</button>-->
                <!--<button v-else class="w-[180rpx] h-[70rpx] text-[26rpx] leading-[70rpx] font-500 m-0 rounded-full remove-border" :class="{'primary-btn-bg !text-[#fff]': parseFloat(totalMoney) > 0, 'bg-[#F7F7F7] !text-[var(&#45;&#45;text-color-light9)]': parseFloat(totalMoney) <= 0}" @click="settlement">去结算</button>-->
                <button
                    class="w-[180rpx] h-[70rpx] text-[26rpx] leading-[70rpx] font-500 m-0 rounded-full remove-border"
                    :class="{'primary-btn-bg !text-[#fff]': parseFloat(totalMoney) > 0, 'bg-[#F7F7F7] !text-[var(--text-color-light9)]': parseFloat(totalMoney) <= 0}" @click="settlement">去结算</button>
            </view>
            <!--  #endif -->
        </view>

        <view v-show="animationElStatus" :style="animationElStatus" class="fixed z-999 flex items-center justify-center text-[#fff] bg-color h-[44rpx] w-[44rpx] rounded-[22rpx] text-center">
            <text class=" nc-iconfont nc-icon-gouwucheV6xx6 !text-[30rpx]"></text>
        </view>
        <mescroll-empty v-if="!tabsData.length && !loading" :option="{tip : '暂无商品分类'}"></mescroll-empty>
        <loading-page :loading="loading"></loading-page>

        <tabbar />
        <!-- 强制绑定手机号 -->
        <bind-mobile ref="bindMobileRef" />
    </view>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, getCurrentInstance } from 'vue';
import { t } from '@/locale';
import { img, redirect } from '@/utils/common';
import { getGoodsCategoryTree, getGoodsPages } from '@/addon/shop/api/goods';
import MescrollBody from '@/components/mescroll/mescroll-body/mescroll-body.vue';
import MescrollEmpty from '@/components/mescroll/mescroll-empty/mescroll-empty.vue';
import useMescroll from '@/components/mescroll/hooks/useMescroll.js';
import addCartPopup from './add-cart-popup.vue'
import bindMobile from '@/components/bind-mobile/bind-mobile.vue';
import { onPageScroll, onReachBottom } from '@dcloudio/uni-app';
import { useLogin } from '@/hooks/useLogin'
import useMemberStore from '@/stores/member'
import useCartStore from '@/addon/shop/stores/cart'
import { cloneDeep } from 'lodash-es'

// 查询购物车列表
const cartStore = useCartStore();
cartStore.getList();

const cartList = computed(() => cartStore.cartList)
const totalNum = computed(() => cartStore.totalNum)
const totalMoney = computed(() => cartStore.totalMoney)
const memberStore = useMemberStore()
const userInfo = computed(() => memberStore.info)
const { mescrollInit, getMescroll } = useMescroll(onPageScroll, onReachBottom);
const prop = defineProps({
    config: {
        type: Object,
        default: (() => {
            return {}
        })
    },
    categoryId: {
        type: [String, Number],
        default: 0
    }
})
let config = prop.config
let categoryId = prop.categoryId;
const list = ref<Array<Object>>([]);
const searchName = ref("");
const loading = ref<boolean>(true);//页面加载动画
const listLoading = ref<boolean>(false);//列表加载动画
const cartData = ref<Array<any>>([])
const instance = getCurrentInstance(); // 获取组件实例
cartData.value = uni.getStorageSync('shopCart') || []

interface mescrollStructure {
    num: number,
    size: number,
    endSuccess: Function,
    [propName: string]: any
}

const getListFn = (mescroll: mescrollStructure) => {
    listLoading.value = false
    getGoodsPages({
        page: mescroll.num,
        limit: mescroll.size,
        goods_category: categoryId, // 商品分类id
    }).then((res: any) => {
        let newArr = res.data.data
        //设置列表数据

        if (mescroll.num == 1) {
            list.value = []; //如果是第一页需手动制空列表
        }
        list.value = list.value.concat(newArr);
        // 商品限购
        goodsMaxBuy();
        loading.value = false;
        mescroll.endSuccess(newArr.length);
        if (!list.value.length) listLoading.value = true
    }).catch(() => {
        loading.value = false;
        listLoading.value = true
        mescroll.endErr(); // 请求失败, 结束加载
    })
}

const goodsMaxBuy = () => {
    list.value.forEach((data, index) => {
        data.isMaxBuy = false;

        let maxBuyNum = -1;
        // 限购 - 是否开启限购
        if (data.is_limit) {
            if (data.max_buy) {
                let max_buy = 0;
                if (data.limit_type == 1) { //单次限购
                    max_buy = data.max_buy;
                } else { // 单人限购
                    let buyVal = data.max_buy - (data.has_buy || 0);
                    max_buy = buyVal > 0 ? buyVal : 0;
                }

                if (max_buy > data.stock) {
                    maxBuyNum = data.stock
                } else if (max_buy <= data.stock) {
                    maxBuyNum = max_buy;
                }
            }
        }

        if (maxBuyNum == 0) {
            data.isMaxBuy = true;
        }
    })
}

const toLink = (goods_id: string) => {
    redirect({ url: '/addon/shop/pages/goods/detail', param: { goods_id } })
}

onMounted(() => {
    getCategoryData()
})

//设置购物车动画
const animationElStatus = ref('')
const animationAddRepeatFlag = ref<Boolean>(false)
const cartRepeatFlag = ref<Boolean>(false)
const animationAddCart = (row: any, id: any) => {
    if (animationAddRepeatFlag.value || cartRepeatFlag.value) return false
    animationAddRepeatFlag.value = true
    cartRepeatFlag.value = true

    let obj: any = {
        goods_id: row.goodsSku.goods_id,
        sku_id: row.goodsSku.sku_id,
        sale_price: goodsPrice(row),
        stock: row.goodsSku.stock
    };
    if (row.id) {
        obj.num = row.num;
        obj.id = row.id;
    }

    // 起购
    let num = 1;
    if (row.min_buy > 0 && !row.num) {
        num = row.min_buy;
    } else {
        num = 1;
    }

    cartStore.increase(obj, num, () => {
        cartRepeatFlag.value = false
    });
    // #ifdef  MP-WEIXIN
    setTimeout(() => {
        uni.createSelectorQuery().in(instance).select('#animation-end').boundingClientRect((res: any) => {
            uni.createSelectorQuery().in(instance).select('#' + id).boundingClientRect((position: any) => {
                animationElStatus.value = `top: ${ position.top }px; left: ${ position.left }px;`
                setTimeout(() => {
                    animationElStatus.value = `top: ${ res.top + res.height / 2 - position.height / 3 }px; left: ${ res.left + res.width / 2 - position.width / 3 }px; transition: all 0.8s; transform: rotate(-720deg);`
                }, 20);

                setTimeout(() => {
                    animationElStatus.value = ''
                    animationAddRepeatFlag.value = false
                }, 1020);

            }).exec()

        }).exec()
    }, 100)
    // #endif
    // #ifdef  H5
    setTimeout(() => {
        const animationEnd: any = window.document.getElementById('animation-end');
        const animationEndLeft = animationEnd.getBoundingClientRect().left;
        const animationEndTop = animationEnd.getBoundingClientRect().top;

        const itemCart: any = window.document.getElementById(id);
        const itemCartLift = itemCart.getBoundingClientRect().left;
        const itemCartTop = itemCart.getBoundingClientRect().top;
        animationElStatus.value = `top: ${ itemCartTop }px; left: ${ itemCartLift }px;`

        setTimeout(() => {
            animationElStatus.value = `top: ${ animationEndTop + animationEnd.offsetHeight / 2 - itemCart.offsetHeight / 3 }px; left: ${ animationEndLeft + animationEnd.offsetWidth / 2 - itemCart.offsetHeight / 3 }px; transition: all 0.8s; transform: rotate(-720deg);`
        }, 20);
        setTimeout(() => {
            animationElStatus.value = ''
            animationAddRepeatFlag.value = false
        }, 1020);

    }, 100);
    // #endif
}

/**
 * @description 获取分类数据
 * */
const tabsData: any = ref<Array<Object>>([])
const getCategoryData = () => {
    loading.value = true;
    getGoodsCategoryTree().then((res: any) => {
        tabsData.value = res.data;
        if (categoryId) {
            for (let i = 0; i < tabsData.value.length; i++) {
                if (tabsData.value[i].category_id == categoryId) {
                    tabActive.value = i;
                    break;
                }
                if (tabsData.value[i].child_list) {
                    for (let k = 0; k < tabsData.value[i].child_list.length; k++) {
                        if (tabsData.value[i].child_list[k].category_id == categoryId) {
                            tabActive.value = i;
                            break;
                        }
                    }
                }
            }
        } else {
            categoryId = res.data[0].category_id;
        }
        loading.value = false;
    }).catch(() => {
        loading.value = false;
    });
}

// 一级菜单样式控制
const tabActive = ref<number>(0)

// 一级菜单点击事件
const firstLevelClick = (index: number, data: any) => {
    tabActive.value = index;
    categoryId = data.category_id;
    list.value = [];
    getMescroll().resetUpScroll();
}

// 搜索名字
const searchNameFn = () => {
    // getMescroll().resetUpScroll();
    // if(searchName.value)
    redirect({ url: '/addon/shop/pages/goods/list', param: { goods_name: encodeURIComponent(searchName.value) } })
}

//点击商品购物车按钮
const cartRef = ref()
const itemCart = (row: any, id: any) => {
    // 虚拟商品，并且需要核销，禁止加入购物车
    if (row.goods_type == 'virtual' && row.virtual_receive_type == 'verify') {
        return toLink(row.goodsSku.goods_id)
    }

    if (config.cart.event !== 'cart') {
        return toLink(row.goodsSku.goods_id)
    }

    if (!userInfo.value) {
        useLogin().setLoginBack({ url: '/addon/shop/pages/goods/category' })
        return false
    }

    if (row.goodsSku.sku_spec_format) {
        cartRef.value.open(row.goodsSku.sku_id)
    } else {
        //单规格添加购物车
        if (!row.goodsSku.stock || parseInt(row.goodsSku.num || 0) > parseInt(row.goodsSku.stock)) {
            uni.showToast({ title: '商品库存不足', icon: 'none' })
            return;
        }
        if (row.min_buy && row.min_buy > parseInt(row.stock)) {
            uni.showToast({ title: '商品库存小于起购数量', icon: 'none' })
            return;
        }
        animationAddCart(row, id)
    }
}

//点击购物车加号 添加数量
const addCartBtn = (item: any, row: any, id: string) => {
    if (parseInt(row.num) >= parseInt(row.stock)) {
        uni.showToast({ title: '商品库存不足', icon: 'none' })
        return;
    }

    // 起购
    let num = row.num;
    if (item.min_buy > 0 && item.min_buy > num) {
        num = item.min_buy;
    }

    /************** 限购-start *****************/
    let maxBuyNum = -1;
    // 限购 - 是否开启限购
    if (item.is_limit && item.max_buy) {
        let max_buy = 0;
        if (item.limit_type == 1) { //单次限购
            max_buy = item.max_buy;
        } else { // 单人限购
            let buyVal = item.max_buy - (item.has_buy || 0);
            max_buy = buyVal > 0 ? buyVal : 0;
        }

        if (max_buy > item.goodsSku.stock) {
            maxBuyNum = item.goodsSku.stock
        } else if (max_buy <= item.goodsSku.stock) {
            maxBuyNum = max_buy;
        }
    }

    if (item.is_limit && num >= item.max_buy) {
        let tips = `该商品单次限购${ item.max_buy }件`;
        if (item.limit_type != 1) { //单次限购
            tips = `该商品每人限购${ item.max_buy }件`;
        }
        uni.showToast({ title: tips, icon: 'none' })
        return false;
    }
    /************** 限购-end *****************/

    let obj = cloneDeep(item)
    obj.num = num;
    obj.id = row.id;
    animationAddCart(obj, id)
}

//点击购物车减号
const reduceCart = (data: any, row: any) => {
    if (cartRepeatFlag.value) return false
    cartRepeatFlag.value = true

    let reduceNum = 1;
    if (data.min_buy > 0 && data.min_buy == row.num) {
        reduceNum = data.min_buy;
    }

    cartStore.reduce({
        id: row.id,
        goods_id: row.goods_id,
        sku_id: row.sku_id,
        stock: row.stock,
        sale_price: row.sale_price,
        num: row.num
    }, reduceNum, () => {
        cartRepeatFlag.value = false
    })

}

//进入购物车
const toCart = () => {
    redirect({ url: '/addon/shop/pages/goods/cart' })
}

//强制绑定手机号
const bindMobileRef: any = ref(null)
const isBindMobile = ref(uni.getStorageSync('isBindMobile'))

/**
 * 结算
 */
const settlement = () => {

    // #ifdef H5
    // 绑定手机号
    // if(uni.getStorageSync('isBindMobile')){
    // 	bindMobileRef.value.open()
    // 	return false
    // }
    // #endif

    if (!totalNum.value) {
        uni.showToast({ title: '还没有选择商品', icon: 'none' })
        return
    }
    const cart_ids: any = []
    Object.values(cartList.value).forEach(item => {
        Object.keys(item).forEach(v => {
            if (v != 'totalNum' && v != 'totalMoney') cart_ids.push(item[v].id)
        })

    })
    if (cart_ids.length == 0) {
        return;
    }
    uni.setStorage({
        key: 'orderCreateData',
        data: { cart_ids },
        success() {
            redirect({ url: '/addon/shop/pages/order/payment' })
        }
    })
}

// 价格类型
const priceType = (data: any) => {
    let type = "";
	type = data.goodsSku.show_type
    return type;
}

// 商品价格
const goodsPrice = (data: any) => {
    let price = "0.00";
	price = data.goodsSku.show_price
    return price;
}
</script>

<style lang="scss" scoped>
.remove-border {
    &::after {
        border: none;
    }
}

.border-color {
    border-color: var(--primary-color) !important;
}

.text-color {
    color: var(--primary-color) !important;
}

.bg-color {
    background-color: var(--primary-color) !important;
}

.class-select {
    position: relative;
    font-weight: bold;

    &::before {
        content: "";
        position: absolute;
        bottom: 0;
        height: 6rpx;
        background-color: var(--primary-color);
        width: 90%;
        left: 50%;
        transform: translateX(-50%);
    }
}

.list-select {
    position: relative;
    margin-right: 28rpx;

    &::before {
        content: "";
        position: absolute;
        background-color: #999;
        width: 2rpx;
        height: 70%;
        top: 50%;
        right: -14rpx;
        transform: translatey(-50%);
    }
}

.transform-rotate {
    transform: rotate(180deg);
}

.text-color {
    color: var(--primary-color);
}

.bg-color {
    background-color: var(--primary-color);
}

// search input
.search-box {
    display: flex;
    align-items: center;
    padding: 0 30rpx;
}

.search-box .search-ipt {
    height: 58rpx;
    background-color: #F6F8F8;
    border-radius: 33rpx;
}

.search-box .search-ipt .input-placeholder {
    color: #A5A6A6;
}

.tabs-box {
    width: 168rpx;
    font-size: 26rpx;
}

.tabs-box .tab-item {
    min-height: 56rpx;
    padding: 20rpx 0;
    text-align: center;
    background-color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
}

.tabs-box .tab-item-active {
    position: relative;
    color: var(--primary-color);
    background-color: var(--temp-bg);

    &::before {
        display: inline-block;
        position: absolute;
        left: 0rpx;
        top: 50%;
        transform: translateY(-50%);
        content: '';
        width: 6rpx;
        height: 48rpx;
        background-color: var(--primary-color);
    }

    &::after {
        display: inline-block;
        position: absolute;
        left: 0rpx;
        top: 50%;
        transform: translateY(-50%);
        content: '';
        width: 6rpx;
        height: 48rpx;
        background-color: var(--primary-color);
    }
}

.scroll-height {
    height: 100%;
}

/*  #ifdef  H5  */
.cart {
    .noData1 {
        height: calc(100vh - 146rpx - 100rpx - 50px - constant(safe-area-inset-bottom));
        height: calc(100vh - 146rpx - 100rpx - 50px - env(safe-area-inset-bottom));
    }

    .noData2 {
        height: calc(100vh - 40rpx - 100rpx - 50px - constant(safe-area-inset-bottom));
        height: calc(100vh - 40rpx - 100rpx - 50px - env(safe-area-inset-bottom));
    }
}

.detail {
    .noData1 {
        height: calc(100vh - 146rpx - 50px - constant(safe-area-inset-bottom));
        height: calc(100vh - 146rpx - 50px - env(safe-area-inset-bottom));
    }

    .noData2 {
        height: calc(100vh - 40rpx - 50px - constant(safe-area-inset-bottom));
        height: calc(100vh - 40rpx - 50px - env(safe-area-inset-bottom));
    }
}

/*  #endif  */
/*  #ifndef  H5  */
.cart {
    .noData1 {
        height: calc(100vh - 146rpx - 100rpx - 100rpx - constant(safe-area-inset-bottom));
        height: calc(100vh - 146rpx - 100rpx - 100rpx - env(safe-area-inset-bottom));
    }

    .noData2 {
        height: calc(100vh - 40rpx - 100rpx - 100rpx - constant(safe-area-inset-bottom));
        height: calc(100vh - 40rpx - 100rpx - 100rpx - env(safe-area-inset-bottom));
    }
}

.detail {
    .noData1 {
        height: calc(100vh - 146rpx - 100rpx - constant(safe-area-inset-bottom));
        height: calc(100vh - 146rpx - 100rpx - env(safe-area-inset-bottom));
    }

    .noData2 {
        height: calc(100vh - 40rpx - 100rpx - constant(safe-area-inset-bottom));
        height: calc(100vh - 40rpx - 100rpx - env(safe-area-inset-bottom));
    }
}
/*  #endif  */

// 空页面
.mescroll-empty.empty-page.part {
    width: 542rpx;
    height: 542rpx;
    margin-top: 0;
    margin-left: 0;
    padding-top: 50rpx;

    .img {
        width: 160rpx !important;
        height: 120rpx !important;
    }
}
</style>

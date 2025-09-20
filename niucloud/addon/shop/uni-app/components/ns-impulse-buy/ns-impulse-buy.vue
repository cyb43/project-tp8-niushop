<template>
    <view v-if="list && list.length">
        <view class="container rounded-[var(--rounded-big)] px-[24rpx] py-[30rpx] mb-[20rpx]">
            <view class="flex items-center justify-between">
                <view class="flex items-center">
                    <text class="head-title text-[28rpx] font-bold pr-[10rpx]" v-if="impulseBuyConfigInfo.type == 'text'" :class="{'column-size !pr-[20rpx]': impulseBuyConfigInfo.sub_title}">{{ impulseBuyConfigInfo.title }}</text>
                    <image v-if="impulseBuyConfigInfo.type == 'image'" class="head-title h-[28rpx] w-[auto] pr-[10rpx]" :class="{'column-size !pr-[20rpx]': impulseBuyConfigInfo.sub_title}" :src="img(impulseBuyConfigInfo.title_image)" mode="heightFix" />
                    <text class="text-[24rpx] text-[#FF5900]" v-if="impulseBuyConfigInfo.sub_title">{{ impulseBuyConfigInfo.sub_title }}</text>
                    <text class="iconfont icon24gl-questionCircle text-[#666] ml-[4rpx] !text-[28rpx]" @click="impulseBuyShow = true" v-if="impulseBuyConfigInfo.rule"></text>
                </view>
                <view class="flex items-center" @click="changeAnotherFn" v-if="isShowChange">
                    <text class="text-[24rpx] text-[#666]">{{ t('changeIt') }}</text>
                    <text class="iconfont iconshuaxin1 ml-[4rpx] text-[#666] !text-[28rpx]"></text>
                </view>
            </view>

            <view class="space-y-4 mt-[30rpx]">
                <view v-for="(item, index) in list" :key="index" class="rounded-[20rpx] flex">
                    <u--image radius="var(--goods-rounded-big)" width="180rpx" height="180rpx" :src="img(item.goods.goods_cover)" model="aspectFill" @click="checkDetail(item)">
                        <template #error>
                            <image class="w-[180rpx] h-[180rpx] rounded-[var(--goods-rounded-big)] overflow-hidden" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill"/>
                        </template>
                    </u--image>
                    <view class="flex flex-1 w-0 flex-col justify-between ml-[20rpx] py-[6rpx]">
                        <view class="line-normal">
                            <view class="truncate text-[#303133] text-[28rpx] mb-[6rpx] leading-[32rpx]" @click="checkDetail(item)">{{ item.goods.goods_name }}</view>
                            <view class="truncate text-[#666] text-[24rpx] mb-[4rpx] leading-[32rpx]">{{ item.sku.sku_name }}</view>
                            <view class="text-[#DA7E2D] break-words text-[24rpx] leading-[32rpx]" v-if="Number(item.limit_buy) > 0">{{ `${t('limitPlaceholderOne')}${item.limit_buy}${t('limitPlaceholderTwo')}￥${goodsPrice(item)}${t('limitPlaceholderThree')}`  }}</view>
                        </view>
                        <view class="flex items-center justify-between mt-2">
                            <view class="text-[var(--price-text-color)] flex items-baseline  price-font">
                                <text class="text-[24rpx] font-500 mr-[4rpx]">￥</text>
                                <text class="text-[40rpx] font-500">{{ parseFloat(item.impulse_buy_price).toFixed(2).split('.')[0] }}</text>
                                <text class="text-[24rpx] font-500">.{{ parseFloat(item.impulse_buy_price).toFixed(2).split('.')[1] }}</text>
                            </view>
                            <view class="h-[50rpx] flex items-center">
                                <button v-if="!item.num" @click="handleBuy(item)" class="!rounded-[30rpx] !bg-transparent !text-[var(--primary-color)] text-[22rpx] border-[2rpx] border-solid border-[var(--primary-color)] min-w-[130rpx] h-[50rpx] flex items-center" :disabled="props.calculateLoading" :class="{'opacity-80': props.calculateLoading}" hover-class="none">{{ t('buyWithOneClick') }}</button>
                                <u-number-box v-else v-model="item.num" :min="0" :max="numLimit(item).max" integer :step="1" input-width="68rpx" :disabled="props.calculateLoading" input-height="52rpx" button-size="52rpx" disabledInput @change="numChange($event, index)">
                                    <template #minus>
                                        <view class="relative w-[26rpx] h-[26rpx]" @click="reduceNumChange(item)">
                                            <text :class="{'text-[#303133]': item.num !== numLimit(item).min, 'text-[var(--text-color-light9)]': props.calculateLoading }" class="text-[24rpx] absolute flex items-center justify-center -left-[20rpx] -bottom-[20rpx] -right-[20rpx] -top-[20rpx] font-500 nc-iconfont nc-icon-jianV6xx"></text>
                                        </view>
                                    </template>
                                    <template #input>
                                        <input class="text-[#303133] text-[28rpx] mx-[14rpx] w-[80rpx] h-[44rpx] bg-[var(--temp-bg)] leading-[44rpx] text-center rounded-[6rpx]" type="number" @blur="goodsSkuBlurFn($event, index)" @click.stop v-model="item.num" :disabled="props.calculateLoading" />
                                    </template>
                                    <template #plus>
                                        <view class="relative w-[26rpx] h-[26rpx]" @click="addNumChange(item)">
                                            <text :class="{ 'text-[var(--text-color-light9)]': ((item.num === numLimit(item).max) || props.calculateLoading), ' text-[#303133]': item.num !== numLimit(item).max }" class="text-[24rpx] absolute flex items-center justify-center -left-[20rpx] -bottom-[20rpx] -right-[20rpx] -top-[20rpx] font-500 nc-iconfont nc-icon-jiahaoV6xx"></text>
                                        </view>
                                    </template>
                                </u-number-box>
                            </view>
                        </view>
                    </view>
                </view>
            </view>

            <view @touchmove.prevent.stop>
                <u-popup :show="impulseBuyShow" @close="impulseBuyShow = false" mode="center" round="var(--rounded-big)" :safeAreaInsetBottom="false">
                    <view class="w-[570rpx] px-[32rpx] popup-common center">
                        <view class="title">{{ t('activityInstructions') }}</view>
                        <scroll-view :scroll-y="true" class="px-[10rpx] box-border h-[350rpx]">
                            <u-parse :content="impulseBuyConfigInfo.rule" :tagStyle="{img: 'vertical-align: top;',p:'overflow: hidden;word-break:break-word;',span:'text-wrap-mode:wrap !important;' }"></u-parse>
                        </scroll-view>
                        <view class="btn-wrap !pt-[40rpx]">
                            <button class="primary-btn-bg w-[480rpx] h-[70rpx] text-[26rpx] leading-[70rpx] rounded-[35rpx] !text-[#fff] font-500" @click="impulseBuyShow = false">{{ t('know') }}</button>
                        </view>
                    </view>
                </u-popup>
            </view>

            <!-- 商品详情 -->
            <goods-detail ref="goodsDetailRef"></goods-detail>
        </view>
    </view>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted  } from 'vue'
import { img } from '@/utils/common'
import { t } from '@/locale'
import { getShopImpulseBuyList, getShopImpulseBuyConfig } from '@/addon/shop/api/impulse_buy'
import goodsDetail from '@/addon/shop/pages/order/components/goods-detail/goods-detail.vue'

const props = defineProps(['data', 'orderKey', 'deliveryType', 'calculateLoading']);
const impulseBuyShow = ref(false)
const impulseBuyConfigInfo = ref({})
const isShowChange = ref(true)
const searchParams = reactive({
    is_change: 0,
    order_key: '',
    delivery_type: '',
    buy_sku_id: []
})
const goodsDetailRef = ref()

const list = ref([])
const emits = defineEmits(['confirm'])
const getConfigInfo = ()=>{
    getShopImpulseBuyConfig().then((res: any) => {
        impulseBuyConfigInfo.value = res.data
    })
}
getConfigInfo()

// 获取商品列表
const getGoodsList = (params: any = {}) => {
    const data = Object.assign(searchParams,params)
    getShopImpulseBuyList(data).then((res: any) => {
        list.value = res.data.list
        isShowChange.value = res.data.is_show_change
        if(list.value){
            list.value.forEach((item: any) =>{
                item.num = 0
            })
        }
    })
}

// 查看详情
const checkDetail = (params: any) => {
    const obj = {impulse_buy_goods_id: params.impulse_buy_goods_id, num: params.num }
    goodsDetailRef.value.open(obj)
}

onMounted(() => {
    searchParams.order_key = props.orderKey
    searchParams.delivery_type = props.deliveryType
    getGoodsList()
})

const addNumChange = (data: any) => {
    if (data.num >= data.sku.stock) {
        uni.showToast({ title: t('stockNotEnough'), icon: 'none' });
        return;
    }
}

const reduceNumChange = (data: any) => {
    if (data.num == 1) {
        data.num = 0
        addCartNumFn(data)
    }
}
const numLimit = (data: any) => {
    let obj = {
        min: 1,
        max: data.sku.stock || 1
    };
    return obj;
}

// 处理购买按钮点击事件
const handleBuy = (params: any) => {
    params.num = numLimit(params).min || 1
    addCartNumFn(params)
}

const numChange = (event: any, index: any) => {
    const data = list.value[index]
    addCartNumFn(data)
}
const goodsSkuBlurFn = (event, index) => {
    setTimeout(() => {
        const data: any = list.value[index]
        data.num = parseInt(data.num)
        if(!data.num){
            data.num = 0
        }else if (data.num <= numLimit(data).min) {
            data.num = numLimit(data).min;
        }
        if (data.num >= numLimit(data).max) {
            data.num = numLimit(data).max;
        }
        addCartNumFn(data)
    }, 0)
}

// 加购数量
const addCartNumFn = (data: any) => {
    if (!data || (data && Object.keys(data).length == 0) || !data.goods_id || !data.sku_id) return;
    if (data.num > parseInt(data.stock)) {
        uni.showToast({ title: t('stockNotEnough'), icon: 'none' })
        return;
    }
    uni.$u.debounce((event: any) => {
        const obj = {
            sku_id: data.sku_id,
            num: data.num,
            impulse_buy_goods_id: data.impulse_buy_goods_id
        }
        emits('confirm', obj)
    }, 500)
}

// 换一换
const changeAnotherFn = () => {
    getGoodsList({is_change: 1})
    emits('confirm', '')
}

const goodsPrice = (params: any) => {
    let price = "0.00";
    if (Object.keys(params).length && Object.keys(params.goods).length && params.goods.member_discount && params.member_price != params.sku.price) {
        // 会员价
        price = params.sku.member_price ? params.sku.member_price : params.sku.price;
    } else {
        price = params.sku.price
    }
    return price;
}

defineExpose({

})
</script>

<style lang="scss" scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
.container{
    background:
        linear-gradient(to bottom,
            rgba(255, 255, 255, 0) 0%,
            rgba(255, 255, 255, 0.8) 15%,
            rgba(255, 255, 255, 1) 20%,
            rgba(255, 255, 255, 1) 100%
        ),
        linear-gradient(91deg, #FFE5E5 50%, #FFF5E4 100%);
}
.head-title{
    position: relative;
    &.column-size::after{
        content: '';
        position: absolute;
        height: 20rpx;
        width: 2rpx;
        top: 50%;
        transform: translateY(-50%);
        background-color: #D9D9D9;
        right: 8rpx;
    }
}
</style>

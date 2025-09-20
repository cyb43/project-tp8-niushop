<template>
    <view @touchmove.prevent.stop>
        <u-popup class="popup-type" :show="goodsSkuPop" @close="closeFn" mode="bottom">
            <view class="py-[32rpx] relative" v-if="goodsDetail.detail" @touchmove.prevent.stop>
                <view class="flex px-[32rpx] mb-[40rpx]">
                    <view class="w-[180rpx] h-[180rpx]">
                        <u--image width="180rpx" height="180rpx" :radius="'var(--goods-rounded-big)'" :src="img(goodsDetail.detail.sku_image)" @click="imgListPreview(goodsDetail.detail.sku_image)" model="aspectFill">
                            <template #error>
                                <image class="w-[180rpx] h-[180rpx] rounded-[var(--goods-rounded-big)] overflow-hidden" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill"/>
                            </template>
                        </u--image>
                    </view>
                    <view class="flex flex-1 flex-col justify-between ml-[20rpx] py-[10rpx]">
                        <view class="w-[100%]">
                            <view class="text-[var(--price-text-color)] flex items-baseline">
                                <text class="price-font" v-if="goodsDetail.point">
                                    <text class="text-[44rpx]">{{ goodsDetail.point }}</text>
                                    <text class="text-[38rpx]">{{ t('point') }}</text>
                                </text>
                                <text class="text-[38rpx]" v-if="goodsDetail.point&&parseFloat(goodsDetail.price)">+
                                </text>
                                <template v-if="goodsDetail.point&&parseFloat(goodsDetail.price)">
                                    <text class="text-[44rpx] price-font">{{ parseFloat(goodsDetail.price).toFixed(2) }}</text>
                                    <text class="text-[38rpx] price-font">元</text>
                                </template>
                                <template v-if="!goodsDetail.point && parseFloat(goodsDetail.price)">
                                    <text class="text-[26rpx] price-font">￥</text>
                                    <text class="text-[44rpx] price-font">{{ parseFloat(goodsDetail.price).toFixed(2).split('.')[0] }}</text>
                                    <text class="text-[26rpx] mr-[6rpx] price-font">.{{ parseFloat(goodsDetail.price).toFixed(2).split('.')[1] }}  </text>
                                </template>

                            </view>
                            <view class="text-[26rpx] leading-[32rpx] text-[var(--text-color-light6)] mt-[10rpx]">库存{{ goodsDetail.detail.stock }}{{ goodsDetail.goods.unit }}</view>
                        </view>
                        <view class="text-[26rpx] leading-[30rpx] text-[var(--text-color-light6)] w-[100%] max-h-[60rpx] multi-hidden"
                            v-if="goodsDetail.goodsSpec && goodsDetail.goodsSpec.length">已选规格：{{ goodsDetail.detail.sku_spec_format }}</view>
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
                    <view class="flex justify-between items-center mt-[8rpx]">
                        <view class="text-[28rpx]">购买数量</view>
                        <text v-if="goodsDetail.detail.limit_num > 0" class="ml-[20rpx] mr-[auto] text-[24rpx] text-[var(--primary-color)]">(限购{{ goodsDetail.detail.limit_num }}{{ goodsDetail.goods.unit }})</text>
                        <u-number-box :min="1"
                                      :max="parseInt(goodsDetail.detail.limit_num)<goodsDetail.stock?parseInt(goodsDetail.detail.limit_num):goodsDetail.stock"
                                      integer :step="1" input-width="68rpx" v-model="buyNum" input-height="52rpx">
                            <template #minus>
                                <text class="text-[30rpx] nc-iconfont nc-icon-jianV6xx font-500" :class="{ '!text-[var(--text-color-light9)]': buyNum <= 1 }"></text>
                            </template>
                            <template #input>
                                <input class="text-[#303133] text-[28rpx] mx-[10rpx] w-[80rpx] h-[44rpx] bg-[var(--temp-bg)] leading-[44rpx] text-center rounded-[6rpx]"
                                    type="number" @input="goodsSkuInputFn" @blur="goodsSkuBlurFn" v-model="buyNum" />
                            </template>
                            <template #plus>
                                <text class="text-[30rpx] nc-iconfont nc-icon-jiahaoV6xx font-500" :class="{ '!text-[var(--text-color-light9)]': buyNum >= goodsDetail.stock || buyNum ==parseInt(goodsDetail.detail.limit_num)}"></text>
                            </template>
                        </u-number-box>
                    </view>
                </scroll-view>
                <view class="px-[20rpx]">
                    
                    <template v-if="goodsDetail.detail.stock > 0">
                        <!-- #ifdef H5 -->
                        <button v-if="isPointGoods" hover-class="none"
                                class="!h-[80rpx] primary-btn-bg leading-[80rpx] text-[26rpx] rounded-[50rpx] font-500"
                                type="primary" @click="confirm">确定</button>
                        <button v-else hover-class="none"
                                class="!h-[80rpx] leading-[80rpx] text-[26rpx] rounded-[50rpx] font-500"
                                disabled>该规格未参与积分商品</button>
                        <!-- #endif -->

                        <!-- #ifdef MP-WEIXIN -->
                        <!--<button v-if="isBindMobile && userInfo && !userInfo.mobile" hover-class="none" class="!h-[80rpx] primary-btn-bg leading-[80rpx] text-[26rpx] rounded-[50rpx] font-500" type="primary" open-type="getPhoneNumber" @getphonenumber="memberStore.bindMobile">确定</button>-->
                        <!--<button v-else hover-class="none" class="!h-[80rpx] primary-btn-bg leading-[80rpx] text-[26rpx] rounded-[50rpx] font-500" type="primary" @click="confirm">确定</button>-->
                        <button hover-class="none"  v-if="isPointGoods"
                                class="!h-[80rpx] primary-btn-bg leading-[80rpx] text-[26rpx] rounded-[50rpx] font-500"
                                type="primary" @click="confirm">确定</button>
                        <button v-else hover-class="none"
                                class="!h-[80rpx] leading-[80rpx] text-[26rpx] rounded-[50rpx] font-500"
                                disabled>该规格未参与积分商品</button>
                        <!-- #endif -->
                    </template>

                    <button hover-class="none" v-else class="!h-[80rpx] leading-[80rpx] text-[26rpx] text-[#fff] bg-[#ccc] rounded-[50rpx] font-500">已售罄</button>
                </view>
            </view>
        </u-popup>
        <!-- 强制绑定手机号 -->
        <bind-mobile ref="bindMobileRef" />
    </view>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue';
import { img, redirect, getToken } from '@/utils/common'
import bindMobile from '@/components/bind-mobile/bind-mobile.vue';
import { useLogin } from '@/hooks/useLogin'
import useMemberStore from '@/stores/member'
import { t } from '@/locale';
import { cloneDeep } from 'lodash-es'

const props = defineProps(['goodsDetail']);
const goodsSkuPop = ref(false);
const callback: any = ref(null);
const currSpec: any = ref({
    skuId: "",
    name: []
})
const openType = ref("");
const buyNum = ref(1)

// 商品价格
const goodsPrice = computed(() => {
    let price = "0.00";
    if (Object.keys(goodsDetail.value).length && Object.keys(goodsDetail.value.goods).length && goodsDetail.value.goods.member_discount && getToken() && goodsDetail.value.member_price != goodsDetail.value.price) {
        // 会员价
        price = goodsDetail.value.member_price ? goodsDetail.value.member_price : goodsDetail.value.price;
    } else {
        price = goodsDetail.value.price
    }
    return price;
})

const goodsSkuInputFn = () => {
    setTimeout(() => {
        if (!buyNum.value || buyNum.value <= 0) {
            buyNum.value = 1;
        }
        if (buyNum.value >= Number(goodsDetail.value.detail.limit_num)) {
            buyNum.value = goodsDetail.value.detail.limit_num;
        }
    }, 0)
}

const goodsSkuBlurFn = () => {
    setTimeout(() => {
        if (!buyNum.value || buyNum.value <= 0) {
            buyNum.value = 1;
        }
        if (buyNum.value >= Number(goodsDetail.value.detail.limit_num)) {
            buyNum.value = goodsDetail.value.detail.limit_num;
        }
    }, 0)
}

// 会员信息
const memberStore = useMemberStore()
const userInfo = computed(() => memberStore.info)

const open = (type = "", fn = "") => {
    openType.value = type;
    goodsSkuPop.value = true;
    callback.value = fn;
}

const closeFn = () => {
    goodsSkuPop.value = false
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
                idItem.sku_image = idItem.sku_image ? idItem.sku_image : data.goods.goods_cover
                if (idItem.sku_id == currSpec.value.skuId) {
                    data.detail = idItem;
                    data.is_join_exchange = idItem.is_join_exchange;
                    
                }
            })
        }
        if(currSpec.value.name) detectionGoodsIsDisable(data, currSpec.value.name)
    }
    return data;
})

const change = (data: any, index: any) => {
    currSpec.value.name[index] = data.name;
    buyNum.value = 1
    getSkuId();
    detectionGoodsIsDisable(goodsDetail.value, currSpec.value.name)
}

const isPointGoods = ref(true)
const detectionGoodsIsDisable = (data: any, currSkuName: any)=>{
    const skuNameArr = []
    data.skuList.forEach((item, index) => {
        skuNameArr.push(item.sku_name)
    })
    isPointGoods.value = true
    if(currSkuName && currSkuName.length && !skuNameArr.includes(currSkuName.join(' '))){
        isPointGoods.value = false 
    }
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

//强制绑定手机号
const bindMobileRef: any = ref(null)
const isBindMobile = ref(uni.getStorageSync('isBindMobile'))

const confirm = () => {
    // #ifdef H5
    // 绑定手机号
    if (!userInfo.value && uni.getStorageSync('isBindMobile')) {
        uni.setStorage({
            key: 'loginBack', data: {
                url: '/addon/shop/pages/point/detail',
                param: {
                    id: goodsDetail.value.exchange_id
                }
            }
        })
        bindMobileRef.value.open()
        return false
    }
    // #endif

    // 检测是否登录
    if (!userInfo.value) {
        useLogin().setLoginBack({ url: '/addon/shop/pages/point/detail', param: { id: goodsDetail.value.exchange_id } })
        return false
    }

    // 立即购买
    const data = {
        sku_id: goodsDetail.value.sku_id,
        num: buyNum.value
    };

    uni.setStorage({
        key: 'orderCreateData',
        data: {
            sku_data: [
                data
            ]
        },
        success: () => {
            const url = goodsDetail.value.is_join_exchange ? '/addon/shop/pages/point/payment' : '/addon/shop/pages/order/payment'
            redirect({ url })
        }
    });

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
.popup-type {
    :deep(.u-popup__content) {
        border-top-left-radius: 16rpx;
        border-top-right-radius: 16rpx;
        overflow: hidden;
    }
}

// 防止覆盖住图片放大
.u-popup :deep(.u-transition) {
    z-index: 999 !important;
}

::v-deep .u-number-box .u-number-box__slot {
    display: flex;
    align-items: center;
}
</style>

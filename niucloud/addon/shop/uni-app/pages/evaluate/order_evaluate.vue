<template>
    <view class="bg-[var(--page-bg-color)] min-h-screen" :style="themeColor()">
        <view class="px-[var(--sidebar-m)] py-[var(--top-m)]">
            <template v-for="(item, index)  in info" :key="index">
                <view class="card-template mb-[var(--top-m)]">
                    <view class="bg-[var(--temp-bg)] p-[20rpx] rounded-[var(--rounded-mid)] flex">
                        <u--image radius="var(--goods-rounded-mid)" width="150rpx" height="150rpx" :src="img(item.goods_image_thumb_small ? item.goods_image_thumb_small : '')" model="aspectFill">
                            <template #error>
                                <u-icon name="photo" color="#999" size="50"></u-icon>
                            </template>
                        </u--image>
                        <view class="flex-1 flex flex-wrap ml-[20rpx] my-[4rpx]">
                            <view>
                                <view class="text-[26rpx] leading-[40rpx] max-w-[450rpx] truncate">{{ item.goods_name }}</view>
                                <view class="max-w-[450rpx] mt-[14rpx] truncate text-[22rpx] text-[var(--text-color-light9)] leading-[28rpx]" v-if="item.sku_name">{{ item.sku_name }}</view>
                            </view>
                            <view class="mt-auto w-full flex justify-between items-center">
                                <view class="flex items-baseline  price-font">
                                    <text class="text-[24rpx] font-500">￥</text>
                                    <text class="text-[40rpx] font-500">{{ parseFloat(item.price).toFixed(2).split('.')[0] }}</text>
                                    <text class="text-[24rpx] font-500">.{{ parseFloat(item.price).toFixed(2).split('.')[1] }}</text>
                                </view>
                                <text class="font-400 text-[28rpx] text-[#333]">x{{ item.num }}</text>
                            </view>
                        </view>
                    </view>
                    <view class="flex items-center mt-[30rpx]">
                        <u-rate :count="5" v-model="form[index].scores" active-color="var(--primary-color)" :size="'36rpx'" gutter="1"></u-rate>
                        <text class="ml-[16rpx] text-[28rpx] pt-[2rpx] text-[var(--primary-color)]">{{ form[index].scores === 1 ? '差评' : form[index].scores === 2 || form[index].scores === 3 ? '中评' : '好评' }}</text>
                    </view>
                    <textarea class="!text-[26rpx] px-[2rpx] mt-[16rpx] w-[100%] !text-[#333] !leading-[1.5]"
                        v-model.trim="form[index].content" placeholder="请在此处输入你的评价"
                        placeholderClass="text-[26rpx] text-[var(--text-color-light9)]" maxlength="200" />
                    <!-- <view class="text-right text-[24rpx] text-[var(--text-color-light6)]">{{ form[index].content.length >= 200 ? 200 : form[index].content.length }}/200</view> -->
                    <upload-img class="mt-[20rpx]" v-model="form[index].images" :max-count="9" :multiple="true" />
                </view>
            </template>
        </view>
        <u-tabbar :fixed="true" :placeholder="true" :safeAreaInsetBottom="true" zIndex="9999">
            <view class="flex items-center pl-[30rpx] pr-[20rpx] box-border justify-between w-[100%]">
                <view class="flex items-center" @click="selectCheck">
                    <text class="iconfont text-color text-[30rpx] mr-[12rpx] text-[var(--text-color-light9)]"
                          :class="{'iconxuanze1 text-[var(--primary-color)]' : is_anonymous === '1' ,'nc-iconfont nc-icon-yuanquanV6xx':is_anonymous !== '1'}"></text>
                    <text class="text-[28rpx] leading-[34rpx]"
                          :class="{'text-[var(--primary-color)]' :is_anonymous === '1', 'text-[var(--text-color-light6)]':is_anonymous !== '1'}">匿名</text>
                </view>
                <button class="!w-[240rpx] !h-[70rpx] text-[26rpx] !m-0 flex-center rounded-full text-white primary-btn-bg remove-border font-500" hover-class="none" @click="submit">提交</button>
            </view>
        </u-tabbar>
        <loading-page :loading="loading"></loading-page>
        <!-- #ifdef MP-WEIXIN -->
        <!-- 小程序隐私协议 -->
        <wx-privacy-popup ref="wxPrivacyPopupRef"></wx-privacy-popup>
        <!-- #endif -->
    </view>
</template>
<script lang="ts" setup>
import { ref, nextTick } from 'vue';
import { getShopOrderDetail } from '@/addon/shop/api/order';
import { setOrderEvaluate } from '@/addon/shop/api/evaluate';
import { onLoad } from '@dcloudio/uni-app';
import { img, redirect, goback } from '@/utils/common';
import uploadImg from '@/addon/shop/pages/evaluate/components/upload-img'

const info = ref<Array<any>>([])
const form = ref<Array<any>>([])
const is_anonymous = ref('2')
const loading = ref(false)
const order_id = ref('')

const wxPrivacyPopupRef: any = ref(null)

onLoad((option: any) => {
    if (option.order_id) {
        order_id.value = option.order_id
        getShopOrderDetailFn(order_id.value)

        // #ifdef MP
        nextTick(() => {
            if (wxPrivacyPopupRef.value) wxPrivacyPopupRef.value.proactive();
        })
        // #endif
    } else {
        let parameter = {
            url: '/addon/shop/pages/order/list',
            param: {
                status: 5
            },
            title: '缺少订单id'
        };
        goback(parameter)
    }
})

const getShopOrderDetailFn = (id: any) => {
    loading.value = true
    getShopOrderDetail(id).then((res: any) => {
        if (res.data.is_evaluate) {
            toLink(order_id.value)
            return false
        }
        res.data.order_goods.forEach((el: any) => {
            if (el.status == 1) {
                info.value.push(el);
                form.value.push({
                    order_id: el.order_id,
                    order_goods_id: el.order_goods_id,
                    goods_id: el.goods_id,
                    content: '',
                    images: [],
                    scores: 5
                })
            }
        })
        loading.value = false
    }).catch(() => {
        loading.value = false
    })
}

const selectCheck = () => {
    is_anonymous.value = is_anonymous.value === '1' ? '2' : '1'
}

const submit = () => {
    if (form.value.some(el => el.content == '')) {
        uni.showToast({ title: '请输入你的评价', icon: 'none' })
        return false
    }
    for (let i = 0; i < form.value.length; i++) {
        let item = form.value[i];
        if (item.content.length > 200) {
            item.content = item.content.substr(0, 200);
        }
    }
    form.value.forEach(v => v.is_anonymous = is_anonymous.value)
    loading.value = true
    setOrderEvaluate({ evaluate_array: form.value }).then(res => {
        loading.value = false
        toLink(order_id.value)
    }).catch(() => {
        loading.value = false
    })
}

const toLink = (order_id: any) => {
    redirect({ url: '/addon/shop/pages/evaluate/order_evaluate_view', param: { order_id }, mode: 'redirectTo' })
}
</script>
<style lang="scss">
.remove-border {
    &::after {
        border: none;
    }
}
</style>

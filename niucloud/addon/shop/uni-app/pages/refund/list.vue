<template>
    <view class="bg-[var(--page-bg-color)] min-h-screen overflow-hidden" :style="themeColor()">

        <mescroll-body ref="mescrollRef" @init="mescrollInit" :down="{ use: false }" @up="getRefundListFn">
            <view class="sidebar-margin pt-[var(--top-m)]" v-if="list.length">
                <view class="mb-[var(--top-m)] card-template" v-for="(item,index) in list" :key="index">
                    <view @click="toLink(item)">
                        <view class="flex justify-between items-center">
                            <view class="text-[#303133] text-[26rpx] font-400 leading-[34rpx]">
                                <text>{{ t('refundNo') }}:</text>
                                <text class="ml-[10rpx]">{{ item.order_refund_no }}</text>
                                <text class="text-[#303133] text-[24rpx] font-400 nc-iconfont nc-icon-fuzhiV6xx1 ml-[11rpx]"
                                    @click.stop="copy(item.order_refund_no)"></text>
                            </view>
                            <view class="text-[26rpx] leading-[34rpx] !text-[var(--primary-color)]">{{ item.refund_type_name }}</view>
                        </view>
                        <view class="flex mt-[20rpx]">
                            <u--image width="150rpx" height="150rpx" :radius="'var(--goods-rounded-big)'" :src="img(item.order_goods.goods_image_thumb_small ? item.order_goods.goods_image_thumb_small : '')" model="aspectFill">
                                <template #error>
                                    <image class="w-[150rpx] h-[150rpx] rounded-[var(--goods-rounded-big)] overflow-hidden" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill"/>
                                </template>
                            </u--image>
                            <view class="ml-[20rpx] flex flex-1 flex-col box-border">
                                <view class="flex justify-between items-baseline">
                                    <view class="text-[28rpx] leading-[40rpx] text-[#303133] max-w-[322rpx]  truncate font-400">{{ item.order_goods.goods_name }}</view>
                                    <view class="text-right leading-[42rpx] ml-[10rpx]">
                                        <text class="text-[22rpx] price-font">￥</text>
                                        <text class="text-[36rpx] font-500 price-font">{{ parseFloat(item.order_goods.price).toFixed(2).split('.')[0] }}</text>
                                        <text class="text-[22rpx] font-500 price-font">.{{ parseFloat(item.order_goods.price).toFixed(2).split('.')[1] }}</text>
                                    </view>
                                </view>
                                <view class="flex justify-between items-baseline mt-[14rpx]">
                                    <view class="flex flex-col">
                                        <view class="text-[26rpx] truncate text-[var(--text-color-light6)] leading-[36rpx] max-w-[369rpx] mb-[10rpx]" v-if="item.order_goods.sku_name">{{ item.order_goods.sku_name }}</view>
                                        <view class="text-[26rpx] font-400 leading-[36rpx] text-[var(--text-color-light6)]">退款状态：{{ item.status_name }}</view>
                                    </view>
                                    <text class="text-right text-[26rpx] font-400 w-[90rpx] leading-[36rpx]">x{{ item.order_goods.num }}</text>
                                </view>
                            </view>
                        </view>
                    </view>
                    <view class="flex justify-end items-baseline mt-[20rpx]">
                        <view class="flex items-center">
                            <text class="text-[22rpx] leading-[30rpx] font-400 mr-[4rpx]">{{ t('refundMoney') }}：</text>
                            <view class="price-font leading-[42rpx] text-[var(--price-text-color)]">
                                <text class="text-[22rpx]">￥</text>
                                <template v-if="item.status == 8">
                                    <text class="text-[36rpx] font-500">{{ parseFloat(item.money).toFixed(2).split('.')[0] }}</text>
                                    <text class="text-[22rpx] font-500">.{{ parseFloat(item.money).toFixed(2).split('.')[1] }}</text>
                                </template>
                                <template v-else>
                                    <text class="text-[36rpx] font-500">{{ parseFloat(item.apply_money).toFixed(2).split('.')[0] }}</text>
                                    <text class="text-[22rpx] font-500">.{{ parseFloat(item.apply_money).toFixed(2).split('.')[1] }}</text>
                                </template>
                            </view>
                        </view>
                    </view>
                    <view class="mt-[20rpx] flex flex-wrap justify-end" v-if="['6','7','8','-1','3','2','5','-3'].indexOf(item.status) == -1">
                        <view class="text-[24rpx] text-[var(--text-color-light6)] font-500 h-[56rpx] leading-[52rpx] box-border px-[23rpx] border-[2rpx] border-solid border-[var(--text-color-light9)] rounded-full ml-[20rpx]"
                            @click.stop="refundBtnFn(item,'cancel')"
                            v-if="['6','7','8','-1','-3'].indexOf(item.status) == -1">{{ t('refundApply') }}</view>
                        <view v-if="['3'].indexOf(item.status) != -1"
                              class="text-[24rpx] font-500 text-[var(--text-color-light6)] h-[56rpx] box-border leading-[52rpx] px-[23rpx] border-[2rpx] border-solid border-[var(--text-color-light9)] rounded-full ml-[20rpx]"
                              @click.stop="refundBtnFn(item,'edit')">编辑退款信息</view>
                        <view v-if="['2'].indexOf(item.status) != -1" class=" text-[24rpx] font-500 text-[var(--text-color-light6)] h-[56rpx] box-border leading-[52rpx] px-[23rpx] border-[2rpx] border-solid border-[var(--text-color-light9)] rounded-full ml-[20rpx]"
                              @click.stop="refundBtnFn(item,'logistics')">填写发货物流</view>
                        <view v-if="['5'].indexOf(item.status) != -1" class="text-[24rpx] font-500 text-[var(--text-color-light6)] h-[56rpx] box-border  leading-[52rpx] px-[23rpx] border-[2rpx] border-solid border-[var(--text-color-light9)] rounded-full ml-[20rpx]"
                              @click.stop="refundBtnFn(item,'editLogistics')">编辑发货物流</view>
                    </view>
                </view>
            </view>
            <mescroll-empty :option="{tip : '暂无订单'}" v-if="!list.length && loading"></mescroll-empty>

        </mescroll-body>
        <u-modal :show="cancelRefundShow" confirmColor="var(--primary-color)" :content="t('cancelRefundContent')"
                 :showCancelButton="true" :closeOnClickOverlay="true" @cancel="refundCancel"
                 @confirm="refundConfirm"></u-modal>
        <!-- #ifdef MP-WEIXIN -->
        <!-- 小程序隐私协议 -->
        <wx-privacy-popup ref="wxPrivacyPopupRef"></wx-privacy-popup>
        <!-- #endif -->
    </view>
</template>

<script setup lang="ts">
import { ref, nextTick } from 'vue';
import { t } from '@/locale'
import { img, redirect, copy } from '@/utils/common'
import { getRefundList, closeRefund } from '@/addon/shop/api/refund';
import MescrollBody from '@/components/mescroll/mescroll-body/mescroll-body.vue';
import MescrollEmpty from '@/components/mescroll/mescroll-empty/mescroll-empty.vue';
import useMescroll from '@/components/mescroll/hooks/useMescroll.js';
import { onLoad, onPageScroll, onReachBottom } from '@dcloudio/uni-app';

const { mescrollInit, downCallback, getMescroll } = useMescroll(onPageScroll, onReachBottom);
const list = ref<Array<Object>>([]);
const loading = ref<boolean>(false);
const cancelRefundShow = ref(false);

const wxPrivacyPopupRef: any = ref(null)

onLoad(() => {
    // #ifdef MP
    nextTick(() => {
        if (wxPrivacyPopupRef.value) wxPrivacyPopupRef.value.proactive();
    })
    // #endif
})

const getRefundListFn = (mescroll) => {
    loading.value = false;
    let data: object = {
        page: mescroll.num,
        limit: mescroll.size
    };

    getRefundList(data).then((res) => {
        let newArr = (res.data.data as Array<Object>);
        //设置列表数据
        if (mescroll.num == 1) {
            list.value = []; //如果是第一页需手动制空列表
        }
        list.value = list.value.concat(newArr);
        mescroll.endSuccess(newArr.length);
        loading.value = true;
    }).catch(() => {
        loading.value = true;
        mescroll.endErr(); // 请求失败, 结束加载
    })
}

const toLink = (data) => {
    redirect({ url: '/addon/shop/pages/refund/detail', param: { order_refund_no: data.order_refund_no } })
}

let currRefundOn = "";
const refundBtnFn = (data, type) => {
    if (type == 'cancel') {
        currRefundOn = data.order_refund_no;
        cancelRefundShow.value = true;
    } else if (type == 'edit') {
        redirect({ url: '/addon/shop/pages/refund/edit_apply', param: { order_refund_no: data.order_refund_no } })
    } else if (type == 'logistics') {
        redirect({
            url: '/addon/shop/pages/refund/detail',
            param: { order_refund_no: data.order_refund_no, type: 'logistics' }
        })
    } else if (type == 'editLogistics') {
        redirect({
            url: '/addon/shop/pages/refund/detail',
            param: { order_refund_no: data.order_refund_no, type: 'logistics', is_edit_delivery: true }
        })
    }
}

const refundConfirm = () => {
    closeRefund(currRefundOn).then((res) => {
        cancelRefundShow.value = false;
        getMescroll().resetUpScroll();
    }).catch(() => {
        cancelRefundShow.value = false;
    })
}

const refundCancel = () => {
    cancelRefundShow.value = false;
}

</script>
<style lang="scss" scoped>
.text-item {
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
</style>

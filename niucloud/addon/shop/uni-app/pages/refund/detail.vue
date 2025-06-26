<template>
    <view :style="themeColor()">
        <view class="bg-[var(--page-bg-color)] min-h-screen overflow-hidden" v-if="!loading">
            <view class="pb-[200rpx]" v-if="type != 'logistics'">
                <view class="bg-linear">
                    <!-- #ifdef MP-WEIXIN -->
                    <top-tabbar :data="topTabbarData" :scrollBool="topTabarObj.getScrollBool()" />
                    <!-- #endif -->
                    <view v-if="detail.status_name"
                          class="flex justify-between items-center pl-[40rpx] pr-[50rpx]  h-[280rpx] box-border pb-[90rpx]">
                        <view class="text-[36rpx] font-500 leading-[42rpx] text-[#fff]">{{ detail.status_name }}</view>
                        <view class="flex items-center">
                            <image v-if="['1','2','4','6','7'].indexOf(detail.status) != -1" class="w-[180rpx] h-[140rpx]" :src="img('addon/shop/detail/payment.png')" mode="aspectFit" />
                            <image v-if="['8'].indexOf(detail.status) != -1" class="w-[180rpx] h-[140rpx]" :src="img('addon/shop/detail/complete.png')" mode="aspectFit" />
                            <image v-if="['3','5','-1'].indexOf(detail.status) != -1" class="w-[180rpx] h-[140rpx]" :src="img('addon/shop/detail/close.png')" mode="aspectFit" />
                        </view>
                    </view>
                </view>
                <view class="sidebar-margin card-template flex justify-between flex-wrap mt-[-76rpx]">
                    <view class="w-[150rpx] h-[150rpx] rounded-[var(--goods-rounded-big)] overflow-hidden" @click="goodsEvent(detail.order_goods.goods_id)">
                        <u--image radius="var(--goods-rounded-big)" width="150rpx" height="150rpx" :src="img(detail.order_goods.goods_image_thumb_small ? detail.order_goods.goods_image_thumb_small : '')" model="aspectFill">
                            <template #error>
                                <image class="w-[150rpx] h-[150rpx] rounded-[var(--goods-rounded-big)] overflow-hidden" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill"/>
                            </template>
                        </u--image>
                    </view>
                    <view class="ml-[20rpx] flex flex-1 flex-col justify-between">
                        <view>
                            <view class="text-[28rpx] max-w-[490rpx] truncate leading-[40rpx]">{{ detail.order_goods.goods_name }}</view>
                            <view class="text-[22rpx] mt-[10rpx] text-[var(--text-color-light9)] truncate max-w-[490rpx] leading-[28rpx]" v-if="detail.order_goods.sku_name">{{ detail.order_goods.sku_name }}</view>
                        </view>
                        <view class="flex justify-between items-center leading-[28rpx] ">
                            <view class="price-font">
                                <text class="text-[24rpx]">￥</text>
                                <text class="text-[40rpx] font-500">{{ parseFloat(detail.order_goods.price).toFixed(2).split('.')[0] }}</text>
                                <text class="text-[24rpx] font-500">.{{ parseFloat(detail.order_goods.price).toFixed(2).split('.')[1] }}</text>
                            </view>
                            <text class="text-right text-[26rpx]">x{{ detail.order_goods.num }}</text>
                        </view>
                    </view>
                </view>
                <view class="sidebar-margin mt-[var(--top-m)] card-template">
                    <view class="justify-between text-[28rpx] card-template-item">
                        <view>{{ t('refundMoney') }}</view>
                        <view class="price-font text-[var(--price-text-color)]">
                            <text class="text-[24rpx] mr-[4rpx]">￥</text>
                            <text class="text-[28rpx]" v-if="detail.status == 8">{{ parseFloat(detail.money).toFixed(2) }}</text>
                            <text class="text-[28rpx]" v-else>{{ parseFloat(detail.apply_money).toFixed(2) }}</text>
                        </view>
                    </view>
                    <view class="justify-between text-[28rpx] card-template-item">
                        <view>{{ t('refundType') }}</view>
                        <view>{{ detail.refund_type_name }}</view>
                    </view>
                    <view class="justify-between text-[28rpx] card-template-item">
                        <view>{{ t('refundCause') }}</view>
                        <view class="w-[400rpx] multi-hidden text-right">{{ detail.reason || '--' }}</view>
                    </view>
                    <view class="justify-between text-[28rpx] card-template-item">
                        <view>{{ t('refundNo') }}</view>
                        <view>{{ detail.order_refund_no }}</view>
                    </view>
                    <view class="justify-between text-[28rpx] card-template-item">
                        <view>{{ t('createTime') }}</view>
                        <view>{{ detail.create_time }}</view>
                    </view>
                    <view class="justify-between text-[28rpx] card-template-item !items-baseline">
                        <view>{{ t('createExplain') }}</view>
                        <view class="flex-1 ml-[60rpx] text-right leading-[1.5] flex justify-end break-all">
                            {{ detail.remark }}
                        </view>
                    </view>
                    <view class="justify-between text-[28rpx] card-template-item !items-baseline break-all"
                          v-if="detail.shop_reason">
                        <view>{{ t('reasonRefusal') }}</view>
                        <view class="flex-1 ml-[60rpx] leading-[1.5] text-right text-[#333]">{{ detail.shop_reason }}</view>
                    </view>
                </view>

                <view class="sidebar-margin mt-[var(--top-m)] card-template">
                    <view class="justify-between text-[28rpx] card-template-item">
                        <view>{{ t('record') }}</view>
                        <view class="flex items-center"
                              @click="redirect({url: '/addon/shop/pages/refund/log', param: { order_refund_no: orderRefundNo }})">
                            <text class="text-[26rpx] text-[var(--text-color-light9)]">{{ t('check') }}</text>
                            <text class="nc-iconfont nc-icon-youV6xx text-[24rpx] text-[var(--text-color-light9)] pt-[2rpx]"></text>
                        </view>
                    </view>
                </view>

                <view
                    class="flex tab-bar justify-between items-center bg-[#fff] fixed left-0 right-0 bottom-0 min-h-[100rpx] pl-[30rpx] pr-[20rpx] flex-wrap">
                    <view class="flex">
                        <view class="flex mr-[20rpx] flex-col justify-center items-center"
                              @click="redirect({ url: '/addon/shop/pages/index', mode: 'reLaunch' })">
                            <view class="nc-iconfont nc-icon-shouyeV6xx11 text-[36rpx]"></view>
                            <text class="text-[20rpx] mt-[10rpx]">{{ t('index') }}</text>
                        </view>
                        <!-- #ifdef MP-WEIXIN -->
                        <view>
                            <nc-contact
                                :send-message-title="sendMessageTitle"
                                :send-message-path="sendMessagePath"
                                :send-message-img="sendMessageImg">
                                <view class="flex flex-col justify-center items-center">
                                    <text class="nc-iconfont nc-icon-kefuV6xx-1 text-[36rpx]"></text>
                                    <text class="text-[20rpx] mt-[10rpx]">客服</text>
                                </view>
                            </nc-contact>
                        </view>
                        <!-- #endif -->
                    </view>

                    <view class="flex justify-end">
                        <view class="min-w-[180rpx] box-border text-[26rpx] h-[70rpx] flex-center border-[2rpx] border-solid border-[#ccc] text-[#333] rounded-full ml-[20rpx]"
                            @click="refundBtnFn('cancel')" v-if="['6','7','8','-1','-3'].indexOf(detail.status) == -1">{{ t('refundApply') }}</view>
                        <view v-if="['3'].indexOf(detail.status) != -1" class="min-w-[180rpx] box-border text-[#333] text-[26rpx] h-[70rpx] flex-center border-[2rpx] border-solid border-[#ccc] rounded-full ml-[20rpx] px-[20rpx]"
                              @click.stop="refundBtnFn('edit')">编辑退款信息</view>
                        <view v-if="['2'].indexOf(detail.status) != -1" class="min-w-[180rpx] box-border text-[#333] text-[26rpx] h-[70rpx] flex-center border-[2rpx] border-solid border-[#ccc] rounded-full ml-[20rpx] px-[20rpx]"
                              @click.stop="refundBtnFn('logistics')">填写发货物流</view>
                        <view v-if="['5'].indexOf(detail.status) != -1" class="min-w-[180rpx] box-border text-[#333] text-[26rpx] h-[70rpx] flex-center border-[2rpx] border-solid border-[#ccc] rounded-full ml-[20rpx] px-[20rpx]"
                              @click.stop="refundBtnFn('editLogistics')">编辑发货物流</view>
                    </view>
                </view>
            </view>
            <view v-else>
                <view class="bg-linear">
                    <!-- #ifdef MP-WEIXIN -->
                    <top-tabbar :data="topTabbarData" :scrollBool="topTabarObj.getScrollBool()" />
                    <!-- #endif -->
                    <view
                        class="flex justify-between items-center pl-[40rpx] pr-[50rpx]  h-[280rpx] box-border pb-[90rpx]">
                        <view class="text-[36rpx] font-500 leading-[42rpx] text-[#fff]">{{ detail.status_name }}</view>
                        <view class="flex items-center">
                            <image class="w-[180rpx] h-[140rpx]" :src="img('addon/shop/detail/payment.png')" mode="aspectFit" />
                        </view>
                    </view>
                </view>
                <view class="sidebar-margin card-template mt-[-79rpx] flex justify-between flex-wrap">
                    <view class="w-[150rpx] h-[150rpx] flex-2" @click="goodsEvent(detail.order_goods.goods_id)">
                        <u--image radius="var(--goods-rounded-big)" width="150rpx" height="150rpx" :src="img(detail.order_goods.sku_image ? detail.order_goods.sku_image.split(',')[0] : '')" model="aspectFill">
                            <template #error>
                                <image class="w-[150rpx] h-[150rpx] rounded-[var(--goods-rounded-big)] overflow-hidden" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill"/>
                            </template>
                        </u--image>
                    </view>
                    <view class="ml-[20rpx] flex flex-1 flex-col justify-between">
                        <view>
                            <view class="text-[28rpx] max-w-[490rpx] truncate leading-[40rpx]">{{ detail.order_goods.goods_name }}</view>
                            <view class="text-[24rpx] mt-[14rpx] text-[var(--text-color-light9)] truncate max-w-[490rpx] leading-[28rpx]" v-if="detail.order_goods.sku_name">{{ detail.order_goods.sku_name }}</view>
                        </view>
                        <view class="flex justify-between items-center leading-[28rpx]">
                            <view class="price-font">
                                <text class="text-[24rpx] font-500">￥</text>
                                <text class="text-[40rpx] font-500">{{ parseFloat(detail.order_goods.price).toFixed(2).split('.')[0] }}</text>
                                <text class="text-[24rpx] font-500">.{{ parseFloat(detail.order_goods.price).toFixed(2).split('.')[1] }}</text>
                            </view>
                            <text class="text-right text-[26rpx]">x{{ detail.order_goods.num }}</text>
                        </view>
                    </view>
                </view>
                <view class="sidebar-margin card-template top-mar">
                    <view class="card-template-item justify-between text-[28rpx]">
                        <view>联系人</view>
                        <view>{{ detail.refund_address.contact_name }}</view>
                    </view>
                    <view class="card-template-item justify-between text-[28rpx]">
                        <view>手机号</view>
                        <view>{{ detail.refund_address.mobile }}</view>
                    </view>
                    <view class="card-template-item justify-between text-[28rpx]">
                        <view>退货地址</view>
                        <view class="w-[460rpx] text-sm text-right" v-if="detail.refund_address">
                            {{ detail.refund_address.full_address || '--' }}
                        </view>
                    </view>
                </view>
                <view class="sidebar-margin card-template top-mar py-[var(--top-m)] mb-[var(--top-m)]">
                    <view class="title">物流信息</view>
                    <u--form labelPosition="left" :model="formData" :rules="rules" errorType='toast' ref="deliveryForm" labelWidth="140rpx" :labelStyle="{'fontSize': '28rpx'}">
                        <u-form-item label="物流公司" prop="express_company" :borderBottom="false">
                            <u--input border="none" v-model="formData.express_company" placeholder="请输入物流公司"
                                      placeholderClass="text-sm !text-[var(--text-color-light9)]" fontSize="28rpx"
                                      maxlength="50"></u--input>
                        </u-form-item>
                        <view class="mt-[16rpx]">
                            <u-form-item label="物流单号" prop="express_number" :borderBottom="false">
                                <u--input border="none" placeholder="请输入物流单号" v-model="formData.express_number"
                                          placeholderClass="text-sm !text-[var(--text-color-light9)]" fontSize="28rpx"
                                          maxlength="100"></u--input>
                            </u-form-item>
                        </view>
                        <view class="mt-[16rpx]">
                            <u-form-item label="物流说明" :borderBottom="false">
                                <u--input border="none" placeholder="选填" v-model="formData.remark"
                                          placeholderClass="text-sm !text-[var(--text-color-light9)]" fontSize="28rpx"
                                          maxlength="100"></u--input>
                            </u-form-item>
                        </view>
                    </u--form>
                </view>
                <u-tabbar :fixed="true" :placeholder="true" :safeAreaInsetBottom="true" zIndex="10">
                    <view class="flex-1 pl-[var(--sidebar-m)] pr-[var(--sidebar-m)] bg-[var(--page-bg-color)]">
                        <button class=" primary-btn-bg text-[#fff] h-[80rpx] leading-[80rpx] rounded-[100rpx] text-[26rpx] font-500"
                            hover-class="none" @click="deliverySave">提交</button>
                    </view>
                </u-tabbar>

            </view>
            <logistics-tracking ref="materialRef"></logistics-tracking>
            <u-modal :show="cancelRefundShow" confirmColor="var(--primary-color)" :content="t('cancelRefundContent')"
                     :showCancelButton="true" :closeOnClickOverlay="true" @cancel="refundCancel"
                     @confirm="refundConfirm"></u-modal>
        </view>

        <loading-page :loading="loading"></loading-page>

        <!-- #ifdef MP-WEIXIN -->
        <!-- 小程序隐私协议 -->
        <wx-privacy-popup ref="wxPrivacyPopupRef"></wx-privacy-popup>
        <!-- #endif -->
    </view>
</template>

<script setup lang="ts">
import { ref, reactive, computed, nextTick } from 'vue';
import { onLoad } from '@dcloudio/uni-app'
import { t } from '@/locale'
import { img, redirect, copy, goback } from '@/utils/common';
import { topTabar } from '@/utils/topTabbar'
import { getRefundDetail, refundDelivery, editRefundDelivery, closeRefund } from '@/addon/shop/api/refund';
import logisticsTracking from '@/addon/shop/pages/order/components/logistics-tracking/logistics-tracking.vue'

/********* 自定义头部 - start ***********/
const topTabarObj = topTabar()
let topTabbarData = topTabarObj.setTopTabbarParam({ title: '退款详情' })
/********* 自定义头部 - end ***********/

const detail = ref<Object>({});
const loading = ref<boolean>(true);
const orderRefundNo = ref('');
const type = ref('');
const isEditDelivery = ref(false);

const sendMessageTitle = ref('')
const sendMessagePath = ref('')
const sendMessageImg = ref('')
// 物流信息
const formData = ref({
    express_number: '',
    express_company: '',
    remark: ''
})

// 物流验证
const rules = computed(() => {
    return {
        'express_number': {
            type: 'string',
            required: true,
            message: '请输入物流单号',
            trigger: ['blur', 'change']
        },
        'express_company': {
            type: 'string',
            required: true,
            message: '请输入物流公司',
            trigger: ['blur', 'change']
        },
    }
})

const wxPrivacyPopupRef: any = ref(null)

onLoad((option: any) => {
    orderRefundNo.value = option.order_refund_no;
    if (orderRefundNo.value) {
        type.value = option.type;
        isEditDelivery.value = option.is_edit_delivery;

        refundDetailFn(orderRefundNo.value);
        // #ifdef MP
        nextTick(() => {
            if (wxPrivacyPopupRef.value) wxPrivacyPopupRef.value.proactive();
        })
        // #endif
    } else {
        let parameter = {
            url: '/addon/shop/pages/refund/list',
            title: '缺少订单号'
        };
        goback(parameter);
    }
});

const refundDetailFn = (refundNo: any) => {
    loading.value = true;
    getRefundDetail(refundNo).then((res: any) => {
        detail.value = res.data;
        // 赋值物流信息
        if (isEditDelivery.value && detail.value.delivery) {
            formData.value.express_number = detail.value.delivery.express_number
            formData.value.express_company = detail.value.delivery.express_company
            formData.value.remark = detail.value.delivery.remark
        }

        sendMessageTitle.value = detail.value.order_goods.goods_name
        sendMessageImg.value = img(detail.value.order_goods.goods_image_thumb_small || '')
        loading.value = false;
    }).catch(() => {
        loading.value = false;
    })
}

const goodsEvent = (id: number) => {
    redirect({
        url: '/addon/shop/pages/goods/detail',
        param: {
            goods_id: id
        }
    })
}

// 提交物流信息
const deliveryForm = ref()
const deliverySave = () => {
    deliveryForm.value.validate().then(res => {
        let obj = { delivery: formData.value, order_refund_no: detail.value.order_refund_no }
        let api = isEditDelivery.value ? editRefundDelivery(obj) : refundDelivery(obj);
        api.then((res) => {
            setTimeout(() => {
                redirect({ url: '/addon/shop/pages/refund/list' })
            }, 500)
        }).catch(() => {
        })
    })
}

const refundBtnFn = (type: any) => {
    if (type == 'cancel') {
        currRefundOn = detail.value.order_refund_no;
        cancelRefundShow.value = true;
    } else if (type == 'edit') {
        redirect({
            url: '/addon/shop/pages/refund/edit_apply',
            param: { order_refund_no: detail.value.order_refund_no }
        })
    } else if (type == 'logistics') {
        redirect({
            url: '/addon/shop/pages/refund/detail',
            param: { order_refund_no: detail.value.order_refund_no, type: 'logistics' }
        })
    } else if (type == 'editLogistics') {
        redirect({
            url: '/addon/shop/pages/refund/detail',
            param: { order_refund_no: detail.value.order_refund_no, type: 'logistics', is_edit_delivery: true }
        })
    }
}

// 撤销售后
const cancelRefundShow = ref(false);
let currRefundOn = "";

const refundConfirm = () => {
    closeRefund(currRefundOn).then((res) => {
        cancelRefundShow.value = false;
        refundDetailFn(orderRefundNo.value);
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

.bg-linear {
    background: linear-gradient(94deg, #F84949 8%, #FF9A68 99%);
}

:deep(.u-form-item__body__left__content__label) {
    height: 40rpx !important;
}

:deep(.u-form-item__body) {
    padding: 0 !important;
    height: 40rpx !important;
}

:deep(.u-form-item) {
    margin-bottom: 34rpx;
}
</style>

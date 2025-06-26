<template>
    <view :style="themeColor()">
        <view class="bg-[var(--page-bg-color)] min-h-screen overflow-hidden" v-if="Object.keys(friendsInfo).length && !loading">
            <view :style="{background: 'url(' + img('static/resource/images/app/friendpay_money.png') + ') left bottom / cover no-repeat'}" class="pb-[194rpx] overflow-hidden">
                <!-- #ifdef MP-WEIXIN -->
                <view class="sticky top-0 left-0 right-0 z-100">
                    <top-tabbar :data="topTabbarData" :scrollBool="topTabarObj.getScrollBool()" />
                </view>
                <!-- #endif -->
                <view class="mt-[20rpx] flex flex-col items-center">
                    <u-avatar :src="img(friendsInfo.member.headimg)" size="50" leftIcon="none" :default-url="img('static/resource/images/default_headimg.png')"  />
                    <view class="flex items-center mt-[20rpx] text-[#fff] text-[26rpx] leading-[36rpx]">
                        <text class="font-bold mr-[10rpx] max-w-[250rpx] truncate">{{ friendsInfo.member.nickname }}</text>
                        <text>请您帮忙付款~</text>
                    </view>
                    <view class="message  bg-[#fe0708] relative max-w-[520rpx] px-[20rpx] py-[12rpx] rounded-[12rpx] border-solid border-[1rpx] border-color  text-[24rpx] text-[#fff] leading-[30rpx] box-border text-center mt-[20rpx] mx-[114rpx]" v-if="friendsInfo.config.pay_leave_message">{{ friendsInfo.config.pay_leave_message }}</view>
                 </view>
            </view>
            <view class="-mt-[154rpx] card-template sidebar-margin mb-[var(--top-m)]">
                <view class="text-[24rpx] text-center mb-[10rpx]">{{ t('payMoney') }}</view>
                <view class="text-center mb-[50rpx]">
                    <text class="text-[32rpx] font-500 price-font text-[#FF4142]">￥</text>
                    <text class="text-[56rpx] font-bold price-font text-[#FF4142]">{{ parseFloat(friendsInfo.money).toFixed(2).split('.')[0] }}</text>
                    <text class="text-[32rpx] font-500 price-font text-[#FF4142]">.{{ parseFloat(friendsInfo.money).toFixed(2).split('.')[1] }}</text>
                </view>
                <view class="px-[20rpx] box-border">
                    <button class="bg-[#FFB4B1] !text-[#fff] h-[80rpx] leading-[80rpx] rounded-[100rpx] text-[26rpx] font-500" hover-class="none" v-if="friendsInfo.status == 2 ">{{ t('finish') }}</button>
                    <button class="bg-[#FFB4B1] !text-[#fff] h-[80rpx] leading-[80rpx] rounded-[100rpx] text-[26rpx] font-500" hover-class="none" v-else-if="friendsInfo.status == -1">{{ t('close') }}</button>
                    <button class="botton-color !text-[#fff] h-[80rpx] leading-[80rpx] rounded-[100rpx] text-[26rpx] font-500" hover-class="none" v-else :loading="operateLoading" @click="save">{{ friendsInfo.config.pay_button_name ?  friendsInfo.config.pay_button_name : t('payGenerously') }}</button>
                </view>
				<view class="px-[10px] flex justify-between items-center text-[var(--text-color-light9)] mt-[20rpx] ">
					<view class="flex items-baseline justify-between text-[var(--text-color-light9)]" @click="redirect({url: '/app/pages/index/index'})">
					    <text class="text-[24rpx] mr-[6rpx]">返回首页</text>
					</view>
					<view class="flex-shrink-0" @click="handleMessage" v-if="friendsInfo.config.pay_explain_switch">
					    <text class="mr-[8rpx] text-[24rpx]">{{ friendsInfo.config.pay_explain_title }}</text>
					    <text class="nc-iconfont nc-icon-jichuxinxiV6xx text-[26rpx] "></text>
					</view>
				</view>
              
				
            </view>
            <view class="card-template sidebar-margin mb-[var(--top-m)]" v-if="friendsInfo.config.pay_info_switch">
                <template v-if="JSON.stringify(friendsInfo.trade_info) !== '[]' && friendsInfo.trade_info.item_list.length">
                    <view class="flex justify-between items-center  mb-[30rpx]">
                        <view class="text-[30rpx] text-[#333] font-500">{{ t('helpPayInfo') }}</view>
                       <!-- <view class="flex-shrink-0" @click="handleMessage" v-if="friendsInfo.config.pay_explain_switch">
                            <text class="mr-[8rpx] text-[24rpx]">{{ friendsInfo.config.pay_explain_title }}</text>
                            <text class="nc-iconfont nc-icon-jichuxinxiV6xx text-[26rpx]"></text>
                        </view> -->
                    </view>
                    <view class="border-0 border-solid border-b-[1rpx] border-[#f6f6f6] mb-[20rpx]">
                        <view v-for="(item, index) in friendsInfo.trade_info.item_list" class="flex justify-between" :class="{' mb-[34rpx]': (index + 1) != friendsInfo.trade_info.length }">
                            <view class="w-[170rpx] h-[170rpx] rounded-[var(--goods-rounded-big)] overflow-hidden flex-shrink-0">
                                <u--image class="overflow-hidden" radius="var(--goods-rounded-big)" width="170rpx" height="170rpx"  :src="img(item.item_image ? item.item_image : '')" model="aspectFill">
                                    <template #error>
                                        <image class="w-[170rpx] h-[170rpx] rounded-[var(--goods-rounded-big)] overflow-hidden" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill" />
                                    </template>
                                </u--image>
                            </view>
                            <view class="ml-[20rpx] flex flex-1 flex-col justify-between">
                                <view>
                                    <view class="text-[28rpx] using-hidden leading-[40rpx] text-[#333]">{{ item.item_name }}</view>
                                    <view class="text-[24rpx] mt-[14rpx] text-[var(--text-color-light9)] using-hidden leading-[28rpx]" v-if="item.item_sub_name ">{{ item.item_sub_name }}</view>
                                </view>
                                <view class="flex justify-between items-baseline">
                                    <view class="price-font text-[#FF4142]">
                                        <text class="text-[24rpx]">￥</text>
                                        <text class="text-[40rpx] font-500">{{ parseFloat(item.item_price).toFixed(2).split('.')[0] }}</text>
                                        <text class="text-[24rpx] font-500">.{{ parseFloat(item.item_price).toFixed(2).split('.')[1] }}</text>
                                    </view>
                                    <text class="text-right text-[26rpx]">x{{ item.item_num }}</text>
                                </view>
                            </view>
                        </view>
                    </view>
                    <view class="text-[26rpx] text-right">{{ friendsInfo.trade_info.item_total }}</view>
                </template>
                <template v-else>
                    <view class="text-[28rpx]  leading-[40rpx] text-[#333]">{{ friendsInfo.body }}</view>
                </template>
            </view>
        </view>
        <!-- 帮付说明 -->
        <message ref="messageRef"></message>
        <!-- 支付 -->
        <pay ref="payRef" />
        <loading-page :loading="loading"></loading-page>
    </view>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { t } from '@/locale'
import { img, redirect, getToken,handleOnloadParams,goback } from '@/utils/common';
import { onLoad, onShow,onHide,onUnload } from '@dcloudio/uni-app'
import { topTabar } from '@/utils/topTabbar';
import { getFriendspayInfo } from '@/app/api/friendspay'
import Message from '@/app/pages/friendspay/components/message.vue'
import { useLogin } from '@/hooks/useLogin'

/********* 自定义头部 - start ***********/
const topTabarObj = topTabar()
let topTabbarData = topTabarObj.setTopTabbarParam({ title: '' })
/********* 自定义头部 - end ***********/

const loading = ref<boolean>(true);
const tradeId = ref<number>(0);
const tradeType = ref<string>('');
const friendsInfo = ref<any>({})
const operateLoading = ref(false)

const firstCall = ref<boolean>(true)

const timeOut: any = ref(null)

onLoad((option: any) =>{
    // #ifdef MP-WEIXIN
    // 处理小程序场景值参数
    option = handleOnloadParams(option);
    // #endif
    tradeId.value = option.id || 0;
    tradeType.value = option.type || '';
})

onShow(()=>{
    if(tradeId.value && tradeType.value){
        getFriendspayInfoFn(tradeType.value, tradeId.value)
    }
})

onHide(()=>{
    if(timeOut.value) {
        clearTimeout(timeOut.value);
        timeOut.value = null;
    }
})

onUnload(()=>{
    if(timeOut.value) {
        clearTimeout(timeOut.value);
        timeOut.value = null;
    }
})

const getFriendspayInfoFn = (tradeType : string, tradeId : number) => {
    if (firstCall.value) { // 检查是否是第一次调用
        loading.value = true;
        firstCall.value = false; // 将标志变量设置为 false
    }
    getFriendspayInfo(tradeType, tradeId).then((res: any) => {
        friendsInfo.value = res.data
        uni.setNavigationBarTitle({
			title: friendsInfo.value.config.pay_page_name
		})
        topTabbarData = topTabarObj.setTopTabbarParam({ title: friendsInfo.value.config.pay_page_name })
        loading.value = false

        if( friendsInfo.value.status != 2 && friendsInfo.value.status != 1  && friendsInfo.value.status != -1){
            // 每隔3秒查询一次支付结果
            timeOut.value = setTimeout(() => {
                getFriendspayInfoFn(tradeType, tradeId)
            }, 1000 * 3)
        }else{
            clearTimeout(timeOut.value);
            timeOut.value = null;
        }

    }).catch(()=>{
        if(timeOut.value) {
            clearTimeout(timeOut.value);
            timeOut.value = null;
        }
        loading.value = false
        let parameter = {
            title: '未找到帮付订单信息',
            url: '/app/pages/index/index',
            mode: 'reLaunch'
        };
        goback(parameter)
    })
}

// 弹框
const messageRef = ref<any>(null);
const handleMessage = () => {
    messageRef.value.open(friendsInfo.value.config)
}

const payRef = ref()
const save = () =>{
    if(getToken()){
        payRef.value?.open(friendsInfo.value.trade_type, friendsInfo.value.trade_id, '/app/pages/index/index', 'friendspay')
    } else {
        useLogin().setLoginBack({
			url: '/app/pages/friendspay/money',
			param: { id: friendsInfo.value.trade_id, type: friendsInfo.value.trade_type }
		})
    }
}

</script>

<style lang="scss" scoped>
.border-color{
    border-color: rgba(255, 255, 255, 0.7);
}
.message{
    &::after{
        content: '';
        width: 16rpx;
        height: 16rpx;
        background-color: #fe0708;
        border: solid 1rpx transparent;
        border-top-color: #fff;
        border-left-color: #fff;
        border-top-left-radius:4rpx;
        position: absolute;
        left: 50%;
        top: 0;
        transform: translateX(-50%) translateY(-50%)  rotate(45deg);
    }
}
.botton-color{
	background: linear-gradient( 94deg, #FB7939 0%, #FE120E 99%), #EF000C;
}
</style>

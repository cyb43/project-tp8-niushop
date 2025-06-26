<template>
    <view  :style="themeColor()">
        <view class="bg-[var(--page-bg-color)] min-h-screen overflow-hidden" v-if="Object.keys(friendsInfo).length && !loading">
            <view :style="{background: 'url(' + img('static/resource/images/app/friendpay_bg.png') + ') left bottom /100% no-repeat'}" class="pb-[168rpx] overflow-hidden">
                <!-- #ifdef MP-WEIXIN -->
                <view class="sticky top-0 left-0 right-0 z-100">
                    <top-tabbar :data="topTabbarData" :scrollBool="topTabarObj.getScrollBool()" />
                </view>
                <!-- #endif -->
                 <view class="mt-[20rpx] flex flex-col items-center">
                    <u-avatar :src="img(friendsInfo.member.headimg)" size="50" leftIcon="none" :default-url="img('static/resource/images/default_headimg.png')"  />
                    <view class="flex items-center mt-[20rpx] text-[#fff] text-[26rpx] leading-[36rpx]">
                        <text class="font-bold mr-[10rpx] max-w-[250rpx] truncate">{{ friendsInfo.member.nickname }}</text>
                        <text>发起了订单帮付请求~</text>
                    </view>
                 </view>
            </view>
            <view class="mt-[-128rpx] card-template sidebar-margin  mb-[var(--top-m)]">
                <view class="text-[24rpx] text-center text-[#333] mb-[10rpx]">{{ t('payMoney') }}</view>
                <view class="text-center mb-[50rpx]">
                    <text class="text-[32rpx] font-500 price-font text-[#FF4142]">￥</text>
                    <text class="text-[56rpx] font-bold price-font text-[#FF4142]">{{ parseFloat(friendsInfo.money).toFixed(2).split('.')[0] }}</text>
                    <text class="text-[32rpx] font-500 price-font text-[#FF4142]">.{{ parseFloat(friendsInfo.money).toFixed(2).split('.')[1] }}</text>
                </view>
                <view class="px-[20rpx] box-border">
                    <button class="bg-[#FFB4B1] !text-[#fff] h-[80rpx] leading-[80rpx] rounded-[100rpx] text-[26rpx] font-500" hover-class="none" v-if="friendsInfo.status == 2">{{ t('finish') }}</button>
                    <button class="bg-[#FFB4B1] !text-[#fff] h-[80rpx] leading-[80rpx] rounded-[100rpx] text-[26rpx] font-500" hover-class="none" v-else-if="friendsInfo.status == -1">{{ t('close') }}</button>
                    <button class="button-color !text-[#fff] h-[80rpx] leading-[80rpx] rounded-[100rpx] text-[26rpx] font-500" hover-class="none" v-else :loading="operateLoading" @click="openShareFn">{{ friendsInfo.config.pay_type_name ?  friendsInfo.config.pay_type_name : t('friendPay') }}</button>
                </view>
                <view class="mt-[20rpx] flex items-baseline justify-center text-[var(--text-color-light9)]" v-if="friendsInfo.status == 2 && JSON.stringify(friendsInfo.trade_info) !== '[]' && friendsInfo.trade_info.detail_url" @click="redirect({url: friendsInfo.trade_info.detail_url })">
                    <text class="text-[24rpx] mr-[6rpx]">查看订单</text>
                    <text class="nc-iconfont nc-icon-youV6xx text-[26rpx]"></text>
                </view>
            </view>
            <view class="card-template sidebar-margin  mb-[var(--top-m)]">
                <view class="flex justify-between items-center mb-[30rpx]">
                    <view class="text-[30rpx] text-[#333] font-500">{{ t('friendPayOrderInfo') }}</view>
                    <view class="text-[#666] leading-[1]" @click="handleMessage" v-if="friendsInfo.config.pay_explain_switch">
                        <text class="mr-[8rpx] text-[24rpx]">{{ friendsInfo.config.pay_explain_title  }}</text>
                        <text class="nc-iconfont nc-icon-jichuxinxiV6xx text-[26rpx]"></text>
                    </view>
                </view>
                <template v-if="JSON.stringify(friendsInfo.trade_info) !== '[]'">
                    <template v-if="friendsInfo.trade_info.item_list.length">
                        <view class="border-0 border-solid border-b-[1rpx] border-[#f6f6f6] mb-[20rpx]">
                            <view v-for="(item, index) in friendsInfo.trade_info.item_list" class="flex justify-between mb-[30rpx]">
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
                                        <view class="text-[24rpx] mt-[14rpx] text-[var(--text-color-light9)] using-hidden leading-[28rpx]" v-if="item.item_sub_name"> {{ item.item_sub_name }} </view>
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
                </template>
                <template v-else>
                    <view class="text-[28rpx]  leading-[40rpx] text-[#333]">{{ friendsInfo.body }}</view>
                </template>
            </view>

            <share-poster ref="sharePosterRef" posterType="friendspay" :posterId="friendsInfo.poster_id" :posterParam="posterParam" :copyUrl="copyUrl" :copyUrlParam="copyUrlParam" />
            <!-- 帮付说明 -->
            <message ref="messageRef"></message>
        </view>

        <loading-page :loading="loading"></loading-page>
    </view>
</template>

<script lang="ts" setup>
import { ref, computed, nextTick } from 'vue';
import { t } from '@/locale'
import { img, redirect, goback} from '@/utils/common';
import { onLoad, onShow, onHide, onUnload } from '@dcloudio/uni-app'
import { getFriendspayInfo } from '@/app/api/friendspay'
import { useShare }from '@/hooks/useShare'
import useMemberStore from '@/stores/member'
import { topTabar } from '@/utils/topTabbar';
import sharePoster from '@/components/share-poster/share-poster.vue'
import Message from '@/app/pages/friendspay/components/message.vue'

/********* 自定义头部 - start ***********/
const topTabarObj = topTabar()
let topTabbarData = topTabarObj.setTopTabbarParam({ title: '' })
/********* 自定义头部 - end ***********/

const loading = ref<boolean>(true);
const operateLoading = ref(false)
const tradeId = ref<number>(0);
const tradeType = ref<string>('');
const friendsInfo = ref<any>({})

const firstCall = ref<boolean>(true)
const timeOut: any = ref(null)

// 分享
const{setShare} = useShare()

// 会员信息
const memberStore = useMemberStore()
const userInfo = computed(() => memberStore.info)

onLoad((option: any) =>{
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
        loading.value = false
        uni.setNavigationBarTitle({
			title: friendsInfo.value.config.pay_page_name
		})
        topTabbarData = topTabarObj.setTopTabbarParam({ title: friendsInfo.value.config.pay_page_name })

       // 分享 - start
       let link = '';

        // #ifdef H5
        let pathName = location.pathname;
		let packageArr: any = ['/app/', '/addon/'];
		for (let i = 0; i < packageArr.length; i++) {
			if (pathName.indexOf(packageArr[i]) != -1) {
				pathName = pathName.substr(0, pathName.indexOf(packageArr[i]));
			}
		}

        link = location.origin + pathName + `/app/pages/friendspay/money?id=${friendsInfo.value.trade_id}&type=${friendsInfo.value.trade_type}`;
        // #endif

		let share: any = {
            desc: friendsInfo.value.config.pay_leave_message,
            path: `/app/pages/friendspay/money?id=${friendsInfo.value.trade_id}&type=${friendsInfo.value.trade_type}`,
            link
        }
        if(friendsInfo.value.member){
            let name = friendsInfo.value.member.nickname
            name = name.length > 15 ? name = name.substring(0, 15) + '...' : name
            share.title = `${name}希望你帮他付${friendsInfo.value.money}元`
        }
        if(JSON.stringify(friendsInfo.value.trade_info) !== '[]' && friendsInfo.value.trade_info.item_list.length){
            // #ifdef H5
            share.url = friendsInfo.value.trade_info.item_list[0].item_image ? friendsInfo.value.trade_info.item_list[0].item_image : friendsInfo.value.config.pay_wechat_share_image
            // #endif
            // #ifdef MP-WEIXIN
            share.url = friendsInfo.value.trade_info.item_list[0].item_image ? friendsInfo.value.trade_info.item_list[0].item_image :  friendsInfo.value.config.pay_weapp_share_image
            // #endif
        }else{
            // #ifdef H5
            share.url = friendsInfo.value.config.pay_wechat_share_image
            // #endif
            // #ifdef MP-WEIXIN
            share.url = friendsInfo.value.config.pay_weapp_share_image
            // #endif
        }
		setShare({
			wechat: {
				...share
			},
			weapp: {
				...share
			}
		});
		// 分享 - end
        copyUrlFn();
        nextTick(() => {
            setTimeout(() => {
                if(sharePosterRef.value) {
                    posterParam.id = friendsInfo.value.trade_id;
                    posterParam.type = friendsInfo.value.trade_type;
                    if (userInfo.value && userInfo.value.member_id) posterParam.member_id = userInfo.value.member_id;
                    sharePosterRef.value.loadPoster();
                }
            }, 400)
        })

        if( friendsInfo.value.status != 2 && friendsInfo.value.status != 1  && friendsInfo.value.status != -1){
            // 每隔3秒查询一次支付结果
            timeOut.value = setTimeout(() => {
                getFriendspayInfoFn(tradeType, tradeId)
            }, 1000 * 3)
        }else{
            clearTimeout(timeOut.value);
            timeOut.value = null;
        }

    }).catch((err: any) => {
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
    });
}

// 弹框
const messageRef = ref<any>(null);
const handleMessage = () => {
    messageRef.value.open(friendsInfo.value.config)
}

/************* 分享海报-start **************/
const sharePosterRef: any = ref(null);
const copyUrl = ref('/app/pages/friendspay/money')
const copyUrlParam = ref('');
let posterParam: any = {};

// 分享海报链接
const copyUrlFn = ()=>{
	copyUrlParam.value = '?id='+ friendsInfo.value.trade_id;
    copyUrlParam.value += '&type=' + friendsInfo.value.trade_type;
}

const openShareFn = ()=>{
    posterParam.id = friendsInfo.value.trade_id;
    posterParam.type = friendsInfo.value.trade_type;
    if (userInfo.value && userInfo.value.member_id) posterParam.member_id = userInfo.value.member_id;
	sharePosterRef.value.openShare()
}
/************* 分享海报-end **************/
</script>

<style lang="scss" scoped>
	.button-color{
		background: linear-gradient( 94deg, #FB7939 0%, #FE120E 99%), #EF000C;
	}
</style>

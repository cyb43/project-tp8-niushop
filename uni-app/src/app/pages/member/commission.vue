<template>
    <view class="bg-[var(--page-bg-color)] min-h-[100vh] w-full" :style="themeColor()" v-if="memberStore.info">
        <view class="fixed w-full z-2 !bg-[var(--page-bg-color)]">
            <view class="pb-[272rpx]" :style="headerStyle">
                <!-- #ifdef MP-WEIXIN -->
                <top-tabbar :data="param" class="top-header" />
                <!-- #endif -->
            </view>
            <view class="mt-[-232rpx] sidebar-margin rounded-[var(--rounded-big)]"
                  :style="{ backgroundImage: 'url(' + img('static/resource/images/member/commission/account_bg.png') + ')',backgroundRepeat:'no-repeat',backgroundSize:'100% 100%'}">
                <view class="pt-[40rpx]">
                    <view class="flex items-center justify-between px-[30rpx]">
                        <view>
                            <view class="text-[26rpx] font-400 text-[#fff] mb-[20rpx]">{{ t('accountCommission') }}</view>
                            <view class="font-bold text-[56rpx] price-font text-[#fff] flex items-baseline">{{ memberStore.info ? moneyFormat(memberStore.info.commission) : '0.00' }}</view>
                        </view>
                        <button @click="applyCashOut" hover-class="none" class="bg-[#fff] rounded-[100rpx] w-[160rpx] h-[70rpx] flex-center text-[#EF000C] m-[0] border-[0] text-[26rpx]">{{ t('transferMoney') }}</button>
                    </view>
                    <view class="flex items-center mt-[60rpx] px-[30rpx] pb-[10rpx] border-[0] border-t-[2rpx] border-solid border-[rgba(255,255,255,.1)] h-[126rpx]">
                        <view class="flex-1">
                            <view class="font-bold text-[#fff] text-[40rpx] mb-[10rpx] price-font">{{ moneyFormat(memberStore.info?.commission_get) || '0.00' }}</view>
                            <view class="text-[24rpx] text-[#fff]">{{ t('commission') }}</view>
                        </view>
                        <view class="flex-1">
                            <view class="font-bold text-[#fff] text-[40rpx] mb-[10rpx] price-font">{{ moneyFormat(memberStore.info?.commission_cash_outing) || '0.00' }}</view>
                            <view class="text-[24rpx] text-[#fff]">{{ t('money') }}</view>
                        </view>
                    </view>
                </view>
            </view>
            <view class="mt-[30rpx] tab-style-1">
                <view class="tab-left">
                    <view class="tab-left-item"
                          :class="{ 'class-select': fromType.from_type === item.from_type && fromType.account_data_gt === item.account_data_gt }"
                          @click="fromTypeFn(item,index)" v-for="(item, index) in accountTypeList">{{ item.name }}</view>
                </view>
                <view class="tab-right" @click="handleSelect">
                    <view class="tab-right-date">日期</view>
                    <view class="nc-iconfont nc-icon-a-riliV6xx-36 tab-right-icon"></view>
                </view>
            </view>
        </view>
        <mescroll-body ref="mescrollRef" @init="mescrollInit" :down="{ use: false }" @up="geCommissionListFn" :top="mescrollTop">
            <view class="px-[var(--sidebar-m)] pt-[10rpx] body-bottom" v-if="list.length">
                <view v-for="(item,index) in list" :key="item.id"
                      class="w-full h-[140rpx] flex justify-between items-center card-template"
                      :class="{'mt-[var(--top-m)]':index}">
                    <view class="flex items-center">
                        <view class="w-[80rpx] h-[80rpx] rounded-[40rpx] text-[40rpx] font-500  text-[#fff] flex items-center justify-center"
                            :class="{'bg-[#EF000C]' :item.account_data > 0, 'bg-[#1379FF]':item.account_data <= 0 }">{{ item.account_data > 0 ? '收' : '提' }}</view>
                        <view class="flex flex-col ml-[20rpx]">
                            <view class="text-[#333] text-[28rpx] leading-[36rpx]">{{ item.from_type_name }}</view>
                            <view class="text-[var(--text-color-light9)] text-[24rpx] mt-[12rpx]">{{ item.create_time }}</view>
                        </view>
                    </view>
                    <view class="text-[36rpx] leading-[50rpx] price-font"
                          :class="{'text-[#EF000C]' :item.account_data > 0, 'text-[#1379FF]':item.account_data <= 0 }">{{ item.account_data > 0 ? '+' + item.account_data : item.account_data }}</view>
                </view>
            </view>
            <mescroll-empty v-if="!list.length && !loading &&!listLoading"></mescroll-empty>
        </mescroll-body>
        <loading-page :loading="loading"></loading-page>
        <!-- 时间选择 -->
        <select-date ref="selectDateRef" @confirm="confirmFn" />
    </view>
</template>

<script setup lang="ts">
import { computed, ref, reactive } from 'vue'
import { t } from '@/locale'
import { moneyFormat, redirect, img } from '@/utils/common';
import useMemberStore from '@/stores/member'
import { getMemberCommission } from '@/app/api/member';
import MescrollBody from '@/components/mescroll/mescroll-body/mescroll-body.vue';
import MescrollEmpty from '@/components/mescroll/mescroll-empty/mescroll-empty.vue';
import useMescroll from '@/components/mescroll/hooks/useMescroll.js';
import { onPageScroll, onReachBottom } from '@dcloudio/uni-app';
import { topTabar } from '@/utils/topTabbar'
import selectDate from '@/components/select-date/select-date.vue';
import useSystemStore from "@/stores/system";

const { downCallback, mescrollInit, getMescroll } = useMescroll(onPageScroll, onReachBottom);
const memberStore = useMemberStore();
const systemStore = useSystemStore()
const info = computed(() => memberStore.info)
// 提现
const applyCashOut = () => {
    uni.setStorageSync('cashOutAccountType', 'commission')
    redirect({ url: '/app/pages/member/apply_cash_out' })
}

/********* 自定义头部 - start ***********/
const topTabarObj = topTabar()
let param = topTabarObj.setTopTabbarParam({ title: '我的佣金', topStatusBar: { bgColor: '#fff', textColor: '#333' } })
/********* 自定义头部 - end ***********/

const headerStyle = computed(() => {
    return {
        backgroundImage: 'url(' + img('static/resource/images/member/commission/commission_bg.png') + ') ',
        backgroundSize: 'cover',
        backgroundRepeat: 'no-repeat',
        backgroundPosition: 'bottom',
    }
})
//  16为自定头部的padding-bottom
const mescrollTop = computed(() => {
    return Object.keys(systemStore.menuButtonInfo).length ? (Number(systemStore.menuButtonInfo.height) * 2 + systemStore.menuButtonInfo.top * 2 + 470 + 16) + 'rpx' : '470rpx'
})

//来源类型
const fromType = ref({
    from_type: '',
    account_data_gt: ''
})
const accountTypeList = ref([
    { name: '全部', from_type: '', account_data_gt: '' },
    { name: '佣金', from_type: '', account_data_gt: 0 },
    { name: '提现', from_type: 'cash_out', account_data_gt: '' },
])
const create_time = ref([])
const list = ref<Array<any>>([])
const loading = ref<boolean>(true)
const listLoading = ref<boolean>(true)
const mescrollRef = ref(null);

const fromTypeFn = (data: any, index: number) => {
    fromType.value.from_type = data.from_type
    fromType.value.account_data_gt = data.account_data_gt
    getMescroll().resetUpScroll();
}
const geCommissionListFn = (mescroll: any) => {
    listLoading.value = true;
    let data: Object = {
        page: mescroll.num,
        limit: mescroll.size,
        from_type: fromType.value.from_type,
        account_data_gt: fromType.value.account_data_gt,
        create_time: create_time.value
    }
    getMemberCommission(data).then((res: any) => {
        let newArr = res.data.data;
        mescroll.endSuccess(newArr.length);
        //设置列表数据
        if (mescroll.num == 1) {
            list.value = []; //如果是第一页需手动制空列表
        }
        list.value = list.value.concat(newArr);
        listLoading.value = false;
        loading.value = false;
    }).catch(() => {
        listLoading.value = false;
        loading.value = false;
        mescroll.endErr(); // 请求失败, 结束加载
    })
}
//日期筛选
const selectDateRef = ref()
const handleSelect = () => {
    selectDateRef.value.show = true
}
// 确定时间筛选
const confirmFn = (data: any) => {
    create_time.value = data;
    list.value = []
    getMescroll().resetUpScroll();
}
</script>

<style lang="scss">
.member-level {
    background: linear-gradient(360deg, #F23621 11%, #FF7F71 100%), #D9D9D9;
    border-radius: 0 20rpx 20rpx 0;
}

:deep(.uni-scroll-view) {
    overflow: auto hidden !important;
}

.body-bottom {
    padding-bottom: calc(20rpx + constant(safe-area-inset-bottom));
    padding-bottom: calc(20rpx + env(safe-area-inset-bottom));
}
</style>

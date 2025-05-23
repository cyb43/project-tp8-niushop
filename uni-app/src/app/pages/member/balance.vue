<template>
    <view class="min-h-[100vh] !bg-[var(--page-bg-color)]" :style="themeColor()" v-if="memberStore.info">
        <view class="fixed w-full z-2  !bg-[var(--page-bg-color)]">
            <view class="pb-[190rpx] text-[#fff] w-full" :style="headerStyle">
                <!-- #ifdef MP-WEIXIN -->
                <top-tabbar :data="param" class="top-header" />
                <!-- #endif -->
                <view class="leading-[38rpx] text-[28rpx] pl-[60rpx] pt-[100rpx]">{{ t('accountBalance') }}</view>
                <view class="flex items-baseline pl-[60rpx]">
                    <text class="text-[36rpx] leading-[52rpx] mr-[6rpx] price-font">￥</text>
                    <text class="text-[56rpx] leading-[72rpx] price-font">{{ memberStore.info ? moneyFormat((parseFloat(memberStore.info.balance) + parseFloat(memberStore.info.money)).toString()).split('.')[0] : '0' }}.</text>
                    <text class="text-[36rpx] leading-[56rpx] price-font">{{ memberStore.info ? moneyFormat((parseFloat(memberStore.info.balance) + parseFloat(memberStore.info.money)).toString()).split('.')[1] : '00' }}</text>
                </view>
            </view>
            <view
                class="sidebar-margin pt-[50rpx] pb-[40rpx] bg-[#fff] rounded-[var(--rounded-big)] px-[40rpx] box-border mt-[-112rpx]">
                <view class="flex flex-col items-center w-full"
                      @click="redirect({ url: '/app/pages/member/detailed_account', param: { type : 'money' } })"
                      :class="{'pt-[12rpx]': !Object.keys(cashOutConfigObj).length || (Object.keys(cashOutConfigObj).length && !systemStore.siteAddons.includes('recharge') && cashOutConfigObj.is_open != 1)}">
                    <view class=" text-[var(--text-color-light9)] text-[26rpx] leading-[34rpx] mb-[12rpx]">{{ t('money') }}</view>
                    <view class="text-[#333] inline-block">
                        <text class="text-[36rpx] mr-[6rpx] price-font">￥</text>
                        <text class="text-[56rpx] font-500 price-font">{{ moneyFormat(memberStore.info?.money).split('.')[0] }}.</text>
                        <text class="text-[36rpx] font-500 price-font">{{ moneyFormat(memberStore.info?.money).split('.')[1] }}</text>
                    </view>
                </view>
                <view class="mt-[60rpx] flex justify-around" v-if="Object.keys(cashOutConfigObj).length && (systemStore.siteAddons.includes('recharge') || cashOutConfigObj.is_open == 1 || rechargeConfigObj.is_use == 1)">
                    <template v-if="systemStore.siteAddons.includes('recharge')">
                        <button v-if="rechargeConfigObj.is_use == 1"
                                class="w-[250rpx] h-[70rpx] rounded-[40rpx] text-[26rpx] font-500 !bg-[#fff] !text-[var(--primary-color)] flex-center !m-0 border-[2rpx] border-[var(--primary-color)] border-solid box-border"
                                hover-class="none" shape="circle"
                                @click="redirect({url: '/addon/recharge/pages/recharge'})">充值</button>
                    </template>
                    <view v-if="cashOutConfigObj.is_open == 1"
                          :class="{'!w-[340rpx]': !systemStore.siteAddons.includes('recharge')}"
                          class="text-center w-[250rpx] h-[70rpx] rounded-[40rpx] text-[26rpx] !text-[#fff] flex-center font-500 !m-0"
                          style="background: linear-gradient( 94deg, #FB7939 0%, #FE120E 99%), #EF000C;"
                          @click="applyCashOut">提现</view>
                </view>
            </view>
            <view class="mt-[30rpx] bg-[var(--page-bg-color)] tab-style-1">
                <view class="tab-left">
                    <view class="tab-left-item" :class="{ 'class-select': fromType === item.key}" @click="fromTypeFn(item.key,index)" v-for="(item, index) in accountTypeList">{{ item.name }}</view>
                </view>
                <view class="tab-right" @click="handleSelect">
                    <view class="tab-right-date">日期</view>
                    <view class="nc-iconfont nc-icon-a-riliV6xx-36 tab-right-icon"></view>
                </view>
            </view>
        </view>
        <mescroll-body ref="mescrollRef" @init="mescrollInit" :down="{ use: false }" height="auto" @up="getListFn" :top="mescrollTop">
            <view class="sidebar-margin pt-[10rpx] body-bottom" v-if="list.length">
                <view v-for="(item,index) in list" :key="item.id"
                      class="w-full h-[140rpx] flex justify-between items-center box-border card-template"
                      :class="{'mt-[var(--top-m)]':index>0}">
                    <view class="flex items-center">
                        <view class="w-[80rpx] h-[80rpx] text-center rounded-[40rpx] text-[40rpx] font-500 leading-[80rpx] text-[#fff]"
                            :class="{'bg-[#EF000C]' :item.account_data > 0&&item.account_type!='money', 'bg-[#03B521]':item.account_data <= 0&&item.account_type!='money','bg-[#1379FF]':item.account_type=='money'}">{{ item.account_type == 'money' ? '提' : item.account_data > 0 ? '收' : '支' }}</view>
                        <view class="flex flex-col ml-[20rpx]">
                            <view class="text-[#333] text-[28rpx] leading-[36rpx]">{{ item.from_type_name }}</view>
                            <view class="text-[var(--text-color-light9)] text-[24rpx] mt-[12rpx]">{{ item.create_time }}</view>
                        </view>
                    </view>
                    <view class="text-right">
                        <view class="text-[36rpx] leading-[40rpx] price-font"
                              :class="{'text-[#EF000C]' :item.account_data > 0&&item.account_type!='money', 'text-[#03B521]':item.account_data <= 0&&item.account_type!='money'}">{{ item.account_data > 0 ? '+' + item.account_data : item.account_data }}</view>
                    </view>
                </view>
            </view>
            <mescroll-empty v-if="!list.length && !loading &&!listLoading"></mescroll-empty>

        </mescroll-body>
        <loading-page :loading="loading"></loading-page>
        <!-- <pay ref="payRef" @close="rechargeLoading = false"></pay> -->
        <!-- 时间选择 -->
        <select-date ref="selectDateRef" @confirm="confirmFn" />
    </view>
</template>

<script setup lang="ts">
import { ref, reactive, computed, watch } from 'vue'
import { t } from '@/locale'
import { moneyFormat, redirect, img, pxToRpx } from '@/utils/common';
import { cashOutConfig, getBalanceListAll, rechargeConfig } from '@/app/api/member';
import MescrollBody from '@/components/mescroll/mescroll-body/mescroll-body.vue';
import MescrollEmpty from '@/components/mescroll/mescroll-empty/mescroll-empty.vue';
import useMescroll from '@/components/mescroll/hooks/useMescroll.js';
import { onShow, onPageScroll, onReachBottom } from '@dcloudio/uni-app';
import useMemberStore from '@/stores/member'
import useSystemStore from '@/stores/system'
import { topTabar } from '@/utils/topTabbar'
import selectDate from '@/components/select-date/select-date.vue';

const { downCallback, mescrollInit, getMescroll } = useMescroll(onPageScroll, onReachBottom);
const memberStore = useMemberStore()
const systemStore = useSystemStore()

/********* 自定义头部 - start ***********/
const topTabarObj = topTabar()
let param = topTabarObj.setTopTabbarParam({ title: '我的余额' })
/********* 自定义头部 - end ***********/

const cashOutConfigObj: any = reactive({})
const rechargeConfigObj: any = reactive({})

// 监听 siteAddons 变化
watch(
    () => systemStore.siteAddons,
    (newAddons, oldAddons) => {
        if (newAddons !== oldAddons) {
            systemStore.siteAddons = newAddons
            if (systemStore.siteAddons.includes('recharge')) {
                rechargeConfig().then((res: any) => {
                    for (let key in res.data) {
                        rechargeConfigObj[key] = res.data[key];
                    }
                })
            }
        }
    }
);

onShow(() => {
    cashOutConfig().then((res: any) => {
        for (let key in res.data) {
            cashOutConfigObj[key] = res.data[key];
        }

    })
    if (systemStore.siteAddons.includes('recharge')) {
        rechargeConfig().then((res: any) => {
            for (let key in res.data) {
                rechargeConfigObj[key] = res.data[key];
            }
        })
    }
})


// 获取系统状态栏的高度
let menuButtonInfo: any = {};
// 如果是小程序，获取右上角胶囊的尺寸信息，避免导航栏右侧内容与胶囊重叠(支付宝小程序非本API，尚未兼容)
// #ifdef MP-WEIXIN || MP-BAIDU || MP-TOUTIAO || MP-QQ
menuButtonInfo = uni.getMenuButtonBoundingClientRect();
// #endif
const headerStyle = computed(() => {
    return {
        backgroundImage: 'url(' + img('static/resource/images/member/balance_bg.png') + ') ',
        backgroundSize: 'cover',
        backgroundRepeat: 'no-repeat',
        backgroundPosition: 'bottom',
    }
})

const mescrollTop = computed(() => {
    if ((cashOutConfigObj.is_open == 1 || rechargeConfigObj.is_use == 1)) {
        if (Object.keys(menuButtonInfo).length) {
            return (pxToRpx(Number(menuButtonInfo.height)) + pxToRpx(menuButtonInfo.top) + pxToRpx(8) + 700) + 'rpx'
        } else {
            return '718rpx'
        }
    } else {
        if (Object.keys(menuButtonInfo).length) {
            return (pxToRpx(Number(menuButtonInfo.height)) + pxToRpx(menuButtonInfo.top) + pxToRpx(8) + 632) + 'rpx'
        } else {
            return '650rpx'
        }
    }
})

//获取数据来源类型
const accountTypeList = ref([
    { name: '全部', key: '' },
    { name: '收入', key: 'income' },
    { name: '支出', key: 'disburse' },
    { name: '提现', key: 'cash_out' },
])
const fromType = ref('')
const create_time = ref([])
//来源类型
const subActive = ref(0)
const fromTypeFn = (key: any, index: any) => {
    fromType.value = key
    subActive.value = index
    getMescroll().resetUpScroll();
}

const applyCashOut = () => {
    uni.setStorageSync('cashOutAccountType', 'money')
    redirect({ url: '/app/pages/member/apply_cash_out' })
}

const list = ref<Array<any>>([]),
    loading = ref<boolean>(true),
    listLoading = ref<boolean>(true),
    mescrollRef = ref(null);

interface mescrollStructure {
    num: number,
    size: number,
    endSuccess: Function,

    [propName: string]: any
}

const getListFn = (mescroll: mescrollStructure) => {
    listLoading.value = true;
    let data: Object = {
        page: mescroll.num,
        limit: mescroll.size,
        trade_type: fromType.value,
        create_time: create_time.value
    };

    getBalanceListAll(data).then((res: any) => {
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

<style lang="scss" scoped>
:deep(.uni-scroll-view) {
    overflow: auto hidden !important;
}

.body-bottom {
    padding-bottom: calc(20rpx + constant(safe-area-inset-bottom));
    padding-bottom: calc(20rpx + env(safe-area-inset-bottom));
}

.pl-20rpx {
    padding-left: 20rpx;
}

</style>

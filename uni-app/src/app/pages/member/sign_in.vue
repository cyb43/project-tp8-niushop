<template>
    <view :style="themeColor()">
        <view class="min-h-screen overflow-hidden" v-if="Object.values(info).length" :class="{ 'bg-[#F6F6F6]' : info && info.is_use }">
            <view v-if="info.is_use">
                <view class="sigin-header">
                    <!-- #ifdef MP-WEIXIN -->
                    <view v-if="info.rule_explain" class="side-tab" :style="{top: topStyle}" @click="signPopup = true">
                        <text class="nc-iconfont nc-icon-a-meiriqiandaoV6xx-36  icon"></text>
                        <text class="desc">签到规则</text>
                    </view>
                    <view :style="{height: headStyle, backgroundImage: 'url(' + img('static/resource/images/app/sigin_uniapp.png') + ')',backgroundSize: '100% 100%', backgroundRepeat: 'no-repeat'}">
                        <top-tabbar :data="topTabbarData" :scrollBool="topTabarObj.getScrollBool()" class="top-header" />
                    </view>
                    <!-- #endif -->
                    <!-- #ifdef H5 -->
                    <view v-if="info.rule_explain" class="side-tab" @click="signPopup = true">
                        <text class="nc-iconfont nc-icon-a-meiriqiandaoV6xx-36 icon"></text>
                        <text class="desc">签到规则</text>
                    </view>
                    <view class="h-[382rpx]" :style="{ backgroundImage: 'url(' + img('static/resource/images/app/sigin_h5.png') + ')',backgroundSize: '100%', backgroundRepeat: 'no-repeat'}"></view>
                    <!-- #endif -->
                </view>
                <view>
                    <view class="sidebar-margin bg-[#fff] rounded-[16rpx] -mt-[85rpx]">
                        <view class="card-template">
                            <view class="mb-[30rpx] flex justify-between items-center" v-if="flag">
                                <view class="flex items-center">
                                    <text class="iconfont iconshangyibu text-[#303133] text-[20rpx]" @click="changeMonth('prev')"></text>
                                    <view class="mx-[24rpx] text-[30rpx] font-500 text-[#303133] leading-[45rpx]">{{ state.curYear }}年{{ state.curMonth + 1 }}月</view>
                                    <text class="iconfont iconxiayibu1 text-[#303133] text-[20rpx]" @click="changeMonth('next')"></text>
                                </view>
                                <view class="flex items-center">
                                    <text class="nc-iconfont nc-icon-shangV6xx-1 text-[var(--text-color-light9)] pt-[4rpx] px-[6rpx] text-[24rpx]" @click="handleChange"></text>
                                </view>
                            </view>
                            <view class="mb-[30rpx] flex justify-between items-center" v-else>
                                <view class="font-500 text-[30rpx]">已连续签到<text class="text-[#EF000C] mx-[4rpx]">{{ info.days }}</text>天</view>
                                <text class="nc-iconfont nc-icon-xiaV6xx pt-[4rpx] px-[6rpx] text-[var(--text-color-light9)] text-[24rpx]" v-if="!flag" @click="flag = !flag"></text>
                            </view>
                            <view class="relative z-9 bg-[#fff] rounded-[18rpx]">
                                <view>
                                    <view class="flex items-center justify-between text-[var(--text-color-light9)] text-[24rpx] mb-[16rpx]">
                                        <text class="w-[14.28%] leading-[36rpx] text-center">周一</text>
                                        <text class="w-[14.28%] leading-[36rpx] text-center">周二</text>
                                        <text class="w-[14.28%] leading-[36rpx] text-center">周三</text>
                                        <text class="w-[14.28%] leading-[36rpx] text-center">周四</text>
                                        <text class="w-[14.28%] leading-[36rpx] text-center">周五</text>
                                        <text class="w-[14.28%] leading-[36rpx] text-center">周六</text>
                                        <text class="w-[14.28%] leading-[36rpx] text-center">周日</text>
                                    </view>
                                    <view class="flex flex-wrap items-center justify-start" v-if="!flag">
                                        <template v-for="(item,index) in state.weekCount" :key="index">
                                            <view class="w-[14.28%] flex flex-col justify-center items-center">
                                                <view v-if="filteredDate(item)"
                                                      class="w-[74rpx] h-[92rpx] bg-[#f4f4f4] text-[var(--text-color-light6)] box-border py-[10rpx] rounded-[8rpx] flex flex-col  items-center"
                                                      :class="{'sigin-bg !text-[#fff]': isVerDate(item),'!bg-[#f9f9f9] border-[1rpx] !text-[var(--text-color-light9)] border-[#f5f5f5] border-solid': !isVerDate(item) && item < state.curDate && (state.curMonth + 1) == (new Date().getMonth() + 1) ,'mb-[20rpx]':isCurrentDate(item),'mb-[30rpx]':!isCurrentDate(item)}"
                                                      @click="getDayPackFn(item)">
                                                    <text class="text-[24rpx] leading-[28rpx] mb-[6rpx]">{{ filteredDate(item) }}</text>
                                                    <view v-if="filteredDate(item)" class="flex items-center justufy-center">
                                                        <image v-if="isPackDate(item)" :src="img('static/resource/images/app/package.png')" class="w-[40rpx] h-[40rpx]"/>
                                                        <image v-else-if="isVerDate(item)" :src="img('static/resource/images/app/hassigin.png')" class="w-[34rpx] h-[34rpx]"/>
                                                        <template v-else>
                                                            <image v-if="!isVerDate(item) && item < state.curDate && (state.curMonth + 1) == (new Date().getMonth() + 1)"
                                                                :src="img('static/resource/images/app/nosigin.png')" class="w-[34rpx] h-[34rpx]"/>
                                                            <image v-else :src="img('static/resource/images/app/nosigin1.png')"
                                                                   class="w-[34rpx] h-[34rpx]"/>
                                                        </template>
                                                    </view>
                                                </view>
                                                <view class="w-[10rpx] h-[10rpx] rounded-[50%] bg-[var(--primary-color)]" v-if="isCurrentDate(item)"></view>
                                            </view>
                                        </template>
                                    </view>
                                    <view class="flex flex-wrap items-center justify-start" v-else>
                                        <template v-for="(item,index) in state.dataCount">
                                            <view class="w-[14.28%] flex flex-col justify-center items-center mb-[30rpx]">
                                                <view v-if="filteredDate(item)"
                                                      class="w-[74rpx] h-[92rpx] bg-[#F6FAFF] text-[var(--text-color-light6)] box-border py-[10rpx] rounded-[8rpx] flex flex-col  items-center"
                                                      :class="{'sigin-bg !text-[#fff]': isVerDate(item) && active ,'!bg-[#FDFDFD] border-[1rpx] border-[#F0F4FA] border-solid': !isVerDate(item) && item < state.curDate && (state.curMonth + 1) == (new Date().getMonth() + 1) && state.curYear == new Date().getFullYear() ,'mb-[20rpx]':isCurrentDate(item),'mb-[30rpx]':!isCurrentDate(item)}"
                                                      @click="getDayPackFn(item)">
                                                    <text class="text-[24rpx] leading-[28rpx] mb-[6rpx]">{{ filteredDate(item) }}</text>
                                                    <view v-if="filteredDate(item)" class="flex items-center justufy-center">
                                                        <image v-if="isPackDate(item)" :src="img('static/resource/images/app/package.png')" class="w-[40rpx] h-[40rpx]" />
                                                        <image v-else-if="isVerDate(item) && active " :src="img('static/resource/images/app/hassigin.png')" class="w-[34rpx] h-[34rpx]"/>
                                                        <template v-else>
                                                            <image v-if="!isVerDate(item) && item < state.curDate && (state.curMonth + 1) == (new Date().getMonth() + 1)"
                                                                :src="img('static/resource/images/app/nosigin.png')" class="w-[34rpx] h-[34rpx]"/>
                                                            <image v-else :src="img('static/resource/images/app/nosigin1.png')" class="w-[34rpx] h-[34rpx]"/>
                                                        </template>
                                                    </view>
                                                </view>
                                                <view class="w-[10rpx] h-[10rpx] rounded-[50%] bg-[var(--primary-color)]" v-if="isCurrentDate(item)"></view>
                                            </view>
                                        </template>
                                    </view>
                                    <view class="mt-[40rpx] flex justify-center" v-if="state.curMonth + 1 == (new Date().getMonth() + 1) && state.curYear == new Date().getFullYear() ">
                                        <button v-if="!info.is_sign"
                                                class="rounded-[40rpx] flex-center !bg-transparent text-[26rpx] font-500"
                                                :style="{width:'490rpx',height:'80rpx',border:'none', color:'#fff',backgroundImage: `url(${img('static/resource/images/app/button_bg2.png')})`,backgroundSize: '100%', backgroundRepeat: 'no-repeat'}"
                                                shape="circle" @click="setSignFn">
                                            <text class="nc-iconfont nc-icon-a-meiriqiandaoV6xx-36 text-[26rpx] text-[#fff] mr-[8rpx]"></text>
                                            <text>立即签到</text>
                                        </button>
                                        <button v-else class="rounded-[40rpx] flex-center !bg-transparent text-[26rpx] font-500"
                                                :style="{width:'490rpx',height:'80rpx',border:'none',color:'#fff', backgroundImage: `url(${img('static/resource/images/app/button_bg1.png')})`,backgroundSize: '100%', backgroundRepeat: 'no-repeat'}"
                                                shape="circle">
                                            <text class="nc-iconfont nc-icon-a-meiriqiandaoV6xx-36 text-[26rpx] text-[#fff] mr-[8rpx]"></text>
                                            <text>已签到</text>
                                        </button>
                                    </view>
                                </view>
                            </view>
                        </view>
                    </view>

                    <view class="mt-[20rpx] mb-[30rpx] sidebar-margin card-template" v-if="info && info.continue_award && Object.keys(info.continue_award).length">
                        <view class="mb-[30rpx] flex items-center">
                            <view class="font-500 text-[30rpx] text-[#303133]">签到奖励</view>
                            <!-- <view class="text-[var(--text-color-light6)] text-[26rpx] leading-[30rpx]">
                                <text>签到记录</text>
                                <image :src="img('static/resource/images/app/more.png')" class="w-[12rpx] h-[18rpx] ml-[8rpx]" />
                            </view> -->
                        </view>
                        <view>
                            <view v-for="(item,index) in info.continue_award" :key="index" class="flex items-center border-box" :class="{'mt-[40rpx]':index}">
                                <view class="w-[90rpx] h-[90rpx] rounded-[50%] bg-[#E7F6FF] flex items-center justify-center  flex-shrink-0" v-if="(index + 1) % 4 == 1">
                                    <image :src="img('static/resource/images/app/icon_02.png')" class="w-[40rpx] h-[40rpx]"/>
                                </view>
                                <view class="w-[90rpx] h-[90rpx] rounded-[50%] bg-[#ffefef] flex items-center justify-center flex-shrink-0" v-else-if="(index + 1) % 4 == 2">
                                    <image :src="img('static/resource/images/app/icon_03.png')" class="w-[40rpx] h-[40rpx]"/>
                                </view>
                                <view class="w-[90rpx] h-[90rpx] rounded-[50%] bg-[#d3feeb] flex items-center justify-center flex-shrink-0" v-else-if="(index + 1) % 4 == 3">
                                    <image :src="img('static/resource/images/app/icon_04.png')" class="w-[40rpx] h-[40rpx]"/>
                                </view>
                                <view class="w-[90rpx] h-[90rpx] rounded-[50%] bg-[#ffeddd] flex items-center justify-center flex-shrink-0" v-else-if="(index + 1) % 4 == 0">
                                    <image :src="img('static/resource/images/app/icon_05.png')" class="w-[40rpx] h-[40rpx]"/>
                                </view>
                                <view class="flex-1 mx-[20rpx]">
                                    <view class="font-400 text-[28rpx] text-[#303133] leading-[38rpx] mb-[10rpx]">连续签到{{ item.continue_sign }}天</view>
                                    <view class="flex flex-wrap" v-if="item.gift && item.gift.total">
                                        <view class="flex">
                                            <image :src="img(item.gift.total.icon)" class="w-[30rpx] h-[30rpx] flex-shrink-0"/>
                                            <view class="text-[24rpx] ml-[8rpx] text-[#FF9000] leading-[34rpx] max-w-[330rpx]">{{ item.gift.total.text }}</view>
                                        </view>
                                    </view>
                                </view>
                                <view class="flex-shrink-0">
                                    <view v-if="Number(info.days) < Number(item.continue_sign) " class="w-[130rpx] h-[54rpx] text-center bg-primary-light rounded-[28rpx] text-[22rpx] text-[var(--primary-color)] flex-center font-500">待完成</view>
                                    <view v-else class="primary-btn-bg w-[130rpx] h-[54rpx] text-center rounded-[27rpx] text-[22rpx] text-[#fff] flex-center font-500">已完成</view>
                                </view>
                            </view>
                        </view>
                    </view>
                </view>
            </view>
            <view class="h-[100vh] w-[100vw] flex justify-center items-center" v-else>
                <!-- #ifdef MP-WEIXIN -->
                <top-tabbar :data="topTabbarData" :scrollBool="topTabarObj.getScrollBool()" class="top-header" />
                <!-- #endif -->
                <u-empty text="签到未开启" width="347rpx" height="265rpx" :icon="img('static/resource/images/system/empty.png')" />
            </view>
            <!-- 签到规则-->
            <u-popup :show="signPopup" :round="16" mode="bottom" @close="signPopup = false">
                <view class="popup-common">
                    <view class="title">签到规则</view>
                    <scroll-view :scroll-y="true" class="px-[30rpx] box-border h-[360rpx] overflow-auto">
                        <template v-for="(item) in info.rule_explain.split('\n')">
                            <view class="text-[28rpx] leading-[40rpx] mb-[20rpx]">{{ item }}</view>
                        </template>
                    </scroll-view>
                    <view class="btn-wrap">
                        <button class="primary-btn-bg btn" @click="signPopup = false">知道了</button>
                    </view>
                </view>
            </u-popup>
            <!-- 签到奖励 -->
            <u-popup :show="awardShow" class="award-popup overflow-hidden"
                     :customStyle="{backgroundColor:'transparent'}" @close="awardShow = false" mode="center"
                     round="var(--rounded-big)" :safeAreaInsetBottom="false">
                <view class="w-[550rpx] -mt-[124rpx]" v-if="Object.values(signAward).length">
                    <view class="flex justify-center">
                        <image :src="img('static/resource/images/app/award.png')" class="w-[484rpx] h-[480rpx] z-10" mode="aspectFill"/>
                    </view>
                    <view class="-mt-[265rpx] bg-award rounded-[40rpx] pt-[100rpx] pb-[50rpx] mb-[50rpx] relative">
                        <view class="px-[32rpx]">
                            <view class="text-[36rpx] text-[#EF000C] font-500 mb-[10rpx] text-center">{{ signAward.title }}</view>
                            <view class="text-[24rpx] text-[#333] leading-[34rpx] text-center mb-[60rpx]" v-if="signAward.info">{{ signAward.info }}</view>
                            <view class="px-[68rpx] mb-[100rpx]">
                                <template v-for="(item,index) in signAward.awards">
                                    <template v-if="item.content" v-for="(subItem,subIndex) in item.content">
                                        <view class="flex items-center mb-[30rpx]">
                                            <image :src="img(subItem.icon)" class="w-[42rpx] h-[42rpx]" />
                                            <view class="ml-[20rpx] text-[28rpx] text-[#303133] leading-[38rpx]">{{ subItem.text }}</view>
                                        </view>
                                    </template>
                                </template>
                            </view>
                            <view class="flex justify-center relative z-30">
                                <view class="w-[370rpx] h-[80rpx] primary-btn-bg font-500 rounded-[100rpx] text-[#ffffff] text-center leading-[80rpx] text-[26rpx]" @click="awardShow = false">我知道了</view>
                            </view>
                        </view>
                    </view>
                    <view class="flex justify-center">
                        <text class="nc-iconfont nc-icon-cuohaoV6xx1 text-[#fff] text-[50rpx]" @click="awardShow = false"></text>
                    </view>
                </view>
            </u-popup>
            <!-- 查看当日或连续签到奖励 -->
            <u-popup :show="packShow" class="award-popup overflow-hidden" :customStyle="{backgroundColor:'transparent'}" @close="packShow = false" mode="center" round="var(--rounded-big)" :safeAreaInsetBottom="false">
                <view class="w-[550rpx] -mt-[124rpx]" v-if="Object.values(packInfo).length">
                    <view class="flex justify-center">
                        <image :src="img('static/resource/images/app/award.png')" class="w-[484rpx] h-[480rpx] z-10" mode="aspectFill"/>
                    </view>
                    <view class="-mt-[265rpx] bg-award rounded-[40rpx] pt-[100rpx] pb-[50rpx] mb-[50rpx] relative">
                        <view class="px-[32rpx]">
                            <view class="text-[36rpx] text-[#303133] font-500 mb-[10rpx] text-center relative z-20">{{ packInfo.title }}</view>
                            <view class="text-[24rpx] text-[#333] leading-[34rpx] text-center mb-[60rpx]">{{ packInfo.info }}</view>
                            <view class="px-[68rpx] mb-[100rpx]">
                                <template v-for="(item,index) in packInfo.awards">
                                    <template v-if="item.content">
                                        <view class="flex items-center mb-[32rpx]" v-for="(subItem,subIndex)  in item.content" :key="subIndex">
                                            <image :src="img(subItem.icon)" class="w-[42rpx] h-[42rpx]" />
                                            <view class="ml-[20rpx] text-[28rpx] text-[#303133] leading-[38rpx]">{{ subItem.text }}</view>
                                        </view>
                                    </template>
                                </template>
                            </view>
                            <view class="flex justify-center  relative z-30">
                                <view class="w-[370rpx] h-[80rpx] border-[2rpx] text-[var(--primary-color)] border-solid rounded-[40rpx] border-[var(--primary-color)] text-center flex-center text-[26rpx] box-border" @click="packShow = false">我知道了</view>
                            </view>
                        </view>
                    </view>
                    <view class="flex justify-center">
                        <text class="nc-iconfont nc-icon-cuohaoV6xx1 text-[#fff] text-[50rpx]" @click="packShow = false"></text>
                    </view>
                </view>
            </u-popup>
        </view>
        <loading-page :loading="loading"></loading-page>
    </view>
</template>

<script setup lang="ts">
import { reactive, ref, toRefs, toRaw, computed } from 'vue'
import { redirect, img, pxToRpx } from '@/utils/common'
import { onLoad } from '@dcloudio/uni-app'
import { getSignInfo, getSignConfig, setSign, getDayPack } from '@/app/api/member'
import useMemberStore from '@/stores/member'
import { topTabar } from '@/utils/topTabbar'

const state = reactive({
    dataCount: [], //当月所有天数
    weekCount: [], //如果签到周期是7天
    curYear: 0, // 当前年
    curMonth: 0, //当前月
    curDate: 0, //当前日
    curWeek: 0, //当前星期
    signInList: [], // 签到列表
    packList: [] //每个小周期内的礼包
})
const week = reactive({
    weekDay: 0, //当前天
    week: 0 //当前星期
})
const loading = ref(false)
const flag = ref(false)
const info: any = ref({}) //签到配置
const signPopup = ref(false)//签到规则弹框
const signAward: any = ref({}) //当日签到奖励
const awardShow = ref(false) // 每日签到弹框
const packShow = ref(false) //查看每天礼包
const packInfo = ref({}) //礼包奖励
const active = ref(false)
let currentYear: any = null
let currentMonth: any = null
onLoad(() => {
    let date = new Date()
    state.curYear = date.getFullYear()
    state.curMonth = date.getMonth()
    state.curDate = date.getDate()
    state.curWeek = date.getDay()
    if (state.curWeek == 0) state.curWeek = 7

    currentYear = toRaw(state.curYear)
    currentMonth = toRaw(state.curMonth)

    //初始化执行
    getDayCounts()
    getWeekCounts()
    getSignInfoFn({ year: state.curYear, month: state.curMonth + 1 })
    getSignConfigFn()
})

// 获取签到配置
const getSignConfigFn = () => {
    loading.value = true
    getSignConfig().then((res: any) => {
        info.value = res.data
        if (!info.value.is_use) {
            topTabbarData = topTabarObj.setTopTabbarParam({
                title: '我的签到',
                topStatusBar: { textColor: '#303133', bgColor: '#fff' }
            })
        }
        loading.value = false
    })
}

// 每个周期签到日期
const getSignInfoFn = (data: any) => {
    getSignInfo(data).then((res: any) => {
        state.signInList = []
        state.packList = []
        state.packList = res.data.period
        if (res.data.length) {
            state.signInList = res.data.days.map((el: any) => {
                return Number(el)
            })
        }

        active.value = true
    })
}

//获取当月总天数
const getDayCounts = () => {
    let counts = new Date(state.curYear, state.curMonth + 1, 0).getDate()
    //获取当前第一天是星期几
    let firstWeekDay = new Date(state.curYear, state.curMonth, 1).getDay()
    state.dataCount = []
    for (let i = 1; i < counts + firstWeekDay; i++) {
        let val = i - firstWeekDay + 1
        state.dataCount.push(val)
    }
}
// 获取7天的日期签到
const getWeekCounts = () => {
    let now = `${ state.curYear }-${ state.curMonth + 1 > 9 ? state.curMonth + 1 : '0' + (state.curMonth + 1) }-${ state.curDate > 9 ? state.curDate : '0' + state.curDate }`
    for (let i = state.curWeek - 1; i >= 0; i--) {
        const day = new Date(now).getDate() - i
        state.weekCount.push(day)
    }
    for (let i = 1; i <= 7 - state.curWeek; i++) {
        const day = new Date(now).getDate() + i
        state.weekCount.push(day)
    }
}

//控制状态
const handleChange = () => {
    let nowDate = new Date().getMonth()

    if (state.curMonth == nowDate) {
        flag.value = !flag.value
    } else {
        state.curMonth = new Date().getMonth()
        state.curYear = new Date().getFullYear()
        getSignInfoFn({ year: state.curYear, month: state.curMonth + 1 })
        flag.value = !flag.value
    }
}

//更改月份
const changeMonth = (type: string) => {
    state.dataCount = []
    if (type == 'prev') {
        state.curMonth--
        if (state.curMonth < 0) {
            state.curMonth = 11
            state.curYear--
        }
        week.weekDay = 1
        active.value = false
    } else {
        state.curMonth++
        if (state.curMonth > 11) {
            state.curMonth = 0
            state.curYear++
        }
        week.weekDay = 1
        active.value = false
    }
    let data = { year: state.curYear, month: state.curMonth + 1 }
    getSignInfoFn(data)
    getDayCounts()
}

const memberStore = useMemberStore()

// 点击签到
const setSignFn = () => {
    setSign().then((res: any) => {
        if (Object.values(res.data).length) {
            signAward.value = res.data
            // 如果没有奖励内容则不展示描述信息
            let isShowInfo = 0
            Object.values(signAward.value.awards).forEach((item: any, index) => {
                if (!item.content) {
                    isShowInfo++;
                }
            })
            if (isShowInfo == Object.values(signAward.value.awards).length) {
                signAward.value.info = "";
            }
            getSignInfoFn({ year: state.curYear, month: state.curMonth + 1 })
            getSignConfigFn()
            memberStore.getMemberInfo()
            awardShow.value = true
        }
    })
}
// 查看每日礼包
const curPickDay: any = ref(null)
const getDayPackFn = (date: number) => {
    let { curYear, curMonth } = toRefs(state)
    let itemDate = `${ curYear.value }-${ (curMonth.value + 1) < 10 ? '0' + (curMonth.value + 1) : (curMonth.value + 1) }-${ date < 10 ? '0' + date : date }`
    let flag = state.packList.some(el => {
        return el.day == itemDate
    })
    if (!flag) return
    curPickDay.value = date
    let obj = {
        year: state.curYear,
        month: state.curMonth + 1,
        day: date
    }
    getDayPack(obj).then((res: any) => {
        if (JSON.stringify(res.data) != '[]') {
            packInfo.value = res.data
            packShow.value = true
        }
    })
}

// 判断是否签到
const isVerDate = (val: any) => {
    return state.signInList.includes(val)
}

//判断是否是当前日期
const isCurrentDate = (date: any) => {
    if (date > 0 && date <= state.dataCount.length) {
        if (date == state.curDate && currentYear == state.curYear && currentMonth == state.curMonth) {
            return true
        }
    } else {
        return false
    }
}

const isPackDate = (date: any) => {
    let { curYear, curMonth } = toRefs(state)
    let itemDate = `${ curYear.value }-${ (curMonth.value + 1) < 10 ? '0' + (curMonth.value + 1) : (curMonth.value + 1) }-${ date < 10 ? '0' + date : date }`
    let flag = state.packList.some((el: any) => {
        return el.day == itemDate && el.award
    })
    return flag
}

// 过滤日期
const filteredDate = (date: any) => {
    return date > 0 ? date : ''
}

// 获取系统状态栏的高度
let menuButtonInfo: any = {};
// 如果是小程序，获取右上角胶囊的尺寸信息，避免导航栏右侧内容与胶囊重叠(支付宝小程序非本API，尚未兼容)
// #ifdef MP-WEIXIN || MP-BAIDU || MP-TOUTIAO || MP-QQ
menuButtonInfo = uni.getMenuButtonBoundingClientRect();
// #endif
/********* 自定义头部 - start ***********/
const topTabarObj = topTabar()
let topTabbarData = topTabarObj.setTopTabbarParam({ title: '我的签到' })
/********* 自定义头部 - end ***********/

const headStyle = computed(() => {
    let style = pxToRpx(Number(menuButtonInfo.height) + menuButtonInfo.top + 8) + 382 + 'rpx;'
    return style
})
const topStyle = computed(() => {
    let style = pxToRpx(Number(menuButtonInfo.height) + menuButtonInfo.top + 8) + 50 + 'rpx;'
    return style
})
</script>

<style lang="scss" scoped>
.bg-color {
    background-color: rgba(102, 102, 102, 0.4);
}

.sigin-bg {
    background: linear-gradient(90deg, #FFA359 0%, #FF5426 100%), #F2F2F2;
}

:deep(.award-popup .u-popup__content) {
    background-color: transparent;
}

.bg-award {
    background: linear-gradient(51deg, #FFFBFC 0%, #FFFCF9 59%, #FFE7E7 100%);
}

.bg-button {
    background: linear-gradient(180deg, #FFEAE1 0%, #FFCDD0 34%, #E0052C 100%);
    border-radius: 40rpx;
}
</style>

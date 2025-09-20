<template>
    <view class="min-h-[100vh] bg-[#f6f6f6] overflow-hidden" :style="themeColor()">
        <template v-if="Object.keys(configInfo).length &&configInfo.active_status =='active'">
            <mescroll-body ref="mescrollRef" @init="mescrollInit" :down="{ use: false }" @up="getGoodsListFn">
                <view class="marketing-head" v-if="!pageLoading">
                    <!-- #ifdef MP-WEIXIN || APP-PLUS -->
                    <top-tabbar :data="param" :isFill="false" class="top-header" />
                    <image v-if="configInfo.banner_list && configInfo.banner_list.length" class="w-[100%] h-[434rpx]"
                           :src="img(configInfo.banner_list[0].imageUrl)" mode="aspectFill"
                           @click="diyStore.toRedirect(configInfo.banner_list[0].toLink)"/>
                    <!-- 占位作用 -->
                    <view v-else class="w-[100%] h-[434rpx]"></view>
                    <view v-if="configInfo.active_desc" class="side-tab" :style="{top: topStyle}" @click="newcomerPopup = true">
                        <text class="iconfont iconbiaoqianV6xx2 icon"></text>
                        <text class="desc">活动规则</text>
                    </view>
                    <!-- #endif -->
                    <!-- #ifdef H5 -->
                    <image v-if="configInfo.banner_list && configInfo.banner_list.length"
                           class="w-[100%] h-[434rpx] -mt-[130rpx]" :src="img(configInfo.banner_list[0].imageUrl)"
                           mode="aspectFill" @click="diyStore.toRedirect(configInfo.banner_list[0].toLink)"/>
                    <!-- 占位作用 -->
                    <view v-else class="w-[100%] h-[434rpx] -mt-[130rpx]"></view>
                    <view v-if="configInfo.active_desc" class="side-tab !top-[30rpx]" @click="newcomerPopup = true">
                        <text class="iconfont iconxinrenV6xx icon"></text>
                        <text class="desc">活动规则</text>
                    </view>
                    <!-- #endif -->
                    <view class="time newcomer-time" v-if="newcomerTime > 0 && newcomerTime != undefined" :style="{'background-image': 'url('+ img('addon/shop/newcomer/time_bg.png') +')'}">
                        <text class="text-[26rpx]">距结束还有：</text>
                        <up-count-down class="text-[#fff] min-w-[150rpx] text-[28rpx]" :time="newcomerTime" format="DD:HH:mm:ss" @change="onChange">
                            <view class="flex">
                                <view class="text-[24rpx] flex items-center" v-if="timeData.days && timeData.days>0">
                                    <text class="min-w-[30rpx] text-right">{{ timeData.days }}</text>
                                    <text class="text-[20rpx]">天</text>
                                </view>
                                <view class="text-[24rpx] flex items-center">
                                    <text class="min-w-[30rpx] text-center" v-if="timeData.hours">{{ timeData.hours >= 10 ? timeData.hours : '0' + timeData.hours }}</text>
                                    <text class="min-w-[30rpx] text-center" v-else>00</text>
                                    <text class="text-[20rpx]">时</text>
                                </view>
                                <view class="text-[24rpx] flex items-center">
                                    <text class="min-w-[30rpx] text-center" v-if="timeData.minutes">{{ timeData.minutes >= 10 ? timeData.minutes : '0' + timeData.minutes }}</text>
                                    <text class="min-w-[30rpx] text-center" v-else>00</text>
                                    <text class="text-[20rpx]">分</text>
                                </view>
                                <view class="text-[24rpx] flex items-center">
                                    <text class="min-w-[30rpx] text-center" v-if="timeData.seconds">{{ timeData.seconds < 10 ? '0' + timeData.seconds : timeData.seconds }}</text>
                                    <text class="min-w-[30rpx] text-center" v-else>00</text>
                                    <text class="text-[20rpx]">秒</text>
                                </view>
                            </view>
                        </up-count-down>
                    </view>
                </view>
                <view class="marketing-list p-[20rpx] relative -mt-[50rpx]" v-if="goodsList.length">
                    <view class="bg-[#fff] flex rounded-[var(--rounded-mid)] p-[20rpx]"
                          v-for="(item,index) in goodsList" :key="index"
                          :class="{'mb-[20rpx]': (goodsList.length-1) != index}" @click="toDetail(item)">
                        <view class="w-[250rpx] h-[250rpx] flex items-center justify-center">
                            <image v-if="item.goods_cover" class="w-[250rpx] h-[250rpx] rounded-[var(--rounded-mid)]"
                                   :src="img(item.goods_cover_thumb_mid)" :mode="'aspectFill'"
                                   @error="item.goods_cover_thumb_mid='static/resource/images/diy/shop_default.jpg'" />
                            <image v-else class="w-[250rpx] h-[250rpx] rounded-[var(--rounded-mid)]"
                                   :src="img('static/resource/images/diy/shop_default.jpg')"
                                   :mode="'aspectFill'"/>
                        </view>
                        <view class="flex flex-col flex-1 ml-[20rpx] pt-[4rpx]">
                            <view class="text-[28rpx] multi-hidden leading-[1.3]">{{ item.goods_name }}</view>
                            <view class="w-[400rpx] flex items-center justify-center">
                                <image class="w-[400rpx] h-[106rpx] mt-[auto] mb-[10rpx]" :src="img('addon/shop/newcomer/subsidy.png')" mode="aspectFit"/>
                            </view>
                            <view class="flex items-center justify-center btn-wrap">
                                <view class="flex items-center text-[#FFF3E0] relative z-4" :style="{'background': 'linear-gradient(to right, #FF8A04 0%, #ff1b1b 84%)'}">
                                    <text class="text-[22rpx]">新人价</text>
                                    <text class="text-[20rpx] ml-[6rpx] mr-[2rpx]">￥</text>
                                    <text class="text-[36rpx] truncate max-w-[160rpx]">{{ parseFloat(item.goodsSku.newcomer_price).toFixed(2) }}</text>
                                </view>
                                <image class="w-[26rpx] h-[54rpx]" :src="img('addon/shop/newcomer/btn_02.png')" mode="heightFix"/>
                                <image class="w-[84rpx] h-[54rpx] relative ml-[-5rpx] z-2" :src="img('addon/shop/newcomer/btn_03.png')" mode="aspectFit" />
                            </view>
                        </view>
                    </view>
                </view>
                <mescroll-empty v-else-if="!goodsList.length && loading" :option="{tip : '暂无商品，请看看其他商品吧！', btnText:'去逛逛'}" @emptyclick="redirect({ url: '/addon/shop/pages/goods/list' })"></mescroll-empty>
            </mescroll-body>
        </template>
        <template v-if="!pageLoading && Object.keys(configInfo).length && configInfo.active_status !='active'">
            <top-tabbar :data="pageNullParam" class="top-header" />
            <mescroll-empty :option="{tip : '活动未开启,请看看其他商品吧！', btnText:'去逛逛'}" @emptyclick="redirect({ url: '/addon/shop/pages/index' })"></mescroll-empty>
        </template>
        <view @touchmove.prevent.stop>
            <u-popup :show="newcomerPopup" @close="closeFn" mode="center" round="var(--rounded-big)">
                <view class="w-[570rpx] px-[32rpx] popup-common center">
                    <view class="title">活动规则</view>
                    <scroll-view :scroll-y="true" class="px-[30rpx] box-border max-h-[260rpx]" v-if="configInfo.active_desc">
                        <template v-for="(item) in configInfo.active_desc.split('\n')">
                            <view class="text-[28rpx] leading-[40rpx] mb-[20rpx]">{{ item }}</view>
                        </template>
                    </scroll-view>
                    <view class="btn-wrap !pt-[40rpx]">
                        <button
                            class="primary-btn-bg w-[480rpx] h-[70rpx] text-[26rpx] leading-[70rpx] rounded-[35rpx] !text-[#fff] font-500"
                            @click="newcomerPopup = false">我知道了</button>
                    </view>
                </view>
            </u-popup>
        </view>
        <loading-page :loading="pageLoading"></loading-page>
    </view>
</template>

<script setup lang="ts">
import { reactive, ref, computed } from 'vue'
import { t } from '@/locale'
import { redirect, img, pxToRpx, timeTurnTimeStamp } from '@/utils/common';
import { getNewcomerGoodsList, getNewcomersConfig } from '@/addon/shop/api/newcomer';
import MescrollBody from '@/components/mescroll/mescroll-body/mescroll-body.vue';
import useMescroll from '@/components/mescroll/hooks/useMescroll.js';
import MescrollEmpty from '@/components/mescroll/mescroll-empty/mescroll-empty.vue'
import { onLoad, onPageScroll, onReachBottom } from '@dcloudio/uni-app';
import useDiyStore from '@/app/stores/diy';
import { topTabar } from '@/utils/topTabbar'
import useSystemStore from "@/stores/system";

const { mescrollInit, downCallback, getMescroll } = useMescroll(onPageScroll, onReachBottom);
const goodsList = ref<Array<any>>([]);
const mescrollRef = ref(null);
const loading = ref<boolean>(false);
const pageLoading = ref<boolean>(true);
const diyStore = useDiyStore();
const systemStore = useSystemStore()
/********* 自定义头部 - start ***********/
const topTabarObj = topTabar()
let param = topTabarObj.setTopTabbarParam({ title: '', topStatusBar: { textColor: '#fff' } })
let pageNullParam = topTabarObj.setTopTabbarParam({ title: '新人专享列表', topStatusBar: { textColor: '#333' } })
const topStyle = computed(() => {
    let style = pxToRpx(Number(systemStore.menuButtonInfo.height) + systemStore.menuButtonInfo.top + 8) + 30 + 'rpx;'
    return style
})
/********* 自定义头部 - end ***********/

/********* 倒计时 - start ***********/
const timeData = ref({});
// 定义 onChange 方法
const onChange = (e) => {
    timeData.value = e;
};
const newcomerTime = ref('');
/********* 倒计时 - end ***********/

/**************** 活动规则 ********************/
const newcomerPopup = ref(false)
const closeFn = () => {
    newcomerPopup.value = false
}
/**************** 活动规则 - end ********************/
onLoad(async(option: any) => {
    getNewcomersConfigFn();
})

const configInfo = ref({});
const getNewcomersConfigFn = () => {
    getNewcomersConfig().then((res: any) => {
        configInfo.value = res.data;
        // let time = configInfo.value && configInfo.value.validity_time ? timeTurnTimeStamp(configInfo.value.validity_time) : 0;
        pageLoading.value = false;
    }).catch(() => {
        pageLoading.value = false;
    })
}

interface mescrollStructure {
    num: number,
    size: number,
    endSuccess: Function,
    [propName: string]: any
}

const isJoin = ref(0) // 是否参与过新人专享活动，1：是，0：否
const getGoodsListFn = (mescroll: mescrollStructure) => {
    loading.value = false;
    let data: object = {
        page: mescroll.num,
        limit: mescroll.size
    };
    getNewcomerGoodsList(data).then((res: any) => {
        isJoin.value = res.data.is_join;
        let newArr = (res.data.data as Array<Object>);
        let now = new Date();
        let timestamp: any = now.getTime();
        newcomerTime.value = Number(res.data.validity_time) * 1000 - timestamp;
        //设置列表数据
        if (Number(mescroll.num) === 1) {
            goodsList.value = []; //如果是第一页需手动制空列表
        }
        goodsList.value = goodsList.value.concat(newArr);
        mescroll.endSuccess(newArr.length);
        loading.value = true;

    }).catch(() => {
        loading.value = true;
        mescroll.endErr(); // 请求失败, 结束加载
    })
}

const toDetail = (data: any) => {
    redirect({
        url: '/addon/shop/pages/goods/detail',
        param: { sku_id: data.goodsSku.sku_id, type: 'newcomer_discount' }
    })
}
</script>

<style lang="scss" scoped>
.marketing-head {
    position: relative;

    .time {
        position: absolute;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 44rpx;
        line-height: 1;
        font-size: 26rpx;
        color: #fff;
        width: 660rpx;
        background-repeat: no-repeat;
        bottom: 84rpx;
        left: 50%;
        transform: translateX(-50%);
        background-size: 100% 100%;
    }
}

.marketing-list {
    // background: linear-gradient(180deg, rgba(255,240,242, 1), rgba(255,255,255, 0) 35%);
    background: linear-gradient(180deg, #FFF2F0 23%, #F6F6F6 92%);
    border-radius: 34rpx 34rpx 0 0;

    .btn-wrap {
        border-radius: 50rpx;
        overflow: hidden;
        align-self: center;
        position: relative;

        view {
            height: 54rpx;
            padding-left: 16rpx;
            padding-right: 30rpx;
        }

        image:nth-of-type(1) {
            position: absolute;
            right: 54rpx;
            top: 0;
            height: 54rpx;
            z-index: 5;
        }
    }
}

/*  #ifdef MP  */
.newcomer-time {
    animation: fadein .15s;
}

/* 进入动画 */
@keyframes fadein {
    0% {
        opacity: 0;
    }
    99% {
        opacity: 0;
    }
    100% {
        opacity: 1;
    }
}

/*  #endif  */
</style>

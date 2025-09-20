<template>
    <view class="shop-newcomer overflow-hidden" :style="warpCss" v-if="list && Object.keys(list).length">
        <view class="style-1 p-[20rpx]" v-if="diyComponent.style.value == 'style-1'">
            <view class="head flex justify-between items-center mb-[16rpx]" @click="toListFn()">
                <image v-if="diyComponent.textImg" class="h-[34rpx] w-[auto]" :src="img(diyComponent.textImg)" mode="heightFix" />
                <view class="time-wrap flex items-center ml-[auto]" v-show="timeData && Object.keys(timeData).length">
                    <text v-if="!getToken() && diyStore.mode != 'decorate'" class="text-[24rpx] font-500" :style="{color: diyComponent.countDown.otherColor}">活动未开始</text>
                    <template v-else-if="activeState()">
                        <text :style="{color: diyComponent.countDown.otherColor}" class="mr-[10rpx] text-[24rpx]">距结束还有</text>
                        <up-count-down class="text-[#fff] text-[28rpx]" :time="newcomerTime" format="HH:mm:ss" @change="onChange">
                            <view class="flex">
                                <view class="text-[24rpx] flex items-center">
                                    <text class="time-num font-500" :style="countDownTextCss" v-if="dayTransitionHours()">{{ dayTransitionHours() }}</text>
                                    <text class="time-num font-500" :style="countDownTextCss" v-else>00</text>
                                    <text :style="{color: diyComponent.countDown.otherColor}" class="text-[20rpx] ml-[6rpx] mr-[7rpx]">:</text>
                                </view>
                                <view class="text-[24rpx] flex items-center">
                                    <text class="time-num font-500" :style="countDownTextCss" v-if="timeData.minutes">{{ timeData.minutes >= 10 ? timeData.minutes : '0' + timeData.minutes }}</text>
                                    <text class="time-num font-500" :style="countDownTextCss" v-else>00</text>
                                    <text :style="{color: diyComponent.countDown.otherColor}" class="text-[20rpx] ml-[6rpx] mr-[7rpx]">:</text>
                                </view>
                                <view class="text-[24rpx] flex items-center">
                                    <text class="time-num font-500" :style="countDownTextCss" v-if="timeData.seconds">{{ timeData.seconds < 10 ? '0' + timeData.seconds : timeData.seconds }}</text>
                                    <text class="time-num font-500" :style="countDownTextCss" v-else>00</text>
                                </view>
                            </view>
                        </up-count-down>
                    </template>
                    <text v-else class="text-[28rpx]" :style="{color: diyComponent.countDown.otherColor}">活动已结束
                    </text>
                </view>
            </view>
            <scroll-view scroll-x="true" class="content">
                <view class="flex">
                    <view class="inline-flex bg-[#fff] p-[16rpx] box-border" :style="commonTempCss()" @click="toDetail(list[0])">
                        <view class="w-[150rpx] h-[150rpx] flex items-center justify-center">
                            <u--image :radius="imageRounded.val" width="150rpx" height="150rpx" :src="img(list[0].sku_image || '')" model="aspectFill">
                                <template #error>
                                    <image class="w-[150rpx] h-[150rpx] overflow-hidden" :style="imageRounded.style" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill" />
                                </template>
                            </u--image>
                        </view>
                        <view class="flex flex-col ml-[20rpx] py-[4rpx] flex-1">
                            <view class="text-[26rpx] w-[200rpx] whitespace-pre-wrap leading-[1.4] multi-hidden" v-if="list[0].goods">{{ list[0].goods.goods_name }}</view>
                            <view class="flex items-center justify-between mt-[auto]">
                                <view class="flex flex-1 items-center">
                                    <text class="text-[20rpx] text-[#FF0000]">￥</text>
                                    <text class="text-[28rpx] font-500 text-[#FF0000] max-w[120rpx] truncate">{{ goodsPrice(list[0]) }}</text>
                                </view>
                                <text class="italic flex items-center justify-center rounded-[40rpx] w-[60rpx] h-[40rpx] leading-1 text-[#fff] font-bold first-btn-bg">抢</text>
                            </view>
                        </view>
                    </view>
                    <template v-for="(item,index) in list" :key="index">
                        <view v-if="index > 0"
                              class="ml-[10rpx] inline-flex flex-col items-center p-[16rpx] bg-[#fff] box-border"
                              :style="commonTempCss()" @click="toDetail(item)">
                            <view class="w-[110rpx] h-[110rpx] flex items-center justify-center">
                                <u--image :radius="imageRounded.val" width="110rpx" height="110rpx" :src="img(item.sku_image || '')" model="aspectFill">
                                    <template #error>
                                        <image class="w-[110rpx] h-[110rpx] overflow-hidden" :style="imageRounded.style" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill" />
                                    </template>
                                </u--image>
                            </view>
                            <view class="flex items-center mt-[auto]">
                                <text class="text-[24rpx] font-500">￥</text>
                                <text class="text-[24rpx] font-500 truncate">{{ goodsPrice(item) }}</text>
                            </view>
                        </view>
                    </template>
                </view>
            </scroll-view>
        </view>
        <view class="style-2 p-[20rpx]" v-if="diyComponent.style.value == 'style-2'">
            <view class="head flex justify-between items-center mb-[16rpx]" @click="toListFn()">
                <image v-if="diyComponent.textImg" class="h-[34rpx] w-[auto]" :src="img(diyComponent.textImg)" mode="heightFix" />
                <view class="time-wrap flex items-center ml-[auto]" v-show="timeData && Object.keys(timeData).length">
                    <text v-if="!getToken() && diyStore.mode != 'decorate'" class="text-[24rpx] font-500" :style="{color: diyComponent.countDown.otherColor}">活动未开始</text>
                    <template v-else-if="activeState()">
                        <text :style="{color: diyComponent.countDown.otherColor}" class="mr-[10rpx] text-[24rpx]">倒计时</text>
                        <up-count-down class="text-[#fff] text-[28rpx]" :time="newcomerTime" format="DD:HH:mm" @change="onChange">
                            <view class="flex">
                                <view class="text-[24rpx] flex items-center">
                                    <text class="time-num" :style="countDownTextCss" v-if="timeData.days">{{ timeData.days }}</text>
                                    <text class="time-num" :style="countDownTextCss" v-else>0</text>
                                    <text :style="{color: diyComponent.countDown.otherColor}" class="text-[22rpx] ml-[6rpx] mr-[7rpx]">天</text>
                                </view>
                                <view class="text-[24rpx] flex items-center">
                                    <text class="time-num" :style="countDownTextCss" v-if="timeData.hours">{{ timeData.hours >= 10 ? timeData.hours : '0' + timeData.hours }}</text>
                                    <text class="time-num" :style="countDownTextCss" v-else>00</text>
                                    <text :style="{color: diyComponent.countDown.otherColor}" class="text-[22rpx] ml-[6rpx] mr-[7rpx]">时</text>
                                </view>
                                <view class="text-[24rpx] flex items-center">
                                    <text class="time-num" :style="countDownTextCss" v-if="timeData.minutes">{{ timeData.minutes >= 10 ? timeData.minutes : '0' + timeData.minutes }}</text>
                                    <text class="time-num" :style="countDownTextCss" v-else>00</text>
                                    <text :style="{color: diyComponent.countDown.otherColor}" class="text-[22rpx] ml-[6rpx] mr-[7rpx]">分</text>
                                </view>
                            </view>
                        </up-count-down>
                    </template>
                    <text v-else class="text-[28rpx]" :style="{color: diyComponent.countDown.otherColor}">活动已结束
                    </text>
                </view>
            </view>
            <scroll-view scroll-x="true" class="content">
                <view class="flex">
                    <view v-for="(item,index) in list" :key="index"
                          class="item-bg mr-[10rpx] inline-flex flex-col items-center p-[6rpx] bg-[#fff] box-border"
                          :style="commonTempCss()" @click="toDetail(item)">
                        <view class="flex items-center justify-center w-[146rpx] h-[146rpx]">
                            <u--image :radius="imageRounded.val" width="146rpx" height="146rpx" :src="img(item.sku_image || '')" model="aspectFill">
                                <template #error>
                                    <image class="w-[146rpx] h-[146rpx] overflow-hidden" :style="imageRounded.style" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill" />
                                </template>
                            </u--image>
                        </view>
                        <image class="h-[32rpx] w-[auto] mt-[12rpx] mb-[8rpx]" :src="img('addon/shop/diy/newcomer/style_2_img.png')" mode="heightFix" />
                        <view class="flex items-center text-[#fff] pb-[4rpx]">
                            <text class="text-[20rpx] font-500">￥</text>
                            <text class="text-[30rpx] max-w-[120rpx] font-500 truncate">{{ goodsPrice(item) }}</text>
                        </view>
                    </view>
                </view>
            </scroll-view>
        </view>
        <view class="style-3 pt-[20rpx] pb-[10rpx] px-[10rpx]" v-if="diyComponent.style.value == 'style-3'">
            <view class="head flex mx-[10rpx] items-center mb-[12rpx]" @click="toListFn()">
                <image v-if="diyComponent.textImg" class="h-[34rpx] w-[auto] mr-[16rpx]" :src="img(diyComponent.textImg)" mode="heightFix" />
                <view class="time-wrap flex items-center" v-show="timeData && Object.keys(timeData).length">
                    <text v-if="!getToken() && diyStore.mode != 'decorate'" :style="{color: diyComponent.countDown.otherColor}" class="text-[24rpx] font-500">活动未开始</text>
                    <up-count-down v-else-if="activeState()" class="text-[#fff] text-[28rpx]" :time="newcomerTime" format="HH:mm:ss" @change="onChange">
                        <view class="flex">
                            <view class="text-[24rpx] flex items-center">
                                <text class="time-num font-500" :style="countDownTextCss" v-if="dayTransitionHours()">{{ dayTransitionHours() }}</text>
                                <text class="time-num font-500" :style="countDownTextCss" v-else>00</text>
                                <text :style="{color: diyComponent.countDown.otherColor}" class="text-[20rpx] font-bold ml-[6rpx] mr-[7rpx]">:</text>
                            </view>
                            <view class="text-[24rpx] flex items-center">
                                <text class="time-num font-500" :style="countDownTextCss" v-if="timeData.minutes">{{ timeData.minutes >= 10 ? timeData.minutes : '0' + timeData.minutes }}</text>
                                <text class="time-num font-500" :style="countDownTextCss" v-else>00</text>
                                <text :style="{color: diyComponent.countDown.otherColor}" class="text-[20rpx] font-bold ml-[6rpx] mr-[7rpx]">:</text>
                            </view>
                            <view class="text-[24rpx] flex items-center">
                                <text class="time-num font-500" :style="countDownTextCss" v-if="timeData.seconds">{{ timeData.seconds < 10 ? '0' + timeData.seconds : timeData.seconds }}</text>
                                <text class="time-num font-500" :style="countDownTextCss" v-else>00</text>
                            </view>
                        </view>
                    </up-count-down>
                    <text v-else :style="{color: diyComponent.countDown.otherColor}" class="text-[26rpx]">活动已结束
                    </text>
                </view>
                <view class="ml-[auto] rounded-[20rpx] flex items-baseline pl-[16rpx] pr-[10rpx] pt-[10rpx] pb-[10rpx]"
                      :style="subTitleCss" @click.stop="diyStore.toRedirect(diyComponent.subTitle.link)">
                    <text class="text-[22rpx]">{{ diyComponent.subTitle.text }}</text>
                    <text class="iconfont iconarrow-right !text-[18rpx] font-bold"></text>
                </view>
            </view>
            <scroll-view scroll-x="true" class="content bg-[#fff] box-border p-[16rpx] rounded-[var(--rounded-small)]">
                <view class="flex">
                    <view v-for="(item,index) in list" :key="index"
                          class="item-bg inline-flex flex-col items-center box-border"
                          :class="{'mr-[16rpx]': index != (list.length-1)}" :style="commonTempCss()"
                          @click="toDetail(item)">
                        <view
                            class="bg-[#f8f8f8] flex items-center justify-center w-[152rpx] h-[152rpx] overflow-hidden"
                            :style="imageRounded.style">
                            <u--image :radius="imageRounded.val" width="152rpx" height="152rpx" :src="img(item.sku_image || '')" model="aspectFill">
                                <template #error>
                                    <image class="w-[152rpx] h-[152rpx] overflow-hidden" :style="imageRounded.style" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill" />
                                </template>
                            </u--image>
                        </view>
                        <image class="h-[32rpx] w-[auto] mt-[12rpx] mb-[10rpx]" :src="img('addon/shop/diy/newcomer/style_3_img.png')" mode="heightFix" />
                        <view class="flex items-center text-[#FF0E00] pb-[2rpx]">
                            <text class="text-[20rpx] font-500">￥</text>
                            <text class="text-[30rpx] max-w-[120rpx] font-500 truncate">{{ goodsPrice(item) }}</text>
                        </view>
                    </view>
                </view>
            </scroll-view>
        </view>
        <view class="style-4 p-[20rpx] pt-[24rpx]" v-if="diyComponent.style.value == 'style-4'"
              :style="{ background: 'url(' + img('addon/shop/diy/newcomer/style_4_head.png') + ') no-repeat'}">
            <view class="head flex mx-[10rpx] items-center justify-between mb-[24rpx]">
                <image v-if="diyComponent.textImg" class="h-[34rpx] w-[auto]" :src="img(diyComponent.textImg)" mode="heightFix" />
                <view class="time-wrap ml-[auto] flex items-center -mt-[8rpx]" v-show="timeData && Object.keys(timeData).length">
                    <text v-if="!getToken() && diyStore.mode != 'decorate'" :style="{color: diyComponent.countDown.otherColor}" class="w-[200rpx] text-center text-[24rpx] font-500 pb-[4rpx]">活动未开始</text>
                    <template v-else-if="activeState()">
                        <text :style="{color: diyComponent.countDown.otherColor}" class="mr-[8rpx] text-[24rpx]">本场仅剩</text>
                        <up-count-down class="text-[#fff] text-[28rpx]" :time="newcomerTime" format="HH:mm:ss" @change="onChange">
                            <view class="flex">
                                <view class="text-[28rpx] flex items-center">
                                    <text class="time-num font-500" :style="countDownTextCss" v-if="dayTransitionHours()">{{ dayTransitionHours() }}</text>
                                    <text class="time-num font-500" :style="countDownTextCss" v-else>00</text>
                                    <text :style="{color: diyComponent.countDown.otherColor}" class="text-[20rpx] ml-[4rpx] font-bold mr-[5rpx]">:</text>
                                </view>
                                <view class="text-[28rpx] flex items-center">
                                    <text class="time-num font-500" :style="countDownTextCss" v-if="timeData.minutes">{{ timeData.minutes >= 10 ? timeData.minutes : '0' + timeData.minutes }}</text>
                                    <text class="time-num font-500" :style="countDownTextCss" v-else>00</text>
                                    <text :style="{color: diyComponent.countDown.otherColor}" class="text-[20rpx] ml-[4rpx] font-bold mr-[5rpx]">:</text>
                                </view>
                                <view class="text-[28rpx] flex items-center">
                                    <text class="time-num font-500" :style="countDownTextCss" v-if="timeData.seconds">{{ timeData.seconds < 10 ? '0' + timeData.seconds : timeData.seconds }}</text>
                                    <text class="time-num font-500" :style="countDownTextCss" v-else>00</text>
                                </view>
                            </view>
                        </up-count-down>
                    </template>
                    <text v-else :style="{color: diyComponent.countDown.otherColor}" class="w-[200rpx] text-center text-[24rpx] pb-[4rpx]">活动已结束</text>
                </view>
            </view>
            <scroll-view scroll-x="true" class="content">
                <view class="flex">
                    <view v-for="(item,index) in list" :key="index"
                          class="item-bg inline-flex flex-col items-center box-border"
                          :class="{'mr-[20rpx]': index != (list.length-1)}" :style="commonTempCss()"
                          @click="toDetail(item)">
                        <view class="relative flex items-center justify-center w-[100%] h-[130rpx] pt-[40rpx] mb-[10rpx]">
                            <u--image :radius="imageRounded.val" width="130rpx" height="130rpx" :src="img(item.sku_image || '')" model="aspectFill">
                                <template #error>
                                    <image class="w-[130rpx] h-[130rpx] overflow-hidden" :style="imageRounded.style" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill" />
                                </template>
                            </u--image>
                            <view class="content-sign text-[20rpx] text-[#fff]">新人价</view>
                        </view>
                        <view class="w-[210rpx] relative -right-[2rpx] -bottom-[2rpx] flex items-center text-[#FF0E00] pb-[2rpx]">
                            <view class="flex items-center justify-center flex-1">
                                <text class="text-[20rpx] font-500">￥</text>
                                <text class="text-[36rpx] max-w-[140rpx] font-500 truncate">{{ goodsPrice(item) }}</text>
                            </view>
                            <text class="btn-bg ml-auto">抢</text>
                        </view>
                    </view>
                </view>
            </scroll-view>
        </view>
    </view>
</template>

<script setup lang="ts">
// 新人专享
import { ref, reactive, computed, onMounted } from 'vue';
import { redirect, img, getToken } from '@/utils/common';
import useDiyStore from '@/app/stores/diy';
import { getNewcomersComponentsList } from '@/addon/shop/api/newcomer'

const props = defineProps(['component', 'index', 'value']);
const diyStore = useDiyStore();

const diyComponent = computed(() => {
    if (props.value) {
        return props.value;
    } else if (diyStore.mode == 'decorate') {
        return diyStore.value[props.index];
    } else {
        return props.component;
    }
})

/********* 倒计时 - start ***********/
    // 使用 reactive 创建响应式对象
const timeData = ref({});
// 定义 onChange 方法
const onChange = (e) => {
    timeData.value = e;
};
const newcomerTime: any = ref('');
/********* 倒计时 - end ***********/

const goodsPrice = (data: any) => {
    let price: any = 0;
    if (data && data.newcomer_price) {
        price = Number(data.newcomer_price).toFixed(2);
    }
    return price;
}

// 将天转换成时
const dayTransitionHours = () => {
    let num = timeData.value.days * 24 + timeData.value.hours;
    num = num ? num : 0;
    num = num >= 10 ? num : '0' + num;
    return num;
}

// 活动状态
const activeState = () => {
    let bool = true;
    if (diyStore.mode != 'decorate' && timeData.value.days <= 0 && timeData.value.hours <= 0 && timeData.value.minutes <= 0 && timeData.value.seconds <= 0 && timeData.value.milliseconds <= 0) {
        bool = false;
    }
    return bool;
}

const warpCss = computed(() => {
    let style = '';
    style += 'position:relative;';
    if (diyComponent.value.componentStartBgColor) {
        if (diyComponent.value.componentStartBgColor && diyComponent.value.componentEndBgColor) style += `background:linear-gradient(${ diyComponent.value.componentGradientAngle },${ diyComponent.value.componentStartBgColor },${ diyComponent.value.componentEndBgColor });`;
        else style += 'background-color:' + diyComponent.value.componentStartBgColor + ';';
    }

    if (diyComponent.value.topRounded) style += 'border-top-left-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
    if (diyComponent.value.topRounded) style += 'border-top-right-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    return style;
})

const imageRounded = computed(() => {
    const obj = {
        val: '',
        style: ''
    };
    if (diyComponent.value.imgElementRounded) {
        obj.val = diyComponent.value.imgElementRounded * 2 + 'rpx';
        obj.style += 'border-radius:' + diyComponent.value.imgElementRounded * 2 + 'rpx;';
    }
    return obj;
})

// 倒计时样式
const countDownTextCss = computed(() => {
    let style = '';
    if (diyComponent.value.countDown && diyComponent.value.countDown.numberBg) {
        if (diyComponent.value.countDown.numberBg.startColor && diyComponent.value.countDown.numberBg.endColor) style += `background:linear-gradient(${ diyComponent.value.countDown.numberBg.startColor },${ diyComponent.value.countDown.numberBg.endColor });`;
        else {
            style += 'background-color:' + (diyComponent.value.countDown.numberBg.startColor || diyComponent.value.countDown.numberBg.endColor) + ';';
        }
    }
    if (diyComponent.value.countDown.numberColor) style += 'color:' + diyComponent.value.countDown.numberColor + ';';
    return style;
})


// 公共模块颜色
const commonTempCss = () => {
    let style = '';
    if (diyComponent.value.topElementRounded) style += 'border-top-left-radius:' + diyComponent.value.topElementRounded * 2 + 'rpx;';
    if (diyComponent.value.topElementRounded) style += 'border-top-right-radius:' + diyComponent.value.topElementRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomElementRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomElementRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomElementRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomElementRounded * 2 + 'rpx;';
    return style;
}

// 副标题样式
const subTitleCss = computed(() => {
    let style = '';
    if (diyComponent.value.subTitle) {
        if (diyComponent.value.subTitle.startColor && diyComponent.value.subTitle.endColor) style += `background:linear-gradient(to right, ${ diyComponent.value.subTitle.startColor },${ diyComponent.value.subTitle.endColor });`;
        else {
            style += 'background-color:' + (diyComponent.value.subTitle.startColor || diyComponent.value.subTitle.endColor) + ';';
        }
    }
    if (diyComponent.value.subTitle.textColor) style += 'color:' + diyComponent.value.subTitle.textColor + ';';
    return style;
})

const list: any = ref([])
const getNewcomerListFn = () => {
    let data = {
        limit: diyComponent.value.source == 'all' ? diyComponent.value.num : '',
        sku_ids: diyComponent.value.source == 'custom' ? diyComponent.value.goods_ids : ''
    }
    getNewcomersComponentsList(data).then((res: any) => {
        newcomerTime.value = res.data.validity_time;
        let now = new Date();
        let timestamp: any = now.getTime();
        newcomerTime.value = Number(newcomerTime.value) * 1000 - timestamp;

        list.value = res.data.goods_list
        list.value.forEach((item:any)=>{
            item.sku_image = item.sku_image ? item.sku_image : item.goods.goods_cover
        })

        // 数据为空时隐藏整个组件
        // if(!(list.value.length && (newcomerTime.value > 0 && isJoin.value == 0))) {
        // 	diyComponent.value.pageStyle = '';
        // }

    })
}

onMounted(() => {
    // 装修模式下刷新
    if (diyStore.mode == 'decorate') {
        let obj = {
            goods: {
                goods_name: "商品名称"
            },
            sku_image: "",
            newcomer_price: 0.01
        };
        list.value.push(obj);
        list.value.push(obj);
        list.value.push(obj);
    } else {
        getNewcomerListFn();
    }
});

// 跳转专享列表
const toListFn = () => {
    redirect({ url: '/addon/shop/pages/newcomer/list' })
}
// 跳转详情
const toDetail = (data: any) => {
    redirect({ url: '/addon/shop/pages/goods/detail', param: { sku_id: data.sku_id, type: 'newcomer_discount' } })
}
</script>

<style lang="scss" scoped>
.shop-newcomer {
    .style-1 {
        .content {
            white-space: nowrap;
        }

        .first-btn-bg {
            background: linear-gradient(140deg, #FF9C24 0%, #FF4837 100%);
        }

        .time-num {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 42rpx;
            padding: 0 6rpx;
            height: 36rpx;
            border-radius: 6rpx;
            box-sizing: border-box;
        }
    }

    .style-2 {
        .content {
            white-space: nowrap;
        }

        .item-bg {
            background: linear-gradient(140deg, #FF7A41, #FF2E0A);
        }

        .time-num {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 42rpx;
            padding: 0 6rpx;
            height: 36rpx;
            border-radius: 6rpx;
            box-sizing: border-box;
        }
    }

    .style-3 {
        .content {
            white-space: nowrap;
        }

        .time-num {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 42rpx;
            padding: 0 6rpx;
            height: 36rpx;
            border-radius: 6rpx;
            box-sizing: border-box;
        }
    }

    .style-4 {
        background-size: 100% 110rpx !important;

        .content {
            white-space: nowrap;
        }

        .time-wrap {
            margin-right: -14rpx;

            .time-num {
                display: flex;
                align-items: center;
                justify-content: center;
                min-width: 32rpx;
                height: 36rpx;
                border-radius: 6rpx;
                font-size: 26rpx;
                box-sizing: border-box;
            }
        }

        .item-bg {
            background: linear-gradient(#FFFFFF 60%, #f7f7f7 100%);
        }

        .btn-bg {
            background: linear-gradient(140deg, #FE2B2B 0%, #FF7236 100%);
            border-radius: 50% 50% 50% 0;
            font-size: 30rpx;
            color: #fff;
            width: 50rpx;
            height: 50rpx;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .content-sign {
            position: absolute;
            left: 0;
            top: 20rpx;
            z-index: 1;
            background: linear-gradient(140deg, #FE2B2B 0%, #FF7236 100%);
            padding: 6rpx 8rpx;
        }
    }
}
</style>

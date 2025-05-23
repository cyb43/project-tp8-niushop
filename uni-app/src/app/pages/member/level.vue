<template>
    <view :style="themeColor()" class="bg-[var(--page-bg-color)] min-h-[100vh] overflow-hidden">
        <loading-page :loading="loading && memberInfo"></loading-page>
        <view v-if="!loading && memberInfo && list && list.length" class="min-h-[100vh] overflow-hidden flex flex-col" :style="{backgroundColor: currLevelInfo.level_style.bg_color }">
            <!-- #ifdef MP -->
            <top-tabbar :data="topTabbarData" :scrollBool="topTabarObj.getScrollBool()" />
            <!-- #endif -->
            <view>
                <view class="pt-[40rpx] mb-[40rpx]">
                    <!-- 轮播图 -->
                    <view class="relative">
                        <swiper class="swiper ns-indicator-dots relative" :style="{ height: '300rpx' }"
                                @change="swiperChange" :current="swiperIndex" previous-margin="30rpx" next-margin="30rpx">
                            <swiper-item class="swiper-item" v-for="(item,index) in list" :key="item.id">
                                <view class="h-[300rpx] relative">
                                    <view v-if="memberInfo.member_level == item.level_id && swiperIndex == index"
                                          class="text-[24rpx] absolute top-0 left-0 z-10 h-[66rpx] !bg-contain w-[150rpx] flex pt-[12rpx] pl-[16rpx]  box-border"
                                          :style="{ background: 'url(' + img(currLevelInfo.level_tag) + ') no-repeat',color: currLevelInfo.level_style.level_color}">当前等级</view>
                                    <view class="absolute top-0 left-0 right-0 bottom-0 z-20 px-[30rpx] pt-[68rpx] box-border"
                                        :class="{'px-[50rpx]': swiperIndex != index}">
                                        <view class="flex items-center leading-[50rpx] mb-[90rpx]">
                                            <image class="h-[32rpx] w-[34rpx] align-middle" :src="img(item.level_icon ? item.level_icon : '')" mode="aspectFill" />
                                            <view class="text-[36rpx]  font-bold ml-[10rpx] max-w-[340rpx] truncate" :style="{color:currLevelInfo.level_style.level_color}">{{ item.level_name }}</view>
                                        </view>
                                        <view class="flex items-baseline" :style="{color: currLevelInfo.level_style.level_color}">
                                            <view class="text-[30rpx] font-500 leading-[38rpx]">{{ memberInfo.growth }}</view>
                                            <view class="text-[24rpx] leading-[34rpx]">/{{ list[index].growth }}成长值</view>
                                        </view>
                                        <view class="flex justify-between items-center mt-[10rpx]">
                                            <view class="flex flex-col flex-1">
                                                <view>
                                                    <progress :percent="progress(index)" :border-radius="100"
                                                              :activeColor="currLevelInfo.level_style.level_color"
                                                              backgroundColor="#fff" stroke-width="4" />
                                                </view>
                                            </view>
                                        </view>
                                    </view>
                                    <view class="relatvie h-full w-full">
                                        <image class="h-full w-full" :src="img(item.level_bg)"
                                               :class="{'swiper-animation': swiperIndex != index}"
                                               :show-menu-by-longpress="true" />
                                    </view>
                                </view>
                            </swiper-item>
                        </swiper>
                    </view>
                </view>
                <view class="mb-[30rpx] relative">
                    <view class="bg-[#fff] opacity-15 h-[2rpx] w-full absolute top-[15rpx]"></view>
                    <view :style="lineStyle" class="bg-[#fff] opacity-60 h-[2rpx]  absolute top-[15rpx] z-4 left-[50%]"></view>
                    <view class="mx-[86rpx]">
                        <scroll-view :scroll-x="true" scroll-with-animation :scroll-into-view="'id' + ( levelIndex  ? levelIndex - 1 : 0)">
                            <view class="flex flex-nowrap py-[10rpx]">
                                <template v-for="(item,index) in list" :key="item.id">
                                    <view :style="levelStyle" class=" flex-shrink-0 flex flex-col items-center justify-center"
                                          @click="changeLevel(index)" :id="'id' + index">
                                        <view class="w-[14rpx] h-[14rpx] level-class" :class="{'level-select': levelIndex == (index)}"></view>
                                        <view :style="maxWidth" class="text-[22rpx] text-[#aaa] mt-[16rpx] truncate" :class="{'!text-[#fff]': levelIndex == (index)}">{{ item.level_name }}</view>
                                    </view>
                                </template>
                            </view>
                        </scroll-view>
                    </view>
                </view>
                <view class="flex mx-[var(--sidebar-m)] pt-[30rpx]  pb-[46rpx]  items-center flex-col level_benefits"
                      v-if="currLevelInfo.benefits_arr && currLevelInfo.benefits_arr.length"
                      :style="{ backgroundImage: 'url(' + img(currLevelInfo.member_bg) + ')'}">
                    <view class="flex items-center justify-center">
                        <text class="text-[#fff] text-[30rpx] font-500 leading-[44rpx]">会员权益</text>
                    </view>
                    <view class="flex flex-wrap w-[690rpx] mt-[40rpx] justify-between">
                        <view class="flex flex-col w-[25%] items-center" v-for="(item,index) in currLevelInfo.benefits_arr" :key="index">
                            <image class="h-[88rpx] w-[88rpx]" :src="img(item.icon)" mode="heightFix" />
                            <text class="text-[rgba(255,255,255,0.9)] mt-[16rpx] text-[24rpx] leading-[34rpx]">{{ item.title }}</text>
                        </view>
                    </view>
                </view>
            </view>

            <view class="flex-1 rounded-t-[40rpx] px-[30rpx] pt-[var(--pad-top-m)] mt-[-10rpx]  relative tab-bar" :style="{background: `linear-gradient( 180deg, ${currLevelInfo.level_style.gift} 0%, #FFFFFF 20%)`}">
                <!-- 升级礼包 -->
                <view v-if="currLevelInfo.gifts_arr && currLevelInfo.gifts_arr.length">
                    <view class="pb-[30rpx] flex items-center">
                        <text class="text-[30rpx] text-[#333] font-500 leading-[44rpx]">升级礼包</text>
                    </view>
                    <view class="flex flex-wrap">
                        <view v-for="(item,index) in currLevelInfo.gifts_arr" :key="index" class="mb-[20rpx]" :class="{'mr-[21rpx]': (index+1) % 3 != 0}">
                            <view class="relative box-border mb-[16rpx] w-[216rpx] h-[180rpx] !bg-contain" :style="{ background: 'url(' + img(item.background) + ') no-repeat'}"></view>
                            <view class="text-center font-500 text-[#333] text-[28rpx] truncate leading-[40rpx] max-w-[216rpx]">{{ item.text }}</view>
                        </view>
                    </view>
                </view>

                <!-- 升级技巧 -->
                <view v-if="upgradeSkills && upgradeSkills.length">
                    <view class="pt-[30rpx] pb-[30rpx] flex items-center">
                        <text class="text-[30rpx] text-[#333] font-500 leading-[44rpx]">升级技巧</text>
                    </view>
                    <view class="pb-[30rpx]">
                        <view class="flex items-center mb-[34rpx]" v-for="(item,index) in upgradeSkills" :key="index">
                            <image class="h-[100rpx] w-[100rpx] mr-[20rpx]" :src="img(item.icon)" mode="heightFix" />
                            <view class="flex flex-col">
                                <view class="text-[28rpx] leading-[38rpx] mb-[8rpx]">{{ item.title }}</view>
                                <view class="text-[24rpx] text-[var(--text-color-light9)] leading-[34rpx]">{{ item.desc }}</view>
                            </view>
                            <text class="skill-btn" @click="redirect({ url: item.button.wap_redirect, param: {} , mode: 'redirectTo'})">{{ item.button.text }}</text>
                        </view>
                    </view>
                </view>
            </view>
        </view>
        <view class="empty-page" v-if="!loading && (!list || !list.length)">
            <image class="img" :src="img('static/resource/images/empty.png')" mode="aspectFill" />
            <text class="desc">暂无会员等级</text>
        </view>
    </view>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { onShow } from '@dcloudio/uni-app'
import { getMemberLevel, getTaskGrowth } from '@/app/api/member';
import { img, redirect, deepClone, getToken } from '@/utils/common';
import useMemberStore from '@/stores/member'
import { topTabar } from '@/utils/topTabbar'

const memberStore = useMemberStore()

const loading = ref(false)
const list = ref([]) // 会员等级
const upgradeSkills = ref([]) // 升级技巧
const swiperIndex = ref(0); //当前滑块的索引
const levelIndex = ref(0); //当前等级的索引

/********* 自定义头部 - start ***********/
const topTabarObj = topTabar()
let topTabbarData = topTabarObj.setTopTabbarParam({ title: '会员等级' })
/********* 自定义头部 - end ***********/

onShow(() => {
    // 判断是否已登录
    if (getToken()) {
        getTaskGrowthFn();
        getMemberLevelFn();
    }
})

// 会员信息
const memberInfo = computed(() => {
    return memberStore.info
})

// 进度条值
const progress = (index: any) => {
    let indent = index;
    let num = 100
    if (list.value[indent] && list.value[indent].growth) {
        num = memberInfo.value.growth / list.value[indent].growth * 100
    }
    return num
}

// 所需的成长值
const upgradeGrowth = (index: any) => {
    let indent = index;
    let num = 0;
    if (list.value[indent] && list.value[indent].growth) {
        num = list.value[indent].growth - memberInfo.value.growth;
    }
    return num
}
const levelStyle = ref(''); // 等级样式
const maxWidth = ref(''); // 等级样式
const lineStyle = ref(''); // 线样式
const getMemberLevelFn = () => {
    loading.value = true;

    getMemberLevel().then((res: any) => {
        list.value = res.data || [];

        // 初始化会员等级数据
        let bool = true;
        if (memberInfo.value && list.value && list.value.length) {
            list.value.forEach((item: any, index) => {
                if (item.level_id == memberInfo.value.member_level) {
                    bool = false;
                    swiperIndex.value = index;
                    levelIndex.value = swiperIndex.value;
                    infoStructureFn(index);
                }
            })
        }
        if (bool) infoStructureFn(0);
        if (list.value && list.value.length >= 5) {
            levelStyle.value = 'width:115rpx;'
            maxWidth.value = 'max-width:115rpx;'
            lineStyle.value = `width:460rpx;transform: translateX(-235rpx);`
        } else if (list.value && list.value.length == 4) {
            levelStyle.value = 'width:144rpx;'
            maxWidth.value = 'max-width:144rpx;'
            lineStyle.value = `width:436rpx;transform: translateX(-218rpx);`
        } else if (list.value && list.value.length == 3) {
            levelStyle.value = 'width:192rpx;'
            maxWidth.value = 'max-width:192rpx;'
            lineStyle.value = `width:388rpx;transform: translateX(-194rpx);`
        } else if (list.value && list.value.length == 2) {
            levelStyle.value = 'width:289rpx;'
            maxWidth.value = 'max-width:289rpx;'
            lineStyle.value = `width:289rpx;transform: translateX(-144rpx);`
        } else {
            maxWidth.value = 'max-width:578rpx;'
            levelStyle.value = 'width:100%;'
        }
        loading.value = false;
    }).catch(() => {
        loading.value = false;
    })
}

const getTaskGrowthFn = () => {
    getTaskGrowth().then((res: any) => {
        upgradeSkills.value = res.data
    })
}

const swiperChange = (e: any) => {
    swiperIndex.value = e.detail.current;
    levelIndex.value = swiperIndex.value
    infoStructureFn(e.detail.current);
};

// 当前的会员等级信息
const currLevelInfo = ref<any>({});
const infoStructureFn = (index: number) => {
    let data: any = deepClone(list.value[index]);
    // 会员权益
    if (data && data.level_benefits) {
        data.benefits_arr = [];
        Object.values(data.level_benefits).forEach((item: any, index, Array) => {
            if (item.content) {
                data.benefits_arr.push(item.content)
            }
        })
    }

    // 升级礼包
    if (data && data.level_gifts) {
        data.gifts_arr = [];
        for (let key in data.level_gifts) {
            if (data.level_gifts[key].content) {
                // 增加类型
                data.level_gifts[key].content.forEach((item: any, index: any, Array: any) => {
                    Array[index].type = key
                })
                data.gifts_arr = data.gifts_arr.concat(data.level_gifts[key].content);
            }
        }
    }
    currLevelInfo.value = data;
}
// 改变会员等级
const changeLevel = (index: any) => {
    levelIndex.value = index;
    swiperIndex.value = index;
    infoStructureFn(index);
}
</script>

<style lang="scss" scoped>
.skill-btn {
    width: 120rpx;
    height: 54rpx;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #333;
    background: linear-gradient(180deg, #FEE8AC 0%, #F5D36E 85%);
    border-radius: 100rpx;
    margin-left: auto;
    font-size: 22rpx;
}

.swiper-animation {
    transform: scale(0.95, 0.95);
    transition-duration: 0.3s;
    transition-timing-function: ease;
}

:deep(.uni-progress) .uni-progress-bar, :deep(.uni-progress) .uni-progress-inner-bar {
    border-radius: 10rpx;
}

.tab-bar {
    padding-bottom: constant(safe-area-inset-bottom);
    padding-bottom: env(safe-area-inset-bottom);
}

.level-class {
    position: relative;

    &::before {
        content: "";
        position: absolute;
        width: 14rpx;
        height: 14rpx;
        background-color: #bbb;
        border-radius: 14rpx;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 10;
    }
}

.level-select {
    position: relative;

    &::after {
        content: "";
        position: absolute;
        width: 26rpx;
        height: 26rpx;
        background-color: #fff;
        opacity: 0.6;
        border-radius: 26rpx;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 1;
    }

    &::before {
        background-color: #fff !important;
    }
}

.level_benefits {
    background-repeat: no-repeat;
    background-size: 100% 100%;
}
</style>

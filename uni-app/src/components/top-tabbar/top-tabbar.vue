<template>
    <view class="ns-navbar-wrap" v-if="diyStore.mode !='decorate' && topStatusBarData" :class="topStatusBarData.style">
        <view class="u-navbar" :class="{'fixed': props.scrollBool != -1, 'absolute': props.scrollBool == -1}" :style="{ backgroundColor: bgColor}">
            <view class="navbar-inner" :style="{ width: '100%', height: placeholderHeight + 'px' }">
                <view v-if="topStatusBarData.style == 'style-1'" class="content-wrap" :class="[topStatusBarData.textAlign]" :style="navbarInnerStyle">
                    <view v-if="isBack" class="back-wrap -ml-[16rpx] text-[26px] nc-iconfont nc-icon-zuoV6xx" :style="{ color: titleTextColor }" @tap="goBack"></view>
                    <view class="title-wrap" :style="styleOneFontSize">{{ data.title }}</view>
                </view>
                <view v-if="topStatusBarData.style == 'style-2'" class="content-wrap" :style="navbarInnerStyle" @click="diyStore.toRedirect(topStatusBarData.link)">
                    <view v-if="isBack" class="back-wrap -ml-[16rpx] text-[26px] nc-iconfont nc-icon-zuoV6xx" :style="{ color: titleTextColor }" @tap="goBack"></view>
                    <view class="title-wrap" :style="{ color: topStatusBarData.textColor }">
                        <view>
                            <image :src="img(topStatusBarData.imgUrl)" mode="heightFix"/>
                        </view>
                        <view :style="{ color: topStatusBarData.textColor }">{{ data.title }}</view>
                    </view>
                </view>

                <view v-if="topStatusBarData.style == 'style-3'" :style="navbarInnerStyle" class="content-wrap">
                    <view v-if="isBack" class="back-wrap -ml-[16rpx] text-[26px] nc-iconfont nc-icon-zuoV6xx" :style="{ color: titleTextColor }" @tap="goBack"></view>
                    <view class="title-wrap" @click="diyStore.toRedirect(topStatusBarData.link)">
                        <image :src="img(topStatusBarData.imgUrl)" mode="heightFix"/>
                    </view>
                    <view class="search" @click="diyStore.toRedirect(topStatusBarData.link)" :style="{ height: systemStore.menuButtonInfo.height - 2 + 'px', lineHeight: systemStore.menuButtonInfo.height - 2 + 'px' }">
                        <text class="nc-iconfont nc-icon-sousuo-duanV6xx1 text-[24rpx] absolute left-[20rpx]"></text>
                        <text class="text-[24rpx]">{{ topStatusBarData.inputPlaceholder }}</text>
                    </view>
                    <view :style="{ 'width': capsuleWidth }"></view>
                </view>

                <view v-if="topStatusBarData.style == 'style-4'" :style="navbarInnerStyle" class="content-wrap">
                    <view v-if="isBack" class="back-wrap -ml-[16rpx] text-[26px] nc-iconfont nc-icon-zuoV6xx" :style="{ color: titleTextColor }" @tap="goBack"></view>
                    <text class="nc-iconfont nc-icon-dizhiguanliV6xx text-[28rpx]" :style="{ color: topStatusBarData.textColor }"></text>
                    <view class="title-wrap" @click.stop="locationVal.reposition()" :style="{ color: topStatusBarData.textColor }" v-if="systemStore.diyAddressInfo">{{ systemStore.diyAddressInfo.community }}</view>
                    <view class="title-wrap" @click.stop="locationVal.reposition()" :style="{ color: topStatusBarData.textColor }" v-else>{{ systemStore.defaultPositionAddress }}</view>
                    <text class="nc-iconfont nc-icon-youV6xx text-[26rpx]" @click.stop="locationVal.reposition()" :style="{ color: topStatusBarData.textColor }"></text>
                </view>
            </view>
        </view>

        <!-- 解决fixed定位后导航栏塌陷的问题 -->
        <view v-if="isFill" class="u-navbar-placeholder" :style="{ width: '100%', paddingTop: placeholderHeight + 'px' }"></view>
    </view>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, getCurrentInstance, nextTick } from 'vue';
import { redirect, img } from '@/utils/common';
import useSystemStore from '@/stores/system';
import useDiyStore from '@/app/stores/diy';
import { useLocation } from '@/hooks/useLocation'

// 获取系统状态栏的高度
let systemInfo = uni.getSystemInfoSync();
let platform = systemInfo.platform;
const systemStore = useSystemStore();

const diyStore = useDiyStore();

const props = defineProps({
    data: {
        type: Object,
        default: {}
    },
    // 标题的颜色
    titleColor: {
        type: String,
        default: '#606266'
    },
    // 自定义返回逻辑
    customBack: {
        type: Function,
        default: null
    },
    scrollBool: {
        type: [String, Number],
        default: -1
    },
    // 是否显示导航栏左边返回图标和辅助文字
    isBack: {
        type: Boolean,
        default: true
    },
    isFill: {
        type: Boolean,
        default: true
    }
})

// 控制-定位后是否对导航栏进行填充
const isFill = computed(() => {
    let bool = true;
    if (imageAdsSameScreen.value) {
        bool = false;
    } else {
        bool = props.isFill;
    }
    return bool;
});

const data = computed(() => {
    return props.data;
});

const topStatusBarData = computed(() => {
    if (props.data && props.data.topStatusBar) return props.data.topStatusBar;
});

// 导航栏内部盒子的样式
const navbarInnerStyle = computed(() => {
    let style = '';

    if (props.isBack) {
        style += 'padding-left: 30rpx;';//30=>右边留边 44=>箭头宽度 10=>箭头的右maring
        if (topStatusBarData.value.style == 'style-1') //样式一需要居中需要有右边padding辅助
            style += 'padding-right:' + (40 + 30 + 10) + 'rpx;'; //30=>左边留边 44=>箭头宽度 10=>箭头的右maring
    } else {
        if (topStatusBarData.value.style == 'style-1') //样式一需要居中需要有右边padding辅助
            style += 'padding-right: 30rpx;'; //右边留边
        style += 'padding-left: 30rpx;'; //左边留边
    }
    // #ifdef MP
    // 导航栏宽度，如果在小程序下，导航栏宽度为胶囊的左边到屏幕左边的距离
    style += 'height:' + systemStore.menuButtonInfo.height + 'px;';
    style += 'padding-top:' + systemStore.menuButtonInfo.top + 'px;';
    style += 'padding-bottom: 8px;';
    // #endif
    return style;
})

// 样式一的字体大小
const styleOneFontSize = computed(() => {
    let style = '';

    // #ifdef H5
    style += 'font-size: 28rpx;';
    // #endif
    // #ifdef MP
    style += 'font-size: 32rpx;';
    if (platform === 'ios') {
        // 苹果(iOS)设备
        style += 'font-weight: 500;';
    } else if (platform === 'android') {
        // 安卓(Android)设备
        style += 'font-size: 36rpx;';
        style += 'font-weight: 500;';
    }
    // #endif
    style += `color: ${ titleTextColor.value };`;
    if (topStatusBarData.value.style == 'style-1') {
        style += `text-align: ${ topStatusBarData.value.textAlign };`;
    }
    return style;
})

const titleTextColor = computed(() => {
    let color = '';
    if (props.scrollBool == 1) {
        color = topStatusBarData.value.rollTextColor;
    } else {
        color = topStatusBarData.value.textColor;
    }
    return color;
})

const bgColor = computed(() => {
    let color = '';
    if (props.scrollBool == 1) {
        color = topStatusBarData.value.rollBgColor;
    } else {
        color = topStatusBarData.value.bgColor;
    }
    return color;
})

/******************************* 存储滚动值-start ***********************/
// 键名和组件名一致即可
let componentsScrollVal = uni.getStorageSync('componentsScrollValGroup')
if (componentsScrollVal) {
    componentsScrollVal.TopTabbar = 0
    uni.setStorageSync('componentsScrollValGroup', componentsScrollVal);
} else {
    let obj = {
        TopTabbar: 0
    }
    uni.setStorageSync('componentsScrollValGroup', obj);
}
/******************************* 存储滚动值-end ***********************/

/******************************* 返回按钮-start ***********************/
// const isBackShow = ref(false);
let pages = getCurrentPages();

// 返回按钮的函数
const goBack = () => {
    // 兼容小程序，未登录状态下点击某个功能跳转到登录页，不登录无法返回的情况
    if (pages.length === 1) {
        if (pages[0].route === 'app/pages/auth/index') {
            uni.getStorage({
                key: 'loginBack',
                success: (res: any) => {
                    res ? redirect(
                        {
                            ...res.data,
                            mode: 'redirectTo'
                        }
                    ) : redirect({ url: '/app/pages/index/index', mode: 'switchTab' })
                },
                fail: (res) => {
                    redirect({ url: '/app/pages/index/index', mode: 'switchTab' })
                }
            });
        } else if (typeof props.customBack === 'function') {
            props.customBack();
        } else {
            redirect({ url: '/app/pages/index/index', mode: 'switchTab' });
        }
    } else {
        // 如果自定义了点击返回按钮的函数，则执行，否则执行返回逻辑
        if (typeof props.customBack === 'function') {
            props.customBack();
        } else {
            uni.navigateBack();
        }
    }
}
/******************************* 返回按钮-end ***********************/

// 微信胶囊宽度+right
const capsuleWidth = computed(() => {
    let width = `calc(100vw - ${ systemStore.menuButtonInfo.right }px + ${ systemStore.menuButtonInfo.width }px + 10px)`;
    return width;
})

// 导航栏塌陷的高度
const placeholderHeight = ref(0);
const instance = getCurrentInstance();
// #ifdef MP
let statusBarHeight = systemStore.menuButtonInfo.height + systemStore.menuButtonInfo.top + 8;
placeholderHeight.value = statusBarHeight || 0;
// #endif
const navbarPlaceholderHeight = () => {
    nextTick(() => {
        const query = uni.createSelectorQuery().in(instance);
        query.select('.ns-navbar-wrap .u-navbar .content-wrap').boundingClientRect(data => {
            placeholderHeight.value = data ? data.height : 0;
            diyStore.$patch((state) => {
                state.topTabarHeight = placeholderHeight.value
            })
        }).exec();
    })
}

/************** 定位-start ****************/
let isOpenLocation = false;
if (topStatusBarData.value && topStatusBarData.value.style == 'style-4') {
    isOpenLocation = true;
}

const locationVal = useLocation(isOpenLocation);
locationVal.onLoad();
locationVal.init();
/************** 定位-end ****************/
let imageAdsSameScreen = ref(false);
onMounted(() => {
    navbarPlaceholderHeight();
    // if (pages.length > 1) {
    // 	isBackShow.value = true;
    // 	// 兼容小程序，未登录状态下点击某个功能跳转到登录页，不登录无法返回的情况
    // }else if(pages.length ==  1 && pages[0].route == 'app/pages/auth/index'){
    // 	isBackShow.value = true;
    // }
    // 刷新定位
    locationVal.refresh();

    // 图文导航开启沉浸式且导航栏开启时，导航栏不占位
    imageAdsSameScreen.value = uni.getStorageSync('imageAdsSameScreen') || false;
});

// 页面onShow调用时，也会触发改方法
const refresh = () => {
    // 刷新定位
    locationVal.refresh();
}

defineExpose({
    refresh
})
</script>

<style scoped lang="scss">
/* #ifdef H5 */
.style-1,
.style-2,
.style-3 {
    display: none;
}

/* #endif */

.u-navbar {
    width: 100%;
    transition: background 0.3s;
    left: 0;
    right: 0;
    top: 0;
    z-index: 991;
}

.navbar-inner {
    display: flex;
    justify-content: space-between;
    position: relative;
    align-items: center;
    // padding-bottom: 20rpx;
    // /* #ifdef H5 */
    // padding-bottom: 40rpx;
    // /* #endif */
}

.back-wrap {
    padding-right: 10rpx;
    line-height: 1;

    .iconfont {
        font-size: 40rpx;
    }
}

.content-wrap {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    flex: 1;
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    height: 60rpx;
    text-align: center;
    flex-shrink: 0;
}

.title-wrap {
    line-height: 1;
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 32rpx;
    color: #000000;
}

.ns-navbar-wrap {

    &.style-2 {
        .content-wrap {
            .title-wrap {
                display: flex;
                align-items: center;
                text-align: left;

                > view {
                    height: 56rpx;
                    line-height: 56rpx;
                    max-width: 300rpx;
                    font-size: 28rpx;

                    image {
                        width: 100%;
                        height: 100%;
                        margin-right: 20rpx;
                    }

                    &:last-child {
                        max-width: 300rpx;
                        overflow: hidden; //超出的文本隐藏
                        text-overflow: ellipsis; //用省略号显示
                        white-space: nowrap; //不换行
                        flex: 1;
                    }
                }
            }
        }
    }

    &.style-3 {
        .content-wrap {
            .title-wrap {
                height: 60rpx;
                max-width: 170rpx;
                flex: initial;
                text-align: center;
                margin-right: 10rpx;

                image {
                    height: 100%;
                    width: 100%;
                }
            }

            .search {
                flex: 1;
                padding-right: 20rpx;
                padding-left: 62rpx;
                position: relative;
                background-color: #fff;
                text-align: left;
                border-radius: 60rpx;
                height: 60rpx;
                line-height: 60rpx;
                border: 1px solid #eeeeee;
                color: rgb(102, 102, 102);
                display: flex;
                align-items: center;
                margin-right: 10rpx;
                overflow: hidden;
                box-sizing: border-box;

                text {
                    overflow: hidden; //超出的文本隐藏
                    text-overflow: ellipsis; //用省略号显示
                    white-space: nowrap; //不换行
                }

                .iconfont {
                    color: #909399;
                    font-size: 32rpx;
                    margin-right: 10rpx;
                }
            }
        }
    }

    &.style-4 {
        .content-wrap {
            top: 0;
            text-align: left;
            padding-left: 30rpx;
            /* #ifdef H5 */
            padding-top: 10rpx;
            padding-bottom: 10rpx;
            /* #endif */

            .title-wrap {
                flex: none;
                margin: 0 10rpx;
                max-width: 360rpx;
                font-size: 27rpx;
                line-height: normal;
            }

            .nearby-store-name {
                margin: 0 10rpx;
                background: rgba(0, 0, 0, .2);
                font-size: 22rpx;
                border-radius: 40rpx;
                padding: 10rpx 20rpx;
                line-height: 1;
            }
        }
    }
}
</style>

<template>
    <view :style="warpCss" class="overflow-hidden relative" v-if="diyComponent && diyComponent.goods && Object.keys(diyComponent.goods).length">
        <!-- 自定义头部 -->
        <view class="flex items-center left-0 right-0 z-10 bg-transparent detail-head" :class="{'!bg-[#fff]' :detailHeadBgChange, 'fixed': diyStore.mode != 'decorate', 'absolute': diyStore.mode == 'decorate'}" :style="navbarInnerStyle">
            <view class="flex-center h-[60rpx] rounded-[30rpx] box-border arrow-left px-[40rpx] leading-[1]" :style="navbarInnerArrowStyle">
                <text class="nc-iconfont nc-icon-zuoV6xx text-[18px]" @click="backToPrevious()"></text>
                <text class="w-[2rpx] h-[26rpx] bg-[#999] mx-[14rpx]"></text>
                <text class="nc-iconfont nc-icon-liebiao-xiV6xx1 text-[16px]" @click="topNav = true"></text>
            </view>
            <view class="ml-auto !pt-[12rpx] !pb-[8rpx] p-[10rpx] arrow-left rounded-full box-border nc-iconfont nc-icon-fenxiangV6xx font-bold text-[#303133] text-[36rpx]"
                :class="{'border-[#d8d8d8]': detailHeadBgChange}" @click="openShareFn"></view>
        </view>
        <view class="fixed top-0 left-0 right-0 bottom-0 z-100 bg-transparent" @click="topNav = false" v-if="topNav">
            <view class="search-box w-[202rpx] bg-[#fff] rounded-[12rpx] relative" :style="fixedInnerStyle">
                <view class="px-[20rpx] flex-center" @click="redirect(item.url)" v-for="(item,index) in menuContents" :key="index">
                    <text class="text-[30rpx] mr-[10rpx]" :class="item.iconfont"></text>
                    <text class="pl-[14rpx] py-[20rpx] flex-1 text-[24rpx] text-[#333] border-0 border-[#ddd] border-b-[1rpx] border-solid">{{ item.name }}</text>
                </view>
            </view>
        </view>

        <view class="w-full relative overflow-hidden" :style="mediumCss">
            <view class="absolute top-0 left-0 w-full h-full transition-transform duration-300 ease-linear transform"
                :class="{'translate-x-0':switchMedia === 'img','translate-x-full':switchMedia != 'img'}">
                <view class="swiper-box">
                    <u-swiper :list="diyComponent.goods.goods_image"
                                @change="swiperChangeFn"
                                :indicator="(diyComponent.goods.goods_image.length != 1 || diyStore.mode == 'decorate') && diyComponent.medium.indicator"
                                :indicatorStyle="{'bottom': '70rpx'}" :autoplay="switchMedia === 'img'?true:false"
                                :height="swiperImageHeight" radius="0" @click="swiperClick"></u-swiper>
                </view>
            </view>
            <view @touchmove.stop.prevent class="media-mode absolute top-0 left-0 w-full h-full transition-transform duration-300 ease-linear transform"
                :class="{'translate-x-0':switchMedia === 'video','-translate-x-full':switchMedia != 'video'}" :style="{background: 'url(' + img(diyComponent.goods.goods_cover_thumb_mid) + ') left bottom / cover no-repeat'}">
                <view :style="goodsVideoHeight">
                    <video id="goodsVideo" class="w-full h-full" :src="img(diyComponent.goods.goods_video)" :poster="img(diyComponent.goods.goods_cover_thumb_mid)" objectFit="cover" play-btn-position="center"></video>
                </view>
            </view>
            <!-- 切换视频、图片 -->
            <view class="media-mode bg-[rgb(0,0,0,.5)] flex items-center rounded-[50rpx] p-[4rpx] absolute bottom-[130rpx] right-[20rpx] text-center leading-[46rpx]" v-if="diyComponent.goods.goods_video != ''">
                <text class="tab-item" :class="{ '!bg-[#fff] !text-[#666]': switchMedia == 'video' }" @click="switchMedia = 'video'">视频</text>
                <view class="tab-item flex items-center" :class="{ '!bg-[#fff] !text-[#666]': switchMedia == 'img' }" @click="(switchMedia = 'img'), videoContext.pause()">
                    <text class="mr-[4rpx]">图片</text>
                    <text v-if="switchMedia == 'img' && diyComponent?.goods?.goods_image?.length > 1">{{swiperCurrentIndex}}/{{diyComponent.goods.goods_image.length}}</text>
                </view>
            </view>
        </view>
        <view v-if="priceType != 'original_price'"
      class="relative flex items-center justify-between !bg-cover box-border pb-[26rpx] h-[136rpx] px-[30rpx]"
      :style="marketGoodCss">
            <view class="text-[#fff] leading-[normal]">
                <text class="text-[26rpx] mr-[10rpx] font-500 leading-[36rpx]" v-if="priceType == 'newcomer_price'">新人价</text>
                <text class="text-[26rpx] mr-[10rpx] font-500 leading-[36rpx]" v-else-if="priceType == 'discount_price'">折扣价</text>
                <text class="text-[26rpx] mr-[10rpx] font-500 leading-[36rpx]" v-else-if="priceType == 'member_price'">会员价</text>
                <view class="inline-block mr-[14rpx] relative top-[2rpx]">
                    <text class="text-[32rpx] price-font mr-[4rpx]">￥</text>
                    <text class="text-[48rpx] -mb-[4rpx] price-font">{{ parseFloat(goodsPrice).toFixed(2).split('.')[0] }}</text>
                    <text class="text-[32rpx] price-font">.{{ parseFloat(goodsPrice).toFixed(2).split('.')[1] }}</text>
                </view>
                <view class="inline-block" v-if="priceType">
                       <!--  #ifdef H5 -->
                    <text class="text-[26rpx] leading-[36rpx] mr-[6rpx] relative -top-[1rpx]" v-if="diyComponent.price">售价</text>
                       <!--  #endif -->
                     <!--  #ifndef  H5 -->
                     <text class="text-[26rpx] leading-[36rpx] mr-[6rpx]" v-if="diyComponent.price">售价</text>
                        <!--  #endif -->
                    <text class="text-[30rpx] relative top-[1rpx] price-font leading-[36rpx]">￥{{ diyComponent.price }}</text>
                </view>
            </view>
            <view v-if="priceType == 'discount_price'" class="flex flex-col text-[#fff] items-end h-[59rpx] justify-between">
                <image class="h-[28rpx] w-[auto] mr-[2rpx]" :src="img('addon/shop/detail/discount_price.png')" mode="heightFix" />
                <view class="flex items-center text-[24rpx] -mb-[10rpx] overflow-hidden h-[28rpx]">
                    <text class="mr-[4rpx] whitespace-nowrap">距结束</text>
                    <up-count-down class="text-[#fff] text-[28rpx]" :time="discountTime" format="DD:HH:mm:ss" @change="onChange">
                        <view class="flex">
                            <view class="text-[24rpx] flex items-center" v-if="timeData.days>0">
                                <text>{{ timeData.days }}</text>
                                <text class="ml-[4rpx] text-[20rpx]">天</text>
                            </view>
                            <view class="text-[24rpx] flex items-center">
                                <text class="min-w-[30rpx] text-center" v-if="timeData.hours">{{ timeData.hours >= 10 ? timeData.hours : '0' + timeData.hours }}</text>
                                <text class="min-w-[30rpx] text-center" v-else>00</text>
                                <text class="text-[20rpx]">时</text>
                            </view>
                            <view class="text-[24rpx] flex items-center">
                                <text class="min-w-[30rpx] text-center">{{ timeData.minutes >= 10 ? timeData.minutes : '0' + timeData.minutes }}</text>
                                <text class="text-[20rpx]">分</text>
                            </view>
                            <view class="text-[24rpx] flex items-center">
                                <text class="min-w-[30rpx] text-center">{{ timeData.seconds < 10 ? '0' + timeData.seconds : timeData.seconds }}</text>
                                <text class="text-[20rpx]">秒</text>
                            </view>
                        </view>
                    </up-count-down>
                </view>
            </view>
        </view>
        <view class="overflow-hidden -mt-[34rpx] relative" :style="ordinaryGoodCss">
            <view class="detail-title relative px-[30rpx]"
                    :class="{'pt-[40rpx]': priceType != 'original_price','pt-[30rpx]': priceType == 'original_price'}">
                <view class="text-[var(--price-text-color)] flex items-baseline mb-[12rpx]" v-if="priceType == 'original_price'">
                    <view class="inline-block goods-price-time">
                        <text class="price-font text-[32rpx]">￥</text>
                        <text class="price-font text-[48rpx]">{{ parseFloat(goodsPrice).toFixed(2).split('.')[0] }}</text>
                        <text class="price-font text-[32rpx] mr-[10rpx]">.{{ parseFloat(goodsPrice).toFixed(2).split('.')[1] }}</text>
                    </view>
                </view>
                <view class="font-medium text-[30rpx] multi-hidden leading-[40rpx]" :style="{'color': diyComponent.goodsInfo.titleColor}">
                    <view class="brand-tag middle" v-if="diyComponent.goods.goods_brand" :style="diyGoods.baseTagStyle(diyComponent?.goods?.goods_brand)">{{ diyComponent?.goods?.goods_brand?.brand_name }}</view>
                    {{ diyComponent.goods.goods_name }}
                </view>
                <view v-if="diyComponent.goods.sub_title" class="text-[26rpx] my-[16rpx] leading-[33rpx]" :style="{'color': diyComponent.goodsInfo.subTitleColor}">
                    {{diyComponent.goods.sub_title}}
                </view>
                
                <view class="flex flex-wrap mt-[16rpx]" v-if="diyComponent.label_info && diyComponent.label_info.length">
                    <template v-for="item in diyComponent.label_info" :key="item.label_id">
                        <image class="img-tag middle" v-if="item.style_type == 'icon' && item.icon" :src="img(item.icon)" mode="heightFix"  @error="diyGoods.error(item,'icon')" />
                        <view class="base-tag middle" v-else-if="item.style_type == 'diy' || !item.icon" :style="diyGoods.baseTagStyle(item)">{{ item.label_name }}</view>
                    </template>
                </view>
                <view class="flex justify-between items-start mt-[24rpx]">
                    <view class="text-[24rpx] leading-[34rpx]" v-if="diyComponent.market_price && parseFloat(diyComponent.market_price) && saleInfo.includes('underlined_price')"  :style="{'color': diyComponent.goodsInfo.saleInfoColor}">
                        <text class="whitespace-nowrap mr-[4rpx]">划线价:</text>
                        <text class="line-through">￥{{ diyComponent.market_price }}</text>
                    </view>
                    <view class="text-[24rpx] leading-[34rpx]" v-if="saleInfo.includes('stock')" :style="{'color': diyComponent.goodsInfo.saleInfoColor}">
                        <text class="whitespace-nowrap mr-[4rpx]">库存:</text>
                        <text>{{ diyComponent.stock }}</text>
                        <text>{{ diyComponent.goods.unit }}</text>
                    </view>
                    <view class="text-[24rpx] leading-[34rpx] flex items-baseline" v-if="saleInfo.includes('sales')" :style="{'color': diyComponent.goodsInfo.saleInfoColor}">
                        <text class="whitespace-nowrap mr-[4rpx]">销量:</text>
                        <text class="mx-[2rpx]">{{ diyComponent.goods.sale_num }}</text>
                        <text>{{ diyComponent.goods.unit }}</text>
                    </view>
                </view>
            </view>
        </view>
        <!-- 装修时，防止点击 -->
        <view v-if="diyStore.mode == 'decorate'" class="absolute z-10 top-0 right-0 bottom-0 left-0"></view>
    </view>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, nextTick, getCurrentInstance } from 'vue';
import { onPageScroll } from '@dcloudio/uni-app'
import { redirect, img, getToken } from '@/utils/common';
import useDiyStore from '@/app/stores/diy';
import useSystemStore from '@/stores/system'
import { useGoods } from '@/addon/shop/hooks/useGoods'
import useGoodsDetailStore from '@/addon/shop/stores/goodsDetail';

const props = defineProps(['component', 'index', 'value', 'global']);
const diyStore = useDiyStore();
const emits = defineEmits(['loadingFn', 'update:componentIsShow']); //商品数据加载完成之后触发
const topNav = ref(false);
const switchMedia: any = ref('img');
const swiperCurrentIndex = ref(1); // 轮播图当前索引
const videoContext: any = ref(null)
const discountTime = ref(0)
const swiperImageHeight = ref('100vw') // 轮播图高度
const diyGoods = useGoods();

const diyComponent = computed(() => {
    if (diyStore.mode == 'decorate') {
        const obj = {
            price: 50,
            goods:{
                goods_image: [img('static/resource/images/diy/shop_default.jpg')],
                goods_name: '商品名称',
                sub_title: '商品副标题',
                unit: '件',
                sale_num: '50'
            },
            market_price: '100.00',
            stock: 80,
            goods_video: '',
        }
        return Object.assign({},obj,diyStore.value[props.index]);
    } else {
        return Object.assign({}, props.component, useGoodsDetailStore().goodsDetail);
    }
})
const saleInfo = ref([])
const diyGlobal = computed(() => {
    return props.global;
})

const warpCss = computed(() => {
    let style = '';
    style += 'position:relative;';
    if (diyComponent.value.componentStartBgColor && diyComponent.value.componentEndBgColor) style += `background:linear-gradient(${ diyComponent.value.componentGradientAngle },${ diyComponent.value.componentStartBgColor },${ diyComponent.value.componentEndBgColor });`;
    else style += 'background-color:' + (diyComponent.value.componentStartBgColor || diyComponent.value.componentEndBgColor) + ';';

    if (diyComponent.value.componentBgUrl) {
        style += `background-image:url('${ img(diyComponent.value.componentBgUrl) }');`;
        style += 'background-size: cover;background-repeat: no-repeat;';
    }

    if (diyComponent.value.topRounded) style += 'border-top-left-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
    if (diyComponent.value.topRounded) style += 'border-top-right-radius:' + diyComponent.value.topRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomRounded) style += 'border-bottom-left-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    if (diyComponent.value.bottomRounded) style += 'border-bottom-right-radius:' + diyComponent.value.bottomRounded * 2 + 'rpx;';
    return style;
})

/************ 自定义头部-start ****************/
const systemStore = useSystemStore()
let platform = systemStore.systemInfo.platform;

// 导航栏内部盒子的样式
const navbarInnerStyle = computed(() => {
    let style = '';
    // 导航栏宽度，如果在小程序下，导航栏宽度为胶囊的左边到屏幕左边的距离
    // #ifdef MP
    let rightButtonWidth = systemStore.menuButtonInfo.width ? systemStore.menuButtonInfo.width * 2 + 'rpx' : '70rpx';
    style += 'height:' + systemStore.menuButtonInfo.height + 'px;';
    style += 'padding-right:calc(' + rightButtonWidth + ' + 30rpx);';
    style += 'padding-left:calc(' + rightButtonWidth + ' + 30rpx);';
    style += 'padding-top:' + systemStore.menuButtonInfo.top + 'px;';
    style += 'padding-bottom: 8px;';

    style += 'font-size: 32rpx;';
    if (platform === 'ios') {
        // 苹果(iOS)设备
        style += 'font-weight: 500;';
    } else if (platform === 'android') {
        // 安卓(Android)设备
        style += 'font-size: 36rpx;';
    }
    // #endif

    // #ifdef H5
    style += 'height: 100rpx;';
    style += 'padding-right: 30rpx;';
    style += 'padding-left: 30rpx;';

    style += 'font-size: 32rpx;';
    if (platform === 'ios') {
        // 苹果(iOS)设备
        style += 'font-weight: 500;';
    } else if (platform === 'android') {
        // 安卓(Android)设备
        style += 'font-size: 36rpx;';
    }
    // #endif
    
   // #ifdef APP-PLUS
   style += 'height: 80rpx;';
   style += 'padding-right: 30rpx;';
   style += 'padding-left: 30rpx;';
   style += 'padding-top:' + systemStore.systemInfo.statusBarHeight + 'px;';
   // #endif
    
    return style;
})

// 导航栏内部盒子的样式
const navbarInnerArrowStyle = computed(() => {
    let style = '';
    // 导航栏宽度，如果在小程序下，导航栏宽度为胶囊的左边到屏幕左边的距离
    // #ifdef MP
    style += 'position: absolute;';
    style += 'left:calc( 100vw - ' + systemStore.menuButtonInfo.right + 'px);';
    if (platform === 'ios') {
        // 苹果(iOS)设备
        style += 'font-weight: 700;';
    } else if (platform === 'android') {
        // 安卓(Android)设备
    }
    // #endif
    return style;
})

// 导航栏头部卡片样式
const fixedInnerStyle = computed(() => {
    let style = '';
    // #ifdef MP
    style += 'top:' + (systemStore.menuButtonInfo.height + systemStore.menuButtonInfo.top + 8) + 'px;';
    style += 'left:calc( 100vw - ' + systemStore.menuButtonInfo.right + 'px);';
    // #endif
    // #ifdef H5
    style += 'top: 100rpx;';
    style += 'left: 30rpx;';
    // #endif
    // #ifdef APP-PLUS
    style += 'top:' + (systemStore.systemInfo.statusBarHeight + uni.upx2px(100)) + 'px;';
    style += 'left: 30rpx;';
    // #endif
    return style;
})

// 头部滚动
const instance = getCurrentInstance();
let swiperHeight = 0
let detailHead = 0

const detailHeadBgChange = ref(false)
onPageScroll((e) => {
    if (swiperHeight == 0 || detailHead == 0) return;
    let height = swiperHeight - detailHead - 20;
    detailHeadBgChange.value = false;
    if (e.scrollTop >= height) {
        detailHeadBgChange.value = true;
    }
})
/************ 自定义头部-end ****************/

/************* 分享海报-start **************/
const openShareFn = () => {
    useGoodsDetailStore().setGoodsDetail({isOpenSharePoster: true});
}
/************* 分享海报-end **************/

// 菜单列表
const menuList = {
   index: {
        name: '首页',
        iconfont: 'nc-iconfont nc-icon-shouyeV6xx11',
        url: { url: '/addon/shop/pages/index', mode: 'reLaunch' }
   },
   search: {
        name: '搜索',
        iconfont: 'nc-iconfont nc-icon-sousuo-duanV6xx1',
        url: { url: '/addon/shop/pages/goods/search' }
   },
   cart: {
        name: '购物车',
        iconfont: 'nc-iconfont nc-icon-gouwucheV6xx1',
        url: { url: '/addon/shop/pages/goods/cart' }
   },
   member: {
        name: '个人中心',
        iconfont: 'nc-iconfont nc-icon-a-wodeV6xx-36',
        url: { url: '/addon/shop/pages/member/index' }
   },
   collect: {
        name: '我的收藏',
        iconfont: 'nc-iconfont nc-icon-guanzhuV6xx',
        url: { url: '/addon/shop/pages/goods/collect' }
   },
   goods_list: {
        name: '商品列表',
        iconfont: 'iconfont icona-yingyongliebiaoV6xx-32',
        url: { url: '/addon/shop/pages/goods/list' }
   },
   goods_category: {
        name: '商品分类',
        iconfont: 'nc-iconfont nc-icon-shangpinfenlei',
        url: { url: '/addon/shop/pages/goods/category' }
   }
}

const menuContents = computed(() => {
    const list = []
    let menu = typeof diyComponent.value.menuContent == 'object' ? diyComponent.value.menuContent : diyComponent.value.menuContent.split(',')
    menu.forEach((item: any) => {
        if (menuList[item]) {
            list.push(menuList[item])
        }
    })
    return list
})

// 图片轮播change事件
const swiperChangeFn = (e: any) => { 
    swiperCurrentIndex.value = e.current + 1;
}

// 价格类型
//''=>原价，新人价=>newcomer_price，discount_price=>折扣价，member_price=>会员价
const priceType = computed(() => {
    let type = "";
    if (diyStore.mode != 'decorate') {
        if (diyGlobal.value.goodsParameter.type == 'newcomer_discount' && diyComponent.value.newcomer_price) {
            type = 'newcomer_price'
        } else if (diyComponent.value.show_type == 'original_price' && diyComponent.value.priceRegion.showWay == 'fixed') {
            type = ''
        } else {
            type = diyComponent.value.show_type
        }
    } else if (diyComponent.value.priceRegion.showWay == 'normal') {
        type = 'original_price'
    }
    return type;
})

// 商品价格
const goodsPrice = computed(() => {
    let price = "0.00";
    if (diyStore.mode != 'decorate') {
        if (diyGlobal.value.goodsParameter.type == 'newcomer_discount' && diyComponent.value.newcomer_price) {
            price = diyComponent.value.newcomer_price
        } else {
            price = diyComponent.value.show_price
        }
    } else {
        price = '100.00' // 装修模式
    }
    return price;
})

// 普通商品样式
const ordinaryGoodCss = computed(() => {
    let style = "";
    
    if (diyComponent.value.goodsInfo.startBgColor && diyComponent.value.goodsInfo.endBgColor) style += `background:linear-gradient(180deg,${diyComponent.value.goodsInfo.startBgColor} 85% ,${diyComponent.value.goodsInfo.endBgColor} 100%);`;
    else style += 'background-color:' + (diyComponent.value.goodsInfo.startBgColor || diyComponent.value.goodsInfo.endBgColor) + ';';

    if (diyComponent.value.goodsInfo.topMargin) style += 'margin-top:' + diyComponent.value.goodsInfo.topMargin * 2 + 'rpx;';
    if (diyComponent.value.goodsInfo.aboutMargin){
        style += 'margin-left:' + diyComponent.value.goodsInfo.aboutMargin * 2 + 'rpx;';
        style += 'margin-right:' + diyComponent.value.goodsInfo.aboutMargin * 2 + 'rpx;';
    }

    if (diyComponent.value.goodsInfo.topRounded) style += 'border-top-left-radius:' + diyComponent.value.goodsInfo.topRounded * 2 + 'rpx;';
    if (diyComponent.value.goodsInfo.topRounded) style += 'border-top-right-radius:' + diyComponent.value.goodsInfo.topRounded * 2 + 'rpx;';
    if (diyComponent.value.goodsInfo.bottomRounded) style += 'border-bottom-left-radius:' + diyComponent.value.goodsInfo.bottomRounded * 2 + 'rpx;';
    if (diyComponent.value.goodsInfo.bottomRounded) style += 'border-bottom-right-radius:' + diyComponent.value.goodsInfo.bottomRounded * 2 + 'rpx;';
    return style;
})

// 价格模块样式
const marketGoodCss = computed(() => {
    let style = "";

    let typeArr = ['newcomer_price','discount_price','member_price']
    let imgVal = typeArr.indexOf(priceType.value) != -1 ? diyComponent.value.priceRegion.marketingBgImg : diyComponent.value.priceRegion.bgImg;
    style += `background: url(${ img(imgVal) }) no-repeat;`;

    if (diyComponent.value.goodsInfo.priceBgColor) {
        style += `background-color: ${ diyComponent.value.goodsInfo.priceBgColor };`;
    }

    if (diyComponent.value.goodsInfo.priceTopMargin) style += 'margin-top:' + diyComponent.value.goodsInfo.priceTopMargin * 2 + 'rpx;';
    if (diyComponent.value.goodsInfo.priceAboutMargin){
        style += 'margin-left:' + diyComponent.value.goodsInfo.priceAboutMargin * 2 + 'rpx;';
        style += 'margin-right:' + diyComponent.value.goodsInfo.priceAboutMargin * 2 + 'rpx;';
    }

    if (diyComponent.value.goodsInfo.priceTopRounded) style += 'border-top-left-radius:' + diyComponent.value.goodsInfo.priceTopRounded * 2 + 'rpx;';
    if (diyComponent.value.goodsInfo.priceTopRounded) style += 'border-top-right-radius:' + diyComponent.value.goodsInfo.priceTopRounded * 2 + 'rpx;';
    if (diyComponent.value.goodsInfo.priceBottomRounded) style += 'border-bottom-left-radius:' + diyComponent.value.goodsInfo.priceBottomRounded * 2 + 'rpx;';
    if (diyComponent.value.goodsInfo.priceBottomRounded) style += 'border-bottom-right-radius:' + diyComponent.value.goodsInfo.priceBottomRounded * 2 + 'rpx;';
    return style;
})

const goodsVideoHeight = computed(() => {
    // 这个主要是解决微信小程序的视频播放被商品信息模块遮挡的问题，如果商品信息模块的margin-top是负值，对应的视频高度也需要减去这个负值，但如果是正值就无需处理
    let style = "";
    let height = 0;
    // #ifdef MP
    if (priceType.value != 'original_price') {
        height = diyComponent.value.goodsInfo.priceTopMargin * 2;
    } else {
        height = diyComponent.value.goodsInfo.topMargin * 2;
    }
    height = height > 0? 0 : height;
    style = `height:calc(100% - ${Math.abs(height)}rpx);`;
    // #endif
    // #ifdef H5
    style = `height: 100%;`;
    // #endif
    return style;
})

const mediumCss = computed(() => {
    let style = "";
    if (diyComponent.value.medium.type == 'height_adaptive') {
        style += `height:${swiperImageHeight.value};`;
    } else {
        style += 'height:100vw;';
    }
    return style;
})

const timeData = ref({});
// 定义 onChange 方法
const onChange = (e) => {
    timeData.value = e;
};

const swiperClick = (index: any) => {
    if (typeof index == 'number') imgListPreview(diyComponent.value.goods.goods_image, index)
}

//预览图片
const imgListPreview = (item: any, index: any) => {
    if (Array.isArray(item)) {
        if (!item.length) return false
        var urlList = item;
        uni.previewImage({
            indicator: "number",
            current: index,
            loop: true,
            urls: urlList
        })
    } else {
        if (item === '') return false
        var urlList = []
        urlList.push(img(item))  //push中的参数为 :src="item.img_url" 中的图片地址
        uni.previewImage({
            indicator: "number",
            loop: true,
            urls: urlList
        })
    }

}

// 返回上一页
const backToPrevious = () => {
    if (getCurrentPages().length > 1) {
        uni.navigateBack({
            delta: 1
        });
    } else {
        redirect({
            url: '/addon/shop/pages/index',
            mode: 'reLaunch'
        });
    }
}

const initFn = () => {
    if (diyComponent.value?.goods.goods_video != '') {
        switchMedia.value = 'video'
        videoContext.value = uni.createVideoContext('goodsVideo');
    }
    saleInfo.value = (typeof diyComponent.value?.saleInfo == 'object') ? diyComponent.value?.saleInfo : diyComponent.value?.saleInfo.split(',');

    // 折扣信息
    if (Object.keys(diyComponent.value?.goods).length && diyGlobal.value.goodsParameter.type == 'discount' && diyComponent.value?.goods.is_discount && Object.keys(diyComponent.value?.discount_info).length) {
        let now = new Date();
        let timestamp: any = now.getTime();
        discountTime.value = diyComponent.value?.discount_info.active.end_time * 1000 - timestamp.toFixed(0)
    }else{
        let now = new Date();
        let timestamp: any = now.getTime();
        if (diyComponent.value && diyComponent.value?.discount_info && diyComponent.value?.discount_info.active && diyComponent.value?.discount_info.active.end_time) {
            discountTime.value = diyComponent.value?.discount_info.active.end_time * 1000 - timestamp.toFixed(0) 
        } 
    }

    setTimeout(() => {
        const query = uni.createSelectorQuery().in(instance);
        query.select('.swiper-box').boundingClientRect((data: any) => {
            swiperHeight = data ? data.height : 0;
        }).exec();
        query.select('.detail-head').boundingClientRect((data: any) => {
            if (data) {
                detailHead = data.height ? data.height : 0;
            }
        }).exec();
    }, 400)

    // 轮播图高度
    if (diyComponent.value.medium.type == 'height_adaptive') {
        const screenWidth = uni.getSystemInfoSync().screenWidth
        swiperImageHeight.value = (screenWidth * diyComponent.value.goods.image_size.height / diyComponent.value.goods.image_size.width) * 2 + 'rpx'
    } else {
        swiperImageHeight.value = '100vw'
    }
}

onMounted(() => {
    saleInfo.value = (typeof diyComponent.value?.saleInfo == 'object') ? diyComponent.value?.saleInfo : diyComponent.value?.saleInfo.split(',');
    // 装修模式下刷新
    if (diyStore.mode == 'decorate') {
        watch(
            () => diyComponent.value,
            (newValue, oldValue) => {
                if (newValue && newValue.componentName == 'ShopGoodsDetailBasicInfo') {
                    nextTick(() => {
                        saleInfo.value = (typeof diyComponent.value?.saleInfo == 'object') ? diyComponent.value?.saleInfo : diyComponent.value?.saleInfo.split(',');
                    })
                }
            }
        )
    } else {
        initFn()
        watch(
            () => diyComponent.value,
            (newValue, oldValue) => {
                if (newValue && newValue.componentName == 'ShopGoodsDetailBasicInfo') {
                    emits('update:componentIsShow', true)
                }       
            },
            { immediate: true }
        )
    }
});
</script>
<style lang="scss" scoped>
.arrow-left {
    // background: rgba(153, 153, 153, 0.1);
    background: rgba(255, 255, 255, 0.6);
    border: 1rpx solid rgba(0, 0, 0, 0.1);
}
.media-mode {
    .tab-item {
        color: #fff;
        font-size: 24rpx;
        line-height: 46rpx;
        border-radius: 50rpx;
        padding: 0 20rpx;
        display: inline-block;
    }
}
.brand-tag {
  position: relative;
  top: -2rpx;
  display: inline;
  line-height: 38rpx;
  padding: 4rpx 8rpx;
  border-radius: 4rpx;
  margin-right: 8rpx;
  background: red;
  vertical-align: middle;
  font-size: 18rpx;
  color: #fff;
  border: 2rpx solid transparent;

  &.middle {
    padding: 4rpx 8rpx;
    border-radius: 8rpx;
    font-size: 20rpx;
    margin-right: 6rpx;
  }
}
.base-tag {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 34rpx;
  font-size: 18rpx;
  padding: 0 8rpx;
  color: #333;
  border-radius: 4rpx;
  background-color: #fff;
  margin-right: 8rpx;
  box-sizing: border-box;
  margin-top: 8rpx;
  border: 2rpx solid transparent;

  &.middle {
    height: 40rpx;
    padding: 0 12rpx;
    border-radius: 8rpx;
    font-size: 20rpx;
    margin-right: 16rpx;
  }
}
.img-tag {
  display: block;
  height: 34rpx;
  width: auto;
  border-radius: 4rpx;
  margin-right: 14rpx;
  box-sizing: border-box;
  margin-top: 8rpx;

  &.middle {
    height: 38rpx;
    border-radius: 8rpx;
    margin-right: 16rpx;
  }
}
.goods-video-height{
   height: 100%; 
}
:deep(.uni-video-bar) {
    bottom: 34rpx !important;
}

:deep(.uni-video-cover) {
    background: none;
}

:deep(.uni-video-cover-duration) {
    display: none;
}

:deep(.uni-video-cover-play-button) {
    border-radius: 50%;
    border: 4rpx solid #fff;
    width: 120rpx;
    height: 120rpx;
    background-size: 30%;
}
</style>

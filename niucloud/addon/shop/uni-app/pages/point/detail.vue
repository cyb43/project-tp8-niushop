<template>
    <view :style="themeColor()">
        <view class="bg-[var(--page-bg-color)] min-h-[100vh] relative" v-if="Object.keys(goodsDetail).length">
            <!-- 自定义头部 -->
            <view class="flex items-center fixed left-0 right-0 z-10 bg-transparent detail-head"
                  :class="{'!bg-[#fff]' :detailHeadBgChange}" :style="navbarInnerStyle">
                <text class="nc-iconfont nc-icon-zuoV6xx" :style="navbarInnerArrowStyle" @click="backToPrevious()"></text>
                <view class="ml-auto !pt-[12rpx] !pb-[8rpx] p-[10rpx] bg-[rgba(255,255,255,.4)] rounded-full border-[2rpx] border-solid border-transparent box-border nc-iconfont nc-icon-fenxiangV6xx font-bold text-[#303133] text-[36rpx]"
                    :class="{'border-[#d8d8d8]': detailHeadBgChange}" @click="openShareFn"></view>
            </view>
            <view class="w-full h-[100vw] relative overflow-hidden">
                <view class="absolute top-0 left-0 w-full h-full transition-transform duration-300 ease-linear transform"
                :class="{'translate-x-0':switchMedia === 'img','translate-x-full':switchMedia != 'img'}">
                    <view class="swiper-box">
                        <u-swiper :list="goodsDetail.goods.goods_image" :indicator="goodsDetail.goods.goods_image.length"       @change="swiperChangeFn"
                                :indicatorStyle="{'bottom': '68rpx',}" :autoplay="true" height="100vw"
                                @click="swiperClick"></u-swiper>
                    </view>
                </view>
                <view @touchmove.stop.prevent class="media-mode absolute top-0 left-0 w-full h-full transition-transform duration-300 ease-linear transform"
                :class="{'translate-x-0':switchMedia === 'video','-translate-x-full':switchMedia != 'video'}" :style="{background: 'url(' + img(goodsDetail.goods.goods_cover_thumb_mid) + ') left bottom / cover no-repeat'}">
                  <view class="goods-video-height">
                        <video id="goodsVideo" class="w-full h-full" :src="img(goodsDetail.goods.goods_video)" :poster="img(goodsDetail.goods.goods_cover_thumb_mid)" objectFit="cover" play-btn-position="center"></video>
                    </view>
                </view>
                <!-- 切换视频、图片 -->
                <view class="media-mode bg-[rgb(0,0,0,.5)] rounded-[50rpx] p-[4rpx] absolute bottom-[130rpx] right-[20rpx] text-center leading-[46rpx]" v-if="goodsDetail.goods.goods_video != ''">
                    <text class="tab-item" :class="{ '!bg-[#fff] !text-[#666]': switchMedia == 'video' }" @click="switchMedia = 'video'">视频</text>
                    <view class="tab-item flex items-center" :class="{ '!bg-[#fff] !text-[#666]': switchMedia == 'img' }" @click="(switchMedia = 'img'), videoContext.pause()">
                        <text class="mr-[4rpx]">图片</text>
                        <text v-if="switchMedia == 'img' && goodsDetail?.goods?.goods_image?.length > 1">{{swiperCurrentIndex}}/{{goodsDetail?.goods?.goods_image?.length}}</text>
                    </view>
                </view>
            </view>
            <view class="rounded-t-[40rpx] -mt-[40rpx] relative flex items-center justify-between !bg-cover box-border pb-[26rpx] h-[136rpx] px-[30rpx]"
                :style="{ background: 'url(' + img('addon/shop/detail/discount_price_bg.png') + ') no-repeat'}">
                <view class="flex items-baseline text-[#fff]">
                    <view class="flex items-center">
                        <view class="inline-block price-font" v-if="goodsDetail.point">
                            <text class="text-[44rpx]">{{ goodsDetail.point }}</text>
                            <text class="text-[38rpx]">{{ t('point') }}</text>
                        </view>
                        <text class="text-[38rpx]" v-if="goodsDetail.point&&parseFloat(goodsDetail.price)">+</text>
                        <view class="inline-block price-font" v-if="parseFloat(goodsDetail.price)">
                            <text class="text-[44rpx]">{{ parseFloat(goodsDetail.price).toFixed(2) }}</text>
                            <text class="text-[38rpx]">{{ t('priceUnit') }}</text>
                        </view>
                    </view>
                </view>
            </view>

            <view class="bg-[var(--page-bg-color)] overflow-hidden rounded-[40rpx] -mt-[28rpx] relative">
                <view class="detail-title relative px-[30rpx] pt-[40rpx]">
                    <view class="text-[#333] font-medium text-[30rpx] multi-hidden leading-[40rpx]">
                        <view class="brand-tag middle" v-if="goodsDetail.goods.goods_brand" :style="diyGoods.baseTagStyle(goodsDetail.goods.goods_brand)">{{ goodsDetail.goods.goods_brand.brand_name }}</view>
                        {{ goodsDetail.goods.goods_name }}
                    </view>
					<view class="text-[26rpx] text-[#666] truncate my-[16rpx] leading-[33rpx]">
						{{goodsDetail.goods.sub_title}}
					</view>
                    <view class="flex flex-wrap mt-[16rpx]" v-if="goodsDetail.label_info && goodsDetail.label_info.length">
                        <template v-for="item in goodsDetail.label_info" :key="item.label_id">
                            <image class="img-tag middle" v-if="item.style_type == 'icon' && item.icon" :src="img(item.icon)" mode="heightFix" @error="diyGoods.error(item,'icon')" />
                            <view class="base-tag middle" v-else-if="item.style_type == 'diy' || !item.icon" :style="diyGoods.baseTagStyle(item)">
                                {{ item.label_name }}
                            </view>
                        </template>
                    </view>
					<view class="flex justify-between items-start mt-[24rpx]  ">
					    <view class="text-[24rpx] leading-[34rpx] text-[var(--text-color-light6)]" v-if="goodsDetail.market_price && parseFloat(goodsDetail.market_price)">
					        <text class="whitespace-nowrap mr-[4rpx]">划线价:</text>
					        <text class="line-through">￥{{ goodsDetail.market_price }}</text>
					    </view>
					    <view class="text-[24rpx] leading-[34rpx] text-[var(--text-color-light6)]" v-if="goodsDetail.stock && parseFloat(goodsDetail.stock)">
					        <text class="whitespace-nowrap mr-[4rpx]">库存:</text>
					        <text>{{ goodsDetail.stock }}</text>
					        <text>{{ goodsDetail.goods.unit }}</text>
					    </view>
					    <view class="text-[24rpx] leading-[34rpx] text-[var(--text-color-light6)] flex items-baseline">
					        <text class="whitespace-nowrap mr-[4rpx]">销量:</text>
					        <text class="mx-[2rpx]">{{ goodsDetail.goods.sale_num }}</text>
					        <text>{{ goodsDetail.goods.unit }}</text>
					    </view>
					</view>
                </view>
                <view class="mt-[24rpx] sidebar-margin card-template" v-if="isGoodsPropertyTemp">
                    <view @click="servicesDataShow = !servicesDataShow" v-if="goodsDetail.service && goodsDetail.service.length" class="card-template-item">
                        <text class="text-[#333] text-[26rpx] leading-[30rpx] font-400 shrink-0">服务</text>
                        <view class="text-[#343434] text-[26rpx] leading-[30rpx] font-400 truncate ml-auto">{{ goodsDetail.service[0].service_name }}</view>
                        <text class="nc-iconfont nc-icon-youV6xx text-[26rpx] text-[var(--text-color-light6)] ml-[8rpx]"></text>
                    </view>
                    <view @click="buyFn" v-if="goodsDetail.goodsSpec && goodsDetail.goodsSpec.length" class="card-template-item">
                        <text class="text-[#333] text-[26rpx] leading-[30rpx] font-400 shrink-0  mr-[20rpx]">已选</text>
                        <view class="ml-auto text-right truncate flex-1 text-[#343434] text-[26rpx] leading-[30rpx] font-400">{{ goodsDetail.sku_spec_format }}</view>
                        <text class="nc-iconfont nc-icon-youV6xx text-[26rpx] text-[var(--text-color-light6)] ml-[8rpx]"></text>
                    </view>
                    <view class="card-template-item" @click="distributionDataOpen" v-if="goodsDetail.goods.goods_type == 'real' && goodsDetail.delivery_type_list && goodsDetail.delivery_type_list.length">
                        <text class="text-[#333] text-[26rpx] leading-[30rpx] font-400 shrink-0">配送</text>
                        <view class="ml-auto flex items-center text-[#343434] text-[26rpx] leading-[30rpx] font-400">{{ goodsDetail.delivery_type_list[selectDeliveryType].name }}</view>
                        <text class="nc-iconfont nc-icon-youV6xx text-[26rpx] text-[var(--text-color-light6)] ml-[8rpx]"></text>
                    </view>

                </view>

                <view class="mt-[var(--top-m)] sidebar-margin card-template">
                    <view class="flex items-center justify-between min-h-[40rpx]" :class="{'mb-[30rpx]': evaluate && evaluate.list && evaluate.list.length}">
                        <text class="title !mb-[0]">宝贝评价({{ evaluate.count }})</text>
                        <view v-if="evaluate.count" class="h-[40rpx] flex items-center" @click="toLink(goodsDetail.goods_id)">
                            <text class="text-[24rpx] text-[var(--text-color-light9)]">查看全部</text>
                            <text class="nc-iconfont nc-icon-youV6xx text-[26rpx] text-[var(--text-color-light9)]"></text>
                        </view>
                        <text v-if="!evaluate.count" class="text-[24rpx] text-[var(--text-color-light6)]">暂无评价
                        </text>
                    </view>
                    <view>
                        <view :class="{'pb-[34rpx]': index != (evaluate.list.length-1)}" v-for="(item, index) in evaluate.list" :key="index">
                            <view class="flex items-center w-full">
                                <u-avatar :default-url="img('static/resource/images/default_headimg.png')" :src="img(item.member_head)" :size="'50rpx'" leftIcon="none" />
                                <text class="ml-[10rpx] text-[28rpx] text-[#333]">{{ item.member_name }}</text>
                            </view>
                            <view class="flex justify-between w-full mt-[16rpx]">
                                <view class="flex-1 w-[540rpx] text-[26rpx] text-[#333] max-h-[72rpx] leading-[36rpx] multi-hidden mr-[50rpx]">{{ item.content }}</view>
                                <view class="w-[80rpx] shrink-0">
                                    <u--image v-if="item.image_mid && item.image_mid.length" width="80rpx" height="80rpx" radius="16rpx" :src="img(item.image_mid[0])" model="aspectFill" @click="imgListPreview(item.images[0])">
                                        <template #error>
                                            <u-icon name="photo" color="#999" size="50"></u-icon>
                                        </template>
                                    </u--image>
                                </view>
                            </view>
                        </view>
                    </view>
                </view>

                <view class="my-[var(--top-m)] goods-sku sidebar-margin card-template" v-if="goodsDetail.goods && goodsDetail.goods.attr_format && Object.keys(goodsDetail.goods.attr_format).length">
                    <view class="title mb-[30rpx]">商品属性</view>
                    <view>
                        <template v-for="(item,index) in goodsDetail.goods.attr_format" :key="index">
                            <view v-if="index < 4 || isAttrFormatShow" class="card-template-item">
                                <view class="text-[26rpx] leading-[30rpx] w-[160rpx] font-400 shrink-0 text-[var(--text-color-light9)]">{{ item.attr_value_name }}</view>
                                <view class="text-[#333] box-border value-wid text-[26rpx] leading-[30rpx] font-400 pl-[20rpx]">{{ Array.isArray(item.attr_child_value_name) ? item.attr_child_value_name.join(',') : item.attr_child_value_name }}</view>
                            </view>
                        </template>
                        <view v-if="goodsDetail.goods.attr_format.length > 4" class="flex-center" @click="isAttrFormatShow = !isAttrFormatShow">
                            <text class="text-[24rpx] mr-[10rpx]">{{ isAttrFormatShow ? '展开' : '收起' }}</text>
                            <text class="nc-iconfont !text-[22rpx]" :class="{'nc-icon-xiaV6xx': isAttrFormatShow, 'nc-icon-shangV6xx-1': !isAttrFormatShow}"></text>
                        </view>
                    </view>
                </view>

                <view class="my-[var(--top-m)] sidebar-margin card-template p-[0] pt-[30rpx] overflow-hidden">
                    <view class="title px-[24rpx]">商品详情</view>
                    <view class="u-content">
                        <u-parse :content="goodsDetail.goods.goods_desc" :tagStyle="{img: 'vertical-align: top;',p:'overflow: hidden;word-break:break-word;' }"></u-parse>
                    </view>
                </view>

                <view class="tab-bar-placeholder"></view>
                <view class="border-[0] border-t-[2rpx] border-solid border-[#f5f5f5] w-[100%] flex justify-between pl-[32rpx] pr-[4rpx] bg-[#fff] box-border fixed left-0 bottom-0 tab-bar z-1 items-center">
                    <view class="flex items-center">
                        <view class="flex flex-col justify-center items-center mr-[38rpx]" @click="redirect({ url: '/addon/shop/pages/index', mode: 'reLaunch' })">
                            <view class="nc-iconfont nc-icon-shouyeV6xx text-[36rpx]"></view>
                            <text class="text-[20rpx] mt-[10rpx]">首页</text>
                        </view>
                        <!-- #ifdef H5 -->
                        <view class="flex flex-col justify-center items-center mr-[38rpx]" @click="openShareFn">
                            <view class="nc-iconfont nc-icon-fenxiangV6xx text-[36rpx]"></view>
                            <text class="text-[20rpx] mt-[10rpx]">分享</text>
                        </view>
                        <!-- #endif -->
                        <!-- #ifdef MP-WEIXIN -->
                        <view>
                            <nc-contact
                                :send-message-title="sendMessageTitle"
                                :send-message-path="sendMessagePath"
                                :send-message-img="sendMessageImg">
                                <view class="flex flex-col justify-center items-center mr-[38rpx]">
                                    <text class="nc-iconfont nc-icon-kefuV6xx-1 text-[36rpx]"></text>
                                    <text class="text-[20rpx] mt-[10rpx]">客服</text>
                                </view>
                            </nc-contact>
                        </view>
                        <!-- #endif -->
                    </view>
                    <view class="flex flex-1" v-if="goodsDetail.goods.status == 1">
                        <button v-if="isShowSingleSku"
                            class="primary-btn-bg flex-1 !h-[70rpx] font-500 text-[26rpx] !text-[#fff] !m-0 !mr-[16rpx] leading-[70rpx] rounded-full remove-border"
                            @click="buyFn('buy_now')">立即兑换</button>
                        <button v-else class="flex-1 !h-[70rpx] font-500 text-[26rpx] !text-[#fff] !bg-[#ccc] !m-0 !mr-[16rpx] leading-[70rpx] rounded-full remove-border">已售罄</button>
                    </view>
                    <view class="flex flex-1" v-else>
                        <button class="w-[100%] !h-[70rpx] font-500 text-[26rpx] !text-[#fff] !bg-[#ccc] !m-0 leading-[70rpx] rounded-full remove-border">该商品已下架</button>
                    </view>
                </view>
            </view>
            <!-- 服务 -->
            <view @touchmove.prevent.stop>
                <u-popup class="popup-type" :show="servicesDataShow" @close="servicesDataShow = false">
                    <view class="popup-common min-h-[480rpx]" @touchmove.prevent.stop>
                        <view class="title">商品服务</view>
                        <scroll-view class="h-[520rpx]" scroll-y="true">
                            <view class="pl-[22rpx] pb-[28rpx] pr-[37rpx]">
                                <view class="flex mb-[28rpx]" v-for="(item, index) in goodsDetail.service">
                                    <image class="mt-[4rpx] w-[32rpx] h-[32rpx] mr-[14rpx]" :src="img(item.image || 'addon/shop/icon_service.png')" mode="aspectFit" />
                                    <view class="flex-1">
                                        <view class="text-[30rpx] leading-[36rpx] text-[#333] mb-[8rpx]">{{ item.service_name }}</view>
                                        <view class="text-[24rpx] leading-[36rpx] text-[var(--text-color-light9)]">{{ item.desc }}</view>
                                    </view>
                                </view>
                            </view>
                        </scroll-view>
                    </view>
                </u-popup>
            </view>
            <!-- 配送 -->
            <view @touchmove.prevent.stop>
                <u-popup class="popup-type" :show="distributionDataShow" @close="distributionDataShow = false">
                    <view class="min-h-[360rpx] popup-common" @touchmove.prevent.stop>
                        <view class="title">配送方式</view>
                        <scroll-view class="h-[520rpx]" scroll-y="true">
                            <view class="px-[var(--popup-sidebar-m)]">
                                <view class="flex mb-[40rpx]" v-for="(item, index) in goodsDetail.delivery_type_list" @click="distributionListFn(item,index)">
                                    <image class="mt-[4rpx] w-[32rpx] h-[32rpx] mr-[14rpx]" :src="img('addon/shop/icon_service.png')" mode="aspectFit" />
                                    <view class="flex-1">
                                        <view class="text-[30rpx] leading-[36rpx] text-[#333] mb-[8rpx]">{{ item.name }}</view>
                                        <view class="text-[24rpx] leading-[36rpx] text-[var(--text-color-light9)]">{{ item.desc }}</view>
                                    </view>
                                </view>
                            </view>
                        </scroll-view>
                    </view>
                </u-popup>
            </view>
            <goods-sku ref="goodsSkuRef" :goods-detail="goodsDetail" @change="specSelectFn"></goods-sku>
            <share-poster ref="sharePosterRef" posterType="shop_point_goods" :posterParam="posterParam" :copyUrlParam="copyUrlParam" />
        </view>
        <loading-page :loading="loading"></loading-page>

        <!-- #ifdef MP-WEIXIN -->
        <!-- 小程序隐私协议 -->
        <wx-privacy-popup ref="wxPrivacyPopupRef"></wx-privacy-popup>
        <!-- #endif -->
    </view>
</template>

<script setup lang="ts">
import { ref, computed, getCurrentInstance, nextTick } from 'vue';
import { onLoad, onShow, onUnload, onPageScroll } from '@dcloudio/uni-app'
import { img, redirect, handleOnloadParams, deepClone, goback } from '@/utils/common';
import { t } from '@/locale';
import { getEvaluateList } from '@/addon/shop/api/goods';
import { getExchangeGoodsDetail } from '@/addon/shop/api/point';
import goodsSku from '@/addon/shop/pages/point/components/goods-sku.vue';
import useMemberStore from '@/stores/member'
import { useShare } from '@/hooks/useShare'
import sharePoster from '@/components/share-poster/share-poster.vue'
import { useGoods } from '@/addon/shop/hooks/useGoods'
import useSystemStore from "@/stores/system";

const diyGoods = useGoods();
// 分享
const { setShare } = useShare()
const swiperCurrentIndex = ref(1); // 轮播图当前索引
const switchMedia: any = ref('img');
const videoContext: any = ref(null)

// 会员信息
const memberStore = useMemberStore()
const userInfo = computed(() => memberStore.info)

const sendMessageTitle = ref('')
const sendMessagePath = ref('')
const sendMessageImg = ref('')

const goodsSkuRef: any = ref(null);
const goodsDetail: any = ref({});

const isAttrFormatShow = ref(false); //控制属性是否展开

const loading = ref<boolean>(false);
const servicesDataShow = ref<boolean>(false)
const distributionDataShow = ref<boolean>(false) //配送

const wxPrivacyPopupRef: any = ref(null)

onLoad((option: any) => {

    // #ifdef MP-WEIXIN
    // 处理小程序场景值参数
    option = handleOnloadParams(option);
    // #endif
    //
    getExchangeGoodsDetail(option.id || '',).then((res: any) => {
        if (JSON.stringify(res.data) === '[]') {
            let goBackParameter = {
                url: '/addon/shop/pages/index',
                title: '找不到该商品',
                mode: 'reLaunch'
            };
            goback(goBackParameter)
            return false
        }

        goodsDetail.value = deepClone(res.data);

        goodsDetail.value.delivery_type_list = goodsDetail.value.goods.delivery_type_list ? Object.values(goodsDetail.value.goods.delivery_type_list) : [];
        goodsDetail.value.goods.goods_image = goodsDetail.value.goods.goods_image_thumb_big;
        goodsDetail.value.goods.goods_image.forEach((item: any, index: any) => {
            goodsDetail.value.goods.goods_image[index] = img(item);
        })

        let data: any = deepClone(res.data);
        goodsDetail.value.goods.attr_format = []
        if (data.goods && data.goods.attr_format) {
            let attrFormatArr: any = deepClone(data.goods.attr_format);
            attrFormatArr.forEach((item: any, index: any) => {
                if ((item.attr_child_value_name && !(item.attr_child_value_name instanceof Array)) || ((item.attr_child_value_name instanceof Array) && item.attr_child_value_name.length)) {
                    goodsDetail.value.goods.attr_format.push(item);
                }
            })
        }
        if (goodsDetail.value.goods.goods_video != '') {
            switchMedia.value = 'video'
            videoContext.value = uni.createVideoContext('goodsVideo');
        }
        sendMessageTitle.value = goodsDetail.value.goods.goods_name
        sendMessagePath.value = '/addon/shop/pages/point/detail?sku_id=' + goodsDetail.value.sku_id;
        sendMessageImg.value = img(goodsDetail.value.goods.goods_cover_thumb_mid)

        // 分享 - start
        let share = {
            title: goodsDetail.value.goods.goods_name,
            desc: goodsDetail.value.goods.sub_title,
            url: goodsDetail.value.goods.goods_cover_thumb_mid
        }
        uni.setNavigationBarTitle({
            title: goodsDetail.value.goods.goods_name
        })
        setShare({
            wechat: {
                ...share
            },
            weapp: {
                ...share
            }
        });
        // 分享 - end

        // 获取评价
        getEvaluateListFn();

        copyUrlFn();

        nextTick(() => {
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
            // #ifdef MP
            if (wxPrivacyPopupRef.value) wxPrivacyPopupRef.value.proactive();
            // #endif
        })
    })
})

onShow(() => {
    // 删除配送方式
    uni.removeStorageSync('distributionType');
})

const specSelectFn = (id: any) => {
    goodsDetail.value.skuList.forEach((item: any, index: any) => {
        if (item.sku_id == id) {
            Object.assign(goodsDetail.value, item);
        }
    })
}

// 判断单规格库存是否为0
const isShowSingleSku = computed(() => {
    let isSingleSpec = false // 是否为单规格，true：多规格，false：单规格
    goodsDetail.value.skuList.length && goodsDetail.value.skuList.forEach((item: any, index: any) => {
        if (item.sku_spec_format) {
            isSingleSpec = true
        }
    })

    // 单规格，库存为0，显示已售罄
    if (!isSingleSpec && goodsDetail.value.stock <= 0) {
        return false;
    } else if (!isSingleSpec && goodsDetail.value.stock > 0) {
        // 单规格，库存大于0，可以购买
        return true;
    }
    return true;
})

// 判断商品属性模块是否展示
const isGoodsPropertyTemp = computed(() => {
    let bool = false;
    if (goodsDetail.value.service && goodsDetail.value.service.length ||
        goodsDetail.value.goodsSpec && goodsDetail.value.goodsSpec.length ||
        goodsDetail.value.goods.goods_type == 'real' && goodsDetail.value.delivery_type_list && goodsDetail.value.delivery_type_list.length) {
        bool = true;
    }
    return bool;
})

const buyFn = (type: any) => {
    goodsSkuRef.value.open(type)
}

// 获取评价
const evaluate = ref({
    count: 0
})

const getEvaluateListFn = () => {
    getEvaluateList(goodsDetail.value.goods_id).then((res: any) => {
        evaluate.value = res.data
    })
}

//进入评论
const toLink = () => {
    redirect({ url: '/addon/shop/pages/evaluate/list', param: { goods_id: goodsDetail.value.goods_id } })
}

//预览图片
const imgListPreview = (item: any, index: any) => {
    if (Array.isArray(item)) {
        var urlList = item;
        if (!item.length) return false
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

/************ 选择配送方式-start ****************/
const selectDeliveryType = ref(0);
const distributionDataOpen = (() => {
    distributionDataShow.value = true;
});
const distributionListFn = ((data: any, index: any) => {
    selectDeliveryType.value = index;
    distributionDataShow.value = false;
    uni.setStorageSync('distributionType', data.name);
});
/************ 选择配送方式-end ****************/


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
    return style;
})

// 导航栏内部盒子的样式
const navbarInnerArrowStyle = computed(() => {
    let style = '';
    // 导航栏宽度，如果在小程序下，导航栏宽度为胶囊的左边到屏幕左边的距离
    // #ifdef MP
    style += "padding-left: 10rpx;"
    style += "padding-right: 10rpx;"
    style += 'position: absolute;';
    style += 'left:calc( 100vw - ' + systemStore.menuButtonInfo.right + 'px);';
    style += 'font-size: 26px;';
    // style += 'font-weight: bold;';
    if (platform === 'ios') {
        // 苹果(iOS)设备
        style += 'font-weight: 700;';
    } else if (platform === 'android') {
        // 安卓(Android)设备
    }
    // #endif
    // #ifdef H5
    style += 'font-size: 26px;';
    // #endif
    return style;
})

// 头部滚动
const instance = getCurrentInstance();
let swiperHeight = 0
let detailHead = 0

const detailHeadBgChange = ref(false)
onPageScroll((e) => {
    let height = swiperHeight - detailHead - 20;
    detailHeadBgChange.value = false;
    if (e.scrollTop >= height) {
        detailHeadBgChange.value = true;
    }
})
/************ 自定义头部-end ****************/

const swiperClick = (index: any) => {
    if (typeof index == 'number') imgListPreview(goodsDetail.value.goods.goods_image, index)
}

/************* 分享海报-start **************/
const sharePosterRef: any = ref(null);
const copyUrlParam = ref('');
let posterParam: any = {};

// 分享海报链接
const copyUrlFn = () => {
    copyUrlParam.value = '?id=' + goodsDetail.value.exchange_id;
    if (userInfo.value && userInfo.value.member_id) copyUrlParam.value += '&mid=' + userInfo.value.member_id;
}
const openShareFn = () => {
    posterParam.id = goodsDetail.value.exchange_id;
    if (userInfo.value && userInfo.value.member_id) {
        posterParam.member_id = userInfo.value.member_id;
    }
    sharePosterRef.value.openShare()
}
/************* 分享海报-end **************/

// 关闭预览图片
onUnload(() => {
    // #ifdef  H5 || APP
    try {
        uni.closePreviewImage()
    } catch (e) {

    }
    // #endif
})

// 图片轮播change事件
const swiperChangeFn = (e: any) => { 
    swiperCurrentIndex.value = e.current + 1;
}
</script>
<style lang="scss" scoped>
@import '@/addon/shop/styles/common.scss';

.remove-border {
    &::after {
        border: none;
    }
}

:deep(.u-cell-group__wrapper) {
    .u-cell__body {
        padding: 23rpx 32rpx;
    }
}

.popup-type {
    :deep(.u-popup__content) {
        border-top-left-radius: 16rpx;
        border-top-right-radius: 16rpx;
        overflow: hidden;
    }
}

.tab-bar-placeholder {
    padding-bottom: calc(constant(safe-area-inset-bottom) + 100rpx);
    padding-bottom: calc(env(safe-area-inset-bottom) + 100rpx);
}

.tab-bar {
    padding-top: 16rpx;
    padding-bottom: calc(constant(safe-area-inset-bottom) + 16rpx);
    padding-bottom: calc(env(safe-area-inset-bottom) + 16rpx);
}

:deep(.u-count-down) .u-count-down__text {
    color: #fff;
    font-size: 28rpx;
}

:deep(.u-swiper-indicator__wrapper--line__bar) {
    height: 5rpx !important;
}

:deep(.u-swiper-indicator__wrapper--line) {
    height: 5rpx !important;
}

.detail-title {
    background: linear-gradient(#fff 70%, #F6F6F6);
}

.goods-sku .value-wid {
    width: calc(100% - 160rpx);
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
.goods-video-height{
   height: calc(100vw - 44rpx); 
}
</style>

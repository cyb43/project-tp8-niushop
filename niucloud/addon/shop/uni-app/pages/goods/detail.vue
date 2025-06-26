<template>
    <view :style="themeColor()">
        <view class="bg-[var(--page-bg-color)] min-h-[100vh] relative" v-if="Object.keys(goodsDetail).length">
            <!-- 自定义头部 -->
            <view class="flex items-center fixed left-0 right-0 z-10 bg-transparent detail-head" :class="{'!bg-[#fff]' :detailHeadBgChange}" :style="navbarInnerStyle">
                <view class="flex-center h-[60rpx] rounded-[30rpx] box-border arrow-left px-[20rpx] leading-[1]" :style="navbarInnerArrowStyle">
                    <text class="nc-iconfont nc-icon-zuoV6xx text-[18px]" @click="backToPrevious()"></text>
                    <text class="w-[2rpx] h-[26rpx] bg-[#999] mx-[14rpx]"></text>
                    <text class="nc-iconfont nc-icon-liebiao-xiV6xx1 text-[16px]" @click="topNav = true"></text>
                </view>
                <view class="ml-auto !pt-[12rpx] !pb-[8rpx] p-[10rpx] bg-[rgba(255,255,255,.4)] rounded-full border-[2rpx] border-solid border-transparent box-border nc-iconfont nc-icon-fenxiangV6xx font-bold text-[#303133] text-[36rpx]"
                    :class="{'border-[#d8d8d8]': detailHeadBgChange}" @click="openShareFn"></view>
            </view>
            <view class="fixed top-0 left-0 right-0 bottom-0 z-100 bg-transparent" @click="topNav = false" v-if="topNav">
                <view class="search-box w-[202rpx] bg-[#fff] rounded-[12rpx] relative" :style="fixedInnerStyle">
                    <view class="px-[20rpx] flex-center" @click="redirect({ url: '/addon/shop/pages/index', mode: 'reLaunch' })">
                        <text class="nc-iconfont nc-icon-shouyeV6xx11 text-[30rpx] mr-[10rpx]"></text>
                        <text class="pl-[14rpx] py-[20rpx] flex-1 text-[24rpx] text-[#333] border-0 border-[#ddd] border-b-[1rpx] border-solid">首页</text>
                    </view>
                    <view class="px-[20rpx] flex-center" @click="redirect({ url: '/addon/shop/pages/goods/search'})">
                        <text class="nc-iconfont nc-icon-sousuo-duanV6xx1 text-[30rpx] mr-[10rpx]"></text>
                        <text class="pl-[14rpx] py-[20rpx] flex-1 text-[24rpx] text-[#333] border-0 border-[#ddd] border-b-[1rpx] border-solid">搜索</text>
                    </view>
                    <view class="px-[20rpx] flex-center" @click="redirect({ url: '/addon/shop/pages/goods/cart'})">
                        <text class="nc-iconfont nc-icon-gouwucheV6xx1 text-[30rpx] mr-[10rpx]"></text>
                        <text class="pl-[14rpx] py-[20rpx] flex-1 text-[24rpx] text-[#333] border-0 border-[#ddd] border-b-[1rpx] border-solid">购物车</text>
                    </view>
                    <view class="px-[20rpx] flex-center" @click="redirect({ url: '/addon/shop/pages/member/index'})">
                        <text class="nc-iconfont nc-icon-a-wodeV6xx-36 text-[30rpx] mr-[10rpx]"></text>
                        <text class="pl-[14rpx] py-[20rpx] flex-1 text-[24rpx] text-[#333]">个人中心</text>
                    </view>
                </view>
            </view>
            <view class="w-full h-[100vw] relative overflow-hidden">
                <view class="absolute top-0 left-0 w-full h-full transition-transform duration-300 ease-linear transform"
                    :class="{'translate-x-0':switchMedia === 'img','translate-x-full':switchMedia != 'img'}">
                    <view class="swiper-box">
                      <u-swiper :list="goodsDetail.goods.goods_image"
                                  @change="swiperChangeFn"
                                  :indicator="goodsDetail.goods.goods_image.length"
                                  :indicatorStyle="{'bottom': '70rpx'}" :autoplay="switchMedia === 'img'?true:false"
                                  height="100vw" radius="0" @click="swiperClick"></u-swiper>
                    </view>
                </view>
                <view @touchmove.stop.prevent class="media-mode absolute top-0 left-0 w-full h-full transition-transform duration-300 ease-linear transform"
                  :class="{'translate-x-0':switchMedia === 'video','-translate-x-full':switchMedia != 'video'}" :style="{background: 'url(' + img(goodsDetail.goods.goods_cover_thumb_mid) + ') left bottom / cover no-repeat'}">
                  <view class="goods-video-height">
                    <video id="goodsVideo" :show-background-playback-button="false" :picture-in-picture-mode="[]" class="w-full h-full" :src="img(goodsDetail.goods.goods_video)" :poster="img(goodsDetail.goods.goods_cover_thumb_mid)" objectFit="cover" play-btn-position="center"></video>
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
            <view v-if="priceType != 'original_price'"
                  class="rounded-t-[40rpx] -mt-[44rpx] relative flex items-center justify-between !bg-cover box-border pb-[26rpx] h-[136rpx] px-[30rpx]"
                  :style="{ background: 'url(' + img('addon/shop/detail/discount_price_bg.png') + ') no-repeat'}">
                <view class="text-[#fff]">
                    <text class="text-[26rpx] mr-[10rpx] font-500 leading-[36rpx]" v-if="priceType == 'newcomer_price'">新人价</text>
                    <text class="text-[26rpx] mr-[10rpx] font-500 leading-[36rpx]" v-else-if="priceType == 'discount_price'">折扣价</text>
                    <text class="text-[26rpx] mr-[10rpx] font-500 leading-[36rpx]" v-else-if="priceType == 'member_price'">会员价</text>
                    <view class="inline-block mr-[14rpx]">
                        <text class="text-[32rpx] price-font mr-[4rpx]">￥</text>
                        <text class="text-[48rpx] -mb-[4rpx] price-font">{{ parseFloat(goodsPrice).toFixed(2).split('.')[0] }}</text>
                        <text class="text-[32rpx] price-font">.{{ parseFloat(goodsPrice).toFixed(2).split('.')[1] }}</text>
                    </view>
                    <view class="inline-block">
                        <text class="text-[26rpx] mr-[6rpx]" v-if="goodsDetail.price">售价:</text>
                        <text class="text-[26rpx] price-font leading-[36rpx]">￥{{ goodsDetail.price }}</text>
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
            <view class="bg-[var(--page-bg-color)] rounded-[40rpx] overflow-hidden -mt-[34rpx] relative">
                <view class="detail-title relative px-[30rpx]"
                      :class="{'pt-[40rpx]': priceType != 'original_price','pt-[30rpx]': priceType == 'original_price'}">
                    <view class="text-[var(--price-text-color)] flex items-baseline mb-[12rpx]" v-if="priceType == 'original_price'">
                        <view class="inline-block goods-price-time">
                            <text class="price-font text-[32rpx]">￥</text>
                            <text class="price-font text-[48rpx]">{{ parseFloat(goodsPrice).toFixed(2).split('.')[0] }}</text>
                            <text class="price-font text-[32rpx] mr-[10rpx]">.{{ parseFloat(goodsPrice).toFixed(2).split('.')[1] }}</text>
                        </view>
                        <!-- <text class="text-[26rpx] text-[var(--text-color-light9)] line-through price-font" v-if="goodsDetail.market_price && parseFloat(goodsDetail.market_price)">
                          ￥{{ goodsDetail.market_price }}
                        </text> -->
                    </view>
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
                            <view class="base-tag middle" v-else-if="item.style_type == 'diy' || !item.icon" :style="diyGoods.baseTagStyle(item)">{{ item.label_name }}</view>
                        </template>
                    </view>
					<view class="flex justify-between items-start mt-[24rpx]">
					    <view class="text-[24rpx] leading-[34rpx] text-[var(--text-color-light6)]" v-if="goodsDetail.market_price && parseFloat(goodsDetail.market_price)">
					        <text class="whitespace-nowrap mr-[4rpx]">划线价:</text>
					        <text class="line-through">￥{{ goodsDetail.market_price }}</text>
					    </view>
					    <view class="text-[24rpx] leading-[34rpx] text-[var(--text-color-light6)]">
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
                        <text class="text-[#333] text-[26rpx] leading-[30rpx] font-400 shrink-0 mr-[20rpx]">已选</text>
                        <view class="ml-auto text-right truncate flex-1 text-[#343434] text-[26rpx] leading-[30rpx] font-400">{{ goodsDetail.sku_spec_format }}</view>
                        <text class="nc-iconfont nc-icon-youV6xx text-[26rpx] text-[var(--text-color-light6)] ml-[8rpx]"></text>
                    </view>
                    <view class="card-template-item" @click="distributionDataOpen"
                          v-if="goodsDetail.goods.goods_type == 'real'&&goodsDetail.delivery_type_list&&goodsDetail.delivery_type_list.length">
                        <text class="text-[#333] text-[26rpx] leading-[30rpx] font-400 shrink-0">配送</text>
                        <view class="ml-auto flex items-center text-[#343434] text-[26rpx] leading-[30rpx] font-400">{{ goodsDetail.delivery_type_list[selectDeliveryType].name }}</view>
                        <text class="nc-iconfont nc-icon-youV6xx text-[26rpx] text-[var(--text-color-light6)] ml-[8rpx]"></text>
                    </view>
                    <view @click="couponListShow = true" v-if="couponList.length" class="card-template-item">
                        <text class="text-[#333] text-[26rpx] leading-[30rpx] font-400 shrink-0 mr-[20rpx]">领券</text>
                        <view class="ml-auto flex-1 flex-nowrap flex items-center overflow-hidden h-[44rpx] content-between">
                            <template v-for="(item, index) in couponList" :key="index">
                                <text v-if="index < 3"
                                      class="tag-item whitespace-nowrap border-[2rpx] px-[6rpx] h-[40rpx] border-solid border-[var(--primary-color)] text-[var(--primary-color)] mt-[4rpx]"
                                      :class="{'mr-[12rpx]': couponList.length != (index+1) && index < 2, 'ml-auto': index == 0}">{{ item.title }}</text>
                            </template>
                        </view>
                        <text class="nc-iconfont nc-icon-youV6xx text-[26rpx] text-[var(--text-color-light6)] ml-[8rpx]"></text>
                    </view>
                    <view class="card-template-item" @click="manjianOpenFn" v-if="Object.keys(manjianData).length">
                        <text class="text-[#333] text-[26rpx] leading-[30rpx] font-400 shrink-0 mr-[20rpx]">优惠</text>
                        <view class="ml-auto flex-1 flex-nowrap flex items-center overflow-hidden h-[44rpx] justify-end">
                            <view class="bg-[var(--primary-color-light)] text-[var(--primary-color)] rounded-[6rpx]	text-[22rpx] flex items-center justify-center w-[86rpx] h-[34rpx] mr-[6rpx]">满减送</view>
                            <view class="truncate max-w-[430rpx] text-[26rpx]">{{ manjianData.name }}</view>
                        </view>
                        <text class="nc-iconfont nc-icon-youV6xx text-[26rpx] text-[var(--text-color-light6)] ml-[8rpx]"></text>
                    </view>
                </view>
                <view class="sidebar-margin" v-if="goodsDetail.sow_show_list">
                    <sow-show :items="goodsDetail.sow_show_list"></sow-show>
                </view>
                <view v-if="goodsDetail.evaluate_is_show" class="mt-[var(--top-m)] sidebar-margin card-template">
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
                                    <u--image v-if="item.image_mid && item.image_mid.length" width="80rpx"
                                              height="80rpx" radius="var(--goods-rounded-mid)"
                                              :src="img(item.image_mid[0])" model="aspectFill"
                                              @click="imgListPreview(item.images[0])">
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
                                <text class="text-[26rpx] leading-[30rpx] w-[160rpx] font-400 shrink-0 text-[var(--text-color-light9)]">{{ item.attr_value_name }}</text>
                                <view class="text-[#333] box-border value-wid text-[26rpx] leading-[30rpx] font-400 pl-[20rpx]">{{ Array.isArray(item.attr_child_value_name) ? item.attr_child_value_name.join(',') : item.attr_child_value_name }}</view>
                                <!-- <text class="nc-iconfont nc-icon-youV6xx text-[26rpx] text-[var(--text-color-light6)] ml-[8rpx]"></text> -->
                            </view>
                        </template>
                        <view v-if="goodsDetail.goods.attr_format.length > 4" class="flex-center" @click="isAttrFormatShow = !isAttrFormatShow">
                            <text class="text-[24rpx] mr-[10rpx]">{{ !isAttrFormatShow ? '展开' : '收起' }}</text>
                            <text class="nc-iconfont !text-[22rpx]" :class="{'nc-icon-xiaV6xx': !isAttrFormatShow, 'nc-icon-shangV6xx-1': isAttrFormatShow}"></text>
                        </view>
                    </view>
                </view>

                <view class="my-[var(--top-m)] sidebar-margin card-template px-[var(--pad-sidebar-m)]">
                    <view class="title">商品详情</view>
                    <view class="u-content">
                        <u-parse :content="goodsDetail.goods.goods_desc" :tagStyle="{img: 'vertical-align: top;',p:'overflow: hidden;word-break:break-word;' }"></u-parse>
                    </view>
                </view>

                <ns-goods-recommend></ns-goods-recommend>

                <view class="tab-bar-placeholder"></view>
                <view class="border-[0] border-t-[2rpx] border-solid border-[#f5f5f5] w-[100%] flex justify-between pl-[32rpx] pr-[4rpx] bg-[#fff] box-border fixed left-0 bottom-0 tab-bar z-1 items-center">
                    <view class="flex items-center">
                        <view class="flex flex-col justify-center items-center mr-[38rpx]" @click="redirect({ url: '/addon/shop/pages/index', mode: 'reLaunch' })">
                            <view class="nc-iconfont nc-icon-shouyeV6xx11 text-[36rpx]"></view>
                            <text class="text-[20rpx] mt-[10rpx]">首页</text>
                        </view>
                        <!-- #ifdef H5 -->
                        <view class="flex flex-col justify-center items-center mr-[38rpx]" @click="redirect({ url: '/addon/shop/pages/goods/cart'})">
                            <view class="iconfont icongouwuche2 text-[38rpx]"></view>
                            <text class="text-[20rpx] mt-[10rpx]">购物车</text>
                        </view>
                        <!-- #endif -->
                        <view class="flex flex-col justify-center items-center mr-[38rpx]" @click="collectFn">
                            <text class="nc-iconfont text-[36rpx]" :class="{'text-[#ff0000] nc-icon-xihuanV6mm': isCollect, 'text-[#303133] nc-icon-guanzhuV6xx' : !isCollect}"></text>
                            <text class="text-[20rpx] mt-[10rpx]">收藏</text>
                        </view>

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
                        <button v-if="goodsDetail.goods.is_gift"
                                class="!w-[420rpx] flex-1 !h-[70rpx] font-500 text-[26rpx] !text-[#fff] !bg-[#ccc] !m-0 leading-[70rpx] rounded-full remove-border"
                        >商品为赠品不可购买
                        </button>
                        <template v-else-if="maxBuy > 0 || maxBuy == -1">
                            <button
                                v-if="pageParameter.type != 'newcomer_discount' && (goodsDetail.goods.goods_type == 'real' || (goodsDetail.goods.goods_type == 'virtual' && goodsDetail.goods.virtual_receive_type != 'verify'))"
                                class="cart-btn-bg flex-1 !h-[70rpx] font-500 text-[26rpx] !text-[#fff] !m-0 !mr-[16rpx] leading-[70rpx] rounded-full remove-border" @click="buyFn('join_cart')">
                                加入购物车
                            </button>
                            <button
                                v-if="isShowSingleSku"
                                :style="{ width : (goodsDetail.goods.goods_type == 'real' || (goodsDetail.goods.goods_type == 'virtual' && goodsDetail.goods.virtual_receive_type != 'verify')) ?  '200rpx' : '400rpx' + '!important'  }"
                                class="flex-1 !h-[70rpx] font-500 text-[26rpx] !text-[#fff] primary-btn-bg !m-0 !mr-[16rpx] leading-[70rpx] rounded-full remove-border"
                                @click="buyFn('buy_now')">立即购买
                            </button>
                            <button v-else
                                    :style="{ width : (goodsDetail.goods.goods_type == 'real' || (goodsDetail.goods.goods_type == 'virtual' && goodsDetail.goods.virtual_receive_type != 'verify')) ?  '200rpx' : '400rpx' + '!important'  }"
                                    class="flex-1 !h-[70rpx] font-500 text-[26rpx] !text-[#fff] !bg-[#ccc] !m-0 !mr-[16rpx] leading-[70rpx] rounded-full remove-border"
                            >已售罄
                            </button>
                        </template>
                        <button v-else-if="maxBuy == 0"
                                :style="{ width : '420rpx' + '!important'  }"
                                class="flex-1 !h-[70rpx] font-500 text-[26rpx] !text-[#fff] !bg-[#ccc] !m-0 leading-[70rpx] rounded-full remove-border"
                        >已达限购数量
                        </button>
                    </view>
                    <view class="flex flex-1" v-else>
                        <button class="w-[100%] !h-[70rpx] font-500 text-[26rpx] !text-[#fff] !bg-[#ccc] !m-0 leading-[70rpx] rounded-full remove-border">该商品已下架</button>
                    </view>
                </view>
            </view>
            <!-- 服务 -->
            <view @touchmove.prevent.stop>
                <u-popup class="popup-type" :show="servicesDataShow" @close="servicesDataShow = false">
                    <view class="min-h-[480rpx] popup-common" @touchmove.prevent.stop>
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
            <!-- 优惠券 -->
            <view @touchmove.prevent.stop>
                <u-popup class="popup-type" :show="couponListShow" @close="couponListShow = false">
                    <view class="min-h-[480rpx] popup-common" @touchmove.prevent.stop>
                        <view class="title">优惠券</view>
                        <scroll-view class="h-[520rpx]" scroll-y="true">
                            <view class="px-[32rpx]">
                                <view class="mb-[30rpx] flex items-center border-[2rpx] border-solid border-[rgba(0,0,0,.1)] rounded-[var(--rounded-small)]"
                                    v-for="(item, index) in couponList" :key="index">
                                    <view
                                        class="flex flex-col items-center my-[20rpx] w-[200rpx] border-0 border-r-[2rpx] border-dashed border-[rgba(0,0,0,.1)]">
                                        <view class="text-xs price-font">
                                            <text class="text-[28rpx]">￥</text>
                                            <text class="text-[48rpx]">{{ Number(item.price) }}</text>
                                        </view>
                                        <text class="text-xs mt-[12rpx]">{{ Number(item.min_condition_money) ? ('满' + Number(item.min_condition_money) + '元可用') : '无门槛' }}</text>
                                    </view>
                                    <view class="ml-[20rpx] flex-1 flex flex-col py-[20rpx]">
                                        <text class="text-xs font-500">{{ item.title }}</text>
                                        <text class="text-xs text-[var(--text-color-light6)] mt-[12rpx]">{{ item.valid_type == 1 && ('领取之日起' + item.length + '天内有效') || item.valid_type == 2 && ('领取之日起至' + item.valid_end_time) }}</text>
                                    </view>
                                    <text v-if="item.btnType === 'collecting'" class="bg-[var(--primary-color)] mr-[20rpx] w-[106rpx] box-border text-center text-[#fff] h-[50rpx] text-[22rpx] px-[20rpx] leading-[50rpx] rounded-[100rpx]" @click="getCouponFn(item, index)">领取</text>
                                    <text v-else class="!bg-[var(--primary-help-color4)] mr-[20rpx] text-[#fff] mr-[20rpx] h-[50rpx] text-[22rpx] px-[20rpx] leading-[50rpx] rounded-[100rpx]">{{ item.btnType === 'collected' ? '已领完' : '已领取' }}</text>
                                </view>
                            </view>
                        </scroll-view>
                        <view class="btn-wrap">
                            <button class="primary-btn-bg btn" @click="couponListShow = false">确定</button>
                        </view>
                    </view>
                </u-popup>
            </view>
            <ns-goods-sku v-if="loading" ref="goodsSkuRef" :goods-detail="goodsDetail" @change="specSelectFn"></ns-goods-sku>
            <ns-goods-manjian ref="manjianShowRef"></ns-goods-manjian>
            <share-poster ref="sharePosterRef" posterType="shop_goods" :posterId="goodsDetail.goods.poster_id" :posterParam="posterParam" :copyUrlParam="copyUrlParam" />
        </view>

        <!-- #ifdef MP-WEIXIN -->
        <!-- 小程序隐私协议 -->
        <wx-privacy-popup ref="wxPrivacyPopupRef"></wx-privacy-popup>
        <!-- #endif -->
    </view>
</template>

<script setup lang="ts">
import { ref, computed, getCurrentInstance, nextTick, } from 'vue';
import { onLoad, onShow, onUnload, onPageScroll } from '@dcloudio/uni-app'
import { img, redirect, handleOnloadParams, getToken, deepClone, goback } from '@/utils/common';
import { t } from '@/locale';
import { getGoodsDetail, browse, collect, cancelCollect, getEvaluateList, getManjian } from '@/addon/shop/api/goods';
import { getShopCouponList, getCoupon } from '@/addon/shop/api/coupon';
import nsGoodsSku from '@/addon/shop/components/ns-goods-sku/ns-goods-sku.vue';
import nsGoodsManjian from '@/addon/shop/components/ns-goods-manjian/ns-goods-manjian.vue';
import useCartStore from '@/addon/shop/stores/cart'
import { useLogin } from '@/hooks/useLogin'
import useMemberStore from '@/stores/member'
import { useShare } from '@/hooks/useShare'
import sharePoster from '@/components/share-poster/share-poster.vue'
import { useGoods } from '@/addon/shop/hooks/useGoods'
import nsGoodsRecommend from '@/addon/shop/components/ns-goods-recommend/ns-goods-recommend.vue';
import sowShow from '@/components/sow-show/sow-show.vue';

const diyGoods = useGoods();
const timeData = ref({});
// 定义 onChange 方法
const onChange = (e) => {
    timeData.value = e;
};
const swiperCurrentIndex = ref(1); // 轮播图当前索引

// 分享
const { setShare } = useShare()

// 会员信息
const memberStore = useMemberStore()
const userInfo = computed(() => memberStore.info)

// 购物车数量
const cartStore = useCartStore();
const cartTotalNum = computed(() => cartStore.totalNum)

const goodsSkuRef: any = ref(null);
const goodsDetail: any = ref({});
const switchMedia: any = ref('img');
const manjianShowRef: any = ref(null); //满减送

const isAttrFormatShow = ref(false); //控制属性是否展开
const topNav = ref(false);

const loading = ref<boolean>(false);
const servicesDataShow = ref<boolean>(false)
const distributionDataShow = ref<boolean>(false) //配送
const couponListShow = ref<boolean>(false) //优惠券
const discountTime = ref(0)

const sendMessageTitle = ref('')
const sendMessagePath = ref('')
const sendMessageImg = ref('')

const wxPrivacyPopupRef: any = ref(null)
const videoContext: any = ref(null)
let pageParameter = {};

onLoad((option: any) => {
    // #ifdef MP-WEIXIN
    // 处理小程序场景值参数
    option = handleOnloadParams(option);
    // #endif
    pageParameter = option;
})

onShow(() => {
    setTimeout(() => { //延迟器用于sku框中的表单上传图片时，让onshow慢与图片上传方法
        // 详情sku-表单，上传图片时不触发onshow内部方法
        if (!uni.getStorageSync('sku_form_refresh')) {
            loading.value = false;
            // 删除配送方式
            uni.removeStorageSync('distributionType');
            cartStore.getList();
            getManjianInfo();
            getDetailInfo();
        } else {
            uni.removeStorageSync('sku_form_refresh');
        }
    })
})

const getDetailInfo = () => {
    getGoodsDetail({
        goods_id: pageParameter.goods_id || '',
        sku_id: pageParameter.sku_id || '',
        type: pageParameter.type || ''  // 来源营销活动类型，例如：discount：限时折扣，newcomer_discount：新人价
    }).then((res: any) => {
        if (!res.data.goods || JSON.stringify(res.data) === '[]') {
            let goBackParameter = {
                url: '/addon/shop/pages/index',
                title: '找不到该商品',
                mode: 'reLaunch'
            };
            goback(goBackParameter)
            return false
        }

        goodsDetail.value = deepClone(res.data);
        isCollect.value = goodsDetail.value.goods.is_collect;
        goodsDetail.value.delivery_type_list = goodsDetail.value.goods.delivery_type_list ? Object.values(goodsDetail.value.goods.delivery_type_list) : [];
        // goodsDetail.value.goods.goods_image = goodsDetail.value.goods.goods_image_thumb_big;
		goodsDetail.value.goods.goods_image = goodsDetail.value.goods.goods_image.split(',');
        goodsDetail.value.goods.goods_image.forEach((item: any, index: any) => {
            goodsDetail.value.goods.goods_image[index] = img(item);
        })


        loading.value = true;

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
        sendMessagePath.value = '/addon/shop/pages/goods/detail?sku_id=' + goodsDetail.value.sku_id;
        if (goodsDetail.value.type) {
            sendMessagePath.value += '&type=' + goodsDetail.value.type;
        }
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

        // 折扣信息
        if (Object.keys(goodsDetail.value.goods).length && goodsDetail.value.type == 'discount' && goodsDetail.value.goods.is_discount && Object.keys(goodsDetail.value.discount_info).length) {
            let now = new Date();
            let timestamp: any = now.getTime();
            discountTime.value = goodsDetail.value.discount_info.active.end_time * 1000 - timestamp.toFixed(0)
        }
        // 商品限购
        goodsMaxBuy(goodsDetail.value);

        // 获取优惠券列表
        getShopCouponListFn();

        // 获取评价
        getEvaluateListFn();

        if (getToken()) {
            // 我的足迹
            myBrowseFn(goodsDetail.value.goods.goods_id);
        }

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

                if (sharePosterRef.value) {
                    posterParam.sku_id = goodsDetail.value.sku_id;
					if(pageParameter.type)   posterParam.active = pageParameter.type;
                    if (userInfo.value && userInfo.value.member_id) posterParam.member_id = userInfo.value.member_id;
                    sharePosterRef.value.loadPoster();
                }

            }, 400)

            // #ifdef MP
            if (wxPrivacyPopupRef.value) wxPrivacyPopupRef.value.proactive();
            // #endif
        })
    })
}

// 我的足迹
const myBrowseFn = (goods_id: any) => {
    browse({
        goods_id
    }).then((res: any) => {
    })
}

// 获取满减信息
let manjianData = ref({})
const getManjianInfo = () => {
    getManjian({
        goods_id: pageParameter.goods_id || '',
        sku_id: pageParameter.sku_id || ''
    }).then((res: any) => {
        if (Object.keys(res.data).length) {
            manjianData.value.condition_type = res.data.condition_type;
            manjianData.value.rule_json = res.data.rule_json;
            manjianData.value.name = res.data.manjian_name;
        }
    })
}

// 商品限购
const maxBuy = ref(-1);
const goodsMaxBuy = (data: any = {}) => {
    // 限购 - 是否开启限购
    if (data.goods.is_limit && userInfo.value && data.goods.stock > 0) {
        if (data.goods.max_buy) {
            let max_buy = 0;
            if (data.goods.limit_type == 1) { //单次限购
                max_buy = data.goods.max_buy;
            } else { // 单人限购
                let buyVal = data.goods.max_buy - (data.goods.has_buy || 0);
                max_buy = buyVal > 0 ? buyVal : 0;
            }

            if (max_buy > data.goods.stock) {
                maxBuy.value = data.goods.stock
            } else if (max_buy <= data.goods.stock) {
                maxBuy.value = max_buy;
            }
        }
    }
}

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
    goodsDetail.value.skuList.forEach((item: any, index: any) => {
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
        goodsDetail.value.goods.goods_type == 'real' && goodsDetail.value.delivery_type_list && goodsDetail.value.delivery_type_list.length || couponList.value.length) {
        bool = true;
    }
    return bool;
})

const manjianOpenFn = () => {
    manjianShowRef.value.open(manjianData.value);
}

const buyFn = (type: any) => {
    if (goodsSkuRef.value) {
        goodsSkuRef.value.open(type)
    }
}

// 收藏
const isCollect: any = ref(0);
const collectFn = () => {
    // 检测是否登录
    if (!userInfo.value) {
        uni.showToast({
            title: '未登录，请先登录后再收藏商品',
            icon: 'none'
        });
        return false
    }
    let api = isCollect.value ? cancelCollect({ goods_ids: [goodsDetail.value.goods_id] }) : collect(goodsDetail.value.goods_id);
    api.then(res => {
        isCollect.value = !isCollect.value;
        if (isCollect.value) {
            uni.showToast({
                title: '收藏成功',
                icon: 'none'
            });
        } else {
            uni.showToast({
                title: '取消收藏',
                icon: 'none'
            });
        }
    })
}

// 优惠券
const couponList = ref([]);
const getShopCouponListFn = () => {
    getShopCouponList({
        category_id: goodsDetail.value.goods.goods_category || '',
        goods_id: goodsDetail.value.goods_id || ''
    }).then((res: any) => {
        couponList.value = res.data.data.map((el: any) => {
            if (el.sum_count != -1 && el.receive_count === el.sum_count) {
                el.btnType = 'collected'//已领完
            }
            if (!userInfo.value) {
                el.btnType = 'collecting'//领用
            } else {
                if (el.is_receive && el.limit_count === el.member_receive_count) {
                    el.btnType = 'using'//去使用
                } else {
                    el.btnType = 'collecting'//领用
                }
            }
            return el
        });
    })
}

// 领取优惠券
const getCouponFn = (data: any, index: any) => {
    // 检测是否登录
    if (!userInfo.value) {
        useLogin().setLoginBack({
            url: '/addon/shop/pages/goods/detail',
            param: { sku_id: goodsDetail.value.sku_id, type: goodsDetail.value.type }
        })
        return false
    }
    getCoupon({
        coupon_id: data.id || '',
        number: 1,
    }).then(res => {
        // couponList.value[index].btnType = 'using'
        getShopCouponListFn();
    })
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
// 获取系统状态栏的高度
let systemInfo = uni.getSystemInfoSync();
let platform = systemInfo.platform;
let menuButtonInfo: any = {};
// 如果是小程序，获取右上角胶囊的尺寸信息，避免导航栏右侧内容与胶囊重叠(支付宝小程序非本API，尚未兼容)
// #ifdef MP-WEIXIN || MP-BAIDU || MP-TOUTIAO || MP-QQ
menuButtonInfo = uni.getMenuButtonBoundingClientRect();
// #endif

// 导航栏内部盒子的样式
const navbarInnerStyle = computed(() => {
    let style = '';
    // 导航栏宽度，如果在小程序下，导航栏宽度为胶囊的左边到屏幕左边的距离
    // #ifdef MP
    let rightButtonWidth = menuButtonInfo.width ? menuButtonInfo.width * 2 + 'rpx' : '70rpx';
    style += 'height:' + menuButtonInfo.height + 'px;';
    style += 'padding-right:calc(' + rightButtonWidth + ' + 30rpx);';
    style += 'padding-left:calc(' + rightButtonWidth + ' + 30rpx);';
    style += 'padding-top:' + menuButtonInfo.top + 'px;';
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
    style += 'position: absolute;';
    style += 'left:calc( 100vw - ' + menuButtonInfo.right + 'px);';
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
    style += 'top:' + (menuButtonInfo.height + menuButtonInfo.top + 8) + 'px;';
    style += 'left:calc( 100vw - ' + menuButtonInfo.right + 'px);';
    // #endif
    // #ifdef H5
    style += 'top: 100rpx;';
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

const swiperClick = (index: any) => {
    if (typeof index == 'number') imgListPreview(goodsDetail.value.goods.goods_image, index)
}

/************* 分享海报-start **************/
const sharePosterRef: any = ref(null);
const copyUrlParam = ref('');
let posterParam: any = {};

// 分享海报链接
const copyUrlFn = () => {
    copyUrlParam.value = '?sku_id=' + goodsDetail.value.sku_id;
    if (goodsDetail.value.type) {
        copyUrlParam.value += '&type=' + goodsDetail.value.type;
    }
    if (userInfo.value && userInfo.value.member_id) copyUrlParam.value += '&mid=' + userInfo.value.member_id;
}

const openShareFn = () => {
    sharePosterRef.value.openShare()
}
/************* 分享海报-end **************/

// 价格类型
//''=>原价，新人价=>newcomer_price，discount_price=>折扣价，member_price=>会员价
const priceType = computed(() =>  {
    let type = "";
	if(goodsDetail.value.type == 'newcomer_discount'&&getToken()&&goodsDetail.value.newcomer_price){
		type='newcomer_price'
	}else{
		type = goodsDetail.value.show_type
	}
	
    return type;
})
// 商品价格
const goodsPrice = computed(() => {
    let price = "0.00";
	if(goodsDetail.value.type == 'newcomer_discount' &&getToken() &&goodsDetail.value.newcomer_price){
		price=goodsDetail.value.newcomer_price
	}else{
		price = goodsDetail.value.show_price
	}
    return price;
})

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

.arrow-left {
    background: rgba(153, 153, 153, 0.1);
    border: 1rpx solid rgba(221, 221, 221, 0.3);
}

.search-box {
    &::after {
        content: '';
        width: 20rpx;
        height: 20rpx;
        background-color: #fff;
        position: absolute;
        left: 50%;
        top: -10rpx;
        transform: translateX(-50%) rotate(45deg);
    }
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

/*  #ifdef MP  */
.goods-price-time {
    animation: fadein .1s;
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
.goods-video-height{
   height: calc(100vw - 44rpx); 
}
</style>

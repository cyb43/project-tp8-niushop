<template>
    <view :style="themeColor()">
        <view class="bg-[var(--page-bg-color)] min-h-screen overflow-hidden" v-if="!loading">
            <view v-if="!loading" class="pb-20rpx">
                <view v-if="detail.status_name" class="pl-[40rpx] pr-[35rpx] bg-linear pb-[100rpx]">
                    <!-- #ifdef MP-WEIXIN -->
                    <top-tabbar :data="topTabbarData" :scrollBool="topTabarObj.getScrollBool()" />
                    <!-- #endif -->
                    <view class="flex justify-between items-center pt-[40rpx]">
                        <view class="flex flex-col">
                            <image class="w-[28rpx] h-[20rpx] mb-[10rpx] ml-[4rpx]" :src="img('addon/shop/detail/head_001.png')" mode="aspectFit" />
                            <view class="text-[#fff] text-[36rpx] font-500 leading-[42rpx]">{{ detail.status_name.name }}</view>
                        </view>
                        <view class="flex items-end relative -bottom-[6rpx]">
                            <image v-if="detail.status == 1" class="w-[160rpx] h-[140rpx]"
                               :src="img('addon/shop/detail/payment.png')" mode="aspectFit" />
                            <image v-if="detail.status == 2" class="w-[160rpx] h-[140rpx]"
                                :src="img('addon/shop/detail/deliver_goods.png')" mode="aspectFit" />
                            <image v-if="detail.status == 3" class="w-[160rpx] h-[140rpx]"
                                :src="img('addon/shop/detail/receive.png')" mode="aspectFit" />
                            <image v-if="detail.status == 5" class="w-[160rpx] h-[140rpx]"
                                :src="img('addon/shop/detail/complete.png')" mode="aspectFit" />
                            <image v-if="detail.status == -1" class="w-[160rpx] h-[140rpx]"
                                :src="img('addon/shop/detail/close.png')" mode="aspectFit" />
                            <image class="w-[8rpx] h-[30rpx] mb-[20rpx]"
                                :src="img('addon/shop/detail/head_002.png')" mode="aspectFit" />
                        </view>
                    </view>
                </view>
                <view class="sidebar-margin mt-[-86rpx] card-template" v-if="detail.delivery_type != 'virtual'">
                    <view v-if="detail.delivery_type == 'express'">
                        <view class="text-[#303133] flex">
                            <text class="nc-iconfont nc-icon-dizhiguanliV6xx text-[40rpx] pt-[12rpx] mr-[20rpx]"></text>
                            <view class="flex flex-col">
                                <view class="text-[30rpx] leading-[38rpx] overflow-hidden">
                                    <text>{{ detail.taker_name }}</text>
                                    <text class="ml-[15rpx]">{{ detail.taker_mobile }}</text>
                                </view>
                                <view class="mt-[12rpx] text-[24rpx] text-[var(--text-color-light6)] using-hidden leading-[26rpx]">{{ detail.taker_full_address.split(detail.taker_address)[0] }}{{ detail.taker_address }}</view>
                            </view>
                        </view>
                    </view>
                    <view v-if="detail.delivery_type == 'store'">
                        <view class="flex items-center mb-3">
                            <view>
                                <u--image class="overflow-hidden" radius="var(--goods-rounded-mid)" width="100rpx"
                                          height="100rpx" :src="img(detail.store.store_logo ? detail.store.store_logo : '')" model="aspectFill">
                                    <template #error>
                                        <image class="w-[100rpx] h-[100rpx] rounded-[var(--goods-rounded-mid)] overflow-hidden" :src="img('addon/shop/store_default.png')" mode="aspectFill" />
                                    </template>
                                </u--image>
                            </view>
                            <view class="flex flex-col ml-[20rpx]">
                                <text class="text-[30rpx] font-500 text-[#303133] mb-[20rpx]">{{ detail.store.store_name }}</text>
                                <text class="text-[24rpx] text-[var(--text-color-light6)] mb-[14rpx]">{{ detail.store.trade_time }}</text>
                                <text class="text-[24rpx] text-[var(--text-color-light6)] leading-[1.4]">{{ detail.store.full_address }}</text>
                            </view>
                        </view>
						<view class="justify-between card-template-item" v-if="detail.taker_name">
						    <view class="text-[28rpx]">姓名</view>
						    <view class="text-[28rpx]">{{ detail.taker_name }}</view>
						</view>
						<view class="justify-between card-template-item" v-if="detail.taker_mobile">
						    <view class="text-[28rpx]">预留手机</view>
						    <view class="text-[28rpx]">{{ detail.taker_mobile }}</view>
						</view>
						<view class="justify-between card-template-item" v-if="detail.buyer_ask_delivery_time">
						    <view class="text-[28rpx]">提货时间</view>
						    <view class="text-[28rpx]">{{ detail.buyer_ask_delivery_time }}</view>
						</view>
                    </view>
                    <view class="flex" v-if="detail.delivery_type == 'local_delivery'">
                        <text @click="getAddress"
                              class="nc-iconfont nc-icon-dizhiguanliV6xx text-[40rpx] pt-[12rpx] mr-[20rpx]"></text>
                        <view class="flex flex-col">
                            <view class="flex leading-[38rpx] overflow-hidden">
                                <text class="text-[30rpx]">{{ detail.taker_name }}</text>
                                <text class="text-[30rpx] ml-[15rpx]">{{ detail.taker_mobile }}</text>
                            </view>
                            <text class="text-[24rpx] mt-[12rpx] leading-[26rpx]">{{ detail.taker_full_address }}</text>
                        </view>
						
                    </view>
                </view>
				<!-- 自提核销-->
				<template v-if="isShowSelfPickup">
				    <view class="sidebar-margin  mt-[var(--top-m)] card-template" v-if="selfPickupInfo && selfPickupInfo.length">
				        <swiper class="h-[450rpx]" circular indicator-dots="true" v-if="selfPickupInfo.length > 1">
				            <swiper-item v-for="(item,index) in selfPickupInfo" :key="index">
				                <view class="flex flex-col items-center justify-center">
				                    <image :src="item.qrcode" class="w-[300rpx] h-[auto]" mode="widthFix" />
				                </view>
				                <view class="flex items-center justify-center mt-[30rpx]">
				                    <text class="text-[28rpx] font-500">{{ item.code }}</text>
				                    <text class="text-[var(--text-color-light6)] text-[24rpx] ml-[10rpx] border-[2rpx] border-solid border-[#666] bg-[#f7f7f7] px-[12rpx] py-[6rpx] rounded" @click="copy(item.code)">复制</text>
				                </view>
				            </swiper-item>
				        </swiper>
				        <template v-else>
				            <view class="flex flex-col items-center justify-center">
				                <image :src="selfPickupInfo[0].qrcode" class="w-[300rpx] h-[auto]" mode="widthFix" />
				            </view>
				            <view class="flex items-center justify-center mt-[30rpx]">
				                <text class="text-[28rpx] font-500">{{ selfPickupInfo[0].code }}</text>
				                <text class="text-[var(--text-color-light6)] text-[24rpx] ml-[10rpx] border-[2rpx] border-solid border-[#666] bg-[#f7f7f7] px-[12rpx] py-[6rpx] rounded" @click="copy(selfPickupInfo[0].code)">复制</text>
				            </view>
				        </template>
				
				    </view>
				</template>
                <view class="sidebar-margin card-template p-[0] py-[var(--pad-top-m)] overflow-hidden"
                      :class="{'pb-[var(--pad-top-m)]': detail.gift_goods.length <= 0}"
                      :style="detail.delivery_type == 'virtual' ? 'margin-top: -86rpx' : 'margin-top: 20rpx'">
                    <view v-for="(goodsItem, goodsIndex) in detail.goods" :key="goodsIndex"
                          class="px-[var(--pad-sidebar-m)]">
                        <view class="order-goods-item flex justify-between flex-wrap mb-[20rpx]">
                            <view class="w-[150rpx] h-[150rpx] rounded-[var(--goods-rounded-big)] overflow-hidden" @click="goodsEvent(goodsItem.goods_id)">
                                <u--image class="overflow-hidden" radius="var(--goods-rounded-big)" width="150rpx"
                                          height="150rpx" :src="img(goodsItem.goods_image_thumb_small ? goodsItem.goods_image_thumb_small : '')" model="aspectFill">
                                    <template #error>
                                        <image class="w-[150rpx] h-[150rpx] rounded-[var(--goods-rounded-big)] overflow-hidden" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill"/>
                                    </template>
                                </u--image>
                            </view>

                            <view class="ml-[20rpx] flex flex-1 flex-col justify-between">
                                <view>
                                    <view class="text-[28rpx] max-w-[490rpx] truncate leading-[40rpx] text-[#333]">{{ goodsItem.goods_name }}</view>
                                    <view v-if="goodsItem.sku_name">
                                        <view class="text-[22rpx] mt-[14rpx] text-[var(--text-color-light9)] truncate max-w-[490rpx] leading-[28rpx]">{{ goodsItem.sku_name }}</view>
                                    </view>
                                </view>
                                <view v-if="goodsItem.manjian_info && Object.keys(goodsItem.manjian_info).length"
                                      class="flex items-center mt-[10rpx] mb-[auto]"
                                      @click.stop="manjianOpenFn(goodsItem.manjian_info)">
                                    <view class="bg-[var(--primary-color-light)] text-[var(--primary-color)] rounded-[6rpx] text-[20rpx] flex items-center justify-center w-[88rpx] h-[36rpx] mr-[6rpx]">满减送</view>
                                    <text class="text-[22rpx] text-[#999]">{{ goodsItem.manjian_info.manjian_name }}
                                    </text>
                                </view>
                                <view class="flex justify-between items-baseline leading-[28rpx] text-[#333]">
                                    <view class="price-font">
                                        <view class="text-[40rpx] inline-block"
                                              v-if="goodsItem.extend && parseFloat(goodsItem.extend.point) > 0">
                                            <text class="text-[40rpx] font-200">{{ goodsItem.extend.point }}</text>
                                            <text class="text-[32rpx] ml-[4rpx]">积分</text>
                                        </view>
                                        <text class="mx-[4rpx] text-[32rpx]" v-if="parseFloat(goodsItem.price) && goodsItem.extend && parseFloat(goodsItem.extend.point) > 0">+</text>
                                        <template v-if="parseFloat(goodsItem.price) && goodsItem.extend && parseFloat(goodsItem.extend.point) > 0">
                                            <text class="text-[40rpx] font-200">{{ parseFloat(goodsItem.price).toFixed(2) }}</text>
                                            <text class="text-[32rpx] ml-[4rpx]">元</text>
                                        </template>
                                        <template v-if="goodsItem.extend && goodsItem.extend.is_newcomer">
                                            <text class="text-[24rpx]">￥</text>
                                            <text class="text-[40rpx] font-500">{{ parseFloat(goodsItem.price).toFixed(2).split('.')[0] }}</text>
                                            <text class="text-[24rpx] font-500">.{{ parseFloat(goodsItem.price).toFixed(2).split('.')[1] }}</text>
                                        </template>
                                        <template v-if="goodsItem.extend && goodsItem.extend.is_impulse_buy">
                                            <text class="text-[24rpx]">￥</text>
                                            <text class="text-[40rpx] font-500">{{ parseFloat(goodsItem.goods_money).toFixed(2).split('.')[0] }}</text>
                                            <text class="text-[24rpx] font-500">.{{ parseFloat(goodsItem.goods_money).toFixed(2).split('.')[1] }}</text>
                                        </template>
                                        <template v-if="!goodsItem.extend">
                                            <text class="text-[24rpx]">￥</text>
                                            <text class="text-[40rpx] font-500">{{ parseFloat(goodsItem.price).toFixed(2).split('.')[0] }}</text>
                                            <text class="text-[24rpx] font-500">.{{ parseFloat(goodsItem.price).toFixed(2).split('.')[1] }}</text>
                                        </template>
                                    </view>
                                    <text class="text-right text-[26rpx]">x{{ goodsItem.num }}</text>
                                </view>
                            </view>
                        </view>
                        <view class="flex items-center box-border mt-[8rpx]"
                              v-if="goodsItem.extend && goodsItem.extend.is_impulse_buy && goodsItem.num>1">
                            <image class="h-[24rpx] w-[56rpx]" :src="img('addon/shop/impulse_buy.png')" mode="heightFix" />
                            <view class="text-[24rpx] text-[#FFB000] leading-[34rpx] ml-[8rpx]">
                                {{ goodsItem.impulse_buy_tips }}
                            </view>
                        </view>
                        <view class="flex items-center box-border mt-[8rpx]"
                              v-if="goodsItem.extend && goodsItem.extend.is_newcomer && goodsItem.num>1">
                            <image class="h-[24rpx] w-[56rpx]" :src="img('addon/shop/newcomer.png')" mode="heightFix" />
                            <view class="text-[24rpx] text-[#FFB000] leading-[34rpx] ml-[8rpx]">
                                第1{{ goodsItem.unit }}，￥{{ parseFloat(goodsItem.extend.newcomer_price).toFixed(2) }}/{{ goodsItem.unit }}；第{{ goodsItem.num > 2 ? '2~' + goodsItem.num : '2' }}{{ goodsItem.unit }}，￥{{ parseFloat(goodsItem.price).toFixed(2) }}/{{ goodsItem.unit }}
                            </view>
                        </view>
                        <view class="flex justify-end w-[100%] mt-[30rpx] mb-[20rpx]"
                              v-if="(goodsItem.status != '1') || (goodsItem.is_enable_refund == 1)">
                            <view v-if="goodsItem.status != '1'"
                                  class="text-[22rpx] text-[#303133] leading-[50rpx] px-[20rpx] border-[2rpx] border-solid border-[#999] rounded-full"
                                  @click="redirect({ url: '/addon/shop/pages/refund/detail', param: { order_refund_no : goodsItem.order_refund_no } })">查看退款</view>
                            <view v-else-if="goodsItem.is_enable_refund == 1"
                                  class="text-[22rpx] text-[#303133]  leading-[50rpx] px-[20rpx] border-[2rpx] border-solid border-[#999] rounded-full ml-[20rpx]"
                                  @click="applyRefund(goodsItem.order_goods_id)">申请退款</view>
                        </view>

                        <!-- 商品的万能表单信息 -->
                        <view :class="{'diy-form-wrap' : goodsDiyFormData.length }" v-if="goodsItem.form_record_id">
                            <diy-form-detail :record_id="goodsItem.form_record_id" completeLayout="style-2" @callback="getGoodsDiyFormDetailCallback" />
                        </view>
                    </view>
                    <view class="pt-[20rpx] bg-[#f9f9f9] mt-[20rpx] mx-[var(--pad-sidebar-m)] rounded-[var(--rounded-big)]"
                        v-if="detail.gift_goods.length">
                        <view class="order-goods-item flex justify-between flex-wrap px-[var(--pad-sidebar-m)] pb-[20rpx]"
                            v-for="(goodsItem, goodsIndex) in detail.gift_goods" :key="goodsIndex">
                            <view class="w-[120rpx] h-[120rpx] rounded-[var(--goods-rounded-big)] overflow-hidden" @click="goodsEvent(goodsItem.goods_id)">
                                <u--image class="overflow-hidden" radius="var(--goods-rounded-big)" width="120rpx" height="120rpx" :src="img(goodsItem.goods_image_thumb_small ? goodsItem.goods_image_thumb_small : '')" model="aspectFill">
                                    <template #error>
                                        <image class="w-[120rpx] h-[120rpx] rounded-[var(--goods-rounded-big)] overflow-hidden" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill" />
                                    </template>
                                </u--image>
                            </view>
                            <view class="ml-[16rpx] py-[8rpx] flex flex-1 flex-col justify-between">
                                <view class="flex items-center">
                                    <view class="bg-[var(--primary-color-light)] whitespace-nowrap text-[var(--primary-color)] rounded-[6rpx] text-[22rpx] flex items-center justify-center w-[64rpx] h-[34rpx] mr-[6rpx]">
                                        赠品
                                    </view>
                                    <view class="text-[26rpx] max-w-[400rpx] truncate leading-[40rpx] text-[#333]">{{ goodsItem.goods_name }}</view>
                                </view>
                                <view class="flex items-center">
                                    <view v-if="goodsItem.sku_name" class="text-[22rpx] text-[var(--text-color-light9)] truncate max-w-[400rpx] leading-[28rpx]">{{ goodsItem.sku_name }}</view>
                                    <view class="ml-[auto] font-400 text-[26rpx] text-[#303133]">
                                        <text>x</text>
                                        <text>{{ goodsItem.num }}</text>
                                    </view>
                                </view>
                            </view>
                        </view>
                    </view>
                </view>
                <view class="sidebar-margin mt-[var(--top-m)] card-template">
                    <view class="justify-between card-template-item">
                        <view class="text-[28rpx]">{{ t('orderNo') }}</view>
                        <view class="flex items-center text-[28rpx]">
                            <text>{{ detail.order_no }}</text>
                            <text class="w-[2rpx] h-[20rpx] bg-[#999] mx-[10rpx]"></text>
                            <text class="text-[var(--primary-color)]" @click="copy(detail.order_no)">复制</text>
                        </view>
                    </view>
                    <view v-if="detail.out_trade_no" class="justify-between card-template-item">
                        <view class="text-[28rpx]">{{ t('orderTradeNo') }}</view>
                        <view class="text-[28rpx]">{{ detail.out_trade_no }}</view>
                    </view>
                    <view class="justify-between card-template-item">
                        <view class="text-[28rpx]">{{ t('createTime') }}</view>
                        <view class="text-[28rpx]">{{ detail.create_time }}</view>
                    </view>
					<view class="justify-between card-template-item" v-if="detail.member_remark">
					    <view class="text-[28rpx] w-[180rpx]">{{ t('memberRemark') }}</view>
					    <view class="text-[28rpx] flex-1 text-right">{{ detail.member_remark }}</view>
					</view>
                    <view class=" card-template-item justify-between">
                        <view class="text-[28rpx]">{{ t('deliveryType') }}</view>
                        <view class="text-[28rpx]">{{ detail.delivery_type_name }}<text v-if="detail.order_delivery && detail.order_delivery[0]?.third_delivery_name && detail.delivery_type == 'local_delivery'&& detail.status_name.status!=2 && detail.status_name.status!=1 ">-{{detail.order_delivery[0].third_delivery_name}}</text></view>
                    </view>
					<view class=" card-template-item justify-between" v-if="detail.delivery_type == 'local_delivery' &&  detail.buyer_ask_delivery_time">
					    <view class="text-[28rpx]">预约配送时间</view>
					    <view class="text-[28rpx]">{{ detail.buyer_ask_delivery_time }}</view>
					</view>
                    <view v-if="detail.pay" class="card-template-item justify-between" :class="{ '!mb-[18rpx]' : detail.member_id !== detail.pay.main_id && detail.pay.status == 2 }">
                        <view class="text-[28rpx]">{{ t('payTypeName') }}</view>
                        <view class="text-[28rpx]">{{ detail.pay.type_name }}</view>
                    </view>
                    <view v-if="detail.pay && detail.member_id !== detail.pay.main_id && detail.pay.status == 2" class="card-template-item justify-end">
                        <view class="friend-pay relative px-[20rpx] py-[12rpx] bg-[#F2F2F2] rounded-[10rpx] flex items-center">
                            <u-avatar :src="img(detail.pay.pay_member_headimg)" size="20" leftIcon="none" :default-url="img('static/resource/images/default_headimg.png')" />
                            <text class="ml-[14rpx] text-[24rpx] using-hidden">{{ detail.pay.pay_member }}{{ t('helpPay') }}</text>
                        </view>
                    </view>
                    <view v-if="detail.pay" class=" card-template-item justify-between">
                        <view class="text-[28rpx]">{{ t('payTime') }}</view>
                        <view class="text-[28rpx]">{{ detail.pay.pay_time }}</view>
                    </view>

                </view>
				<view class="sidebar-margin mt-[var(--top-m)] card-template" v-if="detail.order_delivery && detail.order_delivery[0] && detail.order_delivery[0].third_delivery_info && detail.delivery_type == 'local_delivery' && detail.status_name.status!=2 && detail.status_name.status!=1 ">
				    <view v-if="detail.order_delivery[0].third_delivery_info?.transporter_name" class="justify-between card-template-item">
				        <view class="text-[28rpx]">配送员</view>
				        <view class="text-[28rpx]">{{ detail.order_delivery[0].third_delivery_info?.transporter_name }}</view>
				    </view>
				    <view v-if="detail.order_delivery[0].third_delivery_info?.transporter_phone" class="justify-between card-template-item">
				        <view class="text-[28rpx]">配送员手机号</view>
				        <view class="text-[28rpx]">{{ detail.order_delivery[0].third_delivery_info?.transporter_phone }}</view>
				    </view>
					<view v-if="detail.order_delivery[0].third_delivery_info?.status_name" class="justify-between card-template-item">
					    <view class="text-[28rpx]">配送状态</view>
					    <view class="text-[28rpx]">{{ detail.order_delivery[0].third_delivery_info?.status_name }}</view>
					</view>
				    <view v-if="detail.order_delivery[0].third_delivery_name" class=" card-template-item justify-between">
				        <view class="text-[28rpx]">{{ t('deliveryType') }}</view>
				        <view class="text-[28rpx]"><text v-if="detail.delivery_type == 'local_delivery'&& detail.status_name.status!=2 && detail.status_name.status!=1 ">{{detail.order_delivery[0].third_delivery_name}}</text></view>
				    </view>
				</view>
                <!-- 核销码 -->
                <template v-if="isShowVerify">
                    <view class="sidebar-margin  mt-[var(--top-m)] card-template" v-if="verifyInfo && verifyInfo.length">
                        <swiper class="h-[450rpx]" circular indicator-dots="true" v-if="verifyInfo.length > 1">
                            <swiper-item v-for="(item,index) in verifyInfo" :key="index">
                                <view class="flex flex-col items-center justify-center">
                                    <image :src="item.qrcode" class="w-[300rpx] h-[auto]" mode="widthFix" />
                                </view>
                                <view class="flex items-center justify-center mt-[30rpx]">
                                    <text class="text-[28rpx] font-500">{{ item.code }}</text>
                                    <text class="text-[var(--text-color-light6)] text-[24rpx] ml-[10rpx] border-[2rpx] border-solid border-[#666] bg-[#f7f7f7] px-[12rpx] py-[6rpx] rounded" @click="copy(item.code)">复制</text>
                                </view>
                            </swiper-item>
                        </swiper>
                        <template v-else>
                            <view class="flex flex-col items-center justify-center">
                                <image :src="verifyInfo[0].qrcode" class="w-[300rpx] h-[auto]" mode="widthFix" />
                            </view>
                            <view class="flex items-center justify-center mt-[30rpx]">
                                <text class="text-[28rpx] font-500">{{ verifyInfo[0].code }}</text>
                                <text class="text-[var(--text-color-light6)] text-[24rpx] ml-[10rpx] border-[2rpx] border-solid border-[#666] bg-[#f7f7f7] px-[12rpx] py-[6rpx] rounded" @click="copy(verifyInfo[0].code)">复制</text>
                            </view>
                        </template>

                    </view>
                    <view class="sidebar-margin mt-[var(--top-m)] card-template">
                        <view class="title">核销信息</view>
                        <view class="card-template-item justify-between">
                            <view class="text-[28rpx]">核销次数</view>
                            <view class="price-font font-500 text-[28rpx]">{{ '剩余' + (verifyGoodsData.num - verifyGoodsData.verify_count) + '次' }}/{{ '共' + verifyGoodsData.num + '次' }}</view>
                        </view>
                        <view class="card-template-item justify-between">
                            <view class="text-[28rpx]">有效期</view>
                            <view class="price-font font-500 text-[28rpx]">{{ verifyGoodsData.verify_expire_time ? verifyGoodsData.verify_expire_time : '永久' }}</view>
                        </view>
                    </view>
                </template>

                <!-- 待付款订单的万能表单信息 -->
                <view :class="{'sidebar-margin mt-[var(--top-m)] card-template' : orderDiyFormData.length }"
                      v-if="detail.form_record_id">
                    <diy-form-detail :record_id="detail.form_record_id" completeLayout="style-2" @callback="getOrderDiyFormDetailCallback" />
                </view>
                <view class="sidebar-margin mt-[var(--top-m)] card-template">
                    <view class="card-template-item justify-between">
                        <view class="text-[28rpx]">{{ t('goodsMoney') }}</view>
                        <view class="price-font font-500">
                            <text v-if="parseFloat(detail.point) > 0" class="text-[28rpx]">{{ detail.point }}积分</text>
                            <text v-if="parseFloat(detail.point) > 0 && parseFloat(detail.goods_money)" class="mx-[4rpx] text-[28rpx]">+</text>
                            <template v-if="parseFloat(detail.goods_money) || !parseFloat(detail.point)">
                                <text class="text-[28rpx]">￥</text>
                                <text class="text-[28rpx]">{{ parseFloat(detail.goods_money).toFixed(2).split('.')[0] }}</text>
                                <text class="text-[28rpx]">.{{ parseFloat(detail.goods_money).toFixed(2).split('.')[1] }}</text>
                            </template>
                        </view>
                    </view>
                    <view class=" card-template-item justify-between" v-if="parseFloat(detail.delivery_money)">
                        <view class="text-[28rpx]">{{ t('deliveryMoney') }}</view>
                        <view class="price-font font-500 text-[28rpx]">￥{{ parseFloat(detail.delivery_money).toFixed(2) }}</view>
                    </view>
                    <!-- <view class=" card-template-item justify-between">
                      <view class="text-[28rpx]">{{ t('discountMoney') }}</view>
                      <view class="price-font font-500 text-[28rpx]">
                        -￥{{ parseFloat(detail.discount_money).toFixed(2) }}
                      </view>
                    </view> -->
                    <view class=" card-template-item justify-between" v-if="parseFloat(detail.coupon_money)">
                        <view class="text-[28rpx]">优惠券优惠</view>
                        <view class="price-font font-500 text-[28rpx]">-￥{{ parseFloat(detail.coupon_money).toFixed(2) }}</view>
                    </view>
                    <view class=" card-template-item justify-between" v-if="parseFloat(detail.manjian_discount_money)">
                        <view class="text-[28rpx]">满减优惠</view>
                        <view class="price-font font-500 text-[28rpx]">-￥{{ parseFloat(detail.manjian_discount_money).toFixed(2) }}</view>
                    </view>
                    <view class=" card-template-item justify-between items-baseline">
                        <view class="text-[28rpx]">{{ t('orderMoney') }}</view>
                        <view class="text-[var(--price-text-color)] price-font">
                            <text v-if="parseFloat(detail.point) > 0" class="text-[28rpx]">{{ detail.point }}积分</text>
                            <text v-if="parseFloat(detail.point) > 0 && parseFloat(detail.order_money)" class="mx-[4rpx] text-[28rpx]">+</text>
                            <text v-if="parseFloat(detail.order_money) || !parseFloat(detail.point)" class="text-[28rpx]">￥{{ parseFloat(detail.order_money).toFixed(2) }}</text>
                        </view>
                    </view>
                </view>

                <view class="flex z-2 justify-between items-center bg-[#fff] fixed left-0 right-0 bottom-0 min-h-[100rpx] pl-[30rpx] pr-[20rpx] flex-wrap  pb-ios">
                    <view class="flex">
                        <view class="flex  mr-[34rpx] flex-col justify-center items-center"
                              @click="orderBtnFn('index')">
                            <view class="nc-iconfont nc-icon-shouyeV6xx11 text-[36rpx]"></view>
                            <text class="text-[20rpx] mt-[10rpx]">{{ t('index') }}</text>
                        </view>
                        <!-- #ifdef MP-WEIXIN -->
                        <view>
                            <nc-contact :send-message-title="sendMessageTitle" :send-message-path="sendMessagePath" :send-message-img="sendMessageImg">
                                <view class="flex flex-col justify-center items-center">
                                    <view class="w-[36rpx] h-[36rpx] flex-center">
                                        <text class="nc-iconfont nc-icon-kefuV6xx-1 text-[34rpx]"></text>
                                    </view>
                                    <text class="text-[20rpx] mt-[10rpx]">客服</text>
                                </view>
                            </nc-contact>
                        </view>
                        <!-- #endif -->
                    </view>
                    <view class="flex justify-end">
                        <view class="min-w-[180rpx]  box-border text-[26rpx] h-[70rpx] flex-center border-[2rpx] border-solid border-[#999] rounded-full ml-[20rpx] text-[var(--text-color-light6)]"
                            @click="orderBtnFn('logistics')" v-if="showLogistics(detail)">{{ t('logisticsTracking') }}</view>
                        <view class="min-w-[180rpx] box-border text-[26rpx]  h-[70rpx] flex-center text-center border-[2rpx] border-solid border-[#999] rounded-full ml-[20rpx] text-[var(--text-color-light6)]" v-if="detail.status == 1" @click="orderBtnFn('close')">{{ t('orderClose') }}</view>
                        <view class="min-w-[180rpx] box-border  text-[26rpx] h-[70rpx] flex-center text-center text-[#fff] primary-btn-bg rounded-full ml-[20rpx]" v-if="detail.status == 1" @click="orderBtnFn('pay')">{{ t('topay') }}</view>
                        <view v-if="detail.status == 3" class="min-w-[180rpx] box-border  text-[26rpx] h-[70rpx] flex-center text-center  text-[#fff]  primary-btn-bg rounded-full ml-[20rpx]" @click="orderBtnFn('finish')">{{ t('orderFinish') }}</view>
                        <template v-if="detail.status == 5 && isShowEvaluate">
                            <view v-if="detail.is_evaluate == 1 || (detail.is_evaluate != 1 && evaluateConfig.is_evaluate == 1)"
                                class="min-w-[180rpx] box-border text-[26rpx]  h-[70rpx] flex-center border-[2rpx] border-solid border-[#999] rounded-full ml-[20rpx] !text-[var(--text-color-light6)]"
                                @click="orderBtnFn('evaluate')">{{ detail.is_evaluate == 1 ? t('selectedEvaluate') : t('evaluate') }}</view>
                        </template>
                    </view>
                </view>
            </view>
            <view class="tab-bar-placeholder"></view>
            <pay ref="payRef" @close="payClose"></pay>
            <logistics-tracking ref="materialRef"></logistics-tracking>
            <!-- 满减 -->
            <ns-goods-manjian ref="manjianShowRef"></ns-goods-manjian>
        </view>

        <loading-page :loading="loading"></loading-page>

        <!-- #ifdef MP-WEIXIN -->
        <!-- 小程序隐私协议 -->
        <wx-privacy-popup ref="wxPrivacyPopupRef"></wx-privacy-popup>
        <!-- #endif -->
    </view>
</template>

<script setup lang="ts">
import { ref, reactive, computed, nextTick } from 'vue';
import { onLoad } from '@dcloudio/uni-app'
import { t } from '@/locale'
import { img, redirect, copy, goback } from '@/utils/common';
import { getShopOrderDetail, orderClose, orderFinish } from '@/addon/shop/api/order';
import { getEvaluateConfig } from '@/addon/shop/api/shop';
import { getVerifyCode } from '@/app/api/verify';
import logisticsTracking from '@/addon/shop/pages/order/components/logistics-tracking/logistics-tracking.vue'
import useConfigStore from "@/stores/config";
import nsGoodsManjian from '@/addon/shop/components/ns-goods-manjian/ns-goods-manjian.vue';
import { topTabar } from '@/utils/topTabbar';
import { cloneDeep } from 'lodash-es';
import diyFormDetail from '@/addon/components/diy-form-detail/index.vue'

/********* 自定义头部 - start ***********/
const topTabarObj = topTabar()
let topTabbarData = topTabarObj.setTopTabbarParam({ title: '订单详情' })
/********* 自定义头部 - end ***********/

const detail: any = ref<Object>({});
const loading = ref<boolean>(true);
const type = ref('')
const orderId = ref('')
const evaluateConfig = ref<Object>({});
const isShowEvaluate = ref(true)
const manjianShowRef: any = ref(null); //满减送

const sendMessageTitle = ref('')
const sendMessagePath = ref('')
const sendMessageImg = ref('')

const wxPrivacyPopupRef: any = ref(null)

onLoad((option: any) => {
    if (option.order_id) {
        orderId.value = option.order_id;
        orderDetailFn(orderId.value);
        // #ifdef MP
        nextTick(() => {
            if (wxPrivacyPopupRef.value) wxPrivacyPopupRef.value.proactive();
        })
        // #endif
    } else {
        let parameter = {
            url: '/addon/shop/pages/order/list',
            title: '缺少订单id'
        };
        goback(parameter)
    }
});

getEvaluateConfig().then(({ data }) => {
    evaluateConfig.value = data
})

const orderDetailFn = (id: any) => {
    loading.value = true;
    getShopOrderDetail(id).then((res: any) => {
        detail.value = res.data;
        if (res.data.order_goods && res.data.order_goods.length && isShowVerify.value) {
            let obj: any = {};
            obj.order_goods_id = res.data.order_goods[0].order_goods_id
            getVerifyCodeFn(obj);
        }
		if (res.data.order_goods && res.data.order_goods.length && isShowSelfPickup.value) {
		    let obj: any = {};
		    obj.order_id = res.data.order_id
		    getSelfPickupCodeFn(obj);
		}

        detail.value.goods = []; //购买商品
        detail.value.gift_goods = []; //赠品
        detail.value.order_goods.forEach((item, index) => {
            if (item.is_gift) {
                detail.value.gift_goods.push(item);
            } else {
                detail.value.goods.push(item);
            }
        })

        let evaluateCount = 0;
        for (let i = 0; i < detail.value.order_goods.length; i++) {
            if (detail.value.order_goods[i].status != 1) {
                evaluateCount++;
            }
        }

        if (evaluateCount == detail.value.order_goods.length) {
            isShowEvaluate.value = false;
        }
        sendMessageTitle.value = detail.value.order_goods[0].goods_name
        sendMessageImg.value = img(detail.value.order_goods[0].goods_image_thumb_small || '')
        loading.value = false;
    }).catch(() => {
        loading.value = false;
    })
}

// 满减
const manjianOpenFn = (data: any) => {
    let obj = {};
    obj.condition_type = cloneDeep(data).condition_type;
    obj.rule_json = [cloneDeep(data).rule];
    obj.name = cloneDeep(data).manjian_name;
    manjianShowRef.value.open(obj);
}

//关闭订单
const close = (item: any) => {
    uni.showModal({
        title: '提示',
        content: '您确定要关闭该订单吗？',
        confirmColor: useConfigStore().themeColor['--primary-color'],
        success: res => {
            if (res.confirm) {
                orderClose(item.order_id).then((data) => {
                    orderDetailFn(item.order_id);
                })
            }
        }
    })
}

//订单完成
const finish = (item: any) => {
    // 如果不在微信小程序中
    // #ifndef MP-WEIXIN
    uni.showModal({
        title: '提示',
        content: '您确定物品已收到吗？',
        confirmColor: useConfigStore().themeColor['--primary-color'],
        success: res => {
            if (res.confirm) {
                orderFinish(item.order_id).then((data) => {
                    orderDetailFn(item.order_id);
                })
            }
        }
    })
    // #endif

    // #ifdef MP-WEIXIN
    // 检测微信小程序是否已开通发货信息管理服务
    if (item.pay && item.pay.type == 'wechatpay' && item.is_trade_managed && wx.openBusinessView) {
        wx.openBusinessView({
            businessType: 'weappOrderConfirm',
            extraData: {
                merchant_id: item.mch_id,
                merchant_trade_no: item.out_trade_no
            },
            success: (res: any) => {
                orderFinish(item.order_id).then((data) => {
                    orderDetailFn(item.order_id);
                })
            },
            fail: (res: any) => {
                console.log('小程序确认收货组件打开失败 fail', res);
            }
        })
    } else {
        uni.showModal({
            title: '提示',
            content: '您确定物品已收到吗？',
            confirmColor: useConfigStore().themeColor['--primary-color'],
            success: res => {
                if (res.confirm) {
                    orderFinish(item.order_id).then((data) => {
                        orderDetailFn(item.order_id);
                    })
                }
            }
        })
    }
    // #endif
}

const goodsEvent = (id: number) => {
    if (detail.value.activity_type == 'exchange') {
        redirect({
            url: '/addon/shop/pages/point/detail',
            param: {
                id: detail.value.relate_id
            }
        })
    } else {
        redirect({
            url: '/addon/shop/pages/goods/detail',
            param: {
                goods_id: id
            }
        })
    }
}

const payRef = ref(null)
const materialRef: any = ref(null)
const orderBtnFn = (type = '') => {
    if (type == 'pay')
        payRef.value?.open(detail.value.order_type, detail.value.order_id, `/addon/shop/pages/order/detail?order_id=${ detail.value.order_id }`);
    else if (type == 'close') {
        close(detail.value);
    } else if (type == 'finish') {
        finish(detail.value);
    } else if (type == 'index') {
        redirect({
            url: '/addon/shop/pages/index',
            mode: 'reLaunch'
        });
    } else if (type == 'logistics') {
        if (detail.value.order_delivery.length > 0) {
            let list: any = []
            detail.value.order_delivery.forEach((item: any, index: number) => {
                if(item.delivery_type == 'express' && (item.sub_delivery_type == 'express' || item.sub_delivery_type == 'none_express')) {
                    item.name = `包裹${ index + 1 }`
                    list.push(item)
                }
            })

            let params = {
                id: list[0].id,
                mobile: detail.value.taker_mobile
            }
            materialRef.value.open(params);
            materialRef.value.packageList = list
        }
    } else if (type == 'evaluate') {
        if (!detail.value.is_evaluate) {
            redirect({ url: '/addon/shop/pages/evaluate/order_evaluate', param: { order_id: detail.value.order_id } })
        } else {
            redirect({
                url: '/addon/shop/pages/evaluate/order_evaluate_view',
                param: { order_id: detail.value.order_id }
            })
        }
    }
}

const dateFormat = (res: any, type: any) => {
    let data;
    if (res.indexOf('/') != -1) {
        data = res.split("/");
    } else if (res.indexOf('-') != -1) {
        data = res.split("-");
    }

    let time;
    const index = new Date(res).getDay();
    const week = ['周日', '周一', '周二', '周三', '周四', '周五', '周六']
    if (type == "yearMonthDay") {
        time = data[0] + '年' + data[1] + '月' + data[2] + '日';
    } else if (type == "yearMonthDayWeek") {
        time = data[0] + '年' + data[1] + '月' + data[2] + '日 ' + week[index];
    } else if (type == "monthDayWeek") {
        time = data[1] + '月' + data[2] + '日 ' + week[index];
    } else {
        time = data[1] + '月' + data[2] + '日';
    }

    return time;
}

const applyRefund = (orderGoodsId: number) => {
    redirect({
        url: '/addon/shop/pages/refund/apply',
        param: {
            order_id: detail.value.order_id,
            order_goods_id: orderGoodsId
        }
    })
}

const getAddress = () => {
    uni.openLocation({
        latitude: Number(detail.value.taker_latitude),
        longitude: Number(detail.value.taker_longitude),
        success: function () {
        }
    });
}

const showLogistics = (data: any) => {
    let status = false
    if (data.delivery_type != 'express') return false;
    for (let i = 0; i < data.order_delivery.length; i++) {
        let item = data.order_delivery[i];
        if (item.sub_delivery_type === 'express') {
            status = true;
            break;
        } else {
            status = false
        }
    }
    return status
}

/************ 虚拟商品核销-start ***************/
const verifyGoodsData = ref({}) //虚拟商品
const isShowVerify = computed(() => {
    let bool = false;
    if (detail.value.order_goods.length == 1) {
        verifyGoodsData.value = detail.value.order_goods[0]

        let data = detail.value.order_goods[0];
        bool = data.is_verify == 1 && data.goods_type == 'virtual' && data.delivery_status == 'delivery_finish' && detail.value.status == 3 ? true : false;
    }
    return bool
})
const verifyInfo = ref([])
const getVerifyCodeFn = (data: any) => {
    verifyInfo.value = [];

    getVerifyCode('shopVirtualGoods', data).then((res: any) => {
        verifyInfo.value = res.data;
    })
}
/************ 虚拟商品核销-end ***************/

/************ 自提核销-start ***************/
// const verifyGoodsData = ref({}) //虚拟商品
const isShowSelfPickup = computed(() => {
    let bool = false;
    // if (detail.value.order_goods.length == 1) {
        // verifyGoodsData.value = detail.value.order_goods[0]
        bool = detail.value.delivery_type == 'store' && detail.value.status == 2 ? true : false;
    // }
    return bool
})
const selfPickupInfo = ref([])
const getSelfPickupCodeFn = (data: any) => {
    selfPickupInfo.value = [];

    getVerifyCode('shopPickUpOrder', data).then((res: any) => {
        selfPickupInfo.value = res.data;
    })
}
/************ 自提核销-end ***************/

// 商品表单信息
const goodsDiyFormData = ref([]);
const getGoodsDiyFormDetailCallback = (data: any) => {
    goodsDiyFormData.value = data;
}

// 订单表单信息
const orderDiyFormData = ref([]);
const getOrderDiyFormDetailCallback = (data: any) => {
    orderDiyFormData.value = data;
}
</script>
<style lang="scss" scoped>
.text-item {
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.order-goods-item:nth-child(1) {
    margin-top: 0rpx;
}

.text-color {
    color: $u-primary;
}

.bg-color {
    background-color: $u-primary;
}

.bg-linear {
    background: linear-gradient(90deg, #FF0000 0%, #FF7800 100%);
}

.triangle {
    @apply relative;

    &::before {
        content: "";
        @apply absolute;
        width: 0;
        height: 0;
        position: absolute;
        bottom: -40rpx;
        border: 20rpx solid #EEF3FF;
        border-left-color: transparent;
        border-right-color: transparent;
        border-bottom-color: transparent;
    }
}

.tab-bar-placeholder {
    padding-bottom: calc(constant(safe-area-inset-bottom) + 100rpx);
    padding-bottom: calc(env(safe-area-inset-bottom) + 100rpx);
}

:deep(.u-image__error) {
    background-color: transparent !important;
}

.friend-pay {
    &::after {
        content: '';
        display: block;
        width: 20rpx;
        height: 20rpx;
        background-color: #f2f2f2;
        position: absolute;
        right: 30rpx;
        top: 0;
        transform: translateY(-50%) rotate(45deg);
        border-radius: 4rpx;
    }
}
</style>

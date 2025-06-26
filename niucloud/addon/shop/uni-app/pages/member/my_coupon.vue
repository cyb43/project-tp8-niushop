<template>
    <view class="bg-[#f8f8f8] min-h-screen overflow-hidden" :style="themeColor()" v-if="memberStore.info">
        <view class="fixed left-0 top-0 right-0 z-10">
            <scroll-view :scroll-x="true" class="scroll-Y box-border px-[var(--sidebar-m)] bg-white">
                <view class="flex whitespace-nowrap justify-around items-center h-[88rpx]">
                    <view :class="['text-[28rpx] text-[#333] h-[88rpx] leading-[88rpx] font-400', { 'class-select !text-primary': couponStatus === item.status }]"
                        @click="statusClickFn(item.status)" v-for="(item, index) in statusList">{{ item.status_name }}({{ item.count }})</view>
                </view>
            </scroll-view>
            <scroll-view :scroll-x="true" scroll-with-animation :scroll-into-view="'id' + (subActive ? subActive - 1 : 0)" class="px-[var(--sidebar-m)]  box-border bg-white">
                <view class="items-center flex h-[88rpx]">
                    <text class="flex-shrink-0 w-[126rpx] h-[54rpx] text-[24rpx] flex-center text-center text-[#333] bg-[var(--temp-bg)] rounded-[30rpx] box-border mr-[20rpx] border-[2rpx] border-solid border-[#F8F9FD]"
                        :class="{'!text-primary !border-primary font-500 !bg-[var(--primary-color-light)]':item.value == curType}"
                        v-for="(item,index) in typeList" :key="index" :id="'id' + index"
                        @click="typeClick(index,item.value)">{{ item.label }}
                    </text>
                </view>
            </scroll-view>
        </view>

        <mescroll-body ref="mescrollRef" top="176rpx" @init="mescrollInit" :down="{ use: false }" @up="getMyCouponListFn">
            <view class="py-[var(--top-m)] px-[var(--sidebar-m)]" v-if="list.length">
                <template v-for="(item, index) in list">

                    <view v-if="couponStatus != 1"
                          class="flex items-center relative w-[100%] rounded-[var(--rounded-small)] overflow-hidden bg-[#fff]"
                          :class="{'mt-[var(--top-m)]':index}">
                        <view
                            class=" w-[186rpx] h-[160rpx] flex  flex-col items-center justify-center rounded-[var(--rounded-small)] relative coupon-item"
                            :class="{'bg-[var(--primary-help-color4)]':couponStatus == 2 , 'bg-[var(--primary-color-light)]' :couponStatus == 3 || couponStatus == 4}">
                            <view class="price-font flex items-baseline" :class="{'text-[#fff]' : couponStatus == 2 , 'text-[#FFB4B1]' : couponStatus == 3 || couponStatus == 4}">
                                <text class="text-[30rpx] leading-[34rpx] mr-[2rpx] text-center price-font font-500">￥</text>
                                <text class="text-[54rpx] font-500 leading-[58rpx] price-font truncate">{{ item.coupon_price }}</text>
                            </view>
                            <text class="truncate max-w-[176rpx] mt-[6rpx] text-[24rpx] h-[32rpx] leading-[32rpx]" :class="{'text-[#fff]': couponStatus == 2 , 'text-[var(--primary-help-color4)]': couponStatus == 3 ||couponStatus == 4}">{{ item.title }}</text>
                        </view>
                        <view class="ml-[30rpx] flex-1 h-[100%] box-border py-[20rpx]">
                            <view class="text-[26rpx] leading-[40rpx] text-left font-500">
                                <text v-if="item.min_condition_money === '0.00'">无门槛</text>
                                <text v-else>满{{ item.coupon_min_price }}元可用</text>
                            </view>
                            <view class="mt-[10rpx] flex items-center">
                                <text class="w-[80rpx] text-center bg-[var(--primary-color-light)] whitespace-nowrap text-[var(--primary-color)] text-[18rpx] h-[30rpx] leading-[30rpx] rounded-[15rpx] mr-[10rpx] flex-shrink-0">{{ item.type_name }}</text>
                                <text class="truncate max-w-[226rpx] text-[24rpx] text-[var(--text-color-light6)] leading-[34rpx]">{{ item.title }}</text>
                            </view>
                            <view class="w-[100%] mt-[6rpx] text-[20rpx] leading-[34rpx] text-[var(--text-color-light6)]">
                                <text>有效期至
                                    <text>{{ item.expire_time ? item.expire_time.slice(0, 10) : '' }}</text>
                                </text>
                            </view>
                        </view>
                        <view class="px-[20rpx]">
                            <button class="flex-center rounded-full remove-border"
                                    :style="{width:'150rpx',height:'60rpx',color:'#fff', fontSize:'24rpx', padding:'0',border:'none',backgroundColor:'var(--primary-help-color4)'}"
                                    v-if="couponStatus == 2">已使用</button>
                            <button class="flex-center rounded-full remove-border"
                                    :style="{width:'150rpx',height:'60rpx',color:'var(--primary-help-color4)', fontSize:'24rpx', padding:'0',border:'none',backgroundColor:'var(--primary-color-light)'}"
                                    v-if="couponStatus == 3">已过期</button>
							<button class="flex-center rounded-full remove-border"
							        :style="{width:'150rpx',height:'60rpx',color:'var(--primary-help-color4)', fontSize:'24rpx', padding:'0',border:'none',backgroundColor:'var(--primary-color-light)'}"
							        v-if="couponStatus == 4">已失效</button>
                        </view>
                    </view>
                    <view v-else class="flex items-center relative w-[100%] rounded-[var(--rounded-small)] overflow-hidden bg-[#fff]"
                          :class="{'mt-[var(--top-m)]' : index}">
                        <view class="coupon-bg w-[186rpx] h-[160rpx] flex  flex-col items-center justify-center rounded-[var(--rounded-small)] relative coupon-item">
                            <view class="price-font flex items-baseline text-[#fff]">
                                <text class="text-[30rpx] leading-[34rpx] mr-[2rpx] text-center price-font font-500">￥</text>
                                <text class="text-[54rpx] font-500 leading-[58rpx] price-font truncate">{{ item.coupon_price }}</text>
                            </view>
                            <text class="truncate max-w-[176rpx] mt-[6rpx] text-[22rpx] text-[#fff] h-[32rpx] leading-[32rpx]">{{ item.title }}</text>
                        </view>
                        <view class="ml-[30rpx] flex-1 h-[100%] box-border py-[20rpx]">
                            <view class="text-[26rpx] leading-[40rpx] text-left font-500">
                                <text v-if="item.min_condition_money === '0.00'">无门槛</text>
                                <text v-else>满{{ item.coupon_min_price }}元可用</text>
                            </view>
                            <view class="text-[20rpx]  mt-[10rpx] flex items-center">
                                <text class="w-[80rpx] text-center bg-[var(--primary-color-light)] whitespace-nowrap text-[var(--primary-color)] text-[18rpx] h-[30rpx] leading-[30rpx] rounded-[15rpx] mr-[10rpx] flex-shrink-0">{{ item.type_name }}</text>
                                <text class="truncate max-w-[226rpx] text-[24rpx] text-[var(--text-color-light9)] leading-[34rpx]">{{ item.title }}</text>
                            </view>
                            <view class="w-[100%] mt-[6rpx] text-[20rpx] leading-[34rpx] text-[var(--text-color-light9)]">
                                <text>有效期至
                                    <text>{{ item.expire_time ? item.expire_time.slice(0, 10) : '' }}</text>
                                </text>
                            </view>
                        </view>
                        <view class="px-[20rpx]" v-if="couponStatus === 1">
                            <button hover-class="none" class="flex-center rounded-full remove-border primary-btn-bg"
                                    :style="{width:'150rpx',height:'60rpx',color:'#fff', fontSize:'24rpx', padding:'0',border:'none'}"
                                    @click="toLink(item.coupon_id)">去使用</button>
                        </view>
                    </view>
                </template>
            </view>
            <mescroll-empty v-if="!list.length && loading" :option="{'tip' : '暂无优惠券'}"></mescroll-empty>
        </mescroll-body>
        <pay ref="payRef"></pay>
    </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { img, redirect } from '@/utils/common'
import { getMyCouponList, getMyCouponType, getMyCouponStatusCount } from '@/addon/shop/api/coupon'
import useMemberStore from '@/stores/member'
import MescrollBody from '@/components/mescroll/mescroll-body/mescroll-body.vue'
import MescrollEmpty from '@/components/mescroll/mescroll-empty/mescroll-empty.vue'
import useMescroll from '@/components/mescroll/hooks/useMescroll.js'
import { onLoad, onPageScroll, onReachBottom } from '@dcloudio/uni-app'
import { t } from '@/locale'

const memberStore = useMemberStore()
const { mescrollInit, downCallback, getMescroll } = useMescroll(onPageScroll, onReachBottom)
const list = ref<Array<Object>>([]);
const loading = ref<boolean>(false);
const statusList = ref<Array<Object>>([]);
const couponStatus = ref(1);

const getMyCouponStatusCountFn = () => {
    getMyCouponStatusCount().then((res: any) => {
        // statusList.value = res.data.filter((item: any) => item.status != 4)
		statusList.value = res.data
    })
}
const statusClickFn = (status: any) => {
    couponStatus.value = status;
    list.value = []; //如果是第一页需手动制空列表
    getMescroll().resetUpScroll();
};

const getMyCouponListFn = (mescroll: any) => {
    loading.value = false;
    let data: object = {
        page: mescroll.num,
        limit: mescroll.size,
        status: couponStatus.value,
        type: curType.value
    };

    getMyCouponList(data).then((res: any) => {
        let newArr = (res.data.data as Array<Object>);
        //设置列表数据
        if (mescroll.num == 1) {
            list.value = []; //如果是第一页需手动制空列表
        }
        list.value = list.value.concat(newArr);
        mescroll.endSuccess(newArr.length);
        loading.value = true;
    }).catch(() => {
        loading.value = true;
        mescroll.endErr(); // 请求失败, 结束加载
    })
}
const toLink = (coupon_id: any) => {
    redirect({ url: '/addon/shop/pages/goods/list', param: { coupon_id } })
}

// 类型
const subActive = ref<number>(0)
const curType = ref('all')
const typeList = ref<Array<Object>>([])
const getMyCouponTypeFn = () => {
    getMyCouponType().then((res: any) => {
        const obj = { label: '全部', value: 'all' };
        typeList.value.push(obj)
        typeList.value = typeList.value.concat(res.data)
    })
}
onLoad(() => {
    getMyCouponTypeFn()
    getMyCouponStatusCountFn()
})

const typeClick = (index: number, data: any) => {
    subActive.value = index
    curType.value = data
    list.value = []
    getMescroll().resetUpScroll()
}
</script>

<style lang="scss" scoped>
.remove-border {
    &::after {
        border: none;
    }
}

.class-select {
    position: relative;
    font-weight: 500 !important;

    &::before {
        content: "";
        position: absolute;
        bottom: 10rpx;
        height: 6rpx;
        background-color: $u-primary;
        width: 40rpx;
        left: 50%;
        border-radius: 100rpx;
        transform: translateX(-50%);
    }
}

.coupon-bg {
    background: linear-gradient(169deg, var(--primary-color) 0%, var(--primary-color) 86%);
}

.coupon-item {
    :before {
        content: '';
        display: block;
        width: 28rpx;
        height: 28rpx;
        background-color: #f8f8f8;
        position: absolute;
        top: 50%;
        left: -14rpx;
        border-radius: 14rpx;
        transform: translateY(-50%);
    }

    :after {
        content: '';
        display: block;
        width: 28rpx;
        height: 28rpx;
        background-color: #fff;
        position: absolute;
        top: 50%;
        right: -14rpx;
        border-radius: 14rpx;
        transform: translateY(-50%);
    }
}

:deep(.mescroll-empty) {
    border-radius: var(--rounded-small) !important;
}
</style>

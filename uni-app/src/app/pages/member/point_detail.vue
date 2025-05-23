<template>
    <view class="bg-[var(--page-bg-color)] min-h-[100vh]" :style="themeColor()">
        <view class="fixed left-0 right-0 top-0 z-10085">
            <view class="bg-[#fff] px-[30rpx] h-[88rpx] flex-center relative z-10084">
                <view class="search-input">
                    <view class="flex-1 text-[24rpx] leading-[60rpx] text-[var(--text-color-light9)]" :class="{'!text-[#333]':from_type}" @click="typePopup = true">{{ from_type_name || '请选择来源用途' }}</view>
                    <text class="nc-iconfont nc-icon-shangV6xx-1 !text-[26rpx] ml-[18rpx] !text-[var(--text-color-light6)]" v-if="typePopup" @click="typePopup = false"></text>
                    <text class="nc-iconfont nc-icon-xiaV6xx !text-[26rpx] ml-[18rpx] !text-[var(--text-color-light6)]" v-else @click="typePopup = true"></text>
                </view>
            </view>
            <view class="type-class">
                <u-popup :show="typePopup" mode="top" @close="typePopup = false">
                    <view @touchmove.prevent.stop class="py-[22rpx]">
                        <view class="leading-[80rpx] text-[26rpx] text-[#333] px-[50rpx]"
                              :class="{'bg-[var(--primary-color-light)] !text-primary font-500' : from_type == ''}" @click="searchTypeFn()">全部</view>
                        <view class="leading-[80rpx] text-[26rpx] text-[#333] px-[50rpx]"
                              :class="{'bg-[var(--primary-color-light)] !text-primary font-500' : from_type == index}"
                              v-for="(item,index) in pointType" @click="searchTypeFn(index,item)">{{ item.name }}</view>
                    </view>
                </u-popup>
            </view>
            <view class="px-[var(--sidebar-m)] py-[30rpx] flex items-center justify-between">
                <view class="flex items-center">
                    <view class="px-[30rpx]  bg-[#fff] rounded-[30rpx] text-[24rpx] leading-[54rpx] mr-[20rpx] text-[#333]"
                        :class="{'!text-[var(--primary-color)] font-500':amount_type == item.status}"
                        v-for="(item,index) in typeList" :key="index" @click="loadTypeFn(item.status)">{{ item.name }}</view>
                </view>
                <view class="flex items-center" @click="handleSelect">
                    <view class="text-[26rpx] text-[#333] mr-[10rpx]">日期</view>
                    <view class="nc-iconfont nc-icon-a-riliV6xx-36 !text-[28rpx] leading-[36rpx]"></view>
                </view>
            </view>
        </view>
        <mescroll-body ref="mescrollRef" @init="mescrollInit" :down="{ use: false }" @up="getPointListFn" top="202rpx">
            <view v-for="(item,index) in pointList" :key="index"
                  class="sidebar-margin card-template  mb-[var(--top-m)]">
                <view class="flex justify-between items-center">
                    <view class="text-[#333]">
                        <text class="text-[26rpx]">{{ item.month_info.year }}年</text>
                        <text class="text-[36rpx] font-500 ml-[10rpx] mr-[4rpx]">{{ item.month_info.month }}</text>
                        <text class="text-[26rpx]">月</text>
                    </view>
                    <view>
                        <text class="nc-iconfont nc-icon-xiaV6xx !text-[26rpx] text-[var(--text-color-light6)]" v-if="item.flag" @click="item.flag = false"></text>
                        <text class="nc-iconfont nc-icon-shangV6xx-1 !text-[26rpx] text-[var(--text-color-light6)]" v-else @click="item.flag = true"></text>
                    </view>
                </view>
                <view v-show="item.flag">
                    <template v-for="(subItem,subIndex) in item.month_data" :key="subItem.id">
                        <view class="flex items-center ">
                            <view class="w-[60rpx] h-[60rpx]">
                                <image v-if="subItem.account_data > 0" :src="img('static/resource/images/member/point/detail/point_add.png')" class="w-[60rpx] h-[60rpx]"/>
                                <image v-else :src="img('static/resource/images/member/point/detail/point_min.png')" class="w-[60rpx] h-[60rpx]"/>
                            </view>
                            <view class="flex-1  flex items-center ml-[20rpx]  box-border py-[30rpx] border-0" :class="{'border-solid border-t-[2rpx]  border-[#F0F2F8]' : subIndex}">
                                <view class="flex-1">
                                    <view class="text-[26rpx] text-[#333]">{{ subItem.from_type_name }}</view>
                                    <view class="text-[24rpx] text-[var(--text-color-light9)] mt-[16rpx]">{{ subItem.create_time }}</view>
                                </view>
                                <view class="text-[36rpx] font-500 text-[#03B521] price-font" :class="{ '!text-primary' : subItem.account_data > 0 }">{{ subItem.account_data > 0 ? '+' + subItem.account_data : subItem.account_data }}</view>
                            </view>
                        </view>
                    </template>
                </view>
            </view>
            <mescroll-empty :option="{tip : '暂无积分明细'}" v-if="!pointList.length && loading"></mescroll-empty>
        </mescroll-body>
        <!-- 时间选择 -->
        <select-date ref="selectDateRef" @confirm="confirmFn" />
    </view>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { t } from '@/locale'
import { redirect, img } from '@/utils/common';
import { getPointList, getPointType } from '@/app/api/member';
import MescrollBody from '@/components/mescroll/mescroll-body/mescroll-body.vue';
import MescrollEmpty from '@/components/mescroll/mescroll-empty/mescroll-empty.vue';
import useMescroll from '@/components/mescroll/hooks/useMescroll.js';
import { onPageScroll, onReachBottom } from '@dcloudio/uni-app';
import selectDate from '@/components/select-date/select-date.vue';

const { mescrollInit, downCallback, getMescroll } = useMescroll(onPageScroll, onReachBottom);

const from_type = ref('');
const from_type_name = ref('')
const amount_type = ref('all');
const create_time = ref([])
const pointList = ref<Array<any>>([]);
const mescrollRef = ref(null);
const loading = ref<boolean>(false);
const typeList = ref([
    { name: '全部', status: 'all' },
    { name: '收入', status: 'income' },
    { name: '支出', status: 'disburse' }
])

const getPointListFn = (mescroll: any) => {
    let data = {
        page: mescroll.num,
        page_size: mescroll.size,
        from_type: from_type.value,
        amount_type: amount_type.value,
        create_time: create_time.value
    };
    loading.value = false;
    getPointList(data).then((res: any) => {
        let newArr = res.data.data;
        mescroll.endSuccess(newArr.length);
        //设置列表数据
        if (mescroll.num == 1) {
            pointList.value = []; //如果是第一页需手动制空列表
        }
        pointList.value = pointList.value.concat(newArr);
        pointList.value = pointList.value.map(item => {
            item.flag = true
            return item
        })
        loading.value = true;
    }).catch(() => {
        loading.value = true;
        mescroll.endErr(); // 请求失败, 结束加载
    })
}

const pointType = ref({})
const getPointTypeFn = () => {
    getPointType('point').then((res: any) => {
        pointType.value = res.data
    })
}
getPointTypeFn()
// 关键词搜索条件搜索
const typePopup = ref(false)
const searchTypeFn = (index: string = '', item: any = {}) => {
    from_type.value = index
    from_type_name.value = item.name
    typePopup.value = false
    pointList.value = [];
    getMescroll().resetUpScroll();
}
// 类型搜索
const loadTypeFn = (type: string) => {
    amount_type.value = type;
    pointList.value = [];
    getMescroll().resetUpScroll();
}

//日期筛选
const selectDateRef = ref()
const handleSelect = () => {
    selectDateRef.value.show = true
}
// 确定时间筛选
const confirmFn = (data: any) => {
    create_time.value = data;
    pointList.value = []
    getMescroll().resetUpScroll();
}
</script>

<style lang="scss" scoped>
:deep(.type-class .u-popup .u-transition) {
    top: 86rpx !important;
}
</style>

<template>
    <view class="bg-[var(--page-bg-color)] min-h-screen overflow-hidden" :style="themeColor()">
        <view class="fixed left-0 right-0 top-0 z-99 bg-[#fff]">
            <view class="py-[14rpx] flex items-center justify-between px-[20rpx]">
                <view class="flex-1 search-input mr-[20rpx]">
                    <text @click.stop="searchTypeFn()" class="nc-iconfont nc-icon-sousuo-duanV6xx1 btn"></text>
                    <input class="input" maxlength="50" type="text" v-model="keyword" placeholder="请输入搜索关键词" placeholderClass="text-[var(--text-color-light9)] text-[24rpx]" confirm-type="search" @confirm="searchTypeFn()">
                    <text v-if="keyword" class="nc-iconfont nc-icon-cuohaoV6xx1 clear" @click="keyword=''"></text>
                </view>
                <view class="nc-iconfont nc-icon-a-riliV6xx-36 !text-[30rpx] leading-[36rpx] text-[var(--text-color-light6)]" @click="handleSelect"></view>
            </view>
        </view>
        <mescroll-body ref="mescrollRef" top="88rpx" @init="mescrollInit" :down="{ use: false }" @up="geVerifyRecordFn">
            <view class="sidebar-margin pt-[var(--top-m)]" v-if="list.length">
                <template v-for="(item,index) in list" :key="item.id">
                    <view class="w-full flex flex-col mb-[var(--top-m)] card-template" @click="toLink(item)">
                        <view class="flex items-center mb-[30rpx] leading-[1] text-[26rpx]">
                            <view class="nc-iconfont nc-icon-hexiaotaiV6xx !text-[26rpx] pr-[10rpx]"></view>
                            <text class="truncate text-[#303133] text-[26rpx]">核销码:</text>
                            <text class="ml-[10rpx] max-w-[494rpx]">{{ item.code }}</text>
                            <text class="text-[#303133] text-[26rpx] font-400 nc-iconfont nc-icon-fuzhiV6xx1 ml-[11rpx]" @click.stop="copy(item.code)"></text>
                        </view>
                        <view class="flex flex-1 mb-2" v-for="(dataItem,dataIndex) in item.value.list" :key="dataIndex">
                            <u--image width="130rpx" height="130rpx" :radius="'var(--goods-rounded-big)'" :src="img(dataItem.cover ? dataItem.cover : '')" mode="aspectFill">
                                <template #error>
                                    <image class="w-[130rpx] h-[130rpx] rounded-[var(--goods-rounded-big)] overflow-hidden" :src="img('static/resource/images/diy/shop_default.jpg')" mode="aspectFill"/>
                                </template>
                            </u--image>
                            <view class="flex flex-col flex-1 ml-[20rpx] py-[4rpx]">
                                <view class="max-w-[490rpx] leading-[1.3] truncate text-[28rpx] text-[#303133]">{{ dataItem.name }}</view>
                                <view class="mt-[14rpx] truncate text-[24rpx] text-[var(--text-color-light9)] max-w-[490rpx] " v-if="item.sub_name">{{ item.sub_name }}</view>
                                <view class="text-[24rpx] mt-[10rpx] text-[var(--text-color-light9)]">x{{dataItem.verify_num}}</view>
                            </view>
                        </view>
                        <view class="flex bg-[var(--temp-bg)] py-[20rpx] px-[20rpx] rounded-[12rpx] mt-[20rpx]">
                            <view class="flex-1">
                                <view class="text-[22rpx] text-[var(--text-color-light9)] mb-[10rpx] leading-[30rpx]">核销时间</view>
                                <view class="text-[26rpx] text-[#303133] leading-[36rpx]">{{ item.create_time }}</view>
                            </view>
                            <view class="flex-1">
                                <view class="text-[22rpx] text-[var(--text-color-light9)] mb-[10rpx] leading-[30rpx]">核销员</view>
                                <view class="text-[26rpx] text-[#303133] leading-[36rpx]">{{ item.member ? item.member.nickname : '--' }}</view>
                            </view>
                        </view>
                    </view>
                </template>
            </view>
            <mescroll-empty v-if="!list.length && loading" :option="{tip : '暂无核销记录'}"></mescroll-empty>
        </mescroll-body>
        <!-- 时间选择 -->
        <select-date ref="selectDateRef" @confirm="confirmFn" />
        <!-- #ifdef MP-WEIXIN -->
        <!-- 小程序隐私协议 -->
        <wx-privacy-popup ref="wxPrivacyPopupRef"></wx-privacy-popup>
        <!-- #endif -->
    </view>
</template>

<script setup lang="ts">
import { ref, reactive, computed, nextTick } from 'vue'
import MescrollBody from '@/components/mescroll/mescroll-body/mescroll-body.vue'
import MescrollEmpty from '@/components/mescroll/mescroll-empty/mescroll-empty.vue'
import useMescroll from '@/components/mescroll/hooks/useMescroll.js'
import { onPageScroll, onReachBottom, onLoad } from '@dcloudio/uni-app'
import { getVerifyRecords } from '@/app/api/verify'
import { img, redirect, copy } from '@/utils/common'
import selectDate from '@/components/select-date/select-date.vue';

const keyword = ref<string>('')
const create_time = ref([])
const list = ref<Array<Object>>([])
const loading = ref<boolean>(false)
const { mescrollInit, downCallback, getMescroll } = useMescroll(onPageScroll, onReachBottom)
const wxPrivacyPopupRef: any = ref(null)
onLoad(() => {
    // #ifdef MP
    nextTick(() => {
        if (wxPrivacyPopupRef.value) wxPrivacyPopupRef.value.proactive();
    })
    // #endif
});
const geVerifyRecordFn = (mescroll: any) => {
    loading.value = false;
    let data: object = {
        page: mescroll.num,
        limit: mescroll.size,
        keyword: keyword.value,
        create_time: create_time.value
    };

    getVerifyRecords(data).then((res: any) => {
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

const toLink = (data: AnyObject) => {
    redirect({ url: '/app/pages/verify/detail', param: { code: data.code } })
}

// 关键词搜索条件搜索
const searchTypeFn = () => {
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
    list.value = []
    getMescroll().resetUpScroll();
}

</script>

<style lang="scss" scoped>
:deep(.uni-picker-view-content) {
    z-index: 10;
}

:deep(.uni-picker-view-indicator::before) {
    border: none !important;
}

:deep(.uni-picker-view-indicator::after) {
    border: none !important;
}
</style>
